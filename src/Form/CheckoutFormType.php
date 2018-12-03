<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Order;
use App\Entity\Shipping;
use App\Form\ShippingType;
use App\Form\PaymentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Validator\Constraints\NotBlank;



class CheckoutFormType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('cart-setCheckout'));
        $builder
            ->add('id',HiddenType::class, [
                'mapped' => false // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
            ])
            ->add('shipping',ShippingType::class,[
                'required' => true,
                'constraints' => new NotBlank(),
            ])
            ->add('payment',PaymentType::class,[
                'required' => true,
                'constraints' => [ new NotBlank(),],
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class
        ]);
    }

}