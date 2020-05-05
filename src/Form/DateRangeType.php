<?php

namespace App\Form;

use App\Entity\DateRange;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Form\Extension\Core\Type\DateType;

class DateRangeType extends AbstractType
{

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('daterange-widget'));
        $builder
            ->add('dateRange', TextType::class, [
                    'label' => 'Dátum',
                    'attr' => ['placeholder' => 'Szűrés időre...', 'autocomplete' => 'off'],
                ]
            )
            //            ->add('start', DateType::class, [
//                'label' => 'Dátum',
//                'widget' => 'single_text',
//                'attr' => ['placeholder' => 'éééé-hh-nn', 'autocomplete' => 'off'],
//                'html5' => false,
//                ]
//            )
//            ->add('end', DateType::class, [
//                    'label' => 'Dátum',
//                    'widget' => 'single_text',
//                    'attr' => ['placeholder' => 'éééé-hh-nn', 'autocomplete' => 'off'],
//                    'html5' => false,
//                ]
//            )
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
//						'data_class' => DateRange::class
        ]);
    
    }
    
}

