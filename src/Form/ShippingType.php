<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Shipping;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * ShippingType is used in CheckoutFormType which is the Checkout page
 */
class ShippingType extends AbstractType
{

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder->setAction($this->urlGenerator->generate('cart-setShipping', ['id' => $builder->getData()->getId()]));
        $builder
            ->add('shipping', EntityType::class, [
                'class' => Shipping::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.ordering', 'ASC');
                },
                'choice_label' => 'name',
                //'choice_label' => function($v) { return $v->getName(); },
                'choice_value' => 'id',
                'multiple' => false,
                'expanded' => true,
            ]);
//        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }

//    public function getBlockPrefix()
//    {
//        return '';
//    }

}