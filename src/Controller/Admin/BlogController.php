<?php

namespace App\Controller\Admin;

use App\Entity\BlogArticle;
use App\Entity\ImageEntity;
use App\Form\Blog\BlogArticleFormType;
use App\Services\FileUploader;
use App\Services\StoreSettings;
use DateTime;
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
 * @IsGranted("ROLE_MANAGE_BLOG")
 * @Route("/admin/blog")
 */
class BlogController extends AbstractController
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
     * @Route("/article/new/", name="blog-article-new")
     */
    public function newBlogArticle(Request $request, FileUploader $fileUploader)
    {
        $form = $this->createForm(BlogArticleFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BlogArticle $article */
            $article = $form->getData();
            $imageId = $form->get('imageId')->getData();

            if (!is_null($imageId)) {
                /** @var ImageEntity $image */
                $image = $this->getDoctrine()->getRepository(ImageEntity::class)->find($imageId);
                $article->setImage($image);
            }

            if ($article->isEnabled() && null === $article->getPublishedAt()) {
                $article->setPublishedAt(new DateTime('NOW'));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            $this->addFlash('success', $this->translator->trans('blog.article.updated-successfully'));

            return $this->redirectToRoute('blog-article-edit', ['id' => $article->getId()]);
        }
        return $this->render('admin/blog/article-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/edit/{id}", name="blog-article-edit")
     */
    public function editBlogArticle(Request $request, BlogArticle $article, FileUploader $fileUploader)
    {
        $form = $this->createForm(BlogArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file
             * Ezt akkor kell használni, amikor NEM vue féle képfeltöltés van
             */
//            $file = $form['imageFile']->getData();
//            if (!is_null($file)) {
//                $newFilename = $fileUploader->uploadFile($file, $article->getImage(), ImageEntity::STORE_IMAGE); //2nd param = null, else deletes prev image
//                $img = new ImageEntity();
//                $img->setFile($newFilename);
//                $article->setImage($img);
//            }

            /**
             * Vue féle képfeltöltéskor, az 'imageId' hidden mező megkapja a feltöltött ImageEntity Id-ját
             */
            $imageId = $form->get('imageId')->getData();
            if (!is_null($imageId)) {
                /** @var ImageEntity $image */
                $image = $this->getDoctrine()->getRepository(ImageEntity::class)->find($imageId);
                $article->setImage($image);
            }

            if ($article->isEnabled() && null === $article->getPublishedAt()) {
                $article->setPublishedAt(new DateTime('NOW'));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            $this->addFlash('success', $this->translator->trans('blog.article.created-successfully'));

            return $this->redirectToRoute('blog-article-edit', ['id' => $article->getId()]);
        }
        return $this->render('admin/blog/article-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/duplicate/{id}", name="blog-article-duplicate", methods={"POST", "GET"})
     */
    public function duplicateBlogArticle(Request $request, BlogArticle $article, ValidatorInterface $validator)
    {
        if (!$article) {
            throw new Error('STUPID: Nincs mit leduplikálni!');
        }

        $newArticle = new BlogArticle();
        $newArticle->setTitle($article->getTitle());
        $newArticle->setContent($article->getContent());
        $newArticle->setEnabled(false);
        $newArticle->setImage($article->getImage());
        $newArticle->setAuthor($article->getAuthor());
        $newArticle->setSeoTitle($article->getSeoTitle());
        $newArticle->setSeoDescription($article->getSeoDescription());
        $newArticle->setExcerpt($article->getExcerpt());

        $violations = $validator->validate($newArticle);
        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->addFlash('danger', $this->translator->trans('blog.article.created-successfully', ['message' => $violation->getMessage()]));
            }
            return $this->redirectToRoute('blog-article-edit', ['id' => $article->getId()]);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($newArticle);
        $em->flush();

        $this->addFlash('success', $this->translator->trans('blog.article.duplicated-successfully'));
        return $this->redirectToRoute('blog-article-edit', ['id' => $newArticle->getId()]);
    }

    /**
     * @Route("/article/list/{page}", name="blog-article-list", requirements={"page"="\d+"})
     */
    public function listBlogArticlesWithPagination($page = 1, StoreSettings $settings)
    {
//        $queryBuilder = $this->getDoctrine()
//            ->getRepository(BlogArticle::class)
//            ->findAllQueryBuilder()
//        ;
        $queryBuilder = $this->getDoctrine()
            ->getRepository(BlogArticle::class)
            ->findAll();

//        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta = new Pagerfanta(new ArrayAdapter($queryBuilder));
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

//        if (!$pages) {
//            $this->addFlash('danger', 'Nem talált egy CMS oldalt sem!');
//        }

        return $this->render('admin/blog/article-list.html.twig', [
            'items' => $pages,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($pages),
        ]);
    }

//    /**
//     * @Route("/navigation/new/", name="cms-navigation-new")
//     */
//    public function newCmsNavigation(Request $request, FileUploader $fileUploader)
//    {
//        $form = $this->createForm(CmsNavigationFormType::class);
//
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $navigation = $form->getData();
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($navigation);
//            $em->flush();
//
//            $this->addFlash('success', 'Oldal sikeresen elmentve!');
//
//            return $this->redirectToRoute('cms-navigation-edit', ['id' => $navigation->getId()]);
//        }
//        return $this->render('admin/cms/navigation-edit.html.twig', [
//            'form' => $form->createView(),
//        ]);
//    }
//
//    /**
//     * @Route("/navigation/edit/{id}", name="cms-navigation-edit")
//     */
//    public function editNavigation(Request $request, CmsNavigation $navigation, FileUploader $fileUploader)
//    {
//        $form = $this->createForm(CmsNavigationFormType::class, $navigation);
//
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $navigation = $form->getData();
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($navigation);
//            $em->flush();
//
//            $this->addFlash('success', 'Oldal sikeresen elmentve!');
//
//            return $this->redirectToRoute('cms-navigation-edit', ['id' => $navigation->getId()]);
//        }
//        return $this->render('admin/cms/navigation-edit.html.twig', [
//            'form' => $form->createView(),
//        ]);
//    }
//
//    /**
//     * @Route("/navigation/list/{page}", name="cms-navigation-list", requirements={"page"="\d+"})
//     */
//    public function listCmsNavigation($page = 1, StoreSettings $settings)
//    {
////        $queryBuilder = $this->getDoctrine()
////            ->getRepository(CmsNavigation::class)
////            ->findAllQueryBuilder()
////        ;
////
////        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
//
//        $navigations = $this->getDoctrine()->getRepository(CmsNavigation::class)->findAll();
//
//        $pagerfanta = new Pagerfanta(new ArrayAdapter($navigations));
//        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
//        //$pagerfanta->setCurrentPage($page);
//
//        try {
//            $pagerfanta->setCurrentPage($page);
//        } catch(NotValidCurrentPageException $e) {
//            throw new NotFoundHttpException();
//        }
//
//        $navigations = [];
//        foreach ($pagerfanta->getCurrentPageResults() as $result) {
//            $navigations[] = $result;
//        }
//
//        if (!$navigations) {
////            throw $this->createNotFoundException(
////                'Nem talált egy szállítmányt sem!'
////            );
//            $this->addFlash('danger', 'Nem talált egy CMS oldalt sem!');
//        }
//
//        return $this->render('admin/cms/navigation-list.html.twig', [
//            'navigations' => $navigations,
//            'paginator' => $pagerfanta,
//            'total' => $pagerfanta->getNbResults(),
//            'count' => count($navigations),
//        ]);
//    }
//
//
//    /**
//     * @Route("/sections/new/", name="cms-section-new")
//     */
//    public function newCmsSection(Request $request)
//    {
//        $form = $this->createForm(CmsSectionFormType::class);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            /** @var CmsSection $section */
//            $section = $form->getData();
//            $section->setBelongsTo($form->get('belongsTo')->getData());
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($section);
//            $em->flush();
//
//            $this->addFlash('success', $this->translator->trans('cms.section.section-saved-successfully'));
//            return $this->redirectToRoute('cms-section-edit', ['id' => $section->getId()]);
//        }
////        dd($form->createView());
//        return $this->render('admin/cms/section-edit.html.twig', [
//            'form' => $form->createView(),
//        ]);
//    }
//
//    /**
//     * @Route("/sections/edit/{id}", name="cms-section-edit")
//     */
//    public function editCmsSection(Request $request, CmsSection $section, $id = null)
//    {
//        $form = $this->createForm(CmsSectionFormType::class, $section);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            /** @var CmsSection $section */
//            $section = $form->getData();
//            $section->setBelongsTo($form->get('belongsTo')->getData());
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($section);
//            $em->flush();
//
//            $this->addFlash('success', $this->translator->trans('cms.section.section-saved-successfully'));
//            return $this->redirectToRoute('cms-section-edit', ['id' => $section->getId()]);
//        }
//        return $this->render('admin/cms/section-edit.html.twig', [
//            'form' => $form->createView(),
//        ]);
//    }
//
//    /**
//     * @Route("/sections/{page}", name="cms-section-list", requirements={"page"="\d+"})
//     */
//    public function listSectionWithPagination($page = 1, StoreSettings $settings)
//    {
//        $queryBuilder = $this->getDoctrine()
//            ->getRepository(CmsSection::class)
//            ->findAllQB()
//        ;
//
//        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
//        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
//
//        try {
//            $pagerfanta->setCurrentPage($page);
//        } catch(NotValidCurrentPageException $e) {
//            throw new NotFoundHttpException();
//        }
//
//        $sections = [];
//        foreach ($pagerfanta->getCurrentPageResults() as $result) {
//            $sections[] = $result;
//        }
//
////        if (!$sections) {
////            $this->addFlash('warning', 'Nem talált egy CMS oldalt sem!');
////        }
//
//        return $this->render('admin/cms/section-list.html.twig', [
//            'sections' => $sections,
//            'paginator' => $pagerfanta,
//            'total' => $pagerfanta->getNbResults(),
//        ]);
//    }
}
