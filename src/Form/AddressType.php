<?php
//a SetDiscountType-bol csinaltam
declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class AddressType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->setAction($this->urlGenerator->generate('cart_set_delivery_address', ['id' => '0']));
        $builder->add(
            'id',
            HiddenType::class,
             // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
            ['mapped' => false]
        );
        $builder->add(
            'street',
            TextType::class,
            [
                'required' => true,
                'label' => 'Cím',
            ]
        );
        $builder->add(
            'city',
            TextType::class,
            [
                'required' => true,
                'label' => 'Város',
            ]
        );
        $builder->add(
            'zip',
            IntegerType::class,
            [
                'required' => true,
                'label' => 'Iranyítószám',
            ]
        );
        $builder->add(
            'province',
            TextType::class,
            [
                'required' => true,
                'label' => 'Megye',
            ]
        );
        $builder->add(
            'country',
            TextType::class,
            [
                'required' => true,
                'label' => 'Ország',
            ]
        );
        $builder->add(
            'street',
            TextType::class,
            [
                'required' => true,
                'label' => 'Street',
            ]
        );
        $builder->add(
            'addressType',
            HiddenType::class,
            [
                'attr' => ['value' => '2'],
            ]
        );

    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class
        ]);
    }



}