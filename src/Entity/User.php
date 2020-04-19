<?php

namespace App\Entity;

use App\Constant\LanguageConstants;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Role", mappedBy="user", orphanRemoval=true)
     */
    private $roles;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Player", mappedBy="user", cascade={"persist", "remove"})
     */
    private $player;

    /**
     * @ORM\Column(type="string", length=10, options={"default": "en"})
     */
    private $locale = LanguageConstants::DEFAULT_LOCALE;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
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
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = [];

        foreach($this->roles as $role) {
            $roles[] = $role->getRole();
        }

        if(!$roles) {
            $roles[] = 'ROLE_USER';
        }

        return $roles;
    }

    /**
     * @return string|null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials()
    {

    }

    /**
     * @param Role $role
     *
     * @return User
     */
    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setUser($this);
        }

        return $this;
    }

    /**
     * @param Role $role
     *
     * @return User
     */
    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
            // set the owning side to null (unless already changed)
            if ($role->getUser() === $this) {
                $role->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Player|null
     */
    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @param Player|null $player
     *
     * @return User
     */
    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        // set (or unset) the owning side of the relation if necessary
        $newUser = $player === null ? null : $this;
        if ($newUser !== $player->getUser()) {
            $player->setUser($newUser);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return User
     */
    public function setLocale(string $locale): User
    {
        $this->locale = $locale;

        return $this;
    }
}
