<?php
// Configuration de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => false, // À mettre à true en production avec HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}
require_once '../../translations.php';

// Correction du chemin d'inclusion du header
include '../../Frontend/HTML/includes/header.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Entretien Expert : Protéger Votre Investissement - Check MyKicks</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../Frontend/CSS/style.css">
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #333333;
            --accent-color: #666666;
            --background-color: #FFFFFF;
            --text-color: #1A1A1A;
            --light-gray: #F5F5F5;
            --border-color: #E0E0E0;
        }
        .article-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .article-header {
            margin-bottom: 40px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 30px;
        }
        .article-category {
            font-size: 0.9rem;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .article-title {
            font-size: 2.8rem;
            line-height: 1.2;
            margin-bottom: 20px;
            color: var(--primary-color);
            font-weight: 700;
            letter-spacing: -1px;
        }
        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 30px;
        }
        .article-author {
            font-weight: 500;
        }
        .article-date {
            font-style: italic;
        }
        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-color);
        }
        .article-content p {
            margin-bottom: 25px;
        }
        .article-content h2 {
            font-size: 1.8rem;
            margin: 40px 0 20px;
            color: var(--primary-color);
            font-weight: 600;
        }
        .article-content h3 {
            font-size: 1.4rem;
            margin: 30px 0 15px;
            color: var(--primary-color);
            font-weight: 500;
        }
        .article-content ul, .article-content ol {
            margin: 20px 0;
            padding-left: 20px;
        }
        .article-content li {
            margin-bottom: 10px;
        }
        .article-content blockquote {
            border-left: 4px solid var(--primary-color);
            padding-left: 20px;
            margin: 30px 0;
            font-style: italic;
            color: var(--secondary-color);
        }
        .article-tags {
            display: flex;
            gap: 10px;
            margin: 40px 0;
            flex-wrap: wrap;
        }
        .article-tag {
            background: var(--light-gray);
            padding: 8px 16px;
            font-size: 0.85rem;
            color: var(--primary-color);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        .article-tag:hover {
            background: var(--primary-color);
            color: var(--background-color);
            border-color: var(--primary-color);
        }
        .article-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding-top: 30px;
            border-top: 2px solid var(--border-color);
        }
        .nav-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            color: var(--secondary-color);
        }
        .nav-link.prev::before {
            content: '←';
        }
        .nav-link.next::after {
            content: '→';
        }
        @media (max-width: 768px) {
            .article-title {
                font-size: 2rem;
            }
            .article-content {
                font-size: 1rem;
            }
            .article-meta {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="announcement-bar">
        <p>Authentifiez vos sneakers dès maintenant - Service premium disponible 24/7</p>
    </div>

    <div class="article-container">
        <article>
            <header class="article-header">
                <div class="article-category"><?php echo t('guides'); ?></div>
                <h1 class="article-title">L'Entretien Expert : Protéger Votre Investissement</h1>
                <div class="article-meta">
                    <span class="article-author">Par Sophie Martin - Experte en Entretien</span>
                    <span class="article-date">15 Mars 2024</span>
                </div>
            </header>

            <img src="../../IMG/blog4.jpg" alt="Entretien des Sneakers" class="article-hero-image">

            <div class="article-content">
                <p>L'entretien des sneakers est un art qui nécessite attention et précision. Nous avons rencontré Marc Dubois, expert en restauration et entretien de sneakers, pour vous dévoiler les secrets d'une maintenance optimale.</p>

                <blockquote>"Une paire de sneakers bien entretenue peut conserver sa valeur et son esthétique pendant des années. C'est un investissement qui mérite le plus grand soin."<br><span class="quote-author">- Marc Dubois, Expert en Entretien</span></blockquote>

                <h2>Les Bases de l'Entretien</h2>
                <p>L'entretien régulier commence par le nettoyage. Utilisez des produits spécifiques pour chaque type de matériau :</p>
                <ul>
                    <li>Cuir : Nettoyant doux et crème hydratante</li>
                    <li>Toile : Solution nettoyante douce et brosse souple</li>
                    <li>Mesh : Nettoyant spécial et protection imperméable</li>
                </ul>

                <div class="grid-container">
                    <div class="grid-item">
                        <img src="../../IMG/blog4-1.jpg" alt="Nettoyage des Sneakers" class="article-hero-image">
                        <p>Techniques de nettoyage professionnel</p>
                    </div>
                    <div class="grid-item">
                        <img src="../../IMG/blog4-2.jpg" alt="Produits d'entretien" class="article-hero-image">
                        <p>Les produits essentiels pour l'entretien</p>
                    </div>
                </div>

                <h2>Protection et Stockage</h2>
                <p>Le stockage est crucial pour la préservation de vos sneakers :</p>
                <ul>
                    <li>Conservez vos paires dans un endroit frais et sec</li>
                    <li>Utilisez des embauchoirs en cèdre pour maintenir la forme</li>
                    <li>Évitez l'exposition directe au soleil</li>
                    <li>Rangez vos paires dans des boîtes de stockage adaptées</li>
                </ul>

                <img src="../../IMG/blog4-3.jpg" alt="Système de stockage" class="article-hero-image">
                <blockquote>Système de stockage professionnel pour collectionneurs</blockquote>

                <blockquote>"Investissez dans de bons produits d'entretien. C'est moins coûteux que de devoir restaurer ou remplacer une paire endommagée."<br><span class="quote-author">- Conseil d'expert</span></blockquote>

                <h2>Restauration et Réparation</h2>
                <p>Pour les paires plus anciennes ou endommagées, la restauration peut être nécessaire. Voici les points clés :</p>
                <ul>
                    <li>Nettoyage en profondeur</li>
                    <li>Réparation des coutures</li>
                    <li>Remplacement des semelles si nécessaire</li>
                    <li>Retouche de la couleur</li>
                </ul>

                <div class="article-tags">
                    <span class="article-tag">Entretien</span>
                    <span class="article-tag">Guide</span>
                    <span class="article-tag">Expertise</span>
                    <span class="article-tag">Conseils</span>
                </div>
            </div>
            <nav class="article-navigation">
                <a href="collectionneur-portrait.php" class="nav-link prev">Article précédent</a>
                <a href="mode-durable.php" class="nav-link next">Article suivant</a>
            </nav>
        </article>
    </div>

    <?php include '../../Frontend/HTML/includes/footer.php'; ?>
</body>
</html> 