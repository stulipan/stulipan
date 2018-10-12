<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Sender;
use App\Form\AddressType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class SenderType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('cart_set_sender'));
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
                'label' => 'Feladó',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Szabó Mária',
                    'autocomplete' => 'name'
                ]
            ]
        );
        $builder->add(
            'company',
            TextType::class,
            [
                'label' => 'Cégnév',
                'required' => false,
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
            'customer',
            HiddenType::class,
            // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
            [
                'mapped' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sender::class
        ]);
    }


}