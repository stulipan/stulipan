<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Entity\Subproduct;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;

class SubproductType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $label = $builder->getOption('productKind')->getAttributes()[$builder->getName()];
        $builder
            ->add('price', IntegerType::class,[
                'label' => $label.' ára:',
                'attr' => array('placeholder' => ''),
            ])
            ->getForm();

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Subproduct::class,
            'productKind' => '',
            'inherit_data' => true,
        ]);
    }
}

