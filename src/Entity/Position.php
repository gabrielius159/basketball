<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PositionRepository")
 */
class Position
{
    const POINT_GUARD = 'PG (Point guard)';
    const SHOOTING_GUARD = 'SG (Shooting guard)';
    const SMALL_FORWARD = 'SF (Small forward)';
    const POWER_FORWARD = 'PF (Power forward)';
    const CENTER = 'C (Center)';

    const FULL_POINT_GUARD = 'Įžaidėjas';
    const FULL_SHOOTING_GUARD = 'Gynėjas';
    const FULL_SMALL_FORWARD = 'Lengvasis krašto puolėjas';
    const FULL_POWER_FORWARD = 'Sunkusis krašto puolėjas';
    const FULL_CENTER = 'Centras';

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
     * @ORM\OneToMany(targetEntity="App\Entity\Player", mappedBy="position")
     */
    private $players;

    public function __construct()
    {
        $this->players = new ArrayCollection();
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
     * @return Position
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    /**
     * @param Player $player
     *
     * @return Position
     */
    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
            $player->setPosition($this);
        }

        return $this;
    }

    /**
     * @param Player $player
     *
     * @return Position
     */
    public function removePlayer(Player $player): self
    {
        if ($this->players->contains($player)) {
            $this->players->removeElement($player);

            if ($player->getPosition() === $this) {
                $player->setPosition(null);
            }
        }

        return $this;
    }
}
