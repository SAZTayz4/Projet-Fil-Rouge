<?php

namespace App\Repository;

use App\Entity\HistoriqueAbonnement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HistoriqueAbonnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriqueAbonnement::class);
    }

    public function findByUtilisateur($utilisateur)
    {
        return $this->createQueryBuilder('ha')
            ->andWhere('ha.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('ha.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 