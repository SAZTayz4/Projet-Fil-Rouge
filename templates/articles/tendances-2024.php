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
    <title>Les Sneakers qui Définiront 2024 : Analyse du Marché - Check MyKicks</title>
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
                <div class="article-category"><?php echo t('news'); ?></div>
                <h1 class="article-title">Les Sneakers qui Définiront 2024 : Analyse du Marché</h1>
                <div class="article-meta">
                    <span class="article-author">Par Sophie Martin - Analyste Mode</span>
                    <span class="article-date">15 Mars 2024</span>
                </div>
            </header>

            <img src="../../IMG/blog2.jpg" alt="Tendances Sneakers 2024" class="article-hero-image">

            <div class="article-content">
                <p>2024 marque un tournant majeur dans l'industrie des sneakers, avec l'émergence de nouvelles tendances qui redéfinissent le marché. Entre innovation technologique, durabilité et collaborations exclusives, découvrez les modèles qui façonneront l'année.</p>

                <div class="grid-container">
                    <div class="grid-item">
                        <h2>Design Minimaliste</h2>
                        <p>Retour aux basiques avec des designs épurés et intemporels, privilégiant la qualité des matériaux et la finition.</p>
                        <img src="../../IMG/blog2-1.jpg" alt="Design Minimaliste" class="article-hero-image">
                    </div>
                    <div class="grid-item">
                        <h2>Technologie Avancée</h2>
                        <p>Intégration de nouvelles technologies comme l'auto-lacage et les matériaux adaptatifs pour un confort optimal.</p>
                        <img src="../../IMG/blog2-2.jpg" alt="Technologie Avancée" class="article-hero-image">
                    </div>
                    <div class="grid-item">
                        <h2>Durabilité</h2>
                        <p>Focus sur les matériaux recyclés et les processus de production éthiques, répondant aux attentes des consommateurs.</p>
                        <img src="../../IMG/blog2-3.jpg" alt="Durabilité" class="article-hero-image">
                    </div>
                    <div class="grid-item">
                        <h2>Personnalisation</h2>
                        <p>Montée en puissance des options de personnalisation, permettant aux consommateurs de créer leur paire unique.</p>
                        <img src="../../IMG/blog2-4.jpg" alt="Personnalisation" class="article-hero-image">
                    </div>
                </div>

                <h2>Collaborations à Suivre</h2>
                <div class="grid-container">
                    <div class="grid-item">
                        <img src="../../IMG/blog2-5.jpg" alt="Nike x Off-White" class="article-hero-image">
                        <h3>Nike x Off-White</h3>
                        <p>Nouvelle collection "Transparency"</p>
                    </div>
                    <div class="grid-item">
                        <img src="../../IMG/blog2-6.jpg" alt="Adidas x Parley" class="article-hero-image">
                        <h3>Adidas x Parley</h3>
                        <p>Édition 100% recyclée</p>
                    </div>
                    <div class="grid-item">
                        <img src="../../IMG/blog2-7.jpg" alt="New Balance x Jjjjound" class="article-hero-image">
                        <h3>New Balance x Jjjjound</h3>
                        <p>Collection minimaliste exclusive</p>
                    </div>
                </div>

                <div class="quote-box">
                    <blockquote>"2024 sera l'année où l'innovation technologique rencontrera la durabilité, créant une nouvelle ère pour l'industrie des sneakers."<br><span class="quote-author">- Directeur de l'Innovation, Nike</span></blockquote>
                </div>

                <h2>Innovations Technologiques</h2>
                <p>Les marques investissent massivement dans la recherche et le développement :</p>
                <ul>
                    <li>Matériaux auto-réparants</li>
                    <li>Systèmes de régulation de température</li>
                    <li>Technologies de recyclage avancées</li>
                    <li>Applications de personnalisation en réalité augmentée</li>
                </ul>

                <div class="article-tags">
                    <span class="article-tag">Tendances</span>
                    <span class="article-tag">Innovation</span>
                    <span class="article-tag">Collaborations</span>
                    <span class="article-tag">2024</span>
                </div>
            </div>
            <nav class="article-navigation">
                <a href="authentification-guide.php" class="nav-link prev">Article précédent</a>
                <a href="collectionneur-portrait.php" class="nav-link next">Article suivant</a>
            </nav>
        </article>
    </div>

    <?php include '../../Frontend/HTML/includes/footer.php'; ?>
</body>
</html> 