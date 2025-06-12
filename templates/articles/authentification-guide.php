<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => false, // À mettre à true en production avec HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}
require_once '../../translations.php';

include '../../Frontend/HTML/includes/header.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Art de l'Authentification : Guide Complet des Sneakers - Check MyKicks</title>
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
    <div class="article-container">
        <article>
            <header class="article-header">
                <div class="article-category"><?php echo t('guides'); ?></div>
                <h1 class="article-title">L'Art de l'Authentification : Guide Complet des Sneakers</h1>
                <div class="article-meta">
                    <span class="article-author">Par Thomas Martin - Expert Authentification</span>
                    <span class="article-date">15 Mars 2024</span>
                </div>
            </header>

            <div class="article-content">
                <p>L'authentification des sneakers est un art qui nécessite une attention particulière aux détails et une connaissance approfondie des marques. Dans ce guide complet, nous allons explorer les différentes techniques et points de contrôle essentiels pour authentifier vos sneakers avec précision.</p>

                <h2>Les Fondamentaux de l'Authentification</h2>
                <p>Avant de commencer l'analyse détaillée, il est crucial de comprendre les éléments de base qui définissent une paire authentique :</p>
                <ul>
                    <li>La qualité des matériaux et leur finition</li>
                    <li>La précision des coutures et des détails</li>
                    <li>La cohérence des logos et des marquages</li>
                    <li>L'emballage et les accessoires</li>
                </ul>

                <h2>Analyse des Matériaux</h2>
                <p>Les matériaux utilisés dans les sneakers authentiques sont généralement de haute qualité et présentent des caractéristiques spécifiques :</p>
                <ul>
                    <li>Le cuir doit être souple et présenter une texture naturelle</li>
                    <li>Les tissus doivent être résistants et bien tissés</li>
                    <li>Les semelles doivent être en caoutchouc de qualité</li>
                </ul>

                <blockquote>
                    "La qualité des matériaux est souvent le premier indicateur de l'authenticité d'une paire de sneakers. Les contrefaçons utilisent généralement des matériaux de moindre qualité pour réduire les coûts."
                </blockquote>

                <h2>Points de Contrôle Essentiels</h2>
                <h3>1. Les Logos et Marques</h3>
                <p>Les logos des marques sont des éléments cruciaux à vérifier :</p>
                <ul>
                    <li>Précision des proportions</li>
                    <li>Qualité de l'impression</li>
                    <li>Cohérence des couleurs</li>
                </ul>

                <h3>2. Les Coutures</h3>
                <p>Les coutures des sneakers authentiques sont généralement :</p>
                <ul>
                    <li>Régulières et précises</li>
                    <li>Bien espacées</li>
                    <li>Sans fils lâches ou irréguliers</li>
                </ul>

                <h2>L'Importance de l'Emballage</h2>
                <p>L'emballage original est un élément important de l'authentification :</p>
                <ul>
                    <li>La boîte doit être en bon état</li>
                    <li>Les étiquettes doivent être correctement imprimées</li>
                    <li>Les accessoires doivent être présents et authentiques</li>
                </ul>

                <h2>Conclusion</h2>
                <p>L'authentification des sneakers est un processus qui nécessite de l'expérience et de la patience. En suivant ce guide et en prêtant attention aux détails mentionnés, vous serez mieux équipé pour distinguer les authentiques des contrefaçons.</p>

                <div class="article-tags">
                    <span class="article-tag">Authentification</span>
                    <span class="article-tag">Expertise</span>
                    <span class="article-tag">Guide Pratique</span>
                    <span class="article-tag">Sneakers</span>
                </div>
            </div>

            <nav class="article-navigation">
                <a href="/ProjetFileRouge/templates/blog.php" class="nav-link prev">Retour au blog</a>
                <a href="tendances-2024.php" class="nav-link next">Article suivant</a>
            </nav>
        </article>
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
            <div class="footer-section">
                <h3><?php echo t('footer_links'); ?></h3>
                <p>FAQ</p>
                <p>Blog</p>
                <p>Conditions générales</p>
            </div>
            <div class="footer-section">
                <h3><?php echo t('footer_newsletter'); ?></h3>
                <p><?php echo t('footer_newsletter_text'); ?></p>
            </div>
        </div>
    </footer>
</body>
</html>

include '../../Frontend/HTML/includes/footer.php'; 