<?php

namespace App\Form\Inventory;

use App\Entity\Inventory\InventoryInvoiceCompany;
use App\Entity\Inventory\InventoryInvoice;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;

class InventoryInvoiceFormType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        // createFormBuilder is a shortcut to get the "form factory"
        // and then call "createBuilder()" on it

        $builder
            ->add('company', EntityType::class, [
                'class' => InventoryInvoiceCompany::class,
                'query_builder' => function(EntityRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        ->orderBy('c.company', 'ASC');
                },
                'multiple' => false,
                'label' => 'Beszállító',
                'placeholder' => 'Válassz beszállítót...',
//                'attr' => ['class' => 'custom-select'],
            ])
            ->add('datum', DateType::class, [
					'label' => 'Dátum',
            		'widget' => 'single_text',
            		'attr' => ['placeholder' => 'ÉÉÉÉ-HH-NN', 'autocomplete' => 'off'],
            		'html5' => false,
            		]
			)            	
            ->add('osszeg', NumberType::class,
            	array(
            		'label' => 'Összeg',
            		'attr' => ['placeholder' => 'Számla összege', 'autocomplete' => 'off'],
            		)
            	)
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InventoryInvoice::class
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
    
}

