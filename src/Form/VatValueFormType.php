<?php

namespace App\Form;

use App\Entity\VatRate;
use App\Entity\VatValue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class VatValueFormType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', NumberType::class, [
                'label' => 'ÁFA értéke',
                'attr' => [
//                    'class' => 'form-control',
                    'min' => 0,
                    'autocomplete' => 'off',
                ],
            ])
            ->add('vatRate', EntityType::class, [
                'class' => VatRate::class,
                'label' => 'ÁFA típusa',
                'placeholder' => 'Válassz...',
            ])
            ->add('expiresAt', DateType::class, [
                'label' => 'Lejár ekkor',
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'ÉÉÉÉ-HH-NN', 'autocomplete' => 'off'],
                'html5' => false,
            ])
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VatValue::class,
            'attr' => ['autocomplete' => 'off'],
        ]);
    
    }
}

