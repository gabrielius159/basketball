<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeamStatusRepository")
 */
class TeamStatus
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $win;

    /**
     * @ORM\Column(type="integer")
     */
    private $lose;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Season", inversedBy="teamStatuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $season;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="teamStatuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\Column(type="integer")
     */
    private $points;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getWin(): ?int
    {
        return $this->win;
    }

    /**
     * @param int $win
     *
     * @return TeamStatus
     */
    public function setWin(int $win): self
    {
        $this->win = $win;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLose(): ?int
    {
        return $this->lose;
    }

    /**
     * @param int $lose
     *
     * @return TeamStatus
     */
    public function setLose(int $lose): self
    {
        $this->lose = $lose;

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
     * @return TeamStatus
     */
    public function setSeason(?Season $season): self
    {
        $this->season = $season;

        return $this;
    }

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team|null $team
     *
     * @return TeamStatus
     */
    public function setTeam(?Team $team): self
    {
        $this->team = $team;

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
     * @return TeamStatus
     */
    public function setPoints(int $points): self
    {
        $this->points = $points;

        return $this;
    }
}
