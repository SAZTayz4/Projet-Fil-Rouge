<?php

namespace App\Repository;

use App\Entity\Paiement;
use Doctrine\ORM\EntityRepository;

class PaiementRepository extends EntityRepository
{
    public function findByUtilisateur($utilisateur)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 