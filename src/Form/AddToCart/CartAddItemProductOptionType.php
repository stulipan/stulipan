<?php

declare(strict_types=1);

namespace App\Form\AddToCart;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;


class CartAddItemProductOptionType extends AbstractType
{
    private $urlGenerator;
    private $twig;

    public function __construct(UrlGeneratorInterface $urlGenerator, Environment $twig)
    {
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $product = $builder->getOption('product');

        foreach ($product->getOptions() as $i => $productOption) {
            $builder->add('option_'.$i,CartAddItemProductOptionValueType::class,[
                // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
                'required' => false,
                'mapped' => false,
                'productOption' => $productOption,
            ]);
        }
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'product' => '',
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}