<?php

namespace App\Entity;

use App\Repository\PaiementRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
#[ORM\Table(name: 'paiement')]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id')]
    private User $utilisateur;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $montant;

    #[ORM\Column(type: 'string', columnDefinition: "enum('gratuit','débutant','intermédiaire','avancé')", nullable: true)]
    private ?string $typeAbonnement = null;

    #[ORM\Column(type: 'string', columnDefinition: "enum('MangoPay','Stripe')")]
    private string $methodePaiement;

    #[ORM\Column(type: 'string', columnDefinition: "enum('réussi','échoué')")]
    private string $statut;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $created_at;

    #[ORM\OneToOne(targetEntity: Facture::class, mappedBy: 'paiement')]
    private ?Facture $facture = null;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMontant(): float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getTypeAbonnement(): ?string
    {
        return $this->typeAbonnement;
    }

    public function setTypeAbonnement(?string $typeAbonnement): self
    {
        $this->typeAbonnement = $typeAbonnement;
        return $this;
    }

    public function getMethodePaiement(): string
    {
        return $this->methodePaiement;
    }

    public function setMethodePaiement(string $methodePaiement): self
    {
        $this->methodePaiement = $methodePaiement;
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
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): self
    {
        $this->facture = $facture;
        return $this;
    }
} 