<?php

namespace App\Form;

use App\Entity\Price;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ProductPriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('grossPrice', IntegerType::class,[
            'label' => 'Termék ár',
            'attr' => ['placeholder' => ''],
//            'constraints' => [
//                new NotBlank(),
//                new Range(['min' => 0.0001, 'minMessage' => 'Hibás összeg: nulla vagy negatív.'])
//            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Price::class,
        ]);
    }
}