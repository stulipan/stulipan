<?php

namespace App\Form;

use App\Entity\Product\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductQuantityFormType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('product-edit-stock',['id' => $builder->getData()->getId()]));
        $builder
            ->add('stock', IntegerType::class,[
                    'label' => "Készlet a raktáron",
                    'attr' => ['placeholder' => '', 'class' => 'form-control form-control-sm w-50 p-0', 'autocomplete' => 'off'],
                ])
            ->getForm();

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'attr' => ['novalidate' => ''],
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

}

