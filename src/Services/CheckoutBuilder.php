<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Checkout;
use App\Entity\ClientDetails;
use App\Entity\Customer;
use App\Entity\Model\CustomerBasic;
use App\Entity\OrderLog;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Event\StoreEvent;
use App\Event\OrderEvent;
use DateTime;
use App\Entity\PaymentMethod;
use App\Entity\ShippingMethod;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckoutBuilder
{
    /**
     * @var StoreSessionStorage
     */
    private $storage;

    private $user;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var Checkout
     */
    private $checkout;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    private $translator;

    private $storeSettings;

    public function __construct(StoreSessionStorage $storage, EntityManagerInterface $entityManager, Security $security,
                                EventDispatcherInterface $eventDispatcher, TranslatorInterface $translator, StoreSettings $storeSettings)
    {
        $this->storage = $storage;
        $this->user = $security->getUser();
        $this->em = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->storeSettings = $storeSettings;

        $this->checkout = $this->getCurrent();
        $this->customer = $this->getCustomer();
    }

    /**
     * @return Checkout
     */
    public function getCurrent(): Checkout
    {
        $checkout = $this->storage->getCheckoutById();
        if ($checkout !== null) {
            return $checkout;
        }

        $newCheckout = new Checkout;
        return $newCheckout;
    }

    public function initializeCheckout()
    {
        $checkoutBeforeId = $this->checkout->getId();

        if ($checkoutBeforeId === null) {
            $cart = $this->storage->getCartById();

            $this->runEvent(StoreEvent::IMPORT_ITEMS_FROM_CART);

            $this->checkout->setCart($cart);
            if (!$this->checkout->getToken()) {
                $this->checkout->setToken((Uuid::v4())->toRfc4122());
            }

            $this->em->persist($this->checkout);
            $this->em->flush();

            // Run events
            $this->runEvent(StoreEvent::CHECKOUT_CREATE);
        }

        // Note: When Cart items are updated, Checkout is updated automatically through 'StoreEvent::CART_UPDATE' event
    }

    public function getCustomer()
    {
        $customer = $this->checkout->getCustomer();
        if ($customer !== null) {
            return $customer;
        }
        return new Customer();
    }

    public function setCustomer(Customer $customer): void
    {
        $checkoutBeforeId = $this->checkout->getId();
        if ($checkoutBeforeId) {
            $customer->addCheckout($this->checkout);
            $this->checkout->setCustomer($customer);
            $this->checkout->setEmail($customer->getEmail());
//            $this->checkout->setPhone($customer->getPhone());

            $this->em->persist($this->checkout);
            $this->em->flush();
        }
    }

//    public function setCustomer1(): void
//    {
//        $customerInCheckout = $this->checkout->getCustomer();
//
//        // No customer in checkout AND (yes) customer in user
//        if ($customerInCheckout === null && ($this->user && $this->user->getCustomer() !== null)) {
//            $this->checkout->setCustomer($this->user->getCustomer());
//            $this->em->persist($this->checkout);
//            $this->em->flush();
//        }
//
//        // No customer in checkout AND no customer in user
//        if ($customerInCheckout === null && ($this->user && $this->user->getCustomer() === null)) {
//            $customer = new Customer();
//            $customer->setEmail($this->user->getEmail());
//            $customer->setPhone($this->user->getPhone());
//            $customer->setFirstname($this->user->getFirstname());
//            $customer->setLastname($this->user->getLastname());
//            $customer->setUser($this->user);
//
//            $this->checkout->setCustomer($customer);
//            $this->em->persist($customer);
//            $this->em->persist($this->checkout);
//            $this->em->flush();
//        }
//
//        // No customer in checkout AND no user
//        if ($customerInCheckout === null && ($this->user === null)) {
//            $customer = new Customer();
//            $customer->setEmail($this->user->getEmail());
//            $customer->setPhone($this->user->getPhone());
//            $customer->setFirstname($this->user->getFirstname());
//            $customer->setLastname($this->user->getLastname());
//            $customer->setUser($this->user);
//        }
//
//        if ($customerInCheckout === null) {
//            $customer = new Customer();
//            $customer->setEmail($this->checkout->getEmail());
//            $customer->setPhone($this->checkout->getPhone());
//            $customer->setFirstname($this->checkout->getRecipient()->getFirstname());
//            $customer->setLastname($this->checkout->getRecipient()->getLastname());
//            if ($this->user) {
//                $customer->setUser($this->user);
//            }
//            $this->checkout->setCustomer($customer);
//            $this->em->persist($customer);
//            $this->em->persist($this->checkout);
//            $this->em->flush();
//        }
//
//        $this->customer->setPhone($this->checkout->getPhone());
//        $this->customer->setFirstname($this->checkout->getRecipient()->getFirstname());
//        $this->customer->setLastname($this->checkout->getRecipient()->getLastname());
//        if ($this->user) {
//            $this->customer->setUser($this->user);
//        }
//        $this->em->persist($this->customer);
//        $this->em->flush();
//    }

    /**
     * This is used upon submitting the CustomerBasicType form in Step1
     * @param CustomerBasic $customerBasic
     */
    public function setCustomerBasic(CustomerBasic $customerBasic)
    {
        $customerBeforeId = $this->checkout->getCustomer();

        if ($this->checkout->getEmail() != $customerBasic->getEmail()) {
            $this->checkout->setEmail($customerBasic->getEmail());

            $this->em->persist($this->checkout);
            $this->em->flush();
        }

        if ($customerBeforeId === null) {
            $this->runEvent(StoreEvent::CUSTOMER_CREATE, ['acceptsMarketing' => $customerBasic->isAcceptsMarketing()]);
        } else {
            $this->runEvent(StoreEvent::CUSTOMER_UPDATE, ['acceptsMarketing' => $customerBasic->isAcceptsMarketing()]);
        }
    }

    /**
     * @param string|null $deliveryDate
     * @param null|string $deliveryInterval
     */
    public function setDeliveryDate(string $deliveryDate = null, string $deliveryInterval = null): void
    {
        $isPersisting = false;
        if ($deliveryDate) {
            $deliveryDate = DateTime::createFromFormat('!Y-m-d', $deliveryDate);
            /**
             * If $deliveryDate equals date in database
             */
            if ($this->checkout->getDeliveryDate() && $this->checkout->getDeliveryDate() === $deliveryDate->format('Y-m-d')) {
                // do nothing
            } /**
             * Else update date in database and remove existing interval
             */
            else {
                $this->checkout->setDeliveryDate($deliveryDate);
                $this->checkout->setDeliveryInterval(null);
                $isPersisting = true;
            }
        }
        if ($deliveryInterval) {
            if ($this->checkout->getDeliveryInterval() && $this->checkout->getDeliveryInterval() === $deliveryInterval) {
                // do nothing
            } else {
                $this->checkout->setDeliveryInterval($deliveryInterval);
                $isPersisting = true;
            }
        }

        if ($isPersisting) {
            $this->em->persist($this->checkout);
            $this->em->flush();
        }
    }

    /**
     * @param float $price
     */
    public function setSchedulingPrice(?float $price) {
        $this->checkout->setSchedulingPrice($price);

        $this->em->persist($this->checkout);
        $this->em->flush();
    }

    public function setRecipient(Recipient $recipient, ?bool $isLoggedIn, ?bool $isAbandoned = false): void
    {
        $isGuestCheckout = !$isLoggedIn;

        if ($isLoggedIn) {
            $recipient->setUser($this->user);
            $recipients = $this->user->getRecipients();

            $shortlist = $recipients->filter($this->equalsRecipient($recipient));
            if ($shortlist->isEmpty()) {
                $this->checkout->setRecipient($recipient);
                $this->em->persist($recipient);
            } else {
                $matched = $shortlist->last();
                $this->checkout->setRecipient($matched);
            }
//            $this->checkout->setPhone($this->checkout->getRecipient()->getPhone());
            $this->em->persist($this->checkout);
            $this->em->flush();
        }

        if ($isGuestCheckout) {
            $this->checkout->setRecipient($recipient);
//            $this->checkout->setPhone($this->checkout->getRecipient()->getPhone());
            $this->em->persist($recipient);
            $this->em->persist($this->checkout);
            $this->em->flush();
        }

        if ($isAbandoned) {
            // Previously was Logged-in
            if ($recipient->getUser()) {
                $recipients = $recipient->getUser()->getRecipients();

                $shortlist = $recipients->filter($this->equalsRecipient($recipient));
                if ($shortlist->isEmpty()) {
                    $this->checkout->setRecipient($recipient);
                    $this->em->persist($recipient);
                } else {
                    $matched = $shortlist->last();
                    $this->checkout->setRecipient($matched);
                }
//                $this->checkout->setPhone($this->checkout->getRecipient()->getPhone());
                $this->em->persist($this->checkout);
                $this->em->flush();
            }
            // Previously was Guest checkout
            else {
                $this->checkout->setRecipient($recipient);
//                $this->checkout->setPhone($this->checkout->getRecipient()->getPhone());
                $this->em->persist($recipient);
                $this->em->persist($this->checkout);
                $this->em->flush();
            }
        }
    }

    /**
     * Remove Recipient from Checkout
     */
    public function removeRecipient(): void
    {
        if ($this->checkout->hasRecipient()) {
            $this->checkout->setRecipient(null);
            $this->em->persist($this->checkout);
            $this->em->flush();
        }
    }

    public function setSender(?Sender $sender, ?bool $isSameAsRecipient, ?bool $isLoggedIn, ?bool $isAbandoned = false): void
    {
        $isGuestCheckout = !$isLoggedIn;

        if ($isSameAsRecipient) {
            $this->checkout->setSameAsShipping(true);
            $this->em->persist($this->checkout);
            $this->em->flush();
            return;
        }

        // At this point isSameAsRecipient = false, we're in the Add new sender scenario
        if ($isGuestCheckout || $isLoggedIn || $isAbandoned) {
            if (!$sender) {
                throw new ErrorException('HIBA: The Sender is missing.');
            }
            $this->checkout->setSameAsShipping(false);
        }

        if ($isLoggedIn) {
            $sender->setUser($this->user);
            $senders = $this->user->getSenders();

            $shortlist = $senders->filter($this->equalsSender($sender));
            if ($shortlist->isEmpty()) {
                $this->checkout->setSender($sender);
                $this->em->persist($sender);
            } else {
                $matchedSender = $shortlist->last();
                $this->checkout->setSender($matchedSender);
            }
            $this->em->persist($this->checkout);
            $this->em->flush();
        }

        if ($isGuestCheckout) {
//            if ($this->checkout->hasSender()) {
//                $prevSender = $this->checkout->getSender();
//                $this->checkout->setSender(null);
//                $this->em->remove($prevSender);
//            }
            $this->checkout->setSender($sender);
            $this->em->persist($sender);
            $this->em->persist($this->checkout);
            $this->em->flush();
        }

        if ($isAbandoned) {
            // Previously was Logged-in
            if ($sender->getUser()) {
                $senders = $sender->getUser()->getSenders();

                $shortlist = $senders->filter($this->equalsSender($sender));
                if ($shortlist->isEmpty()) {
                    $this->checkout->setSender($sender);
                    $this->em->persist($sender);
                } else {
                    $matchedSender = $shortlist->last();
                    $this->checkout->setSender($matchedSender);
                }
                $this->em->persist($this->checkout);
                $this->em->flush();
            }
            // Previously was Guest checkout
            else {
                $this->checkout->setSender($sender);
                $this->em->persist($sender);
                $this->em->persist($this->checkout);
                $this->em->flush();
            }
        }

    }

    private function equalsRecipient(Recipient $recipient)
    {
        $equals = function (Recipient $item) use ($recipient) {
            return (
                $item->getFirstname() === $recipient->getFirstname() &&
                $item->getLastname() === $recipient->getLastname() &&
                $item->getPhone() === $recipient->getPhone() &&
//                $item->getCompany() === $recipient->getCompany() &&
//                $item->getCompanyVatNumber() === $recipient->getCompanyVatNumber() &&
                $item->getAddress()->getStreet() === $recipient->getAddress()->getStreet() &&
                $item->getAddress()->getCity() === $recipient->getAddress()->getCity() &&
                $item->getAddress()->getZip() === $recipient->getAddress()->getZip() &&
                $item->getAddress()->getProvince() === $recipient->getAddress()->getProvince() &&
                $item->getAddress()->getCountry() === $recipient->getAddress()->getCountry()
            );
        };
        return $equals;
    }

    private function equalsSender(Sender $sender)
    {
        $equals = function (Sender $item) use ($sender) {
            return (
                $item->getFirstname() === $sender->getFirstname() &&
                $item->getLastname() === $sender->getLastname() &&
                $item->getPhone() === $sender->getPhone() &&
                $item->getCompany() === $sender->getCompany() &&
                $item->getCompanyVatNumber() === $sender->getCompanyVatNumber() &&
                $item->getAddress()->getStreet() === $sender->getAddress()->getStreet() &&
                $item->getAddress()->getCity() === $sender->getAddress()->getCity() &&
                $item->getAddress()->getZip() === $sender->getAddress()->getZip() &&
                $item->getAddress()->getProvince() === $sender->getAddress()->getProvince() &&
                $item->getAddress()->getCountry() === $sender->getAddress()->getCountry()
            );
        };
        return $equals;
    }


    public function setSameAsShipping(?bool $isSameAsShipping)
    {
        $this->checkout->setSameAsShipping($isSameAsShipping);
        $this->em->persist($this->checkout);
        $this->em->flush();
    }

    /**
     * Remove Sender from Checkout
     */
    public function removeSender(): void
    {
        if ($this->checkout->hasSender()) {
            $this->checkout->setSender(null);
            $this->em->persist($this->checkout);
            $this->em->flush();
        }
    }

    /**
     * Set order status
     *
     * @param OrderStatus $status
     */
    public function setStatus(OrderStatus $status): void
    {
        if ($this->checkout) {
            $this->checkout->setStatus($status);

            // Run events
            $this->runEvent(OrderEvent::ORDER_UPDATED, $status->getShortcode());

            $this->em->persist($this->checkout);
            $this->em->flush();
        }
    }

    /**
     * Set payment status
     *
     * @param PaymentStatus $paymentStatus
     */
    public function setPaymentStatus(PaymentStatus $paymentStatus): void
    {
        if ($this->checkout) {
            $this->checkout->setPaymentStatus($paymentStatus);
            $this->em->persist($this->checkout);
            $this->em->flush();
        }
    }


    /**
     * Set payment method
     *
     * @param PaymentMethod $payment
     */
    public function setPaymentMethod(PaymentMethod $payment): void
    {
        if ($this->checkout) {
            $this->checkout->setPaymentMethod($payment);
            $this->checkout->setPaymentFee($payment->getPrice());

            $this->em->persist($this->checkout);
            $this->em->flush();
        }
    }

    /**
     * @param ShippingMethod $shippingMethod
     */
    public function setShippingMethod(ShippingMethod $shippingMethod): void
    {
        if ($this->checkout) {
            $this->checkout->setShippingMethod($shippingMethod);
            $this->checkout->setShippingFee($shippingMethod->getPrice());

            $this->em->persist($this->checkout);
            $this->em->flush();
        }
    }

    /**
     * @param bool|null $isAcceptedTerms
     */
    public function setIsAcceptedTerms(?bool $isAcceptedTerms): void
    {
        if ($this->checkout) {
            $this->checkout->setIsAcceptedTerms($isAcceptedTerms);

            $this->em->persist($this->checkout);
            $this->em->flush();
        }
    }

    /**
     * @param ClientDetails|null $clientDetails
     */
    public function setClientDetails(?ClientDetails $clientDetails): void
    {
        if ($this->checkout) {
            $this->checkout->setClientDetails($clientDetails);

            $this->em->persist($this->checkout);
            $this->em->flush();
        }
    }


    /**
     * @param string|null $token
     */
    public function setToken(?string $token): void
    {
        $this->checkout->setToken($token);

        $this->em->persist($this->checkout);
        $this->em->flush();
    }


    /**
     * Checking if the order has recipient.
     *
     * @return bool
     */
    public function hasRecipient(): bool
    {
        return null !== $this->checkout->getRecipient();
    }

    /**
     * Checking if the order has a sender.
     *
     * @return bool
     */
    public function hasSender(): bool
    {
        return null === $this->checkout->getSender() ? false : true;
    }

    public function hasBillingAddress(): bool
    {
        if ($this->checkout->isSameAsShipping()) {
            return true;
        }
        if ($this->checkout->hasSender()) {
            return true;
        }
        return false;
    }

    /**
     * Checking if the order has a delivery date and time.
     *
     * @return bool
     */
    public function hasDeliveryDate(): bool
    {
        if ($this->checkout->getDeliveryDate() === null || $this->checkout->getDeliveryInterval() === null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function hasPaymentMethod(): bool
    {
        return null === $this->checkout->getPaymentMethod() ? false : true;
    }

    /**
     * @return bool
     */
    public function hasShippingMethod(): bool
    {
        return null === $this->checkout->getShippingMethod() ? false : true;
    }

    /**
     * Checking if the order has a CustomerBasic or normal Customer defined.
     *
     * @return bool
     */
    public function hasCustomer(): bool
    {
        if ($this->customer) {
            return true;
        }
        return false;
//        $valid = true;
//        if ($this->customer) {
//            // We check only for Phone because 'email', 'firstname' and 'lastname' are mandatory for a User/Customer
//            if (!$this->customer->getPhone()) {
//                $valid = false;
//            }
//        } else {
//            if (null === $this->storage->fetch('email') || '' === $this->storage->fetch('email')) {
//                $valid = false;
//            }
//            if (null === $this->storage->fetch('firstname') || '' === $this->storage->fetch('firstname')) {
//                $valid = false;
//            }
//            if (null === $this->storage->fetch('lastname') || '' === $this->storage->fetch('lastname')) {
//                $valid = false;
//            }
//            if (null === $this->checkout->getBillingPhone() || '' === $this->checkout->getBillingPhone()) {
//                $valid = false;
//            }
//        }
//        return true === $valid ? true : false;
    }

    private function runEvent(string $eventName, array $params = []) {
        $params['channel'] = OrderLog::CHANNEL_CHECKOUT;

        $event = new StoreEvent($this->checkout, $params);
        $this->eventDispatcher->dispatch($event, $eventName);
    }

}