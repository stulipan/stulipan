<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class DateRange
{
    /**
     * @var DateTime
     * @Assert\NotBlank(message="Válassz dátumot.")
     */
    private $start;

    /**
     * @var DateTime|null
     * @Assert\NotBlank(message="Válassz dátumot.")
     */
    private $end;

    /**
     * DateRange constructor.
     * @param DateTime $start
     * @param DateTime|null $end
     */
    public function __construct(DateTime $start = null, ?DateTime $end = null)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return DateTime|null
     */
    public function getStart()
    {
        return $this->start;
    }

    public function setStart(DateTime $date)
    {
        $this->start = $date->setTime(0,0);
    }

    /**
     * @return DateTime|null
     */
    public function getEnd()
    {
        return $this->end;
    }

    public function setEnd(DateTime $date = null)
    {
        $this->end = $date->setTime(23,59,59);
    }


}
