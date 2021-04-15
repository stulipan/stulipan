<?php
//ezel tudok adatbázísba írni egy új munkatársat

namespace App\Boltzaras\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

//az alabbibol fogja tudni hogy a Munkatars entity-hez kapcsolodik es azzal dolgozik
use App\Entity\Boltzaras\Munkatars;

/**
 * @Route("/admin")
 */
class MunkatarsController extends AbstractController
{
    /**
     * @Route("/munkatars/new", name="munkatars_new")
     */

    public function index()
    {
        // you can fetch the EntityManager via $this->getDoctrine()
        // or you can add an argument to your action: index(EntityManagerInterface $entityManager)
        $entityManager = $this->getDoctrine()->getManager();

        //itt instaszoljuk az entity-t
        $ember = new Munkatars();      
        $ember->setMunkatarsNeve('Mariann');

        // tell Doctrine you want to (eventually) save the Munkatars (no queries yet)
        $entityManager->persist($ember);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('<html><body>Saved new Munkatars with id '.$ember->getID().' | '.$ember->getMunkatarsNeve().'</body></html>');
    }
}