<?php
session_start();

// Chargement de l'autoloader de Composer
$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die('Le fichier autoload.php n\'existe pas. Veuillez exécuter "composer install" dans le répertoire du projet.');
}
require_once $autoloadPath;

// Chargement direct de TCPDF si nécessaire
$tcpdfPath = __DIR__ . '/../../vendor/tecnickcom/tcpdf/TCPDF-6.6.2/tcpdf.php';
if (!class_exists('TCPDF') && file_exists($tcpdfPath)) {
    require_once $tcpdfPath;
}

if (!class_exists('TCPDF')) {
    die('La bibliothèque TCPDF n\'est pas correctement installée. Veuillez exécuter "composer require tecnickcom/tcpdf" dans le répertoire du projet.');
}

require_once __DIR__ . '/../../config/database.php';

// Classe personnalisée pour le PDF
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'CheckMyKicks - Facture', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Vérification de la session
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour effectuer un paiement.";
    header('Location: /ProjetFileRouge/Frontend/HTML/login.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = null;
    $transactionStarted = false;
    
    try {
        // Récupération et nettoyage des données
        $nom = htmlspecialchars($_POST['nom'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $adresse = htmlspecialchars($_POST['adresse'] ?? '');
        $telephone = htmlspecialchars($_POST['telephone'] ?? '');
        $typeAbonnement = htmlspecialchars($_POST['type_abonnement'] ?? '');
        $montant = floatval($_POST['montant'] ?? 0);

        // Validation des données
        if (empty($nom) || empty($email) || empty($adresse) || empty($telephone) || empty($typeAbonnement) || $montant <= 0) {
            throw new Exception("Tous les champs sont obligatoires.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("L'adresse email n'est pas valide.");
        }

        // Connexion à la base de données
        $pdo = getConnection();
        
        // Début de la transaction
        $pdo->beginTransaction();
        $transactionStarted = true;

        // Insertion du paiement
        $stmt = $pdo->prepare("INSERT INTO paiement (utilisateur_id, typeAbonnement, montant, statut, created_at) VALUES (?, ?, ?, 'réussi', NOW())");
        $stmt->execute([$_SESSION['utilisateur_id'], $typeAbonnement, $montant]);
        $paiementId = $pdo->lastInsertId();

        // Génération du numéro de facture
        $numeroFacture = 'FACT-' . date('Ymd') . '-' . str_pad($paiementId, 4, '0', STR_PAD_LEFT);

        // Création du dossier factures dans le dossier public
        $facturesDir = __DIR__ . '/../../public/factures';
        if (!file_exists($facturesDir)) {
            if (!mkdir($facturesDir, 0777, true)) {
                error_log("Impossible de créer le dossier factures : " . $facturesDir);
                throw new Exception("Impossible de créer le dossier factures.");
            }
            chmod($facturesDir, 0777); // Donne tous les droits au dossier
        }

        // Vérification des permissions du dossier
        if (!is_writable($facturesDir)) {
            error_log("Le dossier factures n'est pas accessible en écriture : " . $facturesDir);
            throw new Exception("Le dossier factures n'est pas accessible en écriture.");
        }

        // Création du PDF avec plus de logs
        try {
            error_log("=== DÉBUT GÉNÉRATION PDF ===");
            error_log("Numéro facture : " . $numeroFacture);
            error_log("Dossier factures : " . $facturesDir);
            
            if (!class_exists('TCPDF')) {
                error_log("ERREUR : TCPDF n'est pas chargé !");
                throw new Exception("TCPDF n'est pas chargé");
            }
            
            error_log("Création de l'instance PDF...");
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            error_log("Instance PDF créée avec succès");
            
            $pdf->SetCreator('CheckMyKicks');
            $pdf->SetAuthor('CheckMyKicks');
            $pdf->SetTitle('Facture ' . $numeroFacture);
            error_log("Métadonnées PDF définies");

            // Définir les constantes TCPDF si elles ne sont pas définies
            if (!defined('PDF_PAGE_ORIENTATION')) {
                define('PDF_PAGE_ORIENTATION', 'P');
            }
            if (!defined('PDF_UNIT')) {
                define('PDF_UNIT', 'mm');
            }
            if (!defined('PDF_PAGE_FORMAT')) {
                define('PDF_PAGE_FORMAT', 'A4');
            }
            error_log("Constantes TCPDF définies");

            error_log("Ajout de la page...");
            $pdf->AddPage();
            error_log("Page ajoutée");
            
            error_log("Configuration de la police...");
            $pdf->SetFont('helvetica', '', 12);
            error_log("Police configurée");

            // Contenu de la facture
            error_log("Préparation du contenu HTML...");
            $html = '<h1>Facture ' . $numeroFacture . '</h1>';
            $html .= '<p>Date: ' . date('d/m/Y') . '</p>';
            $html .= '<p>Client: ' . $nom . '</p>';
            $html .= '<p>Email: ' . $email . '</p>';
            $html .= '<p>Adresse: ' . $adresse . '</p>';
            $html .= '<p>Téléphone: ' . $telephone . '</p>';
            $html .= '<h2>Détails de l\'abonnement</h2>';
            $html .= '<p>Type: ' . $typeAbonnement . '</p>';
            $html .= '<p>Montant: ' . number_format($montant, 2) . ' €</p>';
            error_log("Contenu HTML préparé");

            error_log("Écriture du HTML dans le PDF...");
            $pdf->writeHTML($html, true, false, true, false, '');
            error_log("HTML écrit dans le PDF");

            // Sauvegarde du PDF avec vérification
            $pdfPath = $facturesDir . '/' . $numeroFacture . '.pdf';
            error_log("Tentative de sauvegarde du PDF à : " . $pdfPath);
            
            try {
                error_log("Appel de Output()...");
                $result = $pdf->Output($pdfPath, 'F');
                error_log("Output() terminé, résultat : " . ($result === false ? 'false' : 'true'));
            } catch (Exception $outputError) {
                error_log("ERREUR lors de Output() : " . $outputError->getMessage());
                error_log("Trace : " . $outputError->getTraceAsString());
                throw $outputError;
            }
            
            // Vérification que le fichier a bien été créé
            if (!file_exists($pdfPath)) {
                error_log("ERREUR : Le fichier PDF n'a pas été créé : " . $pdfPath);
                throw new Exception("Le fichier PDF n'a pas été créé");
            }
            
            error_log("Vérification de la taille du fichier...");
            $fileSize = filesize($pdfPath);
            error_log("Taille du fichier : " . $fileSize . " octets");
            
            if ($fileSize === 0) {
                error_log("ERREUR : Le fichier PDF est vide");
                throw new Exception("Le fichier PDF est vide");
            }
            
            error_log("=== FIN GÉNÉRATION PDF SUCCÈS ===");
            
        } catch (Exception $e) {
            error_log("=== ERREUR GÉNÉRATION PDF ===");
            error_log("Message : " . $e->getMessage());
            error_log("Trace : " . $e->getTraceAsString());
            error_log("=== FIN ERREUR ===");
            throw new Exception("Erreur lors de la génération du PDF : " . $e->getMessage());
        }

        // Insertion de la facture dans la base de données
        $stmt = $pdo->prepare("INSERT INTO facture (utilisateur_id, paiement_id, numeroFacture, montantTotal, details, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $details = json_encode([
            'pdf_path' => '/ProjetFileRouge/public/factures/' . $numeroFacture . '.pdf',
            'client_info' => [
                'nom' => $nom,
                'email' => $email,
                'adresse' => $adresse,
                'telephone' => $telephone
            ]
        ]);
        $stmt->execute([$_SESSION['utilisateur_id'], $paiementId, $numeroFacture, $montant, $details]);

        // Mise à jour de l'abonnement
        $stmt = $pdo->prepare("INSERT INTO abonnements (utilisateur_id, type_abonnement, prix, date_debut, date_fin, statut, numero_facture) VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), 'actif', ?)");
        $stmt->execute([$_SESSION['utilisateur_id'], $typeAbonnement, $montant, $numeroFacture]);

        // Validation de la transaction
        if ($transactionStarted) {
            $pdo->commit();
            $transactionStarted = false;
        }

        // Stockage du chemin du PDF en session
        $_SESSION['last_facture_pdf'] = '/ProjetFileRouge/public/factures/' . $numeroFacture . '.pdf';
        $_SESSION['success'] = "Paiement effectué avec succès. Votre facture est disponible dans votre espace client.";

        // Redirection
        header('Location: /ProjetFileRouge/Frontend/HTML/compte.php#factures');
        exit;

    } catch (Exception $e) {
        // Annulation de la transaction en cas d'erreur
        if ($pdo !== null && $transactionStarted) {
            try {
                $pdo->rollBack();
            } catch (PDOException $rollbackError) {
                // Log l'erreur de rollback mais ne pas l'afficher à l'utilisateur
                error_log("Erreur lors du rollback: " . $rollbackError->getMessage());
            }
        }
        
        $_SESSION['error'] = "Erreur lors du traitement du paiement : " . $e->getMessage();
        header('Location: /ProjetFileRouge/Frontend/HTML/paiement.php');
        exit;
    }
} else {
    $_SESSION['error'] = "Méthode de requête invalide.";
    header('Location: /ProjetFileRouge/Frontend/HTML/paiement.php');
    exit;
}

file_put_contents(__DIR__.'/debug_post.txt', print_r($_POST, true));
?> 