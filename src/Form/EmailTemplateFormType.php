<?php

namespace App\Form;

use App\Entity\StoreEmailTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTemplateFormType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('slug', TextType::class, [
                'required' => false,
            ])
            ->add('subject', TextType::class, [
                'required' => false,
            ])
            ->add('body', TextareaType::class, [
                'required' => false,
            ])
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StoreEmailTemplate::class,
            'attr' => ['autocomplete' => 'off'],

        ]);
    
    }
}

