<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameTypeRepository")
 */
class GameType
{
    const TYPE_SCORING = 'TYPE_SCORING';
    const TYPE_ASSISTING = 'TYPE_ASSISTING';
    const TYPE_REBOUNDING = 'TYPE_REBOUNDING';
    const TYPE_STEALING = 'TYPE_STEALING';
    const TYPE_BLOCKING = 'TYPE_BLOCKING';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Player", mappedBy="firstType")
     */
    private $playersWithFirstType;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Player", mappedBy="secondType")
     */
    private $playersWithSecondType;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Attribute", mappedBy="gameType")
     */
    private $attributes;

    /**
     * GameType constructor.
     */
    public function __construct()
    {
        $this->playersWithFirstType = new ArrayCollection();
        $this->playersWithSecondType = new ArrayCollection();
        $this->attributes = new ArrayCollection();
    }

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
     * @return GameType
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return GameType
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getPlayersWithFirstType(): Collection
    {
        return $this->playersWithFirstType;
    }

    /**
     * @param Player $playersWithFirstType
     *
     * @return GameType
     */
    public function addPlayersWithFirstType(Player $playersWithFirstType): self
    {
        if (!$this->playersWithFirstType->contains($playersWithFirstType)) {
            $this->playersWithFirstType[] = $playersWithFirstType;
            $playersWithFirstType->setFirstType($this);
        }

        return $this;
    }

    /**
     * @param Player $playersWithFirstType
     *
     * @return GameType
     */
    public function removePlayersWithFirstType(Player $playersWithFirstType): self
    {
        if ($this->playersWithFirstType->contains($playersWithFirstType)) {
            $this->playersWithFirstType->removeElement($playersWithFirstType);
            // set the owning side to null (unless already changed)
            if ($playersWithFirstType->getFirstType() === $this) {
                $playersWithFirstType->setFirstType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getPlayersWithSecondType(): Collection
    {
        return $this->playersWithSecondType;
    }

    /**
     * @param Player $playersWithSecondType
     *
     * @return GameType
     */
    public function addPlayersWithSecondType(Player $playersWithSecondType): self
    {
        if (!$this->playersWithSecondType->contains($playersWithSecondType)) {
            $this->playersWithSecondType[] = $playersWithSecondType;
            $playersWithSecondType->setSecondType($this);
        }

        return $this;
    }

    /**
     * @param Player $playersWithSecondType
     *
     * @return GameType
     */
    public function removePlayersWithSecondType(Player $playersWithSecondType): self
    {
        if ($this->playersWithSecondType->contains($playersWithSecondType)) {
            $this->playersWithSecondType->removeElement($playersWithSecondType);
            // set the owning side to null (unless already changed)
            if ($playersWithSecondType->getSecondType() === $this) {
                $playersWithSecondType->setSecondType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Attribute[]
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    /**
     * @param Attribute $attribute
     *
     * @return GameType
     */
    public function addAttribute(Attribute $attribute): self
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
            $attribute->setGameType($this);
        }

        return $this;
    }

    /**
     * @param Attribute $attribute
     *
     * @return GameType
     */
    public function removeAttribute(Attribute $attribute): self
    {
        if ($this->attributes->contains($attribute)) {
            $this->attributes->removeElement($attribute);
            // set the owning side to null (unless already changed)
            if ($attribute->getGameType() === $this) {
                $attribute->setGameType(null);
            }
        }

        return $this;
    }
}
