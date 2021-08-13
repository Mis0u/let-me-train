<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\IdTrait;
use App\Helper\CreatedAtTrait;
use App\Repository\MuscleRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MuscleRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Muscle
{
    use IdTrait;
    use CreatedAtTrait;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $target;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private string $upperOrLowerBody;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $slug;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="muscle")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $muscleOwner;

    /**
     * @ORM\OneToMany(targetEntity=Exercice::class, mappedBy="muscle")
     */
    private Collection $exercices;

    public function __construct()
    {
        $this->exercices = new ArrayCollection();
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getUpperOrLowerBody(): ?string
    {
        return $this->upperOrLowerBody;
    }

    public function setUpperOrLowerBody(string $upperOrLowerBody): self
    {
        $this->upperOrLowerBody = $upperOrLowerBody;

        return $this;
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

    public function getMuscleOwner(): ?User
    {
        return $this->muscleOwner;
    }

    public function setMuscleOwner(?User $muscleOwner): self
    {
        $this->muscleOwner = $muscleOwner;

        return $this;
    }

    /**
     * @return Collection|Exercice[]
     */
    public function getExercices(): Collection
    {
        return $this->exercices;
    }

    public function addExercice(Exercice $exercice): self
    {
        if (!$this->exercices->contains($exercice)) {
            $this->exercices[] = $exercice;
            $exercice->setMuscle($this);
        }

        return $this;
    }

    public function removeExercice(Exercice $exercice): self
    {
        if ($this->exercices->removeElement($exercice)) {
            // set the owning side to null (unless already changed)
            if ($exercice->getMuscle() === $this) {
                $exercice->setMuscle(null);
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
        return $this->slug = $slugger->slugify((string) $this->getTarget());
    }
}
