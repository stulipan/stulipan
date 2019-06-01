<?php

declare(strict_types=1);

namespace App\Form;

use App\Controller\Utils\GeneralUtils;
use App\Entity\DeliveryDateInterval;
use App\Entity\Model\DeliveryDate;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Form\DataTransformer\DeliveryDateToStringTransformer;
use App\Form\DataTransformer\DeliveryIntervalToStringTransformer;
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


class CartSelectDeliveryDateFormType extends AbstractType
{
    private $urlGenerator;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var DeliveryDateToStringTransformer
     */
    private $dateTransformer;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em, \Twig_Environment $twig,
                                DeliveryDateToStringTransformer $dateTransformer)
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
        $this->twig = $twig;
        $this->dateTransformer = $dateTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $offset = GeneralUtils::DELIVERY_DATE_HOUR_OFFSET;
        $days = (new \DateTime('+3 days'))->diff(new \DateTime('now'))->days;
        for ($i = 0; $i <= $days; $i++) {
            /**
             * ($i*24 + offset) = 0x24+4 = 4 órával későbbi dátum lesz
             * Ez a '4' megegyezik azzal, amit a javascriptben adtunk meg, magyarán 4 órával
             * későbbi időpont az első lehetséges szállítási nap.
             */
            $dates[(new \DateTime('+'. ($i*24 + $offset) .' hours'))->format('M j, D')] = (new \DateTime('+'. ($i*24 + $offset).' hours'))->format('Y-m-d');
        }

        $builder->setAction($this->urlGenerator->generate('cart-setDeliveryDate'));
        $builder->add('deliveryDate', ChoiceType::class, [
                'mapped' => true,
                'expanded' => true,
                'multiple' => false,
                'choices' => $dates,
//                'choice_label' => function($deliveryDate, $key, $value) {
//                    $e = explode(',', $key);
//                    return $e[0].'<br>'.$e[1];
//                },
                'choice_label' => function($deliveryDate, $key, $value) {
                    $e = explode(',', $key);
                    return $this->twig->render('webshop/cart/choiceItem-label.html.twig', [
                        'item1' => $e[0],
                        'item2' => $e[1],
                    ]);
                },
                'invalid_message' => 'Ez negy valid datum!',

                'placeholder' => false, // Makes the special "empty" option ("Choose an option") disappear from a select widget. Only when multiple => false
            ]);

        // ez akkor kellett amikor a DeliveryDate class (Entity\Model\DeliveryDate)-ban a datum DateTime volt. Most sima string.
//        $builder->get('deliveryDate')->addModelTransformer($this->dateTransformer);

        /**
         * The event listener is needed in order to alter the form right before submitting it. Why?
         * As the values of the deliveryDate radio inputs are changed via AJAX, the form becomes invalid and it's not being submitted.
         */
//        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
//            $form = $event->getForm();
//            $data = $event->getData();
//            $form->remove('deliveryDate');
//            $form->add('deliveryDate', ChoiceType::class, [
//                'choices' => [
//                    $data['deliveryDate'] => $data['deliveryDate'],
//                ]
//            ]);
//        });

        // ez akkor kellett amikor a DeliveryDate class (Entity\Model\DeliveryDate)-ban a datum DateTime volt. Most sima string.
        // es akkor meg a datum alapjan generalta ki a hozza tartozo intervallumot. De ez MAR NINCS HASZNALATBAN, mivel
        // a Step2-ben mar nem form segitsegevel valaszt datumot & intervallumot!
//        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onPostSetData']);
//        $builder->get('deliveryDate')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmitData']);
//        $builder->add('submit',SubmitType::class);   ////////////////????????????
        $builder->getForm();
    }

    function onPostSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data) {
//            $deliveryDate = \DateTime::createFromFormat('!Y-m-d',$data->getDeliveryDate());
            $deliveryDate = $data->getDeliveryDate();

            $specialDate = $this->em->getRepository(DeliverySpecialDate::class)
                ->findOneBy(['specialDate' => $deliveryDate]);

            if (!$specialDate) {
                $dateType = $this->em->getRepository(DeliveryDateType::class)
                    ->findOneBy(['default' => DeliveryDateType::IS_DEFAULT]);
            } else {
                $dateType = $specialDate->getDateType();
            }
            $this->addElements($form, $dateType);
        } else {
            return;
        }
    }

    function onPostSubmitData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getForm()->getData();
        if ($data) {
//            $deliveryDate = \DateTime::createFromFormat('!Y-m-d',$data);
            $deliveryDate = $data;
            $specialDate = $this->em->getRepository(DeliverySpecialDate::class)
                ->findOneBy(['specialDate' => $deliveryDate]);

            if (!$specialDate) {
                $dateType = $this->em->getRepository(DeliveryDateType::class)
                    ->findOneBy(['default' => DeliveryDateType::IS_DEFAULT]);
            } else {
                $dateType = $specialDate->getDateType();
            }
            $this->addElements($form->getParent(), $dateType);
        } else {
            return;
        }

    }

    protected function addElements(FormInterface $form, DeliveryDateType $dateType = null)
    {
        $intervals = null === $dateType ? null : $dateType->getIntervals()->getValues();
//        $form->add('deliveryInterval', CartSelectDeliveryIntervalType::class,[
////            'label' => false,
//            'intervals' => $intervals,
//            'dateType' => $dateType,
//            'required' => false,
//            'mapped' => true,
//        ]);
        $form->add('deliveryInterval', EntityType::class,[
                'class' => DeliveryDateInterval::class,
                'choice_label' => function (DeliveryDateInterval $interval) {
                    return $this->twig->render('webshop/cart/choiceItem-label-deliveryInterval.html.twig', [
                        'intervalName' => $interval->getName(),
                        'intervalPrice' => $interval->getPrice(),
                    ]);
            },
                'mapped' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => $intervals,
            ]);
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







    // Ez volt a regi, eredeti choices.
    //            'choices' => [
//                (new \DateTime('now'))->format('M j \- l') => (new \DateTime('now'))->format('Y-m-d'),
//                (new \DateTime('+1 days'))->format('M j \- l') => (new \DateTime('+1 days'))->format('Y-m-d'),
//                (new \DateTime('+2 days'))->format('M j \- l') => (new \DateTime('+2 days'))->format('Y-m-d'),
//                (new \DateTime('+3 days'))->format('M j \- l') => (new \DateTime('+3 days'))->format('Y-m-d'),
//                (new \DateTime('+4 days'))->format('M j \- l') => (new \DateTime('+4 days'))->format('Y-m-d'),
//                (new \DateTime('+5 days'))->format('M j \- l') => (new \DateTime('+5 days'))->format('Y-m-d'),
//            ],


}