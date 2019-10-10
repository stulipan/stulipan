<?php

namespace App\Form;

use App\Entity\Product\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SubproductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('attributeName',TextType::class,[
            'label' => false,
            'disabled' => 'true',
            'required' => false,
        ]);
        $builder->add('price', SubproductPriceType::class,[
            'label' => 'Ár',
            'required' => false,
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}

