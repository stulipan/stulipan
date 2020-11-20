<?php

namespace App\Admin\Boltzaras\Form;

use App\Entity\Boltzaras\BoltzarasWeb;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;

class BoltzarasWebFormType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('month', DateType::class, [
                'label' => 'Hónap',
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'ÉÉÉÉ-HH-NN', 'autocomplete' => 'off'],
                'html5' => false,
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Webes bevétel',
                'attr' => [
                    'placeholder' => '',
                    'class' => 'form-control',
                    'min' => 0,
                ],
            ])
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BoltzarasWeb::class
        ]);
    
    }

    public function getBlockPrefix()
    {
        return '';
    }
    
}

