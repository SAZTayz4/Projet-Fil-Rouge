<?php

require_once __DIR__ . '/doctrine.php';

try {
    // La connexion est déjà testée dans doctrine.php
    // Vérifions maintenant si nous pouvons accéder à la table utilisateurs
    $query = $entityManager->createQuery('SELECT COUNT(u) FROM App\Entity\User u');
    $count = $query->getSingleScalarResult();
    echo "Nombre d'utilisateurs dans la base : " . $count . "\n";
} catch (\Exception $e) {
    echo "Erreur lors de la requête : " . $e->getMessage() . "\n";
} 