<?php
session_start();
require_once '../../translations.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check MyKicks - <?php echo t('home'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/chatbot.css">
    <script src="/ProjetFileRouge/Frontend/JS/drops.js"></script>
    <script src="/ProjetFileRouge/Frontend/JS/chatbot.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="announcement-bar">
        <p>Authentifiez vos sneakers dès maintenant - Service premium disponible 24/7</p>
    </div>

    <section class="hero-section">
        <div class="hero-text">
            <h1><?php echo t('hero_main_title'); ?></h1>
            <p class="subtitle"><?php echo t('hero_subtitle'); ?></p>
            <p><?php echo t('hero_description'); ?></p>
            <a href="" class="cta-button"><?php echo t('start_now'); ?></a>
        </div>
        <div class="hero-image">
            <img src="../../Frontend/src/IMG_7175.jpg" alt="Sneaker authentification">
        </div>
    </section>

    <section class="features-section">
        <div class="features-header">
            <h2><?php echo t('features_title'); ?></h2>
            <p><?php echo t('features_subtitle'); ?></p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-robot"></i>
                <h3><?php echo t('feature_bot_title'); ?></h3>
                <p><?php echo t('feature_bot_desc'); ?></p>
            </div>
            <div class="feature-card">
                <i class="fas fa-brain"></i>
                <h3><?php echo t('feature_ai_title'); ?></h3>
                <p><?php echo t('feature_ai_desc'); ?></p>
            </div>
            <div class="feature-card">
                <i class="fas fa-chart-line"></i>
                <h3><?php echo t('feature_analytics_title'); ?></h3>
                <p><?php echo t('feature_analytics_desc'); ?></p>
            </div>
            <div class="feature-card">
                <i class="fas fa-cogs"></i>
                <h3><?php echo t('feature_auto_title'); ?></h3>
                <p><?php echo t('feature_auto_desc'); ?></p>
            </div>
        </div>
    </section>

    <section class="how-it-works">
        <div class="how-it-works-header">
            <h2><?php echo t('how_it_works_title'); ?></h2>
            <p><?php echo t('how_it_works_subtitle'); ?></p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3><?php echo t('step_1_title'); ?></h3>
                <p><?php echo t('step_1_desc'); ?></p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3><?php echo t('step_2_title'); ?></h3>
                <p><?php echo t('step_2_desc'); ?></p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3><?php echo t('step_3_title'); ?></h3>
                <p><?php echo t('step_3_desc'); ?></p>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">10K+</div>
                <p><?php echo t('stat_users'); ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-number">1M+</div>
                <p><?php echo t('stat_verifications'); ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-number">95%</div>
                <p><?php echo t('stat_accuracy'); ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-number">24/7</div>
                <p><?php echo t('stat_support'); ?></p>
            </div>
        </div>
    </section>


    <section class="pricing-section">
        <div class="pricing-header">
            <h2><?php echo t('pricing_title'); ?></h2>
            <p><?php echo t('pricing_subtitle'); ?></p>
        </div>
        <div class="pricing-grid">
            <div class="pricing-card">
                <div class="pricing-content">
                    <h3>Avancé</h3>
                    <p class="price">29,99€ /mois</p>
                    <ul>
                        <li>Vérifications illimitées</li>
                        <li>Accès au chatbot</li>
                        <li>Accès à l'auto-cop</li>
                        <li>Support prioritaire</li>
                    </ul>
                </div>
                <a href="/ProjetFileRouge/Frontend/HTML/paiement.php?type=avance" class="btn btn-primary">Choisir ce plan</a>
            </div>
            <div class="pricing-card">
                <div class="pricing-content">
                    <h3>Intermédiaire</h3>
                    <p class="price">19,99€ /mois</p>
                    <ul>
                        <li>50 vérifications par mois</li>
                        <li>Accès au chatbot</li>
                        <li>Accès à l'auto-cop</li>
                    </ul>
                </div>
                <a href="/ProjetFileRouge/Frontend/HTML/paiement.php?type=intermediaire" class="btn btn-primary">Choisir ce plan</a>
            </div>
            <div class="pricing-card">
                <div class="pricing-content">
                    <h3>Débutant</h3>
                    <p class="price">9,99€ /mois</p>
                    <ul>
                        <li>20 vérifications par mois</li>
                        <li>Accès au chatbot</li>
                    </ul>
                </div>
                <a href="/ProjetFileRouge/Frontend/HTML/paiement.php?type=debutant" class="btn btn-primary">Choisir ce plan</a>
            </div>
            <div class="pricing-card">
                <div class="pricing-content">
                    <h3>Gratuit</h3>
                    <p class="price">0,00€ /mois</p>
                    <ul>
                        <li>5 vérifications par mois</li>
                        <li>Accès basique</li>
                    </ul>
                </div>
                <a href="/ProjetFileRouge/Frontend/HTML/paiement.php?type=gratuit" class="btn btn-primary">Choisir ce plan</a>
            </div>
        </div>
        
    </section>

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

    <!-- Structure des notifications -->
    <div id="dropBellFab" class="drop-bell-fab">
        <i class="fas fa-bell"></i>
        <span id="dropBellCount" class="drop-bell-count">0</span>
    </div>

    <div id="dropBellPopup" class="drop-bell-popup">
        <div class="drop-bell-header">
            <h3>Prochains Drops</h3>
            <button class="drop-bell-close" aria-label="Fermer">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="dropBellList" class="drop-bell-list">
            <!-- Les drops seront ajoutés ici dynamiquement -->
        </div>
        <button id="markAllRead" class="drop-bell-mark-all">
            Tout marquer comme lu
        </button>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation des éléments
        const elements = {
            fab: document.getElementById('dropBellFab'),
            popup: document.getElementById('dropBellPopup'),
            closeBtn: document.querySelector('.drop-bell-close'),
            list: document.getElementById('dropBellList'),
            count: document.getElementById('dropBellCount'),
            markAllRead: document.getElementById('markAllRead')
        };

        // Vérification que tous les éléments existent
        Object.entries(elements).forEach(([key, element]) => {
            if (!element) {
                console.error(`Élément manquant: ${key}`);
                return;
            }
        });

        let drops = [];
        <?php
        $jsonFile = '../../WhenToCop/whentocop_products.json';
        $drops = [];
        if (file_exists($jsonFile)) {
            $drops = json_decode(file_get_contents($jsonFile), true) ?: [];
        }
        usort($drops, function($a, $b) {
            return strtotime($a['date_sortie']) - strtotime($b['date_sortie']);
        });
        $today = date('Y-m-d');
        $drops = array_filter($drops, function($drop) use ($today) {
            return $drop['date_sortie'] >= $today;
        });
        ?>

        // Initialisation du chatbot
        const chatbot = new Chatbot();

        // Gestion des drops
        function updateDropsList() {
            if (!elements.list) return;
            
            elements.list.innerHTML = '';
            drops.forEach(drop => {
                const dropElement = document.createElement('div');
                dropElement.className = 'drop-bell-item';
                dropElement.innerHTML = `
                    <div class="drop-bell-item-content">
                        <h4>${drop.nom}</h4>
                        <p>Sortie: ${new Date(drop.date_sortie).toLocaleDateString()}</p>
                        <p>Prix: ${drop.prix}€</p>
                    </div>
                `;
                elements.list.appendChild(dropElement);
            });

            // Mise à jour du compteur
            if (elements.count) {
                elements.count.textContent = drops.length;
            }
        }

        // Gestionnaires d'événements
        if (elements.fab) {
            elements.fab.addEventListener('click', () => {
                if (elements.popup) {
                    elements.popup.classList.toggle('active');
                }
            });
        }

        if (elements.closeBtn) {
            elements.closeBtn.addEventListener('click', () => {
                if (elements.popup) {
                    elements.popup.classList.remove('active');
                }
            });
        }

        if (elements.markAllRead) {
            elements.markAllRead.addEventListener('click', () => {
                drops = [];
                updateDropsList();
                if (elements.popup) {
                    elements.popup.classList.remove('active');
                }
            });
        }

        // Initialisation
        updateDropsList();
    });
    </script>

    <!-- Ajout des ressources SVG manquantes -->
    <script>
    // Création des patterns SVG manquants
    const svgPatterns = {
        'circuit-pattern': `<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <pattern id="circuit-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                <path d="M0 10h20M10 0v20" stroke="#ccc" stroke-width="1" fill="none"/>
                <circle cx="10" cy="10" r="2" fill="#ccc"/>
            </pattern>
        </svg>`,
        'grid-pattern': `<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <pattern id="grid-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                <path d="M0 0h20v20H0z" fill="none" stroke="#eee" stroke-width="1"/>
            </pattern>
        </svg>`
    };

    // Injection des patterns dans le DOM
    Object.entries(svgPatterns).forEach(([id, svg]) => {
        const div = document.createElement('div');
        div.style.display = 'none';
        div.innerHTML = svg;
        document.body.appendChild(div);
    });
    </script>


</body>
</html> 