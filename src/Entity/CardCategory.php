<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="card_category")
 */
class CardCategory
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", name="id", length=5, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="category_name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CardMessage", mappedBy="category")
     *
     */
    private $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @return Collection|CardMessage[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

}