<?php

namespace App\Form;

use App\Entity\nincs;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;

class SubproductSelectedType extends AbstractType
{
    ////////////////////////////////////
    /// NINCS HASZNALVA -- Ez példa az $options használatára /////////////////
    ////////////////////////////////////

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('selectedSubproduct', EntityType::class,[
            'class' => nincs::class,
            'choice_label' => 'name',
            'multiple' => false,
            'expanded' => true,
//            'choices' => $this->em->getRepository(Subproduct::class)->findBy(['product' => $builder->getData()->getId()]),
            'choices' => $options['product']->getValues(),
        ]);
        $builder->add('quantity',IntegerType::class, [
            'mapped' => false,
            'attr' => ['value' => '1'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => nincs::class,
        ]);
    }
}

