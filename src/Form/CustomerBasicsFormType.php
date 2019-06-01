<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Model\CustomerBasic;
use App\Entity\User;

use App\Validator\Constraints\PhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotNull;


class CustomerBasicsFormType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder->setAction($this->urlGenerator->generate('site-register'));
        $builder
            ->add('email', TextType::class,[
                'label' => 'Email címed',
                'required' => true,
                'attr' => [
                    'placeholder' => '',
                    'autocomplete' => 'email'
                ],
                'constraints' => [
                    new NotNull(['message' => "Add meg az email címedet!"]),
                    new Email(['message' => "Hibás email cím!"]),
                ],
            ])
            ->add('lastname', TextType::class,[
                'label' => 'Vezetéknév',
                'required' => true,
                'attr' => [
                    'placeholder' => '',
                    'autocomplete' => 'lastname'
                ],
                'constraints' => [
                    new NotNull(['message' => "Add meg a nevedet!"]),
                ],
            ])
            ->add('firstname', TextType::class,[
                'label' => 'Keresztnév',
                'required' => true,
                'attr' => [
                    'placeholder' => '',
                    'autocomplete' => 'firstname'
                ],
                'constraints' => [
                    new NotNull(['message' => "Add meg a keresztnevedet!"]),
                ],
            ])
            ->add('phone',TelType::class,[
                'label' => 'Telefonszámod',
                'required' => false,
                'constraints' => [
                    new NotNull(['message' => "Add meg a telefonszámot!"]),
                    new PhoneNumber(['regionCode' => 'HU']),
                ],
            ])
            ->add('optin', CheckboxType::class, [
                'label' => 'Pipáld be és értesíteni fogunk akcióinkról. Kizárólag vásárlóink részére!',
                'required' => false,
                'mapped' => false,
//                'constraints' => new IsTrue(['message' => 'Biztosan nem akarsz feliratkozni a hírlevélre?']),
            ])
//            ->add('terms', CheckboxType::class, [
//                'label' => 'Kijelentem, hogy az Általános Szerződési Feltételeket és az Adatvédelmi nyilatkozatot megismertem és elfogadom, az abban szereplő adatkezelésekhez hozzájárulok.',
//                'required' => true,
//                'mapped' => false,
//                'constraints' => new IsTrue(['message' => 'Kérjük olvasd el és fogadd el az ÁSZF-et.']),
//            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomerBasic::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}