<?php

namespace App\Controller\Shop;

use App\Controller\BaseController;
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

class ProductFeedController extends BaseController
{
     /**
     * @Route("/shoprenter-product-feed.csv", name="site-product-shoprenter-product-feed-csv")
     *
     * Template from Facebook here: https://www.facebook.com/business/help/120325381656392?id=725943027795860
     */
    public function productFeedShoprenter(StoreSettings $storeSettings, Localization $localization): Response
    {
        $locale = $localization->getCurrentLocale();
        $filters = [];
        $filters['status'] = ProductStatus::STATUS_ENABLED;
        dd($this->getDoctrine()->getRepository(Product::class)->fetchVisibleProducts());
        $productQuery = $this->getDoctrine()->getManager()->getRepository(Product::class)->findAll();


        dd($productQuery);

        $data = [];
        /** @var Product $p */
        foreach ($productQuery as $p) {
            $coverImageUrl = null;
            $additionalImages = '';
//            dd($p->getImages());
            $description = $p->getDescription() ? str_replace("\n", '', strip_tags($p->getDescription())) : $p->getName();
            $description = rtrim($description, '.') . '.';
            $description .= ' '.'A Rafina termékei (kopogtatók, asztaldíszek, rózsaboxok, stb.) egytől egyig mind kézzel készült, nem sorozatgyártottak. Előfordulhat, hogy egy-egy termék limitált számban kapható, ezért érdemes a kiszemelt darabot minél hamarabb megrendelni. Az elkészítésük során, minden termékre nagy figyelmet fordítunk és gondosan ügyelünk, hogy a végeredmény minőségi darab legyen. Az ajtódíszek és kopogtatók esetén, tudd, hogy rendelkeznek egy akasztóval, úgy ahogy egy képkeretnél az megszokott. Továbbá, minden kopogtatót, egy dekoratív akasztó szalaggal is ellátunk, hogy kézhezvétel után a kopogtatót azonnal fel tudd helyezni az ajtóra.';

//            foreach ($p->getImages() as $index => $image) {
//                if ($index == 0) {
//                    $coverImageUrl = $p->getImages()[$index]->getImageUrl();
//                } else {
//                    if ($additionalImages) {
//                        if (strlen($additionalImages.','.$image->getImageUrl()) <= 2000) {
//                            $additionalImages = $additionalImages.','.$image->getImageUrl();
//                        }
//                        else {
//                            break;
//                        }
//                    }
//                    else {
//                        $additionalImages = $image->getImageUrl();
//                    }
//                }
//            }

            // Kopograto = 157
            // Csináld magad kopogtatók = 159
            // Rozsaboxok = 158
            // Dekoracio = 160
            // Adventi = 161
            // Asztaldiszek = 162
            // Tavaszi dekoráció = 163
            // Virág = 164

            $kategoriak = [];
            $kategoriak['adventi-koszoruk'] = '161';
            $kategoriak['kopogtatok'] = '157';
            $kategoriak['viragdobozok'] = '158';
            $kategoriak['asztaldiszek'] = '162';
            $kategoriak['tavasz'] = '163';
            $kategoriak['dekoraciok'] = '160';
            $kategoriak['virag'] = '164';
            $kategoriak['csinaldmagad'] = '159';

            $statusz = [];
            $statusz['enabled'] = 1;
            $statusz['unavailable'] = 2;
            $statusz['removed'] = 0;

//            $tipus = [];
//            $tipus['adventi-koszoruk'] = 'Adventi';
//            $tipus['kopogtatok'] = 'Kopogtató';
//            $tipus['viragdobozok'] = 'Virágdoboz';
//            $tipus['asztaldiszek'] = 'Asztaldísz';
//            $tipus['tavasz'] = '163';
//            $tipus['dekoraciok'] = 'Dísztárgy';
//            $tipus['virag'] = 'Virág';
//            $tipus['csinaldmagad'] = 'DIY';

            $kategoriaAzonositok = '';
            $allapot = null;
            foreach ($p->getCategories() as $category) {
                $kategoriaAzonositok .= $kategoriak[$category->getSlug()].',';
            }

            $data[] = [
                'Cikkszám' => $p->getSku(),
                'Kategória azonosító(k)' => $kategoriaAzonositok,
                'Terméknév (hu)' => substr($p->getName(), 0, 150),
                'Rövid leírás (hu)' => substr($description, 0, 5000),
                'Elsődleges termékkép' => 'product/IMG_1977.jpeg',
                'Termékkép alt' => substr($p->getName(), 0, 150),
                'Szállítandó termék(igen (1) v. nem (0))' => 1,
                'Státusz (engedélyezett (1) v. letiltott (0) v. kifutott (2))' => $statusz[$p->getStatus()->getShortcode()],
                'Kiszerelés mennyiség' => 0,
                'Bruttó ár' => $p->getPrice()->getNumericValue(),
                'Alapár' => $p->getPrice()->getNumericValue(),
                'Raktárkészlet 1' => $p->getStock(),
                'Terméktípus' => '',
            ];
        }
        $context = [
            'csv_headers' => [
                'Cikkszám',
                'Kategória azonosító(k)',
                'Terméknév (hu)',
                'Rövid leírás (hu)',
                'Elsődleges termékkép',
                'Termékkép alt',
                'Szállítandó termék(igen (1) v. nem (0))',
                'Státusz (engedélyezett (1) v. letiltott (0) v. kifutott (2))',
                'Kiszerelés mennyiség',
                'Bruttó ár',
                'Alapár',
                'Raktárkészlet 1',
                'Terméktípus',

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
            sprintf('shoprenter-product-feed-%s.csv', (new DateTime('now'))->format($locale->getDateFormat())));
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}