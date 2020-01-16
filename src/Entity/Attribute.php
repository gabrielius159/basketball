<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AttributeRepository")
 */
class Attribute
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $defaultValue;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PlayerAttribute", mappedBy="attribute", cascade={"remove"})
     */
    private $playerAttributes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GameType", inversedBy="attributes")
     */
    private $gameType;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Badge", mappedBy="attribute", cascade={"remove"})
     */
    private $badges;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TrainingCamp", mappedBy="attributeToImprove")
     */
    private $trainingCamps;

    /**
     * Attribute constructor.
     */
    public function __construct()
    {
        $this->playerAttributes = new ArrayCollection();
        $this->badges = new ArrayCollection();
        $this->trainingCamps = new ArrayCollection();
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
     * @return Attribute
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getDefaultValue(): ?float
    {
        return $this->defaultValue;
    }

    /**
     * @param float $defaultValue
     *
     * @return Attribute
     */
    public function setDefaultValue(float $defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return Collection|PlayerAttribute[]
     */
    public function getPlayerAttributes(): Collection
    {
        return $this->playerAttributes;
    }

    /**
     * @param PlayerAttribute $playerAttribute
     *
     * @return Attribute
     */
    public function addPlayerAttribute(PlayerAttribute $playerAttribute): self
    {
        if (!$this->playerAttributes->contains($playerAttribute)) {
            $this->playerAttributes[] = $playerAttribute;
            $playerAttribute->setAttribute($this);
        }

        return $this;
    }

    /**
     * @param PlayerAttribute $playerAttribute
     *
     * @return Attribute
     */
    public function removePlayerAttribute(PlayerAttribute $playerAttribute): self
    {
        if ($this->playerAttributes->contains($playerAttribute)) {
            $this->playerAttributes->removeElement($playerAttribute);
            // set the owning side to null (unless already changed)
            if ($playerAttribute->getAttribute() === $this) {
                $playerAttribute->setAttribute(null);
            }
        }

        return $this;
    }

    /**
     * @return GameType|null
     */
    public function getGameType(): ?GameType
    {
        return $this->gameType;
    }

    /**
     * @param GameType|null $gameType
     *
     * @return Attribute
     */
    public function setGameType(?GameType $gameType): self
    {
        $this->gameType = $gameType;

        return $this;
    }

    /**
     * @return Collection|Badge[]
     */
    public function getBadges(): Collection
    {
        return $this->badges;
    }

    /**
     * @param Badge $badge
     *
     * @return Attribute
     */
    public function addBadge(Badge $badge): self
    {
        if (!$this->badges->contains($badge)) {
            $this->badges[] = $badge;
            $badge->setAttribute($this);
        }

        return $this;
    }

    /**
     * @param Badge $badge
     *
     * @return Attribute
     */
    public function removeBadge(Badge $badge): self
    {
        if ($this->badges->contains($badge)) {
            $this->badges->removeElement($badge);
            // set the owning side to null (unless already changed)
            if ($badge->getAttribute() === $this) {
                $badge->setAttribute(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TrainingCamp[]
     */
    public function getTrainingCamps(): Collection
    {
        return $this->trainingCamps;
    }

    /**
     * @param TrainingCamp $trainingCamp
     *
     * @return Attribute
     */
    public function addTrainingCamp(TrainingCamp $trainingCamp): self
    {
        if (!$this->trainingCamps->contains($trainingCamp)) {
            $this->trainingCamps[] = $trainingCamp;
            $trainingCamp->setAttributeToImprove($this);
        }

        return $this;
    }

    /**
     * @param TrainingCamp $trainingCamp
     *
     * @return Attribute
     */
    public function removeTrainingCamp(TrainingCamp $trainingCamp): self
    {
        if ($this->trainingCamps->contains($trainingCamp)) {
            $this->trainingCamps->removeElement($trainingCamp);
            // set the owning side to null (unless already changed)
            if ($trainingCamp->getAttributeToImprove() === $this) {
                $trainingCamp->setAttributeToImprove(null);
            }
        }

        return $this;
    }
}
