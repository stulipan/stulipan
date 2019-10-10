<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\DeliveryDateInterval;
use App\Entity\DeliveryDateType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;


class DeliveryDateIntervalFormType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',TextType::class, [
                'label' => 'Idősáv megnevezése',
                'attr' => [
                    'placeholder' => 'Megnevezés, pl: 16-20',
                    'autocomplete' => 'off'
                ],
            ])
//            ->add('dateType', EntityType::class, [
//                'class' => DeliveryDateType::class,
//                'choice_label' => 'name',
//                'label' => 'Típus',
//                'placeholder' => 'Válassz valamit...',
//            ])
            ->add('price', NumberType::class,[
                'label' => 'Felár',
                'attr' => ['placeholder' => '', 'autocomplete' => 'off',],
                'required' => true,
//                'constraints' => [new NotBlank()],
            ])
            ->add('deliveryLimit', NumberType::class,[
                'label' => 'Szállítások száma',
                'attr' => ['placeholder' => 'Max szállítás'],
                'required' => false,
            ])
            ->add('ordering', NumberType::class,[
                'label' => 'Sorrend',
                'attr' => ['placeholder' => 'Sorrend']
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeliveryDateInterval::class
        ]);
    }
}