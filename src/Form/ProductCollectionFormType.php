<?php

namespace App\Form;

use App\Entity\Product\ProductCategory;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class ProductCollectionFormType extends AbstractType
{
    private $em;
    
    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ProductCategory|null $category */
        $category = $options['data'] ?? null;
        $isEdit = $category && $category->getId();
    
        $queryBuilder = $this->em->getRepository(ProductCategory::class)->createQueryBuilder('p');
        
        if ($isEdit) {
            $queryBuilder
                ->andWhere('p.id <> :catId')
                ->setParameter('catId', $category->getId());
        }
        $queryBuilder->orderBy('p.name', 'ASC');
        
        $builder
            ->add('id',HiddenType::class,[
            
            ])
            ->add('name', null,[
                'label' => 'Kategória',
            ])
            ->add('parent', EntityType::class,[
                'label' => 'Szülőkategória',
                'class' => ProductCategory::class,
                'query_builder' => $queryBuilder, // from above, where I filtered out the current category, which can't be its own parentCategory
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => ' - nincs - ',
            ])

            ->add('enabled', CheckboxType::class, [
                'label' => 'Engedélyezve',
            ])
            ->add('slug', TextType::class,[
                'label' => 'URL slug',
            ])
            ->add('description', TextareaType::class,[
                'label' => 'Leírás',
                'attr' => ['rows' => '5']
            ])
            ->add('seoContent', TextareaType::class,[
                'attr' => ['rows' => '5']
            ]);
        $imageConstraints = [
            new Image([
                'maxSize' => '2M',
            ])
        ];
        if (!$isEdit || !$category->getImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Töltsél fel egy képet.',
            ]);
        }
//        $builder->add('imageFile', FileType::class, [
//            'label' => 'Képfeltöltés...',
//            'data_class' => null,
//            'mapped' => false,
//            'required' => false,
//            'constraints' => $imageConstraints,
//            'attr' => [
//                'placeholder' => 'Képfeltöltés...',
//            ],
//        ]);

//        $builder->add('image', TextType::class, [
//            'mapped' => false,
//        ]);
        $builder->add('image', TextType::class, [
            'mapped' => false,
            'required' => false,
//            'data' => $options['data']->getImagePath(),
        ]);
        $builder->add('imageId', HiddenType::class, [
            'mapped' => false,
            'required' => false,
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductCategory::class,
        ]);
    }
}

