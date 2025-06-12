<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Inclure votre fichier PHP existant pour la page d'accueil
        require_once __DIR__ . '/../../Frontend/HTML/home.php';
        
        return new Response();
    }
} 