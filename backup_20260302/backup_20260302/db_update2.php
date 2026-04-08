<?php
require_once __DIR__ . '/include/db_connect.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Add missing columns to profils table
    $columns_to_add = [
        ['table' => 'profils', 'column' => 'statut_disponibilite', 'type' => "VARCHAR(100) DEFAULT 'disponible'"],
        ['table' => 'profils', 'column' => 'lettre_motivation_path', 'type' => "VARCHAR(255) DEFAULT NULL"],
        ['table' => 'profils', 'column' => 'domaine_formation', 'type' => "VARCHAR(150) DEFAULT NULL"],
        ['table' => 'preferences_utilisateur', 'column' => 'alertes_offres', 'type' => "TINYINT(1) DEFAULT 1"],
    ];

    foreach ($columns_to_add as $col) {
        $check = $db->query("SHOW COLUMNS FROM `{$col['table']}` LIKE '{$col['column']}'");
        if ($check->rowCount() == 0) {
            $db->exec("ALTER TABLE `{$col['table']}` ADD `{$col['column']}` {$col['type']}");
            echo "Added {$col['column']} to {$col['table']}\n";
        }
    }

    echo "Database updated successfully.\n";

} catch (PDOException $e) {
    echo "Error updating DB: " . $e->getMessage() . "\n";
}
?>
