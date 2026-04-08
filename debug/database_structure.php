<?php
require_once '../include/db_connect.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = (new Database())->getConnection();
    
    echo "=== DATABASE STRUCTURE DEBUG ===\n\n";
    
    // Check users table structure
    echo "USERS TABLE STRUCTURE:\n";
    echo "========================\n";
    $columns = $db->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
    
    echo "\nSAMPLE USER DATA:\n";
    echo "=================\n";
    $sample = $db->query("SELECT * FROM users WHERE role = 'entreprise' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($sample) {
        foreach ($sample as $key => $value) {
            echo "- $key: $value\n";
        }
    } else {
        echo "No entreprise user found\n";
    }
    
    echo "\nOFFRES_STAGE TABLE STRUCTURE:\n";
    echo "==============================\n";
    $offer_columns = $db->query("DESCRIBE offres")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($offer_columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
    
    echo "\nENTREPRISE_ACHIEVEMENTS TABLE STRUCTURE:\n";
    echo "======================================\n";
    try {
        $ach_columns = $db->query("DESCRIBE entreprise_achievements")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($ach_columns as $col) {
            echo "- {$col['Field']} ({$col['Type']})\n";
        }
    } catch (Exception $e) {
        echo "Table entreprise_achievements does not exist: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
