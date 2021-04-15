<?php

declare(strict_types=1);

namespace App\Form\Checkout;

use App\Model\CheckoutPaymentMethod;
use App\Entity\PaymentMethod;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentMethodType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //', ['id' => $builder->getData()->getId()]
        $builder->setAction($this->urlGenerator->generate('cart-setPaymentMethod'));
        $builder
            ->add('paymentMethod', EntityType::class, [
                'class' => PaymentMethod::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.ordering', 'ASC');
                },
                'choice_label' => 'name',
//                'choice_label' => function($v) {
//                    return $v->getName(). ' ' . $v->getDescription();
//                },
                'choice_value' => 'id',
//                'choice_name' =>  function($v) { return 'tralala'; },
                'multiple' => false,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CheckoutPaymentMethod::class
        ]);
    }
}