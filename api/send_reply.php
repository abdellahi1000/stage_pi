<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    $user_id = $_SESSION['user_id'] ?? null;
    $request_id = $_POST['request_id'] ?? null;
    $message = trim($_POST['message'] ?? '');

    if (!$user_id || !$request_id || empty($message)) {
        throw new Exception("Données manquantes ou message vide.");
    }

    // Verify ownership or target
    $stmt = $db->prepare("SELECT id, user_id FROM contact_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        throw new Exception("Demande introuvable.");
    }

    // Determine sender_type
    // If user is the original requester
    $sender_type = ($request['user_id'] == $user_id) ? 'user' : 'support';
    
    // Actually, for students/enterprise replying to their own ticket, it's 'user'
    // If it's the target company, we might need another type, but let's stick to 'user' for now for the student side.

    $stmt_ins = $db->prepare("INSERT INTO support_messages (request_id, user_id, sender_type, message_text, status) VALUES (?, ?, ?, ?, 'unread')");
    $stmt_ins->execute([$request_id, $user_id, 'user', $message]);

    // Update last_message_at and set status back to pending if user is replying
    $stmt_upd = $db->prepare("UPDATE contact_requests SET status = 'pending', has_new_reply = 0, last_message_at = NOW() WHERE id = ?");
    $stmt_upd->execute([$request_id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
