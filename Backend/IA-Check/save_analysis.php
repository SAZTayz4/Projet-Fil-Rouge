<?php
// Désactiver l'affichage des erreurs PHP
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Démarrer la session et inclure la configuration
session_start();
require_once __DIR__ . '/../config/database.php';

// Forcer le type de contenu en JSON
header('Content-Type: application/json');

// Fonction pour envoyer une réponse JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Gestionnaire d'erreurs personnalisé
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Erreur PHP dans save_analysis.php : [$errno] $errstr dans $errfile à la ligne $errline");
    sendJsonResponse([
        'success' => false,
        'message' => 'Une erreur interne est survenue'
    ], 500);
});

// Gestionnaire d'exceptions
set_exception_handler(function($e) {
    error_log("Exception dans save_analysis.php : " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l\'enregistrement de l\'analyse'
    ], 500);
});

// Debug session (uniquement en log)
error_log("Session debug save_analysis: " . print_r($_SESSION, true));

// Vérification de l'authentification
if (!isset($_SESSION['utilisateur_id']) && !isset($_SESSION['user_id'])) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Non authentifié (aucun ID utilisateur dans la session)'
    ], 401);
}

try {
    // Récupération de l'ID utilisateur
    $userId = $_SESSION['utilisateur_id'] ?? $_SESSION['user_id'];
    error_log("Tentative de sauvegarde pour l'utilisateur ID: " . $userId);
    
    // Lecture et décodage des données JSON
    $input = file_get_contents('php://input');
    if (!$input) {
        error_log("Aucune donnée reçue dans le corps de la requête");
        throw new Exception('Aucune donnée reçue');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Erreur de décodage JSON: " . json_last_error_msg());
        throw new Exception('Données JSON invalides: ' . json_last_error_msg());
    }

    // Validation des données requises
    if (!isset($data['resultats']) || !isset($data['nombre_images'])) {
        error_log("Données manquantes: " . print_r($data, true));
        throw new Exception('Données manquantes: resultats et nombre_images sont requis');
    }

    // Validation de la structure des résultats
    if (!isset($data['resultats']['predictions']) || !is_array($data['resultats']['predictions'])) {
        error_log("Structure des résultats invalide: " . print_r($data['resultats'], true));
        throw new Exception('Structure des résultats invalide: predictions manquantes ou invalides');
    }

    // Validation du nombre d'images
    if (!is_numeric($data['nombre_images']) || $data['nombre_images'] <= 0) {
        error_log("Nombre d'images invalide: " . $data['nombre_images']);
        throw new Exception('Nombre d\'images invalide');
    }

    if ($data['nombre_images'] !== count($data['resultats']['predictions'])) {
        error_log("Nombre d'images incohérent: attendu " . $data['nombre_images'] . ", reçu " . count($data['resultats']['predictions']));
        throw new Exception('Nombre d\'images incohérent avec les prédictions');
    }

    // Validation de chaque prédiction
    foreach ($data['resultats']['predictions'] as $index => $pred) {
        if (!isset($pred['score']) || !isset($pred['statut']) || !isset($pred['filename'])) {
            error_log("Prédiction invalide à l'index $index: " . print_r($pred, true));
            throw new Exception('Structure de prédiction invalide: champs requis manquants');
        }
        
        // Validation du score
        if (!is_numeric($pred['score'])) {
            error_log("Score non numérique dans la prédiction $index: " . $pred['score']);
            throw new Exception('Score invalide: doit être un nombre');
        }
        
        $pred['score'] = (float)$pred['score'];
        if ($pred['score'] < 0 || $pred['score'] > 1) {
            error_log("Score hors limites dans la prédiction $index: " . $pred['score']);
            throw new Exception('Score invalide: doit être entre 0 et 1');
        }

        // Validation du statut
        if (!in_array($pred['statut'], ['FAKE', 'LEGIT', 'DOUTEUX'])) {
            error_log("Statut invalide dans la prédiction $index: " . $pred['statut']);
            throw new Exception('Statut invalide: doit être FAKE, LEGIT ou DOUTEUX');
        }

        // Nettoyage des données
        $data['resultats']['predictions'][$index] = [
            'score' => $pred['score'],
            'statut' => $pred['statut'],
            'filename' => $pred['filename'],
            'explanation' => isset($pred['explanation']) ? (string)$pred['explanation'] : '',
            'ponderation' => isset($pred['ponderation']) ? (float)$pred['ponderation'] : 1.0
        ];
    }

    // Ajout de métadonnées
    $data['resultats']['metadata'] = [
        'version' => $data['resultats']['version'] ?? '1.0',
        'date_analyse' => $data['resultats']['date_analyse'] ?? date('Y-m-d H:i:s'),
        'nombre_images' => (int)$data['nombre_images']
    ];

    // Log des données reçues (sans les données sensibles)
    $logData = $data;
    error_log("Données validées et nettoyées: " . print_r($logData, true));

    // Connexion à la base de données
    try {
        $pdo = getConnection();
        error_log("Connexion à la base de données réussie");
    } catch (PDOException $e) {
        error_log("Erreur de connexion à la base de données: " . $e->getMessage());
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Début de la transaction
    $pdo->beginTransaction();
    error_log("Transaction démarrée");
    
    try {
        // Préparation et exécution de la requête
        $sql = "INSERT INTO analyses_ia (utilisateur_id, date_analyse, nombre_images, resultats)
                VALUES (?, NOW(), ?, ?)";
        $stmt = $pdo->prepare($sql);
        error_log("Requête préparée: " . $sql);
        
        // Encodage des résultats en JSON
        $resultatsJson = json_encode($data['resultats'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Erreur d'encodage JSON: " . json_last_error_msg());
            throw new Exception('Erreur lors de l\'encodage des résultats: ' . json_last_error_msg());
        }
        error_log("Résultats encodés en JSON avec succès");

        // Log des paramètres de la requête
        error_log("Paramètres de la requête: " . print_r([
            'utilisateur_id' => $userId,
            'nombre_images' => (int)$data['nombre_images'],
            'resultats_length' => strlen($resultatsJson)
        ], true));

        // Exécution de la requête
        $result = $stmt->execute([
            $userId,
            (int)$data['nombre_images'],
            $resultatsJson
        ]);

        if (!$result) {
            error_log("Erreur lors de l'exécution de la requête: " . print_r($stmt->errorInfo(), true));
            throw new Exception('Erreur lors de l\'insertion dans la base de données');
        }

        // Récupération de l'ID de l'analyse
        $analyseId = $pdo->lastInsertId();
        error_log("Analyse enregistrée avec l'ID: " . $analyseId);

        // Validation de la transaction
        $pdo->commit();
        error_log("Transaction validée");

        // Réponse de succès
        sendJsonResponse([
            'success' => true,
            'message' => 'Analyse enregistrée avec succès',
            'analyse_id' => $analyseId
        ]);

    } catch (Exception $e) {
        // Annulation de la transaction en cas d'erreur
        $pdo->rollBack();
        error_log("Transaction annulée: " . $e->getMessage());
        throw $e;
    }

} catch (Exception $e) {
    error_log("Erreur dans save_analysis.php : " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l\'enregistrement de l\'analyse: ' . $e->getMessage()
    ], 500);
} 