<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\ImageEntity;
use App\Model\ImageUsage;
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
    //////////////////////////////////////////////////////////////////////////////////////
    ///                                                                                ///
    ///                                   IMAGE API                                    ///
    ///                                                                                ///
    //////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * INPUT: Takes an image uploaded in a 'multipart/form-data' form.
     * OUTPUT: JSON response with <<id, file, alt>> (ImageEntity's fields)
     *
     * @Route("/api/upload/storeImage/", name="api-upload-storeImage", methods={"POST"})
     */
    public function addImage(Request $request, FileUploader $fileUploader, ValidatorInterface $validator) //Image $image,
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
            $newFilename = $fileUploader->uploadFile($file, null, ImageUsage::WEBSITE_IMAGE); //2nd param = null, else deletes prev image
            $image = new ImageEntity();
            $image->setFile($newFilename);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($image);
        $entityManager->flush();
    
        $image->setFile(FileUploader::UPLOADS_IMAGES_FOLDER .'/'. FileUploader::WEBSITE_FOLDER_NAME .'/'. $image->getFile());
        return $this->jsonNormalized(['images' => [$image]]);
    }
    
    /**
     * INPUT: Takes an image uploaded in a 'multipart/form-data' form.
     * OUTPUT: JSON response with <<id, file, alt>> (ImageEntity's fields)
     *
     * @Route("/api/upload/productImage/", name="api-upload-productImage", methods={"POST"})
     */
    public function addProductImage(Request $request, FileUploader $fileUploader, ValidatorInterface $validator)
    {
        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $request->files->get('imageFile');

//        dd($request->files);
//        dd($file);
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
            $newFilename = $fileUploader->uploadFile($file, null, ImageUsage::PRODUCT_IMAGE); //2nd param = null, else deletes prev image
            $image = new ImageEntity();
            $image->setFile($newFilename);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($image);
        $entityManager->flush();

        $image->setFile(FileUploader::UPLOADS_IMAGES_FOLDER .'/'. FileUploader::PRODUCTS_FOLDER_NAME .'/'. $image->getFile());
        return $this->jsonNormalized(['images' => [$image]]);
    }
}