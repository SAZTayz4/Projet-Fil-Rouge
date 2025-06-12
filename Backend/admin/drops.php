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
                        INSERT INTO drop_sneaker (
                            nom, marque, modele, date_sortie, prix, 
                            description, lien_officiel, image_url, status
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $_POST['nom'],
                        $_POST['marque'],
                        $_POST['modele'],
                        $_POST['date_sortie'],
                        $_POST['prix'],
                        $_POST['description'],
                        $_POST['lien_officiel'],
                        $_POST['image_url'],
                        $_POST['status']
                    ]);
                    $_SESSION['success'] = "Drop ajouté avec succès.";
                    break;

                case 'edit':
                    $stmt = $pdo->prepare("
                        UPDATE drop_sneaker SET 
                            nom = ?, marque = ?, modele = ?, date_sortie = ?, 
                            prix = ?, description = ?, lien_officiel = ?, 
                            image_url = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $_POST['nom'],
                        $_POST['marque'],
                        $_POST['modele'],
                        $_POST['date_sortie'],
                        $_POST['prix'],
                        $_POST['description'],
                        $_POST['lien_officiel'],
                        $_POST['image_url'],
                        $_POST['status'],
                        $_POST['drop_id']
                    ]);
                    $_SESSION['success'] = "Drop mis à jour avec succès.";
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM drop_sneaker WHERE id = ?");
                    $stmt->execute([$_POST['drop_id']]);
                    $_SESSION['success'] = "Drop supprimé avec succès.";
                    break;

                case 'add_reminder':
                    $stmt = $pdo->prepare("
                        INSERT INTO drop_reminders (
                            drop_id, utilisateur_id, date_rappel, status
                        ) VALUES (?, ?, ?, 'pending')
                    ");
                    $stmt->execute([
                        $_POST['drop_id'],
                        $_POST['utilisateur_id'],
                        $_POST['date_rappel']
                    ]);
                    $_SESSION['success'] = "Rappel ajouté avec succès.";
                    break;

                case 'delete_reminder':
                    $stmt = $pdo->prepare("DELETE FROM drop_reminders WHERE id = ?");
                    $stmt->execute([$_POST['reminder_id']]);
                    $_SESSION['success'] = "Rappel supprimé avec succès.";
                    break;
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
    
    header('Location: drops.php');
    exit;
}

// Récupération des drops avec pagination
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
        $where[] = "d.status = ?";
        $params[] = $_GET['status'];
    }
    
    if (isset($_GET['marque']) && $_GET['marque'] !== '') {
        $where[] = "d.marque = ?";
        $params[] = $_GET['marque'];
    }

    if (isset($_GET['date_start']) && $_GET['date_start'] !== '') {
        $where[] = "d.date_sortie >= ?";
        $params[] = $_GET['date_start'] . ' 00:00:00';
    }
    
    if (isset($_GET['date_end']) && $_GET['date_end'] !== '') {
        $where[] = "d.date_sortie <= ?";
        $params[] = $_GET['date_end'] . ' 23:59:59';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Nombre total de drops
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM drop_sneaker d 
        $whereClause
    ");
    if ($params) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $totalDrops = $stmt->fetchColumn();
    $totalPages = ceil($totalDrops / $limit);

    // Récupération des drops
    $stmt = $pdo->prepare("
        SELECT d.*, 
               COUNT(r.id) as nombre_rappels
        FROM drop_sneaker d
        LEFT JOIN drop_reminders r ON d.id = r.drop_id
        $whereClause
        GROUP BY d.id
        ORDER BY d.date_sortie DESC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $drops = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des marques pour le filtre
    $stmt = $pdo->query("SELECT DISTINCT marque FROM drop_sneaker ORDER BY marque");
    $marques = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Statistiques
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_drops,
            COUNT(CASE WHEN status = 'upcoming' THEN 1 END) as drops_a_venir,
            COUNT(CASE WHEN status = 'released' THEN 1 END) as drops_sortis,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as drops_annules
        FROM drop_sneaker
        WHERE date_sortie >= CURRENT_DATE()
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Prochains drops
    $stmt = $pdo->query("
        SELECT d.*, COUNT(r.id) as nombre_rappels
        FROM drop_sneaker d
        LEFT JOIN drop_reminders r ON d.id = r.drop_id
        WHERE d.date_sortie >= CURRENT_DATE()
        AND d.status = 'upcoming'
        GROUP BY d.id
        ORDER BY d.date_sortie ASC
        LIMIT 5
    ");
    $prochainsDrops = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Gestion des drops - CheckMyKicks</title>
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
        .drop-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
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
                    <a class="nav-link" href="analyses.php">
                        <i class='bx bxs-brain'></i> Analyses IA
                    </a>
                    <a class="nav-link active" href="drops.php">
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
                    <h2>Gestion des drops</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDropModal">
                        <i class='bx bx-plus'></i> Nouveau drop
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
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total drops</h5>
                                <h2 class="mb-0"><?php echo number_format($stats['total_drops']); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">À venir</h5>
                                <h2 class="mb-0"><?php echo number_format($stats['drops_a_venir']); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Sortis</h5>
                                <h2 class="mb-0"><?php echo number_format($stats['drops_sortis']); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Annulés</h5>
                                <h2 class="mb-0"><?php echo number_format($stats['drops_annules']); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prochains drops -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Prochains drops</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Nom</th>
                                        <th>Marque</th>
                                        <th>Date de sortie</th>
                                        <th>Prix</th>
                                        <th>Rappels</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prochainsDrops as $drop): ?>
                                    <tr>
                                        <td>
                                            <?php if ($drop['image_url']): ?>
                                            <img src="<?php echo htmlspecialchars($drop['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($drop['nom']); ?>" 
                                                 class="drop-image">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($drop['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($drop['marque']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($drop['date_sortie'])); ?></td>
                                        <td><?php echo number_format($drop['prix'], 2); ?> €</td>
                                        <td><?php echo $drop['nombre_rappels']; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editDropModal<?php echo $drop['id']; ?>">
                                                <i class='bx bx-edit'></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#remindersModal<?php echo $drop['id']; ?>">
                                                <i class='bx bx-bell'></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Marque</label>
                                <select name="marque" class="form-select">
                                    <option value="">Toutes les marques</option>
                                    <?php foreach ($marques as $marque): ?>
                                    <option value="<?php echo htmlspecialchars($marque); ?>" 
                                            <?php echo isset($_GET['marque']) && $_GET['marque'] === $marque ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($marque); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select">
                                    <option value="">Tous les statuts</option>
                                    <option value="upcoming" <?php echo isset($_GET['status']) && $_GET['status'] === 'upcoming' ? 'selected' : ''; ?>>
                                        À venir
                                    </option>
                                    <option value="released" <?php echo isset($_GET['status']) && $_GET['status'] === 'released' ? 'selected' : ''; ?>>
                                        Sorti
                                    </option>
                                    <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : ''; ?>>
                                        Annulé
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
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="drops.php" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des drops -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Nom</th>
                                        <th>Marque</th>
                                        <th>Modèle</th>
                                        <th>Date de sortie</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                        <th>Rappels</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($drops as $drop): ?>
                                    <tr>
                                        <td>
                                            <?php if ($drop['image_url']): ?>
                                            <img src="<?php echo htmlspecialchars($drop['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($drop['nom']); ?>" 
                                                 class="drop-image">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($drop['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($drop['marque']); ?></td>
                                        <td><?php echo htmlspecialchars($drop['modele']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($drop['date_sortie'])); ?></td>
                                        <td><?php echo number_format($drop['prix'], 2); ?> €</td>
                                        <td>
                                            <?php 
                                            switch ($drop['status']) {
                                                case 'upcoming':
                                                    echo '<span class="badge bg-success">À venir</span>';
                                                    break;
                                                case 'released':
                                                    echo '<span class="badge bg-info">Sorti</span>';
                                                    break;
                                                case 'cancelled':
                                                    echo '<span class="badge bg-danger">Annulé</span>';
                                                    break;
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $drop['nombre_rappels']; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editDropModal<?php echo $drop['id']; ?>">
                                                <i class='bx bx-edit'></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#remindersModal<?php echo $drop['id']; ?>">
                                                <i class='bx bx-bell'></i>
                                            </button>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce drop ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="drop_id" value="<?php echo $drop['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal édition drop -->
                                    <div class="modal fade" id="editDropModal<?php echo $drop['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Modifier le drop</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="drop_id" value="<?php echo $drop['id']; ?>">
                                                        
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Nom</label>
                                                                <input type="text" class="form-control" name="nom" 
                                                                       value="<?php echo htmlspecialchars($drop['nom']); ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Marque</label>
                                                                <input type="text" class="form-control" name="marque" 
                                                                       value="<?php echo htmlspecialchars($drop['marque']); ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Modèle</label>
                                                                <input type="text" class="form-control" name="modele" 
                                                                       value="<?php echo htmlspecialchars($drop['modele']); ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Prix</label>
                                                                <input type="number" step="0.01" class="form-control" name="prix" 
                                                                       value="<?php echo $drop['prix']; ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Date de sortie</label>
                                                                <input type="datetime-local" class="form-control" name="date_sortie" 
                                                                       value="<?php echo date('Y-m-d\TH:i', strtotime($drop['date_sortie'])); ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Statut</label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="upcoming" <?php echo $drop['status'] === 'upcoming' ? 'selected' : ''; ?>>
                                                                        À venir
                                                                    </option>
                                                                    <option value="released" <?php echo $drop['status'] === 'released' ? 'selected' : ''; ?>>
                                                                        Sorti
                                                                    </option>
                                                                    <option value="cancelled" <?php echo $drop['status'] === 'cancelled' ? 'selected' : ''; ?>>
                                                                        Annulé
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($drop['description']); ?></textarea>
                                                        </div>

                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Lien officiel</label>
                                                                <input type="url" class="form-control" name="lien_officiel" 
                                                                       value="<?php echo htmlspecialchars($drop['lien_officiel']); ?>">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">URL de l'image</label>
                                                                <input type="url" class="form-control" name="image_url" 
                                                                       value="<?php echo htmlspecialchars($drop['image_url']); ?>">
                                                            </div>
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

                                    <!-- Modal rappels -->
                                    <div class="modal fade" id="remindersModal<?php echo $drop['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Rappels pour <?php echo htmlspecialchars($drop['nom']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- Formulaire d'ajout de rappel -->
                                                    <form method="POST" class="mb-4">
                                                        <input type="hidden" name="action" value="add_reminder">
                                                        <input type="hidden" name="drop_id" value="<?php echo $drop['id']; ?>">
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Utilisateur</label>
                                                                <select name="utilisateur_id" class="form-select" required>
                                                                    <?php
                                                                    $stmt = $pdo->query("SELECT id, nom, email FROM utilisateurs ORDER BY nom");
                                                                    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)):
                                                                    ?>
                                                                    <option value="<?php echo $user['id']; ?>">
                                                                        <?php echo htmlspecialchars($user['nom']); ?> 
                                                                        (<?php echo htmlspecialchars($user['email']); ?>)
                                                                    </option>
                                                                    <?php endwhile; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Date du rappel</label>
                                                                <input type="datetime-local" class="form-control" name="date_rappel" 
                                                                       value="<?php echo date('Y-m-d\TH:i', strtotime($drop['date_sortie'] . ' -1 day')); ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="mt-3">
                                                            <button type="submit" class="btn btn-primary">Ajouter un rappel</button>
                                                        </div>
                                                    </form>

                                                    <!-- Liste des rappels -->
                                                    <h6>Rappels existants</h6>
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Utilisateur</th>
                                                                    <th>Date du rappel</th>
                                                                    <th>Statut</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $stmt = $pdo->prepare("
                                                                    SELECT r.*, u.nom, u.email
                                                                    FROM drop_reminders r
                                                                    JOIN utilisateurs u ON r.utilisateur_id = u.id
                                                                    WHERE r.drop_id = ?
                                                                    ORDER BY r.date_rappel DESC
                                                                ");
                                                                $stmt->execute([$drop['id']]);
                                                                while ($reminder = $stmt->fetch(PDO::FETCH_ASSOC)):
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <?php echo htmlspecialchars($reminder['nom']); ?><br>
                                                                        <small class="text-muted">
                                                                            <?php echo htmlspecialchars($reminder['email']); ?>
                                                                        </small>
                                                                    </td>
                                                                    <td>
                                                                        <?php echo date('d/m/Y H:i', strtotime($reminder['date_rappel'])); ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php 
                                                                        switch ($reminder['status']) {
                                                                            case 'pending':
                                                                                echo '<span class="badge bg-warning">En attente</span>';
                                                                                break;
                                                                            case 'sent':
                                                                                echo '<span class="badge bg-success">Envoyé</span>';
                                                                                break;
                                                                            case 'failed':
                                                                                echo '<span class="badge bg-danger">Échoué</span>';
                                                                                break;
                                                                        }
                                                                        ?>
                                                                    </td>
                                                                    <td>
                                                                        <form method="POST" class="d-inline" 
                                                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rappel ?');">
                                                                            <input type="hidden" name="action" value="delete_reminder">
                                                                            <input type="hidden" name="reminder_id" value="<?php echo $reminder['id']; ?>">
                                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                                <i class='bx bx-trash'></i>
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                                <?php endwhile; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
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
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo urlencode($_GET['status'] ?? ''); ?>&marque=<?php echo urlencode($_GET['marque'] ?? ''); ?>&date_start=<?php echo urlencode($_GET['date_start'] ?? ''); ?>&date_end=<?php echo urlencode($_GET['date_end'] ?? ''); ?>">
                                        Précédent
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($_GET['status'] ?? ''); ?>&marque=<?php echo urlencode($_GET['marque'] ?? ''); ?>&date_start=<?php echo urlencode($_GET['date_start'] ?? ''); ?>&date_end=<?php echo urlencode($_GET['date_end'] ?? ''); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo urlencode($_GET['status'] ?? ''); ?>&marque=<?php echo urlencode($_GET['marque'] ?? ''); ?>&date_start=<?php echo urlencode($_GET['date_start'] ?? ''); ?>&date_end=<?php echo urlencode($_GET['date_end'] ?? ''); ?>">
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

    <!-- Modal ajout drop -->
    <div class="modal fade" id="addDropModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau drop</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" class="form-control" name="nom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Marque</label>
                                <input type="text" class="form-control" name="marque" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Modèle</label>
                                <input type="text" class="form-control" name="modele" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prix</label>
                                <input type="number" step="0.01" class="form-control" name="prix" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date de sortie</label>
                                <input type="datetime-local" class="form-control" name="date_sortie" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select" required>
                                    <option value="upcoming">À venir</option>
                                    <option value="released">Sorti</option>
                                    <option value="cancelled">Annulé</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Lien officiel</label>
                                <input type="url" class="form-control" name="lien_officiel">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">URL de l'image</label>
                                <input type="url" class="form-control" name="image_url">
                            </div>
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