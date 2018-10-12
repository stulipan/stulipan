<?php

namespace App\Controller\Shop;

use App\Entity\Product;
use App\Entity\Category;
//use App\Services\HomepageTools;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class ShopController extends AbstractController
{

    /**
     * @Route("/szallitasi-dijak", name="shipping_details")
     */
    public function showShippingInfo()
    {
        return $this->render('webshop/site/shipping_details.html.twig');
    }

    /**
     * @Route("/cart", name="temp_cart")
     */
    public function showCartPage()
    {
        return $this->render('webshop/site/checkout_cart1.html.twig');
    }

    /**
     * @Route("/checkout", name="temp_checkout")
     */
    public function showPaymentPage()
    {
        return $this->render('webshop/site/checkout_payment1.html.twig');
    }

    /**
     * @Route("/sikeres-rendeles", name="site_thankyou")
     */
    public function showThankyouPage()
    {
        return $this->render('webshop/site/checkout_thankyou.html.twig');
    }


//    public function generateProductList($categoryId = NULL)
//    {
//        //$entityManager = $this->getDoctrine()->getManager();
//        //$termek= $entityManager->getRepository(Product::class)->findAll();
//
//        if (!$categoryId) {
//            $termek = $this->getDoctrine()->getManager()
//                ->getRepository(Product::class)
//                ->findAll();
//
//            $kategoria = 'Virágküldés';
//        }
//        else {
//            $termek = $this->getDoctrine()->getManager()
//                ->getRepository(Product::class)
//                ->findByCategory($categoryId);
//
//            $kategoria = $this->getDoctrine()->getManager()
//                ->getRepository(Category::class)
//                ->find($categoryId);
//        }
//
//        return $termek;
//
//    }

    /**
     * @Route("/", name="homepage")
     */
    public function showHomepage()
    {
        $kategoria = $this->getDoctrine()
            ->getRepository(Category::class)
            ->find(2);

        $termek = $kategoria->getProducts();

        return $this->render('webshop/site/homepage.html.twig', ['termekek' => $termek]);
    }

    /**
     * @Route("/termekek/", name="site_product_listall")
     */
    public function showProductsAll()
    {
        //$termek = $this->generateProductList();
        $entityManager = $this->getDoctrine()->getManager();
        $termek= $entityManager->getRepository(Product::class)->findAll();
        $kategoria = 'Virágküldés';

        if (!$termek) {
            //throw $this->createNotFoundException(
            //    'Nem talált egy terméket sem! '  );

            $this->addFlash('success', 'Nem talált egy terméket sem! ');
            return $this->redirectToRoute('site_product_list');
        }

        return $this->render('webshop/site/product_list.html.twig', [
            'termekek' => $termek,
            'kategoria' => $kategoria
        ]);
    }

    /**
     * @Route("/termekek/{slug}", name="site_product_list")
     */
    public function showProductsByCategory($slug)
    {
        // slug-ból visszafejtem a kategóriát
        $kategoria = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy(['slug' => $slug]);

        if (!$kategoria) {
            return $this->redirectToRoute('404');
        } else {

            //$termek = $this->generateProductList($kategoria->getId());
            $termek = $kategoria->getProducts();

        }



        if (!$termek) {
            //throw $this->createNotFoundException(
            //    'Nem talált egy terméket sem! '  );

            $this->addFlash('livSuccess', 'Nem talált egy terméket sem! ');
            return $this->redirectToRoute('site_product_list');
        }


        return $this->render('webshop/site/product_list.html.twig', [
            'termekek' => $termek,
            'kategoria' => $kategoria,
        ]);
    }

    /**
     * @Route("/termek/{id}", name="site_product_show")
     */
    public function showProduct(Product $termek)
    {

        if (!$termek) {
            throw $this->createNotFoundException('Nem talált egy terméket sem, ezzel az ID-vel: '.$id);
            //return $this->redirectToRoute('404');
        }

        // render a template and print things with {{ termek.productName }}
        return $this->render('webshop/site/product_show.html.twig', ['termek' => $termek]);
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