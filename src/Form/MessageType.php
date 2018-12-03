<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Order;
use App\Form\AddressType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class MessageType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //dump($builder->getData()->getId()); die;
        $builder->setAction($this->urlGenerator->generate('cart-setMessage'));
        $builder
            ->add('message',TextareaType::class,[
                'label' => 'Ide írd az üzenetet (max 200 karakter) ***',
                'required' => false,
                'attr' => ['rows' => '5'],
            ])
            ->add('messageAuthor',TextType::class,[
                'label' => 'Aláírásnév (ezt írjuk az üdvözlőlapra)))',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class
        ]);
    }


}