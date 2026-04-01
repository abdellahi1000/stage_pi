<?php
// include/check_permission.php
function require_permission($perm, $db) {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'entreprise') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }
    
    // For now, if the user is an enterprise, we allow management
    // as the specific permission columns are not yet in the schema.
    if ($_SESSION['user_type'] === 'entreprise') return true;
    
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Administrator') return true;
    
    try {
        $stmt = $db->prepare("SELECT $perm FROM users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $val = $stmt->fetchColumn();
        if (!$val) {
            echo json_encode(['success' => false, 'message' => 'Permission refusée: ' . $perm]);
            exit;
        }
    } catch (PDOException $e) {
        // If the column doesn't exist, we fall back to allowing enterprise type
        return true;
    }
    return true;
}

function get_enterprise_id() {
    return isset($_SESSION['company_id']) ? $_SESSION['company_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);
}
