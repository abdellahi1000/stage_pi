<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Administrator') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $db->prepare("SELECT email, nom, prenom, telephone, photo_profil, bio FROM users WHERE id = :uid");
        $stmt->execute([':uid' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'user' => $user]);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) $data = $_POST;

        $email = $data['email'] ?? '';
        $nom = $data['nom'] ?? '';
        $prenom = $data['prenom'] ?? '';
        $telephone = $data['telephone'] ?? '';
        $bio = $data['bio'] ?? '';
        $current_pw = $data['current_password'] ?? '';
        $new_pw = $data['new_password'] ?? '';
        $confirm_pw = $data['confirm_password'] ?? '';

        // 1. Update Profile Info
        $stmt = $db->prepare("UPDATE users SET email = :email, nom = :nom, prenom = :prenom, telephone = :tel, bio = :bio WHERE id = :uid");
        $stmt->execute([
            ':email' => $email, 
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':tel' => $telephone,
            ':bio' => $bio,
            ':uid' => $user_id
        ]);
        
        $_SESSION['user_email'] = $email;
        $_SESSION['user_nom'] = $nom;
        $_SESSION['user_prenom'] = $prenom;

        // 2. Update Password if provided
        if ($new_pw) {
            if ($new_pw !== $confirm_pw) {
                echo json_encode(['success' => false, 'message' => 'Les nouveaux mots de passe ne correspondent pas']);
                exit;
            }

            // Verify current password
            $stmt_v = $db->prepare("SELECT password FROM users WHERE id = :uid");
            $stmt_v->execute([':uid' => $user_id]);
            $hash = $stmt_v->fetchColumn();

            if ($hash && !password_verify($current_pw, $hash)) {
                echo json_encode(['success' => false, 'message' => 'Le mot de passe actuel est incorrect']);
                exit;
            }

            // Set new password
            $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = :pw WHERE id = :uid");
            $stmt->execute([':pw' => $new_hash, ':uid' => $user_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Compte mis à jour avec succès']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
