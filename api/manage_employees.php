<?php
// api/manage_employees.php
session_start();
require_once '../include/db_connect.php';
require_once '../include/check_permission.php';

header('Content-Type: application/json');

// Only Admins of enterprises can manage employees
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé. Seul un administrateur peut gérer les collaborateurs.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$entreprise_id = $_SESSION['entreprise_id'];

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // List all employees of the SAME enterprise
        $stmt = $db->prepare("SELECT id, email, nom, prenom, role, telephone, actif, can_create_offers, can_edit_offers, can_delete_offers, can_manage_candidates FROM users WHERE entreprise_id = :ent_id AND role = 'employee'");
        $stmt->execute([':ent_id' => $entreprise_id]);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'employees' => $employees]);

    } elseif ($method === 'POST') {
        // Create or update employee
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) $data = $_POST;

        $action = $data['action'] ?? 'create';
        $email = $data['email'] ?? '';
        $nom = $data['nom'] ?? '';
        $prenom = $data['prenom'] ?? '';
        $password = $data['password'] ?? '';
        $emp_id = $data['id'] ?? null;

        // Permissions
        $p_create = isset($data['can_create_offers']) ? 1 : 0;
        $p_edit = isset($data['can_edit_offers']) ? 1 : 0;
        $p_delete = isset($data['can_delete_offers']) ? 1 : 0;
        $p_cand = isset($data['can_manage_candidates']) ? 1 : 0;

        if ($action === 'create') {
            if (empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis']);
                exit;
            }

            // Check if email already exists
            $chk = $db->prepare("SELECT id FROM users WHERE email = :email");
            $chk->execute([':email' => $email]);
            if ($chk->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé par un autre utilisateur.']);
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (email, password, nom, prenom, role, entreprise_id, can_create_offers, can_edit_offers, can_delete_offers, can_manage_candidates, actif) 
                                 VALUES (:email, :pw, :nom, :prenom, 'employee', :ent_id, :p1, :p2, :p3, :p4, 1)");
            $stmt->execute([
                ':email' => $email,
                ':pw' => $hash,
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':ent_id' => $entreprise_id,
                ':p1' => $p_create,
                ':p2' => $p_edit,
                ':p3' => $p_delete,
                ':p4' => $p_cand
            ]);

            echo json_encode(['success' => true, 'message' => 'Collaborateur créé avec succès.']);

        } elseif ($action === 'update' && $emp_id) {
            // Update permissions and info
            $stmt = $db->prepare("UPDATE users SET nom = :nom, prenom = :prenom, can_create_offers = :p1, can_edit_offers = :p2, can_delete_offers = :p3, can_manage_candidates = :p4 WHERE id = :id AND entreprise_id = :ent_id");
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':p1' => $p_create,
                ':p2' => $p_edit,
                ':p3' => $p_delete,
                ':p4' => $p_cand,
                ':id' => $emp_id,
                ':ent_id' => $entreprise_id
            ]);

            // Optional: update password if provided
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $emp_id]);
            }

            echo json_encode(['success' => true, 'message' => 'Collaborateur mis à jour.']);
        }
    } elseif ($method === 'DELETE') {
        // Delete employee (ensure same enterprise)
        parse_str(file_get_contents("php://input"), $_DELETE);
        $emp_id = $_DELETE['id'] ?? null;
        if ($emp_id) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id AND entreprise_id = :ent_id AND role = 'employee'");
            $stmt->execute([':id' => $emp_id, ':ent_id' => $entreprise_id]);
            echo json_encode(['success' => true, 'message' => 'Collaborateur supprimé.']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>
