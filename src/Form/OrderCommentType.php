<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Order;
use App\Entity\OrderLog;
use App\Entity\OrderLogChannel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class OrderCommentType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('order-editComment', ['orderId' => $builder->getData()->getOrder()->getId()]));
        $builder->add('id',HiddenType::class,[
            "mapped" => false,
        ]);
        $builder->add('message',TextareaType::class,[
            'attr' => ['rows' => '1'],
            'required' => false,
//            'constraints' => [
//                new NotBlank(['message' => 'A komment üres!']),
//            ],
        ]);
        $builder->add('order',EntityType::class,[
            'class' => Order::class,
            'choice_label' => 'number',
            'required' => false,
        ]);
        $builder->add('channel',EntityType::class,[
            'class' => OrderLogChannel::class,
            'choice_label' => 'name',
            'required' => false,
            'mapped' => true,
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderLog::class,
            // Az OrderLogban ez van a message field-en:
            // @Assert\NotBlank(message="A komment üres!", groups={"hasznald_ezt_a_formban"})
            'validation_groups' => ['hasznald_ezt_a_formban'],
        ]);
    }


}