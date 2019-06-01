<?php

namespace App\Form;

use App\Entity\Product\Product;
use App\Entity\Product\ProductAttribute;
use App\Entity\Product\ProductKind;
use App\Entity\Product\ProductStatus;
use App\Entity\Product\ProductCategory;

use App\Form\DataTransformer\NumberToPriceTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class ProductFormType extends AbstractType
{
    private $em;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var NumberToPriceTransformer
     */
    private $numberToPriceTransformer;
    
    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator,
                                NumberToPriceTransformer $numberToPriceTransformer, \Twig_Environment $twig)
    {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->numberToPriceTransformer = $numberToPriceTransformer;
        $this->twig = $twig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Product|null $product */
        $product = $options['data'] ?? null;
        $isEdit = $product && $product->getId();

        $builder->add('name', null,[
            'label' => 'Termék neve',
        ]);
        $builder->add('sku', TextType::class,[
            'label' => 'SKU',
            'attr' => ['placeholder' => 'Pl: DF100172'],
        ]);
        $builder->add('description', null,[
            'label' => 'Rövid ismertető',
            'attr' => ['rows' => '5']
        ]);
        $builder->add('categories', EntityType::class, [
            'class' => ProductCategory::class,
//            'choices' => 'name',
            'label' => 'Kategóriák',
            'placeholder' => 'Válassz valamit...',
            'attr' => ['class' => 'custom-select'],
            'multiple' => true,
            'expanded' => false,   // it makes it a multioption select element
        ]);
        
        $builder->add('kind', EntityType::class, [
            'class' => ProductKind::class,
            'choice_label' => 'name',
            'label' => 'Típusa',
            'multiple' => false,
            'expanded' => true,
        ]);
//        $builder->add('prices', null, [
//            'label' => 'Árak',
//            'mapped' => false,
//        ]);
//        $builder->add('price', ProductPriceType::class,[
//            'label' => 'Ár',
//            'mapped' => false,
//        ]);
            
        $builder->add('status', EntityType::class, [
            'class' => ProductStatus::class,
//                'choice_label' => 'name',
            'choice_label' => function($status, $key, $value) {
                    return $this->twig->render('admin/choiceLabel-statusWithIcon.html.twig', [
                        'icon' => $status->getIcon(),
                        'name' => $status->getName(),
                    ]);
                },
            'label' => 'Állapot',
            'multiple' => false,
            'expanded' => true,
        ]);
        $builder->add('stock', IntegerType::class,[
            'label' => "Készlet a raktáron",
            'attr' => ['placeholder' => ''],
        ]);
        
        
//            ->add('grossPrice', IntegerType::class,[
//                'label' => 'Termék ár',
//                'attr' => ['placeholder' => ''],
//            ]);

//            ->add('category', EntityType::class, [
//                'class' => ProductCategory::class,
//                'choice_label' => 'name',
//                'label' => 'Kategóriák',
//                'placeholder' => 'Válassz valamit...',
//                'attr' => ['class' => 'custom-select'],
//            ])
 
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'allow_extra_fields' => true,
        ]);
    }
}

