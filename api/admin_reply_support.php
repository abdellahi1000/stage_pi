<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require_once '../include/session.php';
require_once '../include/db_connect.php';
require_once '../include/check_permission.php';

header('Content-Type: application/json');

// Session & Permission check
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'entreprise' || $_SESSION['user_role'] !== 'Administrator') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit;
}

$request_id = $_POST['request_id'] ?? null;
$reply_message = $_POST['reply_message'] ?? null;
$company_id = get_enterprise_id();

if (!$request_id || !$reply_message) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Des données sont manquantes.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Base de données indisponible.");
    }

    // Security: Check if request belongs to this company (target or internal)
    $stmt = $db->prepare("SELECT cr.id FROM contact_requests cr 
                          LEFT JOIN users u ON cr.user_id = u.id
                          WHERE cr.id = ? AND (cr.target_company_id = ? OR (u.company_id = ? AND cr.user_type = 'enterprise'))");
    $stmt->execute([$request_id, $company_id, $company_id]);
    
    if (!$stmt->fetch()) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Requête introuvable ou accès refusé.']);
        exit;
    }

    // Update main request status
    $stmt = $db->prepare("UPDATE contact_requests SET admin_reply = ?, status = 'answered', has_new_reply = 1, last_message_at = NOW() WHERE id = ?");
    $stmt->execute([$reply_message, $request_id]);

    // Insert into discussion thread
    $stmt_msg = $db->prepare("INSERT INTO support_messages (request_id, user_id, sender_type, message_text) VALUES (?, ?, 'admin', ?)");
    $stmt_msg->execute([$request_id, $_SESSION['user_id'], $reply_message]);

    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Réponse envoyée.']);
} catch (Throwable $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
