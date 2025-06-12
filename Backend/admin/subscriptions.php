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
                case 'add':
                    $stmt = $pdo->prepare("
                        INSERT INTO abonnement (type, prix, limitesVerifications, accesChatbot, accesAutoCop, duree)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $_POST['type'],
                        $_POST['prix'],
                        $_POST['limitesVerifications'],
                        isset($_POST['accesChatbot']) ? 1 : 0,
                        isset($_POST['accesAutoCop']) ? 1 : 0,
                        $_POST['duree']
                    ]);
                    $_SESSION['success'] = "Abonnement ajouté avec succès.";
                    break;

                case 'edit':
                    $stmt = $pdo->prepare("
                        UPDATE abonnement 
                        SET type = ?, prix = ?, limitesVerifications = ?, 
                            accesChatbot = ?, accesAutoCop = ?, duree = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $_POST['type'],
                        $_POST['prix'],
                        $_POST['limitesVerifications'],
                        isset($_POST['accesChatbot']) ? 1 : 0,
                        isset($_POST['accesAutoCop']) ? 1 : 0,
                        $_POST['duree'],
                        $_POST['id']
                    ]);
                    $_SESSION['success'] = "Abonnement modifié avec succès.";
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM abonnement WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $_SESSION['success'] = "Abonnement supprimé avec succès.";
                    break;
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
    
    header('Location: subscriptions.php');
    exit;
}

// Récupération des abonnements
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des types d'abonnement
    $stmt = $pdo->query("SELECT * FROM abonnement ORDER BY prix");
    $abonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques des abonnements
    $stmt = $pdo->query("
        SELECT a.type, COUNT(u.id) as nombre_utilisateurs
        FROM abonnement a
        LEFT JOIN utilisateurs u ON a.id = u.abonnement_id
        GROUP BY a.id, a.type
    ");
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Gestion des abonnements - CheckMyKicks</title>
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
                    <a class="nav-link" href="users.php">
                        <i class='bx bxs-user-detail'></i> Utilisateurs
                    </a>
                    <a class="nav-link active" href="subscriptions.php">
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
                    <h2>Gestion des abonnements</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubscriptionModal">
                        <i class='bx bx-plus'></i> Nouvel abonnement
                    </button>
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

                <!-- Statistiques -->
                <div class="row mb-4">
                    <?php foreach ($stats as $stat): ?>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($stat['type']); ?></h5>
                                <p class="card-text">
                                    <strong><?php echo $stat['nombre_utilisateurs']; ?></strong> utilisateurs
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Liste des abonnements -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Prix</th>
                                        <th>Limites vérifications</th>
                                        <th>Accès Chatbot</th>
                                        <th>Accès AutoCop</th>
                                        <th>Durée (mois)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($abonnements as $abonnement): ?>
                                    <tr>
                                        <td><?php echo $abonnement['id']; ?></td>
                                        <td><?php echo htmlspecialchars($abonnement['type']); ?></td>
                                        <td><?php echo number_format($abonnement['prix'], 2); ?> €</td>
                                        <td><?php echo $abonnement['limitesVerifications']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $abonnement['accesChatbot'] ? 'success' : 'danger'; ?>">
                                                <?php echo $abonnement['accesChatbot'] ? 'Oui' : 'Non'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $abonnement['accesAutoCop'] ? 'success' : 'danger'; ?>">
                                                <?php echo $abonnement['accesAutoCop'] ? 'Oui' : 'Non'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $abonnement['duree'] ?: 'Illimitée'; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editSubscriptionModal<?php echo $abonnement['id']; ?>">
                                                <i class='bx bx-edit'></i>
                                            </button>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet abonnement ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $abonnement['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal d'édition -->
                                    <div class="modal fade" id="editSubscriptionModal<?php echo $abonnement['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="id" value="<?php echo $abonnement['id']; ?>">
                                                    
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier l'abonnement</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Type</label>
                                                            <input type="text" class="form-control" name="type" 
                                                                   value="<?php echo htmlspecialchars($abonnement['type']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Prix</label>
                                                            <input type="number" step="0.01" class="form-control" name="prix" 
                                                                   value="<?php echo $abonnement['prix']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Limites vérifications</label>
                                                            <input type="number" class="form-control" name="limitesVerifications" 
                                                                   value="<?php echo $abonnement['limitesVerifications']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="accesChatbot" 
                                                                       <?php echo $abonnement['accesChatbot'] ? 'checked' : ''; ?>>
                                                                <label class="form-check-label">Accès Chatbot</label>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="accesAutoCop" 
                                                                       <?php echo $abonnement['accesAutoCop'] ? 'checked' : ''; ?>>
                                                                <label class="form-check-label">Accès AutoCop</label>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Durée (mois)</label>
                                                            <input type="number" class="form-control" name="duree" 
                                                                   value="<?php echo $abonnement['duree']; ?>">
                                                            <small class="text-muted">Laissez vide pour illimité</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'ajout -->
    <div class="modal fade" id="addSubscriptionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Nouvel abonnement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <input type="text" class="form-control" name="type" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prix</label>
                            <input type="number" step="0.01" class="form-control" name="prix" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Limites vérifications</label>
                            <input type="number" class="form-control" name="limitesVerifications" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="accesChatbot">
                                <label class="form-check-label">Accès Chatbot</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="accesAutoCop">
                                <label class="form-check-label">Accès AutoCop</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Durée (mois)</label>
                            <input type="number" class="form-control" name="duree">
                            <small class="text-muted">Laissez vide pour illimité</small>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 