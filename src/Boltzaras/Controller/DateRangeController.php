<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a szamla adatbázistáblából

namespace App\Boltzaras\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use App\Form\DateRangeType;


/**
 * @Route("/admin")
 */
class DateRangeController extends AbstractController
{
    /**
     * @Route("/widgets/daterange", name="daterange-widget")
     */
    public function dateRangeForm(Request $request)
    {

        $form = $this->createForm(DateRangeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateRange = $form->getData();
            $splitPieces = explode(" / ",$dateRange['dateRange']);
            $start = $splitPieces[0];
            $end = $splitPieces[1];

            return $this->redirectToRoute('boltzaras_list',[
                'start' => $start,
                'end' => $end,
            ]);
        }

        return $this->render('admin/dateRange-picker-widget.html.twig', [
                'dateRangeForm' => $form->createView(),
            ]
        );
    }
}