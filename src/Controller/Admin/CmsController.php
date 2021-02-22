<?php

namespace App\Controller\Admin;

use App\Entity\CmsPage;
use App\Entity\ImageEntity;
use App\Entity\Model\ErrorEntity;
use App\Entity\Product\ProductCategory;
use App\Form\Cms\CmsPageFormType;
use App\Form\ProductCategoryFormType;
use App\Services\FileUploader;
use Error;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @IsGranted("ROLE_MANAGE_CMS")
 * @Route("/admin/cms")
 */
class CmsController extends AbstractController
{
    /**
     * @Route("/page/new/", name="cms-page-new")
     */
    public function newCmsPage(Request $request, FileUploader $fileUploader)
    {
        $form = $this->createForm(CmsPageFormType::class);
        $title = 'Új oldal';

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $page = $form->getData();

            // $file stores the uploaded file which is an UploadedFile object
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */

//            dd($form);
            if ($form['image']) {$file = $form['image']->getData();}
            if (!is_null($file)) {
                $newFilename = $fileUploader->uploadFile($file, null, FileUploader::IMAGE_OF_CATEGORY_TYPE);
                $img = new ImageEntity();
                $img->setFile($newFilename);
                $page->setImage($img);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($page);
            $entityManager->flush();

            $this->addFlash('success', 'Oldal sikeresen elmentve!');

            return $this->redirectToRoute('cms-page-edit', ['id' => $page->getId()]);
        }
        return $this->render('admin/cms/page-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    /**
     * @Route("/page/edit/{id}", name="cms-page-edit")
     */
    public function editCmsPage(Request $request, CmsPage $page, FileUploader $fileUploader)
    {
        $form = $this->createForm(CmsPageFormType::class, $page);
        $title = 'Oldal módosítása';

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $page = $form->getData();

            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file
             * Ezt akkor kell használni, amikor NEM vue féle képfeltöltés van
             */
//            $file = $form['imageFile']->getData();
//            if (!is_null($file)) {
//                $newFilename = $fileUploader->uploadFile($file, $page->getImage(), FileUploader::IMAGE_OF_PAGE_TYPE); //2nd param = null, else deletes prev image
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
                $page->setImage($image);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($page);
            $entityManager->flush();

            $this->addFlash('success', 'Oldal sikeresen elmentve!');

            return $this->redirectToRoute('cms-page-edit', ['id' => $page->getId()]);
        }
        return $this->render('admin/cms/page-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    /**
     * @Route("/page/duplicate/{id}", name="cms-page-duplicate", methods={"POST", "GET"})
     */
    public function duplicateCmsPage(Request $request, CmsPage $page, ValidatorInterface $validator)
    {
        if (!$page) {
            throw new Error('STUPID: Nincs mit leduplikálni!');
        }

        $newPage = new CmsPage();
        $newPage->setName($page->getName());
        $newPage->setContent($page->getContent());
        $newPage->setEnabled($page->getEnabled());
        $newPage->setParent($page->getParent());
        $newPage->setSlug($page->getSlug().'-1');
        $newPage->setImage($page->getImage());

        $violations = $validator->validate($newPage);
        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->addFlash('danger', 'Sikertelen másolás: '.$violation->getMessage());
            }
            return $this->redirectToRoute('cms-page-edit', ['id' => $page->getId()]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($newPage);
        $entityManager->flush();

        $this->addFlash('success', 'Oldal sikeresen leduplikálva!');
        return $this->redirectToRoute('cms-page-edit', ['id' => $newPage->getId()]);
    }

    /**
     * @Route("/page/list/{page}", name="cms-page-list", requirements={"page"="\d+"})
     */
    public function listCmsWithPagination($page = 1)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(CmsPage::class)
            ->findAllQueryBuilder()
        ;

        //Start with $adapter = new DoctrineORMAdapter() since we're using Doctrine, and pass it the query builder.
        //Next, create a $pagerfanta variable set to new Pagerfanta() and pass it the adapter.
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $pages = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $pages[] = $result;
        }

        if (!$pages) {
//            throw $this->createNotFoundException(
//                'Nem talált egy szállítmányt sem!'
//            );
            $this->addFlash('danger', 'Nem talált egy CMS oldalt sem!');
        }

//        $paginatedCollection = new PaginatedCollection($items, $pagerfanta->getNbResults());

        return $this->render('admin/cms/page-list.html.twig', [
            'items' => $pages,
            'title' => 'CMS oldalak',
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($pages),
        ]);
    }
}