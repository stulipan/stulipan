<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\CmsPage;
use App\Services\SlugBuilder;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This has to be configured in services.yaml
 */
class SetSlugCmsPage
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
     * @param CmsPage $cmsPage
     * @param LifecycleEventArgs $args
     */
    public function prePersist(CmsPage $cmsPage, LifeCycleEventArgs $args)
    {
        $slug = $this->slugBuilder->slugify($cmsPage->getName());
        $cmsPage->setSlug($this->buildValidSlug($cmsPage, $slug));
    }

    /**
     * Updates the slug
     *
     * @param CmsPage $cmsPage
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(CmsPage $cmsPage, PreUpdateEventArgs $args)
    {
        $slug = $this->slugBuilder->slugify($cmsPage->getName());
        $cmsPage->setSlug($this->buildValidSlug($cmsPage, $slug));
    }

    private function buildValidSlug(CmsPage $cmsPage, string $slug)
    {
        $cmsPage->setSlug($slug);
        $errors = $this->validator->validate($cmsPage);

        if (count($errors) > 0) {
            $slugOriginal = $this->slugBuilder->slugify($cmsPage->getName());
            $postfix = $this->slugBuilder->numberedPostfix($slugOriginal, $slug);
            return $this->buildValidSlug($cmsPage, $slugOriginal.'-'.$postfix);
        }
        return $slug;
    }
}