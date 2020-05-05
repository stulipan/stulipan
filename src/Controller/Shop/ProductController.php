<?php

namespace App\Controller\Shop;

use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Form\CartAddItemType;
use App\Services\Settings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route({
     *     "hu": "/termekek/",
     *     "en": "/products/",
     *      }, name="site-product-listall")
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
     * @Route({
     *     "hu": "/termekkollekcio/{slug}",
     *     "en": "/collection/{slug}",
     *      }, name="site-product-list")
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

//    /**
//     * @Route("/termek/{slug}", name="site-product-show")
//     */
    /**
     * @Route({
     *     "hu": "/termekek/{slug}",
     *     "en": "/products/{slug}"
     * }, name="site-product-show")
     */
    public function showProduct($slug = null) //Product $product
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        if (!$product) {
            throw $this->createNotFoundException('Nem talált egy terméket sem, ezzel az ID-vel');
            //return $this->redirectToRoute('404');
        }
//        $form = $this->createForm(CartAddItemType::class, $product, ['subproducts' => $product->getSubproducts()]);
        $form = $this->createForm(CartAddItemType::class, $product, ['options' => $product->getOptions()]);

        // render a template and print things with {{ termek.productName }}
        return $this->render('webshop/site/product-show.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/products/show/{id}", name="site-product-showById")
     */
    public function showProductById(Request $request, Product $product)
    {
        if (!$product) {
            throw $this->createNotFoundException('Nem talált egy terméket sem, ezzel az ID-vel');
            //return $this->redirectToRoute('404');
        }
        return $this->redirectToRoute('site-product-show', ['slug' => $product->getSlug()]);
    }
}