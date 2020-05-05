<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a szamla adatbázistáblából

namespace App\Controller\Admin;

use App\Entity\DeliveryDateInterval;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Form\DeliveryDateTypeFormType;
use App\Form\DeliverySpecialDateFormType;
use DateTime;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

//az alabbibol fogja tudni hogy a InventoryProduct entity-hez kapcsolodik es azzal dolgozik

use App\Pagination\PaginatedCollection;

/**
 * @IsGranted("ROLE_DELIVERY_SETTINGS")
 * @Route("/admin")
 */
class DeliveryDateController extends AbstractController
{
    /**
     * @Route("/delivery/datetype/{page}", name="delivery-date-type-list", requirements={"page"="\d+"})
     */
    public function listDeliveryDateTypesWithPagination($page = 1)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(DeliveryDateType::class)
            ->findAllQueryBuilder()
        ;

        //Start with $adapter = new DoctrineORMAdapter() since we're using Doctrine, and pass it the query builder.
        //Next, create a $pagerfanta variable set to new Pagerfanta() and pass it the adapter.
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $types = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $types[] = $result;
        }

        if (!$types) {
//            throw $this->createNotFoundException(
//                'Nem talált egy szállítmányt sem!'
//            );
            $this->addFlash('danger', 'Nem talált egy dátumtípust sem!');
        }

//        $paginatedCollection = new PaginatedCollection($items, $pagerfanta->getNbResults());

        return $this->render('admin/delivery-date-type-list.html.twig', [
            'items' => $types,
            'title' => 'Idősávok',
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($types),
        ]);
    }

    /**
     * @Route("/delivery/datetype/edit/{id}", name="delivery-date-type-edit")
     */
    public function editDateType(Request $request, ?DeliveryDateType $dateType, $id = null)
    {
        if (!$dateType) {
            /**
             * create new
             */
            $dateType = new DeliveryDateType();
            $interval = new DeliveryDateInterval();
            $interval->setDateType($dateType);
            $dateType->addInterval($interval);

            $interval = new DeliveryDateInterval();
            $interval->setDateType($dateType);
            $dateType->addInterval($interval);

            $form = $this->createForm(DeliveryDateTypeFormType::class, $dateType);
            $title = 'Új idősávcsoport';
        } else {
            /**
             * edit existing
             */
            if (!$dateType->hasIntervals()) {
                $interval = new DeliveryDateInterval();
                $interval->setDateType($dateType);
                $dateType->addInterval($interval);

                $interval = new DeliveryDateInterval();
                $interval->setDateType($dateType);
                $dateType->addInterval($interval);
            }
            $form = $this->createForm(DeliveryDateTypeFormType::class, $dateType);
            $title = 'Idősávok szerkesztése';
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $dateType = $form->getData();
            $dateType->setUpdatedAt(new DateTime('NOW'));
            $dateType->setCreatedAt(new DateTime('NOW'));

            foreach ($dateType->getIntervals() as $i => $interval) {
                if (!$interval->getName() && !$interval->getPrice()) {
                    $dateType->removeInterval($interval);
                }
                if ($interval->getName() && !$interval->getPrice()) {
                    $interval->setPrice(0);
                }
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($dateType);
            $entityManager->flush();

            $this->addFlash('success', 'Dátumtípus sikeresen elmentve!');

            return $this->redirectToRoute('delivery-date-type-list');
        }

        return $this->render('admin/delivery-date-type-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    /**
     * @Route("/delivery/occasion/{page}", name="occasion-list", requirements={"page"="\d+"})
     */
    public function listSpecialDatesWithPagination($page = 1)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(DeliverySpecialDate::class)
            ->findAllQueryBuilder()
        ;

        //Start with $adapter = new DoctrineORMAdapter() since we're using Doctrine, and pass it the query builder.
        //Next, create a $pagerfanta variable set to new Pagerfanta() and pass it the adapter.
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $dates = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $dates[] = $result;
        }

        if (!$dates) {
//            throw $this->createNotFoundException(
//                'Nem talált egy szállítmányt sem!'
//            );
            $this->addFlash('danger', 'Nem talált egy speciális szállítási napot sem!');
        }

//        $paginatedCollection = new PaginatedCollection($items, $pagerfanta->getNbResults());

        return $this->render('admin/occasion-list.html.twig', [
            'items' => $dates,
            'title' => 'Kiemelt napok',
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($dates),
        ]);
    }

    /**
     * @Route("/delivery/occasion/edit/{id}", name="occasion-edit")
     */
    public function editSpecialDate(Request $request, ?DeliverySpecialDate $specialDate, $id = null)
    {
        if (!$specialDate) {
            /**
             * create new
             */
            $specialDate = new DeliverySpecialDate();

            $form = $this->createForm(DeliverySpecialDateFormType::class, $specialDate);
            $title = 'Új kiemelt nap';
        } else {
            /**
             * edit existing
             */
            $form = $this->createForm(DeliverySpecialDateFormType::class, $specialDate);
            $title = 'Kiemelt nap';
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $specialDate = $form->getData();
            $specialDate->setUpdatedAt(new DateTime('NOW'));
            $specialDate->setCreatedAt(new DateTime('NOW'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($specialDate);
            $entityManager->flush();

            $this->addFlash('success', 'Dátum sikeresen elmentve!');

//            return $this->redirectToRoute('occasion-list');
        }

        return $this->render('admin/occasion-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

}