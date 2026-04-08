<?php
require_once 'include/db_connect.php';
$db = new Database();
$conn = $db->getConnection();
try {
    $conn->exec("ALTER TABLE candidatures ADD COLUMN acceptance_message TEXT NULL;");
    $conn->exec("ALTER TABLE candidatures ADD COLUMN acceptance_date DATETIME NULL;");
    $conn->exec("ALTER TABLE candidatures ADD COLUMN company_contact_email VARCHAR(255) NULL;");
    $conn->exec("ALTER TABLE candidatures ADD COLUMN company_contact_phone VARCHAR(50) NULL;");
    $conn->exec("ALTER TABLE candidatures ADD COLUMN company_whatsapp VARCHAR(50) NULL;");
    echo "Columns added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
