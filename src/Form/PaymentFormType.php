<?php

namespace App\Form;

use App\Entity\PaymentMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentFormType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => '',
                ]
            ])
            ->add('shortcode', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => '',
                ]
            ])
            ->add('short', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Szállítási mód leírása ide...',
                    'rows' => '3'
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Szállítási mód leírása ide...',
                    'rows' => '3'
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Szállítási díj',
                'required' => false,
                'attr' => [
                    'placeholder' => '',
                    'min' => 0,
                ],
            ])
            ->add('ordering', NumberType::class, [
                'label' => "Sorrend",
                'required' => false,
                'attr' => [
                    'placeholder' => '',
                    'min' => 0,
                ],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Engedélyezve',
                'required' => false,
//                'choices' => [
//                    'Igen' => 1,
//                    'Nem' => 0,
//                ],
//                'multiple' => true,
//                'expanded' => true,
            ])
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentMethod::class,
            'attr' => ['autocomplete' => 'off'],

        ]);
    
    }

//    public function getBlockPrefix()
//    {
//        return '';
//    }
    
}

