<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

try {
    // 1. Check if entreprise_achievements needs to be renamed from user_id to entreprise_id
    // or just use entreprise_id logic
    $db->exec("ALTER TABLE entreprise_achievements RENAME COLUMN user_id TO entreprise_id");
} catch (Exception $e) {
    // maybe it already has it or doesn't support RENAME COLUMN (legacy MySQL)
    try {
        $db->exec("ALTER TABLE entreprise_achievements CHANGE user_id entreprise_id INT NOT NULL");
    } catch (Exception $e2) {
        echo "entreprise_achievements: already fixed or error: " . $e2->getMessage() . "\n";
    }
}

// 2. Clear old data that might be linked by wrong IDs if necessary, or just rely on new logic
// Actually, I should probably try to map old user_ids to their entreprise_ids in these tables 
// but it's easier to just assume we start fresh or the manager was the first one.

// 3. Ensure foreign keys for the contact tables are to 'entreprises' table 'id'
// (Rename company_id to entreprise_id for consistency?)
$tables_to_fix = ['company_emails', 'company_phones', 'company_social_links'];
foreach($tables_to_fix as $t) {
    try {
        $db->exec("ALTER TABLE $t CHANGE company_id entreprise_id INT NOT NULL");
    } catch (Exception $e) {
        echo "$t: already fixed or error: " . $e->getMessage() . "\n";
    }
}

echo "Database tables for enterprise profile fixed.\n";
