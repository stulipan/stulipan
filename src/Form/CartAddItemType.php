<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Product\Product;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\CartSelectWhereToType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CartAddItemType extends AbstractType
{

    private $urlGenerator;
    private $em;
    private $twig;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em, \Twig_Environment $twig)
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
        $this->twig = $twig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('cart-addItem', ['id' => $builder->getData()->getId()]));

        $builder->add('id',HiddenType::class,[
            "mapped" => false,
        ]);
        $builder->add('whereTo',CartSelectWhereToType::class,[
            'required' => false,
            'mapped' => false,
        ]);
        $builder->add('deliveryDate', CartSelectDeliveryDateFormType::class, [
           'required' => false,
            'mapped' => false,
        ]);
        $builder->add('quantity',IntegerType::class, [
            'mapped' => false,
            'attr' => ['value' => '1'],
        ]);
        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onPostSetData']);
    }

    /**
     * Ezt azért kell, mert vannak termékek, amikhez tartoznak altermékek.
     * Az altermékekhez pedig külön mezők tartoznak.
     */
    function onPostSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $product = $event->getData();
        if ($product->hasSubproducts()) {
            $this->addElements($form);
        } else {
            return;
        }
    }


    protected function addElements(FormInterface $form)
    {
        $product = $form->getData();
        if ($product->hasSubproducts()) {
            $form->add('selectedSubproduct', EntityType::class,[
                'class' => Product::class,
//                'choice_label' => 'name',
                'choice_label' => function (Product $subproduct) {
                    return $this->twig->render('webshop/site/choiceItem-subproduct.html.twig', [
                        'name' => $subproduct->getAttribute(),
                        'price' => $subproduct->getPrice(),
                    ]);
                },
                'multiple' => false,
                'expanded' => true,
//                Ez nem jo, mert nem rakja sorrendbe a Subproductokat !!! Ne hasznald !!!
//                'choices' => $this->em->getRepository(Subproduct::class)->findBy(['product' => $product->getId()]),
                'choices' => $form->getConfig()->getOption('subproducts')->getValues(),
                'data' => $this->em->getReference("App:Subproduct", $form->getConfig()->getOption('subproducts')->get('1')->getId()),
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
//            'data_class' => Product::class,
            'subproducts' => '',
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }

}