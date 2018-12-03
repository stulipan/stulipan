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
        $builder
            ->add('id',HiddenType::class,[
                 // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
                'mapped' => false,
            ])
            ->add('street',TextType::class,[
                'required' => true,
                'label' => 'Cím',
            ])
            ->add('city',TextType::class,[
                'label' => 'Város',
                'required' => true,
            ])
            ->add('zip',IntegerType::class,[
                'required' => true,
                'label' => 'Iranyítószám',
            ])
            ->add('province',TextType::class,[
                'required' => true,
                'label' => 'Megye',
            ])
            ->add('country',TextType::class,[
                'required' => true,
                'label' => 'Ország',
            ])
            ->add('street',TextType::class,[
                'required' => true,
                'label' => 'Street',
            ])
            ->add('addressType',HiddenType::class,[
                'attr' => ['value' => '2'],
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'attr' => ['novalidate' => 'novalidate'],
            'error_bubbling' => true,
//            'by_reference' => false,  // https://symfony.com/doc/current/reference/forms/types/form.html#by-reference
        ]);
    }


}