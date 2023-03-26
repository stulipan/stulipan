<?php

namespace App\Controller\Shop;

use App\Entity\CmsPage;
use App\Model\PreviewContent;
use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CmsPageController extends AbstractController
{
    /**
     * @Route({
     *      "hu": "/oldalak/{slug}",
     *      "en": "/pages/{slug}"
     * }, name="site-page-show")
     */
    public function showPage(Request $request, $slug = null) //?CmsPage $cmsPage,  - nem kell, mert id-t kap az URL-ben
    {
        $previewMode = $request->query->get(PreviewContent::PREVIEW_TOKEN);
        $cmsPage = $this->getDoctrine()->getRepository(CmsPage::class)->findOneBy(['slug' => $slug]);

        // Nincs CmsPage
        if (!$cmsPage) {
            throw $this->createNotFoundException('STUPID: Nincs ilyen CMS oldal!' );
        }

        // Van CmsPage, megnezzuk a 'preview' es az 'enabled' allapotokat
        $canBeRendered = true;

//        if ($previewMode) {
//            $canBeRendered = true;
//        }

        if (!$previewMode && !$cmsPage->isEnabled()) {
            $canBeRendered = false;
        }

        if ($canBeRendered === true) {
            return $this->render('webshop/site/page-show.html.twig',[
                'page' => $cmsPage
            ]);
        }
        throw $this->createNotFoundException('STUPID: Nincs ilyen CMS oldal!' );
    }
}