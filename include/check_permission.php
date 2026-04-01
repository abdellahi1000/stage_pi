<?php
// include/check_permission.php
function require_permission($perm, $db) {
    $user_type = $_SESSION['user_type'] ?? '';
    $user_role = $_SESSION['user_role'] ?? '';

    // Allow both 'entreprise' and 'admin' types to pass through
    if ($user_type !== 'entreprise' && $user_type !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }

    // An Administrator (of any type) always passes - they have all permissions
    if ($user_role === 'Administrator') return true;

    // For non-admin employees, check the specific permission column
    try {
        $stmt = $db->prepare("SELECT `$perm` FROM users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $val = $stmt->fetchColumn();
        if (!$val) {
            echo json_encode(['success' => false, 'message' => 'Permission refusée: ' . $perm]);
            exit;
        }
    } catch (PDOException $e) {
        // If column doesn't exist yet, allow through
        return true;
    }
    return true;
}

function get_enterprise_id() {
    return isset($_SESSION['company_id']) ? $_SESSION['company_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);
}
