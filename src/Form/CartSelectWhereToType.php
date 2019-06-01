<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Geo\GeoPlace;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class CartSelectWhereToType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //dump($builder->getData()->getId()); die;
//        $builder->setAction($this->urlGenerator->generate('cart-setMessage'));
        $builder
            ->add('whereTo', EntityType::class,[
                'class' => GeoPlace::class,
                'choice_label' => 'city',
                'query_builder' => function(EntityRepository $repo) {
                    return $repo->createQueryBuilder('g')
                        ->orderBy('g.city', 'ASC');
                    },
                'multiple' => false,
                'label' => 'Hova kéred',
                'placeholder' => 'Település: Budakalász, vagy Irányítószám: 2011',
            ])
//            ->add('whereTo',TextType::class,[
//                'label' => 'Hova kéred',
//                'required' => false,
//            ])
            ->getForm()
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
//            'data_class' => GeoNames::class
        ]);
    }


}