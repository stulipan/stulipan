<?php

declare(strict_types=1);

namespace App\Form\Cms;

use App\Entity\CmsNavigationItem;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class CmsNavigationItemFormType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',TextType::class, [
                'label' => 'Megnevezés',
                'attr' => [
                    'placeholder' => 'Megnevezés, pl: 16-20',
                    'autocomplete' => 'off'
                ],
            ])
            ->add('url', TextType::class,[
                'label' => 'Url',
                'attr' => ['placeholder' => '', 'autocomplete' => 'off',],
                'required' => true,
                'constraints' => [new NotBlank()],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Engedélyezve',
            ])
//            ->add('ordering', NumberType::class,[
//                'label' => 'Sorrend',
//                'attr' => ['placeholder' => 'Sorrend']
//            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsNavigationItem::class
        ]);
    }
}