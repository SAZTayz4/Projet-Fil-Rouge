<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
<<<<<<< HEAD
    $email = trim($_POST['email'] ?? '');
=======
    $email = $_POST['email'] ?? '';
>>>>>>> 634ce6e29b4a5095cb42ddfd52b8126da5340ac4
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Tous les champs sont requis.';
    } else {
        try {
<<<<<<< HEAD
            // Vérification de la connexion à la base de données
            if (!$pdo) {
                throw new PDOException("Erreur de connexion à la base de données");
            }

            // Rechercher l'utilisateur par email
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            // Vérification du résultat de la requête
            if ($stmt->rowCount() === 0) {
                $_SESSION['error'] = 'Aucun utilisateur trouvé avec cet email.';
            } else {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['motDePasse'])) {
                    $_SESSION['utilisateur_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['success'] = 'Connexion réussie !';
                    
                    // Mise à jour de last_login
                    $updateStmt = $pdo->prepare("UPDATE utilisateurs SET last_login = NOW() WHERE id = :id");
                    $updateStmt->execute(['id' => $user['id']]);
                    
                    header('Location: /ProjetFileRouge/Frontend/HTML/home.php');
                    exit;
                } else {
                    $_SESSION['error'] = 'Mot de passe incorrect.';
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Erreur de connexion : ' . $e->getMessage();
            // Pour le débogage, vous pouvez décommenter la ligne suivante
            // error_log("Erreur de connexion : " . $e->getMessage());
=======
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
>>>>>>> 634ce6e29b4a5095cb42ddfd52b8126da5340ac4
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