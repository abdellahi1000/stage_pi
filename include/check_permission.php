<?php
// include/check_permission.php
function require_permission($perm, $db) {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'entreprise') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Administrator') return true;
    
    $stmt = $db->prepare("SELECT $perm FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $val = $stmt->fetchColumn();
    if (!$val) {
        echo json_encode(['success' => false, 'message' => 'Permission refusée: ' . $perm]);
        exit;
    }
    return true;
}

function get_enterprise_id() {
    return isset($_SESSION['company_id']) ? $_SESSION['company_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);
}
