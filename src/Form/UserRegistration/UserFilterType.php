<?php

namespace App\Form\UserRegistration;

use App\Entity\Product\ProductStatus;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserFilterType extends AbstractType
{
    private $urlGenerator;
    private $translator;

    public function __construct(UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('user-list-filter'));
        $builder->add('dateRange', HiddenType::class, [
            'attr' => ['autocomplete' => 'off'],
            'required' => false,
        ]);
        $builder->add('searchTerm', TextType::class, [
            'attr' => ['autocomplete' => 'off'],
            'required' => false,
        ]);
        $builder->add('status',ChoiceType::class,[
            'choices' => [
                'Active' => true,
                'Disabled' => false,
            ],
            'placeholder' => $this->translator->trans('user.filter.filter-by-status-choose'),
            'required' => false,
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    
    }
    
}

