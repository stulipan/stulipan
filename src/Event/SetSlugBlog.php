<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Blog;
use App\Services\SlugBuilder;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This has to be configured in services.yaml
 */
class SetSlugBlog
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
     * @param Blog $entity
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Blog $entity, LifeCycleEventArgs $args)
    {
        $slug = $this->slugBuilder->slugify($entity->getName());
        $entity->setSlug($this->buildValidSlug($entity, $slug));
    }

    /**
     * Updates the slug
     *
     * @param Blog $entity
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Blog $entity, PreUpdateEventArgs $args)
    {
        $slug = $this->slugBuilder->slugify($entity->getName());
        $entity->setSlug($this->buildValidSlug($entity, $slug));
    }

    private function buildValidSlug(Blog $entity, string $slug)
    {
        $entity->setSlug($slug);
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            $slugOriginal = $this->slugBuilder->slugify($entity->getName());
            $postfix = $this->slugBuilder->numberedPostfix($slugOriginal, $slug);
            return $this->buildValidSlug($entity, $slugOriginal.'-'.$postfix);
        }
        return $slug;
    }
}