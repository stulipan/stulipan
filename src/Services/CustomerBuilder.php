<?php

namespace App\Services;


use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CustomerBuilder
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Security $security
     */
    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * Used in:
     *      - App\Security\LoginFormAuthenticator
     *      - App\Event\SetOrderNumber
     *
     * Builds or retrieves Customer object.
     *
     * It verifies if a Customer is present in a logged in user or a given order.
     * Otherwise creates a new Customer object.
     *
     * @param Order|null $order
     * @return Customer|null
     */
    public function retrieveCustomer(?Order $order): ?Customer
    {
        $user = $this->security->getUser();
        /** @var Customer $customerInUser */
        $customerInUser = null;
        $customerInOrder = $order ? $order->getCustomer() : null;
        $customer = null;


        /** After login, add current user as Customer (to the current Order and to the current Recipient also) */
        if ($user) {
            if ($user->hasCustomer()) {
                $customerInUser = $user->getCustomer();
            }
        }

        if ($user && $customerInUser && $customerInOrder) {
            $customerInUser->setPhone($customerInOrder->getPhone());
//            $customerInUser->setFirstname($customerInOrder->getFirstname());
//            $customerInUser->setLastname($customerInOrder->getLastname());

            if (!$customerInUser->isAcceptsMarketing() && $customerInOrder->isAcceptsMarketing()) {
                $customerInUser->setAcceptsMarketing($customerInOrder->isAcceptsMarketing());
                $customerInUser->setAcceptsMarketingUpdatedAt($customerInOrder->getAcceptsMarketingUpdatedAt());
                $customerInUser->setMarketingOptinLevel($customerInOrder->getMarketingOptinLevel());
            }
            $customer = $customerInUser;

            // Remove customerInOrder from db
            $order->setCustomer(null);
            $this->em->remove($customerInOrder);
//            $this->em->flush();
        }

        if ($user && $customerInUser && !$customerInOrder) {
            $customer = $customerInUser;
        }

        if ($user && !$customerInUser && $customerInOrder) {
            $customerInOrder->setUser($user);
            // update Customer name with the name in User
            $customerInOrder->setFirstname($user->getFirstname());
            $customerInOrder->setLastname($user->getLastname());
            $customer =  $customerInOrder;
        }

        if (!$user && $customerInOrder) {
            $customer = $customerInOrder;
        }

        if (!isset($customer)) {
            if ($user) {
                $customerWithEmail = $this->em->getRepository(Customer::class)->findOneBy(['email' => $user->getEmail()]);
//                dd($customerWithEmail);
                if ($customerWithEmail) {
                    $customer = $customerWithEmail;
                } else {
                    $customer = new Customer();
                    $customer->setUser($user);
                    $customer->setEmail($user->getEmail());
                    $customer->setFirstname($user->getFirstname());
                    $customer->setLastname($user->getLastname());
                }
            }
        }

//        dd($customer);
        return $customer;
    }
}