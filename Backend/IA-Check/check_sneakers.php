<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
set_exception_handler(function($e) {
    error_log("Erreur dans check_sneakers.php : " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
});

session_start();
require_once __DIR__ . '/../config/database.php';

// Headers pour JSON et CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Fonction pour envoyer une réponse JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Vérification de l'authentification
if (!isset($_SESSION['utilisateur_id']) && !isset($_SESSION['user_id'])) {
    sendJsonResponse(['error' => true, 'message' => 'Non autorisé'], 401);
}

$userId = $_SESSION['utilisateur_id'] ?? $_SESSION['user_id'];
error_log("Vérification des sneakers pour l'utilisateur ID: " . $userId);

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    sendJsonResponse(['error' => false, 'message' => 'API OK (GET)']);
}

// Vérification des fichiers
if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
    sendJsonResponse(['error' => true, 'message' => 'Aucune image fournie'], 400);
}

// Configuration
$allowedTypes = ['image/jpeg', 'image/png'];
$maxFileSize = 5 * 1024 * 1024; // 5MB
$maxFiles = 6;
$uploadDir = __DIR__ . '/temp/';
$results = [];

// Création du dossier temporaire si nécessaire
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Traitement des fichiers
$files = $_FILES['images'];
$fileCount = count($files['name']);

if ($fileCount > $maxFiles) {
    sendJsonResponse(['error' => true, 'message' => "Maximum $maxFiles images autorisées"], 400);
}

try {
    $pdo = getConnection();
    
    // Traitement de chaque fichier
    for ($i = 0; $i < $fileCount; $i++) {
        $file = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];

        // Vérifications de sécurité
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("Erreur lors de l'upload du fichier " . $file['name'] . ": " . $file['error']);
            continue;
        }

        if (!in_array($file['type'], $allowedTypes)) {
            error_log("Type de fichier non autorisé: " . $file['type']);
            continue;
        }

        if ($file['size'] > $maxFileSize) {
            error_log("Fichier trop volumineux: " . $file['name']);
            continue;
        }

        // Génération d'un nom de fichier unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Déplacement du fichier
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Simulation d'analyse IA (à remplacer par votre modèle IA)
            $score = simulateIAnalysis($filepath);
            
            // Génération d'une heatmap (simulée)
            $heatmap = generateHeatmap($filepath);
            
            // Ajout du résultat
            $results[] = [
                'original_image' => '/ProjetFileRouge/Backend/IA-Check/temp/' . $filename,
                'score' => $score,
                'heatmap' => $heatmap,
                'explanation' => generateExplanation($score)
            ];
        } else {
            error_log("Erreur lors du déplacement du fichier: " . $file['name']);
        }
    }

    if (empty($results)) {
        throw new Exception('Aucune image n\'a pu être traitée');
    }

    // Sauvegarde dans la base de données via save_analysis.php
    $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/ProjetFileRouge/Backend/IA-Check/save_analysis.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'nombre_images' => count($results),
        'resultats' => ['predictions' => $results]
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Cookie: ' . $_SERVER['HTTP_COOKIE']
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Erreur lors de la sauvegarde de l'analyse: " . $response);
        throw new Exception('Erreur lors de la sauvegarde de l\'analyse');
    }

    $saveResponse = json_decode($response, true);
    if (!$saveResponse['success']) {
        throw new Exception($saveResponse['message'] ?? 'Erreur lors de la sauvegarde');
    }

    // Réponse
    sendJsonResponse([
        'error' => false,
        'result' => [
            'predictions' => $results,
            'analyse_id' => $saveResponse['analyse_id']
        ]
    ]);

} catch (Exception $e) {
    error_log("Erreur dans check_sneakers.php : " . $e->getMessage());
    sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
} finally {
    // Nettoyage des fichiers temporaires après 1 heure
    cleanupTempFiles($uploadDir);
}

/**
 * Simule une analyse IA (à remplacer par votre modèle)
 */
function simulateIAnalysis($imagePath) {
    // Simulation d'un score entre 0 et 1
    return mt_rand(0, 100) / 100;
}

/**
 * Génère une heatmap simulée
 */
function generateHeatmap($imagePath) {
    // Simulation d'une heatmap en base64
    $image = imagecreatefromstring(file_get_contents($imagePath));
    if (!$image) return null;

    $width = imagesx($image);
    $height = imagesy($image);
    $heatmap = imagecreatetruecolor($width, $height);

    // Création d'une heatmap aléatoire
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $intensity = mt_rand(0, 255);
            $color = imagecolorallocate($heatmap, $intensity, 0, 0);
            imagesetpixel($heatmap, $x, $y, $color);
        }
    }

    // Conversion en base64
    ob_start();
    imagepng($heatmap);
    $heatmapData = ob_get_clean();
    imagedestroy($heatmap);
    imagedestroy($image);

    return 'data:image/png;base64,' . base64_encode($heatmapData);
}

/**
 * Génère une explication basée sur le score
 */
function generateExplanation($score) {
    if ($score >= 0.8) {
        return "Cette paire semble authentique avec une forte probabilité.";
    } elseif ($score <= 0.2) {
        return "Cette paire présente des signes de contrefaçon.";
    } else {
        return "Résultat incertain, des vérifications supplémentaires sont recommandées.";
    }
}

/**
 * Nettoie les fichiers temporaires plus vieux que 1 heure
 */
function cleanupTempFiles($dir) {
    $files = glob($dir . '*');
    $now = time();

    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= 3600) { // 1 heure
                unlink($file);
            }
        }
    }
} 