<?php

namespace App\Entity;

use App\Utils\Award;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlayerRepository")
 * @Vich\Uploadable
 */
class Player
{
    const MIN_HEIGHT = 150;
    const MAX_HEIGHT = 220;
    const MIN_WEIGHT = 60;
    const MAX_WEIGHT = 150;

    const CONTRACT_YEAR_ONE = 1;
    const CONTRACT_YEAR_TWO = 2;
    const CONTRACT_YEAR_THREE = 3;
    const CONTRACT_YEAR_FOUR = 4;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $lastname;

    /**
     * @ORM\Column(type="datetime")
     */
    private $born;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Position", inversedBy="players", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $position;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="players")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Server", inversedBy="players")
     */
    private $server;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GameType", inversedBy="playersWithFirstType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $firstType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GameType", inversedBy="playersWithSecondType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $secondType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isRealPlayer;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PlayerStats", mappedBy="player", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $playerStats;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PlayerAttribute", mappedBy="player", cascade={"persist", "remove"})
     */
    private $playerAttributes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $contractYears;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $contractSalary;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="players")
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PlayerBadge", mappedBy="player", cascade={"persist", "remove"})
     */
    private $playerBadges;

    /**
     * @ORM\Column(type="integer")
     */
    private $weight;

    /**
     * @ORM\Column(type="float")
     */
    private $height;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="players", fileNameProperty="image", size="imageSize")
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $imageSize;

    // cascade={"persist"}
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="player")
     */
    private $user;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $jerseyNumber;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TrainingCamp", inversedBy="players")
     */
    private $camp;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $trainingFinishes;

    /**
     * @ORM\Column(type="float")
     */
    private $money;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $seasonEndsContract;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PlayerAward", mappedBy="player", cascade={"persist", "remove"})
     */
    private $playerAwards;

    /**
     * Player constructor.
     */
    public function __construct()
    {
        $this->playerStats = new ArrayCollection();
        $this->playerAttributes = new ArrayCollection();
        $this->playerBadges = new ArrayCollection();
        $this->playerAwards = new ArrayCollection();
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
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return Player
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
     * @return Player
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getBorn(): ?\DateTimeInterface
    {
        return $this->born;
    }

    /**
     * @param \DateTimeInterface $born
     *
     * @return Player
     */
    public function setBorn(\DateTimeInterface $born): self
    {
        $this->born = $born;

        return $this;
    }

    /**
     * @return Position|null
     */
    public function getPosition(): ?Position
    {
        return $this->position;
    }

    /**
     * @param Position|null $position
     *
     * @return Player
     */
    public function setPosition(?Position $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country|null $country
     *
     * @return Player
     */
    public function setCountry(?Country $country): self
    {
        $this->country = $country;

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
     * @return Player
     */
    public function setServer(?Server $server): self
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return GameType|null
     */
    public function getFirstType(): ?GameType
    {
        return $this->firstType;
    }

    /**
     * @param GameType|null $firstType
     *
     * @return Player
     */
    public function setFirstType(?GameType $firstType): self
    {
        $this->firstType = $firstType;

        return $this;
    }

    /**
     * @return GameType|null
     */
    public function getSecondType(): ?GameType
    {
        return $this->secondType;
    }

    /**
     * @param GameType|null $secondType
     *
     * @return Player
     */
    public function setSecondType(?GameType $secondType): self
    {
        $this->secondType = $secondType;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsRealPlayer(): ?bool
    {
        return $this->isRealPlayer;
    }

    /**
     * @param bool $isRealPlayer
     *
     * @return Player
     */
    public function setIsRealPlayer(bool $isRealPlayer): self
    {
        $this->isRealPlayer = $isRealPlayer;

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
     * @return PlayerStats|null
     */
    public function getCurrentPlayerStats(): PlayerStats
    {
        if(!$this->playerStats) {
            return null;
        }

        return $this->getPlayerStats()->first();
    }

    /**
     * @param PlayerStats $playerStat
     *
     * @return Player
     */
    public function addPlayerStat(PlayerStats $playerStat): self
    {
        if (!$this->playerStats->contains($playerStat)) {
            $this->playerStats[] = $playerStat;
            $playerStat->setPlayer($this);
        }

        return $this;
    }

    /**
     * @param PlayerStats $playerStat
     *
     * @return Player
     */
    public function removePlayerStat(PlayerStats $playerStat): self
    {
        if ($this->playerStats->contains($playerStat)) {
            $this->playerStats->removeElement($playerStat);
            // set the owning side to null (unless already changed)
            if ($playerStat->getPlayer() === $this) {
                $playerStat->setPlayer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PlayerAttribute[]
     */
    public function getPlayerAttributes(): Collection
    {
        return $this->playerAttributes;
    }

    /**
     * @param PlayerAttribute $playerAttribute
     *
     * @return Player
     */
    public function addPlayerAttribute(PlayerAttribute $playerAttribute): self
    {
        if (!$this->playerAttributes->contains($playerAttribute)) {
            $this->playerAttributes[] = $playerAttribute;
            $playerAttribute->setPlayer($this);
        }

        return $this;
    }

    /**
     * @param PlayerAttribute $playerAttribute
     *
     * @return Player
     */
    public function removePlayerAttribute(PlayerAttribute $playerAttribute): self
    {
        if ($this->playerAttributes->contains($playerAttribute)) {
            $this->playerAttributes->removeElement($playerAttribute);
            // set the owning side to null (unless already changed)
            if ($playerAttribute->getPlayer() === $this) {
                $playerAttribute->setPlayer(null);
            }
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getContractYears(): ?int
    {
        return $this->contractYears;
    }

    /**
     * @param int|null $contractYears
     *
     * @return Player
     */
    public function setContractYears(?int $contractYears): self
    {
        $this->contractYears = $contractYears;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getContractSalary(): ?float
    {
        return $this->contractSalary;
    }

    /**
     * @param float|null $contractSalary
     *
     * @return Player
     */
    public function setContractSalary(?float $contractSalary): self
    {
        $this->contractSalary = $contractSalary;

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
     * @return Player
     */
    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return Collection|PlayerBadge[]
     */
    public function getPlayerBadges(): Collection
    {
        return $this->playerBadges;
    }

    /**
     * @param PlayerBadge $playerBadge
     *
     * @return Player
     */
    public function addPlayerBadge(PlayerBadge $playerBadge): self
    {
        if (!$this->playerBadges->contains($playerBadge)) {
            $this->playerBadges[] = $playerBadge;
            $playerBadge->setPlayer($this);
        }

        return $this;
    }

    /**
     * @param PlayerBadge $playerBadge
     *
     * @return Player
     */
    public function removePlayerBadge(PlayerBadge $playerBadge): self
    {
        if ($this->playerBadges->contains($playerBadge)) {
            $this->playerBadges->removeElement($playerBadge);
            // set the owning side to null (unless already changed)
            if ($playerBadge->getPlayer() === $this) {
                $playerBadge->setPlayer(null);
            }
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWeight(): ?int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     *
     * @return Player
     */
    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getHeight(): ?float
    {
        return $this->height;
    }

    /**
     * @param float $height
     *
     * @return Player
     */
    public function setHeight(float $height): self
    {
        $this->height = $height;

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
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     *
     * @return Player
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getJerseyNumber(): ?int
    {
        return $this->jerseyNumber;
    }

    /**
     * @param int|null $jerseyNumber
     *
     * @return Player
     */
    public function setJerseyNumber(?int $jerseyNumber): self
    {
        $this->jerseyNumber = $jerseyNumber;

        return $this;
    }

    /**
     * @return TrainingCamp|null
     */
    public function getCamp(): ?TrainingCamp
    {
        return $this->camp;
    }

    /**
     * @param TrainingCamp|null $camp
     *
     * @return Player
     */
    public function setCamp(?TrainingCamp $camp): self
    {
        $this->camp = $camp;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getTrainingFinishes(): ?\DateTimeInterface
    {
        return $this->trainingFinishes;
    }

    /**
     * @param \DateTimeInterface|null $trainingFinishes
     *
     * @return Player
     */
    public function setTrainingFinishes(?\DateTimeInterface $trainingFinishes): self
    {
        $this->trainingFinishes = $trainingFinishes;

        return $this;
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function isTrainingFinished(): bool
    {
        $now = new \DateTime('now');

        if($this->getTrainingFinishes() === null || $this->getTrainingFinishes()->getTimestamp() < $now->getTimestamp()) {
            return true;
        }

        return false;
    }

    /**
     * @return float|null
     */
    public function getMoney(): ?float
    {
        return $this->money;
    }

    /**
     * @param float $money
     *
     * @return Player
     */
    public function setMoney(float $money): self
    {
        $this->money = $money;

        return $this;
    }

    /**
     * @return float
     */
    public function getPlayerRating(): float
    {
        $rating = 0.0;
        $attributeCount = 0;
        $attributes = $this->getPlayerAttributes();

        if(count($attributes) > 0) {
            foreach($attributes as $playerAttribute) {
                $rating += $playerAttribute->getValue();
                $attributeCount++;
            }

            return round(($rating / $attributeCount), 0);
        }

        return 0;
    }

    /**
     * @return int|null
     */
    public function getSeasonEndsContract(): ?int
    {
        return $this->seasonEndsContract;
    }

    /**
     * @param int|null $seasonEndsContract
     *
     * @return Player
     */
    public function setSeasonEndsContract(?int $seasonEndsContract): self
    {
        $this->seasonEndsContract = $seasonEndsContract;

        return $this;
    }

    /**
     * @return Collection|PlayerAward[]
     */
    public function getPlayerAwards(): Collection
    {
        return $this->playerAwards;
    }

    /**
     * @param PlayerAward $playerAward
     *
     * @return Player
     */
    public function addPlayerAward(PlayerAward $playerAward): self
    {
        if (!$this->playerAwards->contains($playerAward)) {
            $this->playerAwards[] = $playerAward;
            $playerAward->setPlayer($this);
        }

        return $this;
    }

    /**
     * @param PlayerAward $playerAward
     *
     * @return Player
     */
    public function removePlayerAward(PlayerAward $playerAward): self
    {
        if ($this->playerAwards->contains($playerAward)) {
            $this->playerAwards->removeElement($playerAward);
            // set the owning side to null (unless already changed)
            if ($playerAward->getPlayer() === $this) {
                $playerAward->setPlayer(null);
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getChampionRings(): int
    {
        $awards = $this->getPlayerAwards()->filter(function (PlayerAward $playerAward) {
            return $playerAward->getAward() === Award::PLAYER_CHAMPION;
        });

        return count($awards);
    }

    /**
     * @return int
     */
    public function getMVPs(): int
    {
        $mvps = $this->getPlayerAwards()->filter(function (PlayerAward $playerAward) {
            return $playerAward->getAward() === Award::PLAYER_MVP;
        });

        return count($mvps);
    }

    /**
     * @return int
     */
    public function getDPOYs(): int
    {
        $dpoy = $this->getPlayerAwards()->filter(function (PlayerAward $playerAward) {
            return $playerAward->getAward() === Award::PLAYER_DPOY;
        });

        return count($dpoy);
    }
}
