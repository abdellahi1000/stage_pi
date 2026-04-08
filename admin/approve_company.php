<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($id <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

try {
    if ($action === 'approve') {
        // Approve means: account_status = 'admin_approved', verified_status = 1, actif = 1
        $query = "UPDATE users SET account_status = 'admin_approved', verified_status = 1, actif = 1 WHERE id = :id AND type_compte = 'entreprise'";
    } else {
        // Reject means: account_status = 'rejected', verified_status = 0, actif = 0
        $query = "UPDATE users SET account_status = 'rejected', verified_status = 0, actif = 0 WHERE id = :id AND type_compte = 'entreprise'";
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucune mise à jour effectuée. Entreprise introuvable ou statut déjà défini.']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur technique: ' . $e->getMessage()]);
}
?>
