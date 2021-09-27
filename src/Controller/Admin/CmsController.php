<?php

namespace App\Controller\Admin;

use App\Entity\CmsNavigation;
use App\Entity\CmsPage;
use App\Entity\CmsSection;
use App\Entity\ImageEntity;
use App\Form\Cms\CmsNavigationFormType;
use App\Form\Cms\CmsPageFormType;
use App\Form\Cms\CmsSectionFormType;
use App\Services\FileUploader;
use App\Services\StoreSettings;
use Error;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_MANAGE_CMS")
 * @Route("/admin/cms")
 */
class CmsController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/page/new/", name="cms-page-new")
     */
    public function newCmsPage(Request $request, FileUploader $fileUploader)
    {
        $form = $this->createForm(CmsPageFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $page = $form->getData();
            $imageId = $form->get('imageId')->getData();

//            if (is_null($imageId)) {
//
//                // $file stores the uploaded file which is an UploadedFile object
//                /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
//
//                if ($form['image']) {
//                    $file = $form['imageFile']->getData();
//                }
//                if (!is_null($file)) {
//                    $newFilename = $fileUploader->uploadFile($file, null, ImageEntity::STORE_IMAGE);
//                    $img = new ImageEntity();
//                    $img->setFile($newFilename);
//                    $img->setType(ImageEntity::STORE_IMAGE);
//                    $page->setImage($img);
//                }
//            }
            if (!is_null($imageId)) {
                /** @var ImageEntity $image */
                $image = $this->getDoctrine()->getRepository(ImageEntity::class)->find($imageId);
                $page->setImage($image);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            $this->addFlash('success', 'Oldal sikeresen elmentve!');

            return $this->redirectToRoute('cms-page-edit', ['id' => $page->getId()]);
        }
        return $this->render('admin/cms/page-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/page/edit/{id}", name="cms-page-edit")
     */
    public function editCmsPage(Request $request, CmsPage $page, FileUploader $fileUploader)
    {
        $form = $this->createForm(CmsPageFormType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $page = $form->getData();

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
                $page->setImage($image);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            $this->addFlash('success', 'Oldal sikeresen elmentve!');

            return $this->redirectToRoute('cms-page-edit', ['id' => $page->getId()]);
        }
        return $this->render('admin/cms/page-edit.html.twig', [
            'form' => $form->createView(),
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

        $em = $this->getDoctrine()->getManager();
        $em->persist($newPage);
        $em->flush();

        $this->addFlash('success', 'Oldal sikeresen leduplikálva!');
        return $this->redirectToRoute('cms-page-edit', ['id' => $newPage->getId()]);
    }

    /**
     * @Route("/page/list/{page}", name="cms-page-list", requirements={"page"="\d+"})
     */
    public function listCmsWithPagination($page = 1, StoreSettings $settings)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(CmsPage::class)
            ->findAllQueryBuilder()
        ;

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
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

        return $this->render('admin/cms/page-list.html.twig', [
            'items' => $pages,
            'title' => 'CMS oldalak',
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($pages),
        ]);
    }

    /**
     * @Route("/navigation/new/", name="cms-navigation-new")
     */
    public function newCmsNavigation(Request $request, FileUploader $fileUploader)
    {
        $form = $this->createForm(CmsNavigationFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $navigation = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($navigation);
            $em->flush();

            $this->addFlash('success', 'Oldal sikeresen elmentve!');

            return $this->redirectToRoute('cms-navigation-edit', ['id' => $navigation->getId()]);
        }
        return $this->render('admin/cms/navigation-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/navigation/edit/{id}", name="cms-navigation-edit")
     */
    public function editNavigation(Request $request, CmsNavigation $navigation, FileUploader $fileUploader)
    {
        $form = $this->createForm(CmsNavigationFormType::class, $navigation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $navigation = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($navigation);
            $em->flush();

            $this->addFlash('success', 'Oldal sikeresen elmentve!');

            return $this->redirectToRoute('cms-navigation-edit', ['id' => $navigation->getId()]);
        }
        return $this->render('admin/cms/navigation-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/navigation/list/{page}", name="cms-navigation-list", requirements={"page"="\d+"})
     */
    public function listCmsNavigation($page = 1, StoreSettings $settings)
    {
//        $queryBuilder = $this->getDoctrine()
//            ->getRepository(CmsNavigation::class)
//            ->findAllQueryBuilder()
//        ;
//
//        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));

        $navigations = $this->getDoctrine()->getRepository(CmsNavigation::class)->findAll();

        $pagerfanta = new Pagerfanta(new ArrayAdapter($navigations));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $navigations = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $navigations[] = $result;
        }

        if (!$navigations) {
//            throw $this->createNotFoundException(
//                'Nem talált egy szállítmányt sem!'
//            );
            $this->addFlash('danger', 'Nem talált egy CMS oldalt sem!');
        }

        return $this->render('admin/cms/navigation-list.html.twig', [
            'navigations' => $navigations,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($navigations),
        ]);
    }


    /**
     * @Route("/sections/new/", name="cms-section-new")
     */
    public function newCmsSection(Request $request)
    {
        $form = $this->createForm(CmsSectionFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CmsSection $section */
            $section = $form->getData();
            $section->setBelongsTo($form->get('belongsTo')->getData());

            $em = $this->getDoctrine()->getManager();
            $em->persist($section);
            $em->flush();

            $this->addFlash('success', $this->translator->trans('cms.section.section-saved-successfully'));
            return $this->redirectToRoute('cms-section-edit', ['id' => $section->getId()]);
        }
//        dd($form->createView());
        return $this->render('admin/cms/section-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/sections/edit/{id}", name="cms-section-edit")
     */
    public function editCmsSection(Request $request, CmsSection $section, $id = null)
    {
        $form = $this->createForm(CmsSectionFormType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CmsSection $section */
            $section = $form->getData();
            $section->setBelongsTo($form->get('belongsTo')->getData());

            $em = $this->getDoctrine()->getManager();
            $em->persist($section);
            $em->flush();

            $this->addFlash('success', $this->translator->trans('cms.section.section-saved-successfully'));
            return $this->redirectToRoute('cms-section-edit', ['id' => $section->getId()]);
        }
        return $this->render('admin/cms/section-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/sections/{page}", name="cms-section-list", requirements={"page"="\d+"})
     */
    public function listSectionWithPagination($page = 1, StoreSettings $settings)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(CmsSection::class)
            ->findAllQB()
        ;

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $sections = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $sections[] = $result;
        }

//        if (!$sections) {
//            $this->addFlash('warning', 'Nem talált egy CMS oldalt sem!');
//        }

        return $this->render('admin/cms/section-list.html.twig', [
            'sections' => $sections,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
        ]);
    }
}