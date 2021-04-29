<?php

namespace App\Controller\Shop;

use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Form\AddToCart\CartAddItemType;
use App\Services\StoreSettings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        $products= $this->getDoctrine()->getRepository(Product::class)->findAllOrdered();

        if (!$products) {
            $this->addFlash('success', 'Nem talált egy terméket sem! ');
        }

        return $this->render('webshop/site/product-list.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route({
     *     "hu": "/kategoria/{slug}",
     *     "en": "/collection/{slug}",
     *      }, name="site-product-list")
     */
    public function showProductsByCategory($slug)
    {
        // slug-ból visszafejtem a kategóriát
        $category = $this->getDoctrine()
            ->getRepository(ProductCategory::class)
            ->findOneBy(['slug' => $slug, 'enabled' => true]);

        if (!$category) {
            throw $this->createNotFoundException('HIBA: Missing collection.');
        } else {
            $products = $category->getProducts();
        }
        if (!$products) {
            $this->addFlash('error', 'Nem talált egy terméket sem! ');
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