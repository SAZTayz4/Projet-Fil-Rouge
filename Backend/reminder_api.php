<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        // Ajouter un rappel
        $dropId = $_POST['drop_id'] ?? null;
        if ($dropId) {
            try {
                $sql = "INSERT INTO drop_reminders (utilisateur_id, drop_id, created_at) VALUES (?, ?, NOW())";
                $stmt = $db->prepare($sql);
                $stmt->execute([$_SESSION['utilisateur_id'], $dropId]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                // Si c'est une erreur de doublon, on considère que c'est un succès
                if ($e->getCode() == 23000) {
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erreur lors de l\'ajout du rappel']);
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID de drop manquant']);
        }
        break;

    case 'remove':
        // Supprimer un rappel
        $dropId = $_POST['drop_id'] ?? null;
        if ($dropId) {
            $sql = "DELETE FROM drop_reminders WHERE utilisateur_id = ? AND drop_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$_SESSION['utilisateur_id'], $dropId]);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID de drop manquant']);
        }
        break;

    case 'list':
        // Lister les rappels
        $sql = "SELECT r.*, d.* FROM drop_reminders r 
                JOIN drops d ON r.drop_id = d.sku 
                WHERE r.utilisateur_id = ? 
                ORDER BY d.date_sortie ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['utilisateur_id']]);
        $reminders = $stmt->fetchAll();
        echo json_encode(['reminders' => $reminders]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action non valide']);
        break;
}
?> 