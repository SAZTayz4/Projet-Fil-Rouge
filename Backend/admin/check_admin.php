<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Vérifier si l'utilisateur a le rôle admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../Frontend/HTML/home.php');
    exit();
}

// Inclure la configuration de la base de données
require_once __DIR__ . '/../config/database.php';
?> 