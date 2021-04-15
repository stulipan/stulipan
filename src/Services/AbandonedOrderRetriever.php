<?php

namespace App\Services;


use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class AbandonedOrderRetriever
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
     *
     * Retrieves the previous unfinished Order, if any.
     * In other words, it creates an abandoned Order.
     *
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $abandonedOrder = null;

        if ($user) {
            if ($user->hasCustomer()) {
                $customer = $user->getCustomer();
            } else {
                $customers = $this->em->getRepository(Customer::class)->findBy(['email' => $user->getEmail()]);
                $customer = $customers ?? $customers->last();
            }
        }

        if (isset($customer) && $customer) {
            $abandonedOrder = $this->em->getRepository(Order::class)->findLastPartialOrder(['customer' => $customer]);
        }

        return $abandonedOrder;
    }
}