<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Status;
use App\Entity\Category;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

class ProductFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // createFormBuilder is a shortcut to get the "form factory"
        // and then call "createBuilder()" on it

        $builder
            ->add('productName', null,
                array(
                    'label' => 'Termék neve',
                )
            )
            ->add('sku', TextType::class,
                array(
                    'label' => 'SKU',
                    'attr' => array('placeholder' => 'Pl: DF100172'),
                    //'constraints' => array( new NotNull(array('message' => "Please provide Kassza")), )
                )
            )
            ->add('description', null,
                array(
                    'label' => 'Rövid ismertető',
                    'attr' => ['rows' => '5']
                )
            )
            ->add('status', EntityType::class, [
                    'class' => Status::class,
                    'choice_label' => 'statusName',
                    'label' => 'Állapot',
                    'placeholder' => 'Válassz...',
                    'attr' => ['class' => 'custom-select'],
                    //'constraints' => [ new NotNull(array('message' => "Válaszd ki magad!")), ]
                ]

            )
            ->add('grossPrice', IntegerType::class,
                array(
                    'label' => 'Termék ár',
                    'attr' => array('placeholder' => ''),
                    //'constraints' => array( new NotNull(array('message' => "Please provide Kassza")), )
                )
            )
            ->add('stock', IntegerType::class,
                array(
                    'label' => "Készlet a raktáron",
                    'attr' => array('placeholder' => ''),
                    //'constraints' => array( new NotNull(array('message' => "Please provide Name")), )
                )
            )
            ->add('category', EntityType::class, [
                    'class' => Category::class,
                    'choice_label' => 'categoryName',
                    'label' => 'Kategóriák',
                    'placeholder' => 'Válassz valamit...',
                    'attr' => ['class' => 'custom-select'],
                ]
            )
            ->add('image', FileType::class, [
                    'label' => 'Képfeltöltés...',
                    'attr' => ['class' => 'custom-file-input',],
                    'data_class' => null,
                ]
            )
            ->getForm();

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class
            //vagy ez ugyanaz: 'data_class' => 'App\Entity\Product'
        ]);

    }

}

