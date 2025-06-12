<?php

namespace App\Controller;

use App\Entity\DropSneaker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DropsController extends AbstractController
{
    #[Route('/drops', name: 'app_drops')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $selectedBrand = $request->query->get('brand');
        $selectedPriceRange = $request->query->get('price');
        $searchQuery = $request->query->get('search');
        $page = $request->query->getInt('page', 1);
        $itemsPerPage = 12;

        $repository = $entityManager->getRepository(DropSneaker::class);
        $queryBuilder = $repository->createQueryBuilder('d');

        if ($selectedBrand) {
            $queryBuilder->andWhere('d.marque = :brand')
                        ->setParameter('brand', $selectedBrand);
        }

        if ($searchQuery) {
            $queryBuilder->andWhere('d.nom LIKE :search OR d.marque LIKE :search')
                        ->setParameter('search', '%' . $searchQuery . '%');
        }

        if ($selectedPriceRange) {
            $price = (int)str_replace(['€', ' '], '', $selectedPriceRange);
            switch($selectedPriceRange) {
                case '0-100':
                    $queryBuilder->andWhere('d.prixRetail <= :price')
                                ->setParameter('price', 100);
                    break;
                case '100-200':
                    $queryBuilder->andWhere('d.prixRetail > :minPrice AND d.prixRetail <= :maxPrice')
                                ->setParameter('minPrice', 100)
                                ->setParameter('maxPrice', 200);
                    break;
                case '200+':
                    $queryBuilder->andWhere('d.prixRetail > :price')
                                ->setParameter('price', 200);
                    break;
            }
        }

        $queryBuilder->orderBy('d.dateSortie', 'ASC');
        
        $totalItems = count($queryBuilder->getQuery()->getResult());
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        $queryBuilder->setFirstResult(($page - 1) * $itemsPerPage)
                    ->setMaxResults($itemsPerPage);

        $drops = $queryBuilder->getQuery()->getResult();
        $brands = $entityManager->getRepository(DropSneaker::class)
                              ->createQueryBuilder('d')
                              ->select('DISTINCT d.marque')
                              ->getQuery()
                              ->getResult();

        // Inclure votre fichier PHP existant
        require_once __DIR__ . '/../../Frontend/HTML/drops.php';
        
        return new Response();
    }

    #[Route('/drops/{id}', name: 'app_drop_show')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $drop = $entityManager->getRepository(DropSneaker::class)->find($id);

        if (!$drop) {
            throw $this->createNotFoundException('Drop non trouvé');
        }

        // Inclure votre fichier PHP existant pour l'affichage des détails
        require_once __DIR__ . '/../../Frontend/HTML/drop-details.php';
        
        return new Response();
    }
} 