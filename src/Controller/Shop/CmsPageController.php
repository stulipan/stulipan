<?php

namespace App\Controller\Shop;

use App\Entity\CmsPage;
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
        $cmsPage = $this->getDoctrine()->getRepository(CmsPage::class)->findOneBy(['slug' => $slug]);
        if (!$cmsPage || !$cmsPage->isEnabled()) {
            throw $this->createNotFoundException('STUPID: Nincs ilyen CMS oldal!' );
        }
        return $this->render('webshop/site/page-show.html.twig',[
            'page' => $cmsPage
        ]);
    }


}