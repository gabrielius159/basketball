<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlayerStatsRepository")
 */
class PlayerStats
{
    const CATEGORY_POINTS = 'points';
    const CATEGORY_REBOUNDS = 'rebounds';
    const CATEGORY_ASSISTS = 'assists';
    const CATEGORY_STEALS = 'steals';
    const CATEGORY_BLOCKS = 'blocks';

    const COEFFICIENT_POINTS = 1;
    const COEFFICIENT_REBOUNDS = 2;
    const COEFFICIENT_ASSISTS = 2;
    const COEFFICIENT_STEALS = 5;
    const COEFFICIENT_BLOCKS = 5;
    const COEFFICIENT_SEASONS_PLAYED = 2;


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="playerStats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\Column(type="integer")
     */
    private $gamesPlayed;

    /**
     * @ORM\Column(type="integer")
     */
    private $points;

    /**
     * @ORM\Column(type="integer")
     */
    private $rebounds;

    /**
     * @ORM\Column(type="integer")
     */
    private $assists;

    /**
     * @ORM\Column(type="integer")
     */
    private $steals;

    /**
     * @ORM\Column(type="integer")
     */
    private $blocks;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Season", inversedBy="playerStats")
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
     * @return Player|null
     */
    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @param Player|null $player
     *
     * @return PlayerStats
     */
    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getGamesPlayed(): ?int
    {
        return $this->gamesPlayed;
    }

    /**
     * @param int $gamesPlayed
     *
     * @return PlayerStats
     */
    public function setGamesPlayed(int $gamesPlayed): self
    {
        $this->gamesPlayed = $gamesPlayed;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPoints(): ?int
    {
        return $this->points;
    }

    /**
     * @param int $points
     *
     * @return PlayerStats
     */
    public function setPoints(int $points): self
    {
        $this->points = $points;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRebounds(): ?int
    {
        return $this->rebounds;
    }

    /**
     * @param int $rebounds
     *
     * @return PlayerStats
     */
    public function setRebounds(int $rebounds): self
    {
        $this->rebounds = $rebounds;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAssists(): ?int
    {
        return $this->assists;
    }

    /**
     * @param int $assists
     *
     * @return PlayerStats
     */
    public function setAssists(int $assists): self
    {
        $this->assists = $assists;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSteals(): ?int
    {
        return $this->steals;
    }

    /**
     * @param int $steals
     *
     * @return PlayerStats
     */
    public function setSteals(int $steals): self
    {
        $this->steals = $steals;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBlocks(): ?int
    {
        return $this->blocks;
    }

    /**
     * @param int $blocks
     *
     * @return PlayerStats
     */
    public function setBlocks(int $blocks): self
    {
        $this->blocks = $blocks;

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
     * @return PlayerStats
     */
    public function setSeason(?Season $season): self
    {
        $this->season = $season;

        return $this;
    }

    /**
     * @return array
     */
    public function getPlayerStatsArray(): array
    {
        return [
            'PPG' => $this->points / ($this->gamesPlayed === 0 ? 1 : $this->gamesPlayed),
            'RPG' => $this->rebounds / ($this->gamesPlayed === 0 ? 1 : $this->gamesPlayed),
            'APG' => $this->assists / ($this->gamesPlayed === 0 ? 1 : $this->gamesPlayed),
            'SPG' => $this->steals / ($this->gamesPlayed === 0 ? 1 : $this->gamesPlayed),
            'BPG' => $this->blocks / ($this->gamesPlayed === 0 ? 1 : $this->gamesPlayed),
        ];
    }
}
