<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ServerRepository")
 */
class Server
{
    const SERVER_ONE = 'Serveris 1';
    const SERVER_TWO = 'Serveris 2';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Player", mappedBy="server")
     */
    private $players;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Season", mappedBy="server", orphanRemoval=true)
     */
    private $seasons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Team", mappedBy="server")
     */
    private $teams;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->seasons = new ArrayCollection();
        $this->teams = new ArrayCollection();
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
     * @return Server
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
     * @return Server
     */
    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
            $player->setServer($this);
        }

        return $this;
    }

    /**
     * @param Player $player
     *
     * @return Server
     */
    public function removePlayer(Player $player): self
    {
        if ($this->players->contains($player)) {
            $this->players->removeElement($player);
            // set the owning side to null (unless already changed)
            if ($player->getServer() === $this) {
                $player->setServer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Season[]
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    public function addSeason(Season $season): self
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setServer($this);
        }

        return $this;
    }

    public function removeSeason(Season $season): self
    {
        if ($this->seasons->contains($season)) {
            $this->seasons->removeElement($season);
            // set the owning side to null (unless already changed)
            if ($season->getServer() === $this) {
                $season->setServer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    /**
     * @param Team $team
     *
     * @return Server
     */
    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $team->setServer($this);
        }

        return $this;
    }

    /**
     * @param Team $team
     *
     * @return Server
     */
    public function removeTeam(Team $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
            // set the owning side to null (unless already changed)
            if ($team->getServer() === $this) {
                $team->setServer(null);
            }
        }

        return $this;
    }
}
