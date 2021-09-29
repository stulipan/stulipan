<?php

namespace App\Services;

use App\Entity\ImageEntity;
use App\Model\ImageUsage;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FYI: FileUploader class is a service defined in service.yaml, 
 * along with the $targetDirectory
 */
class FileUploader
{
    const UPLOADS_IMAGES_FOLDER = '/uploads/images';
    const WEBSITE_FOLDER_NAME = 'store';
    const PRODUCTS_FOLDER_NAME = 'products';

    private $targetDirectory;
    private $requestStackContext;
    private $em;

    /**
     * @param $targetDirectory
     * FYI:   $targetDirectory is defined in services.yaml under 'services > _defaults > bind'
     */
    public function __construct(string $targetDirectory, RequestStackContext $requestStackContext,
                                EntityManagerInterface $em)
    {
        $this->targetDirectory = $targetDirectory;
        $this->requestStackContext = $requestStackContext;
        $this->em = $em;
    }

    /**
     * Handles uploading of a file, and returns the uploaded file's name as a string
     * 2nd param = null, else deletes prev. image file passed on in $existingImage
     */
    public function uploadFile(UploadedFile $file, ?ImageEntity $existingImage, string $purpose = null): string  //?string $existingFilename,
    {
        // Based on kind of the image to be uploaded, pick the appropriate target folder
        switch ($purpose) {
            case ImageEntity::STORE_IMAGE:
                $folder = self::WEBSITE_FOLDER_NAME;
                break;
            case ImageEntity::PRODUCT_IMAGE:
                $folder = self::PRODUCTS_FOLDER_NAME;
                break;
            default:
                $folder = self::WEBSITE_FOLDER_NAME;
        }
        
        if ($existingImage instanceof ImageEntity) {
            $existingFilename = $existingImage->getFile();
        } else {
            $existingFilename = null;
        }
        
        $destination = $this->targetDirectory.'/'.$folder;
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $originalFilename.'-'.uniqid().'.'.$file->guessExtension();

        // moves the file to the directory where images are stored
        $file->move(
            $destination,
            $newFilename
        );

        if ($existingFilename) {
            try {
                unlink($destination.'/'.$existingFilename);
            } catch (ErrorException $e) {
                throw new FileNotFoundException();
            }
            $this->em->remove($existingImage);
            $this->em->flush();
        }

        return $newFilename;
    }

    public function getPublicPath(string $path): string
    {
        return $this->requestStackContext->getBasePath() . self::UPLOADS_IMAGES_FOLDER . '/' . $path;
    }

    public function getImageFile()
    {

    }
}