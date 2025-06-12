<?php

namespace App\Entity;

use App\Repository\HistoriqueAbonnementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueAbonnementRepository::class)]
#[ORM\Table(name: 'historique_abonnement')]
class HistoriqueAbonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private User $utilisateur;

    #[ORM\ManyToOne(targetEntity: Abonnement::class)]
    #[ORM\JoinColumn(name: 'abonnement_id', referencedColumnName: 'id', nullable: false)]
    private Abonnement $abonnement;

    #[ORM\Column(name: 'dateDebut', type: 'date')]
    private \DateTime $dateDebut;

    #[ORM\Column(name: 'dateFin', type: 'date', nullable: true)]
    private ?\DateTime $dateFin = null;

    #[ORM\Column(name: 'statut', type: 'string', columnDefinition: "enum('actif','expiré','annulé')")]
    private string $statut = 'actif';

    #[ORM\Column(name: 'estActuel', type: 'boolean', options: ['default' => false])]
    private bool $estActuel = false;

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

    public function getUtilisateur(): User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(User $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getAbonnement(): Abonnement
    {
        return $this->abonnement;
    }

    public function setAbonnement(Abonnement $abonnement): self
    {
        $this->abonnement = $abonnement;
        return $this;
    }

    public function getDateDebut(): \DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTime $dateFin): self
    {
        $this->dateFin = $dateFin;
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

    public function isEstActuel(): bool
    {
        return $this->estActuel;
    }

    public function setEstActuel(bool $estActuel): self
    {
        $this->estActuel = $estActuel;
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