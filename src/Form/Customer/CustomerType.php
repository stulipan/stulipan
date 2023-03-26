<?php

declare(strict_types=1);

namespace App\Form\Customer;

use App\Entity\Customer;
use App\Services\StoreSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CustomerType extends AbstractType
{
    private $urlGenerator;
    private $authorization;
    private $storeSettings;

    public function __construct(UrlGeneratorInterface $urlGenerator, AuthorizationCheckerInterface $authorization, StoreSettings $storeSettings)
    {
        $this->urlGenerator = $urlGenerator;
        $this->authorization = $authorization;
        $this->storeSettings = $storeSettings;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $urlName = $options['urlName'] ?? 'cart-setCustomer';
        $emailAttr = [
            'placeholder' => '',
            'autocomplete' => 'email',
        ];
        if ($this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            $emailAttr['readonly'] = 'readonly';
        }

        if (!$builder->getEmptyData()) {
            $builder->setAction($this->urlGenerator->generate($urlName, ['id' => $builder->getData()->getId()]));
        } else {
            $builder->setAction($this->urlGenerator->generate($urlName));
        }
        $builder
            ->add('email', TextType::class,[
                'required' => true,
                'attr' => $emailAttr,
            ]);

        $builder->add('lastname', TextType::class,[
//            'mapped' => false,
            'required' => true,
            'attr' => [
                'placeholder' => '',
                'autocomplete' => 'lastname'
            ],
        ])
        ->add('firstname', TextType::class,[
//            'mapped' => false,
            'required' => true,
            'attr' => [
                'placeholder' => '',
                'autocomplete' => 'firstname'
            ],
        ])
        ->add('phone',TelType::class,[
//            'mapped' => false,
            'required' => false,
//                'constraints' => [
//                    new NotNull(['message' => "Add meg a telefonszámot!"]),
//                    new PhoneNumber(['regionCode' => 'HU']),
//                ],
        ])
        ->add('acceptsMarketing', CheckboxType::class, [
            'required' => false,
            'mapped' => true,
//                'constraints' => new IsTrue(['message' => 'Biztosan nem akarsz feliratkozni a hírlevélre?']),
        ])
        ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
            'attr' => ['novalidate' => 'novalidate'],
            'urlName' => 'cart-setCustomer',
        ]);
    }
}