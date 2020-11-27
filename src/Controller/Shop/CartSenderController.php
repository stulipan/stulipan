<?php

namespace App\Controller\Shop;

use App\Entity\Address;
use App\Entity\Geo\GeoCountry;
use App\Entity\Order;
use App\Entity\OrderBuilder;
use App\Entity\Sender;
use App\Form\SenderType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Sodium\add;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CartSenderController extends AbstractController
{
    /**
     * @var OrderBuilder
     */
    private $orderBuilder;

    private $errorMessage = 'Unauthorized access: Request must come through XmlHttpRequest and user must be logged in!';
    public function __construct(OrderBuilder $orderBuilder)
    {
        $this->orderBuilder = $orderBuilder;
    }

    /**
     * Handles the Sender form. It is used to create and submit the form from JS.
     *
     * @Route("/cart/editSender/{id}", name="cart-editSender")
     */
    public function editSenderForm(Request $request, ?Sender $sender, $id = null, ValidatorInterface $validator)
    {
        if ($request->isXmlHttpRequest() && $this->isGranted("IS_AUTHENTICATED_FULLY")) {
            $orderBuilder = $this->orderBuilder;
            $customer = $orderBuilder->getCurrentOrder()->getCustomer() ? $orderBuilder->getCurrentOrder()->getCustomer() : null;
            if (!$sender) {
                $orderBuilder->removeSender($orderBuilder->getCurrentOrder()->getSender());  // torli a mar elmentett Sendert

                $sender = new Sender();
                if ($customer) {
                    //                $sender->setName($customer->getFullname());
                    //                $sender->setFirstname($customer->getFirstname());
                    //                $sender->setLastname($customer->getLastname());
                    //                $sender->setPhone($customer->getPhone());
                    $sender->setCustomer($customer); // kitoltom a step1-ben megadott nevvel.
                }
                // Ezzel mondom meg neki, mi legyen a default country ertek (azaz Magyarorszag)
                $address = new Address();
                $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
                $sender->setAddress($address);
                $form = $this->createForm(SenderType::class, $sender);
            } else {
                $form = $this->createForm(SenderType::class, $sender);
            }

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //elobb elmentem a sender formadatokat a Sender tablaba
                $sender = $form->getData();
                if ($customer) {
                    $sender->setCustomer($customer); // a feladót egy Customerhez kotjuk
                }
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($sender);
                $entityManager->flush();

                $orderBuilder->setSender($sender);

                /** If AJAX request, returns the current Sender */
                if ($request->isXmlHttpRequest()) {
                    //                return $this->redirectToRoute('cart-getSender');
                    return $this->render('webshop/cart/sender_form.html.twig', [
                        'order' => $orderBuilder->getCurrentOrder(),
                        'senderForm' => $form->createView(),
                    ]);
                }
            }
            /**
             * Renders form with errors
             * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
             * (!?, there is a validation error)
             */
            if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
                $html = $this->renderView('webshop/cart/sender_form.html.twig', [
                    'order' => $orderBuilder->getCurrentOrder(),
                    'senderForm' => $form->createView(),
                ]);
                return new Response($html, 400);

            }
            /**
             * Renders form initially with data
             */
            return $this->render('webshop/cart/sender_form.html.twig', [
                'order' => $orderBuilder->getCurrentOrder(),
                'senderForm' => $form->createView(),
            ]);
        } else {
            return new JsonResponse(['error' => $this->erroMessage], Response::HTTP_UNAUTHORIZED); // 401
        }
        throw $this->createAccessDeniedException("You don't have access to this page!");
    }

    /**
     * Gets the list of Senders. Handles 2 situations, see getRecipients()
     *
     * @Route("/cart/getSenders", name="cart-getSenders")
     */
    public function getSenders(Request $request)
    {
        if ($request->isXmlHttpRequest() && $this->isGranted("IS_AUTHENTICATED_FULLY")) {
            $orderBuilder = $this->orderBuilder;
            /** If the Order has a Customer, returns the list of the customer's Senders */
            if ($orderBuilder->getCurrentOrder()->getCustomer()) {
                $senders = $orderBuilder->getCurrentOrder()->getCustomer()->getSenders();
            } /** Else, simply returns the Sender saved already in the Order (This is the Guest Checkout scenario) */
            else {
                $senders = new ArrayCollection();
                /** Verifies if a Sender exists. If not return the Sender form. */
                if ($orderBuilder->hasSender()) {
                    $senders->add($orderBuilder->getCurrentOrder()->getSender());
                }
            }
            if (!$senders || $senders->isEmpty()) {
                $sender = new Sender();
                // Ezzel mondom meg neki, mi legyen a default country ertek (azaz Magyarorszag)
                $address = new Address();
                $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
                $sender->setAddress($address);
                $form = $this->createForm(SenderType::class, $sender);

                return $this->render('webshop/cart/sender_form.html.twig', [
                    'order' => $orderBuilder->getCurrentOrder(),
                    'senderForm' => $form->createView(),
                ]);
            }

            return $this->render('webshop/cart/sender_list.html.twig', [
                'senders' => $senders,
                'selectedSender' => $orderBuilder->getCurrentOrder()->getSender() ? $orderBuilder->getCurrentOrder()->getSender()->getId() : null,
            ]);
        } else {
            return new JsonResponse(['error' => $this->erroMessage], Response::HTTP_UNAUTHORIZED); // 401
        }
        throw $this->createAccessDeniedException("You don't have access to this page!");
    }

    /**
     * @Route("/cart/getSender", name="cart-getSender")
     */
    public function getSender(Request $request)
    {
        $orderBuilder = $this->orderBuilder;
        if ($orderBuilder->hasSender()) {
            $sender = $orderBuilder->getCurrentOrder()->getSender();
        } else {
            $sender = new Sender();
            // Ezzel mondom meg neki, mi legyen a default country ertek (azaz Magyarorszag)
            $address = new Address();
            $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $sender->setAddress($address);
            $form = $this->createForm(SenderType::class, $sender);

            return $this->render('webshop/cart/sender_form.html.twig', [
                'order' => $orderBuilder->getCurrentOrder(),
                'senderForm' => $form->createView(),
            ]);
        }
        return $this->render('webshop/cart/sender-current.html.twig', [
            'senderForm' => $this->createForm(SenderType::class, $sender)->createView(),
            'selectedSender' => $orderBuilder->getCurrentOrder()->getSender() ? $orderBuilder->getCurrentOrder()->getSender()->getId() : null,
        ]);
//        return $this->render('webshop/cart/sender-current.html.twig', [
//            'sender' => $sender,
//            'selectedSender' => $orderBuilder->getCurrentOrder()->getSender() ? $orderBuilder->getCurrentOrder()->getSender()->getId() : null,
//        ]);
    }

    /**
     * Picks a Sender from the sender list and assigns it to the current Order.
     * It is used in JS.
     *
     * @Route("/cart/pickSender/{id}", name="cart-pickSender")
     */
    public function pickSender(Request $request, Sender $sender)
    {
        if ($request->isXmlHttpRequest() && $this->isGranted("IS_AUTHENTICATED_FULLY")) {
            if ($this->getUser() === $sender->getCustomer()) {
                $orderBuilder = $this->orderBuilder;
                $orderBuilder->setSender($sender);

                //            return $this->render('webshop/cart/sender-current.html.twig', [
                //                'sender' => $sender,
                //                'selectedSender' => $orderBuilder->getCurrentOrder()->getSender() ? $orderBuilder->getCurrentOrder()->getSender()->getId() : null,
                //            ]);
                return $this->render('webshop/cart/sender_form.html.twig', [
                    'senderForm' => $this->createForm(SenderType::class, $sender)->createView(),
                    'selectedSender' => $orderBuilder->getCurrentOrder()->getSender() ? $orderBuilder->getCurrentOrder()->getSender()->getId() : null,
                ]);
            }
        } else {
            return new JsonResponse(['error' => $this->erroMessage], Response::HTTP_UNAUTHORIZED); // 401
        }
        throw $this->createAccessDeniedException("You don't have access to this page!");
    }

    /**
     * Deletes a Sender. Used in JS.
     *
     * @Route("/cart/deleteSender/{id}", name="cart-deleteSender", methods={"DELETE", "GET"})
     */
    public function deleteSender(Request $request, ?Sender $sender, $id = null)
    {
        if ($request->isXmlHttpRequest() && $this->isGranted("IS_AUTHENTICATED_FULLY")) {
            // If User from session is equal to User in Recipient
            if ($this->getUser() === $sender->getCustomer()) {
                $this->orderBuilder->getCustomer()->removeSender($sender);
                if ($this->orderBuilder->getCurrentOrder()->getSender() == $sender) {
                    $this->orderBuilder->removeSender();
                }
                //            $this->orderBuilder->setFallbackSender();
                $this->getDoctrine()->getManager()->remove($sender);
                $this->getDoctrine()->getManager()->flush();
                return $this->redirectToRoute('cart-getSender');
            }
        } else {
            return new JsonResponse(['error' => $this->erroMessage], Response::HTTP_UNAUTHORIZED); // 401
        }
        throw $this->createAccessDeniedException("You don't have access to this page!");
    }
}