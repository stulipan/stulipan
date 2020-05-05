<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\DeliveryDateInterval;
use App\Entity\Model\DeliveryDate;
use App\Form\DataTransformer\DeliveryIntervalToStringTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;


class CartSelectDeliveryIntervalType extends AbstractType
{
    private $urlGenerator;
    private $em;
    private $twig;

    /**
     * @var DeliveryIntervalToStringTransformer
     */
    private $intervalTransformer;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em, Environment $twig,
                                DeliveryIntervalToStringTransformer $intervalTransformer)
    {
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
        $this->em = $em;
        $this->intervalTransformer = $intervalTransformer;
    }

    /**
     * NINCS HASZNALATBAN! !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deliveryInterval', EntityType::class,[
                'class' => DeliveryDateInterval::class,
//                'choice_label' => 'name',
                'choice_label' => function (DeliveryDateInterval $interval) {
                    return $this->twig->render('webshop/cart/choiceItem-label-deliveryInterval.html.twig', [
                        'intervalName' => $interval->getName(),
                        'intervalPrice' => $interval->getPrice(),
                    ]);
                },
                'mapped' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => $options['intervals'],
//                'choices' => $this->em->getRepository(DeliveryDateInterval::class)->findBy(['dateType' => $options['dateType']]),
            ]);
//        $builder->get('deliveryInterval')->addModelTransformer($this->intervalTransformer);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeliveryDate::class,
            'intervals' => '',
            'dateType' => '',
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'interval';
    }


}