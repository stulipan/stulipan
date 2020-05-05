<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductImage;
use App\Services\FileUploader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * This has to be configured in services.yaml
 */
class ImageSetFullPath implements ServiceSubscriberInterface
{
    /** @var ContainerInterface */
    private $container;
    
    /** @var FileUploader */
    private $fileUploader;
    
    /** @var CacheManager */
    private $cacheManager;
    
    public function __construct(ContainerInterface $container, FileUploader $fileUploader, CacheManager $cacheManager)
    {
        $this->container = $container;
        $this->fileUploader = $fileUploader;
        $this->cacheManager = $cacheManager;
    }
    
    /**
     * @param LifeCycleEventArgs $args
     */
    public function postLoad(LifeCycleEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if ($entity instanceof ProductImage || $entity instanceof ProductCategory) {
            $publicPath = $this->container->get(FileUploader::class)->getPublicPath($entity->getImagePath());
            $entity->setImageUrl(
                $this->cacheManager->getBrowserPath($publicPath, 'product_image')
            );
        }
    }

    /**
     * @param LifeCycleEventArgs $args
     */
    public function postUpdate(LifeCycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ProductImage || $entity instanceof ProductCategory) {
            $publicPath = $this->container->get(FileUploader::class)->getPublicPath($entity->getImagePath());
            $entity->setImageUrl(
                $this->cacheManager->getBrowserPath($publicPath, 'product_image')
            );
        }
    }
    
    public static function getSubscribedServices()
    {
        return [
            FileUploader::class,
        ];
    }
}