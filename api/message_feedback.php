<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];
$message_id = $_POST['message_id'] ?? null;
$type = $_POST['type'] ?? ''; // 'like' or 'dislike'

if (!$message_id || !in_array($type, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    // Check if feedback already exists
    $check = $db->prepare("SELECT feedback_type FROM message_feedback WHERE message_id = ? AND user_id = ?");
    $check->execute([$message_id, $user_id]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        if ($existing['feedback_type'] === $type) {
            // Toggle off
            $db->prepare("DELETE FROM message_feedback WHERE message_id = ? AND user_id = ?")->execute([$message_id, $user_id]);
            $col = ($type === 'like' ? 'like_count' : 'dislike_count');
            $db->prepare("UPDATE support_messages SET $col = GREATEST(0, $col - 1) WHERE id = ?")->execute([$message_id]);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            // Switch type
            $db->prepare("UPDATE message_feedback SET feedback_type = ? WHERE message_id = ? AND user_id = ?")->execute([$type, $message_id, $user_id]);
            $oldCol = ($existing['feedback_type'] === 'like' ? 'like_count' : 'dislike_count');
            $newCol = ($type === 'like' ? 'like_count' : 'dislike_count');
            $db->prepare("UPDATE support_messages SET $oldCol = GREATEST(0, $oldCol - 1), $newCol = $newCol + 1 WHERE id = ?")->execute([$message_id]);
            echo json_encode(['success' => true, 'action' => 'switched']);
        }
    } else {
        // New feedback
        $db->prepare("INSERT INTO message_feedback (message_id, user_id, feedback_type) VALUES (?, ?, ?)")->execute([$message_id, $user_id, $type]);
        $col = ($type === 'like' ? 'like_count' : 'dislike_count');
        $db->prepare("UPDATE support_messages SET $col = $col + 1 WHERE id = ?")->execute([$message_id]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
