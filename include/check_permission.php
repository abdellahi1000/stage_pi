<?php
// include/check_permission.php
function require_permission($perm, $db) {
    $user_role = $_SESSION['user_role'] ?? '';

    // Only 'admin' and 'employee' roles can access enterprise features
    if ($user_role !== 'admin' && $user_role !== 'employee') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }

    // Admins always have all permissions
    if ($user_role === 'admin') return true;

    // For employees, check their specific permission in users table
    try {
        $stmt = $db->prepare("SELECT `$perm` FROM users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $val = $stmt->fetchColumn();
        if (!$val) {
            echo json_encode(['success' => false, 'message' => 'Permission refusée: ' . $perm]);
            exit;
        }
    } catch (PDOException $e) {
        return true; // Default to allow if permission system is missing columns
    }
    return true;
}

function get_enterprise_id() {
    return $_SESSION['entreprise_id'] ?? 0;
}
