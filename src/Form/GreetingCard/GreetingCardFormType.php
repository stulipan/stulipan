<?php

declare(strict_types=1);

namespace App\Form\GreetingCard;

use App\Model\CartGreetingCard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class GreetingCardFormType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('cart-setMessage'));
        $builder
            ->add('message',TextareaType::class,[
                'label' => 'Ide írd az üzenetet...',
                'required' => false,
                'attr' => ['rows' => '5'],
// Ez akkor kell, ha form szintu validaciot akarok. Most a CartGreetingCard class-on van egy custom validator.
//                'constraints' => [
//                    new Callback([$this, 'validateMessage']),
//                ],
            ])
            ->add('author',TextType::class,[
                'label' => 'Kinek a részéről (neved)',
                'required' => false,
//                'constraints' => [
//                    new Callback([$this, 'validateMessageAuthor']),
//                ]
            ]);
    }

    public function validateMessage($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $data = $form->getData()->getCard();

        if($data) {
            if (!$value && $data->getAuthor()) {
                $context
                    ->buildViolation('Az üzenet lemaradt!')
                    ->addViolation();
            }
        }
    }

    public function validateMessageAuthor($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $data = $form->getData()->getCard();
        if ($data) {
            if (!$value && $data->getMessage()) {
                $context
                    ->buildViolation('Nem írtad alá az üzenetet')
                    ->addViolation();
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CartGreetingCard::class,
        ]);
    }
}