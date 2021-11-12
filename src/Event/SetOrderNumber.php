<?php

declare(strict_types=1);

namespace App\Event;

use App\Controller\Utils\GeneralUtils;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\User;
use App\Services\CustomerBuilder;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

/**
 * This has to be configured in services.yaml
 */
class SetOrderNumber
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var User $user
     */
    private $user;

    /**
     * @param CustomerBuilder $customerBuilder
     */
    private $customerBuilder;
    
    public function __construct(EntityManagerInterface $em, Security $security, CustomerBuilder $customerBuilder)
    {
        $this->em = $em;
        $this->user = $security->getUser();
        $this->customerBuilder = $customerBuilder;
    }
    
    /**
     * Creates Order number and sets Order's Customer
     * @param LifeCycleEventArgs $args
     */
    public function postPersist(LifeCycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Order) {
            $customer = $this->customerBuilder->retrieveCustomer($entity);

            // Upon adding an item to the Cart the Order may not have a Customer yet
            if ($customer) {
                $customer->addOrder($entity);
                $entity->setCustomer($customer);
            }

            $today = new DateTime('now');
            $orderNumber =  (string) GeneralUtils::ORDER_NUMBER_FIRST_DIGIT
                .(GeneralUtils::ORDER_NUMBER_RANGE + $entity->getId())
                .$today->format('d');
            $entity->setNumber($orderNumber);

            $args->getEntityManager()->persist($customer);
            $args->getEntityManager()->persist($entity);

            // $this->em->persist($entity);


            // $this->em->persist($customer);
            // nem kell, a 'cascade={"persist"}' miatt a lentiekben
            ///**
            //     * @var Order[]|ArrayCollection|null
            //     *
            //     * ==== One User/Customer has Orders ====
            //     * ==== mappedBy="customer" => az Order entitásban definiált 'customer' attribútumról van szó ====
            //     *
            //     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="customer", cascade={"persist"}, orphanRemoval=true)
            //     * @ORM\JoinColumn(name="id", referencedColumnName="customer_id", nullable=true)
            //     * @ORM\OrderBy({"id" = "ASC"})
            //     * @ Assert\NotBlank(message="Egy felhasználónak több rendelése lehet.")
            //     */
            //    private $orders = [];

            $args->getEntityManager()->flush();
//            $this->em->flush();
        }
    }
}