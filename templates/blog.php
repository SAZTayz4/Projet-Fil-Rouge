<?php
// Configuration de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => false, // À mettre à true en production avec HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}
require_once '../translations.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check MyKicks - <?php echo t('Blog'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../Frontend/CSS/style.css">
    <link rel="stylesheet" href="../Frontend/CSS/blog.css">
</head>
<body>
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
                        <a href="/ProjetFileRouge/Frontend/HTML/comptes.php" class="icon-link">
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

    <div class="blog-container">
        <div class="blog-header">
            <h1><?php echo t('Blog'); ?></h1>
            <p><?php echo t('blog_subtitle'); ?></p>
        </div>

        <div class="blog-search-container">
            <div class="blog-search">
                <i class="fas fa-search search-icon"></i>
                <input type="text" placeholder="<?php echo t('search_articles'); ?>">
            </div>
        </div>

        <div class="blog-categories">
            <button class="category-button active" data-category="all"><?php echo t('all'); ?></button>
            <button class="category-button" data-category="news"><?php echo t('news'); ?></button>
            <button class="category-button" data-category="guides"><?php echo t('guides'); ?></button>
            <button class="category-button" data-category="reviews"><?php echo t('reviews'); ?></button>
            <button class="category-button" data-category="interviews"><?php echo t('interviews'); ?></button>
        </div>

        <div class="blog-grid">
            <a href="articles/authentification-guide.php" class="blog-card" data-category="guides">
                <div class="blog-content">
                    <div class="blog-category"><?php echo t('guides'); ?></div>
                    <h2 class="blog-title">L'Art de l'Authentification : Guide Complet des Sneakers</h2>
                    <p class="blog-excerpt">Découvrez les techniques professionnelles pour authentifier vos sneakers. De l'analyse des matériaux à la vérification des détails, apprenez à distinguer les authentiques des contrefaçons avec précision.</p>
                    <div class="blog-tags">
                        <span class="blog-tag">Authentification</span>
                        <span class="blog-tag">Expertise</span>
                        <span class="blog-tag">Guide Pratique</span>
                    </div>
                    <div class="blog-meta">
                        <div class="blog-author">
                            <span>Thomas Martin - Expert Authentification</span>
                        </div>
                        <div class="blog-info">
                            <span class="blog-date">15 Mars 2024</span>
                            <span class="read-time"><i class="far fa-clock"></i> 8 min</span>
                        </div>
                    </div>
                    <span class="read-more">Lire l'article</span>
                </div>
            </a>

            <!-- Article 2 -->
            <a href="articles/tendances-2024.php" class="blog-card" data-category="news">
                <div class="blog-content">
                    <div class="blog-category"><?php echo t('news'); ?></div>
                    <h2 class="blog-title">Les Sneakers qui Définiront 2024 : Analyse du Marché</h2>
                    <p class="blog-excerpt">Une analyse approfondie des tendances émergentes dans l'industrie des sneakers. Des collaborations exclusives aux innovations technologiques, découvrez ce qui façonne l'avenir de la culture sneaker.</p>
                    <div class="blog-tags">
                        <span class="blog-tag">Tendances 2024</span>
                        <span class="blog-tag">Analyse de Marché</span>
                        <span class="blog-tag">Innovation</span>
                    </div>
                    <div class="blog-meta">
                        <div class="blog-author">
                            <span>Sophie Dubois - Analyste Mode</span>
                        </div>
                        <div class="blog-info">
                            <span class="blog-date">12 Mars 2024</span>
                            <span class="read-time"><i class="far fa-clock"></i> 6 min</span>
                        </div>
                    </div>
                    <span class="read-more">Lire l'article</span>
                </div>
            </a>

            <a href="articles/collectionneur-portrait.php" class="blog-card" data-category="interviews">
                <div class="blog-content">
                    <div class="blog-category"><?php echo t('interviews'); ?></div>
                    <h2 class="blog-title">Portrait d'un Collectionneur : L'Art de la Collection</h2>
                    <p class="blog-excerpt">Rencontre avec un collectionneur passionné qui nous dévoile les secrets de sa collection de plus de 500 paires. Une immersion dans l'univers fascinant de la collection de sneakers rares et exclusives.</p>
                    <div class="blog-tags">
                        <span class="blog-tag">Collection</span>
                        <span class="blog-tag">Passion</span>
                        <span class="blog-tag">Rareté</span>
                    </div>
                    <div class="blog-meta">
                        <div class="blog-author">
                            <span>Lucas Bernard - Journaliste Mode</span>
                        </div>
                        <div class="blog-info">
                            <span class="blog-date">10 Mars 2024</span>
                            <span class="read-time"><i class="far fa-clock"></i> 10 min</span>
                        </div>
                    </div>
                    <span class="read-more">Lire l'article</span>
                </div>
            </a>

            <!-- Article 4 -->
            <a href="articles/entretien-expert.php" class="blog-card" data-category="guides">
                <div class="blog-content">
                    <div class="blog-category"><?php echo t('guides'); ?></div>
                    <h2 class="blog-title">L'Entretien Expert : Protéger Votre Investissement</h2>
                    <p class="blog-excerpt">Guide complet sur l'entretien professionnel des sneakers. Des techniques de nettoyage aux méthodes de stockage, apprenez à préserver la valeur et l'apparence de vos paires les plus précieuses.</p>
                    <div class="blog-tags">
                        <span class="blog-tag">Entretien</span>
                        <span class="blog-tag">Conservation</span>
                        <span class="blog-tag">Expertise</span>
                    </div>
                    <div class="blog-meta">
                        <div class="blog-author">
                            <span>Emma Laurent - Restauratrice</span>
                        </div>
                        <div class="blog-info">
                            <span class="blog-date">8 Mars 2024</span>
                            <span class="read-time"><i class="far fa-clock"></i> 7 min</span>
                        </div>
                    </div>
                    <span class="read-more">Lire l'article</span>
                </div>
            </a>

            <!-- Article 5 -->
            <a href="articles/mode-durable.php" class="blog-card" data-category="news">
                <div class="blog-content">
                    <div class="blog-category"><?php echo t('news'); ?></div>
                    <h2 class="blog-title">L'Impact de la Mode Durable sur l'Industrie des Sneakers</h2>
                    <p class="blog-excerpt">Analyse approfondie de la révolution durable dans l'industrie des sneakers. Des matériaux innovants aux pratiques éthiques, découvrez comment la durabilité transforme le marché.</p>
                    <div class="blog-tags">
                        <span class="blog-tag">Durabilité</span>
                        <span class="blog-tag">Innovation</span>
                        <span class="blog-tag">Éthique</span>
                    </div>
                    <div class="blog-meta">
                        <div class="blog-author">
                            <span>Marc Petit - Expert RSE</span>
                        </div>
                        <div class="blog-info">
                            <span class="blog-date">5 Mars 2024</span>
                            <span class="read-time"><i class="far fa-clock"></i> 9 min</span>
                        </div>
                    </div>
                    <span class="read-more">Lire l'article</span>
                </div>
            </a>

            <!-- Article 6 -->
            <a href="articles/evolution-sneakers.php" class="blog-card" data-category="guides">
                <div class="blog-content">
                    <div class="blog-category"><?php echo t('guides'); ?></div>
                    <h2 class="blog-title">L'Évolution des Sneakers : Du Sport à l'Art</h2>
                    <p class="blog-excerpt">Une exploration fascinante de l'histoire des sneakers, de leur origine sportive à leur statut d'icône culturelle. Découvrez comment les sneakers sont devenues un symbole de style et d'expression personnelle.</p>
                    <div class="blog-tags">
                        <span class="blog-tag">Histoire</span>
                        <span class="blog-tag">Culture</span>
                        <span class="blog-tag">Évolution</span>
                    </div>
                    <div class="blog-meta">
                        <div class="blog-author">
                            <span>Julie Moreau - Historienne de la Mode</span>
                        </div>
                        <div class="blog-info">
                            <span class="blog-date">1 Mars 2024</span>
                            <span class="read-time"><i class="far fa-clock"></i> 12 min</span>
                        </div>
                    </div>
                    <span class="read-more">Lire l'article</span>
                </div>
            </a>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des filtres de catégorie
        const categoryButtons = document.querySelectorAll('.category-button');
        const blogCards = document.querySelectorAll('.blog-card');
        const searchInput = document.querySelector('.blog-search input');

        // Fonction pour filtrer les articles
        function filterArticles(category, searchTerm = '') {
            blogCards.forEach(card => {
                const cardCategory = card.dataset.category;
                const cardTitle = card.querySelector('.blog-title').textContent.toLowerCase();
                const cardExcerpt = card.querySelector('.blog-excerpt').textContent.toLowerCase();
                const cardTags = Array.from(card.querySelectorAll('.blog-tag')).map(tag => tag.textContent.toLowerCase());
                
                const matchesCategory = category === 'all' || cardCategory === category;
                const matchesSearch = searchTerm === '' || 
                    cardTitle.includes(searchTerm) || 
                    cardExcerpt.includes(searchTerm) || 
                    cardTags.some(tag => tag.includes(searchTerm));

                if (matchesCategory && matchesSearch) {
                    card.style.display = 'block';
                    // Animation de fade-in
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.style.opacity = '1';
                    }, 50);
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Gestion des clics sur les boutons de catégorie
        categoryButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Mise à jour de l'état actif des boutons
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                // Filtrage des articles
                const category = button.dataset.category;
                const searchTerm = searchInput.value.toLowerCase();
                filterArticles(category, searchTerm);
            });
        });

        // Gestion de la recherche
        searchInput.addEventListener('input', () => {
            const searchTerm = searchInput.value.toLowerCase();
            const activeCategory = document.querySelector('.category-button.active').dataset.category;
            filterArticles(activeCategory, searchTerm);
        });

        // Initialisation : afficher tous les articles
        filterArticles('all');
    });
    </script>

</body>
</html> 