<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameDayScoresRepository")
 */
class GameDayScores
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\GameDay", inversedBy="gameDayScores", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $gameDay;

    /**
     * @ORM\Column(type="json")
     */
    private $teamOnePlayerStats = [];

    /**
     * @ORM\Column(type="json")
     */
    private $teamTwoPlayerStats = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $teamOneScore;

    /**
     * @ORM\Column(type="integer")
     */
    private $teamTwoScore;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return GameDay|null
     */
    public function getGameDay(): ?GameDay
    {
        return $this->gameDay;
    }

    /**
     * @param GameDay $gameDay
     *
     * @return GameDayScores
     */
    public function setGameDay(GameDay $gameDay): self
    {
        $this->gameDay = $gameDay;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getTeamOnePlayerStats(): ?array
    {
        return $this->teamOnePlayerStats;
    }

    /**
     * @param array $teamOnePlayerStats
     *
     * @return GameDayScores
     */
    public function setTeamOnePlayerStats(array $teamOnePlayerStats): self
    {
        $this->teamOnePlayerStats = $teamOnePlayerStats;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getTeamTwoPlayerStats(): ?array
    {
        return $this->teamTwoPlayerStats;
    }

    /**
     * @param array $teamTwoPlayerStats
     *
     * @return GameDayScores
     */
    public function setTeamTwoPlayerStats(array $teamTwoPlayerStats): self
    {
        $this->teamTwoPlayerStats = $teamTwoPlayerStats;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTeamOneScore(): ?int
    {
        return $this->teamOneScore;
    }

    /**
     * @param int $teamOneScore
     *
     * @return GameDayScores
     */
    public function setTeamOneScore(int $teamOneScore): self
    {
        $this->teamOneScore = $teamOneScore;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTeamTwoScore(): ?int
    {
        return $this->teamTwoScore;
    }

    /**
     * @param int $teamTwoScore
     *
     * @return GameDayScores
     */
    public function setTeamTwoScore(int $teamTwoScore): self
    {
        $this->teamTwoScore = $teamTwoScore;

        return $this;
    }
}
