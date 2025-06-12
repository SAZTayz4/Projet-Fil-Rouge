<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: /ProjetFileRouge/Backend/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validation des données
    $errors = [];

    if (empty($nom)) {
        $errors[] = "Le nom est requis.";
    }

    if (empty($email)) {
        $errors[] = "L'email est requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if (empty($errors)) {
        try {
            $pdo = getConnection();
            
            // Vérifier si l'email existe déjà pour un autre utilisateur
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['utilisateur_id']]);
            
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Cette adresse email est déjà utilisée par un autre compte.";
            } else {
                // Mettre à jour les informations
                $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, email = ? WHERE id = ?");
                $stmt->execute([$nom, $email, $_SESSION['utilisateur_id']]);
                
                $_SESSION['success'] = "Profil mis à jour avec succès.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour du profil.";
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