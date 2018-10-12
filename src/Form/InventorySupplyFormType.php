<?php

namespace App\Form;

use App\Entity\InventorySupply;
//use App\Form\InventorySupplyItemFormType;

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
                    'attr' => ['placeholder' => 'éééé-hh-nn', 'class' => 'asdf', 'autocomplete' => 'off'],
                    'html5' => false,
                ]
            )

            ->add('items', CollectionType::class, [
							'entry_type' => InventorySupplyItemFormType::class,
                           'entry_options' => array('label' => false),
							]

            )
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
						'data_class' => InventorySupply::class
        ]);
    
    }
    
}

