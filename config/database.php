<?php
// Activation du rapport d'erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1:3308');
if (!defined('DB_NAME')) define('DB_NAME', 'checkmykicks');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'root'); // Mets ton mot de passe MySQL ici

function getConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $host = '127.0.0.1:3308';
            $dbname = 'checkmykicks';
            $username = 'root';
            $password = 'root';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $pdo = new PDO($dsn, $username, $password, $options);
            
            // Test de la connexion
            $pdo->query('SELECT 1');
            
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données : " . $e->getMessage());
            throw new Exception("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
        }
    }
    
    return $pdo;
}

// Création de la table abonnements si elle n'existe pas
try {
    $pdo = getConnection();
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS abonnements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            type_abonnement VARCHAR(50) NOT NULL,
            prix DECIMAL(10,2) NOT NULL,
            date_debut DATETIME NOT NULL,
            date_fin DATETIME NOT NULL,
            statut ENUM('actif', 'inactif', 'annule') NOT NULL DEFAULT 'actif',
            numero_facture VARCHAR(50) NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (Exception $e) {
    error_log("Erreur lors de la création de la table abonnements : " . $e->getMessage());
}

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
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die('Erreur de connexion à la base de données. Veuillez vérifier :
        1. Que le serveur MySQL est en cours d\'exécution
        2. Que les identifiants sont corrects
        3. Que la base de données "checkmykicks" existe
        Message d\'erreur : ' . $e->getMessage());
}
?> 