<?php
// Activation des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Définition des constantes
define('BASE_URL', '/ProjetFileRouge');
define('DEFAULT_IMAGE', BASE_URL . '/Frontend/src/images/default-shoe.jpg');

// Headers pour JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');

// Fonction pour envoyer une réponse JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Fonction pour vérifier et corriger les chemins d'images
function validateImagePath($path) {
    if (empty($path)) {
        return DEFAULT_IMAGE;
    }
    return $path;
}

// Gestionnaire d'erreurs personnalisé
function handleError($errno, $errstr, $errfile, $errline) {
    error_log("Erreur PHP [$errno] $errstr dans $errfile à la ligne $errline");
    sendJsonResponse([
        'error' => true,
        'reply' => 'Désolé, une erreur est survenue. Veuillez réessayer.',
        'debug' => [
            'error' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]
    ], 500);
}

// Gestionnaire d'exceptions
function handleException($e) {
    error_log("Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    sendJsonResponse([
        'error' => true,
        'reply' => 'Désolé, une erreur est survenue. Veuillez réessayer.',
        'debug' => [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ], 500);
}

// Enregistrement des gestionnaires
set_error_handler('handleError');
set_exception_handler('handleException');

require_once __DIR__ . '/../../Backend/config/database.php';

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse([
        'error' => true,
        'reply' => 'Méthode non autorisée'
    ], 405);
}

// Récupération du message
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendJsonResponse([
        'error' => true,
        'reply' => 'Désolé, votre message n\'a pas pu être traité.',
        'debug' => ['json_error' => json_last_error_msg()]
    ], 400);
}

$message = trim($data['message'] ?? '');

if (empty($message)) {
    sendJsonResponse([
        'error' => true,
        'reply' => 'Veuillez entrer un message.'
    ], 400);
}

// Configuration de l'API
$config = [
    'api_url' => 'http://192.168.1.114:5055/chatbot', // API locale
    'api_timeout' => 30,
    'debug_mode' => true,
    'max_message_length' => 500
];

// Validation de la longueur du message
if (strlen($message) > $config['max_message_length']) {
    sendJsonResponse([
        'error' => true,
        'reply' => 'Le message est trop long. Maximum ' . $config['max_message_length'] . ' caractères.'
    ], 400);
}

// Nettoyage du message
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

try {
    // URL de l'API
    $apiUrl = $config['api_url'];
    
    // Configuration de cURL
    $ch = curl_init($apiUrl);
    if ($ch === false) {
        throw new Exception('Impossible d\'initialiser cURL');
    }

    // Log pour le débogage
    if ($config['debug_mode']) {
        error_log("Tentative de connexion à l'API: " . $apiUrl);
        error_log("Message envoyé: " . $message);
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['message' => $message], JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => $config['api_timeout'],
        CURLOPT_VERBOSE => $config['debug_mode']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($config['debug_mode']) {
        error_log("Code HTTP reçu: " . $httpCode);
        error_log("Réponse brute: " . $response);
    }
    
    if (curl_errno($ch)) {
        $curlError = curl_error($ch);
        error_log("Erreur cURL: " . $curlError);
        throw new Exception('Erreur de connexion à l\'API: ' . $curlError);
    }
    
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Erreur API (HTTP $httpCode): " . substr($response, 0, 1000));
    }

    // Vérification que la réponse est du JSON valide
    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Réponse invalide de l\'API: ' . $response);
    }

    // Formatage de la réponse
    $formattedResponse = [
        'error' => false,
        'reply' => $responseData['reply'] ?? 'Désolé, je n\'ai pas compris votre demande.',
        'matches' => []
    ];

    // Si des produits sont fournis, les formater
    if (!empty($responseData['matches']) && is_array($responseData['matches'])) {
        foreach ($responseData['matches'] as $product) {
            $formattedResponse['matches'][] = [
                'name' => $product['name'] ?? 'Nom inconnu',
                'price' => $product['price'] ?? 'Prix inconnu',
                'size' => $product['size'] ?? 'Taille inconnue',
                'article_image' => $product['article_image'] ?? DEFAULT_IMAGE,
                'link' => $product['link'] ?? '#'
            ];
        }
    }

    sendJsonResponse($formattedResponse);

} catch (Exception $e) {
    error_log("Erreur chatbot: " . $e->getMessage());
    sendJsonResponse([
        'error' => true,
        'reply' => 'Désolé, je rencontre des difficultés techniques. Veuillez réessayer dans quelques instants.',
        'debug' => [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ], 500);
} 