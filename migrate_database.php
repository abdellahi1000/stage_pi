<?php
require_once 'include/db_connect.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Create entreprises table
    $db->exec("CREATE TABLE IF NOT EXISTS entreprises (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        secteur VARCHAR(100),
        taille VARCHAR(50),
        adresse TEXT,
        registre VARCHAR(100),
        num_fiscal VARCHAR(100),
        document_pdf VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Add entreprise_id and update role in users table
    // First, check if entreprise_id exists
    $columns = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('entreprise_id', $columns)) {
        $db->exec("ALTER TABLE users ADD COLUMN entreprise_id INT DEFAULT NULL AFTER id");
    }

    // Role column already exists but we might want to standardize it
    // The user wants roles: admin / employee / student
    // Current type_compte: etudiant, entreprise, admin
    
    // We will use 'role' column or 'type_compte'? 
    // The user explicitly said: role (admin / employee / student)
    // Let's modify the 'role' column to be an enum if possible, or just standard varchar
    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'employee', 'student') DEFAULT 'student'");

    // 3. Update offers table
    // Currently named 'offres' in stagematch.sql
    if (!in_array('entreprise_id', $db->query("SHOW COLUMNS FROM offres")->fetchAll(PDO::FETCH_COLUMN))) {
        $db->exec("ALTER TABLE offres ADD COLUMN entreprise_id INT DEFAULT NULL AFTER id");
    }

    // 4. Migration of existing enterprise data (optional but good for stability)
    // Find users who are 'entreprise' and create enterprise records for them
    $old_enterprise_users = $db->query("SELECT * FROM users WHERE type_compte = 'entreprise'")->fetchAll();
    foreach ($old_enterprise_users as $user) {
        // Check if already linked
        if (empty($user['entreprise_id'])) {
            $stmt = $db->prepare("INSERT INTO entreprises (name, secteur, taille, adresse, registre, num_fiscal) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user['nom'], 
                $user['industry_sector'], 
                $user['company_size'], 
                $user['adresse'], 
                $user['commercial_registration_number'], 
                $user['tax_identification_number']
            ]);
            $ent_id = $db->lastInsertId();
            
            $db->prepare("UPDATE users SET entreprise_id = ?, role = 'admin' WHERE id = ?")->execute([$ent_id, $user['id']]);
        }
    }

    // Update existing students
    $db->exec("UPDATE users SET role = 'student' WHERE type_compte = 'etudiant'");
    // Update existing system admins
    $db->exec("UPDATE users SET role = 'admin' WHERE type_compte = 'admin'");

    echo "Migration completed successfully!";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
