<?php
$host = '127.0.0.1';
$dbname = 'stagematch';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create companies table
    $create_companies = "
    CREATE TABLE IF NOT EXISTS companies (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        owner_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($create_companies);
    echo "Table 'companies' created/checked.\n";

    // 2. Add email_employer to users
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'email_employer'")->fetchAll();
    if (count($cols) == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email_employer VARCHAR(255) NULL AFTER email");
        echo "Column 'email_employer' added to users.\n";
    }

    // 3. Migrate existing Administrator users of type 'entreprise' to companies
    // Each admin forms a company
    $stmt = $pdo->query("SELECT id, nom, email FROM users WHERE type_compte = 'entreprise' AND role = 'Administrator'");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($admins as $admin) {
        $check_comp = $pdo->prepare("SELECT id FROM companies WHERE owner_id = :oid");
        $check_comp->execute([':oid' => $admin['id']]);
        if ($check_comp->rowCount() == 0) {
            // Because company_id in users often points to the admin's user id, we will try to make the company id MATCH the admin user id
            // to preserve other relations if possible, but actually we can just insert and then update users.
            
            // Wait, users already has company_id. If an admin represents the company, their company_id is their own id.
            $insert_comp = $pdo->prepare("INSERT INTO companies (id, name, owner_id) VALUES (:id, :name, :oid)");
            // We force companies.id = users.id (the admin's id)
            $insert_comp->execute([
                ':id' => $admin['id'], // company id same as admin user id
                ':name' => $admin['nom'],
                ':oid' => $admin['id']
            ]);
            echo "Created company '" . $admin['nom'] . "' for admin ID " . $admin['id'] . "\n";
            
            // Also set email_employer to email
            $pdo->prepare("UPDATE users SET email_employer = email WHERE id = :id")->execute([':id' => $admin['id']]);
        }
    }

    // Update email_employer for managers as well (just copy email into email_employer if null)
    $pdo->exec("UPDATE users SET email_employer = email WHERE type_compte = 'entreprise' AND email_employer IS NULL");

    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
