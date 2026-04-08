<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();
try {
    $db->exec("ALTER TABLE support_messages ADD COLUMN status ENUM('unread', 'read') DEFAULT 'unread' AFTER message_text");
    echo "Support messages table updated with status column.";
} catch (PDOException $e) {
    echo "Error or Column Already Exists: " . $e->getMessage();
}
?>
