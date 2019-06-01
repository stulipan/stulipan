<?php

namespace App\Form;

use App\Entity\nincs;
use App\Entity\Price;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\Range;

class SubproductPriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('grossPrice', IntegerType::class,[
            'label' => 'Termék ár (price)',
            'attr' => ['placeholder' => ''],
             'constraints' => [
                new Range(['min' => 0.0001, 'minMessage' => 'Az összeg nem lehet nulla vagy negatív.'])
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Price::class,
        ]);
    }
}