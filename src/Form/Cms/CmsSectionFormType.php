<?php

namespace App\Form\Cms;

use App\Entity\CmsSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;

class CmsSectionFormType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CmsSection|null $section */
        $section = $options['data'] ?? null;
        $isEdit = $section && $section->getId();

        $builder
            ->add('id',HiddenType::class,[
                'mapped' => true,
            
            ])
            ->add('name', null,[
                'mapped' => true,
            ])
            ->add('belongsTo', ChoiceType::class,[
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotNull(['message' => $this->translator->trans('cms.section.page-is-missing')]),
                ],
                'choices' => [
                    $this->translator->trans('cms.section.homepage') => CmsSection::HOMEPAGE,
                    $this->translator->trans('cms.section.product-page') => CmsSection::PRODUCT_PAGE,
                    $this->translator->trans('cms.section.collection-page') => CmsSection::COLLECTION_PAGE,
                ],
            ])

            ->add('enabled', CheckboxType::class, [
                'mapped' => true,
            ])
            ->add('slug', TextType::class,[
                'mapped' => true,
            ])
            ->add('content', TextareaType::class,[
                'mapped' => true,
            ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsSection::class,
        ]);
    }
}