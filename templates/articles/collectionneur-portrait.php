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
    <title>Portrait d'un Collectionneur : L'Art de la Collection - Check MyKicks</title>
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
                <div class="article-category"><?php echo t('interviews'); ?></div>
                <h1 class="article-title">Portrait d'un Collectionneur : L'Art de la Collection</h1>
                <div class="article-meta">
                    <span class="article-author">Par Lucas Bernard - Journaliste Mode</span>
                    <span class="article-date">10 Mars 2024</span>
                </div>
            </header>

            <img src="../../IMG/blog3.jpg" alt="Collection de Sneakers" class="article-hero-image">

            <div class="article-content">
                <p>Dans l'univers passionnant des sneakers, certains collectionneurs se distinguent par leur vision unique et leur dévouement à l'art de la collection. Aujourd'hui, nous rencontrons Alexandre Moreau, un collectionneur parisien dont la passion pour les sneakers a transformé sa vie.</p>

                <blockquote>"Tout a commencé il y a 15 ans avec une paire de Air Jordan 1. J'étais fasciné par l'histoire derrière ce modèle, par sa signification culturelle. C'est ce qui m'a poussé à en apprendre davantage sur chaque paire que j'acquérais."<br><span class="quote-author">- Alexandre Moreau, Collectionneur</span></blockquote>

                <div class="grid-container">
                    <div class="grid-item">
                        <img src="../../IMG/blog3-1.jpg" alt="Air Jordan 1 Collection" class="article-hero-image">
                        <p>La première paire de la collection : Air Jordan 1</p>
                    </div>
                    <div class="grid-item">
                        <img src="../../IMG/blog3-2.jpg" alt="Salle d'exposition" class="article-hero-image">
                        <p>La salle d'exposition personnelle d'Alexandre</p>
                    </div>
                </div>

                <h2>L'Approche de la Collection</h2>
                <p>"Je ne collectionne pas pour la valeur monétaire, mais pour l'histoire et l'art. Chaque paire raconte une histoire, représente une époque, une collaboration, une innovation. C'est cette dimension narrative qui m'intéresse."</p>

                <h2>La Gestion d'une Collection de Plus de 500 Paires</h2>
                <p>"L'organisation est cruciale. J'ai développé un système de catalogage détaillé, avec des conditions de stockage optimales pour chaque paire. La température, l'humidité, la lumière sont des facteurs essentiels à maîtriser."</p>

                <img src="../../IMG/blog3-3.jpg" alt="Système de stockage" class="article-hero-image">
                <blockquote>Le système de stockage professionnel de la collection</blockquote>

                <blockquote>"Commencez petit, apprenez l'histoire, développez votre propre goût. Ne vous laissez pas influencer par les tendances. Une collection authentique reflète votre personnalité et votre passion."<br><span class="quote-author">- Conseil aux nouveaux collectionneurs</span></blockquote>

                <div class="article-tags">
                    <span class="article-tag">Collection</span>
                    <span class="article-tag">Passion</span>
                    <span class="article-tag">Rareté</span>
                    <span class="article-tag">Interview</span>
                </div>
            </div>
            <nav class="article-navigation">
                <a href="tendances-2024.php" class="nav-link prev">Article précédent</a>
                <a href="entretien-expert.php" class="nav-link next">Article suivant</a>
            </nav>
        </article>
    </div>

    <?php include '../../Frontend/HTML/includes/footer.php'; ?>
</body>
</html> 