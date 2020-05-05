<?php

namespace App\Form;

use App\Entity\DateRange;

use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Entity\Product\ProductStatus;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\NotNull;
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
            'label' => 'Dátum',
            'attr' => ['placeholder' => 'Szűrés időre...', 'autocomplete' => 'off'],
            'required' => false,
        ]);
        $builder->add('searchTerm', TextType::class, [
            'label' => $this->translator->trans('customer.search'),
            'attr' => ['placeholder' => $this->translator->trans('customer.search-placeholder'), 'autocomplete' => 'off'],
            'required' => false,
        ]);
        $builder->add('status',EntityType::class,[
            'class' => ProductStatus::class, // !!!!!!!!!!!!!!!!!!!!!!!!!!!! PRodiuct Status !!!!
            'placeholder' => $this->translator->trans('customer.choose-status'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('s')
                    ->orderBy('s.name', 'ASC');
            },
            'choice_label' => 'name',
            'required' => false,
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
//						'data_class' => DateRange::class
        ]);
    
    }
    
}

