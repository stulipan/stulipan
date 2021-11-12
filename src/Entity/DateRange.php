<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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

    public function getStart()
    {
        return $this->start;
    }

    public function setStart(DateTime $date)
    {
        $this->start = $date->setTime(0,0);
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setEnd(DateTime $date = null)
    {
        $this->end = $date->setTime(23,59,59);
    }


}
