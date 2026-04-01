<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];
$request_id = $_POST['request_id'] ?? $_POST['demand_id'] ?? null;
$content = $_POST['message'] ?? '';

if (!$request_id || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    // Verify ownership
    $stmt = $db->prepare("SELECT user_id, last_message_at FROM contact_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $demand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$demand || $demand['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Demande non trouvée']);
        exit;
    }
    
    // Check message limit (3 per day per request)
    // Count user messages sent TODAY for this demand
    $countStmt = $db->prepare("SELECT COUNT(*) FROM support_messages WHERE request_id = ? AND sender_type = 'user' AND DATE(created_at) = CURDATE()");
    $countStmt->execute([$request_id]);
    $msgs_today = $countStmt->fetchColumn();
    
    // Original demand message is sent once (usually on Day 1). 
    // If it was sent Today, it counts towards the limit of 3.
    $origTagStmt = $db->prepare("SELECT COUNT(*) FROM contact_requests WHERE id = ? AND DATE(created_at) = CURDATE()");
    $origTagStmt->execute([$request_id]);
    $orig_today = $origTagStmt->fetchColumn();

    $total_today = $msgs_today + $orig_today;
    
    if ($total_today >= 3) {
        echo json_encode([
            'success' => false, 
            'message' => 'You have reached the daily message limit. You can send new messages tomorrow.',
            'daily_limit' => true
        ]);
        exit;
    }
    
    // Insert new message
    $ins = $db->prepare("INSERT INTO support_messages (request_id, user_id, sender_type, message_text) VALUES (?, ?, 'user', ?)");
    $ins->execute([$request_id, $user_id, $content]);
    
    // Reset notification and update last_message_at
    $upd = $db->prepare("UPDATE contact_requests SET status = 'pending', has_new_reply = 0, last_message_at = NOW() WHERE id = ?");
    $upd->execute([$request_id]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
