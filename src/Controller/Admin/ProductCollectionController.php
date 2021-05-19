<?php

namespace App\Controller\Admin;

use App\Entity\ImageEntity;
use App\Entity\Product\ProductCategory;
use App\Form\ProductCollectionFormType;
use App\Model\ImageType;
use App\Model\ImageUsage;
use App\Normalizer\DfrObjectSerializer;
use App\Pagination\PaginatedCollection;
use App\Services\FileUploader;
use App\Services\StoreSettings;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin")
 */
class ProductCollectionController extends AbstractController
{

    /**
     * @Route("/collections/{page}", name="collection-list", requirements={"page"="\d+"})
     */
    public function listCollections($page = 1, StoreSettings $settings)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(ProductCategory::class)
            ->findAllQueryBuilder();
        $imageDirectory =  $this->getParameter('category_images_directory');

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $categories = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $categories[] = $result;
        }

        if (!$categories) {
            throw $this->createNotFoundException('Nem talált egy termékkategoriát sem!');
        }

        return $this->render('admin/product/collection-list.html.twig', [
            'collections' => $categories,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($categories),
            'title' => 'Kategóriák',
            'imageDir' => $imageDirectory,
        ]);
    }

    /**
     * @Route("/collections/new/", name="collections-new")
     */
    public function newCollection(Request $request, FileUploader $fileUploader, TranslatorInterface $translator)
    {
        $form = $this->createForm(ProductCollectionFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collection = $form->getData();

            // $file stores the uploaded file which is an UploadedFile object
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */

//            dd($form);
            if ($form['image']) {$file = $form['image']->getData();}
            if (!is_null($file)) {
                $newFilename = $fileUploader->uploadFile($file, null, ImageEntity::STORE_IMAGE);
                $img = new ImageEntity();
                $img->setFile($newFilename);
                $img->setType(ImageEntity::STORE_IMAGE);
                $collection->setImage($img);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($collection);
            $em->flush();

            $this->addFlash('success', $translator->trans('collection.new-collection-created-successfully'));

            return $this->redirectToRoute('collections-edit', ['id' => $collection->getId()]);
        }
        return $this->render('admin/product/collection-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/collections/edit/{id}", name="collections-edit")
     */
    public function editCollection(Request $request, ProductCategory $collection, FileUploader $fileUploader, TranslatorInterface $translator)
    {
        $form = $this->createForm(ProductCollectionFormType::class, $collection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collection = $form->getData();

            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file
             * Ezt akkor kell használni, amikor NEM vue féle képfeltöltés van
             */
//            $file = $form['imageFile']->getData();
//            if (!is_null($file)) {
//                $newFilename = $fileUploader->uploadFile($file, $page->getImage(), ImageEntity::STORE_IMAGE); //2nd param = null, else deletes prev image
//                $img = new ImageEntity();
//                $img->setFile($newFilename);
//                $page->setImage($img);
//            }

            /**
             * Vue féle képfeltöltéskor, az 'imageId' hidden mező megkapja a feltöltött ImageEntity Id-ját
             */
            $imageId = $form->get('imageId')->getData();
            if (!is_null($imageId)) {
                /** @var ImageEntity $image */
                $image = $this->getDoctrine()->getRepository(ImageEntity::class)->find($imageId);
                $collection->setImage($image);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($collection);
            $em->flush();

            $this->addFlash('success', $translator->trans('generic.changes-saved'));

            return $this->redirectToRoute('collections-edit', ['id' => $collection->getId()]);
        }
        return $this->render('admin/product/collection-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/product/newCategory/", name="collection-edit")
     */
    public function newProductCategory(Request $request, FileUploader $fileUploader)
    {
        $form = $this->createForm(ProductCollectionFormType::class);
        $title = 'Új kategória';

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            
            // $file stores the uploaded file which is an UploadedFile object
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */

            $file = $form['imageFile']->getData();
            if (!is_null($file)) {
                $newFilename = $fileUploader->uploadFile($file, null, ImageEntity::STORE_IMAGE);
                $img = new ImageEntity();
                $img->setFile($newFilename);
                $category->setImage($img);
//                $category->setImage($newFilename);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Kategória sikeresen elmentve!');

            return $this->redirectToRoute('product-listCategories');
        }
        return $this->render('admin/product/product-category-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    /**
     * @Route("/product/editCategory/{id}", name="product-editCategory")
     */
    public function editProductCategory(Request $request, ProductCategory $category, FileUploader $fileUploader)
    {
        $form = $this->createForm(ProductCollectionFormType::class, $category);
        $title = 'Kategória módosítása';

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $form['imageFile']->getData();

            if (!is_null($file)) {
                $newFilename = $fileUploader->uploadFile($file, $category->getImage(), ImageEntity::STORE_IMAGE); //2nd param = null, else deletes prev image
                $img = new ImageEntity();
                $img->setFile($newFilename);
                $category->setImage($img);
//                $category->setImage($newFilename);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Kategória sikeresen elmentve!');

            return $this->redirectToRoute('product-listCategories');
        }
        return $this->render('admin/product/product-category-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    /**
     * @Route("/product/category/{page}", name="product-listCategories", requirements={"page"="\d+"})
     */
    public function listProductCategories($page = 1, StoreSettings $settings)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(ProductCategory::class)
            ->findAllQueryBuilder();
        $imageDirectory =  $this->getParameter('category_images_directory');

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $categories = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $categories[] = $result;
        }

        if (!$categories) {
            throw $this->createNotFoundException(
                'Nem talált egy termékkategoriát sem!'
            );
        }

//        $paginatedCollection = new PaginatedCollection($categories, $pagerfanta->getNbResults());

        // render a template, then in the template, print things with {{ szamla.munkatars }}
        return $this->render('admin/product/category-list-react.html.twig', [
            'categories' => $categories,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($categories),
            'imageDir' => $imageDirectory,
        ]);
    }

    /**
     * @Route("/product/deleteCategory/{id}", name="product-deleteCategory", methods={"GET"})
     */
    public function deleteProductCategory(ProductCategory $category)

    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('product-listCategories');
    }
}