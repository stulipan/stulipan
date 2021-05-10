<?php

declare(strict_types=1);

namespace App\Form\Checkout;

use App\Model\CheckoutShippingMethod;
use App\Entity\ShippingMethod;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ShippingMethodType extends AbstractType
{
    private $urlGenerator;
    private $em;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em)
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('cart-setShippingMethod'));

//        // This is how to pre-check the radio, if there's only one option (ShippingMethod) available
//        $shippingMethods = $this->em->getRepository(ShippingMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']);
//        $count = count($shippingMethods);
//        $shippingMethodOptions = [
//            'class' => ShippingMethod::class,
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('s')
//                    ->andWhere('s.enabled = true')
//                    ->orderBy('s.ordering', 'ASC');
//            },
//            'choice_label' => 'name',
//            'choice_value' => 'id',
//            'multiple' => false,
//            'expanded' => true,
//        ];
//        if ($count == 1) {
//            $shippingMethodOptions['data'] = $shippingMethods[0];
//        }

        $builder
            ->add('shippingMethod', EntityType::class, [
                'class' => ShippingMethod::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->andWhere('s.enabled = true')
                        ->orderBy('s.ordering', 'ASC');
                },
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => false,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CheckoutShippingMethod::class
        ]);
    }
}