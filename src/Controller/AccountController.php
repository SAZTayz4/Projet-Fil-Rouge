<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AccountController extends AbstractController
{
    #[Route('/compte', name: 'app_account')]
    #[IsGranted('ROLE_USER')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Inclure le fichier PHP directement
        ob_start();
        include __DIR__ . '/../../Frontend/HTML/comptes.php';
        $content = ob_get_clean();
        
        return new Response($content);
    }
} 