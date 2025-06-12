<?php
// Activation du rapport d'erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', '127.0.0.1:3308');
define('DB_NAME', 'checkmykicks');
define('DB_USER', 'root');
define('DB_PASS', 'root');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    // Journalisation de l'erreur
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    
    // Message d'erreur plus détaillé
    die('Erreur de connexion à la base de données. Veuillez vérifier :
        1. Que le serveur MySQL est en cours d\'exécution
        2. Que les identifiants sont corrects
        3. Que la base de données "checkmykicks" existe
        Message d\'erreur : ' . $e->getMessage());
}

// Fonction pour obtenir une connexion à la base de données
function getConnection() {
    global $pdo;
    if (!$pdo) {
        throw new PDOException("Aucune connexion à la base de données n'est disponible");
    }
    return $pdo;
}
?> 