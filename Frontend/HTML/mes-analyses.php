<?php
session_start();
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Analyses - CheckMyKicks</title>
    <link rel="stylesheet" href="/ProjetFileRouge/Frontend/CSS/style.css">
    <link rel="stylesheet" href="/ProjetFileRouge/Backend/IA-Check/ia-check.css">
    <link rel="stylesheet" href="/ProjetFileRouge/Frontend/CSS/mes-analyses.css">
    <style>
        
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="analyses-container">
        <div class="analyses-header">
            <h1>Mes Analyses</h1>
            <p>Consultez l'historique de vos vérifications de sneakers</p>
        </div>

        <div id="analyses-content">
            <div class="loading">Chargement de vos analyses...</div>
        </div>

        <div class="pagination" id="pagination"></div>
    </div>

    <script src="../JS/mes-analyses.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html> 