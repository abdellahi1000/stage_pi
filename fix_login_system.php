<?php
require_once 'include/db_connect.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Ensure columns exist
    $columns = [
        'password_admin' => "VARCHAR(255) DEFAULT NULL AFTER password",
        'role' => "VARCHAR(50) DEFAULT NULL AFTER type_compte",
        'company_id' => "INT DEFAULT NULL AFTER role"
    ];

    foreach ($columns as $col => $definition) {
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE '$col'");
        if ($stmt->rowCount() === 0) {
            $db->exec("ALTER TABLE users ADD COLUMN $col $definition");
        }
    }

    // 2. Initialize roles and company_id
    // Enterprise accounts link to themselves as company_id by default
    $db->exec("UPDATE users SET company_id = id WHERE type_compte = 'entreprise' AND company_id IS NULL");
    
    // Set a default password_admin for existing enterprises for testing (e.g. "admin123")
    // In production, this should be set by the user or via a secure process.
    $default_admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $db->exec("UPDATE users SET password_admin = '$default_admin_hash' WHERE type_compte = 'entreprise' AND (password_admin IS NULL OR password_admin = '')");

    echo "Database structure updated successfully.\n";
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>
