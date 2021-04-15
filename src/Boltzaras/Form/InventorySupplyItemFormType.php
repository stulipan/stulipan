<?php

namespace App\Boltzaras\Form;

use App\Entity\Boltzaras\InventorySupplyItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use App\Boltzaras\Form\InventoryProductFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventorySupplyItemFormType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', InventoryProductFormType::class, [
                'label' => false,
                'disabled' => 'true',
                'required' => false,
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Mennyiség',
                'attr' => ['placeholder' => ''],
                'required' => false,
            ])
            ->add('cog', NumberType::class, [
                'label' => 'Beszer. ár',
                'attr' => ['placeholder' => ''],
                'required' => false,
            ])
            ->add('markup', NumberType::class, [
                'label' => 'Szorzó',
                'attr' => ['placeholder' => ''],
                'required' => false,
            ])
//            ->add('markup', NumberType::class, [
//                'label' => 'Szorzó',
//                'attr' => ['placeholder' => ''],
//                'required' => false,
//            ])
            ->add('afterMarkup', NumberType::class, [
                'label' => 'Szorzó után',
                'attr' => ['placeholder' => ''],
                'required' => false,
                'mapped' => false,
            ])
            ->add('retailPrice', NumberType::class, [
                'label' => 'Eladási ár',
                'attr' => ['placeholder' => ''],
//                'required' => false,
            ])
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InventorySupplyItem::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

}

