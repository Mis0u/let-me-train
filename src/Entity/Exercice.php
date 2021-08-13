<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\IdTrait;
use App\Helper\CreatedAtTrait;
use App\Repository\ExerciceRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExerciceRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Exercice
{
    use IdTrait;
    use CreatedAtTrait;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $slug;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $name;

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

    public function getSlug(): string
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

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setSlugValue(): string
    {
        $slugger = new Slugify();
        return $this->slug = $slugger->slugify((string) $this->getName());
    }
}
