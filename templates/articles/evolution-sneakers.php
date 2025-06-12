<?php
// Configuration de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => false,
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
    <title>L'Évolution des Sneakers : Du Sport à la Mode - Check MyKicks</title>
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
        .footer {
            width: 100vw;
            margin-left: calc(-50vw + 50%);
            position: relative;
            
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
                <div class="article-category"><?php echo t('histoire'); ?></div>
                <h1 class="article-title">L'Évolution des Sneakers : Du Sport à la Mode</h1>
                <div class="article-meta">
                    <span class="article-author">Par Jean Dupont - Historien de la Mode</span>
                    <span class="article-date">25 Mars 2024</span>
                </div>
            </header>
            <img src="../../IMG/blog6.jpg" alt="Évolution des Sneakers" class="article-hero-image">
            <div class="article-content">
                <p>Des terrains de sport aux défilés de mode, les sneakers ont connu une transformation remarquable. Retour sur cette évolution fascinante qui a révolutionné la mode et la culture.</p>
                <blockquote>"Les sneakers sont passées d'équipement sportif à symbole culturel, reflétant les changements sociaux et les tendances de chaque époque."<br><span class="quote-author">- Dr. Sophie Martin, Historienne de la Mode</span></blockquote>
                <h2>Les Origines Sportives</h2>
                <p>L'histoire des sneakers commence dans le sport :</p>
                <ul>
                    <li>Premières chaussures de tennis en caoutchouc</li>
                    <li>Développement des baskets de basket-ball</li>
                    <li>Innovations technologiques pour la performance</li>
                    <li>Émergence des grandes marques sportives</li>
                </ul>
                <div class="grid-container">
                    <div class="grid-item">
                        <img src="../../IMG/blog6-1.jpg" alt="Sneakers Vintage" class="article-hero-image">
                        <p>Les premières sneakers de sport</p>
                    </div>
                    <div class="grid-item">
                        <img src="../../IMG/blog6-2.jpg" alt="Évolution du Design" class="article-hero-image">
                        <p>L'évolution du design à travers les décennies</p>
                    </div>
                </div>
                <h2>L'Entrée dans la Mode</h2>
                <p>Le tournant vers la mode s'est opéré progressivement :</p>
                <ul>
                    <li>Adoption par les subcultures urbaines</li>
                    <li>Collaborations avec des designers</li>
                    <li>Apparition dans les défilés de mode</li>
                    <li>Influence des célébrités et des athlètes</li>
                </ul>
                <img src="../../IMG/blog6-3.jpg" alt="Sneakers Mode" class="article-hero-image">
                <blockquote>Les sneakers sur les podiums de mode</blockquote>
                <blockquote>"Les collaborations entre marques sportives et maisons de mode ont révolutionné l'industrie, créant un nouveau segment de marché."<br><span class="quote-author">- Marc Laurent, Expert en Mode</span></blockquote>
                <h2>Impact Culturel</h2>
                <p>Les sneakers ont profondément influencé la culture :</p>
                <ul>
                    <li>Émergence de la culture sneaker</li>
                    <li>Création de communautés de collectionneurs</li>
                    <li>Influence sur la musique et l'art</li>
                    <li>Développement du marché de la revente</li>
                </ul>
                <div class="article-tags">
                    <span class="article-tag">Histoire</span>
                    <span class="article-tag">Culture</span>
                    <span class="article-tag">Mode</span>
                    <span class="article-tag">Évolution</span>
                </div>
            </div>
            <nav class="article-navigation">
                <a href="mode-durable.php" class="nav-link prev">Article précédent</a>
                <a href="tendances-2024.php" class="nav-link next">Article suivant</a>
            </nav>
        </article>
    </div>
    <?php include '../../Frontend/HTML/includes/footer.php'; ?>
</body>
</html> 