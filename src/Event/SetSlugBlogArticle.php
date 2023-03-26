<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\BlogArticle;
use App\Services\SlugBuilder;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This has to be configured in services.yaml
 */
class SetSlugBlogArticle
{
    /**
     * @var SlugBuilder
     */
    private $slugBuilder;
    private $validator;

    public function __construct(SlugBuilder $slugBuilder, ValidatorInterface $validator)
    {
        $this->slugBuilder = $slugBuilder;
        $this->validator = $validator;
    }

    /**
     * Creates the slug
     *
     * @param BlogArticle $entity
     * @param LifecycleEventArgs $args
     */
    public function prePersist(BlogArticle $entity, LifeCycleEventArgs $args)
    {
        $slug = $this->slugBuilder->slugify($entity->getTitle());
        $entity->setSlug($this->buildValidSlug($entity, $slug));
        $entity->setSeoTitle($entity->getTitle());

        if ($entity->getContent()) {
            $entity->setSeoDescription(substr(strip_tags($entity->getContent()), 0, 155));
        }
    }

    /**
     * Updates the slug
     *
     * @param BlogArticle $entity
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(BlogArticle $entity, PreUpdateEventArgs $args)
    {
        // Do nothing when there is already a slug.
        if (null !== $entity->getSlug()) {
            return;
        }
        $slug = $this->slugBuilder->slugify($entity->getTitle());
        $entity->setSlug($this->buildValidSlug($entity, $slug));
    }

    private function buildValidSlug(BlogArticle $entity, string $slug)
    {
        $entity->setSlug($slug);
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            $slugOriginal = $this->slugBuilder->slugify($entity->getTitle());
            $postfix = $this->slugBuilder->numberedPostfix($slugOriginal, $slug);
            return $this->buildValidSlug($entity, $slugOriginal.'-'.$postfix);
        }
        return $slug;
    }
}
