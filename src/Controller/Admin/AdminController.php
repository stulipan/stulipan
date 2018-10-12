<?php

namespace App\Controller\Admin;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpFoundation\Request;


class AdminController extends Controller
{

    /**
     * @Route("/admin", name="admin")
     */
    public function showAdmin()
    {
        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/admin/dashboard/", name="dashboard")
     */
    public function simpleDashboard()
    {

        return $this->render('admin/dashboard.html.twig');
    }
}