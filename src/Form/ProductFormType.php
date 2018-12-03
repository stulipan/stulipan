<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductKind;
use App\Entity\Status;
use App\Entity\Category;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
        $builder
            ->add('productName', null,[
                'label' => 'Termék neve',
            ])
            ->add('sku', TextType::class,[
                'label' => 'SKU',
                'attr' => array('placeholder' => 'Pl: DF100172'),
            ])
            ->add('description', null,[
                'label' => 'Rövid ismertető',
                'attr' => ['rows' => '5']
            ])
            ->add('status', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'statusName',
                'label' => 'Állapot',
                'placeholder' => 'Válassz...',
                'attr' => ['class' => 'custom-select'],
            ])
            ->add('grossPrice', IntegerType::class,[
                'label' => 'Termék ár',
                'attr' => array('placeholder' => ''),
            ])
            ->add('stock', IntegerType::class,[
                'label' => "Készlet a raktáron",
                'attr' => array('placeholder' => ''),
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'categoryName',
                'label' => 'Kategóriák',
                'placeholder' => 'Válassz valamit...',
                'attr' => ['class' => 'custom-select'],
            ])
            ->add('kind', EntityType::class, [
                'class' => ProductKind::class,
                'choice_label' => 'name',
                'label' => 'Terméktípus',
                'multiple' => false,
                'expanded' => true,
//                'placeholder' => 'Válassz típust...',
//                'attr' => ['class' => 'custom-select'],
            ])
            ->add('subproducts', CollectionType::class,[
                'entry_type' => SubproductType::class,
                /**
                 * Az entry_options-ben megadott opciokat (productKind) tovabbitja az child formba
                 * A productKind opciot definialni kell a gyerek formban, hogy tudja fogadni
                 */
                'entry_options' => [
                    'productKind' => $builder->getData()->getKind(),
                ],
                'label' => 'Altermékek',
            ])
            ->add('image', FileType::class, [
                'label' => 'Képfeltöltés...',
                'attr' => ['class' => 'custom-file-input',],
                'data_class' => null,
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

}

