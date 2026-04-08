<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

try {
    echo "Updating offres schema...\n";

    $stmt = $db->query("SHOW COLUMNS FROM offres");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('specialization', $columns)) {
        echo "Adding 'specialization' column...\n";
        $db->exec("ALTER TABLE offres ADD COLUMN specialization VARCHAR(255) DEFAULT NULL");
    }

    if (!in_array('tags', $columns)) {
        echo "Adding 'tags' column...\n";
        $db->exec("ALTER TABLE offres ADD COLUMN tags TEXT DEFAULT NULL");
    }

    if (!in_array('archived_by_admin', $columns)) {
        echo "Adding 'archived_by_admin' column...\n";
        $db->exec("ALTER TABLE offres ADD COLUMN archived_by_admin TINYINT(1) DEFAULT 0");
    }

    echo "Migration completed successfully.\n";
}
catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
?>
