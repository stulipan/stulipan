<?php

namespace App\Form;

use App\Entity\Keszlet;

use Symfony\Component\Form\AbstractType;
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

class KeszletFormType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        // createFormBuilder is a shortcut to get the "form factory"
        // and then call "createBuilder()" on it

        $builder
            ->add('datum', DateType::class, [
                'label' => 'Dátum',
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'éééé-hh-nn', 'class' => 'asdf', 'autocomplete' => 'off'],
                'html5' => false,
                ]
            )
            ->add('termeknev', TextType::class, [
							'label' => 'Megnevezés',
							'attr' => ['placeholder' => 'Pl: Rózsa, vörös'],
							]
				
            )
            ->add('stock', IntegerType::class, [
                'label' => 'Darab',
                'attr' => ['placeholder' => 'Darabszám'],
            	]
            )
            ->add('cog', NumberType::class, [
                    'label' => 'Beszerzési ár (bruttó)',
                    'attr' => ['placeholder' => ''],
                    ]
            )
            ->add('grossPrice', NumberType::class, [
                    'label' => 'Eladási ár (bruttó)',
                    'attr' => ['placeholder' => ''],
                ]
            )
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
						'data_class' => Keszlet::class
        ]);
    
    }
    
}

