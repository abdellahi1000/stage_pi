<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

$tables = ['company_emails', 'company_phones', 'company_social_links'];

foreach ($tables as $t) {
    try {
        // 1. Drop existing constraints
        $stmt = $db->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = '$t' 
            AND TABLE_SCHEMA = 'stagematch' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($constraints as $cname) {
            $db->exec("ALTER TABLE $t DROP FOREIGN KEY $cname");
            echo "Dropped $cname from $t\n";
        }

        // 2. Map 'entreprise_id' (old user_id) to actual 'entreprise_id'
        $db->exec("
            UPDATE $t a
            INNER JOIN users u ON a.entreprise_id = u.id
            SET a.entreprise_id = u.entreprise_id
            WHERE u.entreprise_id IS NOT NULL AND u.entreprise_id > 0
        ");
        echo "Mapped user_ids to entreprise_ids in $t\n";

        // 3. Delete orphans
        $db->exec("DELETE FROM $t WHERE entreprise_id NOT IN (SELECT id FROM entreprises)");
        echo "Deleted orphans in $t\n";

        // 4. Correct foreign key
        $db->exec("ALTER TABLE $t ADD CONSTRAINT fk_{$t}_entreprise FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE");
        echo "Added corrrect FK to $t\n";

    } catch (Exception $e) {
        echo "Error in $t: " . $e->getMessage() . "\n";
    }
}
