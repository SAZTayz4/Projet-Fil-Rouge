<?php

namespace App\Entity;

use App\Repository\AbonnementRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
#[ORM\Table(name: 'abonnement')]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', columnDefinition: "enum('gratuit','débutant','intermédiaire','avancé')")]
    private string $type;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $prix;

    #[ORM\Column]
    private int $limitesVerifications;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $accesChatbot = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $accesAutoCop = false;

    #[ORM\Column(nullable: true)]
    private ?int $duree = null;

    #[ORM\Column(name: 'created_at')]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'updated_at')]
    private \DateTime $updatedAt;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'abonnement')]
    private $utilisateurs;

    #[ORM\OneToMany(targetEntity: HistoriqueAbonnement::class, mappedBy: 'abonnement')]
    private $historiques;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getPrix(): float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getLimitesVerifications(): int
    {
        return $this->limitesVerifications;
    }

    public function setLimitesVerifications(int $limitesVerifications): self
    {
        $this->limitesVerifications = $limitesVerifications;
        return $this;
    }

    public function isAccesChatbot(): bool
    {
        return $this->accesChatbot;
    }

    public function setAccesChatbot(bool $accesChatbot): self
    {
        $this->accesChatbot = $accesChatbot;
        return $this;
    }

    public function isAccesAutoCop(): bool
    {
        return $this->accesAutoCop;
    }

    public function setAccesAutoCop(bool $accesAutoCop): self
    {
        $this->accesAutoCop = $accesAutoCop;
        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;
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
} 