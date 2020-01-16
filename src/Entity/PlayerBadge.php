<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlayerBadgeRepository")
 */
class PlayerBadge
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="playerBadges")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Badge")
     * @ORM\JoinColumn(nullable=false)
     */
    private $badge;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Player|null
     */
    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @param Player|null $player
     *
     * @return PlayerBadge
     */
    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    /**
     * @return Badge|null
     */
    public function getBadge(): ?Badge
    {
        return $this->badge;
    }

    /**
     * @param Badge|null $badge
     *
     * @return PlayerBadge
     */
    public function setBadge(?Badge $badge): self
    {
        $this->badge = $badge;

        return $this;
    }
}
