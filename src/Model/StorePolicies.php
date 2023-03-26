<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\StorePolicy;

class StorePolicies
{
    /**
     * @var StorePolicy|null
     */
    private $termsAndConditions;

    /**
     * @var StorePolicy|null
     */
    private $privacyPolicy;

    /**
     * @var StorePolicy|null
     */
    private $shippingInformation;

    /**
     * @var StorePolicy|null
     */
    private $returnPolicy;

    /**
     * @var StorePolicy|null
     */
    private $contactInformation;

    /**
     * @var StorePolicy|null
     */
    private $legalNotice;

    public function __construct(StorePolicy $termsAndConditions,
                                StorePolicy $privacyPolicy,
                                StorePolicy $shippingInformation,
                                StorePolicy $returnPolicy,
                                StorePolicy $contactInformation,
                                StorePolicy $legalNotice)
    {
        $this->termsAndConditions = $termsAndConditions;
        $this->privacyPolicy = $privacyPolicy;
        $this->shippingInformation = $shippingInformation;
        $this->returnPolicy = $returnPolicy;
        $this->contactInformation = $contactInformation;
        $this->legalNotice = $legalNotice;
    }

    /**
     * @return StorePolicy|null
     */
    public function getTermsAndConditions(): ?StorePolicy
    {
        return $this->termsAndConditions;
    }

    /**
     * @param StorePolicy|null $termsAndConditions
     */
    public function setTermsAndConditions(?StorePolicy $termsAndConditions): void
    {
        $this->termsAndConditions = $termsAndConditions;
    }

    /**
     * @return StorePolicy|null
     */
    public function getPrivacyPolicy(): ?StorePolicy
    {
        return $this->privacyPolicy;
    }

    /**
     * @param StorePolicy|null $privacyPolicy
     */
    public function setPrivacyPolicy(?StorePolicy $privacyPolicy): void
    {
        $this->privacyPolicy = $privacyPolicy;
    }

    /**
     * @return StorePolicy|null
     */
    public function getShippingInformation(): ?StorePolicy
    {
        return $this->shippingInformation;
    }

    /**
     * @param StorePolicy|null $shippingInformation
     */
    public function setShippingInformation(?StorePolicy $shippingInformation): void
    {
        $this->shippingInformation = $shippingInformation;
    }

    /**
     * @return StorePolicy|null
     */
    public function getReturnPolicy(): ?StorePolicy
    {
        return $this->returnPolicy;
    }

    /**
     * @param StorePolicy|null $returnPolicy
     */
    public function setReturnPolicy(?StorePolicy $returnPolicy): void
    {
        $this->returnPolicy = $returnPolicy;
    }

    /**
     * @return StorePolicy|null
     */
    public function getContactInformation(): ?StorePolicy
    {
        return $this->contactInformation;
    }

    /**
     * @param StorePolicy|null $contactInformation
     */
    public function setContactInformation(?StorePolicy $contactInformation): void
    {
        $this->contactInformation = $contactInformation;
    }

    /**
     * @return StorePolicy|null
     */
    public function getLegalNotice(): ?StorePolicy
    {
        return $this->legalNotice;
    }

    /**
     * @param StorePolicy|null $legalNotice
     */
    public function setLegalNotice(?StorePolicy $legalNotice): void
    {
        $this->legalNotice = $legalNotice;
    }
}