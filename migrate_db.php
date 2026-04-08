<?php
require_once 'include/db_connect.php';
$database = new Database();
$db = $database->getConnection();

$queries = [
    "ALTER TABLE offres ADD COLUMN places_alternances INT DEFAULT 0",
    "ALTER TABLE offres ADD COLUMN archived_by_admin TINYINT(1) DEFAULT 0",
];

foreach ($queries as $q) {
    try {
        $db->exec($q);
        echo "Executed: $q\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "Skipped: $q (column already exists)\n";
        } else {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}
?>
