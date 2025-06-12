<?php
session_start();
require_once '../includes/notifications.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$notificationId = $data['id'] ?? null;

if (!$notificationId) {
    echo json_encode(['success' => false, 'message' => 'ID de notification manquant']);
    exit;
}

$notificationSystem = new NotificationSystem($db);
$success = $notificationSystem->markAsRead($notificationId);

echo json_encode(['success' => $success]);
?> 