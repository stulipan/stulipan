<?php

namespace App\Form;

use App\Entity\Product\ProductBadge;
use Stulipan\Traducible\Form\PropertyType;
use Stulipan\Traducible\Form\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProductBadgeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ProductBadge|null $badge */
        $badge = $options['data'] ?? null;
        $isEdit = $badge && $badge->getId();

        $builder
//            ->add('nume', PropertyType::class, [
//                'data_class' => $badge->getTranslationEntityClass(),
//                '__translationClass' => $badge->getTranslationEntityClass(),
//                '__property' => '',
//                'mapped' => false,
//                '__translatableEntity' => $badge,
//                '__currentLocale' => $options['__currentLocale'],
//
//            ])
            ->add('translations', TranslationsType::class, [
                '__translationClass' => $badge->getTranslationEntityClass(),
                '__currentLocale' => $options['__currentLocale'],
            ])
            ->add('ordering', NumberType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => '',
                    'min' => 0,
                ],
            ])
            ->add('css', TextType::class,[
            ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductBadge::class,
            '__currentLocale' => '',
        ]);
    }
}

