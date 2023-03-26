<?php

namespace App\Form\Cms;

use App\Entity\CmsNavigation;

use App\Entity\ImageEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class CmsNavigationFormType extends AbstractType
{
    private $em;
    
    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CmsNavigation|null $navigation */
        $navigation = $options['data'] ?? null;
        $isEdit = $navigation && $navigation->getId();
    
        $queryBuilder = $this->em->getRepository(CmsNavigation::class)->createQueryBuilder('p');
        
        if ($isEdit) {
            $queryBuilder
                ->andWhere('p.id <> :pId')
                ->setParameter('pId', $navigation->getId());
        }
        $queryBuilder->orderBy('p.name', 'ASC');
        
        $builder
            ->add('id',HiddenType::class,[
            
            ])
            ->add('name', null,[
                'label' => 'Neve',
            ])
//            ->add('parent', EntityType::class,[
//                'label' => 'Szülőoldal',
//                'class' => CmsPage::class,
//                'query_builder' => $queryBuilder, // from above, where I filtered out the current page, which can't be its own parent page
//                'choice_label' => 'name',
//                'choice_value' => 'id',
//                'placeholder' => ' - nincs - ',
//            ])

            ->add('enabled', CheckboxType::class, [
                'label' => 'Engedélyezve',
            ])
            ->add('navigationItems',CollectionType::class,[
                'entry_type' => CmsNavigationItemFormType::class,
                /**
                 * Az entry_options-ben megadott opciokat tovabbitja a child formba
                 * A productKind opciot definialni kell a gyerek formban, hogy tudja fogadni
                 */
                'entry_options' => [
                    'label' => false,
                ],
                'label' => 'Menupontok',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'constraints' => [new Valid()],
            ]);
//            ->add('slug', TextType::class,[
//                'label' => 'Slug',
//            ])
//            ->add('content', TextareaType::class,[
//                'label' => 'Leírás',
//                'attr' => ['rows' => '5']
//            ]);

        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsNavigation::class,
        ]);
    }
}

