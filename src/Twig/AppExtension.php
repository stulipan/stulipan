<?php

namespace App\Twig;

use App\Services\FileUploader;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions(): array
    {
        return [
          new TwigFunction('uploaded_asset', [$this, 'getPathOfUploadedAsset'])
        ];
    }

    public function getPathOfUploadedAsset(string $path): string
    {
        return $this->container
            ->get(FileUploader::class)
            ->getPublicPath($path);
    }

    public static function getSubscribedServices()
    {
        return [
            FileUploader::class,
        ];
    }
}