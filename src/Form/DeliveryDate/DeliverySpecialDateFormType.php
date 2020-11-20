<?php

declare(strict_types=1);

namespace App\Form\DeliveryDate;

use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class DeliverySpecialDateFormType extends AbstractType
{
    private $urlGenerator;
    private $em;
    private $twig;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em, Environment $twig)
    {
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('specialDate', DateType::class, [
                'label' => 'Dátum',
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'ÉÉÉÉ-HH-NN', 'autocomplete' => 'off'],
                'html5' => false,
            ])
            ->add('dateType', EntityType::class, [
                'class' => DeliveryDateType::class,
//                'choice_label' => 'name',
                'choice_label' => function (DeliveryDateType $type) {
                    return $this->twig->render('admin/occasion-choice-widget.html.twig', [
                        'type' => $type,
                    ]);
                },
                'label' => 'Típus',
                'multiple' => false,
                'expanded' => true,
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeliverySpecialDate::class,
            'attr' => ['autocomplete' => 'off']
        ]);
    }


}