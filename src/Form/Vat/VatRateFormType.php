<?php

namespace App\Form\Vat;

use App\Entity\VatRate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VatRateFormType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
                'label' => 'ÁFA megnevezése',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Pl: Alapértelmezett ÁFA...',
                ],
            ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VatRate::class
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
    
}

