<?php

namespace App\Form;

use App\Entity\InventoryInvoiceCompany;
use App\Entity\InventoryInvoice;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

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
    
}

