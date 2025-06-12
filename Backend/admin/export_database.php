<?php
require_once 'check_admin.php';

try {
    $host = '127.0.0.1:3308';
    $dbname = 'checkmykicks';
    $username = 'root';
    $password = 'root';
    
    // Récupérer toutes les tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $export = "-- Export de la base de données CheckMyKicks\n";
    $export .= "-- Généré le " . date('Y-m-d H:i:s') . "\n\n";
    $export .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        // Structure de la table
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $createTable = $stmt->fetch();
        $export .= "-- Structure de la table `$table`\n";
        $export .= "DROP TABLE IF EXISTS `$table`;\n";
        $export .= $createTable['Create Table'] . ";\n\n";
        
        // Données de la table
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $export .= "-- Données de la table `$table`\n";
            $columns = array_keys($rows[0]);
            $export .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $rowValues = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $rowValues[] = 'NULL';
                    } else {
                        $rowValues[] = "'" . addslashes($value) . "'";
                    }
                }
                $values[] = '(' . implode(', ', $rowValues) . ')';
            }
            $export .= implode(",\n", $values) . ";\n\n";
        }
    }
    
    $export .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Définir les en-têtes pour le téléchargement
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="checkmykicks_export_' . date('Y-m-d_H-i-s') . '.sql"');
    header('Content-Length: ' . strlen($export));
    
    echo $export;
    
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de l'exportation : " . $e->getMessage();
    header('Location: dashboard.php');
    exit();
}
?> 