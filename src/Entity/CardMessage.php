<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="card_message")
 * @ORM\Entity()
 * ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class CardMessage
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", length=5, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     * @Assert\NotBlank(message="Hiányzik az üzenet.")
     * @ORM\Column(name="message", type="string", length=255, nullable=false)
     */
    private $message;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=true)
     */
    private $title;

    /**
     * @var CardCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CardCategory", inversedBy="messages")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @Assert\NotBlank(message="Válassz egy kategóriát.")
     */
    private $category;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function __toString(): string
    {
        return $this->getMessage();
    }

    /**
     * @param null|string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return CardCategory
     */
    public function getCategory(): CardCategory
    {
        return $this->category;
    }

    /**
     * @param CardCategory $category
     */
    public function setCategory(?CardCategory $category)
    {
        $this->category = $category;
    }

}
