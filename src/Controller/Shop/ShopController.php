<?php

namespace App\Controller\Shop;

use App\Entity\CmsPage;
use App\Entity\CmsPage4Twig;
use App\Entity\Product\Product;
use App\Services\StoreSettings;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ShopController extends AbstractController
{
    /**
     * @ Route("/", name="index")
     */
    public function index()
    {
        return $this->redirectToRoute('homepage');
    }
    /**
     * @Route("/", name="homepage")
     */
    public function showHomepage(StoreSettings $settings)
    {
        $products= $this->getDoctrine()->getRepository(Product::class)->findAllOrdered();
        return $this->render('webshop/site/homepage.html.twig', ['products' => $products]);
    }


    /**
     * @Route("/404", name="404")
     */
    public function show404()
    {
        return $this->render('webshop/site/404.html.twig');
    }



}