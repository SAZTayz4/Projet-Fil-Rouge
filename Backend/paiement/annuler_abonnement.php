<?php
session_start();
require_once '../../config/database.php';

// Vérification de la connexion
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: /ProjetFileRouge/Backend/auth/login.php');
    exit;
}

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: /ProjetFileRouge/Frontend/HTML/compte.php');
    exit;
}

// Vérification de l'ID de l'abonnement
if (!isset($_POST['abonnement_id'])) {
    $_SESSION['error'] = "ID d'abonnement manquant.";
    header('Location: /ProjetFileRouge/Frontend/HTML/compte.php');
    exit;
}

// Fonction de logging personnalisée
function logAbonnement($message, $type = 'INFO') {
    $logDir = __DIR__ . '/../../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logFile = $logDir . '/abonnements.log';
    $date = date('Y-m-d H:i:s');
    $logMessage = "[$date][$type] $message" . PHP_EOL;
    
    // Rotation des logs si le fichier dépasse 5MB
    if (file_exists($logFile) && filesize($logFile) > 5 * 1024 * 1024) {
        $backupFile = $logDir . '/abonnements_' . date('Y-m-d_H-i-s') . '.log';
        rename($logFile, $backupFile);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

try {
    $pdo = getConnection();
    
    // Log de début de traitement
    logAbonnement("Début de l'annulation d'abonnement - ID: " . $_POST['abonnement_id'] . " - Utilisateur: " . $_SESSION['utilisateur_id']);
    
    // Vérification que l'abonnement appartient bien à l'utilisateur
    $stmt = $pdo->prepare("
        SELECT * FROM abonnements 
        WHERE id = :abonnement_id 
        AND utilisateur_id = :utilisateur_id 
        AND statut = 'actif'
    ");
    
    $stmt->execute([
        'abonnement_id' => $_POST['abonnement_id'],
        'utilisateur_id' => $_SESSION['utilisateur_id']
    ]);
    
    $abonnement = $stmt->fetch();
    
    if (!$abonnement) {
        logAbonnement("Abonnement non trouvé - ID: " . $_POST['abonnement_id'] . " - Utilisateur: " . $_SESSION['utilisateur_id'], 'ERROR');
        $_SESSION['error'] = "Abonnement non trouvé ou déjà annulé.";
        header('Location: /ProjetFileRouge/Frontend/HTML/compte.php');
        exit;
    }
    
    // Log des détails de l'abonnement
    logAbonnement("Détails de l'abonnement trouvé - Type: " . $abonnement['type_abonnement'] . " - Date fin: " . $abonnement['date_fin']);
    
    // Début de la transaction
    $pdo->beginTransaction();
    
    try {
        // Mise à jour du statut de l'abonnement
        $stmt = $pdo->prepare("
            UPDATE abonnements 
            SET statut = 'annulé', 
                date_fin = CASE 
                    WHEN date_fin > NOW() THEN date_fin 
                    ELSE NOW() 
                END
            WHERE id = :abonnement_id
        ");
        
        $stmt->execute(['abonnement_id' => $_POST['abonnement_id']]);
        
        // Vérification que la mise à jour a bien été effectuée
        if ($stmt->rowCount() === 0) {
            throw new Exception("Aucune ligne mise à jour lors de l'annulation de l'abonnement");
        }
        
        // Ajout d'une entrée dans l'historique des abonnements
        $stmt = $pdo->prepare("
            INSERT INTO historique_abonnement 
            (utilisateur_id, abonnement_id, dateDebut, dateFin, statut) 
            VALUES 
            (:utilisateur_id, :abonnement_id, :dateDebut, :dateFin, 'annulé')
        ");
        
        $stmt->execute([
            'utilisateur_id' => $_SESSION['utilisateur_id'],
            'abonnement_id' => $_POST['abonnement_id'],
            'dateDebut' => $abonnement['date_debut'],
            'dateFin' => $abonnement['date_fin']
        ]);
        
        // Validation de la transaction
        $pdo->commit();
        
        logAbonnement("Annulation d'abonnement réussie - ID: " . $_POST['abonnement_id'], 'SUCCESS');
        $_SESSION['success'] = "Votre abonnement a été annulé avec succès. Il restera actif jusqu'à la fin de la période payée.";
        
    } catch (Exception $e) {
        // Annulation de la transaction en cas d'erreur
        $pdo->rollBack();
        logAbonnement("Erreur lors de l'annulation de l'abonnement - " . $e->getMessage(), 'ERROR');
        throw $e;
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Une erreur est survenue lors de la suppression de l'abonnement. Veuillez réessayer ou contacter le support.<br><small>" . htmlspecialchars($e->getMessage()) . "</small>";
    // Affichage pour debug
    echo "<pre style='color:red;background:#fff;padding:20px;border:2px solid red'>";
    echo "Erreur PHP : " . htmlspecialchars($e->getMessage());
    echo "\n\nStack trace :\n" . htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
    exit;
}

header('Location: /ProjetFileRouge/Frontend/HTML/compte.php');
exit; 