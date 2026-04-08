<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

// Mapping achievements from old user_ids to their actual entreprise_id
try {
    // 1. Clear invalid rows before adding constraint (or update if possible)
    // Map current 'entreprise_id' (which was user_id) to actual 'entreprises.id'
    $db->exec("
        UPDATE entreprise_achievements a
        INNER JOIN users u ON a.entreprise_id = u.id
        SET a.entreprise_id = u.entreprise_id
        WHERE u.entreprise_id IS NOT NULL AND u.entreprise_id > 0
    ");
    echo "Mapped user_ids to entreprise_ids.\n";

    // 2. Delete any remaining invalid rows (those that don't match any entreprise)
    $db->exec("
        DELETE FROM entreprise_achievements 
        WHERE entreprise_id NOT IN (SELECT id FROM entreprises)
    ");
    echo "Deleted orphaned achievements.\n";

    // 3. Re-try adding foreign key
    $db->exec("ALTER TABLE entreprise_achievements ADD CONSTRAINT fk_achievements_entreprise FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE");
    echo "Successfully added fk_achievements_entreprise referencing entreprises(id)\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
