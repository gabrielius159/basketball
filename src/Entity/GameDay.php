<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameDayRepository")
 */
class GameDay
{
    const STATUS_WAITING = 'WAITING';
    const STATUS_FINISHED = 'FINISHED';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Season", inversedBy="gameDays")
     * @ORM\JoinColumn(nullable=false)
     */
    private $season;

    /**
     * @ORM\Column(type="datetime")
     */
    private $time;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="gameDays")
     * @ORM\JoinColumn(nullable=true)
     */
    private $teamOne;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     * @ORM\JoinColumn(nullable=true)
     */
    private $teamTwo;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $status;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\GameDayScores", mappedBy="gameDay", cascade={"persist", "remove"})
     */
    private $gameDayScores;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return GameDay
     */
    public function setSeason(?Season $season): self
    {
        $this->season = $season;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    /**
     * @param \DateTimeInterface $time
     *
     * @return GameDay
     */
    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return Team|null
     */
    public function getTeamOne(): ?Team
    {
        return $this->teamOne;
    }

    /**
     * @param Team|null $teamOne
     *
     * @return GameDay
     */
    public function setTeamOne(?Team $teamOne): self
    {
        $this->teamOne = $teamOne;

        return $this;
    }

    /**
     * @return Team|null
     */
    public function getTeamTwo(): ?Team
    {
        return $this->teamTwo;
    }

    /**
     * @param Team|null $teamTwo
     *
     * @return GameDay
     */
    public function setTeamTwo(?Team $teamTwo): self
    {
        $this->teamTwo = $teamTwo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return GameDay
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return GameDayScores|null
     */
    public function getGameDayScores(): ?GameDayScores
    {
        return $this->gameDayScores;
    }

    /**
     * @param GameDayScores $gameDayScores
     *
     * @return GameDay
     */
    public function setGameDayScores(GameDayScores $gameDayScores): self
    {
        $this->gameDayScores = $gameDayScores;

        // set the owning side of the relation if necessary
        if ($this !== $gameDayScores->getGameDay()) {
            $gameDayScores->setGameDay($this);
        }

        return $this;
    }
}
