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
    <title>Check MyKicks - <?php echo t('register'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/auth.css">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <div class="nav-left">
                <a href="#" class="nav-link"><?php echo t('Nos Tarifs'); ?></a>
                <a href="/drops" class="nav-link"><?php echo t('Prochain Drop'); ?></a>
                <a href="#" class="nav-link"><?php echo t('header_blog'); ?></a>
            </div>
            
            <div class="nav-center">
                <a href="/" class="logo">CheckMyKicks</a>
            </div>
            
            <div class="nav-right">
                <div class="nav-icons">
                    <a href="/spotcheck" class="nav-link"><?php echo t('SpotCheck'); ?></a>
                    <a href="/ProjetFileRouge/Frontend/HTML/comptes.php" class="icon-link">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <div class="auth-container">
        <div class="auth-header">
            <h1><?php echo t('register'); ?></h1>
            <p><?php echo t('create_account'); ?></p>
        </div>

        <form class="auth-form" action="../../Backend/auth/register_process.php" method="POST">
            <div class="form-group">
                <label for="name"><?php echo t('name'); ?></label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="email"><?php echo t('email'); ?></label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password"><?php echo t('password'); ?></label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password"><?php echo t('confirm_password'); ?></label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="auth-btn"><?php echo t('register'); ?></button>
        </form>

        <div class="auth-links">
            <p><?php echo t('already_account'); ?> <a href="login.php"><?php echo t('login'); ?></a></p>
        </div>
    </div>
</body>
</html> 