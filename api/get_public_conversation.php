<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

$email = $_GET['email'] ?? $_POST['email'] ?? null;

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email requis']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    // Find demands for this email
    $stmt = $db->prepare("SELECT id, title, status, has_new_reply, created_at, message FROM contact_requests WHERE email = ? AND user_type = 'problem' ORDER BY created_at DESC");
    $stmt->execute([$email]);
    $demands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($demands as &$d) {
        $mStmt = $db->prepare("SELECT * FROM support_messages WHERE request_id = ? ORDER BY created_at ASC");
        $mStmt->execute([$d['id']]);
        $msgs = $mStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add original message as first item
        array_unshift($msgs, [
            'id' => 0,
            'sender_type' => 'user',
            'message_text' => $d['message'],
            'created_at' => $d['created_at'],
            'is_original' => true
        ]);
        
        $d['messages'] = $msgs;
    }

    echo json_encode(['success' => true, 'demands' => $demands]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
