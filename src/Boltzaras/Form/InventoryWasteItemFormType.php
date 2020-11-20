<?php

namespace App\Form\Inventory;

use App\Entity\Boltzaras\InventoryWasteItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryWasteItemFormType extends AbstractType
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
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
						'data_class' => InventoryWasteItem::class
        ]);
    
    }

}

