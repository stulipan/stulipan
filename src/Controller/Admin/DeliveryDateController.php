<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a szamla adatbázistáblából

namespace App\Controller\Admin;

use App\Entity\DeliveryDateInterval;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Form\DeliveryDateTypeFormType;
use App\Form\DeliverySpecialDateFormType;
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
 * @IsGranted("ROLE_DELIVERY_DATES")
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
            'title' => 'Szállítási idősávok',
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
            $title = 'Idősávok módosítása';
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $dateType = $form->getData();
            $dateType->setUpdatedAt(new \DateTime('NOW'));
            $dateType->setCreatedAt(new \DateTime('NOW'));

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
     * @Route("/delivery/specialdate/{page}", name="delivery-special-date-list", requirements={"page"="\d+"})
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

        return $this->render('admin/delivery-special-date-list.html.twig', [
            'items' => $dates,
            'title' => 'Kiemelt szállítási napok',
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($dates),
        ]);
    }

    /**
     * @Route("/delivery/specialdate/edit/{id}", name="delivery-special-date-edit")
     */
    public function editSpecialDate(Request $request, ?DeliverySpecialDate $specialDate, $id = null)
    {
        if (!$specialDate) {
            /**
             * create new
             */
            $specialDate = new DeliverySpecialDate();

            $form = $this->createForm(DeliverySpecialDateFormType::class, $specialDate);
            $title = 'Új szállítási nap';
        } else {
            /**
             * edit existing
             */
            $form = $this->createForm(DeliverySpecialDateFormType::class, $specialDate);
            $title = 'Szállítási nap módosítása';
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $specialDate = $form->getData();
            $specialDate->setUpdatedAt(new \DateTime('NOW'));
            $specialDate->setCreatedAt(new \DateTime('NOW'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($specialDate);
            $entityManager->flush();

            $this->addFlash('success', 'Dátum sikeresen elmentve!');

//            return $this->redirectToRoute('delivery-special-date-list');
        }

        return $this->render('admin/delivery-special-date-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

}