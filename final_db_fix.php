<?php
require_once 'include/db_connect.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "Starting Database Migration...<br>";

    // 1. Create entreprises table
    $db->exec("CREATE TABLE IF NOT EXISTS entreprises (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        registration_number VARCHAR(255),
        identification_number VARCHAR(255),
        full_address TEXT,
        activity_sector VARCHAR(255),
        company_type VARCHAR(255),
        creation_year VARCHAR(4),
        official_document VARCHAR(255),
        -- Legacy support columns
        secteur VARCHAR(255),
        taille VARCHAR(255),
        adresse TEXT,
        registre VARCHAR(255),
        num_fiscal VARCHAR(255),
        documents JSON,
        document_pdf VARCHAR(255),
        doc_registry VARCHAR(255),
        doc_tax VARCHAR(255),
        doc_stamp VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $ent_cols = $db->query("SHOW COLUMNS FROM entreprises")->fetchAll(PDO::FETCH_COLUMN);
    $cols_to_add = [
        'registration_number'   => 'VARCHAR(255) AFTER name',
        'identification_number' => 'VARCHAR(255) AFTER registration_number',
        'full_address'          => 'TEXT AFTER identification_number',
        'activity_sector'       => 'VARCHAR(255) AFTER full_address',
        'company_type'          => 'VARCHAR(255) AFTER activity_sector',
        'creation_year'         => 'VARCHAR(4) AFTER company_type',
        'official_document'     => 'VARCHAR(255) AFTER creation_year',
        'doc_registry'          => 'VARCHAR(255) AFTER num_fiscal',
        'doc_tax'               => 'VARCHAR(255) AFTER doc_registry',
        'doc_stamp'             => 'VARCHAR(255) AFTER doc_tax',
        'document_pdf'          => 'VARCHAR(255) AFTER doc_stamp',
        'documents'             => 'JSON AFTER document_pdf',
        'slogan'                => 'VARCHAR(255) AFTER name',
        'website_url'           => 'VARCHAR(255) AFTER slogan',
        'bio'                   => 'TEXT AFTER website_url',
        'photo_profil'          => 'VARCHAR(255) AFTER bio'
    ];
    foreach ($cols_to_add as $col => $def) {
        if (!in_array($col, $ent_cols)) {
            $db->exec("ALTER TABLE entreprises ADD COLUMN $col $def");
        }
    }
    echo "✔ Table 'entreprises' validated/created.<br>";

    // 2. Fix users table
    // Update existing records to match the new role system before modifying column to ENUM
    $db->exec("UPDATE users SET role = 'student' WHERE type_compte = 'etudiant' OR role = 'user'");
    $db->exec("UPDATE users SET role = 'admin' WHERE type_compte = 'admin' OR type_compte = 'entreprise'");
    
    $users_cols = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('entreprise_id', $users_cols)) {
        $db->exec("ALTER TABLE users ADD COLUMN entreprise_id INT DEFAULT NULL AFTER id");
        echo "✔ Column 'entreprise_id' added to 'users'.<br>";
    }

    // Standardize role column to ENUM
    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'employee', 'student') DEFAULT 'student'");
    echo "✔ Column 'role' standardized in 'users'.<br>";

    // 3. Fix offres table (Rename from offres if exists)
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('offres', $tables) && !in_array('offres', $tables)) {
        $db->exec("RENAME TABLE offres TO offres");
        echo "✔ Table 'offres' renamed to 'offres'.<br>";
    } elseif (!in_array('offres', $tables)) {
        $db->exec("CREATE TABLE offres (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            entreprise_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        echo "✔ Table 'offres' created.<br>";
    }

    // Ensure 'offres' has 'entreprise_id' and 'title' (titre)
    $offres_cols = $db->query("SHOW COLUMNS FROM offres")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('entreprise_id', $offres_cols)) {
        $db->exec("ALTER TABLE offres ADD COLUMN entreprise_id INT AFTER id");
    }
    // Rename titre to title if strictly required, but for compatibility I'll keep both or use title as primary
    if (in_array('titre', $offres_cols) && !in_array('title', $offres_cols)) {
        $db->exec("ALTER TABLE offres CHANGE titre title VARCHAR(255)");
        echo "✔ Column 'titre' renamed to 'title' in 'offres'.<br>";
    }

    // New Fix: Make legacy columns nullable in offres
    try {
        $db->exec("ALTER TABLE offres MODIFY COLUMN user_id INT NULL");
        $db->exec("ALTER TABLE offres MODIFY COLUMN entreprise VARCHAR(255) NULL");
        $db->exec("ALTER TABLE offres MODIFY COLUMN localisation VARCHAR(255) NULL");
    } catch (PDOException $e) {}

    // 4. Migration of existing data
    // Link users to entries in 'entreprises'
    $stmt = $db->query("SELECT * FROM users WHERE type_compte = 'entreprise' AND entreprise_id IS NULL");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        // Create entreprise record
        $ins = $db->prepare("INSERT INTO entreprises (name, secteur, taille, adresse, registre, num_fiscal) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([
            $user['nom'] ?? 'Inconnue',
            $user['industry_sector'] ?? '',
            $user['company_size'] ?? '',
            $user['adresse'] ?? '',
            $user['commercial_registration_number'] ?? '',
            $user['tax_identification_number'] ?? ''
        ]);
        $ent_id = $db->lastInsertId();
        
        // Update user
        $upd = $db->prepare("UPDATE users SET entreprise_id = ?, role = 'admin' WHERE id = ?");
        $upd->execute([$ent_id, $user['id']]);
    }

    // 5. Add Constraints (Foreign Keys)
    try {
        // Users to Entreprises
        $db->exec("ALTER TABLE users ADD CONSTRAINT fk_user_entreprise FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE SET NULL");
        echo "✔ Foreign Key 'fk_user_entreprise' added.<br>";
    } catch (PDOException $e) { /* already exists or error */ }

    try {
        // Offres to Entreprises
        $db->exec("ALTER TABLE offres ADD CONSTRAINT fk_offre_entreprise FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE");
        echo "✔ Foreign Key 'fk_offre_entreprise' added.<br>";
    } catch (PDOException $e) { /* already exists or error */ }

    echo "✔ Data migration and relationships completed.<br>";
    echo "<b>All Backend Fixes Applied Successfully!</b>";

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>
