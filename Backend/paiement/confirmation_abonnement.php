<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification de la connexion et des données d'abonnement
if (!isset($_SESSION['utilisateur_id']) || !isset($_SESSION['abonnement'])) {
    header('Location: /ProjetFileRouge/Frontend/HTML/home.php');
    exit;
}

$abonnement = $_SESSION['abonnement'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation abonnement - CheckMyKicks</title>
    <link rel="stylesheet" href="/ProjetFileRouge/Frontend/CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-card">
            <h1>Abonnement confirmé !</h1>
            <div class="confirmation-details">
                <p><strong>Type d'abonnement :</strong> <?php echo htmlspecialchars(ucfirst($abonnement['type'])); ?></p>
                <p><strong>Prix :</strong> <?php echo number_format($abonnement['prix'], 2); ?> € /mois</p>
                <p><strong>Date de souscription :</strong> <?php echo date('d/m/Y H:i', strtotime($abonnement['date_creation'])); ?></p>
            </div>
            <div class="confirmation-actions">
                <a href="/ProjetFileRouge/Frontend/HTML/compte.php" class="btn btn-primary">Retour à mon compte</a>
                <a href="/ProjetFileRouge/Frontend/HTML/home.php" class="btn btn-secondary">Retour à l'accueil</a>
            </div>
        </div>
    </div>

    <style>
    .confirmation-container {
        max-width: 600px;
        margin: 60px auto;
        padding: 20px;
    }
    .confirmation-card {
        background: #fff;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        text-align: center;
    }
    .confirmation-details {
        margin: 30px 0;
        text-align: left;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .confirmation-details p {
        margin: 10px 0;
        font-size: 16px;
    }
    .confirmation-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 30px;
    }
    .btn {
        padding: 12px 24px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .btn-primary {
        background: #007bff;
        color: white;
    }
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    .btn:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }
    </style>
</body>
</html>
<?php
// Nettoyage des données de session après affichage
unset($_SESSION['abonnement']);
?> 