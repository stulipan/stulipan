<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\ImageEntity;
use App\Form\ImageUploaderType;
use App\Services\FileUploader;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    /**
     * @Route("/images/{page}", name="image-list", requirements={"page"="\d+"}, methods={"GET"})
     */
    public function listImages($page = 1)
    {
        $limit = 50;
//        $images = $this->getDoctrine()->getRepository(ImageEntity::class)->findBy(
//            ['type' => ImageEntity::STORE_IMAGE],
//            ['id' => 'DESC'],
//            $limit,
//            $limit * ($page-1),
//        );

        $queryBuilder = $this->getDoctrine()->getRepository(ImageEntity::class)
            ->findAllStoreImagesQB();

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($limit);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $items = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $items[] = $result;
        }



        $form = $this->createForm(ImageUploaderType::class, ['imageId' => null]);

        return $this->render('admin/image/image-list.html.twig', [
            'images' => $items,
            'form' => $form->createView(),
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
        ]);
    }

    /**
     *
     * @Route("/images/upload", name="image-upload", methods={"POST"})
     */
    public function uploadStoreImageFromAdmin(Request $request, FileUploader $fileUploader)
    {
        $form = $this->createForm(ImageUploaderType::class, $request->request->get('image_uploader'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageId = $form->get('imageId')->getData();
            if (!is_null($imageId)) {
                $this->addFlash('success', 'Kép sikeresen feltöltve!');
            }
        }
        return $this->redirectToRoute('image-list');
    }


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
    public function addStoreImage(Request $request, FileUploader $fileUploader, ValidatorInterface $validator) //Image $image,
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
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
        
        if (!is_null($file)) {
            $newFilename = $fileUploader->uploadFile($file, null, ImageEntity::STORE_IMAGE); //2nd param = null, else deletes prev image
            $image = new ImageEntity();
            $image->setFile($newFilename);
            $image->setType(ImageEntity::STORE_IMAGE);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($image);
        $em->flush();
    
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
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
        
        if (!is_null($file)) {
            $newFilename = $fileUploader->uploadFile($file, null, ImageEntity::PRODUCT_IMAGE); //2nd param = null, else deletes prev image
            $image = new ImageEntity();
            $image->setFile($newFilename);
            $image->setType(ImageEntity::PRODUCT_IMAGE);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($image);
        $em->flush();

        $image->setFile(FileUploader::UPLOADS_IMAGES_FOLDER .'/'. FileUploader::PRODUCTS_FOLDER_NAME .'/'. $image->getFile());
        return $this->jsonNormalized(['images' => [$image]]);
    }
}