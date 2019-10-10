<?php

namespace App\Form\DataTransformer;

use App\Entity\ImageEntity;
use App\Services\FileUploader;
use Symfony\Component\Form\DataTransformerInterface;

class ImageToImageWithUrl implements DataTransformerInterface
{
    
    /*
     * NINCS HASZNALVA !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     */
    
    /**
     * Transforms a Image object to an Image with url
     * @return ImageEntity
     */
    public function transform($image)
    {
        if (null === $image) {
            return;
        }
//        dd($interval);
        $image->setUrl(FileUploader::IMAGES_FOLDER.'/'.FileUploader::CATEGORY_FOLDER.'/'.$image->getFile());
        return $image;
    }

    /**
     * Transforms an Image with url to simple Image object
     * @return ImageEntity
     */
    public function reverseTransform($image)
    {
        if (!$image) {
            return;
        }
        
        return $image;
    }
}