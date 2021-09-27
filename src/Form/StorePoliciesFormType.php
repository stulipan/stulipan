<?php

declare(strict_types=1);

namespace App\Form;

use App\Model\StorePolicies;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StorePoliciesFormType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('policy-edit'));
        $builder
            ->add('termsAndConditions',StorePolicyType::class,[
                'required' => false,
            ])
            ->add('privacyPolicy',StorePolicyType::class,[
                'required' => false,
            ])
            ->add('shippingInformation',StorePolicyType::class,[
                'required' => false,
            ])
            ->add('returnPolicy',StorePolicyType::class,[
                'required' => false,
            ])
            ->add('contactInformation',StorePolicyType::class,[
                'required' => false,
            ])
            ->add('legalNotice',StorePolicyType::class,[
                'required' => false,
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StorePolicies::class,
        ]);
    }
}