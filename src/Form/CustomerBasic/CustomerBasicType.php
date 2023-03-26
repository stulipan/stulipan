<?php

declare(strict_types=1);

namespace App\Form\CustomerBasic;

use App\Entity\Model\CustomerBasic;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerBasicType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('cart-setCustomer'));
        $builder
            ->add('email', TextType::class,[
                'required' => true,
                'attr' => [
                    'placeholder' => '',
                    'autocomplete' => 'email'
                ],
            ])
//            ->add('lastname', TextType::class,[
//                'required' => true,
//                'attr' => [
//                    'placeholder' => '',
//                    'autocomplete' => 'lastname'
//                ],
//            ])
//            ->add('firstname', TextType::class,[
//                'required' => true,
//                'attr' => [
//                    'placeholder' => '',
//                    'autocomplete' => 'firstname'
//                ],
//            ])
//            ->add('phone',TelType::class,[
//                'required' => false,
//            ])
            ->add('acceptsMarketing', CheckboxType::class, [
                'required' => false,
                'mapped' => true,
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomerBasic::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}