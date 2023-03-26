<?php

namespace App\Controller\Shop;

use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{

    /**
     * @ Route("/sitemap.xml", name="site-sitemap")
     * @Route("/sitemap.{_format}", defaults={"_format"="xml"}, requirements={"_format"="xml"}))
     */
    public function showSitemap(Request $request, UrlGeneratorInterface $urlGenerator)
    {
        // We define an array of urls
        $urls = [];
        // We store the hostname of our website
        $hostname = $request->getHost();

        $urls[] = [
            'loc' => $urlGenerator->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'changefreq' => 'weekly',
            'priority' => '1.0'
        ];
        $urls[] = [
            'loc' => $urlGenerator->generate('site-product-listall', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'changefreq' => 'weekly',
            'priority' => '1.0'
        ];
//        $urls[] = ['loc' => $this->get('router')->generate('homepage'), 'changefreq' => 'weekly', 'priority' => '1.0'];
//        $urls[] = ['loc' => $this->get('router')->generate('site-product-listall'), 'changefreq' => 'weekly', 'priority' => '1.0'];

        // Collections
        $collections = $this->getDoctrine()->getRepository(ProductCategory::class)->findBy(['enabled' => true]);

        foreach ($collections as $collection) {
            $urls[] = [
                'loc' => $urlGenerator->generate('site-product-list', ['slug' => $collection->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => 'weekly',
                'priority' => '1.0'
            ];
        }

        // Products
        $products = $this->getDoctrine()->getRepository(Product::class)->findBy(['status' => Product::STATUS_ENABLED]);

        foreach ($products as $product) {
            $urls[] = [
                'loc' => $urlGenerator->generate('site-product-show', ['slug' => $product->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        }

        // Rolunk
        $urls[] = [
            'loc' => $urlGenerator->generate('site-page-show', ['slug' => 'rolunk'], UrlGeneratorInterface::ABSOLUTE_URL),
            'changefreq' => 'weekly',
            'priority' => '0.4'
        ];



        // Once our array is filled, we define the controller response
        $response = new Response();
        $response->headers->set('Content-Type', 'application/xml');

        return $this->render('webshop/site/sitemap.html.twig', [
            'urls' => $urls,
//            'hostname' => $hostname
        ]);
    }
}