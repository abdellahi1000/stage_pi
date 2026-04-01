<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || ($_SESSION['user_type'] !== 'entreprise' && $_SESSION['user_type'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$company_id = $_SESSION['company_id'];
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $db->prepare("SELECT * FROM company_faqs WHERE company_id = :cid ORDER BY created_at DESC");
        $stmt->execute([':cid' => $company_id]);
        $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'faqs' => $faqs]);

    } elseif ($action === 'add') {
        if ($_SESSION['user_role'] !== 'Administrator') {
            echo json_encode(['success' => false, 'message' => 'Seul l\'administrateur peut ajouter des FAQs.']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $question = trim($data['question'] ?? '');
        $answer = trim($data['answer'] ?? '');

        if (!empty($question) && !empty($answer)) {
            $stmt = $db->prepare("INSERT INTO company_faqs (company_id, question, answer) VALUES (:cid, :question, :answer)");
            $stmt->execute([
                ':cid' => $company_id,
                ':question' => $question,
                ':answer' => $answer
            ]);
            echo json_encode(['success' => true, 'message' => 'FAQ ajoutée avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Les champs sont obligatoires.']);
        }
    } elseif ($action === 'delete') {
         if ($_SESSION['user_role'] !== 'Administrator') {
            echo json_encode(['success' => false, 'message' => 'Seul l\'administrateur peut supprimer des FAQs.']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if ($id) {
            $stmt = $db->prepare("DELETE FROM company_faqs WHERE id = :id AND company_id = :cid");
            $stmt->execute([':id' => $id, ':cid' => $company_id]);
            echo json_encode(['success' => true, 'message' => 'FAQ supprimée.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID manquant.']);
        }
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données.']);
}
?>
