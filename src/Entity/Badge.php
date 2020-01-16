<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BadgeRepository")
 */
class Badge
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Attribute", inversedBy="badges")
     * @ORM\JoinColumn(nullable=false)
     */
    private $attribute;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Badge
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Attribute|null
     */
    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    /**
     * @param Attribute|null $attribute
     *
     * @return Badge
     */
    public function setAttribute(?Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }
}
