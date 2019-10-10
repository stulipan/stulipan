<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Payment;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * PaymentType is used in CheckoutFormType which is the Checkout page
 */
class PaymentType extends AbstractType
{

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder->setAction($this->urlGenerator->generate('cart-setPayment', ['id' => $builder->getData()->getId()]));
        $builder
            ->add('payment', EntityType::class, [
                'class' => Payment::class,
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