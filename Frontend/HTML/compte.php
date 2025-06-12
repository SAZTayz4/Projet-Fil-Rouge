<?php
session_start();
require_once '../../config/database.php';
require_once __DIR__ . '/../../translations.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: /ProjetFileRouge/Backend/auth/login.php');
    exit;
}

try {
    $pdo = getConnection();

// Récupérer les informations de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo '<div style="color:red">Erreur : utilisateur non trouvé ou problème de requête SQL.</div>';
    }

// Récupérer les factures
$stmt = $pdo->prepare("SELECT f.*, p.typeAbonnement, p.montant 
                       FROM facture f 
                       JOIN paiement p ON f.paiement_id = p.id 
                       WHERE f.utilisateur_id = ? 
                       ORDER BY f.created_at DESC");
$stmt->execute([$_SESSION['utilisateur_id']]);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les paiements récents
$stmt = $pdo->prepare("SELECT p.*, a.type as abonnement_type 
                       FROM paiement p 
                       LEFT JOIN abonnement a ON p.typeAbonnement = a.type 
                       WHERE p.utilisateur_id = ? 
                       ORDER BY p.created_at DESC 
                       LIMIT 5");
$stmt->execute([$_SESSION['utilisateur_id']]);
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les vérifications de l'utilisateur
error_log("Début de la récupération des vérifications pour l'utilisateur ID: " . $_SESSION['utilisateur_id']);

try {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM analyses_ia 
        WHERE utilisateur_id = :utilisateur_id 
        ORDER BY date_analyse DESC
    ");

    $stmt->execute(['utilisateur_id' => $_SESSION['utilisateur_id']]);
    $verifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Nombre de vérifications trouvées : " . count($verifications));
    
    // Log détaillé de chaque vérification
    foreach ($verifications as $index => $verif) {
        error_log("Vérification #" . ($index + 1) . " : " . print_r([
            'id' => $verif['id'],
            'date_analyse' => $verif['date_analyse'],
            'nombre_images' => $verif['nombre_images'],
            'resultats_length' => strlen($verif['resultats']),
            'utilisateur_id' => $verif['utilisateur_id']
        ], true));
        
        // Vérification du contenu JSON
        $resultats = json_decode($verif['resultats'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Erreur de décodage JSON pour la vérification #" . ($index + 1) . " : " . json_last_error_msg());
        } else {
            error_log("Contenu JSON valide pour la vérification #" . ($index + 1));
        }
    }
} catch (PDOException $e) {
    error_log("Erreur PDO lors de la récupération des vérifications : " . $e->getMessage());
    $verifications = [];
}

// Récupérer l'historique des abonnements
$stmt = $pdo->prepare("SELECT h.*, a.type as abonnement_type, a.prix as abonnement_prix 
                       FROM historique_abonnement h 
                       JOIN abonnement a ON h.abonnement_id = a.id 
                       WHERE h.utilisateur_id = ? 
                       ORDER BY h.dateDebut DESC");
$stmt->execute([$_SESSION['utilisateur_id']]);
$historiqueAbonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération de l'abonnement actif
    $stmt = $pdo->prepare("
        SELECT * FROM abonnements 
        WHERE utilisateur_id = :utilisateur_id 
        AND statut = 'actif' 
        AND date_fin > NOW()
        ORDER BY date_debut DESC 
        LIMIT 1
    ");
    
    $stmt->execute(['utilisateur_id' => $_SESSION['utilisateur_id']]);
    $abonnement = $stmt->fetch();
    
    // Récupération de l'historique des abonnements
    $stmt = $pdo->prepare("
        SELECT * FROM abonnements 
        WHERE utilisateur_id = :utilisateur_id 
        ORDER BY date_debut DESC
    ");
    
    $stmt->execute(['utilisateur_id' => $_SESSION['utilisateur_id']]);
    $historique = $stmt->fetchAll();
    
    // Récupération des informations d'abonnement avec les limites
    try {
        error_log("Tentative de récupération des informations d'abonnement pour l'utilisateur " . $_SESSION['utilisateur_id']);
        
        // Récupération du dernier paiement réussi et de l'abonnement associé
        $stmt = $pdo->prepare("
            SELECT p.*, a.type as type_abonnement, a.limitesVerifications, a.prix
            FROM paiement p
            JOIN abonnement a ON p.typeAbonnement = a.type
            WHERE p.utilisateur_id = :utilisateur_id 
            AND p.statut = 'réussi'
            ORDER BY p.created_at DESC
            LIMIT 1
        ");
        
        $stmt->execute(['utilisateur_id' => $_SESSION['utilisateur_id']]);
        $lastPayment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lastPayment) {
            error_log("Dernier paiement trouvé : " . print_r($lastPayment, true));
            
            // Calcul des dates de début et fin basées sur le paiement
            $date_debut = date('Y-m-d H:i:s', strtotime($lastPayment['created_at']));
            $date_fin = date('Y-m-d H:i:s', strtotime($lastPayment['created_at'] . ' +1 month'));
            
            // Récupération des vérifications utilisées depuis la date de début du nouvel abonnement
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as verifications_utilisees 
                FROM analyses_ia 
                WHERE utilisateur_id = :utilisateur_id 
                AND date_analyse >= :date_debut
            ");
            $stmt->execute([
                'utilisateur_id' => $_SESSION['utilisateur_id'],
                'date_debut' => $date_debut
            ]);
            $verifications_utilisees = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $abonnementInfo = [
                'type_abonnement' => $lastPayment['typeAbonnement'],
                'statut' => 'actif',
                'date_debut' => $date_debut,
                'date_fin' => $date_fin,
                'verifications_utilisees' => $verifications_utilisees['verifications_utilisees'],
                'limitesVerifications' => $lastPayment['limitesVerifications'],
                'prix' => $lastPayment['prix']
            ];
            
            error_log("Informations d'abonnement préparées : " . print_r($abonnementInfo, true));
        } else {
            error_log("Aucun paiement réussi trouvé pour l'utilisateur " . $_SESSION['utilisateur_id']);
            $abonnementInfo = null;
        }
    } catch (Exception $e) {
        error_log("Erreur lors de la récupération des informations d'abonnement : " . $e->getMessage());
        $abonnementInfo = null;
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "Une erreur est survenue lors de la récupération des données";
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - CheckMyKicks</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/compte.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="account-container">
        <div class="account-grid">
            <div class="account-sidebar">
                <ul class="account-menu">
                    <li><a href="#profile" class="active"><i class="fas fa-user"></i> Profil</a></li>
                    <li><a href="#abonnement"><i class="fas fa-credit-card"></i> Abonnement</a></li>
                    <li><a href="#factures"><i class="fas fa-file-invoice"></i> Factures</a></li>
                    <li><a href="#verifications"><i class="fas fa-check-circle"></i> Vérifications</a></li>
                    <li><a href="/ProjetFileRouge/Backend/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                </ul>
            </div>

            <div class="account-content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <section id="profile" class="account-section active">
                    <h2 class="section-title">Mon Profil</h2>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="profile-container">
                        <div class="profile-header">
                            <div class="profile-photo">
                                <?php if (isset($user['photo_profil']) && $user['photo_profil']): ?>
                                    <img src="/ProjetFileRouge/uploads/profiles/<?php echo htmlspecialchars($user['photo_profil']); ?>" 
                                         alt="Photo de profil" 
                                         id="profile-preview"
                                         class="profile-image">
                                <?php else: ?>
                                    <img src="/ProjetFileRouge/Frontend/images/default-profile.png" 
                                         alt="Photo de profil par défaut" 
                                         id="profile-preview"
                                         class="profile-image">
                                <?php endif; ?>
                                
                                <form action="/ProjetFileRouge/Backend/profile/update_photo.php" 
                                      method="POST" 
                                      enctype="multipart/form-data" 
                                      class="photo-form">
                                    <label for="photo_profil" class="btn-upload">
                                        <i class="fas fa-camera"></i>
                                        Changer la photo
                                    </label>
                                    <input type="file" 
                                           id="photo_profil" 
                                           name="photo_profil" 
                                           accept="image/*" 
                                           style="display: none;"
                                           onchange="previewImage(this)">
                                </form>
                            </div>
                    <div class="profile-info">
                                <h3><?php echo htmlspecialchars($user['nom']); ?></h3>
                                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                                <p class="profile-date">Membre depuis <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        </div>

                        <div class="profile-forms">
                            <!-- Formulaire de modification des informations de base -->
                            <form action="/ProjetFileRouge/Backend/profile/update_profile.php" 
                                  method="POST" 
                                  class="profile-form">
                                <h3>Informations personnelles</h3>
                                
                                <div class="form-group">
                                    <label for="nom">Nom</label>
                                    <input type="text" 
                                           id="nom" 
                                           name="nom" 
                                           value="<?php echo htmlspecialchars($user['nom']); ?>" 
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" 
                                           required>
                                </div>

                                <button type="submit" class="btn-save">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </form>

                            <!-- Formulaire de modification du mot de passe -->
                            <form action="/ProjetFileRouge/Backend/profile/update_password.php" 
                                  method="POST" 
                                  class="password-form">
                                <h3>Changer le mot de passe</h3>
                                
                                <div class="form-group">
                                    <label for="current_password">Mot de passe actuel</label>
                                    <input type="password" 
                                           id="current_password" 
                                           name="current_password" 
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="new_password">Nouveau mot de passe</label>
                                    <input type="password" 
                                           id="new_password" 
                                           name="new_password" 
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                                    <input type="password" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           required>
                                </div>

                                <button type="submit" class="btn-save">
                                    <i class="fas fa-key"></i> Changer le mot de passe
                                </button>
                            </form>

                            <!-- Formulaire de modification des informations de contact -->
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
                            $stmt->execute([$_SESSION['utilisateur_id']]);
                            $utilisateurs = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            
                            <form action="/ProjetFileRouge/Backend/profile/update_contact.php" 
                                  method="POST" 
                                  class="contact-form">
                                <h3>Informations de contact</h3>
                                
                                <div class="form-group">
                                    <label for="adresse">Adresse</label>
                                    <textarea id="adresse" 
                                              name="adresse" 
                                              rows="3"><?php echo isset($utilisateurs['adresse']) ? htmlspecialchars($utilisateurs['adresse']) : ''; ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="telephone">Téléphone</label>
                                    <input type="tel" 
                                           id="telephone" 
                                           name="telephone" 
                                           value="<?php echo isset($utilisateurs['telephone']) ? htmlspecialchars($utilisateurs['telephone']) : ''; ?>">
                                </div>

                                <button type="submit" class="btn-save">
                                    <i class="fas fa-address-book"></i> Mettre à jour les informations de contact
                                </button>
                            </form>
                        </div>
                    </div>
                </section>

                <section id="abonnement" class="account-section">
                    <h2 class="section-title" style="text-align:center; margin-bottom: 0.5em;">
                        <i>Mon Abonnement</i>
                    </h2>
                    <div class="subscription-card-modern" id="subscription-card">
                        <?php if ($abonnementInfo): ?>
                            <div class="subscription-header">
                                <span class="subscription-type">
                                    <i class="fas fa-gem"></i> <?php echo htmlspecialchars(ucfirst($abonnementInfo['type_abonnement'])); ?>
                                </span>
                                <span class="subscription-status-badge <?php echo $abonnementInfo['statut']; ?>">
                                    <?php echo htmlspecialchars(ucfirst($abonnementInfo['statut'])); ?>
                                </span>
                            </div>
                            <div class="subscription-details-table">
                                <div class="subscription-row">
                                    <span>Date de début :</span>
                                    <span><?php echo date('d/m/Y', strtotime($abonnementInfo['date_debut'])); ?></span>
                                </div>
                                <div class="subscription-row">
                                    <span>Date de fin :</span>
                                    <span><?php echo date('d/m/Y', strtotime($abonnementInfo['date_fin'])); ?></span>
                                </div>
                                <div class="subscription-row">
                                    <span>Prix :</span>
                                    <span><strong><?php echo number_format($abonnementInfo['prix'], 2, ',', ' '); ?> € / mois</strong></span>
                                </div>
                                <?php if (!empty($factures)):
                                    $facture = $factures[0]; // la plus récente
                                ?>
                                <div class="subscription-row">
                                    <span>Dernière facture :</span>
                                    <span>
                                        <a href="/ProjetFileRouge/public/factures/<?php echo htmlspecialchars($facture['numeroFacture']); ?>.pdf" 
                                           target="_blank" 
                                           class="facture-link">
                                            N°<?php echo htmlspecialchars($facture['numeroFacture']); ?>
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <div class="subscription-row">
                                    <span>Vérifications :</span>
                                    <span>
                                        <span class="verifications-count">
                                            <?php echo $abonnementInfo['verifications_utilisees']; ?> / <?php echo $abonnementInfo['limitesVerifications']; ?>
                                        </span>
                                        <?php if ($abonnementInfo['verifications_utilisees'] >= $abonnementInfo['limitesVerifications']): ?>
                                            <span class="limit-reached-badge">
                                                <i class="fas fa-exclamation-triangle"></i> Limite atteinte
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="progress-bar-modern">
                                <div class="progress-modern" style="width: <?php echo min(($abonnementInfo['verifications_utilisees'] / $abonnementInfo['limitesVerifications']) * 100, 100); ?>%"></div>
                                <span class="progress-label">
                                    <?php echo round(min(($abonnementInfo['verifications_utilisees'] / $abonnementInfo['limitesVerifications']) * 100, 100)); ?>%
                                </span>
                            </div>
                            <?php if (strtotime($abonnementInfo['date_fin']) - time() < 7 * 24 * 60 * 60): ?>
                                <div class="subscription-renewal-notice">
                                    <i class="fas fa-clock"></i>
                                    Votre abonnement expire bientôt. 
                                    <a href="/ProjetFileRouge/Frontend/HTML/paiement.php" class="renew-link">
                                        Renouveler maintenant
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-subscription">
                                <i class="fas fa-gem subscription-icon"></i>
                                <p>Vous n'avez pas d'abonnement actif.</p>
                                <a href="/ProjetFileRouge/Frontend/HTML/paiement.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Souscrire un abonnement
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <style>
                .subscription-card-modern {
                    background: #fff;
                    border-radius: 15px;
                    padding: 25px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    transition: all 0.3s ease;
                }

                .subscription-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 20px;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #f0f0f0;
                }

                .subscription-type {
                    font-size: 1.2em;
                    font-weight: 600;
                    color: #2c3e50;
                }

                .subscription-status-badge {
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-size: 0.9em;
                    font-weight: 500;
                }

                .subscription-status-badge.actif {
                    background: #e3fcef;
                    color: #00a854;
                }

                .subscription-status-badge.expiré {
                    background: #fff1f0;
                    color: #f5222d;
                }

                .subscription-details-table {
                    margin: 20px 0;
                }

                .subscription-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid #f0f0f0;
                }

                .subscription-row:last-child {
                    border-bottom: none;
                }

                .subscription-row span:first-child {
                    color: #666;
                }

                .subscription-row span:last-child {
                    font-weight: 500;
                }

                .progress-bar-modern {
                    background: #f5f5f5;
                    border-radius: 10px;
                    height: 8px;
                    margin: 15px 0;
                    position: relative;
                    overflow: hidden;
                }

                .progress-modern {
                    background: linear-gradient(90deg, #4CAF50, #8BC34A);
                    height: 100%;
                    border-radius: 10px;
                    transition: width 0.3s ease;
                }

                .progress-label {
                    position: absolute;
                    right: 0;
                    top: -20px;
                    font-size: 0.9em;
                    color: #666;
                }

                .subscription-renewal-notice {
                    margin-top: 20px;
                    padding: 12px;
                    background: #fffbe6;
                    border: 1px solid #ffe58f;
                    border-radius: 8px;
                    color: #d48806;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }

                .renew-link {
                    color: #1890ff;
                    text-decoration: none;
                    font-weight: 500;
                    margin-left: 5px;
                }

                .renew-link:hover {
                    text-decoration: underline;
                }

                .facture-link {
                    color: #1890ff;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                }

                .facture-link:hover {
                    text-decoration: underline;
                }

                .verifications-count {
                    font-weight: 500;
                    color: #2c3e50;
                }

                .limit-reached-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                    padding: 4px 8px;
                    background: #fff1f0;
                    color: #f5222d;
                    border-radius: 4px;
                    font-size: 0.85em;
                    margin-left: 10px;
                }

                .no-subscription {
                    text-align: center;
                    padding: 30px 20px;
                }

                .subscription-icon {
                    font-size: 3em;
                    color: #d9d9d9;
                    margin-bottom: 15px;
                }

                .no-subscription p {
                    color: #666;
                    margin-bottom: 20px;
                }

                .btn-primary {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 12px 24px;
                    background: #1890ff;
                    color: white;
                    border-radius: 6px;
                    text-decoration: none;
                    font-weight: 500;
                    transition: all 0.3s ease;
                }

                .btn-primary:hover {
                    background: #40a9ff;
                    transform: translateY(-2px);
                }
                </style>

                <script>
                // Fonction pour actualiser l'abonnement
                function refreshSubscription() {
                    fetch(window.location.href)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newSubscriptionCard = doc.getElementById('subscription-card');
                            const currentSubscriptionCard = document.getElementById('subscription-card');
                            
                            if (newSubscriptionCard && currentSubscriptionCard) {
                                currentSubscriptionCard.innerHTML = newSubscriptionCard.innerHTML;
                                
                                // Ajouter une animation de transition
                                currentSubscriptionCard.style.opacity = '0';
                                setTimeout(() => {
                                    currentSubscriptionCard.style.opacity = '1';
                                }, 100);
                            }
                        })
                        .catch(error => console.error('Erreur lors de l\'actualisation:', error));
                }

                // Actualiser l'abonnement toutes les 30 secondes si on est sur la section abonnement
                function startSubscriptionRefresh() {
                    const subscriptionSection = document.getElementById('abonnement');
                    if (subscriptionSection && subscriptionSection.classList.contains('active')) {
                        refreshSubscription();
                    }
                }

                // Démarrer l'actualisation périodique
                setInterval(startSubscriptionRefresh, 30000);

                // Actualiser l'abonnement lors du changement de section
                document.querySelectorAll('.account-menu a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        if (this.getAttribute('href') === '#abonnement') {
                            refreshSubscription();
                        }
                    });
                });

                // Actualiser l'abonnement lors du retour sur la page
                window.addEventListener('focus', function() {
                    if (document.getElementById('abonnement').classList.contains('active')) {
                        refreshSubscription();
                    }
                });
                </script>

                <section id="factures" class="account-section">
                    <h2 class="section-title">Mes Factures</h2>
                    <div class="invoice-list">
                        <?php if (empty($factures) && isset($_SESSION['last_facture_pdf'])): ?>
                            <a href="<?php echo htmlspecialchars($_SESSION['last_facture_pdf']); ?>" class="btn-download" target="_blank">
                                Télécharger la dernière facture PDF
                            </a>
                        <?php elseif (empty($factures)): ?>
                            <p>Aucune facture disponible.</p>
                        <?php else: ?>
                            <?php foreach ($factures as $facture): 
                                $details = json_decode($facture['details'], true);
                            ?>
                                <div class="invoice-item">
                                    <div class="invoice-info">
                                        <strong>Facture <?php echo htmlspecialchars($facture['numeroFacture']); ?></strong>
                                        <span class="invoice-date">
                                            <?php echo date('d/m/Y', strtotime($facture['created_at'])); ?> - 
                                            Abonnement <?php echo htmlspecialchars($facture['typeAbonnement']); ?>
                                        </span>
                                    </div>
                                    <div class="invoice-amount">
                                        <?php echo number_format($facture['montantTotal'], 2, ',', ' '); ?> €
                                    </div>
                                    <div class="invoice-actions">
                                        <a href="/ProjetFileRouge/public/factures/<?php echo htmlspecialchars($facture['numeroFacture']); ?>.pdf" 
                                           class="btn-download" 
                                           target="_blank"
                                           title="Télécharger la facture">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="/ProjetFileRouge/public/factures/<?php echo htmlspecialchars($facture['numeroFacture']); ?>.pdf" 
                                           class="btn-view" 
                                           target="_blank"
                                           title="Voir la facture">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <section id="verifications" class="account-section">
                    <h2 class="section-title">Mes Vérifications</h2>
                    <div class="verification-list">
                        <?php if (empty($verifications)): ?>
                            <div class="no-verifications">
                                <i class="fas fa-search"></i>
                                <p>Vous n'avez pas encore effectué de vérifications.</p>
                                <a href="/ProjetFileRouge/Frontend/HTML/ia.php" class="btn-primary">
                                    <i class="fas fa-robot"></i> Faire une vérification
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="verifications-grid">
                                <?php foreach ($verifications as $analyse): ?>
                                    <?php
                                        // Debug temporaire
                                        // print_r($analyse);

                                        $dateAnalyse = !empty($analyse['date_analyse']) ? $analyse['date_analyse'] : null;
                                        $dateAffichee = $dateAnalyse ? date('d/m/Y H:i', strtotime($dateAnalyse)) : 'Date inconnue';
                                        $nombreImages = isset($analyse['nombre_images']) ? (int)$analyse['nombre_images'] : 0;
                                        $resultats = !empty($analyse['resultats']) ? json_decode($analyse['resultats'], true) : null;
                                        $statut = 'En cours';
                                        if (is_array($resultats) && isset($resultats['predictions'][0]['statut'])) {
                                            $statut = $resultats['predictions'][0]['statut'];
                                        }
                                        $score = 0;
                                        if (is_array($resultats) && isset($resultats['predictions'][0]['score'])) {
                                            $score = is_numeric($resultats['predictions'][0]['score']) ? $resultats['predictions'][0]['score'] * 100 : 0;
                                        }
                                    ?>
                                    <div class="verification-card">
                                        <div class="verification-header">
                                            <span class="verification-date">
                                                <?php echo htmlspecialchars($dateAffichee); ?>
                                            </span>
                                            <span class="verification-status">
                                                <?php echo htmlspecialchars($statut); ?>
                                            </span>
                                        </div>
                                        <div class="verification-body">
                                            <div class="verification-info">
                                                <span class="verification-count">
                                                    <i class="fas fa-images"></i> <?php echo htmlspecialchars((string)$nombreImages); ?> images
                                                </span>
                                                <span class="verification-score">
                                                    <?php echo htmlspecialchars(number_format($score, 1)); ?>%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="verification-footer">
                                            <a href="voir_analyse.php?id=<?php echo htmlspecialchars($analyse['id']); ?>" class="btn-details">
                                                <i class="fas fa-eye"></i> Voir les détails
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="verifications-footer">
                                <a href="/ProjetFileRouge/Frontend/HTML/ia.php" class="btn-primary">
                                    <i class="fas fa-robot"></i> Nouvelle vérification
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuration des validateurs
        const validators = {
            email: {
                pattern: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/,
                message: 'Veuillez entrer une adresse email valide'
            },
            telephone: {
                pattern: /^(\+33|0)[1-9](\d{2}){4}$/,
                message: 'Veuillez entrer un numéro de téléphone valide (ex: 0612345678)'
            },
            password: {
                pattern: /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/,
                message: 'Le mot de passe doit contenir au moins 8 caractères, une lettre, un chiffre et un caractère spécial'
            }
        };

        // Fonction de validation générique
        function validateField(input, validator) {
            const value = input.value.trim();
            const errorElement = input.nextElementSibling?.classList.contains('error-message') 
                ? input.nextElementSibling 
                : document.createElement('div');
            
            if (!errorElement.classList.contains('error-message')) {
                errorElement.className = 'error-message';
                input.parentNode.insertBefore(errorElement, input.nextSibling);
            }

            if (!value) {
                showError(input, errorElement, 'Ce champ est requis');
                return false;
            }

            if (validator && !validator.pattern.test(value)) {
                showError(input, errorElement, validator.message);
                return false;
            }

            showSuccess(input, errorElement);
            return true;
        }

        // Affichage des erreurs
        function showError(input, errorElement, message) {
            input.classList.add('error');
            input.classList.remove('success');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            errorElement.style.color = 'var(--danger-color)';
            errorElement.style.fontSize = '12px';
            errorElement.style.marginTop = '5px';
            errorElement.style.fontWeight = '500';
        }

        // Affichage du succès
        function showSuccess(input, errorElement) {
            input.classList.remove('error');
            input.classList.add('success');
            errorElement.style.display = 'none';
        }

        // Validation en temps réel
        function setupRealTimeValidation(form) {
            const inputs = form.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    const validator = validators[input.name];
                    if (validator) {
                        validateField(input, validator);
                    }
                });

                input.addEventListener('blur', function() {
                    const validator = validators[input.name];
                    if (validator) {
                        validateField(input, validator);
                    }
                });
            });
        }

        // Validation des formulaires
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            setupRealTimeValidation(form);

            form.addEventListener('submit', function(e) {
                let isValid = true;
                const inputs = form.querySelectorAll('input, textarea');

                inputs.forEach(input => {
                    const validator = validators[input.name];
                    if (validator) {
                        if (!validateField(input, validator)) {
                            isValid = false;
                        }
                    }
                });

                // Validation spéciale pour le mot de passe
                if (form.classList.contains('password-form')) {
                    const newPassword = form.querySelector('#new_password');
                    const confirmPassword = form.querySelector('#confirm_password');
                    
                    if (newPassword.value !== confirmPassword.value) {
                        showError(confirmPassword, 
                            confirmPassword.nextElementSibling, 
                            'Les mots de passe ne correspondent pas');
                        isValid = false;
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                    // Animation de secousse pour le formulaire
                    form.classList.add('shake');
                    setTimeout(() => form.classList.remove('shake'), 500);
                }
            });
        });

        // Prévisualisation de l'image
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                const preview = document.getElementById('profile-preview');
                const maxSize = 5 * 1024 * 1024; // 5MB
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

                if (!allowedTypes.includes(file.type)) {
                    alert('Format de fichier non supporté. Utilisez JPG, PNG ou GIF.');
                    input.value = '';
                    return;
                }

                if (file.size > maxSize) {
                    alert('L\'image ne doit pas dépasser 5MB.');
                    input.value = '';
                    return;
                }

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.opacity = '0';
                    setTimeout(() => {
                        preview.style.opacity = '1';
                    }, 100);
                }

                reader.readAsDataURL(file);
                
                // Soumettre automatiquement après la sélection
                setTimeout(() => {
                    input.form.submit();
                }, 500);
            }
        }

        // Gestion des sections
        const sections = document.querySelectorAll('.account-section');
        const menuLinks = document.querySelectorAll('.account-menu a');
        
        function showSection(sectionId) {
            sections.forEach(section => {
                section.style.display = 'none';
                section.classList.remove('active');
                if (section.id === sectionId) {
                    section.style.display = 'block';
                    setTimeout(() => {
                    section.classList.add('active');
                    }, 50);
                }
            });

            menuLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + sectionId) {
                    link.classList.add('active');
                }
            });

            history.pushState(null, '', '#' + sectionId);
        }

        menuLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    const sectionId = this.getAttribute('href').substring(1);
                    showSection(sectionId);
                }
            });
        });

        // Afficher la section correspondant au hash de l'URL
        const hash = window.location.hash.substring(1) || 'profile';
        showSection(hash);

        // Gérer les changements de hash dans l'URL
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.substring(1) || 'profile';
            showSection(hash);
        });

        // Ajout des styles CSS pour les animations et validations
        const style = document.createElement('style');
        style.textContent = `
            .error {
                border-color: var(--danger-color) !important;
                background-color: rgba(255, 0, 0, 0.02) !important;
            }

            .success {
                border-color: var(--success-color) !important;
                background-color: rgba(0, 0, 0, 0.02) !important;
            }

            .error-message {
                color: var(--danger-color);
                font-size: 12px;
                margin-top: 5px;
                font-weight: 500;
                display: none;
            }

            .shake {
                animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
            }

            @keyframes shake {
                10%, 90% { transform: translate3d(-1px, 0, 0); }
                20%, 80% { transform: translate3d(2px, 0, 0); }
                30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
                40%, 60% { transform: translate3d(4px, 0, 0); }
            }

            .profile-image {
                transition: opacity 0.3s ease;
            }

            input:focus, textarea:focus {
                outline: none;
                box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
            }

            .btn-save:disabled {
                opacity: 0.7;
                cursor: not-allowed;
            }

            .form-group {
                position: relative;
            }

            .form-group input:focus + label,
            .form-group textarea:focus + label {
                color: var(--primary-color);
            }
        `;
        document.head.appendChild(style);
    });
    </script>
</body>
</html> 