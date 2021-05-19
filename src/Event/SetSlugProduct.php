<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Product\Product;
use App\Services\Localization;
use App\Services\SlugBuilder;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Uid\Uuid;

/**
 * This has to be configured in services.yaml
 */
class SetSlugProduct
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var SlugBuilder
     */
    private $slugBuilder;

    public function __construct(EntityManagerInterface $em, SlugBuilder $slugBuilder)
    {
        $this->em = $em;
        $this->slugBuilder = $slugBuilder;
    }
    
    /**
     * Creates a temporary slug for the Product
     *
     * @param Product $product
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Product $product, LifeCycleEventArgs $args)
    {
        $uuid = Uuid::v4();
        $product->setSlug($uuid->toRfc4122());
    }

    /**
     * Creates the final slug for the Product
     *
     * @param Product $product
     * @param LifecycleEventArgs $args
     */
    public function postPersist(Product $product, LifeCycleEventArgs $args)
    {
        $slug = $this->slugBuilder->slugify($product->getName());
        $product->setSlug($slug . '-p' . $product->getId());  // adds a '-p37432'

        $em = $args->getObjectManager();
        $em->persist($product);
        $em->flush();
    }
}