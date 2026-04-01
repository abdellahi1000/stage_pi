<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

$request_id = $_POST['request_id'] ?? null;
$content = $_POST['message'] ?? '';
$email = $_POST['email'] ?? null;

if (!$request_id || empty($content) || !$email) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    // Verify request exists and matches email
    $stmt = $db->prepare("SELECT id FROM contact_requests WHERE id = ? AND email = ?");
    $stmt->execute([$request_id, $email]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Demande non trouvée']);
        exit;
    }
    
    // Apply 3-message limit per day
    $countStmt = $db->prepare("SELECT COUNT(*) FROM support_messages WHERE request_id = ? AND sender_type = 'user' AND DATE(created_at) = CURDATE()");
    $countStmt->execute([$request_id]);
    if ($countStmt->fetchColumn() >= 3) {
        echo json_encode(['success' => false, 'message' => 'Limite de 3 messages par jour atteinte.']);
        exit;
    }
    
    // Insert
    $ins = $db->prepare("INSERT INTO support_messages (request_id, user_id, sender_type, message_text, status) VALUES (?, NULL, 'user', ?, 'read')");
    $ins->execute([$request_id, $content]);
    
    // Update request sync
    $db->prepare("UPDATE contact_requests SET status = 'pending', has_new_reply = 0, last_message_at = NOW() WHERE id = ?")->execute([$request_id]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
