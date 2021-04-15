<?php
//a SetDiscountType-bol csinaltam
declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;

use App\Entity\Geo\GeoCountry;
use App\Entity\Geo\GeoPlace;
use App\Entity\Order;
use App\Entity\OrderStatus;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotNull;


class OrderStatusType extends AbstractType
{
    private $urlGenerator;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em)
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('order-editStatus', ['id' => $builder->getData()->getId()]));
        $builder->add('id',HiddenType::class,[
             // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
            'mapped' => false,
        ]);
        $builder->add('status',EntityType::class,[
            'class' => OrderStatus::class,
            'label' => 'Állapot',
            'placeholder' => 'Válassz...',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC');
            },
            'choice_label' => 'name',
            'constraints' => [
                    new NotNull(['message' => 'Válaszd ki a rendelés állapotát.']),
                ],
        ]);
        $builder->add('sendNotification', CheckboxType::class, [
            'label' => 'Értesítés az ügyfélnek',
            'required' => false,
            'mapped' => false,
        ]);
        $builder->add('addNote', CheckboxType::class, [
            'label' => 'Megjegyzés hozzáadása',
            'required' => false,
            'mapped' => false,
        ]);
        $builder->add('note',TextareaType::class,[
            'label' => 'Megjegyzés',
            'attr' => ['rows' => '3'],
            'required' => false,
            'mapped' => false,
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'attr' => ['novalidate' => 'novalidate'],
            'error_bubbling' => true,
        ]);
    }

}