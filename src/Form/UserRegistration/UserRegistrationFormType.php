<?php

declare(strict_types=1);

namespace App\Form\UserRegistration;

use App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\IsTrue;


class UserRegistrationFormType extends AbstractType
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('site-register'));
        $builder
            ->add('id', HiddenType::class,[
                // ha hidden mezőről van szó, ami maga az ID, akkor azt nem szabad map-elni az entityvel.
                'mapped' => false,
            ])
            ->add('email', TextType::class,[
                'required' => true,
                'attr' => [
                    'placeholder' => '',
                    'autocomplete' => 'email'
                ]
            ])
            ->add('lastname', TextType::class,[
                'required' => true,
                'attr' => [
                    'placeholder' => '',
                    'autocomplete' => 'lastname'
                ]
            ])
            ->add('firstname', TextType::class,[
                'required' => true,
                'attr' => [
                    'placeholder' => '',
                    'autocomplete' => 'firstname'
                ]
            ])
            ->add('password',PasswordType::class,[
                'required' => true,
                'always_empty' => false, // Ezzel nem felejti el a jelszót, amikor hibás/hiányzó adatok miatt újratölti a form-ot
            ])
            ->add('username',TextType::class,[
                'required' => false,
            ])
            ->add('optin', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
//                'constraints' => new IsTrue(['message' => 'Biztosan nem akarsz feliratkozni a hírlevélre?']),
            ])
            ->add('terms', CheckboxType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => new IsTrue(['message' => 'Kérjük olvasd el és fogadd el az ÁSZF-et.']),
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'registration';
    }


}