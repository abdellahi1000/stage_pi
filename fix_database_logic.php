<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

try {
    echo "Starting final database cleanup...\n";

    // 1. Ensure columns exist (just in case)
    $stmt = $db->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('password_admin', $columns)) {
        echo "Adding 'password_admin' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN password_admin VARCHAR(255) DEFAULT NULL");
    }
    if (!in_array('role', $columns)) {
        echo "Adding 'role' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT NULL");
    }
    if (!in_array('company_id', $columns)) {
        echo "Adding 'company_id' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN company_id INT DEFAULT NULL");
    }

    // 2. Strict Rules for Role
    // Students -> role = NULL
    $db->exec("UPDATE users SET role = NULL WHERE type_compte = 'etudiant'");
    echo "Set role = NULL for all students.\n";

    // Normal company account -> role = NULL
    // By default, everyone is NULL initially
    $db->exec("UPDATE users SET role = NULL WHERE type_compte = 'entreprise'");
    echo "Set role = NULL for all enterprise accounts initially.\n";

    // Company administrator -> role = 'Administrator'
    // We assume any account with a password_admin set IS an administrator
    $db->exec("UPDATE users SET role = 'Administrator' WHERE type_compte = 'entreprise' AND password_admin IS NOT NULL AND password_admin != ''");
    echo "Set role = 'Administrator' for enterprise accounts with an admin password.\n";

    // 3. Company IDs
    // Link administrator to the company (self-link if they are the primary account)
    $db->exec("UPDATE users SET company_id = id WHERE type_compte = 'entreprise' AND role = 'Administrator' AND company_id IS NULL");
    echo "Linked Administrators to their own company_id if NULL.\n";

    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
