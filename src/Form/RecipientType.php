<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Recipient;
use App\Form\AddressType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class RecipientType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('cart_set_recipient'));
        $builder->add(
            'id',
            HiddenType::class,
            // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
            ['mapped' => false]
        );
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'Címzett',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Szabó János',
                    'autocomplete' => 'name'
                ]
            ]
        );
        $builder->add(
            'address',
            AddressType::class,
            [
                'label' => false,
            ]
        );
        $builder->add(
            'phone',
            IntegerType::class,
            [
                'label' => 'Telefonszám',
                'required' => false,
            ]
        );
//        $builder->add(
//            'submit',
//            SubmitType::class,
//            [
//                'label' => 'Mentés',
//                'attr' => [
//                    'icon' => 'fa fa-minus-circle'
//                ]
//            ]
//        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recipient::class
        ]);
    }


}