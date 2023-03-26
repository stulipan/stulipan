<?php

namespace App\Form\Blog;

use App\Entity\Blog;
use App\Entity\BlogArticle;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class BlogArticleFormType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var BlogArticle|null $article */
        $article = $options['data'] ?? null;
        $isEdit = $article && $article->getId();

        $blog = null;
        if (!$isEdit) {
            $blogs = $this->em->getRepository(Blog::class)->findAll();
            if ($blogs) {
                $blog = $blogs[0];
            }
        } else {
            $blog = $article->getBlog();
        }


        $builder
            ->add('title', null,[
            ])
            ->add('content', TextareaType::class,[
                'attr' => ['rows' => '5']
            ])
            ->add('excerpt', TextareaType::class,[
                'attr' => ['rows' => '5']
            ])
            ->add('seoTitle', null,[
            ])
            ->add('seoDescription', TextareaType::class,[
                'attr' => ['rows' => '5']
            ])
            ->add('slug', TextType::class,[
                'label' => 'Slug',
            ])

            ->add('enabled', CheckboxType::class, [
            ])
            ->add('blog', EntityType::class, [
                'class' => Blog::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => ' - nincs - ',
                'data' => $blog
            ])
            ->add('author', null,[
            ])
            ;

        $imageConstraints = [
            new Image([
                'maxSize' => '2M',
            ])
        ];
        if (!$isEdit || !$article->getImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Töltsél fel egy képet.',
            ]);
        }
        $builder->add('imageId', HiddenType::class, [
            'mapped' => false,
            'required' => false,
        ]);
        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BlogArticle::class,
        ]);
    }
}

