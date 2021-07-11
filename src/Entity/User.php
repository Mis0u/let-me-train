<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private string $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private string $email;

    /**
     * @ORM\Column(type="json")
     * @var string[]
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private string $gender;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $slug;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $alias;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $country;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $lastConnection;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isBlockedByAttempt;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isBlockedByAdmin;

    /**
     * @ORM\OneToMany(targetEntity=Muscle::class, mappedBy="muscleOwner")
     */
    private Collection $muscle;

    public function __construct()
    {
        $this->muscle = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getLastConnection(): ?\DateTimeImmutable
    {
        return $this->lastConnection;
    }

    public function setLastConnection(?\DateTimeImmutable $lastConnection): self
    {
        $this->lastConnection = $lastConnection;

        return $this;
    }

    public function getIsBlockedByAttempt(): ?bool
    {
        return $this->isBlockedByAttempt;
    }

    public function setIsBlockedByAttempt(bool $isBlockedByAttempt): self
    {
        $this->isBlockedByAttempt = $isBlockedByAttempt;

        return $this;
    }

    public function getIsBlockedByAdmin(): ?bool
    {
        return $this->isBlockedByAdmin;
    }

    public function setIsBlockedByAdmin(bool $isBlockedByAdmin): self
    {
        $this->isBlockedByAdmin = $isBlockedByAdmin;

        return $this;
    }

    /**
     * @return Collection|Muscle[]
     */
    public function getMuscle(): Collection
    {
        return $this->muscle;
    }

    public function addMuscle(Muscle $muscle): self
    {
        if (!$this->muscle->contains($muscle)) {
            $this->muscle[] = $muscle;
            $muscle->setMuscleOwner($this);
        }

        return $this;
    }

    public function removeMuscle(Muscle $muscle): self
    {
        if ($this->muscle->removeElement($muscle)) {
            // set the owning side to null (unless already changed)
            if ($muscle->getMuscleOwner() === $this) {
                $muscle->setMuscleOwner(null);
            }
        }

        return $this;
    }
}
