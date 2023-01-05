<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;
use App\Entity\Sender;
use App\Form\AddressType;

use App\Validator\Constraints\PhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class SenderType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$builder->getEmptyData()) {
            $builder->setAction($this->urlGenerator->generate('cart-editSender', ['id' => $builder->getData()->getId()]));
        } else {
            $builder->setAction($this->urlGenerator->generate('cart-editSender', ['id' => $builder->getData()->getId()]));
        }
        $builder
            ->add('id',HiddenType::class, [
                'mapped' => false,  // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
            ])
            ->add('firstname',TextType::class, [
                'required' => true,
                'attr' => ['autocomplete' => 'given-name']
            ])
            ->add('lastname',TextType::class, [
                'required' => true,
                'attr' => ['autocomplete' => 'family-name']
            ])
            ->add('company',TextType::class, [
                'required' => false,
                'attr' => ['autocomplete' => 'organization'],
            ])
            ->add('companyVatNumber',TextType::class, [
                'required' => false,
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('address',AddressType::class, [
                'label' => false,
                'addressType' => Address::BILLING_ADDRESS,  // this option is defined in AddressType, so that it can receive a value
            ]);
            $builder->add('phone',TelType::class,[
                'required' => false,
                'constraints' => [
                    new PhoneNumber(['regionCode' => 'HU', 'message' => 'checkout.sender.invalid-phone']),
                ],
                'attr' => ['autocomplete' => 'tel']
            ]);
            $builder->add('customer',HiddenType::class, [
                'mapped' => false,
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sender::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}