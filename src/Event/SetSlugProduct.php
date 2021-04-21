<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Product\Product;
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
    private $slug;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->slug = new Slugify();
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
        $slug = $this->slug->slugify($product->getName());
        $product->setSlug($slug . '-p' . $product->getId());  // adds a '-p37432'

        $em = $args->getObjectManager();
        $em->persist($product);
        $em->flush();
    }
}