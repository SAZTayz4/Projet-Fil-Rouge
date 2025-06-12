<?php
session_start();
require_once '../../translations.php';

$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'recent';

$url = 'https://checkmyckicks.ngrok.app/vinted-feed';
$options = [
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ]
];
$context = stream_context_create($options);
$json = @file_get_contents($url, false, $context);
$pairs = [];
if ($json !== false) {
    $pairs = json_decode($json, true);
    // Limiter aux 500 annonces les plus récentes
    if (count($pairs) > 500) {
        // Trier par date (en supposant que les annonces plus récentes sont à la fin du tableau)
        $pairs = array_slice($pairs, -500);
    }
}

if ($search) {
    $pairs = array_filter($pairs, function($pair) use ($search) {
        return strpos(strtolower($pair['name']), $search) !== false;
    });
}

$sizes = ['36', '36.5', '37', '37.5', '38', '38.5', '39', '39.5', '40', '40.5', '41', '41.5', '42', '42.5', '43', '43.5', '44', '44.5', '45', '45.5', '46', '46.5', '47', '47.5', '48'];

// Récupération des filtres
$selected_sizes = isset($_GET['sizes']) ? (array)$_GET['sizes'] : [];
$selected_brand = isset($_GET['brand']) ? strtolower(trim($_GET['brand'])) : '';
$selected_state = isset($_GET['state']) ? strtolower(trim($_GET['state'])) : '';

// Filtrage des paires
if (!empty($selected_sizes) || $selected_brand || $selected_state) {
    $pairs = array_filter($pairs, function($pair) use ($selected_sizes, $selected_brand, $selected_state) {
        $ok = true;
        
        // Filtre des pointures
        if (!empty($selected_sizes)) {
            $pair_size = strtolower($pair['size']);
            $size_match = false;
            foreach ($selected_sizes as $size) {
                $normalized_pair_size = preg_replace('/[^0-9.]/', '', $pair_size);
                $normalized_selected_size = preg_replace('/[^0-9.]/', '', $size);
                if ($normalized_pair_size === $normalized_selected_size) {
                    $size_match = true;
                    break;
                }
            }
            if (!$size_match) $ok = false;
        }

        // Filtre de marque
        if ($selected_brand) {
            if (strpos(strtolower($pair['name']), $selected_brand) === false) {
                $ok = false;
            }
        }

        // Filtre d'état
        if ($selected_state) {
            $pair_state = strtolower($pair['state'] ?? $pair['size']);
            $state_match = false;
            
            switch($selected_state) {
                case 'neuf':
                    if (strpos($pair_state, 'neuf') !== false || 
                        strpos($pair_state, 'jamais porté') !== false ||
                        strpos($pair_state, 'new') !== false) {
                        $state_match = true;
                    }
                    break;
                case 'tres bon etat':
                    if (strpos($pair_state, 'très bon état') !== false || 
                        strpos($pair_state, 'tres bon etat') !== false ||
                        strpos($pair_state, 'très bon') !== false ||
                        strpos($pair_state, 'very good') !== false) {
                        $state_match = true;
                    }
                    break;
                case 'bon etat':
                    if (strpos($pair_state, 'bon état') !== false || 
                        strpos($pair_state, 'bon etat') !== false ||
                        strpos($pair_state, 'bon') !== false ||
                        strpos($pair_state, 'good') !== false) {
                        $state_match = true;
                    }
                    break;
            }
            
            if (!$state_match) $ok = false;
        }

        return $ok;
    });
}

// Tri
if ($sort === 'alpha') {
    usort($pairs, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
} elseif ($sort === 'price_asc') {
    usort($pairs, function($a, $b) {
        $price_a = intval(preg_replace('/[^0-9]/', '', $a['price']));
        $price_b = intval(preg_replace('/[^0-9]/', '', $b['price']));
        return $price_a - $price_b;
    });
} elseif ($sort === 'price_desc') {
    usort($pairs, function($a, $b) {
        $price_a = intval(preg_replace('/[^0-9]/', '', $a['price']));
        $price_b = intval(preg_replace('/[^0-9]/', '', $b['price']));
        return $price_b - $price_a;
    });
} elseif ($sort === 'oldest') {
    $pairs = array_reverse($pairs);
}

// Au début du fichier, après la récupération des données
$pairs_per_page = 28;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$total_pairs = count($pairs);
$total_pages = ceil($total_pairs / $pairs_per_page);

// Limiter aux 500 annonces les plus récentes
if ($total_pairs > 500) {
    $pairs = array_slice($pairs, -500);
    $total_pairs = 500;
    $total_pages = ceil($total_pairs / $pairs_per_page);
}

// Calculer les paires à afficher pour la page courante
$start_index = ($current_page - 1) * $pairs_per_page;
$pairs_to_display = array_slice($pairs, $start_index, $pairs_per_page);

// Fonction pour générer l'URL de pagination
function getPageUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotCheck - Check MyKicks</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/spotcheck.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="announcement-bar">
        <p>Authentifiez vos sneakers dès maintenant - Service premium disponible 24/7</p>
    </div>
    <div class="spotcheck-container">
        <aside class="spotcheck-filters">
            <h3>Filtres</h3>
            <form method="get" class="filters-form">
                <!-- Pointures -->
                <div class="filter-block">
                    <label>Pointures</label>
                    <div class="size-grid">
                        <?php foreach ($sizes as $size): ?>
                            <label class="size-checkbox">
                                <input type="checkbox" name="sizes[]" value="<?php echo $size; ?>"
                                    <?php echo in_array($size, $selected_sizes) ? 'checked' : ''; ?>
                                    onchange="this.form.submit()">
                                <span><?php echo $size; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Marque -->
                <div class="filter-block">
                    <label for="filter-brand">Marque</label>
                    <input type="text" id="filter-brand" name="brand" 
                        value="<?php echo htmlspecialchars($selected_brand); ?>"
                        placeholder="Nike, Adidas..." 
                        onchange="this.form.submit()">
                </div>

                <!-- État -->
                <div class="filter-block">
                    <label for="filter-state">État</label>
                    <select id="filter-state" name="state" onchange="this.form.submit()">
                        <option value="">Tous les états</option>
                        <option value="neuf" <?php echo $selected_state === 'neuf' ? 'selected' : ''; ?>>Neuf / Jamais porté</option>
                        <option value="tres bon etat" <?php echo $selected_state === 'tres bon etat' ? 'selected' : ''; ?>>Très bon état</option>
                        <option value="bon etat" <?php echo $selected_state === 'bon etat' ? 'selected' : ''; ?>>Bon état</option>
                    </select>
                </div>

                <a href="spotcheck.php" class="reset-btn">Réinitialiser les filtres</a>
            </form>
        </aside>
        <main class="spotcheck-main">
            <div class="spotcheck-header-title"></div>
            <form class="spotcheck-searchbar" method="get">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                <input type="text" name="search" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>"
                    placeholder="Recherchez une paire, une marque..." autocomplete="off">
            </form>
            <div class="spotcheck-topbar">
                <div style="font-weight:500;">
                    <?php echo $total_pairs; ?> résultats trouvés 
                    <?php if ($total_pairs > 500): ?>
                        (limité aux 500 plus récentes)
                    <?php endif; ?>
                </div>
                <div class="spotcheck-sort">
                    <a href="<?php echo getPageUrl(1); ?>&sort=recent" class="<?php echo (!isset($_GET['sort']) || $_GET['sort']=='recent') ? 'active' : ''; ?>">Plus récent</a>
                    <a href="<?php echo getPageUrl(1); ?>&sort=oldest" class="<?php echo (isset($_GET['sort']) && $_GET['sort']=='oldest') ? 'active' : ''; ?>">Plus ancien</a>
                </div>
            </div>
            <div class="spotcheck-grid">
                <?php if (empty($pairs_to_display)): ?>
                    <div style="color:#888;text-align:center;width:100%;padding:40px 0;">Aucune paire trouvée</div>
                <?php else: ?>
                    <?php foreach ($pairs_to_display as $pair): ?>
                        <div class="spotcheck-card">
                            <img src="<?php echo htmlspecialchars($pair['article_image']); ?>" alt="<?php echo htmlspecialchars($pair['name']); ?>">
                            <div class="spotcheck-card-body">
                                <div class="spotcheck-card-title"><?php echo htmlspecialchars($pair['name']); ?></div>
                                <div class="spotcheck-card-info"><b>Prix :</b> <?php echo htmlspecialchars($pair['price']); ?></div>
                                <div class="spotcheck-card-info"><b>Taille :</b> <?php echo htmlspecialchars($pair['size']); ?></div>
                                <a class="spotcheck-card-link" href="<?php echo htmlspecialchars($pair['link']); ?>" target="_blank">Voir l'annonce</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="<?php echo getPageUrl($current_page - 1); ?>" class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $start_page + 4);
                
                if ($end_page - $start_page < 4) {
                    $start_page = max(1, $end_page - 4);
                }

                if ($start_page > 1): ?>
                    <a href="<?php echo getPageUrl(1); ?>" class="pagination-btn">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="<?php echo getPageUrl($i); ?>" 
                       class="pagination-btn <?php echo $i === $current_page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="<?php echo getPageUrl($total_pages); ?>" class="pagination-btn">
                        <?php echo $total_pages; ?>
                    </a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="<?php echo getPageUrl($current_page + 1); ?>" class="pagination-btn">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </main>
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

    <script src="../JS/spotcheck.js"></script>
</body>
</html> 