<?php

declare(strict_types=1);

namespace App\Form\DeliveryDate;

use App\Controller\Utils\GeneralUtils;
use App\Entity\DeliveryDateInterval;
use App\Entity\Model\DeliveryDate;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Form\DataTransformer\DeliveryDateToStringTransformer;
use App\Form\DataTransformer\DeliveryIntervalToStringTransformer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class CartSelectDeliveryDateFormType extends AbstractType
{
    private $urlGenerator;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var DeliveryDateToStringTransformer
     */
    private $dateTransformer;

    private $translator;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em, Environment $twig,
                                DeliveryDateToStringTransformer $dateTransformer, TranslatorInterface $translator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
        $this->twig = $twig;
        $this->dateTransformer = $dateTransformer;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $offset = GeneralUtils::DELIVERY_DATE_HOUR_OFFSET;
        $days = (new DateTime('+2 months'))->diff(new DateTime('now'))->days;
        for ($i = 0; $i <= $days; $i++) {
            /**
             * ($i*24 + offset) = 0x24+4 = 4 órával későbbi dátum lesz
             * Ez a '4' megegyezik azzal, amit a javascriptben adtunk meg, magyarán 4 órával
             * későbbi időpont az első lehetséges szállítási nap.
             */
            $dates[(new DateTime('+'. ($i*24 + $offset) .' hours'))->format('M j, D')] = (new DateTime('+'. ($i*24 + $offset).' hours'))->format('Y-m-d');
        }

        $builder->setAction($this->urlGenerator->generate('cart-setDeliveryDate'));
        $builder->add('deliveryDate', ChoiceType::class, [
                'mapped' => true,
                'expanded' => true,
                'multiple' => false,
                'choices' => $dates,
                'choice_label' => function($deliveryDate, $key, $value) {
                    $dateTime = (new DateTime())->createFromFormat('Y-m-d', $deliveryDate);
                    return $this->twig->render('webshop/site/vp-label-date.html.twig', [
                        'item1' => $dateTime,
                        'item2' => $dateTime,
                        ]);
                },
                'invalid_message' => 'Ez negy valid datum!',
                'placeholder' => false, // Makes the special "empty" option ("Choose an option") disappear from a select widget. Only when multiple => false
            ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeliveryDate::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'dateForm';
    }
}