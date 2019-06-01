<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SubproductPrice
 *
 * @ORM\Entity()
 */
class SubproductPriceNincs extends Price
{
    /**
     * @var nincs
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Subproduct", mappedBy="price")
     * @Assert\NotBlank(message="Nem választottál alterméket.")
     */
    private $subproduct;

    /**
     * @return nincs
     */
    public function getSubproduct(): ?nincs
    {
        return $this->subproduct;
    }

    /**
     * @param nincs $subproduct
     */
    public function setSubproduct(?nincs $subproduct)
    {
        $this->subproduct = $subproduct;
    }
}
