<?php

namespace App\Controller\Other;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class AzSessionController extends Controller
{
     /**
      * @Route("/session",name="session")
      */

    public function showSession()
    {
        $session = new Session();
        //$session->start();

        // set and get session attributes
        $session->set('name', 'Drak');
        $session->get('name');

        // set flash messages
        $session->getFlashBag()->add('notice', 'Profile updated');

        // retrieve messages
        foreach ($session->getFlashBag()->get('notice', array()) as $message) {
            echo '<div class="flash-notice">' . $message . '</div>';
        }

        return $this->render('webshop/admin/flash.html.twig');
    }

}