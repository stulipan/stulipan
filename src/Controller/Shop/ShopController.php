<?php

namespace App\Controller\Shop;

use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
//use App\Services\HomepageTools;
use App\Form\CartAddItemType;
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
    public function showHomepage()
    {
        $products= $this->getDoctrine()->getRepository(Product::class)->findAll();
        return $this->render('webshop/site/homepage.html.twig', ['products' => $products]);
    }

    /**
     * @Route("/termekek/", name="site-product-listall")
     */
    public function showProductsAll()
    {
        //$products = $this->generateProductList();
        $entityManager = $this->getDoctrine()->getManager();
        $products= $this->getDoctrine()->getRepository(Product::class)->findAll();
        $category = 'Virágküldés';

        if (!$products) {
            //throw $this->createNotFoundException(
            //    'Nem talált egy terméket sem! '  );

            $this->addFlash('success', 'Nem talált egy terméket sem! ');
            return $this->redirectToRoute('site-product-list');
        }

        return $this->render('webshop/site/product-list.html.twig', [
            'products' => $products,
            'category' => $category,
        ]);
    }

    /**
     * @Route("/termekek/{slug}", name="site-product-list")
     */
    public function showProductsByCategory($slug)
    {
        // slug-ból visszafejtem a kategóriát
        $category = $this->getDoctrine()
            ->getRepository(ProductCategory::class)
            ->findOneBy(['slug' => $slug]);

        if (!$category) {
            return $this->redirectToRoute('404');
        } else {
            //$products = $this->generateProductList($category->getId());
            $products = $category->getProducts();
        }
        if (!$products) {
            //throw $this->createNotFoundException(
            //    'Nem talált egy terméket sem! '  );
            $this->addFlash('livSuccess', 'Nem talált egy terméket sem! ');
            return $this->redirectToRoute('site-product-list');
        }

        return $this->render('webshop/site/product-list.html.twig', [
            'products' => $products,
            'category' => $category,
        ]);
    }

    /**
     * @Route("/termek/{id}", name="site-product-show")
     */
    public function showProduct(Product $product)
    {
        if (!$product) {
            throw $this->createNotFoundException('Nem talált egy terméket sem, ezzel az ID-vel');
            //return $this->redirectToRoute('404');
        }
        $form = $this->createForm(CartAddItemType::class, $product, ['subproducts' => $product->getSubproducts()]);

        // render a template and print things with {{ termek.productName }}
        return $this->render('webshop/site/product-show.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
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