<?php

namespace App\Services;

use App\Entity\Product;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
//use Symfony\Bridge\Doctrine\RegistryInterface;



class HomepageTools extends AbstractController
{

    public function generateProductList()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $termek= $entityManager->getRepository(Product::class)->findAll();
        //$termek = $this->getDoctrine()
          //  ->getRepository(Product::class)
            //->findAll();
        //dump($termek); die;
        return $termek;

    }

}