<?php

declare(strict_types=1);

namespace App\Model;

final class OrdersSummary
{
    /**
     * @var float
     */
    private $totalRevenue;

    /**
     * @var int
     */
    private $orderCount;

    /**
     * @var int
     */
    private $unpaidCount;

    /**
     * @var int
     */
    private $unfulfilledCount;

    /**
     * @var int
     */
    private $customerCount;

    /** @var string */
    private $dateRange;

    /**
     * @return float
     */
    public function getTotalRevenue(): ?float
    {
        return $this->totalRevenue;
    }

    /**
     * @param float $totalRevenue
     */
    public function setTotalRevenue(float $totalRevenue): void
    {
        $this->totalRevenue = $totalRevenue;
    }

    /**
     * @return int
     */
    public function getOrderCount(): ?int
    {
        return $this->orderCount;
    }

    /**
     * @param int $orderCount
     */
    public function setOrderCount(int $orderCount): void
    {
        $this->orderCount = $orderCount;
    }

    /**
     * @return int
     */
    public function getUnpaidCount(): ?int
    {
        return $this->unpaidCount;
    }

    /**
     * @param int $unpaidCount
     */
    public function setUnpaidCount(int $unpaidCount): void
    {
        $this->unpaidCount = $unpaidCount;
    }

    /**
     * @return int
     */
    public function getUnfulfilledCount(): ?int
    {
        return $this->unfulfilledCount;
    }

    /**
     * @param int $unfulfilledCount
     */
    public function setUnfulfilledCount(int $unfulfilledCount): void
    {
        $this->unfulfilledCount = $unfulfilledCount;
    }

    /**
     * @return int
     */
    public function getCustomerCount(): ?int
    {
        return $this->customerCount;
    }

    /**
     * @param int $customerCount
     */
    public function setCustomerCount(int $customerCount): void
    {
        $this->customerCount = $customerCount;
    }

    /**
     * @return string|null
     */
    public function getDateRange(): ?string
    {
        return $this->dateRange;
    }

    /**
     * @param string $dateRange
     */
    public function setDateRange(string $dateRange): void
    {
        $this->dateRange = $dateRange;
    }

}