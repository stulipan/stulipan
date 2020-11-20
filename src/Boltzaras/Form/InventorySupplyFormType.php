<?php

namespace App\Form\Inventory;

use App\Entity\Boltzaras\InventorySupply;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class InventorySupplyFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datum', DateType::class, [
                'label' => 'Dátum',
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'ÉÉÉÉ-HH-NN', 'autocomplete' => 'off'],
                'html5' => false,
            ])
            ->add('items', CollectionType::class, [
                'entry_type' => InventorySupplyItemFormType::class,
                'entry_options' => ['label' => false],
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InventorySupply::class,
            'attr' => ['autocomplete' => 'off'],
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
    
}

