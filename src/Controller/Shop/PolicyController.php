<?php

namespace App\Controller\Shop;

use App\Entity\StorePolicy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PolicyController extends AbstractController
{
    /**
     * @Route({
     *      "hu": "/policies/{slug}",
     *      "en": "/policies/{slug}"
     * }, name="site-policy-show")
     */
    public function showPolicy(Request $request, $slug = null)
    {
        $policy = $this->getDoctrine()->getRepository(StorePolicy::class)->findOneBy(['slug' => $slug]);
        if (!$policy) {
            throw $this->createNotFoundException('STUPID: Nincs ilyen Policy oldal!' );
        }
        return $this->render('webshop/site/page-show.html.twig',[
            'page' => $policy
        ]);
    }


}