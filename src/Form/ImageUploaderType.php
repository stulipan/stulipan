<?php

namespace App\Form;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class ImageUploaderType extends AbstractType
{
    private $em;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator) {
        $this->em = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->urlGenerator->generate('image-upload'));

        $imageConstraints = [
            new Image([
                'maxSize' => '2M',
            ])
        ];
//        if (!$isEdit || !$page->getImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Töltsél fel egy képet.',
            ]);
//        }
        $builder->add('imageId', HiddenType::class, [
            'mapped' => false,
            'required' => false,
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
//            'data_class' => CmsPage::class,
        ]);
    }
}

