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
                case 'update_status':
                    $stmt = $pdo->prepare("UPDATE paiement SET statut = ? WHERE id = ?");
                    $stmt->execute([$_POST['statut'], $_POST['payment_id']]);
                    
                    // Si le paiement est marqué comme réussi, mettre à jour l'abonnement
                    if ($_POST['statut'] === 'réussi') {
                        $stmt = $pdo->prepare("
                            UPDATE abonnements 
                            SET statut = 'actif' 
                            WHERE utilisateur_id = (SELECT utilisateur_id FROM paiement WHERE id = ?)
                        ");
                        $stmt->execute([$_POST['payment_id']]);
                    }
                    
                    $_SESSION['success'] = "Statut du paiement mis à jour avec succès.";
                    break;
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
    
    header('Location: payments.php');
    exit;
}

// Récupération des paiements avec pagination
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Filtres
    $where = [];
    $params = [];
    
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $where[] = "p.statut = ?";
        $params[] = $_GET['status'];
    }
    
    if (isset($_GET['date_start']) && $_GET['date_start'] !== '') {
        $where[] = "p.created_at >= ?";
        $params[] = $_GET['date_start'] . ' 00:00:00';
    }
    
    if (isset($_GET['date_end']) && $_GET['date_end'] !== '') {
        $where[] = "p.created_at <= ?";
        $params[] = $_GET['date_end'] . ' 23:59:59';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Nombre total de paiements
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM paiement p 
        $whereClause
    ");
    if ($params) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $totalPayments = $stmt->fetchColumn();
    $totalPages = ceil($totalPayments / $limit);

    // Récupération des paiements
    $stmt = $pdo->prepare("
        SELECT p.*, u.email, u.nom, f.numeroFacture, f.statut as facture_statut
        FROM paiement p
        JOIN utilisateurs u ON p.utilisateur_id = u.id
        LEFT JOIN facture f ON p.id = f.paiement_id
        $whereClause
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_paiements,
            SUM(CASE WHEN statut = 'réussi' THEN montant ELSE 0 END) as montant_total,
            COUNT(CASE WHEN statut = 'réussi' THEN 1 END) as paiements_reussis,
            COUNT(CASE WHEN statut = 'échoué' THEN 1 END) as paiements_echoues
        FROM paiement
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <title>Gestion des paiements - CheckMyKicks</title>
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
                    <a class="nav-link" href="subscriptions.php">
                        <i class='bx bxs-crown'></i> Abonnements
                    </a>
                    <a class="nav-link active" href="payments.php">
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
                    <h2>Gestion des paiements</h2>
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

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Paiements du mois</h5>
                                <h2 class="mb-0"><?php echo number_format($stats['total_paiements']); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">CA du mois</h5>
                                <h2 class="mb-0"><?php echo number_format($stats['montant_total'], 2); ?> €</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Paiements réussis</h5>
                                <h2 class="mb-0"><?php echo number_format($stats['paiements_reussis']); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Paiements échoués</h5>
                                <h2 class="mb-0"><?php echo number_format($stats['paiements_echoues']); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="réussi" <?php echo isset($_GET['status']) && $_GET['status'] === 'réussi' ? 'selected' : ''; ?>>
                                        Réussi
                                    </option>
                                    <option value="échoué" <?php echo isset($_GET['status']) && $_GET['status'] === 'échoué' ? 'selected' : ''; ?>>
                                        Échoué
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date début</label>
                                <input type="date" class="form-control" name="date_start" 
                                       value="<?php echo isset($_GET['date_start']) ? $_GET['date_start'] : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date fin</label>
                                <input type="date" class="form-control" name="date_end" 
                                       value="<?php echo isset($_GET['date_end']) ? $_GET['date_end'] : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">Filtrer</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des paiements -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Utilisateur</th>
                                        <th>Type</th>
                                        <th>Montant</th>
                                        <th>Méthode</th>
                                        <th>Statut</th>
                                        <th>Facture</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo $payment['id']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($payment['created_at'])); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($payment['nom']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($payment['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($payment['typeAbonnement']); ?></td>
                                        <td><?php echo number_format($payment['montant'], 2); ?> €</td>
                                        <td><?php echo $payment['methodePaiement']; ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                <select name="statut" class="form-select form-select-sm" 
                                                        onchange="this.form.submit()">
                                                    <option value="réussi" <?php echo $payment['statut'] === 'réussi' ? 'selected' : ''; ?>>
                                                        Réussi
                                                    </option>
                                                    <option value="échoué" <?php echo $payment['statut'] === 'échoué' ? 'selected' : ''; ?>>
                                                        Échoué
                                                    </option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if ($payment['numeroFacture']): ?>
                                            <a href="<?php echo htmlspecialchars($payment['numeroFacture']); ?>" 
                                               target="_blank" class="btn btn-sm btn-info">
                                                <i class='bx bx-file'></i> Voir
                                            </a>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Non générée</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#paymentDetailsModal<?php echo $payment['id']; ?>">
                                                <i class='bx bx-detail'></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal détails paiement -->
                                    <div class="modal fade" id="paymentDetailsModal<?php echo $payment['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Détails du paiement #<?php echo $payment['id']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <dl class="row">
                                                        <dt class="col-sm-4">Utilisateur</dt>
                                                        <dd class="col-sm-8">
                                                            <?php echo htmlspecialchars($payment['nom']); ?><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($payment['email']); ?></small>
                                                        </dd>

                                                        <dt class="col-sm-4">Date</dt>
                                                        <dd class="col-sm-8">
                                                            <?php echo date('d/m/Y H:i', strtotime($payment['created_at'])); ?>
                                                        </dd>

                                                        <dt class="col-sm-4">Type d'abonnement</dt>
                                                        <dd class="col-sm-8">
                                                            <?php echo htmlspecialchars($payment['typeAbonnement']); ?>
                                                        </dd>

                                                        <dt class="col-sm-4">Montant</dt>
                                                        <dd class="col-sm-8">
                                                            <?php echo number_format($payment['montant'], 2); ?> €
                                                        </dd>

                                                        <dt class="col-sm-4">Méthode de paiement</dt>
                                                        <dd class="col-sm-8">
                                                            <?php echo $payment['methodePaiement']; ?>
                                                        </dd>

                                                        <dt class="col-sm-4">Statut</dt>
                                                        <dd class="col-sm-8">
                                                            <span class="badge bg-<?php echo $payment['statut'] === 'réussi' ? 'success' : 'danger'; ?>">
                                                                <?php echo $payment['statut']; ?>
                                                            </span>
                                                        </dd>

                                                        <?php if ($payment['numeroFacture']): ?>
                                                        <dt class="col-sm-4">Facture</dt>
                                                        <dd class="col-sm-8">
                                                            <a href="<?php echo htmlspecialchars($payment['numeroFacture']); ?>" 
                                                               target="_blank" class="btn btn-sm btn-info">
                                                                <i class='bx bx-file'></i> Voir la facture
                                                            </a>
                                                        </dd>
                                                        <?php endif; ?>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo urlencode($_GET['status'] ?? ''); ?>&date_start=<?php echo urlencode($_GET['date_start'] ?? ''); ?>&date_end=<?php echo urlencode($_GET['date_end'] ?? ''); ?>">
                                        Précédent
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($_GET['status'] ?? ''); ?>&date_start=<?php echo urlencode($_GET['date_start'] ?? ''); ?>&date_end=<?php echo urlencode($_GET['date_end'] ?? ''); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo urlencode($_GET['status'] ?? ''); ?>&date_start=<?php echo urlencode($_GET['date_start'] ?? ''); ?>&date_end=<?php echo urlencode($_GET['date_end'] ?? ''); ?>">
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