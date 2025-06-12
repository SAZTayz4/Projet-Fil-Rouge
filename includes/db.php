<?php
function getDB() {
    static $db = null;
    
    if ($db === null) {
        try {
            $host = 'localhost';
            $dbname = 'checkmykicks';
            $username = 'root';
            $password = 'root';
            
            $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    
    return $db;
}
?> 