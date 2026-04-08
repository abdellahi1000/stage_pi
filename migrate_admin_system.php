<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

try {
    echo "Starting migration...\n";

    $stmt = $db->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('company_id', $columns)) {
        echo "Adding 'company_id' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN company_id INT DEFAULT NULL");
    }

    if (!in_array('role', $columns)) {
        echo "Adding 'role' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'employee'");
    }

    if (!in_array('password_admin', $columns)) {
        echo "Adding 'password_admin' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN password_admin VARCHAR(255) DEFAULT NULL");
    }

    // Create blocked_students table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS blocked_students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        student_id INT NOT NULL,
        reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_block (company_id, student_id)
    )");
    echo "Ensured 'blocked_students' table exists.\n";

    // Initialize company_id for existing enterprise accounts
    $db->exec("UPDATE users SET company_id = id WHERE type_compte = 'entreprise' AND company_id IS NULL");
    
    // Set default role for existing enterprise accounts
    $db->exec("UPDATE users SET role = 'employee' WHERE type_compte = 'entreprise' AND (role IS NULL OR role = '')");

    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
?>
