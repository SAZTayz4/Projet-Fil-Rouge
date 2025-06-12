<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Headers pour JSON et CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Fonction pour envoyer une réponse JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse([
        'error' => true,
        'message' => 'Méthode non autorisée'
    ], 405);
}

// Vérification de l'authentification
if (!isset($_SESSION['utilisateur_id'])) {
    sendJsonResponse([
        'error' => true,
        'message' => 'Authentification requise'
    ], 401);
}

try {
    $pdo = getConnection();
    
    // Paramètres de pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(50, intval($_GET['limit']))) : 6;
    $offset = ($page - 1) * $limit;
    
    // Compte total d'analyses
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM analyses_ia 
        WHERE utilisateur_id = ?
    ");
    $countStmt->execute([$_SESSION['utilisateur_id']]);
    $total = $countStmt->fetchColumn();
    
    // Récupération des analyses
    $stmt = $pdo->prepare("
        SELECT 
            id,
            date_analyse,
            nombre_images,
            resultats
        FROM analyses_ia 
        WHERE utilisateur_id = ?
        ORDER BY date_analyse DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$_SESSION['utilisateur_id'], $limit, $offset]);
    
    $analyses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatage des résultats
    foreach ($analyses as &$analyse) {
        $analyse['resultats'] = json_decode($analyse['resultats'], true);
        $analyse['date_analyse'] = date('d/m/Y H:i', strtotime($analyse['date_analyse']));
    }
    
    // Calcul de la pagination
    $totalPages = ceil($total / $limit);
    
    // Réponse
    sendJsonResponse([
        'error' => false,
        'data' => [
            'analyses' => $analyses,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => $totalPages
            ]
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur base de données: " . $e->getMessage());
    sendJsonResponse([
        'error' => true,
        'message' => 'Erreur lors de la récupération des analyses'
    ], 500);
} 