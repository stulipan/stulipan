<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductFormType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



class ProductController extends Controller
{

    /**
     * @Route("/admin/termek/", name="product_list")
     */
    public function listActionOld()
    {
        $termek = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();

        if (!$termek) {
            //throw $this->createNotFoundException('Nem talált egy terméket sem!');

            $this->addFlash('livSuccess', 'Nem talált egy terméket sem! ');
            return $this->redirectToRoute('product_list');
        }

        // render a template, then in the template, print things with {{ termek.munkatars }}
        foreach($termek as $i => $item) {
            // $termek[$i] is same as $item
            $termek[$i]->getCreatedAt()->format('Y-m-d H:i:s');
            $termek[$i]->getUpdatedAt()->format('Y-m-d H:i:s');
        }

        return $this->render('admin/product/product_list.html.twig', ['termekek' => $termek]);
    }


    /**
     * @Route("/admin/termek/new", name="product_new")
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(ProductFormType::class);

        // handleRequest only handles data on POST
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formAdatok = $form->getData();
            $formAdatok->setCreatedAt();
            $formAdatok->setUpdatedAt();
            $formAdatok->setRank(10);

            // $file stores the uploaded PDF file
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $formAdatok->getImage();

            if (!is_null($file)) {

                $fileName = $file->getClientOriginalName();
                //hasznald ezt ha random fajlnevet generalj a feltoltott kepnek:
                //$fileName = $fileTemp.$this->generateUniqueFileName().'.'.$file->guessExtension();


                // moves the file to the directory where images are stored
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );

                // updates the 'image' property to store the image file name
                // instead of its contents
                $formAdatok->setImage($fileName);

            }

            //dump($form->getData());die;

            // you can fetch the EntityManager via $this->getDoctrine()
            // or you can add an argument to your action: index(EntityManagerInterface $entityManager)
            $entityManager = $this->getDoctrine()->getManager();

            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($formAdatok);

            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();

            $this->addFlash('livSuccess', 'Sikeresen elmentetted az új terméket!');

            //return $this->redirectToRoute('product_new');
            return $this->redirectToRoute('product_list');
        }

        return $this->render('admin/product/product_new.html.twig', array(
                'form' => $form->createView(),
            )
        );
    }


    /**
     * @Route("/admin/termek/edit/{id}", name="product_edit")
     */
    public function editAction(Request $request, Product $formAdatok)
    {
        //itt instanszolja a termek formot, amit a $formAdatokkal populálja
        //lásd a második paramétert, ami megmondja a formnak milyen adatokat szórjon bele
        $form = $this->createForm(ProductFormType::class, $formAdatok);

        // handleRequest only handles data on POST
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formAdatok = $form->getData();
            $formAdatok->setUpdatedAt();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($formAdatok);
            $entityManager->flush();

            $this->addFlash('livSuccess', 'Sikeresen módosítottad a terméket!');

            return $this->redirectToRoute('product_list');

        }

        return $this->render('admin/product/product_edit.html.twig', array(
                'form' => $form->createView(),
            )
        );
    }



    /**
     * @Route("/admin/termek/show/{id}", name="product_show")
     */
    public function showAction(Product $termek)
    {
        if (!$termek) {
            throw $this->createNotFoundException(
                'Nem talált egy terméket sem, ezzel az ID-vel: '.$id
            );
        }

        // render a template and print things with {{ termek.productName }}
        $termek->getCreatedAt()->format('Y-m-d H:i:s');
        $termek->getUpdatedAt()->format('Y-m-d H:i:s');
        return $this->render('admin/product/product_list.html.twig', ['termek' => $termek]);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

}