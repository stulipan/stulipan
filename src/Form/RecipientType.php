<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;
use App\Entity\Recipient;
use App\Form\AddressType;

use App\Form\DataTransformer\StringToNumberTransformer;
use App\Validator\Constraints\PhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class RecipientType extends AbstractType
{
    private $urlGenerator;

    private $stringToNumberTransformer;

    public function __construct(UrlGeneratorInterface $urlGenerator, StringToNumberTransformer $stringToNumberTransformer)
    {
        $this->urlGenerator = $urlGenerator;
        $this->stringToNumberTransformer = $stringToNumberTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$builder->getEmptyData()) {
            $builder->setAction($this->urlGenerator->generate('cart-editRecipient', ['id' => $builder->getData()->getId()]));
        } else {
            $builder->setAction($this->urlGenerator->generate('cart-editRecipient', ['id' => $builder->getData()->getId()]));
        }
        $builder->add('id', HiddenType::class,[
                'mapped' => false, // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
            ]);
        $builder->add('name', TextType::class,[
                'label' => 'Címzett',
                'required' => true,
                'attr' => [
                    'placeholder' => '',
                    'autocomplete' => 'name'
                ]
            ]);
        $builder->add('address',AddressType::class,[
            'label' => false,
            'addressType' => Address::DELIVERY_ADDRESS,  // this option is defined in AddressType, so that it can receive a value
        ]);
        $builder->add('phone',TelType::class,[
                'label' => 'Telefonszám',
                'required' => false,
                'constraints' => [
                    new PhoneNumber(['regionCode' => 'HU']),
                ],
            ]);
//        $builder->get('phone')->addModelTransformer($this->stringToNumberTransformer);  //ez akkor kellett amikor a telszmot int -kent taroltam db-ben
        $builder->add('customer',HiddenType::class,[
                'mapped' => false,
            ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recipient::class,
            'attr' => [
                'novalidate' => 'novalidate',
//                'autocomplete' => 'pleasedont',
                'data-autocomplete-url' => $this->urlGenerator->generate('cart-search-api'),
            ],
        ]);
    }
//    public function getBlockPrefix()
//    {
//        return '';
//    }


}