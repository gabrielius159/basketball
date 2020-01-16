<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrainingCampRepository")
 * @Vich\Uploadable
 */
class TrainingCamp
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Attribute", inversedBy="trainingCamps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $attributeToImprove;

    /**
     * @ORM\Column(type="float")
     */
    private $skillPoints;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Player", mappedBy="camp")
     */
    private $players;

    /**
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="training_camps", fileNameProperty="image", size="imageSize")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Badge")
     */
    private $badge;

    /**
     * TrainingCamp constructor.
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
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
     * @return TrainingCamp
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Attribute|null
     */
    public function getAttributeToImprove(): ?Attribute
    {
        return $this->attributeToImprove;
    }

    /**
     * @param Attribute|null $attributeToImprove
     *
     * @return TrainingCamp
     */
    public function setAttributeToImprove(?Attribute $attributeToImprove): self
    {
        $this->attributeToImprove = $attributeToImprove;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getSkillPoints(): ?float
    {
        return $this->skillPoints;
    }

    /**
     * @param float $skillPoints
     *
     * @return TrainingCamp
     */
    public function setSkillPoints(float $skillPoints): self
    {
        $this->skillPoints = $skillPoints;

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
     * @return TrainingCamp
     */
    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
            $player->setCamp($this);
        }

        return $this;
    }

    /**
     * @param Player $player
     *
     * @return TrainingCamp
     */
    public function removePlayer(Player $player): self
    {
        if ($this->players->contains($player)) {
            $this->players->removeElement($player);
            // set the owning side to null (unless already changed)
            if ($player->getCamp() === $this) {
                $player->setCamp(null);
            }
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDuration(): ?int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     *
     * @return TrainingCamp
     */
    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return TrainingCamp
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;

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
     * @return Badge|null
     */
    public function getBadge(): ?Badge
    {
        return $this->badge;
    }

    /**
     * @param Badge|null $badge
     *
     * @return TrainingCamp
     */
    public function setBadge(?Badge $badge): self
    {
        $this->badge = $badge;

        return $this;
    }
}
