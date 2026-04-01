<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$request_id = $_GET['request_id'] ?? null;
if (!$request_id) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

$db = (new Database())->getConnection();

try {
    // Basic check: user should only see their own requests OR admins see company requests
    $user_id = $_SESSION['user_id'];
    $company_id = $_SESSION['company_id'] ?? null;
    $user_type = $_SESSION['user_type'];

    $stmt_access = $db->prepare("SELECT user_id, target_company_id FROM contact_requests WHERE id = ?");
    $stmt_access->execute([$request_id]);
    $req = $stmt_access->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        echo json_encode(['success' => false, 'message' => 'Requête introuvable']);
        exit;
    }

    // Check sender's company
    $stmt_sender = $db->prepare("SELECT company_id FROM users WHERE id = ?");
    $stmt_sender->execute([$req['user_id']]);
    $sender_company_id = $stmt_sender->fetchColumn();

    $is_owner = ($req['user_id'] == $user_id);
    $is_target_company = ($company_id && $req['target_company_id'] == $company_id);
    $is_internal_manager = ($company_id && $sender_company_id == $company_id && $_SESSION['user_role'] === 'Administrator');

    if (!$is_owner && !$is_target_company && !$is_internal_manager) {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM support_messages WHERE request_id = ? ORDER BY created_at ASC");
    $stmt->execute([$request_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get company name for display
    $stmt_comp = $db->prepare("SELECT nom FROM users WHERE id = ?");
    $stmt_comp->execute([$req['target_company_id']]);
    $company_name = $stmt_comp->fetchColumn();

    echo json_encode([
        'success' => true, 
        'request' => [
            'id' => $request_id,
            'title' => $req['title'] ?? 'Sans titre',
            'status' => $req['status'] ?? 'pending',
            'company_name' => $company_name ?: 'Support Technique'
        ],
        'messages' => $messages
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
