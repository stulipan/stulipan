<?php

namespace App\Form;

use App\Entity\InventoryCategory;
use App\Entity\InventoryProduct;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class InventoryProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('productName', TextType::class, [
							'label' => 'Megnevezés',
							'attr' => ['placeholder' => 'Pl: Rózsa, vörös'],
            ])
            ->add('category', EntityType::class, [
                    'class' => InventoryCategory::class,
                    'choice_label' => 'categoryName',
                    'label' => 'Kategóriák',
                    'placeholder' => 'Válassz valamit...',
                    'attr' => ['class' => 'custom-select'],
            ])
//            ->add('category', null, [
////                'class' => InventoryCategory::class,
//                'choice_label' => 'categoryName',
//                'label' => 'Kategóriák',
//                'placeholder' => 'Válassz valamit...',
//                'attr' => ['class' => 'custom-select'],
//            ])
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
						'data_class' => InventoryProduct::class
        ]);
    
    }

//    public function getBlockPrefix()
//    {
//        return 'product';
//    }
    
}

