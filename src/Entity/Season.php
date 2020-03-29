<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SeasonRepository")
 */
class Season
{
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PREPARING = 'PREPARING';
    const STATUS_FINISHED = 'FINISHED';

    const STATUS_NAME = [
        self::STATUS_ACTIVE => 'In progress',
        self::STATUS_PREPARING => 'Preseason',
        self::STATUS_FINISHED => 'Finished'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Server", inversedBy="seasons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $server;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PlayerStats", mappedBy="season")
     */
    private $playerStats;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeamStatus", mappedBy="season")
     */
    private $teamStatuses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GameDay", mappedBy="season", orphanRemoval=true)
     */
    private $gameDays;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\TeamAward", mappedBy="season", cascade={"persist", "remove"})
     */
    private $teamAward;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserReward", mappedBy="season", cascade={"persist", "remove"})
     */
    private $userReward;

    /**
     * Season constructor.
     */
    public function __construct()
    {
        $this->playerStats = new ArrayCollection();
        $this->teamStatuses = new ArrayCollection();
        $this->gameDays = new ArrayCollection();
        $this->userRewards = new ArrayCollection();
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
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Season
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Server|null
     */
    public function getServer(): ?Server
    {
        return $this->server;
    }

    /**
     * @param Server|null $server
     *
     * @return Season
     */
    public function setServer(?Server $server): self
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return Collection|PlayerStats[]
     */
    public function getPlayerStats(): Collection
    {
        return $this->playerStats;
    }

    /**
     * @param PlayerStats $playerStat
     *
     * @return Season
     */
    public function addPlayerStat(PlayerStats $playerStat): self
    {
        if (!$this->playerStats->contains($playerStat)) {
            $this->playerStats[] = $playerStat;
            $playerStat->setSeason($this);
        }

        return $this;
    }

    /**
     * @param PlayerStats $playerStat
     *
     * @return Season
     */
    public function removePlayerStat(PlayerStats $playerStat): self
    {
        if ($this->playerStats->contains($playerStat)) {
            $this->playerStats->removeElement($playerStat);
            // set the owning side to null (unless already changed)
            if ($playerStat->getSeason() === $this) {
                $playerStat->setSeason(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TeamStatus[]
     */
    public function getTeamStatuses(): Collection
    {
        return $this->teamStatuses;
    }

    /**
     * @param TeamStatus $teamStatus
     *
     * @return Season
     */
    public function addTeamStatus(TeamStatus $teamStatus): self
    {
        if (!$this->teamStatuses->contains($teamStatus)) {
            $this->teamStatuses[] = $teamStatus;
            $teamStatus->setSeason($this);
        }

        return $this;
    }

    /**
     * @param TeamStatus $teamStatus
     *
     * @return Season
     */
    public function removeTeamStatus(TeamStatus $teamStatus): self
    {
        if ($this->teamStatuses->contains($teamStatus)) {
            $this->teamStatuses->removeElement($teamStatus);
            // set the owning side to null (unless already changed)
            if ($teamStatus->getSeason() === $this) {
                $teamStatus->setSeason(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|GameDay[]
     */
    public function getGameDays(): Collection
    {
        return $this->gameDays;
    }

    /**
     * @param GameDay $gameDay
     *
     * @return Season
     */
    public function addGameDay(GameDay $gameDay): self
    {
        if (!$this->gameDays->contains($gameDay)) {
            $this->gameDays[] = $gameDay;
            $gameDay->setSeason($this);
        }

        return $this;
    }

    /**
     * @param GameDay $gameDay
     *
     * @return Season
     */
    public function removeGameDay(GameDay $gameDay): self
    {
        if ($this->gameDays->contains($gameDay)) {
            $this->gameDays->removeElement($gameDay);
            // set the owning side to null (unless already changed)
            if ($gameDay->getSeason() === $this) {
                $gameDay->setSeason(null);
            }
        }

        return $this;
    }

    /**
     * @return TeamAward|null
     */
    public function getTeamAward(): ?TeamAward
    {
        return $this->teamAward;
    }

    /**
     * @param TeamAward|null $teamAward
     *
     * @return Season
     */
    public function setTeamAward(?TeamAward $teamAward): self
    {
        $this->teamAward = $teamAward;

        // set (or unset) the owning side of the relation if necessary
        $newSeason = $teamAward === null ? null : $this;
        if ($newSeason !== $teamAward->getSeason()) {
            $teamAward->setSeason($newSeason);
        }

        return $this;
    }

    /**
     * @return UserReward|null
     */
    public function getUserReward(): ?UserReward
    {
        return $this->userReward;
    }

    /**
     * @param UserReward $userReward
     *
     * @return Season
     */
    public function setUserReward(UserReward $userReward): self
    {
        $this->userReward = $userReward;

        // set the owning side of the relation if necessary
        if ($this !== $userReward->getSeason()) {
            $userReward->setSeason($this);
        }

        return $this;
    }
}
