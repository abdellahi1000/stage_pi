<?php
/**
 * definitive_stabilize.php - FINAL DATABASE FIX
 * Uses standard MySQL 8.0 syntax (compatible with MariaDB too).
 */
require_once __DIR__ . '/include/db_connect.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    echo "Starting Definitive Database Stabilization...\n\n";

    // --- 1. TABLES ENHANCEMENT ---

    // Function to check and add column
    function addColumnIfMissing($db, $table, $column, $definition) {
        $check = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($check->rowCount() == 0) {
            $db->exec("ALTER TABLE `$table` ADD `$column` $definition");
            echo "[SUCCESS] Added `$column` to `$table`.\n";
            return true;
        }
        return false;
    }

    // A. COMPANIES TABLE
    $db->exec("CREATE TABLE IF NOT EXISTS companies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        website VARCHAR(255),
        logo VARCHAR(255),
        industry_sector VARCHAR(100),
        company_size VARCHAR(50),
        address TEXT,
        owner_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[SUCCESS] Companies table verified.\n";

    // B. USERS TABLE
    addColumnIfMissing($db, 'users', 'company_id', 'INT NULL');
    addColumnIfMissing($db, 'users', 'role', "ENUM('Administrator', 'Manager', 'Employee') DEFAULT 'Employee'");
    addColumnIfMissing($db, 'users', 'password_admin', 'VARCHAR(255) NULL');
    addColumnIfMissing($db, 'users', 'verified_status', 'TINYINT(1) DEFAULT 0');
    addColumnIfMissing($db, 'users', 'account_status', "ENUM('pending', 'email_verified', 'admin_approved', 'rejected') DEFAULT 'pending'");
    addColumnIfMissing($db, 'users', 'industry_sector', 'VARCHAR(100) NULL');
    addColumnIfMissing($db, 'users', 'company_size', 'VARCHAR(50) NULL');
    addColumnIfMissing($db, 'users', 'year_established', 'INT NULL');
    addColumnIfMissing($db, 'users', 'commercial_registry_doc', 'VARCHAR(255) NULL');
    addColumnIfMissing($db, 'users', 'tax_document', 'VARCHAR(255) NULL');
    addColumnIfMissing($db, 'users', 'official_stamp_doc', 'VARCHAR(255) NULL');
    
    // Ensure type_compte enum is correct
    try {
        $db->exec("ALTER TABLE users MODIFY COLUMN type_compte ENUM('etudiant', 'entreprise', 'admin') NOT NULL DEFAULT 'etudiant'");
        echo "[SUCCESS] Users type_compte enum stabilized.\n";
    } catch (PDOException $e) {
        echo "[INFO] type_compte already correct or error: " . $e->getMessage() . "\n";
    }

    // C. OFFRES_STAGE TABLE
    addColumnIfMissing($db, 'offres', 'company_id', 'INT NULL');
    addColumnIfMissing($db, 'offres', 'type_contrat', "ENUM('Stage', 'Alternance', 'CDI', 'CDD') DEFAULT 'Stage'");

    // D. CANDIDATURES TABLE
    $cand_cols = [
        "acceptance_message" => "TEXT NULL",
        "acceptance_date" => "DATETIME NULL",
        "company_contact_email" => "VARCHAR(255) NULL",
        "company_contact_phone" => "VARCHAR(50) NULL",
        "company_whatsapp" => "VARCHAR(50) NULL",
        "vu_par_entreprise" => "TINYINT(1) DEFAULT 0",
        "vu_par_etudiant" => "TINYINT(1) DEFAULT 0",
        "cv_specifique" => "VARCHAR(255) NULL"
    ];
    foreach ($cand_cols as $col => $type) {
        addColumnIfMissing($db, 'candidatures', $col, $type);
    }

    // E. SUPPORT MESSAGES TABLE
    $db->exec("CREATE TABLE IF NOT EXISTS support_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        contact_request_id INT NOT NULL,
        user_id INT NULL,
        sender_type ENUM('user', 'admin') NOT NULL,
        message_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[SUCCESS] Support messages table verified.\n";

    // --- 2. DATA MIGRATION ---

    // Migrate existing enterprise users to companies structure if needed
    $stmt = $db->query("SELECT id, nom, prenom, type_compte FROM users WHERE type_compte = 'entreprise' AND company_id IS NULL");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Create a company for this owner
        $comp_stmt = $db->prepare("INSERT INTO companies (name, owner_id) VALUES (?, ?)");
        $comp_stmt->execute([$row['nom'], $row['id']]);
        $company_id = $db->lastInsertId();
        
        // Link owner to company
        $db->exec("UPDATE users SET company_id = $company_id, role = 'Administrator' WHERE id = " . $row['id']);
        
        // Migrate their offers
        $db->exec("UPDATE offres SET company_id = $company_id WHERE user_id = " . $row['id']);
        
        echo "[MIGRATED] User ID " . $row['id'] . " linked to new Company ID $company_id.\n";
    }

    // Fix orphaned offers
    $db->exec("UPDATE offres o JOIN users u ON o.user_id = u.id SET o.company_id = u.company_id WHERE o.company_id IS NULL AND u.company_id IS NOT NULL");
    echo "[SUCCESS] Orphaned offers linked to companies.\n";

    echo "\nALL SYSTEMS STABILIZED.\n";

} catch (PDOException $e) {
    die("FATAL ERROR: " . $e->getMessage());
}
