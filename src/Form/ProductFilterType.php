<?php

namespace App\Form;

use App\Entity\DateRange;

use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Entity\Product\ProductStatus;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\NotNull;

class ProductFilterType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('product-list-filter'));
        $builder->add('searchTerm', TextType::class, [
            'label' => 'Keresés',
            'attr' => ['placeholder' => 'Keresés...', 'autocomplete' => 'off'],
            'required' => false,
        ]);
        $builder->add('status',EntityType::class,[
            'class' => ProductStatus::class,
            'label' => 'Állapot',
            'placeholder' => 'Termék állapota...',
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

