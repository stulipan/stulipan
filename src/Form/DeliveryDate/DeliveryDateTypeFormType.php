<?php

declare(strict_types=1);

namespace App\Form\DeliveryDate;

use App\Entity\DeliveryDateType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Valid;

class DeliveryDateTypeFormType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextType::class, [
                'label' => 'Idősávcsoport neve',
                'attr' => ['placeholder' => 'Csoport neve', 'rows' => 3],
            ])
            ->add('description',TextareaType::class, [
                'label' => 'Rövid leírás',
                'attr' => ['placeholder' => ''],
            ])
            ->add('intervals',CollectionType::class,[
                'entry_type' => DeliveryDateIntervalFormType::class,
                /**
                 * Az entry_options-ben megadott opciokat tovabbitja a child formba
                 * A productKind opciot definialni kell a gyerek formban, hogy tudja fogadni
                 */
                'entry_options' => [
                    'label' => false,
                ],
                'label' => 'Idősávok',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'constraints' => [new Valid()],
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeliveryDateType::class,
            'attr' => ['autocomplete' => 'off']
        ]);
    }


}