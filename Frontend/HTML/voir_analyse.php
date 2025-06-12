<?php
session_start();
require_once '../../Backend/config/database.php';

if (!isset($_SESSION['utilisateur_id']) || !isset($_GET['id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['utilisateur_id'];
$id = (int)$_GET['id'];

try {
    $pdo = getConnection();
    
    $sql = "SELECT * FROM analyses_ia WHERE id = ? AND utilisateur_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $userId]);
    $analyse = $stmt->fetch();

    if (!$analyse) {
        throw new Exception("Analyse introuvable.");
    }

    $resultats = json_decode($analyse['resultats'], true);
    
    // Calcul du statut global
    $statut = 'En cours';
    $scoreMoyen = 0;
    if (isset($resultats['predictions']) && !empty($resultats['predictions'])) {
        $scores = array_map(function($pred) { return $pred['score']; }, $resultats['predictions']);
        $scoreMoyen = array_sum($scores) / count($scores);
        $statut = $scoreMoyen >= 0.8 ? 'Légitime' : ($scoreMoyen <= 0.2 ? 'Fake' : 'Douteux');
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: compte.php#verifications');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de la vérification - CheckMyKicks</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/voir_analyse.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="analysis-container">
        <div class="analysis-header">
            <div class="analysis-title">
                <h1>Vérification #<?php echo $analyse['id']; ?></h1>
                <div class="analysis-status <?php echo strtolower($statut); ?>">
                    <?php echo $statut; ?>
                </div>
            </div>
            
            <div class="analysis-details">
                <div class="analysis-card">
                    <h3>Informations générales</h3>
                    <div class="analysis-info">
                        <p><strong>Date de l'analyse :</strong> <?php echo date('d/m/Y H:i', strtotime($analyse['date_analyse'])); ?></p>
                        <p><strong>Nombre d'images :</strong> <?php echo $analyse['nombre_images']; ?></p>
                        <p><strong>Score moyen :</strong> <?php echo number_format($scoreMoyen * 100, 1); ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($resultats['predictions']) && !empty($resultats['predictions'])): ?>
            <h2>Détails des analyses</h2>
            <div class="predictions-grid">
                <?php foreach ($resultats['predictions'] as $index => $prediction): ?>
                    <div class="prediction-card">
                        <img src="<?php echo htmlspecialchars($prediction['original_image']); ?>" 
                             alt="Image analysée" 
                             class="prediction-image">
                        
                        <?php if (isset($prediction['heatmap'])): ?>
                            <div class="heatmap-container">
                                <img src="<?php echo htmlspecialchars($prediction['heatmap']); ?>" 
                                     alt="Heatmap" 
                                     class="heatmap-overlay">
                            </div>
                        <?php endif; ?>
                        
                        <div class="prediction-details">
                            <div class="prediction-score">
                                Score : <?php echo number_format($prediction['score'] * 100, 1); ?>%
                            </div>
                            
                            <?php if (isset($prediction['explanation'])): ?>
                                <div class="prediction-explanation">
                                    <?php echo htmlspecialchars($prediction['explanation']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="compte.php#verifications" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour aux vérifications
        </a>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 