<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Model\MessageAndCustomer;
use App\Validator\Constraints\MessageWithAuthor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class MessageAndCustomerFormType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('cart-setMessageAndCustomer'));
        $builder
            ->add('card',MessageType::class,[
                'required' => false,
            ])
            ->add('customer',CustomerBasicsFormType::class,[
//                'label' => 'Aláírásnév (ezt írjuk az üdvözlőlapra)))',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessageAndCustomer::class,

        ]);
    }


}