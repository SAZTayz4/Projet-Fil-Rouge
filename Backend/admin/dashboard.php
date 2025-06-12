<?php
require_once 'check_admin.php';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'delete_user':
                $userId = $_POST['user_id'] ?? '';
                if ($userId && $userId != $_SESSION['utilisateur_id']) {
                    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
                    $stmt->execute([$userId]);
                    $_SESSION['success'] = "Utilisateur supprimé avec succès.";
                }
                break;
                
            case 'execute_sql':
                $sql = $_POST['sql'] ?? '';
                if (!empty($sql)) {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $_SESSION['success'] = "Requête SQL exécutée avec succès.";
                }
                break;
                
            case 'drop_table':
                $tableName = $_POST['table_name'] ?? '';
                if (!empty($tableName)) {
                    $stmt = $pdo->prepare("DROP TABLE IF EXISTS `$tableName`");
                    $stmt->execute();
                    $_SESSION['success'] = "Table '$tableName' supprimée avec succès.";
                }
                break;
                
            case 'truncate_table':
                $tableName = $_POST['table_name'] ?? '';
                if (!empty($tableName)) {
                    $stmt = $pdo->prepare("TRUNCATE TABLE `$tableName`");
                    $stmt->execute();
                    $_SESSION['success'] = "Table '$tableName' vidée avec succès.";
                }
                break;
                
            case 'update_user_role':
                $userId = $_POST['user_id'] ?? '';
                $newRole = $_POST['new_role'] ?? '';
                if ($userId && $newRole) {
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
                    $stmt->execute([$newRole, $userId]);
                    $_SESSION['success'] = "Rôle utilisateur mis à jour avec succès.";
                }
                break;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Récupérer les données
try {
    // Compter les utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM utilisateurs");
    $userCount = $stmt->fetch()['count'];
    
    // Récupérer tous les utilisateurs
    $stmt = $pdo->query("SELECT * FROM utilisateurs ORDER BY id DESC");
    $users = $stmt->fetchAll();
    
    // Récupérer la liste des tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Récupérer les informations de la base de données
    $stmt = $pdo->query("SELECT table_name, table_rows, data_length, index_length 
                        FROM information_schema.tables 
                        WHERE table_schema = DATABASE()");
    $tableInfo = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = "Erreur lors de la récupération des données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panneau d'Administration - CheckMyKicks</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .admin-header {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-title {
            color: #667eea;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }

        .admin-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .card-title {
            color: #667eea;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .users-table th,
        .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .users-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #667eea;
        }

        .users-table tr:hover {
            background: #f8f9fa;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .sql-editor {
            width: 100%;
            height: 200px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            padding: 15px;
            resize: vertical;
        }

        .table-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .table-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }

        .table-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .table-stats {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title"><i class="fas fa-shield-alt"></i> Panneau d'Administration</h1>
            <a href="../auth/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $userCount; ?></div>
                <div class="stat-label">Utilisateurs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($tables); ?></div>
                <div class="stat-label">Tables</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($tableInfo); ?></div>
                <div class="stat-label">Structures</div>
            </div>
        </div>

        <div class="admin-grid">
            <!-- Gestion des utilisateurs -->
            <div class="admin-card">
                <h2 class="card-title">
                    <i class="fas fa-users"></i> Gestion des Utilisateurs
                </h2>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td class="table-actions">
                                <?php if ($user['id'] != $_SESSION['utilisateur_id']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_user_role">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="new_role" onchange="this.form.submit()">
                                            <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <span class="btn btn-warning">Vous</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Exécution de requêtes SQL -->
            <div class="admin-card">
                <h2 class="card-title">
                    <i class="fas fa-code"></i> Exécuter du SQL
                </h2>
                <form method="POST">
                    <input type="hidden" name="action" value="execute_sql">
                    <div class="form-group">
                        <label for="sql">Requête SQL :</label>
                        <textarea name="sql" id="sql" class="sql-editor" placeholder="Entrez votre requête SQL ici..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play"></i> Exécuter
                    </button>
                </form>
            </div>

            <!-- Gestion des tables -->
            <div class="admin-card">
                <h2 class="card-title">
                    <i class="fas fa-table"></i> Gestion des Tables
                </h2>
                <div class="table-list">
                    <?php foreach ($tableInfo as $table): ?>
                    <div class="table-item">
                        <div class="table-name"><?php echo htmlspecialchars($table['table_name']); ?></div>
                        <div class="table-stats">
                            Lignes: <?php echo $table['table_rows']; ?><br>
                            Taille: <?php echo round($table['data_length'] / 1024, 2); ?> KB
                        </div>
                        <div style="margin-top: 10px;">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="truncate_table">
                                <input type="hidden" name="table_name" value="<?php echo $table['table_name']; ?>">
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir vider cette table ?')">
                                    <i class="fas fa-broom"></i> Vider
                                </button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="drop_table">
                                <input type="hidden" name="table_name" value="<?php echo $table['table_name']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('ATTENTION : Cette action supprimera définitivement la table. Êtes-vous sûr ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Outils avancés -->
            <div class="admin-card">
                <h2 class="card-title">
                    <i class="fas fa-tools"></i> Outils Avancés
                </h2>
                <div class="form-group">
                    <button type="button" class="btn btn-primary" onclick="exportDatabase()">
                        <i class="fas fa-download"></i> Exporter la BDD
                    </button>
                    <button type="button" class="btn btn-warning" onclick="showDatabaseInfo()">
                        <i class="fas fa-info-circle"></i> Infos BDD
                    </button>
                </div>
                <div id="database-info" style="display: none; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>Informations de la Base de Données</h4>
                    <p><strong>Nom :</strong> <?php echo DB_NAME; ?></p>
                    <p><strong>Hôte :</strong> <?php echo DB_HOST; ?></p>
                    <p><strong>Nombre de tables :</strong> <?php echo count($tables); ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportDatabase() {
            if (confirm('Voulez-vous exporter la base de données ?')) {
                window.location.href = 'export_database.php';
            }
        }

        function showDatabaseInfo() {
            const infoDiv = document.getElementById('database-info');
            infoDiv.style.display = infoDiv.style.display === 'none' ? 'block' : 'none';
        }

        // Confirmation pour les actions dangereuses
        document.addEventListener('DOMContentLoaded', function() {
            const dangerButtons = document.querySelectorAll('.btn-danger');
            dangerButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Cette action est irréversible. Êtes-vous sûr de vouloir continuer ?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>