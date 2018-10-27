<?php

declare(strict_types=1);

namespace App\Entity;

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
     *@Assert\NotBlank(message="Válassz dátumot.")
     */
    private $start;

    /**
     *@Assert\NotBlank(message="Válassz dátumot.")
     */
    private $end;

    public function getStart()
    {
        return $this->start;
    }

    public function setStart(\DateTime $date)
    {
        $this->start = $date;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setEnd(\DateTime $date = null)
    {
        $this->end = $date;
    }


}
