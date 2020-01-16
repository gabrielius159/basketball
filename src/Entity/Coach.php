<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CoachRepository")
 */
class Coach
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $lastname;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Team", inversedBy="coach", cascade={"persist"})
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GameType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $firstGameType;

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
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return Coach
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     *
     * @return Coach
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

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
     * @return Coach
     */
    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return GameType|null
     */
    public function getFirstGameType(): ?GameType
    {
        return $this->firstGameType;
    }

    /**
     * @param GameType|null $firstGameType
     *
     * @return Coach
     */
    public function setFirstGameType(?GameType $firstGameType): self
    {
        $this->firstGameType = $firstGameType;

        return $this;
    }
}
