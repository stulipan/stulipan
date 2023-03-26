<?php

namespace App\Controller\Shop;

use App\Entity\Blog;
use App\Entity\BlogArticle;
use App\Entity\Product\Product;
use App\Model\PreviewContent;
use App\Services\StoreSettings;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @Route({
     *     "hu": "/blog",
     *     "en": "/blog",
     *      }, name="site-article-list"), requirements={"page"="\d+"}
     */
    public function showArticles(Request $request, $page = 1, StoreSettings $settings) //$slug,
    {
        $previewMode = $request->query->get(PreviewContent::PREVIEW_TOKEN);
        $blog = $this->getDoctrine()
            ->getRepository(Blog::class)
            ->findOneBy(['slug' => 'blog']);

        if (!$blog) {
            throw $this->createNotFoundException('HIBA: Missing blog.');
        }

        $canBeRendered = true;

//        if (!isset($previewMode)) {
//            $canBeRendered = false;
//        }

        if (true === $canBeRendered) {
            if (isset($previewMode)) {
                $articles = $blog->getArticles();
            } else {
                $articles = $blog->getArticlesPublished();
            }
//            if (count($articles) == 0) {
//                $this->addFlash('error', 'Nem talált egy terméket sem! ');
//            }

            $pagerfanta = new Pagerfanta(new ArrayAdapter($articles->getValues()));
            $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
//            $pagerfanta->setMaxPerPage(2);

            try {
                $pagerfanta->setCurrentPage($page);
            } catch(NotValidCurrentPageException $e) {
                throw new NotFoundHttpException();
            }

            $articles = [];
            foreach ($pagerfanta->getCurrentPageResults() as $result) {
                $articles[] = $result;
            }

//            dd($pagerfanta->getNbResults());

            return $this->render('webshop/site/blog-article-list.html.twig', [
                'items' => $articles,
                'blog' => $blog,
                'previewMode' => isset($previewMode) ? true : false,
                'paginator' => $pagerfanta,
                'total' => $pagerfanta->getNbResults(),
            ]);
        }
        throw $this->createNotFoundException('HIBA: Missing blog.');
    }

    /**
     * @Route({
     *      "hu": "/blog/{slug}",
     *      "en": "/blog/{slug}"
     * }, name="site-article-show")
     */
    public function showBlogArticle(Request $request, $slug = null) //?CmsPage $cmsPage,  - nem kell, mert id-t kap az URL-ben  //$blog = null,
    {
        $previewMode = $request->query->get(PreviewContent::PREVIEW_TOKEN);
        $blog = $this->getDoctrine()->getRepository(Blog::class)->findOneBy(['slug' => 'blog']);
        $article = $this->getDoctrine()->getRepository(BlogArticle::class)->findOneBy(['slug' => $slug]);

        if (!$blog) {
            throw $this->createNotFoundException('STUPID: Nincs ilyen Blog kategória!' );
        }

        if (!$article) {
            throw $this->createNotFoundException('STUPID: Nincs ilyen cikk!' );
        }

        // Van CmsPage, megnezzuk a 'preview' es az 'enabled' allapotokat
        $canBeRendered = true;

        if (!$previewMode && !$article->isEnabled()) {
            $canBeRendered = false;
        }
//      =================

        $products = $this->getDoctrine()->getRepository(Product::class)->fetchVisibleProducts(12);

//      =================
        if ($canBeRendered === true) {
            return $this->render('webshop/site/blog-article-show.html.twig',[
                'article' => $article,
                'products' => $products,
            ]);
        }
        throw $this->createNotFoundException('STUPID: Nincs ilyen cikk!' );
    }


}