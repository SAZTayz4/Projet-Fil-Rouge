<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = [__DIR__ . '/../../src/Entity'];
$isDevMode = true;

$dbParams = [
    'driver'   => 'pdo_mysql',
    'host'     => 'localhost',
    'user'     => 'root',
    'password' => 'root',
    'dbname'   => 'checkmykicks',
    'charset'  => 'utf8mb4'
];

try {
    // Création de la base de données si elle n'existe pas
    $pdo = new PDO(
        "mysql:host={$dbParams['host']};charset={$dbParams['charset']}",
        $dbParams['user'],
        $dbParams['password']
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbParams['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Configuration de Doctrine avec le nouveau système d'annotations
    $config = Setup::createAttributeMetadataConfiguration($paths, $isDevMode);
    $entityManager = EntityManager::create($dbParams, $config);
    
    // Création du schéma
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
    $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
    $schemaTool->updateSchema($metadata);
    
    echo "Base de données initialisée avec succès !\n";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
} 