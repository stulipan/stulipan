<?php

declare(strict_types=1);

namespace App\Form\AddToCart;

use App\Form\DeliveryDate\CartSelectDeliveryDateFormType;
use App\Model\AddToCartModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
        $product = $builder->getOption('product');
        $builder->setAction($this->urlGenerator->generate('cart-addItem'));

        $builder->add('productId',HiddenType::class,[
            "mapped" => true,
        ]);
        // EZ OKOZTA A "Error: Allowed memory size of... exhausted"
//        $builder->add('whereTo',CartSelectWhereToType::class,[
//            'required' => false,
//            'mapped' => false,
//        ]);
//        $builder->add('options',CollectionType::class,[
//            'entry_type' => ProductOptionFormType::class,
//            /**
//             * Az entry_options-ben megadott opciokat tovabbitja a child formba
//             * A productKind opciot definialni kell a gyerek formban, hogy tudja fogadni
//             */
//            'entry_options' => [
//                'label' => false,
//                'product' => $builder->getData(),
////                'data_class' => ProductOptionValue::class,
//            ],
//            'label' => 'Termékvariációk',
//            'allow_add' => true,
//            'allow_delete' => true,
//            'by_reference' => false,
//            'constraints' => [new Valid()],
//            'mapped' => false,
//        ]);
//        dd($builder->getData());
        $builder->add('options', CartAddItemProductOptionType::class, [
            'required' => false,
            'mapped' => false,
            'product' => $product,
        ]);
        $builder->add('deliveryDate', CartSelectDeliveryDateFormType::class, [
           'required' => false,
            'mapped' => true,
        ]);

        // If product is flower, then set the 'quantity' input field as hidden.
        $isFlower = $product->isFlower();
        $builder->add('quantity', $isFlower ? HiddenType::class : IntegerType::class, [
            'mapped' => true,
            'attr' => ['value' => '1'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AddToCartModel::class,
            'options' => '',
            'product' => '',
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }

}