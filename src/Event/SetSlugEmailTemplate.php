<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\StoreEmailTemplate;
use App\Services\SlugBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This has to be configured in services.yaml
 */
class SetSlugEmailTemplate
{
    /**
     * @var SlugBuilder
     */
    private $slugBuilder;

    private $em;
    private $validator;

    public function __construct(SlugBuilder $slugBuilder, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->slugBuilder = $slugBuilder;
        $this->em = $em;
        $this->validator = $validator;
    }
    
    /**
     * Creates the slug
     *
     * @param StoreEmailTemplate $obj
     * @param LifecycleEventArgs $args
     */
    public function prePersist(StoreEmailTemplate $obj, LifeCycleEventArgs $args)
    {
        $slug = $this->slugBuilder->slugify($obj->getName());
        $obj->setSlug($this->buildValidSlug($obj, $slug));
    }

    /**
     * Updates the slug
     *
     * @param StoreEmailTemplate $obj
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(StoreEmailTemplate $obj, PreUpdateEventArgs $args)
    {
        $slug = $this->slugBuilder->slugify($obj->getName());
        $obj->setSlug($this->buildValidSlug($obj, $slug));
    }

    private function buildValidSlug($obj, $slug)
    {
        $obj->setSlug($slug);
        $errors = $this->validator->validate($obj);

        if (count($errors) > 0) {
            return $this->buildValidSlug($obj, $slug.'-1');
        }
        return $slug;
    }
}