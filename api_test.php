<?php
require_once __DIR__ . '/include/db_connect.php';
try {
    $db = (new Database())->getConnection();
    $stmt = $db->query("SELECT id, title, status, has_new_reply, created_at,
                                 (SELECT COUNT(*) FROM support_messages WHERE request_id = contact_requests.id AND sender_type IN ('admin', 'support') AND status = 'unread') as unread_count
                          FROM contact_requests ORDER BY created_at DESC LIMIT 5");
    $demands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['demands' => $demands], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
