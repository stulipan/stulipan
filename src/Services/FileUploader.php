<?php

namespace App\Services;

use App\Entity\ImageEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * FYI: FileUploader class is a service defined in service.yaml, 
 * along with the $targetDirectory
 */
class FileUploader
{
    const IMAGES_FOLDER = '/uploads/images';
    const CATEGORY_FOLDER = 'categories';
    const PRODUCT_FOLDER = 'products';
    const OTHER_FOLDER = 'other';
    
    const IMAGE_OF_CATEGORY_TYPE = '1';
    const IMAGE_OF_PRODUCT_TYPE = '2';
    const IMAGE_OF_OTHER_TYPE = '0';

    private $targetDirectory;
    private $requestStackContext;
    private $em;
    
    /**
     * @param $targetDirectory
     * FYI:   $targetDirectory is defined in services.yaml under 'services > _defaults > bind'
     */
    public function __construct(string $targetDirectory, RequestStackContext $requestStackContext, EntityManagerInterface $em)
    {
        $this->targetDirectory = $targetDirectory;
        $this->requestStackContext = $requestStackContext;
        $this->em = $em;
    }

    /**
     * Handles uploading of a file, and returns the uploaded file's name as a string
     * 2nd param = null, else deletes prev. image file passed on in $existingImage
     */
    public function uploadFile(UploadedFile $file, ?ImageEntity $existingImage, int $purpose = null): string  //?string $existingFilename,
    {
        // Based on kind of the image to be uploaded, pick the appropriate target folder
        switch ($purpose) {
            case self::IMAGE_OF_CATEGORY_TYPE:
                $folder = self::CATEGORY_FOLDER;
                break;
            case self::IMAGE_OF_PRODUCT_TYPE:
                $folder = self::PRODUCT_FOLDER;
                break;
            default:
                $folder = self::OTHER_FOLDER;
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
            } catch (\ErrorException $e) {
                throw new \Symfony\Component\Filesystem\Exception\FileNotFoundException();
            }
            $this->em->remove($existingImage);
            $this->em->flush();
        }

        return $newFilename;
    }

    public function getPublicPath(string $path): string
    {
//        dd($this->requestStackContext->getBasePath());
        return $this->requestStackContext->getBasePath().'/uploads/images/'.$path;
    }
}