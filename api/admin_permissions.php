<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Administrator') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$company_id = $_SESSION['company_id'] ?? $_SESSION['user_id'] ?? 0;
$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        // List all other users (employees) for this company
        $stmt = $db->prepare("SELECT id, nom, email, prenom, can_create_offers, can_edit_offers, can_delete_offers, can_manage_candidates, can_block_users 
                              FROM users 
                              WHERE company_id = :cid AND id != :uid");
        $stmt->execute([':cid' => $company_id, ':uid' => $user_id]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'collaborators' => $users]);

    } elseif ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        $target_id = $data['user_id'] ?? null;
        $can_create = isset($data['can_create']) ? (int)$data['can_create'] : 0;
        $can_edit = isset($data['can_edit']) ? (int)$data['can_edit'] : 0;
        $can_delete = isset($data['can_delete']) ? (int)$data['can_delete'] : 0;
        $can_manage_candidates = isset($data['can_manage_candidates']) ? (int)$data['can_manage_candidates'] : 0;
        $can_block_users = isset($data['can_block_users']) ? (int)$data['can_block_users'] : 0;

        if ($target_id) {
            // Verify target user belongs to the same company
            $stmt_v = $db->prepare("SELECT id FROM users WHERE id = :tid AND company_id = :cid");
            $stmt_v->execute([':tid' => $target_id, ':cid' => $company_id]);
            
            if ($stmt_v->rowCount() > 0) {
                $stmt = $db->prepare("UPDATE users SET 
                                        can_create_offers = :create, 
                                        can_edit_offers = :edit,
                                        can_delete_offers = :delete,
                                        can_manage_candidates = :manage_candidates,
                                        can_block_users = :block_users 
                                      WHERE id = :tid");
                $stmt->execute([
                    ':create' => $can_create,
                    ':edit' => $can_edit,
                    ':delete' => $can_delete,
                    ':manage_candidates' => $can_manage_candidates,
                    ':block_users' => $can_block_users,
                    ':tid' => $target_id
                ]);
                echo json_encode(['success' => true, 'message' => "Permissions mises à jour"]);
            } else {
                echo json_encode(['success' => false, 'message' => "Utilisateur non trouvé ou accès refusé"]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => "Données invalides"]);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
