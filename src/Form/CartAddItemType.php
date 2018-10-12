<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CartAddItemType extends AbstractType
{

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('cart_add_item', ['id' => $builder->getData()->getId()]));
        $builder->add(
            'id',
            HiddenType::class,
            // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
            ["mapped" => false]
        );
        $builder->add(
            'quantity',
            IntegerType::class,
            // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
            [
                'mapped' => false,
                'attr' => ['value' => '1'],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class
        ]);
    }

}