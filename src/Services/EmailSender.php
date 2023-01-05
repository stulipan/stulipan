<?php

namespace App\Services;

use App\Entity\Order;
use App\Entity\StoreEmailTemplate;
use App\Twig\AppExtension;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;

class EmailSender
{
    private const ADMIN_EMAIL_ADDRESS = 'rafinadekor@gmail.com';

    private $em;
    private $mailer;
    private $appExtension;
    private $storeSettings;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer,
                                AppExtension $appExtension, StoreSettings $storeSettings)
    {
        $this->em = $entityManager;
        $this->mailer = $mailer;
        $this->appExtension = $appExtension;
        $this->storeSettings = $storeSettings;

    }

    /**
     * @param Order|null $order
     * @param string $templateSlug
     * @throws ErrorException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendEmail(?Order $order, string $templateSlug, bool $isAdminNotification = false)
    {
        if (!$order) {
            throw new ErrorException('STUPID: Hianyzik az order!');
        }
        if (!$templateSlug) {
            throw new ErrorException('STUPID: Hianyzik az email template!');
        }

        $template = $this->em->getRepository(StoreEmailTemplate::class)->findOneBy(['slug' => $templateSlug]);

        if (!$template) {
            throw new ErrorException('Nincs ilyen email template!');
        }

        $loader = new ArrayLoader([
            'subject' => $template->getSubject(),
            $templateSlug => $template->getBody(),
        ]);
        $twig = new Environment($loader);
        $twig->addExtension($this->appExtension);

//        $subject = str_replace('{{orderNumber}}', '#'.$order->getNumber(), $template->getSubject());
//        $subject = $this->translator->trans($template->getSubject(), [
//            '{{orderNumber}}' => '#'.$order->getNumber(),
//            '{{storeUrl}}' => $this->storeSettings->get('store.url'),
//            '{{totalAmount}}' => $this->appExtension->formatMoney($order->getSummary()->getTotalAmountToPay()),
//        ]);
        $subject = $twig->render('subject', [
            'orderNumber' => '#'.$order->getNumber(),
            'storeUrl' => $this->storeSettings->get('store.url'),
            'totalAmount' => $this->appExtension->formatMoney($order->getTotalAmountToPay()),
        ]);


        $html = $twig->render($templateSlug, [
            'subject' => $subject,
            'order' => $order,
            'youReceivedThisEmail' => 'Ezt a levelet a www.rafina.hu oldalon leadott rendelésed miatt kaptad.'.PHP_EOL,
            'legalNotice' => 'Balla Kálmán Ferenc EV., Székhely: 4492 Dombrád, Andrássy út 219/A.; Adószám: 57264510-1-51'.PHP_EOL,
            'storeUrl' => $this->storeSettings->get('store.url'),
        ]);

        if (!$isAdminNotification) {
            // Ez megy a vásárlónak
            $email = (new TemplatedEmail())
                ->from(new Address(
                    $this->storeSettings->get('notifications.sender-email'),
                    $this->storeSettings->get('notifications.sender-name')
                ))
                ->replyTo(new Address(
                    $this->storeSettings->get('notifications.sender-email'),
                    $this->storeSettings->get('notifications.sender-name')
                ))
                ->to($order->getEmail())
                ->subject($subject)
                ->html($html)
            ;
        } else {
            // Ez megy az adminnak
            $email = (new TemplatedEmail())
                ->from(new Address(
                    'rendeles@rafina.hu',
                    $order->getFullname()
                ))
                ->replyTo(new Address(
                    $this->storeSettings->get('notifications.sender-email'),
                    $this->storeSettings->get('notifications.sender-name')
                ))
                ->to(self::ADMIN_EMAIL_ADDRESS)
                ->subject($subject)
                ->html($html)
            ;
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new ErrorException('Az emailt nem sikerült elküldeni! '.$e->getMessage());
        }
    }
}