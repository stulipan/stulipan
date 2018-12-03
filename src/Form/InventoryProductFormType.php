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

//        $builder->createNamed('name','valami');
//        dump($builder->getAttributes());die;
        $builder
            ->add('productName', TextType::class, [
							'label' => 'Megnevezés',
							'attr' => ['placeholder' => 'Pl: Rózsa, vörös'],
            ])
            /**
             * elvileg ez radiobutton kéne legyen, de nem jön össze valamiért...
             */
            ->add('category', EntityType::class, [
                'class' => InventoryCategory::class,
//                'choice_label' => 'categoryName',
                'choice_label' => function ($category) {
                    return $category->getCategoryName();
                },
                'multiple' => false,
                'expanded' => true,
                'label' => 'Termék típusa',
            ])
            /**
             * ez egy dropdown
             */
//            ->add('category', EntityType::class, [
//                'class' => InventoryCategory::class,
//                'multiple' => false,
//                'label' => 'Termék típusa',
//                'placeholder' => 'Válassz valamit...',
//                'attr' => ['class' => 'custom-select'],
//            ])
            /**
             * ez egy VisualPicker
             * a product_edit.html.twig -ben a _product_form_withVisualPicker.html.twig -et kell használni
             */
//            ->add('category', EntityType::class, [
//                'class' => InventoryCategory::class,
//                'expanded' => true,
//                'multiple' => false,
//                'label' => 'Termék típusa',
//            ])


//            ->add('category', EntityType::class, [
//                    'class' => InventoryCategory::class,
//                    'choice_label' => 'categoryName',
//                    'label' => 'Kategóriák',
//                    'placeholder' => 'Válassz valamit...',
//                    'attr' => ['class' => 'custom-select'],
//            ])
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
            'data_class' => InventoryProduct::class,
        ]);
    
    }

//    /**
//     * Ez lesz a form neve
//     *
//     */
//    public function getBlockPrefix()
//    {
//        return 'valami';
//    }

    public function getBlockPrefix()
    {
        return '';
    }
}

