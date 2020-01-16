<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRewardRepository")
 * @Vich\Uploadable
 */
class UserReward
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
    private $mvpAwardName;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $dpoyAwardName;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Season", inversedBy="userReward", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $season;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $imageMvp;

    /**
     * @Vich\UploadableField(mapping="user_rewards_mvp", fileNameProperty="imageMvp", size="imageMvpSize")
     * @var File
     */
    private $imageMvpFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedMvpAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $imageMvpSize;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $imageDpoy;

    /**
     * @Vich\UploadableField(mapping="user_rewards_dpoy", fileNameProperty="imageDpoy", size="imageDpoySize")
     * @var File
     */
    private $imageDpoyFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDpoyAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $imageDpoySize;

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
    public function getMvpAwardName(): ?string
    {
        return $this->mvpAwardName;
    }

    /**
     * @param string $mvpAwardName
     *
     * @return UserReward
     */
    public function setMvpAwardName(string $mvpAwardName): self
    {
        $this->mvpAwardName = $mvpAwardName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDpoyAwardName(): ?string
    {
        return $this->dpoyAwardName;
    }

    /**
     * @param string $dpoyAwardName
     *
     * @return UserReward
     */
    public function setDpoyAwardName(string $dpoyAwardName): self
    {
        $this->dpoyAwardName = $dpoyAwardName;

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
     * @param Season $season
     *
     * @return UserReward
     */
    public function setSeason(Season $season): self
    {
        $this->season = $season;

        return $this;
    }

    /**
     * @param File|null $image
     *
     * @throws \Exception
     */
    public function setImageMvpFile(File $image = null)
    {
        $this->imageMvpFile = $image;

        if ($image) {
            $this->updatedMvpAt = new \DateTime('now');
        }
    }

    /**
     * @return File
     */
    public function getImageMvpFile()
    {
        return $this->imageMvpFile;
    }

    /**
     * @param $image
     */
    public function setImageMvp($image)
    {
        $this->imageMvp = $image;
    }

    /**
     * @return string
     */
    public function getImageMvp()
    {
        return $this->imageMvp;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedMvpAt(): ?\DateTimeInterface
    {
        return $this->updatedMvpAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     *
     * @return UserReward
     */
    public function setUpdatedMvpAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedMvpAt = $updatedAt;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getImageMvpSize(): ?int
    {
        return $this->imageMvpSize;
    }

    /**
     * @param int|null $imageSize
     *
     * @return UserReward
     */
    public function setImageMvpSize(?int $imageSize): self
    {
        $this->imageMvpSize = $imageSize;

        return $this;
    }

    /**
     * @param File|null $image
     *
     * @throws \Exception
     */
    public function setImageDpoyFile(File $image = null)
    {
        $this->imageDpoyFile = $image;

        if ($image) {
            $this->updatedDpoyAt = new \DateTime('now');
        }
    }

    /**
     * @return File
     */
    public function getImageDpoyFile()
    {
        return $this->imageDpoyFile;
    }

    /**
     * @param $image
     */
    public function setImageDpoy($image)
    {
        $this->imageDpoy = $image;
    }

    /**
     * @return string
     */
    public function getImageDpoy()
    {
        return $this->imageDpoy;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedDpoyAt(): ?\DateTimeInterface
    {
        return $this->updatedDpoyAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     *
     * @return UserReward
     */
    public function setUpdatedDpoyAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedDpoyAt = $updatedAt;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getImageDpoySize(): ?int
    {
        return $this->imageDpoySize;
    }

    /**
     * @param int|null $imageSize
     *
     * @return UserReward
     */
    public function setImageDpoySize(?int $imageSize): self
    {
        $this->imageDpoySize = $imageSize;

        return $this;
    }
}
