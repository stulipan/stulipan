<?php

namespace App\Controller\Shop;

use App\Controller\BaseController;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductStatus;
use App\Form\AddToCart\CartAddItemType;
use App\Model\AddToCartModel;
use App\Model\PreviewContent;
use App\Services\Localization;
use App\Services\StoreSettings;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ProductController extends BaseController //extends AbstractController
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

        $addToCartModel = new AddToCartModel($product->getId(), 1, null);
        $form = $this->createForm(CartAddItemType::class, $addToCartModel, ['product' => $product]);

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
    public function facebookProductCSV(StoreSettings $storeSettings, Localization $localization): Response
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
//                'price' => $p->getSellingPrice().' '.$locale->getCurrencyCode(),
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
            sprintf('facebook-product-feed-%s.csv', (new DateTime('now'))->format($locale->getDateFormat())));
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/google-product-feed.csv", name="site-product-google-product-feed-csv")
     *
     * Template from Facebook here: https://www.facebook.com/business/help/120325381656392?id=725943027795860
     */
    public function googleProductCSV(StoreSettings $storeSettings, Localization $localization): Response
    {
        $locale = $localization->getCurrentLocale();
        $filters = [];
        $filters['status'] = ProductStatus::STATUS_ENABLED;
        $productQuery = $this->getDoctrine()->getRepository(Product::class)->findAllQuery($filters);

        $data = [];
        /** @var Product $p */
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
                    if ($additionalImages) {
                        if (strlen($additionalImages.','.$image->getImageUrl()) <= 2000) {
                            $additionalImages = $additionalImages.','.$image->getImageUrl();
                        }
                        else {
                            break;
                        }
                    }
                    else {
                        $additionalImages = $image->getImageUrl();
                    }
                }
            }

            $data[] = [
                'id' => $p->getSku(),
                'title' => substr($p->getName(), 0, 150),
                'description' => substr($description, 0, 5000),
                'availability' => ($p->getStock() > 0 ? 'in stock' : 'out of stock'),
                'condition' => 'new',
                'price' => $p->getPrice()->getNumericValue().' '.$locale->getCurrencyCode(),
//                'price' => $p->getSellingPrice().' '.$locale->getCurrencyCode(),
                'link' => $this->generateUrl('site-product-show', ['slug' => $p->getSlug()], UrlGenerator::ABSOLUTE_URL),
                'image_link' => $coverImageUrl,
                'additional_image_link' => $additionalImages,
                'brand' => substr($storeSettings->get('store.brand'), 0, 70),
                'google_product_category' => 'Home & Garden > Decor > Seasonal & Holiday Decorations',
            ];
        }
        $context = [
            'csv_headers' => [
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
            ],
            'csv_delimiter' => "\t",
        ];

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
            sprintf('google-product-feed-%s.csv', (new DateTime('now'))->format($locale->getDateFormat())));
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/sendgrid-contacts-igen.csv", name="site-sendgrid-contacts-igen-csv")
     *
     * Template from Facebook here: https://www.facebook.com/business/help/120325381656392?id=725943027795860
     */
    public function sendgridContactsCSVigen(StoreSettings $storeSettings, Localization $localization): Response
    {
        $locale = $localization->getCurrentLocale();
        $filters = [];
        $filters['status'] = ProductStatus::STATUS_ENABLED;
//        $dataQuery = $this->getDoctrine()->getRepository(Customer::class)->findAll();
        $dataQuery = $this->getDoctrine()->getRepository(Customer::class)->findBy(['acceptsMarketing' => true]);

        $data = [];
//        foreach ($dataQuery->getResult() as $p) {
        foreach ($dataQuery as $p) {

            /** @var Order $lastOrder */
            $address = null;
            $lastOrder = $p->getLastOrder();
            if ($lastOrder) {
                $address = $lastOrder->getBillingAddress();
            }

            $data[] = [
                'email' => $p->getEmail(),
                'first_name' => $p->getFirstname(),
                'last_name' => $p->getLastname(),
                'address_line_1' => $address ? $address->getStreet() : '',
                'address_line_2' => '',
                'city' => $address ? $address->getCity() : '',
                'state_province_region' => $address ? $address->getProvince() : '',
                'postal_code' => $address ? $address->getZip() : '',
                'country' => $address ? $address->getCountry()->getName() : '',
            ];
        }
        $context = ['csv_headers' => [
            'email',
            'first_name',
            'last_name',
            'address_line_1',
            'address_line_2',
            'city',
            'state_province_region',
            'postal_code',
            'country',
        ]];

        $encoders = [new CsvEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $csvContent = $serializer->serialize($data, 'csv', $context);

        $response = new Response($csvContent);
        $response->headers->set('Content-Encoding', 'UTF-8');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('sendgrid-contacts-igen-%s.csv', (new DateTime('now'))->format($locale->getDateFormat())));
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/sendgrid-contacts-nem.csv", name="site-sendgrid-contacts-nem-csv")
     *
     * Template from Facebook here: https://www.facebook.com/business/help/120325381656392?id=725943027795860
     */
    public function sendgridContactsCSVnem(StoreSettings $storeSettings, Localization $localization): Response
    {
        $locale = $localization->getCurrentLocale();
        $filters = [];
        $filters['status'] = ProductStatus::STATUS_ENABLED;
        $dataQuery = $this->getDoctrine()->getRepository(Customer::class)->findBy(['acceptsMarketing' => false]);

        $data = [];
//        foreach ($dataQuery->getResult() as $p) {
        foreach ($dataQuery as $p) {

            /** @var Order $lastOrder */
            $address = null;
            $lastOrder = $p->getLastOrder();
            if ($lastOrder) {
                $address = $lastOrder->getBillingAddress();
            }

            $data[] = [
                'email' => $p->getEmail(),
                'first_name' => $p->getFirstname(),
                'last_name' => $p->getLastname(),
                'address_line_1' => $address ? $address->getStreet() : '',
                'address_line_2' => '',
                'city' => $address ? $address->getCity() : '',
                'state_province_region' => $address ? $address->getProvince() : '',
                'postal_code' => $address ? $address->getZip() : '',
                'country' => $address ? $address->getCountry()->getName() : '',
            ];
        }
        $context = ['csv_headers' => [
            'email',
            'first_name',
            'last_name',
            'address_line_1',
            'address_line_2',
            'city',
            'state_province_region',
            'postal_code',
            'country',
        ]];

        $encoders = [new CsvEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $csvContent = $serializer->serialize($data, 'csv', $context);

        $response = new Response($csvContent);
        $response->headers->set('Content-Encoding', 'UTF-8');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('sendgrid-contacts-nem-%s.csv', (new DateTime('now'))->format($locale->getDateFormat())));
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}