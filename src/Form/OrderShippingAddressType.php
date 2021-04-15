<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;
use App\Entity\Order;

use App\Form\DataTransformer\StringToNumberTransformer;
use App\Validator\Constraints\PhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class OrderShippingAddressType extends AbstractType
{
    private $urlGenerator;

    private $stringToNumberTransformer;

    public function __construct(UrlGeneratorInterface $urlGenerator, StringToNumberTransformer $stringToNumberTransformer)
    {
        $this->urlGenerator = $urlGenerator;
        $this->stringToNumberTransformer = $stringToNumberTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$builder->getEmptyData()) {
            $builder->setAction($this->urlGenerator->generate('order-editShippingInfo', ['id' => $builder->getData()->getId()]));
        } else {
            $builder->setAction($this->urlGenerator->generate('order-editShippingInfo', ['id' => $builder->getData()->getId()]));
        }
        $builder->add('id', HiddenType::class,[
                'mapped' => false, // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
            ]);
        $builder->add('shippingFirstname', TextType::class,[
            'label' => 'Címzett',
            'required' => true,
            'attr' => [
                'placeholder' => '',
                'autocomplete' => 'firstname'
            ]
        ]);
        $builder->add('shippingLastname', TextType::class,[
            'label' => 'Címzett',
            'required' => true,
            'attr' => [
                'placeholder' => '',
                'autocomplete' => 'lastname'
            ]
        ]);
        $builder->add('shippingAddress',OrderAddressType::class,[
            'label' => false,
            'addressType' => Address::DELIVERY_ADDRESS,  // this option is defined in AddressType, so that it can receive a value
        ]);
        $builder->add('shippingPhone',TelType::class,[
                'label' => 'Telefonszám',
                'required' => false,
                'constraints' => [
                    new PhoneNumber(['regionCode' => 'HU']),
                ],
            ]);
        $builder->add('customer',HiddenType::class,[
                'mapped' => false,
            ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'attr' => [
                'novalidate' => 'novalidate',
//                'autocomplete' => 'pleasedont',
                'data-autocomplete-url' => $this->urlGenerator->generate('cart-search-api'),
            ],
        ]);
    }

}