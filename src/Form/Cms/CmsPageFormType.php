<?php

namespace App\Form\Cms;

use App\Entity\CmsPage;

use App\Entity\ImageEntity;
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

class CmsPageFormType extends AbstractType
{
    private $em;
    
    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CmsPage|null $page */
        $page = $options['data'] ?? null;
        $isEdit = $page && $page->getId();
    
        $queryBuilder = $this->em->getRepository(CmsPage::class)->createQueryBuilder('p');
        
        if ($isEdit) {
            $queryBuilder
                ->andWhere('p.id <> :pId')
                ->setParameter('pId', $page->getId());
        }
        $queryBuilder->orderBy('p.name', 'ASC');
        
        $builder
            ->add('id',HiddenType::class,[
            
            ])
            ->add('name', null,[
                'label' => 'Oldal neve',
            ])
            ->add('parent', EntityType::class,[
                'label' => 'Szülőoldal',
                'class' => CmsPage::class,
                'query_builder' => $queryBuilder, // from above, where I filtered out the current page, which can't be its own parent page
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => ' - nincs - ',
            ])

            ->add('enabled', CheckboxType::class, [
                'label' => 'Engedélyezve',
            ])
            ->add('slug', TextType::class,[
                'label' => 'Slug',
            ])
            ->add('content', TextareaType::class,[
                'label' => 'Leírás',
                'attr' => ['rows' => '5']
            ]);
        $imageConstraints = [
            new Image([
                'maxSize' => '2M',
            ])
        ];
        if (!$isEdit || !$page->getImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Töltsél fel egy képet.',
            ]);
        }
//        EZ MAR NINCS HASZNALATBAN
//        Akkor hasznaltam, amikor nem VUE feltoltes volt
//        $builder->add('imageFile', ImageType::class, [
//            'mapped' => false,
//            'required' => false,
//            'constraints' => $imageConstraints,
//            'attr' => [
//                'placeholder' => 'Képfeltöltés...',
//            ]
//        ]);
        $builder->add('imageId', HiddenType::class, [
            'mapped' => false,
            'required' => false,
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsPage::class,
        ]);
    }
}

