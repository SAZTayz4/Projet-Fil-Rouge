<?php
session_start();
require_once '../../translations.php';
require_once '../../config/doctrine.php';

use App\Entity\Abonnement;

$entityManager = $GLOBALS['entityManager'];
$qb = $entityManager->createQueryBuilder();
$abonnements = $qb->select('a')
                  ->from(Abonnement::class, 'a')
                  ->getQuery()
                  ->getResult();
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check MyKicks - Abonnements</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <div class="nav-left">
                <a href="#pricing" class="nav-link"><?php echo t('Nos Tarifs'); ?></a>
                <a href="/ProjetFileRouge/Frontend/HTML/drops.php" class="nav-link"><?php echo t('Prochain Drop'); ?></a>
                <a href="/ProjetFileRouge/templates/blog.php" class="nav-link"><?php echo t('header_blog'); ?></a>
            </div>
            <div class="nav-center">
                <a href="/ProjetFileRouge/Frontend/HTML/home.php" class="logo">CheckMyKicks</a>
            </div>
            <div class="nav-right">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/ProjetFileRouge/Frontend/HTML/comptes.php" class="nav-link">Mon Compte</a>
                    <a href="/ProjetFileRouge/Backend/auth/logout.php" class="nav-link">Déconnexion</a>
                <?php else: ?>
                    <a href="/ProjetFileRouge/Backend/auth/login.php" class="nav-link">Connexion</a>
                    <a href="/ProjetFileRouge/Backend/auth/register.php" class="nav-link">Inscription</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="container py-5">
        <h1 class="mb-4">Nos abonnements</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert <?php echo $_SESSION['message_type']; ?>">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <div class="pricing-grid">
            <?php foreach ($abonnements as $abonnement): ?>
                <div class="pricing-card">
                    <h3><?php echo htmlspecialchars($abonnement->getType()); ?></h3>
                    <div class="price"><?php echo number_format($abonnement->getPrix(), 2, ',', ' '); ?>€ <span><?php echo t('per_month'); ?></span></div>
                    <ul class="pricing-features">
                        <?php foreach ($abonnement->getCaracteristiques() as $carac): ?>
                            <li><?php echo htmlspecialchars($carac); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="/ProjetFileRouge/Backend/paiement/traiter.php" method="post">
                            <input type="hidden" name="abonnement_id" value="<?php echo $abonnement->getId(); ?>">
                            <button type="submit" class="pricing-button">Simuler l'abonnement</button>
                        </form>
                    <?php else: ?>
                        <a href="/ProjetFileRouge/Backend/auth/login.php" class="pricing-button">Connectez-vous pour souscrire</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3><?php echo t('footer_concept'); ?></h3>
                <p><?php echo t('footer_concept_text'); ?></p>
            </div>
            <div class="footer-section">
                <h3><?php echo t('footer_contact'); ?></h3>
                <p><?php echo t('footer_hours'); ?></p>
                <p><?php echo t('footer_time'); ?></p>
                <p>contact@checkmykicks.com</p>
            </div>
        </div>
    </footer>
</body>
</html> 