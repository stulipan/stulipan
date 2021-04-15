<?php
//a SetDiscountType-bol csinaltam
declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;

use App\Entity\Geo\GeoCountry;
use App\Entity\Geo\GeoPlace;
use App\Entity\OrderAddress;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class OrderAddressType extends AbstractType
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
        $preferredCountries = $this->em->getRepository(GeoCountry::class)->findBy(['alpha2' => 'hu']);
        $builder->add('id',HiddenType::class,[
            'mapped' => false, // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
        ]);
        $builder->add('street',TextType::class,[
            'attr' => ['autocomplete' => 'street-address'],
        ]);
        $builder->add('city',TextType::class,[
            'attr' => ['autocomplete' => 'address-level2'],
        ]);
        $builder->add('zip',IntegerType::class,[
            'attr' => ['autocomplete' => 'postal-code'],
        ]);
        $builder->add('province',TextType::class,[
            'attr' => ['autocomplete' => 'address-level1'],
        ]);
        $builder->add('country',EntityType::class,[
            'class' => GeoCountry::class,
            'placeholder' => 'Válassz országot...',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC');
            },
            'choice_label' => 'name',
            'preferred_choices' => $preferredCountries,
            'attr' => ['autocomplete' => 'country-name'],
        ]);
        $builder->add('addressType',HiddenType::class,[
            'attr' => ['value' => $options['addressType']],
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderAddress::class,
            'addressType' => '',
            'attr' => ['novalidate' => 'novalidate'],
            'error_bubbling' => true,
//            'by_reference' => false,  // https://symfony.com/doc/current/reference/forms/types/form.html#by-reference
        ]);
    }

}