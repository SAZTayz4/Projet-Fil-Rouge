<?php
session_start();
require_once '../includes/notifications.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$notificationSystem = new NotificationSystem($db);
$success = $notificationSystem->markAllAsRead($_SESSION['user_id']);

echo json_encode(['success' => $success]);
?> 