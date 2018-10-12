<?php

namespace App\Form;

use App\Entity\Boltzaras;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

class BoltzarasFormType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        // createFormBuilder is a shortcut to get the "form factory"
        // and then call "createBuilder()" on it

        $builder
            ->add('munkatars', null, [
							'label' => 'Munkatárs neve', 
							'placeholder' => 'Válassz...',
		            		'attr' => ['class' => 'custom-select'],
							//'constraints' => [ new NotNull(array('message' => "Válaszd ki magad!")), ]
							]
				
            	)            
            ->add('idopont', DateType::class, [ 
					'label' => 'Időpont', 
            		'widget' => 'single_text',
            		'attr' => ['placeholder' => 'éééé-hh-nn', 'class' => 'asdf', 'autocomplete' => 'off'],
            		'html5' => false,
            		]
			)            	
            ->add('kassza', IntegerType::class, 
            	array(
            		'label' => 'Kasszába beütve', 
            		'attr' => array('placeholder' => 'Kassza'), 
	                //'constraints' => array( new NotNull(array('message' => "Please provide Kassza")), )
            		)
            	)            
            ->add('bankkartya', IntegerType::class, 
            	array(
            		'label' => "Bankkártya", 
            		'attr' => array('placeholder' => 'Bankkártya'),
            		//'constraints' => array( new NotNull(array('message' => "Please provide Name")), )
            		)
            	)            
            ->add('keszpenz', IntegerType::class, 
            	array(
					'label' => 'Készpénz', 
					'attr' => array('placeholder' => 'Készpénz'),
					//'constraints' => array( new NotNull(array('message' => "Please provide keszpenz")), )
            		)
            	)            
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
						'data_class' => Boltzaras::class
						//vagy ez ugyanaz: 'data_class' => 'App\Entity\Boltzaras'
        ]);
    
    }
    
}

