<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_status")
 */
class Status
{

    /**
     *
     * @ORM\Column(name="id", length=5, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     *
     * @ORM\Column(name="status_name", type="string", length=100, nullable=false)
     */
    private $statusName='';


    // getter methods
    public function getId()
    {
        return $this->id;
    }

    public function getStatusName()
    {
        return $this->statusName;
    }

    // setter methods
    public function setStatusName(Status $nev)
    {
        $this->statusName = $nev;
    }

    public function __toString()
    {
        return $this->getStatusName();
    }


}