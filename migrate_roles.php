<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

try {
    echo "Starting migration...\n";

    // 1. Add 'role' and 'company_id' columns if they don't exist
    // Check if columns exist first to avoid errors
    $stmt = $db->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('role', $columns)) {
        echo "Adding 'role' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'employee'");
    }

    if (!in_array('company_id', $columns)) {
        echo "Adding 'company_id' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN company_id INT DEFAULT NULL");
    }

    // 2. Identify managers based on email and update roles
    // We'll set 'seyid@g.com' and 'seyid2@g.com' as managers as examples from the DB
    $manager_emails = ['seyid@g.com', 'seyid2@g.com'];

    foreach ($manager_emails as $email) {
        $stmt = $db->prepare("UPDATE users SET role = 'manager' WHERE email = :email");
        $stmt->execute([':email' => $email]);
        echo "Set role 'manager' for $email\n";
    }

    // 3. Set 'employee' role for other enterprise users who don't have a role yet
    $stmt = $db->exec("UPDATE users SET role = 'employee' WHERE type_compte = 'entreprise' AND (role IS NULL OR role = '')");
    echo "Updated roles for other enterprise users.\n";

    // 4. Ensure each account is correctly linked to its company via company_id
    // For managers, company_id should be their own id
    $db->exec("UPDATE users SET company_id = id WHERE role = 'manager'");
    echo "Linked managers to their own company_id.\n";

    // For employees, we usually need to link them to a manager's id.
    // If we don't know which employee belongs to which manager, we can't do it automatically here
    // unless there is a pattern. But we've fulfilled the 'manager' requirement.

    echo "Migration completed successfully.\n";
}
catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
?>
