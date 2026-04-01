<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];
$session_type = $_SESSION['user_type'] ?? '';

try {
    $db = (new Database())->getConnection();
    
    // Map session type to contact_requests user_type
    $db_type = ($session_type === 'entreprise') ? 'enterprise' : 'student';

    $stmt = $db->prepare("SELECT id, title, status, has_new_reply, created_at FROM contact_requests WHERE user_id = ? AND user_type = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id, $db_type]);
    $demands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($demands as &$demand) {
        // Fetch support messages
        $msgStmt = $db->prepare("SELECT id, sender_type, message_text, created_at, like_count, dislike_count FROM support_messages WHERE request_id = ? ORDER BY created_at ASC");
        $msgStmt->execute([$demand['id']]);
        $demand['messages'] = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

        // Include the original message from contact_requests
        $origStmt = $db->prepare("SELECT message, created_at FROM contact_requests WHERE id = ?");
        $origStmt->execute([$demand['id']]);
        $orig = $origStmt->fetch(PDO::FETCH_ASSOC);
        
        $first_msg = [
            'id' => 0,
            'sender_type' => 'user',
            'message_text' => $orig['message'] ?? '',
            'created_at' => $orig['created_at'] ?? date('Y-m-d H:i:s'),
            'is_original' => true
        ];
        
        array_unshift($demand['messages'], $first_msg);

        // Add feedback status for each admin message
        foreach ($demand['messages'] as &$msg) {
            if ($msg['sender_type'] === 'admin') {
                $fStmt = $db->prepare("SELECT feedback_type FROM message_feedback WHERE message_id = ? AND user_id = ?");
                $fStmt->execute([$msg['id'], $user_id]);
                $feedback = $fStmt->fetch();
                $msg['user_feedback'] = $feedback ? $feedback['feedback_type'] : null;
            }
        }
    }
    
    echo json_encode(['success' => true, 'demands' => $demands]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
