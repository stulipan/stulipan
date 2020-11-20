<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a szamla adatbázistáblából

namespace App\Boltzaras\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

use App\Form\DateRangeType;
use App\Entity\DateRange;

use App\Pagination\PaginatedCollection;

/**
 * @Route("/admin")
 */
class DateRangeController extends AbstractController
{

    /**
     * @Route("/widgets/daterange", name="daterange-widget")
     */
    public function dateRangeForm(Request $request) //, ?DateRange $dateRange
    {

        $form = $this->createForm(DateRangeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateRange = $form->getData();
            $splitPieces = explode(" / ",$dateRange['dateRange']);
            $start = $splitPieces[0];
            $end = $splitPieces[1];

//            // str_replace kicseréli 10/31/2018 => 10-31-2018
//            $start = str_replace("/","-",$splitPieces[0]);
//            $end = str_replace("/","-",$splitPieces[1]);
            return $this->redirectToRoute('boltzaras_list',[
                'start' => $start,
                'end' => $end,
            ]);
        }

        return $this->render('admin/dateRange-picker-widget.html.twig', [
                'dateRangeForm' => $form->createView(),
            ]
        );
//        $form = $this->createForm(DateRangeType::class, $dateRange);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $dateRange = $form->getData();
//
//            return $this->redirectToRoute('boltzaras_list',[
//                'start' => $dateRange->getStart()->format('Y-m-d h:m'),
//                'end' => $dateRange->getEnd()->format('Y-m-d h:m'),
//            ]);
//        }
//
//        return $this->render('admin/dateRange-picker-widget.html.twig', [
//                'form' => $form->createView(),
//            ]
//        );
    }

    
}