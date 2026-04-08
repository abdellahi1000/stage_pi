<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

$queries = [
    "ALTER TABLE users ADD COLUMN commercial_registration_number VARCHAR(100) NULL AFTER visibilite_entreprise",
    "ALTER TABLE users ADD COLUMN tax_identification_number VARCHAR(100) NULL AFTER commercial_registration_number",
    "ALTER TABLE users ADD COLUMN industry_sector VARCHAR(100) NULL AFTER tax_identification_number",
    "ALTER TABLE users ADD COLUMN company_size VARCHAR(50) NULL AFTER industry_sector",
    "ALTER TABLE users ADD COLUMN year_established VARCHAR(4) NULL AFTER company_size",
    "ALTER TABLE users ADD COLUMN commercial_registry_doc VARCHAR(255) NULL AFTER year_established",
    "ALTER TABLE users ADD COLUMN tax_document VARCHAR(255) NULL AFTER commercial_registry_doc",
    "ALTER TABLE users ADD COLUMN official_stamp_doc VARCHAR(255) NULL AFTER tax_document",
    "ALTER TABLE users ADD COLUMN verified_status BOOLEAN DEFAULT FALSE AFTER official_stamp_doc",
    "ALTER TABLE users ADD COLUMN account_status ENUM('pending', 'email_verified', 'admin_approved', 'rejected') DEFAULT 'pending' AFTER verified_status"
];

foreach($queries as $q) {
    try {
        $db->exec($q);
        echo "Executed: $q\n";
    } catch(PDOException $e) {
        echo "Error on $q: " . $e->getMessage() . "\n";
    }
}
