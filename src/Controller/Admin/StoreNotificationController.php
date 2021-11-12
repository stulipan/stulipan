<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use App\Entity\Geo\GeoCountry;
use App\Entity\Order;
use App\Entity\OrderAddress;
use App\Entity\OrderItem;
use App\Entity\PaymentMethod;
use App\Entity\Product\Product;
use App\Entity\ShippingMethod;
use App\Entity\StoreEmailTemplate;
use App\Form\EmailTemplateFormType;
use App\Services\StoreSettings;
use App\Twig\AppExtension;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @Route("/admin/settings")
 */
class StoreNotificationController extends AbstractController
{
    /**
     * @IsGranted("ROLE_MANAGE_STORE_NOTIFICATIONS")
     * @Route("/notifications", name="notification-list")
     */
    public function listNotifications()
    {
        $notifications = $this->getDoctrine()
            ->getRepository(StoreEmailTemplate::class)
            ->findAll();

        return $this->render('admin/settings/notification-list.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * @IsGranted("ROLE_MANAGE_STORE_NOTIFICATIONS")
     * @Route("/notifications/edit/{id}", name="notification-edit")
     */
    public function editNotification(Request $request, ?StoreEmailTemplate $emailTemplate,
                                      $id = null, TranslatorInterface $translator, AppExtension $appExtension,
                                     StoreSettings $storeSettings
                                    )
    {
        if (!$emailTemplate) {
            // new
            $form = $this->createForm(EmailTemplateFormType::class);
        } else {
            // edit existing
            $form = $this->createForm(EmailTemplateFormType::class, $emailTemplate);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailTemplate = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($emailTemplate);
            $em->flush();

            $this->addFlash('success', $translator->trans('settings.notification.email-template-saved-successfully'));
            return $this->redirectToRoute('notification-edit', ['id' => $emailTemplate->getId()]);
        }

        $products = $this->getDoctrine()->getRepository(Product::class)->fetchVisibleProducts();
        $shippingMethod = $this->getDoctrine()->getRepository(ShippingMethod::class)->findBy(['enabled' => true])[0];
        $paymentMethod = $this->getDoctrine()->getRepository(PaymentMethod::class)->findBy(['enabled' => true])[0];

        $order = new Order();
        $order->setNumber('20909931');
        $order->setEmail('info@example.com');
        $order->setFirstname('Jane');
        $order->setLastname('Doe');
        $order->setPhone('+36xx1234567');

            $product = $products[0];
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity(2);
            $orderItem->setUnitPrice($product->getPrice()->getNumericValue());
            $orderItem->setPriceTotal($orderItem->getUnitPrice() * $orderItem->getQuantity());
        $order->addItem($orderItem);

            $product = $products[1];
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity(1);
            $orderItem->setUnitPrice($product->getPrice()->getNumericValue());
            $orderItem->setPriceTotal($orderItem->getUnitPrice() * $orderItem->getQuantity());
        $order->addItem($orderItem);
        $order->setShippingMethod($shippingMethod);
        $order->setPaymentMethod($paymentMethod);
        $order->setShippingFee(1190);
        $order->setPaymentFee(500);
        $order->setSchedulingPrice(2990);
        $order->setShippingFirstname($order->getFirstname());
        $order->setShippingLastname($order->getLastname());
        $order->setShippingPhone($order->getPhone());

            $address = new OrderAddress();
            $address->setStreet('Broken Dreams Boulevard 17.');
            $address->setCity('New Amsterdam');
            $address->setProvince('NA');
            $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $address->setZip('3255');
            $address->setAddressType(Address::DELIVERY_ADDRESS);
        $order->setShippingAddress($address);

        $templateSlug = $emailTemplate->getSlug();
        $loader = new ArrayLoader([
            'subject' => $emailTemplate->getSubject(),
            $templateSlug => $emailTemplate->getBody(),
        ]);
        $twig = new Environment($loader);
        $twig->addExtension($appExtension);

        $subject = $twig->render('subject', [
            'orderNumber' => '#'.$order->getNumber(),
            'storeUrl' => $storeSettings->get('store.url'),
            'totalAmount' => $appExtension->formatMoney($order->getSummary()->getTotalAmountToPay()),
        ]);

        $html = $subject.PHP_EOL.PHP_EOL.PHP_EOL;
        $html .= $twig->render($templateSlug, [
            'subject' => $subject,
            'order' => $order,
            'youReceivedThisEmail' => 'Ezt a levelet a www.rafina.hu oldalon leadott rendelésed miatt kaptad.'.PHP_EOL,
            'legalNotice' => 'Example Holding Ltd., Address: 3255 New Amsterdam, Broken Dreams Boulevard 17.; VAT number: EU12345678'.PHP_EOL,
            'storeUrl' => $storeSettings->get('store.url'),
        ]);

        return $this->render('admin/settings/notification-edit.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'emailHtml' => $html,
        ]);
    }

}