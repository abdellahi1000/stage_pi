<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

function addColumn($db, $table, $column, $type) {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($stmt->rowCount() == 0) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $type");
            echo "Added $column to $table.\n";
        } else {
            echo "Column $column already exists in $table.\n";
        }
    } catch (Exception $e) {
        echo "Error on $table.$column: " . $e->getMessage() . "\n";
    }
}

addColumn($db, 'entreprises', 'hr_manager', 'VARCHAR(255) NULL');
addColumn($db, 'entreprises', 'adresse', 'TEXT NULL');
addColumn($db, 'entreprises', 'bio', 'TEXT NULL');
addColumn($db, 'entreprises', 'website_url', 'VARCHAR(500) NULL');
addColumn($db, 'entreprises', 'taille', 'VARCHAR(50) NULL');
addColumn($db, 'entreprises', 'secteur', 'VARCHAR(255) NULL');
