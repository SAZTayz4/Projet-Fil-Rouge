<?php
session_start();
header('Content-Type: application/json');

if (isset($_POST['lang'])) {
    $lang = $_POST['lang'];
    
    $allowed_languages = ['fr', 'en', 'de', 'es', 'it', 'jp', 'kr'];
    
    if (in_array($lang, $allowed_languages)) {
        $_SESSION['user_language'] = $lang;
        
        setcookie('user_language', $lang, time() + (86400 * 30), '/');
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Langue non supportée']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Aucune langue spécifiée']);
}
?> 