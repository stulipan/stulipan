<?php

declare(strict_types=1);

namespace App\Form\Cms;

use App\Entity\CmsNavigationItem;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('url', TextType::class,[
                'attr' => ['autocomplete' => 'off',],
                'required' => true,
                'constraints' => [new NotBlank()],
            ])
            ->add('enabled', CheckboxType::class, [
            ])
            ->add('ordering', NumberType::class,[
            ])
            ->add('classname', TextType::class,[
            ])

            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsNavigationItem::class
        ]);
    }
}