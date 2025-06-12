<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = '127.0.0.1';
$username = 'root';
$password = 'root';
$dbname = 'checkmykicks';
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données !\n";
    header("Location: home.php");
    exit();
} catch (PDOException $e) {
    die("La connexion a échoué : " . $e->getMessage());
}

?>
