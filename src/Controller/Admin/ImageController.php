<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\ImageEntity;
use App\Services\FileUploader;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Image;

/**
 * @Route("/admin")
 */
class ImageController extends BaseController
{
//    private $targetDirectory;
//
//    public function __construct(string $targetDirectory)
//    {
//        $this->targetDirectory = $targetDirectory;
//    }

    //////////////////////////////////////////////////////////////////////////////////////
    ///                                                                                ///
    ///                                   IMAGE API                                    ///
    ///                                                                                ///
    //////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * INPUT: Takes an image uploaded in a 'multipart/form-data' form.
     * OUTPUT: JSON response with <<id, file, alt>> (ImageEntity's fields)
     *
     * @Route("/api/images/category/", name="api-images-newCategoryImage", methods={"POST"})
     */
    public function addCategoryImage(Request $request, FileUploader $fileUploader, ValidatorInterface $validator) //Image $image,
    {
        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $request->files->get('imageFile');
        
        $violations = $validator->validate(
            $file, [
                new NotBlank(),
                new Image([
                    'maxSize' => '2M',
                ]),
        ]);
        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors['imageFile'] = $violation->getMessage();
            }
            return $this->jsonNormalized(['errors' => $errors], 422);
        }
        
        if (!is_null($file)) {
            $newFilename = $fileUploader->uploadFile($file, null, FileUploader::IMAGE_OF_CATEGORY_TYPE); //2nd param = null, else deletes prev image
            $image = new ImageEntity();
            $image->setFile($newFilename);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($image);
        $entityManager->flush();
    
//        $image->setFile($image->getFile());
        $image->setFile(FileUploader::IMAGES_FOLDER.'/'.FileUploader::CATEGORY_FOLDER.'/'.$image->getFile());
        return $this->jsonNormalized(['images' => [$image]]);
    }
    
    /**
     * INPUT: Takes an image uploaded in a 'multipart/form-data' form.
     * OUTPUT: JSON response with <<id, file, alt>> (ImageEntity's fields)
     *
     * @Route("/api/images/product/", name="api-images-newProductImage", methods={"POST"})
     */
    public function addProductImage(Request $request, FileUploader $fileUploader, ValidatorInterface $validator)
    {
        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $request->files->get('imageFile');
        
        $violations = $validator->validate(
            $file, [
            new NotBlank(),
            new Image([
                'maxSize' => '2M',
            ]),
        ]);
        
        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors['imageFile'] = $violation->getMessage();
            }
            return $this->jsonNormalized(['errors' => $errors], 422);
        }
        
        if (!is_null($file)) {
            $newFilename = $fileUploader->uploadFile($file, null, FileUploader::IMAGE_OF_PRODUCT_TYPE); //2nd param = null, else deletes prev image
            $image = new ImageEntity();
            $image->setFile($newFilename);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($image);
        $entityManager->flush();

//        $image->setFile($image->getFile());
        $image->setFile(FileUploader::IMAGES_FOLDER.'/'.FileUploader::PRODUCT_FOLDER.'/'.$image->getFile());
        return $this->jsonNormalized(['images' => [$image]]);
    }
}