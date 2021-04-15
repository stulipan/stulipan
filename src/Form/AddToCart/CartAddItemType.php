<?php

declare(strict_types=1);

namespace App\Form\AddToCart;

use App\Entity\Product\Product;
use App\Entity\Product\ProductKind;
use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Form\DeliveryDate\CartSelectDeliveryDateFormType;
use App\Repository\ProductRepository;
use App\Services\StoreSettings;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\AddToCart\CartSelectWhereToType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Valid;
use Twig\Environment;

class CartAddItemType extends AbstractType
{

    private $urlGenerator;
    private $em;
    private $twig;
    private $settings;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em, Environment $twig,
                                StoreSettings $settings)
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
        $this->twig = $twig;
        $this->settings = $settings;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('cart-addItem', ['id' => $builder->getData()->getId()]));

        $builder->add('id',HiddenType::class,[
            "mapped" => false,
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
        $builder->add('options', CartAddItemProductOptionType::class, [
            'required' => false,
            'mapped' => false,
            'product' => $builder->getData(),
        ]);
        $builder->add('deliveryDate', CartSelectDeliveryDateFormType::class, [
           'required' => false,
            'mapped' => false,
        ]);

        // If product is flower, then set the 'quantity' input field as hidden.
        $isFlower = $builder->getData()->isFlower();
        $builder->add('quantity', $isFlower ? HiddenType::class : IntegerType::class, [
            'mapped' => false,
            'attr' => ['value' => '1'],
        ]);
//        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onPostSetData']);
    }
//
//    /**
//     * Ezt azért kell, mert egyes termékekhez tartoznak termékvariációk (pl: Szín, Méret).
//     * Egy termékvariációnak van altermékei, pl: Méret: M, L, XL.
//     */
//    function onPostSetData(FormEvent $event)
//    {
//        $form = $event->getForm();
//        $product = $event->getData();
////        dd($product->hasOptions());
//        if ($product->hasOptions()) {
//            $this->addElements($form);
//        } else {
//            return;
//        }
//    }
//
//    protected function addElements(FormInterface $form)
//    {
//        $product = $form->getData();
//        if ($product->hasOptions()) {
//            $form->add('options', CollectionType::class,[
//                'entry_type' => ProductOptionFormType::class,
//                /**
//                 * Az entry_options-ben megadott opciokat tovabbitja a child formba
//                 * A productKind opciot definialni kell a gyerek formban, hogy tudja fogadni
//                 */
//                'entry_options' => [
//                    'label' => false,
//                ],
//                'label' => 'Termékvariációk',
//                'allow_add' => true,
//                'allow_delete' => true,
//                'by_reference' => false,
//                'constraints' => [new Valid()],
//                'mapped' => false,
//            ]);
//        }
//    }

//    /**
//     * Ezt azért kell, mert vannak termékek, amikhez tartoznak altermékek.
//     * Az altermékekhez pedig külön mezők tartoznak.
//     */
//    function onPostSetData(FormEvent $event)
//    {
//        $form = $event->getForm();
//        $product = $event->getData();
//        if ($product->hasSubproducts()) {
//            $this->addElements($form);
//        } else {
//            return;
//        }
//    }
//
//
//    protected function addElements(FormInterface $form)
//    {
//        $product = $form->getData();
//        if ($product->hasSubproducts()) {
//            $form->add('selectedSubproduct', EntityType::class,[
//                'class' => Product::class,
////                'choice_label' => 'name',
//                'choice_label' => function (Product $subproduct) {
//                    return $this->twig->render('webshop/site/choiceItem-subproduct.html.twig', [
//                        'name' => $subproduct->getAttribute(),
//                        'price' => $subproduct->getPrice(),
//                    ]);
//                },
//                'multiple' => false,
//                'expanded' => true,
////                Ez nem jo, mert nem rakja sorrendbe a Subproductokat !!! Ne hasznald !!!
////                'choices' => $this->em->getRepository(Subproduct::class)->findBy(['product' => $product->getId()]),
//                'choices' => $form->getConfig()->getOption('subproducts')->getValues(),
//                'data' => $this->em->getReference("App:Subproduct", $form->getConfig()->getOption('subproducts')->get('1')->getId()),
//            ]);
//        }
//    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
//            'data_class' => Product::class,
//            'data_class' => null,
            'options' => '',
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }

}