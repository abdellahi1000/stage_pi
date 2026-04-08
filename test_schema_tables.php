<?php
$host = '127.0.0.1';
$dbname = 'stagematch';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables:\n";
    foreach ($tables as $table) {
        if ($table == 'users' || $table == 'entreprises' || $table == 'companies' || strpos($table, 'compani') !== false) {
            echo "\nTable: $table\n";
            $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                echo "  " . $col['Field'] . " - " . $col['Type'] . "\n";
            }
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
