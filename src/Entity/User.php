<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'utilisateurs')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $nom;

    #[ORM\Column(length: 100, unique: true)]
    private string $email;

    #[ORM\Column(name: 'motDePasse', length: 255)]
    private string $motDePasse;

    #[ORM\Column(type: 'string', columnDefinition: "enum('admin','client')", options: ['default' => 'client'])]
    private string $role = 'client';

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $token = null;

    #[ORM\ManyToOne(targetEntity: Abonnement::class)]
    #[ORM\JoinColumn(name: 'abonnement_id', referencedColumnName: 'id', nullable: true)]
    private ?Abonnement $abonnement = null;

    #[ORM\Column(name: 'created_at')]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'updated_at')]
    private \DateTime $updatedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $last_login;

    #[ORM\Column(name: 'photo_profil', length: 255, nullable: true)]
    private ?string $photoProfil = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getAbonnement(): ?Abonnement
    {
        return $this->abonnement;
    }

    public function setAbonnement(?Abonnement $abonnement): self
    {
        $this->abonnement = $abonnement;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->motDePasse);
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->last_login;
    }

    public function setLastLogin(\DateTimeInterface $last_login): self
    {
        $this->last_login = $last_login;
        return $this;
    }

    public function getRoles(): array
    {
        return [$this->role];
    }

    public function getPassword(): string
    {
        return $this->motDePasse;
    }

    public function setPassword(string $password): self
    {
        $this->motDePasse = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getPhotoProfil(): ?string
    {
        return $this->photoProfil;
    }

    public function setPhotoProfil(?string $photoProfil): self
    {
        $this->photoProfil = $photoProfil;
        return $this;
    }
} 