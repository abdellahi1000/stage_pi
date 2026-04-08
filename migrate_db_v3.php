<?php
require_once 'include/db_connect.php';
$database = new Database();
$db = $database->getConnection();

$queries = [
    "ALTER TABLE candidatures ADD COLUMN type_contrat VARCHAR(50) DEFAULT 'Stage'",
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
