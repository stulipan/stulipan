<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\CmsNavigation;
use App\Services\SlugBuilder;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This has to be configured in services.yaml
 */
class SetSlugCmsNavigation
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
     * @param CmsNavigation $cmsNavigation
     * @param LifecycleEventArgs $args
     */
    public function prePersist(CmsNavigation $cmsNavigation, LifeCycleEventArgs $args)
    {
        $slug = $this->slugBuilder->slugify($cmsNavigation->getName());
        $cmsNavigation->setSlug($this->buildValidSlug($cmsNavigation, $slug));
    }

    /**
     * Updates the slug
     *
     * @param CmsNavigation $cmsNavigation
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(CmsNavigation $cmsNavigation, PreUpdateEventArgs $args)
    {
        $slug = $this->slugBuilder->slugify($cmsNavigation->getName());
        $cmsNavigation->setSlug($this->buildValidSlug($cmsNavigation, $slug));

    }

    private function buildValidSlug(CmsNavigation $cmsNavigation, string $slug)
    {
        $cmsNavigation->setSlug($slug);
        $errors = $this->validator->validate($cmsNavigation);

        if (count($errors) > 0) {
            $slugOriginal = $this->slugBuilder->slugify($cmsNavigation->getName());
            $postfix = $this->slugBuilder->numberedPostfix($slugOriginal, $slug);
            return $this->buildValidSlug($cmsNavigation, $slugOriginal.'-'.$postfix);
        }
        return $slug;
    }
}