<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

try {
    // 1. Ensure 'hr_manager' column exists in 'entreprises'
    $db->exec("ALTER TABLE entreprises ADD COLUMN IF NOT EXISTS hr_manager VARCHAR(255) NULL");
    echo "Ensured 'hr_manager' column exists in 'entreprises'.\n";

    // 2. Also ensure 'adresse' column exists in 'entreprises' (just in case)
    $db->exec("ALTER TABLE entreprises ADD COLUMN IF NOT EXISTS adresse TEXT NULL");
    echo "Ensured 'adresse' column exists in 'entreprises'.\n";

} catch (Exception $e) {
    // Column may already exist, or other error.
    echo "Error: " . $e->getMessage() . "\n";
}
