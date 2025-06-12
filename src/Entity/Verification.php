<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

#[ORM\Entity]
#[ORM\Table(name: 'verification')]
class Verification
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id')]
    private $utilisateur;

    #[ORM\ManyToOne(targetEntity: Annonce::class)]
    #[ORM\JoinColumn(name: 'annonce_id', referencedColumnName: 'id')]
    private $annonce;

    #[ORM\Column(type: 'string', columnDefinition: "enum('legit','fake')")]
    private $resultat;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private $precision;

    #[ORM\Column(type: 'string', columnDefinition: "enum('IA_image','IA_texte','AutoCop')")]
    private $methode;

    #[ORM\Column(type: 'datetime')]
    private $created_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getAnnonce(): ?Annonce
    {
        return $this->annonce;
    }

    public function setAnnonce(?Annonce $annonce): self
    {
        $this->annonce = $annonce;
        return $this;
    }

    public function getResultat(): ?string
    {
        return $this->resultat;
    }

    public function setResultat(string $resultat): self
    {
        $this->resultat = $resultat;
        return $this;
    }

    public function getPrecision(): ?float
    {
        return $this->precision;
    }

    public function setPrecision(?float $precision): self
    {
        $this->precision = $precision;
        return $this;
    }

    public function getMethode(): ?string
    {
        return $this->methode;
    }

    public function setMethode(string $methode): self
    {
        $this->methode = $methode;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }
} 