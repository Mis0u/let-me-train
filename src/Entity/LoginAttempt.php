<?php

namespace App\Entity;

use App\Repository\LoginAttemptRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LoginAttemptRepository::class)
 */
class LoginAttempt
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $ipAddress;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $date;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $country;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $email;

    public function __construct(?string $ipAddress, string $country, string $email)
    {
        $this->ipAddress = $ipAddress;
        $this->country   = $country;
        $this->email     = $email;
        $this->date      = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }
}
