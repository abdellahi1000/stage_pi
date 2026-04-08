<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

try {
    echo "Starting update migration...\n";

    $stmt = $db->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('can_create_offers', $columns)) {
        echo "Adding 'can_create_offers' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN can_create_offers TINYINT(1) DEFAULT 1");
    }

    if (!in_array('can_delete_offers', $columns)) {
        echo "Adding 'can_delete_offers' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN can_delete_offers TINYINT(1) DEFAULT 1");
    }

    if (!in_array('role', $columns)) {
        echo "Adding 'role' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'employee'");
    }

    if (!in_array('company_id', $columns)) {
        echo "Adding 'company_id' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN company_id INT DEFAULT NULL");
    }

    // Ensure managers have company_id = their id
    $db->exec("UPDATE users SET role = 'Administrator' WHERE type_compte = 'entreprise' AND (role IS NULL OR role = 'manager')");
    $db->exec("UPDATE users SET company_id = id WHERE role = 'Administrator'");

    echo "Update migration completed successfully.\n";
}
catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
?>
