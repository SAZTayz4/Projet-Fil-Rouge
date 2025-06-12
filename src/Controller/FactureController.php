<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Entity\User;
use App\Repository\FactureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FactureController extends AbstractController
{
    private string $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    #[Route('/facture/{id}/telecharger', name: 'facture_telecharger')]
    public function telecharger(Facture $facture): Response
    {
        // Vérifier que l'utilisateur est connecté et a le droit d'accéder à cette facture
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        if ($facture->getUtilisateur()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette facture.');
        }

        $pdfPath = $this->uploadDir . '/' . $facture->getDetails()['pdf_path'];
        
        if (!file_exists($pdfPath)) {
            throw new NotFoundHttpException('La facture n\'a pas été trouvée.');
        }

        return new BinaryFileResponse($pdfPath, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . basename($pdfPath) . '"'
        ]);
    }
} 