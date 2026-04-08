<?php
require_once __DIR__ . '/include/db_connect.php';
try {
    $db = (new Database())->getConnection();
    
    // Check support_messages
    $stmt = $db->query("DESCRIBE support_messages");
    echo "support_messages:\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    // Check contact_requests
    $stmt = $db->query("DESCRIBE contact_requests");
    echo "\ncontact_requests:\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
