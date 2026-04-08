<?php
/**
 * finalize_db.php - PERMANENT SYSTEM STABILIZATION
 * Fixes all database inconsistencies and ensures all necessary columns exist.
 */
require_once __DIR__ . '/include/db_connect.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    echo "Starting Database Stabilization...\n\n";

    // 1. Stabilize users table
    $user_queries = [
        "ALTER TABLE users MODIFY COLUMN type_compte ENUM('etudiant', 'entreprise', 'admin') NOT NULL DEFAULT 'etudiant'",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS verified_status TINYINT(1) DEFAULT 0",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS visibilite_entreprise TINYINT(1) DEFAULT 1",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS account_status ENUM('pending', 'email_verified', 'admin_approved', 'rejected') DEFAULT 'pending'",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS company_size VARCHAR(50) NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS industry_sector VARCHAR(100) NULL"
    ];

    foreach ($user_queries as $q) {
        try {
            $db->exec($q);
            echo "[SUCCESS] $q\n";
        } catch (PDOException $e) {
            echo "[STK] $q | Note: " . $e->getMessage() . "\n";
        }
    }

    // 2. Stabilize candidatures table (The most critical part)
    // First, ensure the status enum is correct and broad enough
    try {
        $db->exec("ALTER TABLE candidatures MODIFY COLUMN statut VARCHAR(50) DEFAULT 'pending'");
        echo "[SUCCESS] Candidatures statut converted to VARCHAR temporarily for migration\n";
    } catch (PDOException $e) {}

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
        $check = $db->query("SHOW COLUMNS FROM candidatures LIKE '$col'");
        if ($check->rowCount() == 0) {
            $db->exec("ALTER TABLE candidatures ADD $col $type");
            echo "[SUCCESS] Added $col to candidatures\n";
        }
    }

    // Normalize existing statuses
    $db->exec("UPDATE candidatures SET statut = 'accepted' WHERE statut IN ('accepte', 'ACCEPTE', 'ACCEPTED')");
    $db->exec("UPDATE candidatures SET statut = 'rejected' WHERE statut IN ('refuse', 'REFUSE', 'REJECTED')");
    $db->exec("UPDATE candidatures SET statut = 'pending' WHERE statut IN ('en_attente', 'PENDING', 'EN_ATTENTE')");
    $db->exec("UPDATE candidatures SET statut = 'closed' WHERE statut IN ('CLOSED', 'cloturee')");
    echo "[SUCCESS] Status values normalized\n";

    // Re-apply ENUM for performance and integrity
    try {
        $db->exec("ALTER TABLE candidatures MODIFY COLUMN statut ENUM('pending', 'accepted', 'rejected', 'closed') DEFAULT 'pending'");
        echo "[SUCCESS] Re-applied ENUM to candidatures.statut\n";
    } catch (PDOException $e) {
        echo "[ERROR] Failed to re-apply ENUM: " . $e->getMessage() . "\n";
    }

    // 3. Create notes table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS notes_candidatures (
        id INT AUTO_INCREMENT PRIMARY KEY,
        candidature_id INT NOT NULL,
        user_id INT NOT NULL,
        note TEXT NOT NULL,
        date_note TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (candidature_id) REFERENCES candidatures(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[SUCCESS] notes_candidatures table ensured\n";

    echo "\nSYSTEM STABILIZATION COMPLETE.\n";

} catch (PDOException $e) {
    die("FATAL ERROR: " . $e->getMessage());
}
