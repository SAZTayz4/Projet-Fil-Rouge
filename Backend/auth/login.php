<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Tous les champs sont requis.';
    } else {
        try {
            // Rechercher l'utilisateur par email
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['motDePasse'])) {
                $_SESSION['utilisateur_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['success'] = 'Connexion réussie !';
                header('Location: ../../Frontend/HTML/home.php');
                exit;
            } else {
                $_SESSION['error'] = 'Email ou mot de passe incorrect.';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Une erreur est survenue lors de la connexion : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - CheckMyKicks</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../Frontend/CSS/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Connexion</h2>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="auth-button">Se connecter</button>
            </form>
            <p class="auth-link">Pas encore de compte ? <a href="register.php">Inscrivez-vous</a></p>
        </div>
    </div>
</body>
</html> 