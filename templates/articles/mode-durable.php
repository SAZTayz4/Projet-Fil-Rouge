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
    <title>L'Impact de la Mode Durable sur l'Industrie des Sneakers - Check MyKicks</title>
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
                <div class="article-category"><?php echo t('durabilite'); ?></div>
                <h1 class="article-title">L'Impact de la Mode Durable sur l'Industrie des Sneakers</h1>
                <div class="article-meta">
                    <span class="article-author">Par Marc Petit - Expert RSE</span>
                    <span class="article-date">5 Mars 2024</span>
                </div>
            </header>
            <img src="../../IMG/blog5.jpg" alt="Mode Durable Sneakers" class="article-hero-image">
            <div class="article-content">
                <p>L'industrie des sneakers est en pleine transformation, avec une prise de conscience croissante de l'importance de la durabilité. Les marques innovent et repensent leurs processus de production pour répondre aux enjeux environnementaux.</p>
                <div class="grid-container">
                    <div class="grid-item">
                        <h2>Matériaux Recyclés</h2>
                        <p>Les marques développent des matériaux innovants à partir de déchets plastiques et de matériaux recyclés, réduisant ainsi leur empreinte carbone.</p>
                        <img src="../../IMG/blog5-1.jpg" alt="Matériaux Recyclés" class="article-hero-image">
                    </div>
                    <div class="grid-item">
                        <h2>Processus de Production</h2>
                        <p>Optimisation des chaînes de production pour réduire la consommation d'eau et d'énergie, tout en améliorant les conditions de travail.</p>
                        <img src="../../IMG/blog5-2.jpg" alt="Processus de Production" class="article-hero-image">
                    </div>
                    <div class="grid-item">
                        <h2>Circularité</h2>
                        <p>Mise en place de programmes de recyclage et de réparation pour prolonger la durée de vie des produits.</p>
                        <img src="../../IMG/blog5-3.jpg" alt="Circularité" class="article-hero-image">
                    </div>
                    <div class="grid-item">
                        <h2>Transparence</h2>
                        <p>Traçabilité complète de la chaîne d'approvisionnement et communication transparente sur les pratiques durables.</p>
                        <img src="../../IMG/blog5-4.jpg" alt="Transparence" class="article-hero-image">
                    </div>
                </div>
                <blockquote>"La durabilité n'est plus une option, c'est une nécessité. L'industrie des sneakers doit montrer l'exemple en matière d'innovation responsable."<br><span class="quote-author">- Directeur RSE, Nike</span></blockquote>
                <h2>Les Marques qui Innovent</h2>
                <div class="grid-container">
                    <div class="grid-item">
                        <img src="../../IMG/blog5-1.jpg" alt="Nike Move to Zero" class="article-hero-image">
                        <h3>Nike Move to Zero</h3>
                        <p>Objectif zéro déchet et zéro carbone d'ici 2025</p>
                    </div>
                    <div class="grid-item">
                        <img src="../../IMG/blog5-2.jpg" alt="Adidas Parley" class="article-hero-image">
                        <h3>Adidas x Parley</h3>
                        <p>Sneakers fabriquées à partir de déchets plastiques océaniques</p>
                    </div>
                    <div class="grid-item">
                        <img src="../../IMG/blog5-3.jpg" alt="Veja" class="article-hero-image">
                        <h3>Veja</h3>
                        <p>Cuir végétal et caoutchouc naturel d'Amazonie</p>
                    </div>
                </div>
                <h2>L'Impact sur le Consommateur</h2>
                <p>Les consommateurs sont de plus en plus sensibles aux enjeux environnementaux :</p>
                <ul>
                    <li>Demande croissante de transparence</li>
                    <li>Préférence pour les marques éthiques</li>
                    <li>Acceptation de prix plus élevés pour des produits durables</li>
                    <li>Engagement dans la seconde vie des produits</li>
                </ul>
                <div class="article-tags">
                    <span class="article-tag">Durabilité</span>
                    <span class="article-tag">Innovation</span>
                    <span class="article-tag">Éthique</span>
                    <span class="article-tag">Environnement</span>
                </div>
            </div>
            <nav class="article-navigation">
                <a href="entretien-expert.php" class="nav-link prev">Article précédent</a>
                <a href="evolution-sneakers.php" class="nav-link next">Article suivant</a>
            </nav>
        </article>
    </div>
    <?php include '../../Frontend/HTML/includes/footer.php'; ?>
</body>
</html> 