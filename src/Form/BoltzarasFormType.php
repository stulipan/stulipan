<?php

namespace App\Form;

use App\Entity\Boltzaras;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\DateType;

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
//		         'attr' => ['class' => 'custom-select'],
            ])
            ->add('idopont', DateType::class, [ 
                'label' => 'Időpont',
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'ÉÉÉÉ-HH-NN', 'autocomplete' => 'off'],
                'html5' => false,
            ])
            ->add('kassza', NumberType::class, [
                'label' => 'Kasszába beütve',
                'attr' => [
                    'placeholder' => 'Kassza',
                    'class' => 'form-control',
                    'min' => 0,
                ],
            ])
            ->add('bankkartya', NumberType::class, [
                'label' => "Bankkártya",
                'attr' => [
                    'placeholder' => 'Bankkártya',
                    'min' => 0,
                ],
            ])
            ->add('keszpenz', NumberType::class, [
                'label' => 'Készpénz',
                'attr' => [
                    'placeholder' => 'Készpénz',
                    'min' => 0,
                ],
            ])
            ->add('note', TextareaType::class, [
                'label' => 'Megjegyzés',
                'attr' => [
                    'placeholder' => 'Megjegyzés ide...',
                    'rows' => '3'
                ],
            ])
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
						'data_class' => Boltzaras::class
						//vagy ez ugyanaz: 'data_class' => 'App\Entity\Boltzaras'
        ]);
    
    }

//    public function getBlockPrefix()
//    {
//        return '';
//    }
    
}

