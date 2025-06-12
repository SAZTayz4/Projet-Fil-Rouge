<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: /ProjetFileRouge/Backend/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo_profil'])) {
    $file = $_FILES['photo_profil'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Vérification du type de fichier
    if (!in_array($file['type'], $allowedTypes)) {
        $_SESSION['error'] = "Le type de fichier n'est pas autorisé. Utilisez JPG, PNG ou GIF.";
        header('Location: /ProjetFileRouge/Frontend/HTML/compte.php#profile');
        exit;
    }

    // Vérification de la taille
    if ($file['size'] > $maxSize) {
        $_SESSION['error'] = "L'image est trop volumineuse. Taille maximale : 5MB.";
        header('Location: /ProjetFileRouge/Frontend/HTML/compte.php#profile');
        exit;
    }

    try {
        $pdo = getConnection();
        
        // Créer le dossier uploads/profiles s'il n'existe pas
        $uploadDir = __DIR__ . '/../../uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Générer un nom de fichier unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'profile_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $newFilename;

        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Supprimer l'ancienne photo si elle existe
            $stmt = $pdo->prepare("SELECT photo_profil FROM utilisateurs WHERE id = ?");
            $stmt->execute([$_SESSION['utilisateur_id']]);
            $oldPhoto = $stmt->fetchColumn();

            if ($oldPhoto && file_exists($uploadDir . $oldPhoto)) {
                unlink($uploadDir . $oldPhoto);
            }

            // Mettre à jour la base de données
            $stmt = $pdo->prepare("UPDATE utilisateurs SET photo_profil = ? WHERE id = ?");
            $stmt->execute([$newFilename, $_SESSION['utilisateur_id']]);

            $_SESSION['success'] = "Photo de profil mise à jour avec succès.";
        } else {
            throw new Exception("Erreur lors du téléchargement de l'image.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Une erreur est survenue : " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Aucune image n'a été envoyée.";
}

header('Location: /ProjetFileRouge/Frontend/HTML/compte.php#profile');
exit; 