<?php
// Activation du rapport d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../translations.php';
require_once '../../Backend/config/database.php';

// Configuration de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => false, // À mettre à true en production avec HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

// Rediriger si déjà connecté
if (isset($_SESSION['utilisateur_id'])) {
    header('Location: /ProjetFileRouge/Frontend/HTML/comptes.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check MyKicks - <?php echo t('login'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Connexion</h2>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <form method="POST" action="../../Backend/auth/login_process.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="auth-button">Se connecter</button>
            </form>
            <p class="auth-link">Pas encore de compte ? <a href="register.php">Inscrivez-vous</a></p>
        </div>
    </div>
</body>
</html> 