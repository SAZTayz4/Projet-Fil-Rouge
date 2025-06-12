<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
#[ORM\Table(name: 'facture')]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Paiement::class)]
    #[ORM\JoinColumn(name: 'paiement_id', referencedColumnName: 'id', nullable: false)]
    private Paiement $paiement;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private User $utilisateur;

    #[ORM\Column(name: 'numeroFacture', length: 50, unique: true)]
    private string $numeroFacture;

    #[ORM\Column(name: 'montantTotal', type: 'decimal', precision: 10, scale: 2)]
    private float $montantTotal;

    #[ORM\Column(type: 'json')]
    private array $details;

    #[ORM\Column(name: 'statut', type: 'string', columnDefinition: "enum('payée','en_attente','annulée')")]
    private string $statut = 'en_attente';

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaiement(): Paiement
    {
        return $this->paiement;
    }

    public function setPaiement(Paiement $paiement): self
    {
        $this->paiement = $paiement;
        return $this;
    }

    public function getUtilisateur(): User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(User $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getNumeroFacture(): string
    {
        return $this->numeroFacture;
    }

    public function setNumeroFacture(string $numeroFacture): self
    {
        $this->numeroFacture = $numeroFacture;
        return $this;
    }

    public function getMontantTotal(): float
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(float $montantTotal): self
    {
        $this->montantTotal = $montantTotal;
        return $this;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function setDetails(array $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
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
} 