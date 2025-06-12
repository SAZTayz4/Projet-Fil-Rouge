<?php

namespace App\Service;

use App\Entity\Paiement;
use App\Entity\User;
use App\Entity\Abonnement;
use App\Entity\HistoriqueAbonnement;
use App\Entity\Facture;
use Doctrine\ORM\EntityManagerInterface;

class PaiementService
{
    private EntityManagerInterface $entityManager;
    private FactureService $factureService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FactureService $factureService
    ) {
        $this->entityManager = $entityManager;
        $this->factureService = $factureService;
    }

    public function traiterPaiement(User $user, Abonnement $abonnement): Paiement
    {
        // Créer un paiement factice
        $paiement = new Paiement();
        $paiement->setUtilisateur($user);
        $paiement->setMontant($abonnement->getPrix());
        $paiement->setTypeAbonnement($abonnement->getType());
        $paiement->setMethodePaiement('Simulation');
        $paiement->setStatut('réussi');

        // Sauvegarder le paiement
        $this->entityManager->persist($paiement);
        $this->entityManager->flush();

        // Générer la facture factice
        $facture = $this->factureService->genererFacture($paiement, $user);
        $paiement->setFacture($facture);

        // Mettre à jour l'abonnement de l'utilisateur
        $user->setAbonnement($abonnement);
        $this->entityManager->persist($user);
        $this->entityManager->persist($facture);
        $this->entityManager->flush();

        return $paiement;
    }

    private function mettreAJourHistoriqueAbonnement(User $user, Abonnement $abonnement): void
    {
        // Désactiver l'ancien abonnement actif s'il existe
        $ancienHistorique = $this->entityManager->getRepository(HistoriqueAbonnement::class)
            ->findOneBy(['utilisateur' => $user, 'estActuel' => true]);

        if ($ancienHistorique) {
            $ancienHistorique->setEstActuel(false);
            $ancienHistorique->setStatut('expiré');
            $ancienHistorique->setDateFin(new \DateTime());
            $this->entityManager->persist($ancienHistorique);
        }

        // Créer le nouvel historique d'abonnement
        $historique = new HistoriqueAbonnement();
        $historique->setUtilisateur($user);
        $historique->setAbonnement($abonnement);
        $historique->setDateDebut(new \DateTime());
        
        // Calculer la date de fin si l'abonnement a une durée
        if ($abonnement->getDuree()) {
            $dateFin = (new \DateTime())->modify('+' . $abonnement->getDuree() . ' days');
            $historique->setDateFin($dateFin);
        }
        
        $historique->setStatut('actif');
        $historique->setEstActuel(true);

        // Mettre à jour l'abonnement de l'utilisateur
        $user->setAbonnement($abonnement);

        $this->entityManager->persist($historique);
        $this->entityManager->persist($user);
    }
} 