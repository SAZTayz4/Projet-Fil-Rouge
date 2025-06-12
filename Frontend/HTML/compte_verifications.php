<?php
session_start();
require_once '../../Backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

$sql = "SELECT * FROM analyses_ia WHERE utilisateur_id = ? ORDER BY date_analyse DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$analyses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes vérifications IA</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <h2>Mes vérifications IA</h2>
    <div class="verifications-list">
        <?php foreach ($analyses as $analyse): ?>
            <div class="analyse-item">
                <span><?= date('d/m/Y H:i', strtotime($analyse['date_analyse'])) ?></span>
                <span><?= $analyse['nombre_images'] ?> images</span>
                <a href="voir_analyse.php?id=<?= $analyse['id'] ?>">Voir le détail</a>
            </div>
        <?php endforeach; ?>
        <?php if (empty($analyses)): ?>
            <p>Aucune vérification effectuée pour le moment.</p>
        <?php endif; ?>
    </div>
</body>
</html> 