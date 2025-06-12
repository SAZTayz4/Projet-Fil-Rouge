<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: /ProjetFileRouge/Backend/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = trim($_POST['adresse'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    // Validation des données
    $errors = [];

    if (empty($adresse)) {
        $errors[] = "L'adresse est requise.";
    }

    if (!empty($telephone)) {
        // Nettoyer le numéro de téléphone (enlever les espaces, tirets, etc.)
        $telephone = preg_replace('/[^0-9+]/', '', $telephone);
        
        // Validation basique du format du numéro de téléphone
        if (!preg_match('/^(\+33|0)[1-9](\d{2}){4}$/', $telephone)) {
            $errors[] = "Le numéro de téléphone n'est pas valide.";
        }
    }

    if (empty($errors)) {
        try {
            $pdo = getConnection();
            
            // Vérifier si un compte existe déjà pour cet utilisateur
            $stmt = $pdo->prepare("SELECT id FROM compte WHERE utilisateur_id = ?");
            $stmt->execute([$_SESSION['utilisateur_id']]);
            $compteExists = $stmt->fetch();

            if ($compteExists) {
                // Mettre à jour le compte existant
                $stmt = $pdo->prepare("UPDATE compte SET adresse = ?, telephone = ? WHERE utilisateur_id = ?");
                $stmt->execute([$adresse, $telephone, $_SESSION['utilisateur_id']]);
            } else {
                // Créer un nouveau compte
                $stmt = $pdo->prepare("INSERT INTO compte (utilisateur_id, adresse, telephone, methodePaiement) VALUES (?, ?, ?, 'carte')");
                $stmt->execute([$_SESSION['utilisateur_id'], $adresse, $telephone]);
            }
            
            $_SESSION['success'] = "Informations de contact mises à jour avec succès.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour des informations de contact.";
            error_log($e->getMessage());
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
} else {
    $_SESSION['error'] = "Méthode de requête invalide.";
}

header('Location: /ProjetFileRouge/Frontend/HTML/compte.php#profile');
exit; 