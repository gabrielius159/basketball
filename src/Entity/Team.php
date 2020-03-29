<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 * @Vich\Uploadable
 */
class Team
{
    const DEFAULT_PLAYER_LIMIT_IN_TEAM = 10;

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
     * @ORM\Column(type="string", length=50)
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Server", inversedBy="teams")
     * @ORM\JoinColumn(nullable=false)
     */
    private $server;

    /**
     * @ORM\Column(type="float")
     */
    private $budget;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeamStatus", mappedBy="team", cascade={"remove"})
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $teamStatuses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Player", mappedBy="team")
     */
    private $players;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Coach", mappedBy="team", cascade={"persist"})
     */
    private $coach;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="teams", fileNameProperty="image", size="imageSize")
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $imageSize;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GameDay", mappedBy="teamOne", orphanRemoval=true)
     */
    private $gameDays;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeamAward", mappedBy="team", cascade={"remove"})
     */
    private $teamAwards;

    /**
     * Team constructor.
     */
    public function __construct()
    {
        $this->teamStatuses = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->gameDays = new ArrayCollection();
        $this->teamAwards = new ArrayCollection();
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
     * @return Team
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Team
     */
    public function setCity(string $city): self
    {
        $this->city = $city;

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
     * @return Team
     */
    public function setServer(?Server $server): self
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getBudget(): ?float
    {
        return $this->budget;
    }

    /**
     * @param float $budget
     *
     * @return Team
     */
    public function setBudget(float $budget): self
    {
        $this->budget = $budget;

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
     * @return TeamStatus|null
     */
    public function getCurrentTeamStatus(): TeamStatus
    {
        if(!$this->teamStatuses) {
            return null;
        }

        return $this->getTeamStatuses()->first();
    }

    /**
     * @param TeamStatus $teamStatus
     *
     * @return Team
     */
    public function addTeamStatus(TeamStatus $teamStatus): self
    {
        if (!$this->teamStatuses->contains($teamStatus)) {
            $this->teamStatuses[] = $teamStatus;
            $teamStatus->setTeam($this);
        }

        return $this;
    }

    /**
     * @return float
     */
    public function getUsedBudget(): float
    {
        $budgetUsed = 0;
        foreach($this->players as $player) {
            $budgetUsed += $player->getContractSalary();
        }

        return $budgetUsed;
    }

    /**
     * @param TeamStatus $teamStatus
     *
     * @return Team
     */
    public function removeTeamStatus(TeamStatus $teamStatus): self
    {
        if ($this->teamStatuses->contains($teamStatus)) {
            $this->teamStatuses->removeElement($teamStatus);
            // set the owning side to null (unless already changed)
            if ($teamStatus->getTeam() === $this) {
                $teamStatus->setTeam(null);
            }
        }

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
     * @return Collection|Player[]
     */
    public function getRealPlayers(): Collection
    {
        return $this->players->filter(function(Player $player) {
            return $player->getIsRealPlayer() === true;
        });
    }

    /**
     * @return Collection
     */
    public function getFakePlayers(): Collection
    {
        return $this->getPlayers()->filter(function(Player $player) {
            return $player->getIsRealPlayer() == false;
        });
    }

    /**
     * @param Position $position
     *
     * @return Collection
     */
    public function getFakePlayerSamePosition(Position $position): Collection
    {
        return $this->getPlayers()->filter(function(Player $player) use ($position) {
            return $player->getPosition() === $position && $player->getIsRealPlayer() == false;
        });
    }

    /**
     * @param Player $player
     *
     * @return Team
     */
    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
            $player->setTeam($this);
        }

        return $this;
    }

    /**
     * @param Player $player
     *
     * @return Team
     */
    public function removePlayer(Player $player): self
    {
        if ($this->players->contains($player)) {
            $this->players->removeElement($player);
            // set the owning side to null (unless already changed)
            if ($player->getTeam() === $this) {
                $player->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Coach|null
     */
    public function getCoach(): ?Coach
    {
        return $this->coach;
    }

    /**
     * @param Coach|null $coach
     *
     * @return Team
     */
    public function setCoach(?Coach $coach): self
    {
        $this->coach = $coach;

        // set (or unset) the owning side of the relation if necessary
        $newTeam = $coach === null ? null : $this;
        if ($newTeam !== $coach->getTeam()) {
            $coach->setTeam($newTeam);
        }

        return $this;
    }

    /**
     * @param File|null $image
     *
     * @throws \Exception
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            $this->updatedAt = new \DateTime('now');
        }
    }

    /**
     * @return File
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     *
     * @return Player
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    /**
     * @param int|null $imageSize
     *
     * @return Player
     */
    public function setImageSize(?int $imageSize): self
    {
        $this->imageSize = $imageSize;

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
     * @return Team
     */
    public function addGameDay(GameDay $gameDay): self
    {
        if (!$this->gameDays->contains($gameDay)) {
            $this->gameDays[] = $gameDay;
            $gameDay->setTeamOne($this);
        }

        return $this;
    }

    /**
     * @param GameDay $gameDay
     *
     * @return Team
     */
    public function removeGameDay(GameDay $gameDay): self
    {
        if ($this->gameDays->contains($gameDay)) {
            $this->gameDays->removeElement($gameDay);
            // set the owning side to null (unless already changed)
            if ($gameDay->getTeamOne() === $this) {
                $gameDay->setTeamOne(null);
                $gameDay->setTeamTwo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPlayersOrderByRating(): Collection
    {
        $iterator = $this->getPlayers()->getIterator();
        $iterator->uasort(function (Player $a, Player $b) {
            return ($a->getPlayerRating() > $b->getPlayerRating()) ? -1 : 1;
        });
        $collection = new ArrayCollection(iterator_to_array($iterator));

        return $collection;
    }

    /**
     * @return Collection|TeamAward[]
     */
    public function getTeamAwards(): Collection
    {
        return $this->teamAwards;
    }

    /**
     * @param TeamAward $teamAward
     *
     * @return Team
     */
    public function addTeamAward(TeamAward $teamAward): self
    {
        if (!$this->teamAwards->contains($teamAward)) {
            $this->teamAwards[] = $teamAward;
            $teamAward->setTeam($this);
        }

        return $this;
    }

    /**
     * @param TeamAward $teamAward
     *
     * @return Team
     */
    public function removeTeamAward(TeamAward $teamAward): self
    {
        if ($this->teamAwards->contains($teamAward)) {
            $this->teamAwards->removeElement($teamAward);
            // set the owning side to null (unless already changed)
            if ($teamAward->getTeam() === $this) {
                $teamAward->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getFullTeamName(): string
    {
        return $this->getCity().' '.$this->getName();
    }
}
