<?php

namespace App\Controller\Shop;

use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductStatus;
use App\Form\AddToCart\CartAddItemType;
use App\Model\PreviewContent;
use App\Services\Localization;
use App\Services\StoreSettings;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
        $products= $this->getDoctrine()->getRepository(Product::class)->fetchVisibleProducts();

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
    public function showProductsByCategory($slug, Request $request)
    {
        $previewMode = $request->query->get(PreviewContent::PREVIEW_TOKEN);
        $category = $this->getDoctrine()
            ->getRepository(ProductCategory::class)
            ->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException('HIBA: Missing collection.');
        }

        $canBeRendered = true;

        if (!isset($previewMode) && !$category->isEnabled()) {
            $canBeRendered = false;
        }

        if ($canBeRendered === true) {
            if (isset($previewMode)) {
                $products = $category->getProducts();
            } else {
                $products = $this->getDoctrine()->getRepository(Product::class)->retrieveByCategory($category);
            }
            if (count($products) == 0) {
                $this->addFlash('error', 'Nem talált egy terméket sem! ');
            }

            return $this->render('webshop/site/product-list.html.twig', [
                'products' => $products,
                'category' => $category,
                'previewMode' => isset($previewMode) ? true : false,
            ]);
        }
        throw $this->createNotFoundException('HIBA: Missing collection.');
    }

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
        }
//        $form = $this->createForm(CartAddItemType::class, $product, ['subproducts' => $product->getSubproducts()]);
        $form = $this->createForm(CartAddItemType::class, $product, ['options' => $product->getOptions()]);

        $productStatus = $this->getDoctrine()->getRepository(ProductStatus::class)->findBy(['shortcode' => ProductStatus::STATUS_ENABLED]);
        $recommendedProducts = $this->getDoctrine()->getRepository(Product::class)->retrieveByCategory($product->getCategories()[0]);
        $recommendedCount = count($recommendedProducts);
        if ($recommendedCount <=12 ) {
            $recommendedProducts2 = $this->getDoctrine()->getRepository(Product::class)->findBy(
                ['status' => $productStatus],
                [],
                (12 - $recommendedCount)
            );
            $recommendedProducts = array_merge($recommendedProducts, $recommendedProducts2);
        }
        return $this->render('webshop/site/product-show.html.twig', [
            'product' => $product,
            'recommendedProducts' => $recommendedProducts,
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

    /**
     * @Route("/facebook-product-feed.csv", name="site-product-facebook-product-feed-csv")
     *
     * Template from Facebook here: https://www.facebook.com/business/help/120325381656392?id=725943027795860
     */
    public function downloadProductCSV(StoreSettings $storeSettings, Localization $localization): Response
    {
        $locale = $localization->getCurrentLocale();
        $filters = [];
        $filters['status'] = ProductStatus::STATUS_ENABLED;
        $productQuery = $this->getDoctrine()->getRepository(Product::class)->findAllQuery($filters);

        $data = [];
        foreach ($productQuery->getResult() as $p) {
            $coverImageUrl = null;
            $additionalImages = '';
//            dd($p->getImages());
            $description = $p->getDescription() ? str_replace("\n", '', strip_tags($p->getDescription())) : $p->getName();
            $description = rtrim($description, '.') . '.';
            $description .= ' '.'A Rafina termékei (kopogtatók, asztaldíszek, rózsaboxok, stb.) egytől egyig mind kézzel készült, nem sorozatgyártottak. Előfordulhat, hogy egy-egy termék limitált számban kapható, ezért érdemes a kiszemelt darabot minél hamarabb megrendelni. Az elkészítésük során, minden termékre nagy figyelmet fordítunk és gondosan ügyelünk, hogy a végeredmény minőségi darab legyen. Az ajtódíszek és kopogtatók esetén, tudd, hogy rendelkeznek egy akasztóval, úgy ahogy egy képkeretnél az megszokott. Továbbá, minden kopogtatót, egy dekoratív akasztó szalaggal is ellátunk, hogy kézhezvétel után a kopogtatót azonnal fel tudd helyezni az ajtóra.';

            foreach ($p->getImages() as $index => $image) {
                if ($index == 0) {
                    $coverImageUrl = $p->getImages()[$index]->getImageUrl();
                } else {
                    $additionalImages = $additionalImages ? $additionalImages.','.$image->getImageUrl() : $image->getImageUrl();
                }
            }

            $data[] = [
                'id' => $p->getSku(),
                'title' => $p->getName(),
                'description' => $description,
                'availability' => ($p->getStock() > 0 ? 'in stock' : 'out of stock'),
                'condition' => 'new',
                'price' => $p->getPrice()->getNumericValue().' '.$locale->getCurrencyCode(),
                'link' => $this->generateUrl('site-product-show', ['slug' => $p->getSlug()], UrlGenerator::ABSOLUTE_URL),
                'image_link' => $coverImageUrl,
                'additional_image_link' => $additionalImages,
                'brand' => $storeSettings->get('store.brand'),
                'google_product_category' => 'Home & Garden > Decor > Seasonal & Holiday Decorations',
            ];
        }
        $context = ['csv_headers' => [
            'id',
            'title',
            'description',
            'availability',
            'condition',
            'price',
            'link',
            'image_link',
            'additional_image_link',
            'brand',
            'google_product_category'
        ]];

        $encoders = [new CsvEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $csvContent = $serializer->serialize($data, 'csv', $context);
//        dd($csvContent);

        $response = new Response($csvContent);
        $response->headers->set('Content-Encoding', 'UTF-8');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('products-%s.csv', (new DateTime('now'))->format($locale->getDateFormat())));
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}