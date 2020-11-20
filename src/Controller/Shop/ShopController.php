<?php

namespace App\Controller\Shop;

use App\Entity\Product\Product;
use App\Services\StoreSettings;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ShopController extends AbstractController
{

    /**
     * @Route("/szallitasi-dijak", name="shipping_details")
     */
    public function showShippingInfo()
    {
        return $this->render('webshop/site/shipping_details.html.twig');
    }







//    public function generateProductList($categoryId = NULL)
//    {
//        //$entityManager = $this->getDoctrine()->getManager();
//        //$products= $entityManager->getRepository(Product::class)->findAll();
//
//        if (!$categoryId) {
//            $products = $this->getDoctrine()->getManager()
//                ->getRepository(Product::class)
//                ->findAll();
//
//            $category = 'Virágküldés';
//        }
//        else {
//            $products = $this->getDoctrine()->getManager()
//                ->getRepository(Product::class)
//                ->findByCategory($categoryId);
//
//            $category = $this->getDoctrine()->getManager()
//                ->getRepository(ProductCategory::class)
//                ->find($categoryId);
//        }
//
//        return $products;
//
//    }
    
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
//        $metaTitle = $settings->get('meta-title');
//        dd($metaTitle);
        $products= $this->getDoctrine()->getRepository(Product::class)->findAll();
        return $this->render('webshop/site/homepage.html.twig', ['products' => $products]);
    }

    /**
     * @Route("/rolunk", name="about")
     */
    public function showAbout()
    {

        return $this->render('webshop/site/about.html.twig');
    }

    /**
     * @Route("/404", name="404")
     */
    public function show404()
    {
        return $this->render('webshop/site/404.html.twig');
    }



}