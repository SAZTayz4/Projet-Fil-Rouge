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
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM analyses_ia WHERE id = ?");
                    $stmt->execute([$_POST['analysis_id']]);
                    $_SESSION['success'] = "Analyse supprimée avec succès.";
                    break;
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
    
    header('Location: analyses.php');
    exit;
}

// Récupération des analyses avec pagination
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
    
    if (isset($_GET['date_start']) && $_GET['date_start'] !== '') {
        $where[] = "a.date_analyse >= ?";
        $params[] = $_GET['date_start'] . ' 00:00:00';
    }
    
    if (isset($_GET['date_end']) && $_GET['date_end'] !== '') {
        $where[] = "a.date_analyse <= ?";
        $params[] = $_GET['date_end'] . ' 23:59:59';
    }

    if (isset($_GET['user_id']) && $_GET['user_id'] !== '') {
        $where[] = "a.utilisateur_id = ?";
        $params[] = $_GET['user_id'];
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Nombre total d'analyses
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM analyses_ia a 
        $whereClause
    ");
    if ($params) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $totalAnalyses = $stmt->fetchColumn();
    $totalPages = ceil($totalAnalyses / $limit);

    // Récupération des analyses
    $stmt = $pdo->prepare("
        SELECT a.*, u.email, u.nom
        FROM analyses_ia a
        JOIN utilisateurs u ON a.utilisateur_id = u.id
        $whereClause
        ORDER BY a.date_analyse DESC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $analyses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des utilisateurs pour le filtre
    $stmt = $pdo->query("SELECT id, nom, email FROM utilisateurs ORDER BY nom");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_analyses,
            SUM(nombre_images) as total_images,
            AVG(
                JSON_EXTRACT(resultats, '$.average_score') * 100
            ) as score_moyen
        FROM analyses_ia
        WHERE DATE(date_analyse) = CURRENT_DATE()
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Distribution des résultats
    $stmt = $pdo->query("
        SELECT 
            JSON_EXTRACT(resultats, '$.predictions[0].statut') as statut,
            COUNT(*) as nombre
        FROM analyses_ia
        WHERE DATE(date_analyse) = CURRENT_DATE()
        GROUP BY JSON_EXTRACT(resultats, '$.predictions[0].statut')
    ");
    $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Gestion des analyses IA - CheckMyKicks</title>
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
                    <a class="nav-link" href="payments.php">
                        <i class='bx bxs-wallet'></i> Paiements
                    </a>
                    <a class="nav-link active" href="analyses.php">
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
                    <h2>Gestion des analyses IA</h2>
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
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Analyses aujourd'hui</h5>
                                <h2 class="mb-0"><?php echo number_format($stats['total_analyses']); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Images analysées</h5>
                                <h2 class="mb-0"><?php echo number_format($stats['total_images']); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Score moyen</h5>
                                <h2 class="mb-0"><?php echo number_format($stats['score_moyen'], 1); ?>%</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distribution des résultats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Distribution des résultats aujourd'hui</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($distribution as $dist): ?>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?php 
                                            switch ($dist['statut']) {
                                                case 'LEGIT':
                                                    echo '<span class="badge bg-success">Authentique</span>';
                                                    break;
                                                case 'FAKE':
                                                    echo '<span class="badge bg-danger">Contrefaçon</span>';
                                                    break;
                                                case 'OOD':
                                                    echo '<span class="badge bg-warning">Indéterminé</span>';
                                                    break;
                                            }
                                            ?>
                                        </h5>
                                        <h2 class="mb-0"><?php echo number_format($dist['nombre']); ?></h2>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Utilisateur</label>
                                <select name="user_id" class="form-select">
                                    <option value="">Tous les utilisateurs</option>
                                    <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" 
                                            <?php echo isset($_GET['user_id']) && $_GET['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['nom']); ?> 
                                        (<?php echo htmlspecialchars($user['email']); ?>)
                                    </option>
                                    <?php endforeach; ?>
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

                <!-- Liste des analyses -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Utilisateur</th>
                                        <th>Images</th>
                                        <th>Score moyen</th>
                                        <th>Résultat</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analyses as $analysis): 
                                        $resultats = json_decode($analysis['resultats'], true);
                                        $scoreMoyen = isset($resultats['average_score']) ? $resultats['average_score'] : 0;
                                        $statut = isset($resultats['predictions'][0]['statut']) ? $resultats['predictions'][0]['statut'] : 'OOD';
                                    ?>
                                    <tr>
                                        <td><?php echo $analysis['id']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($analysis['date_analyse'])); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($analysis['nom']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($analysis['email']); ?></small>
                                        </td>
                                        <td><?php echo $analysis['nombre_images']; ?></td>
                                        <td><?php echo number_format($scoreMoyen * 100, 1); ?>%</td>
                                        <td>
                                            <?php 
                                            switch ($statut) {
                                                case 'LEGIT':
                                                    echo '<span class="badge bg-success">Authentique</span>';
                                                    break;
                                                case 'FAKE':
                                                    echo '<span class="badge bg-danger">Contrefaçon</span>';
                                                    break;
                                                case 'OOD':
                                                    echo '<span class="badge bg-warning">Indéterminé</span>';
                                                    break;
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#analysisDetailsModal<?php echo $analysis['id']; ?>">
                                                <i class='bx bx-detail'></i>
                                            </button>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette analyse ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="analysis_id" value="<?php echo $analysis['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal détails analyse -->
                                    <div class="modal fade" id="analysisDetailsModal<?php echo $analysis['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Détails de l'analyse #<?php echo $analysis['id']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <dl class="row">
                                                        <dt class="col-sm-3">Utilisateur</dt>
                                                        <dd class="col-sm-9">
                                                            <?php echo htmlspecialchars($analysis['nom']); ?><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($analysis['email']); ?></small>
                                                        </dd>

                                                        <dt class="col-sm-3">Date d'analyse</dt>
                                                        <dd class="col-sm-9">
                                                            <?php echo date('d/m/Y H:i', strtotime($analysis['date_analyse'])); ?>
                                                        </dd>

                                                        <dt class="col-sm-3">Nombre d'images</dt>
                                                        <dd class="col-sm-9">
                                                            <?php echo $analysis['nombre_images']; ?>
                                                        </dd>

                                                        <dt class="col-sm-3">Score moyen</dt>
                                                        <dd class="col-sm-9">
                                                            <?php echo number_format($scoreMoyen * 100, 1); ?>%
                                                        </dd>

                                                        <dt class="col-sm-3">Détails des prédictions</dt>
                                                        <dd class="col-sm-9">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Image</th>
                                                                            <th>Score</th>
                                                                            <th>Statut</th>
                                                                            <th>Explication</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($resultats['predictions'] as $prediction): ?>
                                                                        <tr>
                                                                            <td><?php echo htmlspecialchars($prediction['filename']); ?></td>
                                                                            <td><?php echo number_format($prediction['score'] * 100, 1); ?>%</td>
                                                                            <td>
                                                                                <?php 
                                                                                switch ($prediction['statut']) {
                                                                                    case 'LEGIT':
                                                                                        echo '<span class="badge bg-success">Authentique</span>';
                                                                                        break;
                                                                                    case 'FAKE':
                                                                                        echo '<span class="badge bg-danger">Contrefaçon</span>';
                                                                                        break;
                                                                                    case 'OOD':
                                                                                        echo '<span class="badge bg-warning">Indéterminé</span>';
                                                                                        break;
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                            <td><?php echo htmlspecialchars($prediction['explanation']); ?></td>
                                                                        </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </dd>
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
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&user_id=<?php echo urlencode($_GET['user_id'] ?? ''); ?>&date_start=<?php echo urlencode($_GET['date_start'] ?? ''); ?>&date_end=<?php echo urlencode($_GET['date_end'] ?? ''); ?>">
                                        Précédent
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&user_id=<?php echo urlencode($_GET['user_id'] ?? ''); ?>&date_start=<?php echo urlencode($_GET['date_start'] ?? ''); ?>&date_end=<?php echo urlencode($_GET['date_end'] ?? ''); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&user_id=<?php echo urlencode($_GET['user_id'] ?? ''); ?>&date_start=<?php echo urlencode($_GET['date_start'] ?? ''); ?>&date_end=<?php echo urlencode($_GET['date_end'] ?? ''); ?>">
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