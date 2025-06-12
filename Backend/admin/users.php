<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

// Vérification de l'authentification et du rôle admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_role':
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
                    $stmt->execute([$_POST['role'], $_POST['user_id']]);
                    $_SESSION['success'] = "Rôle utilisateur mis à jour avec succès.";
                    break;

                case 'update_subscription':
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET abonnement_id = ? WHERE id = ?");
                    $stmt->execute([$_POST['abonnement_id'], $_POST['user_id']]);
                    $_SESSION['success'] = "Abonnement utilisateur mis à jour avec succès.";
                    break;

                case 'delete_user':
                    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
                    $stmt->execute([$_POST['user_id']]);
                    $_SESSION['success'] = "Utilisateur supprimé avec succès.";
                    break;
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
    
    header('Location: users.php');
    exit;
}

// Récupération des utilisateurs avec pagination
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Recherche
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $where = '';
    $params = [];
    
    if ($search) {
        $where = "WHERE nom LIKE ? OR email LIKE ?";
        $params = ["%$search%", "%$search%"];
    }

    // Nombre total d'utilisateurs
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs $where");
    if ($params) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $totalUsers = $stmt->fetchColumn();
    $totalPages = ceil($totalUsers / $limit);

    // Récupération des utilisateurs
    $stmt = $pdo->prepare("
        SELECT u.*, a.type as abonnement_type 
        FROM utilisateurs u 
        LEFT JOIN abonnement a ON u.abonnement_id = a.id 
        $where 
        ORDER BY u.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des types d'abonnement pour le formulaire
    $stmt = $pdo->query("SELECT id, type FROM abonnement ORDER BY prix");
    $abonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur de base de données : " . $e->getMessage());
    $error = "Une erreur est survenue lors de la récupération des données.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - CheckMyKicks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 1rem;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,.2);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h4 class="text-white">CheckMyKicks</h4>
                    <p class="text-white-50">Administration</p>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="index.php">
                        <i class='bx bxs-dashboard'></i> Tableau de bord
                    </a>
                    <a class="nav-link active" href="users.php">
                        <i class='bx bxs-user-detail'></i> Utilisateurs
                    </a>
                    <a class="nav-link" href="subscriptions.php">
                        <i class='bx bxs-crown'></i> Abonnements
                    </a>
                    <a class="nav-link" href="payments.php">
                        <i class='bx bxs-wallet'></i> Paiements
                    </a>
                    <a class="nav-link" href="analyses.php">
                        <i class='bx bxs-brain'></i> Analyses IA
                    </a>
                    <a class="nav-link" href="drops.php">
                        <i class='bx bxs-shopping-bag'></i> Drops
                    </a>
                    <a class="nav-link" href="notifications.php">
                        <i class='bx bxs-bell'></i> Notifications
                    </a>
                    <a class="nav-link" href="../auth/logout.php">
                        <i class='bx bxs-log-out'></i> Déconnexion
                    </a>
                </nav>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Gestion des utilisateurs</h2>
                    <div class="text-muted">
                        <?php echo date('d/m/Y H:i'); ?>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Barre de recherche -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Rechercher un utilisateur..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class='bx bx-search'></i> Rechercher
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des utilisateurs -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Abonnement</th>
                                        <th>Date d'inscription</th>
                                        <th>Dernière connexion</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_role">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <select name="role" class="form-select form-select-sm" 
                                                        onchange="this.form.submit()" 
                                                        <?php echo $user['id'] === $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                    <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>
                                                        Client
                                                    </option>
                                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                                                        Admin
                                                    </option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_subscription">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <select name="abonnement_id" class="form-select form-select-sm" 
                                                        onchange="this.form.submit()">
                                                    <option value="">Aucun</option>
                                                    <?php foreach ($abonnements as $abonnement): ?>
                                                    <option value="<?php echo $abonnement['id']; ?>" 
                                                            <?php echo $user['abonnement_id'] == $abonnement['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($abonnement['type']); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais'; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">
                                        Précédent
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">
                                        Suivant
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 