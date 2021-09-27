<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\ImageEntity;
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
        
        if ($entity instanceof ProductImage) {
            if ($entity->getImage()) {
                $publicPath = $this->fileUploader->getPublicPath($entity->getImagePath());
                $entity->setImageUrl(
                    $this->cacheManager->getBrowserPath($publicPath, 'product_large')
                );
                $entity->setThumbnailUrl(
                    $this->cacheManager->getBrowserPath($publicPath, 'product_medium')
                );
            }
        }
        if ($entity instanceof ProductCategory) {
            if ($entity->getImage()) {
                $publicPath = $this->fileUploader->getPublicPath($entity->getImagePath());
                $entity->setImageUrl(
                    $this->cacheManager->getBrowserPath($publicPath, 'unscaled')
                );
            }
        }

        if ($entity instanceof ImageEntity) {
//            if ($entity->getType() == ImageEntity::STORE_IMAGE) {
                $publicPath = $this->fileUploader->getPublicPath($entity->getPath());
                $entity->setUrl(
                    $this->cacheManager->getBrowserPath($publicPath, 'unscaled')
                );
//            }
        }
    }

    /**
     * @param LifeCycleEventArgs $args
     */
    public function postUpdate(LifeCycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ProductImage) {
            if ($entity->getImage()) {
                $publicPath = $this->fileUploader->getPublicPath($entity->getImagePath());
                $entity->setImageUrl(
                    $this->cacheManager->getBrowserPath($publicPath, 'product_large')
                );
                $entity->setThumbnailUrl(
                    $this->cacheManager->getBrowserPath($publicPath, 'product_medium')
                );
            }
        }

        if ($entity instanceof ProductCategory) {
            if ($entity->getImage()) {
                $publicPath = $this->fileUploader->getPublicPath($entity->getImagePath());
                $entity->setImageUrl(
                    $this->cacheManager->getBrowserPath($publicPath, 'unscaled')
                );
            }
        }
//        $entity = $args->getEntity();
//
//        if ($entity instanceof ProductImage || $entity instanceof ProductCategory) {
//            $publicPath = $this->container->get(FileUploader::class)->getPublicPath($entity->getImagePath());
//            $entity->setImageUrl(
//                $this->cacheManager->getBrowserPath($publicPath, 'product_large')
//            );
//        }
    }
    
    public static function getSubscribedServices()
    {
        return [
            FileUploader::class,
        ];
    }
}