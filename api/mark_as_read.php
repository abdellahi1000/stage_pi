<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];
$demand_id = $_POST['demand_id'] ?? null;

if (!$demand_id) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    $stmt = $db->prepare("UPDATE contact_requests SET has_new_reply = 0 WHERE id = ? AND user_id = ?");
    $stmt->execute([$demand_id, $user_id]);

    $stmtMsg = $db->prepare("UPDATE support_messages SET status = 'read' WHERE request_id = ? AND user_id = ? AND sender_type IN ('support', 'admin')");
    $stmtMsg->execute([$demand_id, $user_id]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
