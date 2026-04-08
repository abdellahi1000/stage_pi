<?php
$host = '127.0.0.1';
$dbname = 'stagematch';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $users = $pdo->query("SELECT id, nom, email, password_admin, company_id, role, type_compte FROM users WHERE type_compte = 'entreprise'")->fetchAll(PDO::FETCH_ASSOC);
    echo "Enterprises:\n";
    print_r($users);
    
    // Check if companies table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'companies'")->fetchAll(PDO::FETCH_COLUMN);
    if(count($tables) > 0) {
        $comps = $pdo->query("SELECT * FROM companies")->fetchAll(PDO::FETCH_ASSOC);
        echo "\nCompanies:\n";
        print_r($comps);
    } else {
        echo "\nTable 'companies' does not exist.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
