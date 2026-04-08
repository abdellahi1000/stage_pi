<?php
$host = '127.0.0.1';
$dbname = 'stagematch';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->query("SET NAMES utf8");
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables with company_id, entreprise_id or user_id:\n";
    foreach ($tables as $table) {
        $cols = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
        $found = [];
        foreach($cols as $col) {
            if(strpos($col['Field'], 'company_id') !== false || strpos($col['Field'], 'entreprise_id') !== false || strpos($col['Field'], 'user_id') !== false || strpos($col['Field'], 'employeur_id') !== false) {
                $found[] = $col['Field'];
            }
        }
        if($found) {
            echo "- $table: " . implode(', ', $found) . "\n";
        }
    }
} catch (Exception $e) { echo $e->getMessage(); }
?>
