<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Product\Product;
use App\Entity\Price;
use App\Entity\Product\ProductBadge;
use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductKind;
use App\Entity\Product\ProductStatus;
use App\Entity\SalesChannel;
use App\Entity\VatRate;
use App\Form\ProductBadgeFormType;
use App\Form\ProductFilterType;
use App\Form\ProductFormType;
use App\Services\FileUploader;
use App\Services\Localization;
use App\Services\StoreSettings;
use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/admin")
 */
class ProductBadgeController extends BaseController
{
     /**
     * @Route("/badges", name="badge-list",
     *     requirements={"page"="\d+"},
     *     )
     */
    public function listBadges(Request $request, $page = 1, StoreSettings $settings)
    {
        $searchTerm = $request->query->get('searchTerm');
        $page = $request->query->get('page') ? $request->query->get('page') : $page;

        $em = $this->getDoctrine();
        $queryBuilder = $em->getRepository(ProductBadge::class)->findAll();

        $pagerfanta = new Pagerfanta(new ArrayAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $badges = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $badges[] = $result;
        }

        return $this->render('admin/product/badge-list.html.twig', [
            'badges' => $badges,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            ]);
    }

    /**
     * @Route("/badges/edit/{id}", name="badge-edit")
     */
    public function editBadge(Request $request, ProductBadge $badge)
    {
        $form = $this->createForm(ProductBadgeFormType::class, $badge, ['__currentLocale' => 'hu']);  //, ['__currentLocale' => 'hu']
        $form->handleRequest($request);

//        dd($form->getData());

        if ($form->isSubmitted() && $form->isValid()) {
            $badge = $form->getData();



            $em = $this->getDoctrine()->getManager();
            $em->persist($badge);
            $em->flush();

            $this->addFlash('success', 'Oldal sikeresen elmentve!');
            return $this->redirectToRoute('badge-edit', ['id' => $badge->getId()]);
        }
        return $this->render('admin/product/badge-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}