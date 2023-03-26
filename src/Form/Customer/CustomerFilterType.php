<?php

namespace App\Form\Customer;

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

class CustomerFilterType extends AbstractType
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
        $builder->setAction($this->urlGenerator->generate('customer-list-filter'));
        $builder->add('dateRange', HiddenType::class, [
            'attr' => ['autocomplete' => 'off'],
            'required' => false,
        ]);
        $builder->add('searchTerm', TextType::class, [
            'attr' => ['autocomplete' => 'off'],
            'required' => false,
        ]);
        $builder->add('acceptsMarketing',ChoiceType::class,[
            'choices' => [
                $this->translator->trans('customer.filter.filter-by-accepts-marketing-subscribed') => true,
                $this->translator->trans('customer.filter.filter-by-accepts-marketing-no') => false,
            ],
            'placeholder' => $this->translator->trans('customer.filter.filter-by-accepts-marketing-choose'),
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

