<?php
session_start();
require_once __DIR__ . '/../config/database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Debug session (uniquement en log, pas d'affichage)
error_log("Session debug: " . print_r($_SESSION, true));

// Vérification de l'authentification avec les deux variables de session possibles
if (!isset($_SESSION['utilisateur_id']) && !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié (aucun ID utilisateur dans la session)']);
    exit;
}

try {
    // Utiliser l'ID utilisateur correct
    $userId = $_SESSION['utilisateur_id'] ?? $_SESSION['user_id'];

    // Log pour debug
    error_log("Vérification des limites pour l'utilisateur ID: " . $userId);

    // Vérifie l'abonnement actif dans la table abonnements avec plus de détails
    $sql = "SELECT a.limitesVerifications, a.type, ab.date_debut, ab.date_fin, ab.type_abonnement
            FROM abonnements ab
            JOIN abonnement a ON a.type = ab.type_abonnement
            WHERE ab.utilisateur_id = ? AND ab.statut = 'actif'
            ORDER BY ab.date_debut DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $abo = $stmt->fetch();

    // Log pour debug
    error_log("Résultat de la requête abonnement: " . print_r($abo, true));

    if (!$abo) {
        error_log("Aucun abonnement actif trouvé pour l'utilisateur ID: " . $userId);
        echo json_encode(['success' => false, 'message' => 'Aucun abonnement actif']);
        exit;
    }

    // Vérification spécifique pour l'abonnement avancé
    if ($abo['type'] === 'avancé' && $abo['limitesVerifications'] !== 50) {
        error_log("Erreur de configuration : L'abonnement avancé devrait avoir 50 vérifications");
        // Forcer la limite à 50 pour l'abonnement avancé
        $abo['limitesVerifications'] = 50;
    }

    $_SESSION['limitesVerifications'] = $abo['limitesVerifications'];
    $_SESSION['type_abonnement'] = $abo['type'];

    // Compte les analyses faites pendant la période d'abonnement
    $sql = "SELECT COUNT(*) FROM analyses_ia WHERE utilisateur_id = ? AND date_analyse BETWEEN ? AND ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $abo['date_debut'], $abo['date_fin']]);
    $nbAnalyses = $stmt->fetchColumn();

    // Log pour debug
    error_log("Nombre d'analyses effectuées: " . $nbAnalyses . " sur " . $abo['limitesVerifications'] . " autorisées");

    if ($nbAnalyses >= $abo['limitesVerifications']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Limite de vérifications atteinte pour votre abonnement !',
            'details' => [
                'type_abonnement' => $abo['type'],
                'limite' => $abo['limitesVerifications'],
                'utilisees' => $nbAnalyses
            ]
        ]);
        exit;
    }

    echo json_encode([
        'success' => true, 
        'remaining' => $abo['limitesVerifications'] - $nbAnalyses,
        'details' => [
            'type_abonnement' => $abo['type'],
            'limite' => $abo['limitesVerifications'],
            'utilisees' => $nbAnalyses
        ]
    ]);
} catch (Exception $e) {
    error_log("Erreur dans check_limit.php : " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la vérification de l\'abonnement',
        'error' => $e->getMessage()
    ]);
} 