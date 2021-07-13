<?php

namespace App\Entity;

use App\Repository\ExerciceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;

/**
 * @ORM\Entity(repositoryClass=ExerciceRepository::class)
 */
class Exercice
{
    /**
     * @ORM\Id
     * @ORM\Column(type="ulid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UlidGenerator::class)
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $slug;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Muscle::class, inversedBy="exercices")
     */
    private ?Muscle $muscle;

    /**
     * @ORM\OneToMany(targetEntity=Repetition::class, mappedBy="exercice")
     */
    private Collection $repetitions;

    public function __construct()
    {
        $this->repetitions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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

    public function getMuscle(): ?Muscle
    {
        return $this->muscle;
    }

    public function setMuscle(?Muscle $muscle): self
    {
        $this->muscle = $muscle;

        return $this;
    }

    /**
     * @return Collection|Repetition[]
     */
    public function getRepetitions(): Collection
    {
        return $this->repetitions;
    }

    public function addRepetition(Repetition $repetition): self
    {
        if (!$this->repetitions->contains($repetition)) {
            $this->repetitions[] = $repetition;
            $repetition->setExercice($this);
        }

        return $this;
    }

    public function removeRepetition(Repetition $repetition): self
    {
        if ($this->repetitions->removeElement($repetition)) {
            // set the owning side to null (unless already changed)
            if ($repetition->getExercice() === $this) {
                $repetition->setExercice(null);
            }
        }

        return $this;
    }
}
