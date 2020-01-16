<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeamAwardRepository")
 */
class TeamAward
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Season", inversedBy="teamAward", cascade={"persist"})
     */
    private $season;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="teamAwards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $award;

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
     * @return TeamAward
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
     * @return TeamAward
     */
    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
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
     * @return TeamAward
     */
    public function setAward(string $award): self
    {
        $this->award = $award;

        return $this;
    }
}
