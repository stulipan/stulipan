<?php

namespace App\Services;

use App\Entity\Checkout;
use App\Entity\Customer;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AbandonedOrderRetriever
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Used in:
     *      - App\Security\LoginFormAuthenticator
     *
     * Retrieves the previous unfinished Checkout, if any. In other words, it creates an Abandoned Order.
     *
     * @return Checkout|null
     */
    public function getCheckout(?User $user, ?Checkout $exceptedCheckout): ?Checkout
    {
        $abandoned = null;

        if (!$user) {
            return null;
        }

        if ($user->hasCustomer()) {
            $customer = $user->getCustomer();
        } else {
            $customers = $this->em->getRepository(Customer::class)->findBy(['email' => $user->getEmail()]);
            $customer = $customers ?? $customers->last();
        }

        if (isset($customer) && $customer) {
            $abandoned = $this->em->getRepository(Checkout::class)->findLast([
                'customer' => $customer,
                'except' => $exceptedCheckout
            ]);
        }
        return $abandoned;
    }
}