<?php

declare(strict_types=1);

namespace App\Form\DeliveryDate;

use App\Entity\Model\HiddenDeliveryDate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CartHiddenDeliveryDateFormType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('cart-setDeliveryDate'));
        $builder->add('deliveryDate', TextType::class, [
                'mapped' => true,
                'required' => true,
                'label' => '',
            ]);
        $builder->add('deliveryInterval', TextType::class, [
            'mapped' => true,
            'required' => true,
            'label' => '',
        ]);
        $builder->add('deliveryFee', NumberType::class, [
            'mapped' => true,
            'required' => true,
            'label' => '',
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HiddenDeliveryDate::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'hidden';
    }
}