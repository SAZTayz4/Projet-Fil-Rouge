<?php
session_start();
header('Content-Type: application/json');

if (isset($_POST['lang'])) {
    $lang = $_POST['lang'];
    $_SESSION['user_language'] = $lang;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Aucune langue spécifiée']);
}
?> 