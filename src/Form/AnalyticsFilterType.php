<?php

namespace App\Form;

use App\Services\AnalyticsBreakdown;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AnalyticsFilterType extends AbstractType
{
    private $urlGenerator;
    private $translator;
    private $analyticsBreakdown;

    public function __construct(UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator,
                                AnalyticsBreakdown $analyticsBreakdown)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->analyticsBreakdown = $analyticsBreakdown;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $breakdownList = $this->analyticsBreakdown->getBreakdownList();

        $builder->setAction($this->urlGenerator->generate('analytics-filter'));
        $builder->add('dateRange', TextType::class, [
            'attr' => ['placeholder' => $this->translator->trans('analytics.filter-by-date'), 'autocomplete' => 'off'],
            'required' => false,
        ]);
        $builder->add('groupBy',ChoiceType::class,[
            'placeholder' => $this->translator->trans('analytics.group-by'),
            'choices' => $breakdownList,
            'required' => false,
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    
    }

    public function getBlockPrefix()
    {
        return parent::getBlockPrefix().'_'.str_shuffle('0123456789');
    }
}

