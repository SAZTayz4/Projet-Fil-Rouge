<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/notifications.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$db = getDB();
$notificationSystem = new NotificationSystem($db);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        $notifications = $notificationSystem->getUnreadNotifications($_SESSION['utilisateur_id']);
        echo json_encode(['notifications' => $notifications]);
        break;

    case 'mark_read':
        // Marquer une notification comme lue
        $notificationId = $_POST['notification_id'] ?? null;
        if ($notificationId) {
            $notificationSystem->markAsRead($notificationId);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID de notification manquant']);
        }
        break;

    case 'mark_all_read':
        // Marquer toutes les notifications comme lues
        $notificationSystem->markAllAsRead($_SESSION['utilisateur_id']);
        echo json_encode(['success' => true]);
        break;

    case 'add':
        // Ajouter une notification (utilisé par d'autres parties de l'application)
        $type = $_POST['type'] ?? null;
        $message = $_POST['message'] ?? null;
        $link = $_POST['link'] ?? null;

        if ($type && $message) {
            $notificationSystem->addNotification($_SESSION['utilisateur_id'], $type, $message, $link);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Type et message requis']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action non valide']);
        break;
}
?> 