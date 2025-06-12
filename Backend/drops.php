<?php
require_once '../../translations.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lire le fichier JSON des drops
$jsonFile = '../../WhenToCop/whentocop_products.json';
$drops = [];
if (file_exists($jsonFile)) {
    $drops = json_decode(file_get_contents($jsonFile), true);
}

// Trier les drops par date
usort($drops, function($a, $b) {
    return strtotime($a['date_sortie']) - strtotime($b['date_sortie']);
});

// Pagination
$items_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$total_items = count($drops);
$total_pages = ceil($total_items / $items_per_page);
$offset = ($current_page - 1) * $items_per_page;

// Filtres
$selected_brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$selected_price_range = isset($_GET['price']) ? $_GET['price'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Appliquer les filtres
$filtered_drops = array_filter($drops, function($drop) use ($selected_brand, $selected_price_range, $search_query) {
    $matches_brand = empty($selected_brand) || $drop['marque'] === $selected_brand;
    $matches_search = empty($search_query) || 
                     stripos($drop['nom'], $search_query) !== false || 
                     stripos($drop['marque'], $search_query) !== false;
    
    if (!empty($selected_price_range)) {
        $price = (int)str_replace(['€', ' '], '', $drop['prix_retail']);
        switch($selected_price_range) {
            case '0-100':
                $matches_price = $price <= 100;
                break;
            case '100-200':
                $matches_price = $price > 100 && $price <= 200;
                break;
            case '200+':
                $matches_price = $price > 200;
                break;
            default:
                $matches_price = true;
        }
    } else {
        $matches_price = true;
    }
    
    return $matches_brand && $matches_search && $matches_price;
});

// Récupérer les marques uniques pour le filtre
$brands = array_unique(array_column($drops, 'marque'));

// Paginer les résultats filtrés
$filtered_drops = array_values($filtered_drops);
$total_filtered_items = count($filtered_drops);
$total_filtered_pages = ceil($total_filtered_items / $items_per_page);
$paginated_drops = array_slice($filtered_drops, $offset, $items_per_page);
?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check MyKicks - Prochains Drops</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/drops.css">
    <script>
    window.isUserLoggedIn = <?php echo isset($_SESSION['utilisateur_id']) ? 'true' : 'false'; ?>;
    </script>
    <script src="../JS/drops.js" defer></script>
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
                    
                        <a href="/ProjetFileRouge/Frontend/HTML/compte.php" class="icon-link">
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

    <div class="drops-container">

        <div class="drops-content">
            <div class="drops-header">
                <h1>Prochains Drops</h1>
                <p>Découvrez les prochaines sorties de sneakers</p>
            </div>

            <?php if (empty($paginated_drops)): ?>
                <div class="no-drops">
                    <i class="fas fa-calendar-times"></i>
                    <p>Aucun drop prévu pour le moment</p>
                    <button class="btn-refresh" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Rafraîchir
                    </button>
                </div>
            <?php else: ?>
                <?php
                // Grouper les drops par date de sortie
                $drops_by_date = [];
                foreach ($paginated_drops as $drop) {
                    $drops_by_date[$drop['date_sortie']][] = $drop;
                }
                ?>
                <?php foreach ($drops_by_date as $date => $drops_list): ?>
                    <div class="date-section">
                       
                        <div class="drops-grid">
                            <?php foreach ($drops_list as $drop): ?>
                                <div class="drop-card" onclick="showDropDetails(<?php echo htmlspecialchars(json_encode($drop)); ?>)">
                                    <span class="drop-date-badge">
                                        <?php
                                        $mois = mb_strtoupper((new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, 'LLL'))->format(strtotime($drop['date_sortie'])));
                                        $jour = date('j', strtotime($drop['date_sortie']));
                                        echo "$mois $jour";
                                        ?>
                                    </span>
                                    <img src="<?php echo htmlspecialchars($drop['image']); ?>" alt="<?php echo htmlspecialchars($drop['nom']); ?>" class="drop-image">
                                    <div class="drop-info">
                                        <h3 class="drop-title"><?php echo htmlspecialchars($drop['nom']); ?></h3>
                                        <div class="drop-brand"><?php echo htmlspecialchars($drop['marque']); ?></div>
                                        <div class="drop-price"><?php echo htmlspecialchars($drop['prix_retail']); ?></div>
                                        <div class="drop-actions">
                                            <button class="btn-reminder" onclick="event.stopPropagation(); setReminder(<?php echo htmlspecialchars(json_encode($drop)); ?>)">
                                                <i class="far fa-bell"></i> Rappel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($total_filtered_pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_filtered_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&brand=<?php echo urlencode($selected_brand); ?>&price=<?php echo urlencode($selected_price_range); ?>&search=<?php echo urlencode($search_query); ?>" 
                               class="<?php echo $current_page === $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div id="dropModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="modal-left">
                <img id="modalImage" class="modal-image" src="" alt="">
            </div>
            <div class="modal-right">
                <div class="modal-breadcrumb">
                    <span>Sneakers</span>
                    <span id="modalBrand"></span>
                    <span id="modalModel"></span>
                </div>
                <div class="modal-status">Disponible</div>
                <h2 class="modal-title" id="modalTitle"></h2>
                <div class="modal-price" id="modalPrice"></div>
                
                <div class="modal-size-selector">
                    <label class="modal-size-label">Sélectionne ta taille (EU)</label>
                    <select class="modal-size-dropdown">
                        <option value="">Sélectionne une taille</option>
                        <option value="40">40</option>
                        <option value="41">41</option>
                        <option value="42">42</option>
                        <option value="43">43</option>
                        <option value="44">44</option>
                        <option value="45">45</option>
                    </select>
                </div>

                <div class="modal-marketplace-section">
                    <h3 class="modal-marketplace-title">Où acheter</h3>
                    <div id="modalMarketplaces"></div>
                </div>

                <div class="modal-info-grid">
                    <div class="modal-info-item">
                        <div class="modal-info-label">SKU</div>
                        <div class="modal-info-value" id="modalSKU"></div>
                    </div>
                    <div class="modal-info-item">
                        <div class="modal-info-label">Coloris</div>
                        <div class="modal-info-value" id="modalColors"></div>
                    </div>
                    <div class="modal-info-item">
                        <div class="modal-info-label">Date de sortie</div>
                        <div class="modal-info-value" id="modalDate"></div>
                    </div>
                    <div class="modal-info-item">
                        <div class="modal-info-label">Prix de vente</div>
                        <div class="modal-info-value" id="modalPrixVente"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('dropModal');
        const closeModal = document.querySelector('.close-modal');

        function showDropDetails(drop) {
            document.getElementById('modalImage').src = drop.image;
            document.getElementById('modalTitle').textContent = drop.nom;
            document.getElementById('modalDate').textContent = `Date de sortie : ${drop.date_sortie}`;
            document.getElementById('modalBrand').textContent = `Marque : ${drop.marque}`;
            document.getElementById('modalPrice').textContent = `Prix : ${drop.prix_retail}`;
            document.getElementById('modalModel').textContent = `Modèle : ${drop.modele}`;
            document.getElementById('modalColors').textContent = `Couleurs : ${drop.couleurs}`;
            document.getElementById('modalSKU').textContent = drop.sku || 'N/A';
            
            const marketplacesDiv = document.getElementById('modalMarketplaces');
            marketplacesDiv.innerHTML = '';
            drop.marketplaces.forEach(marketplace => {
                const tag = document.createElement('span');
                tag.className = 'marketplace-tag';
                tag.textContent = marketplace;
                marketplacesDiv.appendChild(tag);
            });

            modal.style.display = 'block';
        }

        closeModal.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function setReminder(drop) {
            if (!<?php echo isset($_SESSION['utilisateur_id']) ? 'true' : 'false'; ?>) {
                window.location.href = '/ProjetFileRouge/Backend/auth/login.php';
                return;
            }
            
            // TODO: Implémenter la logique de rappel
            alert('Rappel configuré avec succès !');
        }

        // Animation et logique des filtres
        function toggleFilter(header) {
            // Fermer tous les autres
            document.querySelectorAll('.filter-header').forEach(h => {
                if (h !== header) {
                    h.classList.remove('active');
                    if (h.nextElementSibling) h.nextElementSibling.style.display = 'none';
                }
            });
            // Ouvrir/fermer celui cliqué
            header.classList.toggle('active');
            const content = header.nextElementSibling;
            if (header.classList.contains('active')) {
                content.style.display = 'block';
                content.style.animation = 'fadeInFilter 0.3s';
            } else {
                content.style.display = 'none';
            }
        }

        // Animation CSS
        const style = document.createElement('style');
        style.innerHTML = `@keyframes fadeInFilter { from { opacity: 0; transform: translateY(-10px);} to { opacity: 1; transform: none; } }`;
        document.head.appendChild(style);

        // Effacer les filtres et soumettre
        const clearBtn = document.getElementById('clearFiltersBtn');
        if(clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.filter-options input[type="radio"]').forEach(input => {
                    if(input.value === '') input.checked = true;
                    else input.checked = false;
                });
                this.form.submit();
            });
        }

        // Ouvre le premier filtre par défaut
        window.addEventListener('DOMContentLoaded', () => {
            const firstHeader = document.querySelector('.filter-header');
            if(firstHeader) toggleFilter(firstHeader);
        });

        window.isUserLoggedIn = <?php echo isset($_SESSION['utilisateur_id']) ? 'true' : 'false'; ?>;
    </script>
</body>
</html> 