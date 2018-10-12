<?php
// src/Controller/LuckyController.php

namespace App\Controller\Other;

# use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#class LuckyController
class LuckyController extends AbstractController
{
     /**
      * @Route("/lucky/number/",name="luckynumber"))
      */

    public function number()
    {
#        $number = random_int(0, $max);
        $number = 1;

        return $this->render('lucky/number.html.twig', array('number' => $number, ));
    }
}