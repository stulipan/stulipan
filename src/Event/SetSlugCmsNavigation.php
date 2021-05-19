<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\CmsNavigation;
use App\Services\SlugBuilder;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * This has to be configured in services.yaml
 */
class SetSlugCmsNavigation
{
    /**
     * @var SlugBuilder
     */
    private $slugBuilder;

    public function __construct(SlugBuilder $slugBuilder)
    {
        $this->slugBuilder = $slugBuilder;
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
        $cmsNavigation->setSlug($slug);
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
        $cmsNavigation->setSlug($slug);
    }
}