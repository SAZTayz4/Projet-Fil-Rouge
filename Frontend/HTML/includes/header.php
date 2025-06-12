<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../translations.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ProjetFileRouge/config/database.php';

set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
});
?>
<header class="header">
    <nav class="nav-container">
        <div class="nav-left">
            <a href="/ProjetFileRouge/Frontend/HTML/ia.php" class="nav-link">IA-CMK</a>
            <a href="/ProjetFileRouge/Frontend/HTML/drops.php" class="nav-link"><?php echo t('Prochain Drop'); ?></a>
            <a href="/ProjetFileRouge/templates/blog.php" class="nav-link"><?php echo t('header_blog'); ?></a>
        </div>

        <div class="nav-center">
            <a href="/ProjetFileRouge/Frontend/HTML/home.php" class="logo">CheckMyKicks</a>
        </div>

        <div class="nav-right">
            <div class="nav-icons">
                <?php if (isset($_SESSION['utilisateur_id'])): ?>
                    <a href="/ProjetFileRouge/Frontend/HTML/compte.php" class="icon-link">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="/ProjetFileRouge/Backend/auth/logout.php" class="nav-link">Déconnexion</a>
                    <a href="/ProjetFileRouge/Frontend/HTML/langues.html" class="nav-link">Langues</a>
                    <a href="/ProjetFileRouge/Frontend/HTML/spotcheck.php" class="nav-link">SpotCheck</a>
                <?php else: ?>
                    <a href="/ProjetFileRouge/Backend/auth/login.php" class="nav-link">Connexion</a>
                    <a href="/ProjetFileRouge/Backend/auth/register.php" class="nav-link">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<div class="announcement-bar">
    <p>Authentifiez vos sneakers dès maintenant - Service premium disponible 24/7</p>
</div>
