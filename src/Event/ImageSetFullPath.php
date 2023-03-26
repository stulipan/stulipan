<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\ImageEntity;
use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductImage;
use App\Model\ImageFileResource;
use App\Services\FileUploader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Liip\ImagineBundle\Config\FilterFactoryCollection;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\LiipImagineBundle;
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

    private $filterManager;

    public function __construct(ContainerInterface $container, FileUploader $fileUploader,
                                CacheManager $cacheManager, FilterManager $filterManager)
    {
        $this->container = $container;
        $this->fileUploader = $fileUploader;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
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

        // Images used throughout the whole website
        if ($entity instanceof ImageEntity) {
//            if ($entity->getType() == ImageEntity::STORE_IMAGE) {
                $publicPath = $this->fileUploader->getPublicPath($entity->getPath());
                $entity->setUrl(
                    $this->cacheManager->getBrowserPath($publicPath, 'unscaled')
                );

                $allFilters = $this->filterManager->getFilterConfiguration()->all();  // retrieve all filters that are set up in liip_imagine.yaml
                $allowedStrings  = ['unscaled', 'size_'];

                $filters = array_filter(
                    $allFilters,
                    function ($key) use ($allowedStrings) {
                        return array_filter($allowedStrings, function ($value) use ($key) {
                            return strpos($key, $value) !== false;
                        });
                    },
                    ARRAY_FILTER_USE_KEY
                );


                // filters ==> [
                //      'unscaled' => [ ],
                //      'unscaled_200' => [ ],
                //      'size_1200' => [ ],
                //      'size_600' => [ ],
                //      'size_200 => [ ]'
                // ]
                //
                // keys ==> ['unscaled', 'unscaled_200', 'size_1200', 'size_600', 'size_200']
                $keys = array_keys($filters);

//                // keys ==> ['unscaled', 'unscaledW200', 'size1200', 'size600', 'size200']
//                $keys = array_map(
//                    function($val) {
//                        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $val))));
//                    }, $keys);

                $fileResource = new ImageFileResource();
                foreach ($keys as $value) {
                    $property = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $value))));
                    $fileResource->setProperty($property, $this->cacheManager->getBrowserPath($publicPath, $value));
                    // equiv. to ==> $fileResource->setSize600($this->cacheManager->getBrowserPath($publicPath, 'size_600'));
                }

                $entity->setFileResource($fileResource);
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
