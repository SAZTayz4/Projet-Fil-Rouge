<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

// Vérification de l'authentification et du rôle admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Récupération des statistiques pour le tableau de bord
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Nombre total d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs");
    $totalUsers = $stmt->fetchColumn();

    // Nombre d'abonnements actifs
    $stmt = $pdo->query("SELECT COUNT(*) FROM abonnements WHERE statut = 'actif'");
    $activeSubscriptions = $stmt->fetchColumn();

    // Chiffre d'affaires du mois
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(montant), 0) 
        FROM paiement 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ");
    $monthlyRevenue = $stmt->fetchColumn();

    // Nombre d'analyses IA du jour
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM analyses_ia 
        WHERE DATE(date_analyse) = CURRENT_DATE()
    ");
    $todayAnalyses = $stmt->fetchColumn();

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
    <title>Administration - CheckMyKicks</title>
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
        .stat-card {
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
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
                    <a class="nav-link active" href="index.php">
                        <i class='bx bxs-dashboard'></i> Tableau de bord
                    </a>
                    <a class="nav-link" href="users.php">
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
                    <h2>Tableau de bord</h2>
                    <div class="text-muted">
                        <?php echo date('d/m/Y H:i'); ?>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Statistiques -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Utilisateurs</h5>
                                <h2 class="mb-0"><?php echo number_format($totalUsers); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Abonnements actifs</h5>
                                <h2 class="mb-0"><?php echo number_format($activeSubscriptions); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">CA du mois</h5>
                                <h2 class="mb-0"><?php echo number_format($monthlyRevenue, 2); ?> €</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Analyses aujourd'hui</h5>
                                <h2 class="mb-0"><?php echo number_format($todayAnalyses); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphiques et tableaux récents -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Derniers paiements</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Utilisateur</th>
                                                <th>Montant</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $stmt = $pdo->query("
                                                SELECT p.*, u.email 
                                                FROM paiement p 
                                                JOIN utilisateurs u ON p.utilisateur_id = u.id 
                                                ORDER BY p.created_at DESC 
                                                LIMIT 5
                                            ");
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                            ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo number_format($row['montant'], 2); ?> €</td>
                                                <td>
                                                    <span class="badge bg-<?php echo $row['statut'] === 'réussi' ? 'success' : 'danger'; ?>">
                                                        <?php echo $row['statut']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Dernières analyses IA</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Utilisateur</th>
                                                <th>Images</th>
                                                <th>Score moyen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $stmt = $pdo->query("
                                                SELECT a.*, u.email 
                                                FROM analyses_ia a 
                                                JOIN utilisateurs u ON a.utilisateur_id = u.id 
                                                ORDER BY a.date_analyse DESC 
                                                LIMIT 5
                                            ");
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                                $resultats = json_decode($row['resultats'], true);
                                                $scoreMoyen = isset($resultats['average_score']) ? $resultats['average_score'] : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['date_analyse'])); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo $row['nombre_images']; ?></td>
                                                <td><?php echo number_format($scoreMoyen * 100, 1); ?>%</td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html> 