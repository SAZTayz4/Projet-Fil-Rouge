<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

require_once __DIR__ . '/../vendor/autoload.php';

$paths = [__DIR__ . '/../src/Entity'];
$isDevMode = true;

// Configuration de la base de données
$dbParams = [
    'driver'   => 'pdo_mysql',
    'host'     => '127.0.0.1:3308',
    'user'     => 'root',
    'password' => 'root',
    'dbname'   => 'checkmykicks',
    'charset'  => 'utf8mb4'
];

// Configuration pour les attributs
$config = ORMSetup::createAttributeMetadataConfiguration(
    $paths,
    $isDevMode
);

// Création de l'EntityManager
try {
    $connection = DriverManager::getConnection($dbParams, $config);
    $entityManager = new EntityManager($connection, $config);
    
    // Test de la connexion
    if ($connection->isConnected()) {
        echo "Connexion à la base de données réussie!\n";
    }
} catch (\Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Rendre l'entityManager disponible globalement
$GLOBALS['entityManager'] = $entityManager;
