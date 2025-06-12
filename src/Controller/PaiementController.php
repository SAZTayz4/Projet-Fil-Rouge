<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Service\PaiementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/paiement')]
class PaiementController extends AbstractController
{
    private PaiementService $paiementService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PaiementService $paiementService,
        EntityManagerInterface $entityManager
    ) {
        $this->paiementService = $paiementService;
        $this->entityManager = $entityManager;
    }

    #[Route('/abonnement/{id}', name: 'app_paiement_abonnement', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function acheterAbonnement(Abonnement $abonnement): Response
    {
        try {
            $paiement = $this->paiementService->traiterPaiement(
                $this->getUser(),
                $abonnement
            );

            $this->addFlash('success', 'Votre abonnement a été activé avec succès ! Une facture a été générée.');
            return $this->redirectToRoute('app_compte');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la simulation du paiement.');
            return $this->redirectToRoute('app_abonnement_liste');
        }
    }

    #[Route('/liste', name: 'app_abonnement_liste')]
    public function listeAbonnements(): Response
    {
        $abonnements = $this->entityManager
            ->getRepository(Abonnement::class)
            ->findAll();

        return $this->render('abonnement/liste.html.twig', [
            'abonnements' => $abonnements
        ]);
    }
} 