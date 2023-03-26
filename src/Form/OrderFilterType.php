<?php

namespace App\Form;

use App\Entity\DateRange;

use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderFilterType extends AbstractType
{
    private $urlGenerator;
    private $translator;
    private $em;

    public function __construct(UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->em = $entityManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $statusList = [
            $this->em->getRepository(OrderStatus::class)->findOneBy(['shortcode' => OrderStatus::ORDER_CREATED]),
            $this->em->getRepository(OrderStatus::class)->findOneBy(['shortcode' => OrderStatus::STATUS_FULFILLED]),
        ];

        $builder->setAction($this->urlGenerator->generate('order-list-filter'));
        $builder->add('dateRange', TextType::class, [
            'label' => 'Dátum',
            'attr' => ['placeholder' => $this->translator->trans('order.filter.filter-by-date'), 'autocomplete' => 'off'],
            'required' => false,
        ]);
        $builder->add('searchTerm', TextType::class, [
            'label' => 'Keresés',
            'attr' => ['placeholder' => $this->translator->trans('order.filter.search-placeholder'), 'autocomplete' => 'off'],
            'required' => false,
        ]);
        $builder->add('orderStatus',EntityType::class,[
            'class' => OrderStatus::class,
            'placeholder' => $this->translator->trans('order.filter.choose-order-status'),
            'choices' => $statusList,
//            'query_builder' => function (EntityRepository $er) {
//                return $er->createQueryBuilder('s')
//                    ->andWhere('s.id IN (:statusList)')
//                    ->setParameter('statusList', $statusList)
//                    ->orderBy('s.id', 'ASC');
//            },
            'choice_label' => 'name',
//            'constraints' => [
//                new NotNull(['message' => 'Válaszd ki a rendelés állapotát.']),
//            ],
            'required' => false,
        ]);
        $builder->add('paymentStatus',EntityType::class,[
            'class' => PaymentStatus::class,
            'placeholder' => $this->translator->trans('order.filter.choose-payment-status'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC');
            },
            'choice_label' => 'name',
//            'constraints' => [
//                new NotNull(['message' => 'Válaszd ki a rendelés állapotát.']),
//            ],
            'required' => false,
        ]);
        $builder->add('isCanceled',ChoiceType::class,[
            'required' => false,
            'choices' => [
                'Nyitott' => 'no',
                'Törölt' => 'yes',
            ],

        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
//						'data_class' => DateRange::class
        ]);
    
    }

    public function getBlockPrefix()
    {
        return parent::getBlockPrefix().'_'.str_shuffle('0123456789');
    }
}

