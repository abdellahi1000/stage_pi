<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

try {
    // 1. Get current constraints for entreprise_achievements
    $stmt = $db->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = 'entreprise_achievements' 
        AND TABLE_SCHEMA = 'stagematch' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($constraints as $cname) {
        try {
            $db->exec("ALTER TABLE entreprise_achievements DROP FOREIGN KEY $cname");
            echo "Dropped old constraint: $cname\n";
        } catch (Exception $e) {
            echo "Failed to drop $cname: " . $e->getMessage() . "\n";
        }
    }

    // 2. Ensure entreprise_id column exists (it should, from my last fix)
    // 3. Add the correct foreign key to 'entreprises' table
    try {
        $db->exec("ALTER TABLE entreprise_achievements ADD CONSTRAINT fk_achievements_entreprise FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE");
        echo "Successfully added fk_achievements_entreprise referencing entreprises(id)\n";
    } catch (Exception $e) {
        echo "Error adding constraint: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "General error: " . $e->getMessage() . "\n";
}
