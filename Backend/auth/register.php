<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($nom) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = 'Tous les champs sont requis.';
    } elseif ($password !== $confirmPassword) {
        $_SESSION['error'] = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Cet email est déjà utilisé.';
            } else {
                // Créer le nouvel utilisateur
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, motDePasse, role, abonnement_id) VALUES (?, ?, ?, 'client', 1)");
                $stmt->execute([$nom, $email, $hashedPassword]);

                $_SESSION['success'] = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Une erreur est survenue lors de l\'inscription : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - CheckMyKicks</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../Frontend/CSS/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Inscription</h2>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="auth-button">S'inscrire</button>
            </form>
            <p class="auth-link">Déjà un compte ? <a href="login.php">Connectez-vous</a></p>
        </div>
    </div>
</body>
</html> 