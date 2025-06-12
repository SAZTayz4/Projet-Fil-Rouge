<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Accès non autorisé');
}

// Récupérer le numéro de facture depuis l'URL
$facture = basename($_SERVER['REQUEST_URI']);
if (!preg_match('/^FACT-\d{8}-\d+\.pdf$/', $facture)) {
    header('HTTP/1.0 400 Bad Request');
    die('Format de facture invalide');
}

try {
    $pdo = getConnection();
    
    // Vérifier si l'utilisateur a accès à cette facture
    $stmt = $pdo->prepare("
        SELECT f.* FROM facture f 
        WHERE f.numeroFacture = ? 
        AND f.utilisateur_id = ?
        LIMIT 1
    ");
    $stmt->execute([str_replace('.pdf', '', $facture), $_SESSION['utilisateur_id']]);
    
    if (!$stmt->fetch()) {
        header('HTTP/1.0 403 Forbidden');
        die('Accès non autorisé à cette facture');
    }
    
    // Si tout est OK, servir le fichier
    $file = __DIR__ . '/' . $facture;
    if (file_exists($file)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $facture . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
    
    header('HTTP/1.0 404 Not Found');
    die('Facture non trouvée');
    
} catch (Exception $e) {
    error_log($e->getMessage());
    header('HTTP/1.0 500 Internal Server Error');
    die('Une erreur est survenue');
} 