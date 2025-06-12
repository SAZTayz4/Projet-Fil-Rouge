<?php

require_once __DIR__ . '/doctrine.php';

use Doctrine\ORM\Tools\SchemaTool;

// Création du schéma
$schemaTool = new SchemaTool($entityManager);
$metadata = $entityManager->getMetadataFactory()->getAllMetadata();

try {
    $schemaTool->updateSchema($metadata);
    echo "Base de données mise à jour avec succès!\n";
} catch (\Exception $e) {
    echo "Erreur lors de la mise à jour de la base de données: " . $e->getMessage() . "\n";
} 