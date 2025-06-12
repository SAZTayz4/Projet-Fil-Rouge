<?php
session_start();
require_once '../../Backend/config/database.php';

// Vérification de l'authentification
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: login.php');
    exit;
}

// Debug session (uniquement en log)
error_log("Session IA: " . print_r($_SESSION, true));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CheckMyKicks - Analyse IA</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../../Backend/IA-Check/ia-check.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
     <header class="header">
    <nav class="nav-container">
        <div class="nav-left">
        <a href="/ProjetFileRouge/Frontend/HTML/ia-check.php" class="nav-link">IA-CMK</a>
            <a href="/ProjetFileRouge/Frontend/HTML/drops.php" class="nav-link">Prochain Drop</a>
            <a href="/ProjetFileRouge/templates/blog.php" class="nav-link">Blog</a>
        </div>

        <div class="nav-center">
            <a href="/ProjetFileRouge/Frontend/HTML/home.php" class="logo">CheckMyKicks</a>
        </div>

        <div class="nav-right">
            <div class="nav-icons">
                                    
                    <a href="/ProjetFileRouge/Frontend/HTML/compte.php" class="icon-link">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="/ProjetFileRouge/Backend/auth/logout.php" class="nav-link">Déconnexion</a>
                    <a href="/ProjetFileRouge/Frontend/HTML/langues.html" class="nav-link">Langues</a>
                    <a href="/ProjetFileRouge/Frontend/HTML/spotcheck.php" class="nav-link">SpotCheck</a>

                            </div>
        </div>
    </nav>
</header>

<div class="announcement-bar">
    <p>Authentifiez vos sneakers dès maintenant - Service premium disponible 24/7</p>
</div>

<style>

</style> 
    <div class="ia-check-container">
        <div class="upload-section">
            <h2>Analyse IA de Sneakers</h2>
            <p class="subtitle">Téléchargez jusqu'à 6 photos de vos sneakers pour une analyse approfondie</p>
            
            <div class="sneaker-upload-grid" id="sneakerUploadGrid">
                <div class="upload-slot" data-label="Apparence">
                    <label>Apparence</label>
                    <input type="file" accept="image/*" style="display:none;">
                    <div class="slot-img"><i class="fas fa-shoe-prints"></i></div>
                </div>
                <div class="upload-slot" data-label="Size Tag Gauche">
                    <label>Size Tag Gauche</label>
                    <input type="file" accept="image/*" style="display:none;">
                    <div class="slot-img"><i class="fas fa-barcode"></i></div>
                </div>
                <div class="upload-slot" data-label="Size Tag Droite">
                    <label>Size Tag Droite</label>
                    <input type="file" accept="image/*" style="display:none;">
                    <div class="slot-img"><i class="fas fa-barcode"></i></div>
                </div>
                <div class="upload-slot" data-label="Couture Int. Talon Gauche">
                    <label>Couture Int. Talon Gauche</label>
                    <input type="file" accept="image/*" style="display:none;">
                    <div class="slot-img"><i class="fas fa-grip-lines-vertical"></i></div>
                </div>
                <div class="upload-slot" data-label="Couture Int. Talon Droite">
                    <label>Couture Int. Talon Droite</label>
                    <input type="file" accept="image/*" style="display:none;">
                    <div class="slot-img"><i class="fas fa-grip-lines-vertical"></i></div>
                </div>
                <div class="upload-slot" data-label="Box Label">
                    <label>Box Label</label>
                    <input type="file" accept="image/*" style="display:none;">
                    <div class="slot-img"><i class="fas fa-box"></i></div>
                </div>
                <div class="upload-slot" data-label="Box Code">
                    <label>Box Code</label>
                    <input type="file" accept="image/*" style="display:none;">
                    <div class="slot-img"><i class="fas fa-barcode"></i></div>
                </div>
                <div class="upload-slot add-slot">
                    <label>+ (Autre)</label>
                    <button class="add-btn" type="button">+</button>
                </div>
            </div>
            <div class="no-box-row">
                <input type="checkbox" id="no-box" />
                <label for="no-box">NO BOX</label>
            </div>

            <button class="analyze-btn" id="analyzeBtn">
                <i class="fas fa-robot"></i> Analyser les images
            </button>
        </div>

        <div class="results-section" id="resultsSection">
            <h3>Résultats de l'analyse</h3>
            <div id="resultsContent">
                <div class="result-slot valid">
                    <span>Apparence</span>
                    <span class="heatmap-dot"></span>
                    <span class="result-msg">OK</span>
                </div>
                <div class="result-slot invalid">
                    <span>Box Code</span>
                    <span class="heatmap-dot"></span>
                    <span class="result-msg">Problème détecté</span>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>Notre Concept</h3>
            <p>La première plateforme d'authentification de sneakers par la communauté.</p>
        </div>
        <div class="footer-section">
            <h3>Contact</h3>
            <p>Du lundi au jeudi</p>
            <p>10h - 19h</p>
            <p>contact@checkmykicks.com</p>
        </div>
        <div class="footer-section">
            <h3>Liens Utiles</h3>
            <p><a href="/ProjetFileRouge/Frontend/HTML/faq.php">FAQ</a></p>
            <p><a href="/ProjetFileRouge/Frontend/HTML/mentions-legales.php">Mentions légales</a></p>
            <p><a href="/ProjetFileRouge/Frontend/HTML/confidentialite.php">Politique de confidentialité</a></p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 CheckMyKicks. Tous droits réservés.</p>
    </div>
</footer> 
    <script src="../JS/ia.js"></script>
</body>
</html> 