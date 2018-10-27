<?php

namespace App\Form;

use App\Entity\InventoryWaste;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class InventoryWasteFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('datum', DateType::class, [
                    'label' => 'Dátum',
                    'widget' => 'single_text',
                    'attr' => ['placeholder' => 'ÉÉÉÉ-HH-NN', 'autocomplete' => 'off'],
                    'html5' => false,
                ]
            )

            ->add('items', CollectionType::class, [
							'entry_type' => InventoryWasteItemFormType::class,
                           'entry_options' => array('label' => false),
							]

            )
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
						'data_class' => InventoryWaste::class
        ]);
    
    }
    
}

