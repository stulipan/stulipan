<?php
//a SetDiscountType-bol csinaltam
declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;

use App\Entity\Geo\GeoCountry;
use App\Entity\Geo\GeoPlace;
use App\Entity\OrderAddress;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class OrderAddressType extends AbstractType
{
    private $urlGenerator;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em)
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $preferredCountries = $this->em->getRepository(GeoCountry::class)->findBy(['alpha2' => 'hu']);
        //$builder->setAction($this->urlGenerator->generate('cart_set_delivery_address', ['id' => '0']));
        $builder->add('id',HiddenType::class,[
             // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
            'mapped' => false,
        ]);
        $builder->add('street',TextType::class,[
            'label' => 'Cím',
        ]);
        $builder->add('city',TextType::class,[
            'label' => 'Város',
            'attr' => ['autocomplete' => 'cityXXX'],
        ]);
        $builder->add('zip',IntegerType::class,[
            'label' => 'Iranyítószám',
        ]);

//        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPostSubmitData']);

        $builder->add('province',TextType::class,[
            'label' => 'Megye',
        ]);
        $builder->add('country',EntityType::class,[
            'class' => GeoCountry::class,
            'label' => 'Ország',
            'placeholder' => 'Válassz országot...',
            'attr' => ['class' => 'custom-select'],
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC');
            },
            'choice_label' => 'name',
            'preferred_choices' => $preferredCountries,
        ]);
        $builder->add('addressType',HiddenType::class,[
            'attr' => ['value' => $options['addressType']],
        ]);
        $builder->getForm();
    }

//    /**
//     * Elősször azt próbáltam, hogy a form adatait manipulálom FormEvent kapcsán
//     */
//    function onPostSubmitData(FormEvent $event)
//    {
//        $form = $event->getForm();
////        $formFieldData = $event->getForm()->getData(); // 'zip'
//        $formData = $event->getData();
//        if ($formData['zip']) {
//            $place = $this->em->getRepository(GeoPlace::class)
//                ->findOneBy(['zip' => $formData['zip']]);
//
//            if (null !== $place && '' !== $place) {
//                $formData['city'] = $place->getCity();
//                $formData['province'] = $place->getProvince();
//                $event->setData($formData);
//
////                $this->addElements($form->getParent(), $place);
//            }
//        } else {
//            return;
//        }
//    }
//
//    protected function addElements(FormInterface $form, GeoPlace $place = null)
//    {
//        $city = null === $place ? null : $place->getCity();
//        $province = null === $place ? null : $place->getProvince();
//
//        $form->add('city', TextType::class,[
//            'label' => 'Város',
//            'attr' => ['autocomplete' => 'cityXXX'],
//        ]);
//        $form->get('city')->setData($city);
//        $form->add('province',TextType::class,[
//            'label' => 'Megye',
//        ]);
//        $form->get('province')->setData($province);
//    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderAddress::class,
            'addressType' => '',
            'attr' => ['novalidate' => 'novalidate'],
            'error_bubbling' => true,
//            'by_reference' => false,  // https://symfony.com/doc/current/reference/forms/types/form.html#by-reference
        ]);
    }

}