<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: /ProjetFileRouge/Backend/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation des données
    $errors = [];

    if (empty($currentPassword)) {
        $errors[] = "Le mot de passe actuel est requis.";
    }

    if (empty($newPassword)) {
        $errors[] = "Le nouveau mot de passe est requis.";
    } elseif (strlen($newPassword) < 8) {
        $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        try {
            $pdo = getConnection();
            
            // Vérifier le mot de passe actuel
            $stmt = $pdo->prepare("SELECT motDePasse FROM utilisateurs WHERE id = ?");
            $stmt->execute([$_SESSION['utilisateur_id']]);
            $currentHash = $stmt->fetchColumn();

            if (!password_verify($currentPassword, $currentHash)) {
                $_SESSION['error'] = "Le mot de passe actuel est incorrect.";
            } else {
                // Hasher et mettre à jour le nouveau mot de passe
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utilisateurs SET motDePasse = ? WHERE id = ?");
                $stmt->execute([$newHash, $_SESSION['utilisateur_id']]);
                
                $_SESSION['success'] = "Mot de passe mis à jour avec succès.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour du mot de passe.";
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