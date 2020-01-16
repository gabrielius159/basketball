<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlayerAwardRepository")
 */
class PlayerAward
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
    private $award;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="playerAwards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Season")
     * @ORM\JoinColumn(nullable=false)
     */
    private $season;

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
    public function getAward(): ?string
    {
        return $this->award;
    }

    /**
     * @param string $award
     *
     * @return PlayerAward
     */
    public function setAward(string $award): self
    {
        $this->award = $award;

        return $this;
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
     * @return PlayerAward
     */
    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    /**
     * @return Season|null
     */
    public function getSeason(): ?Season
    {
        return $this->season;
    }

    /**
     * @param Season|null $season
     *
     * @return PlayerAward
     */
    public function setSeason(?Season $season): self
    {
        $this->season = $season;

        return $this;
    }
}
