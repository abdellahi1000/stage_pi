<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM etudiant_documents WHERE user_id = :uid ORDER BY created_at DESC");
    $stmt->execute([':uid' => $user_id]);
    $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'documents' => $docs]);
} 
elseif ($method === 'POST') {
    // Check if action exists (delete)
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $doc_id = $_POST['id'];
        // Security check
        $check = $db->prepare("SELECT type, file_path FROM etudiant_documents WHERE id = :id AND user_id = :uid");
        $check->execute([':id' => $doc_id, ':uid' => $user_id]);
        $doc = $check->fetch(PDO::FETCH_ASSOC);
        
        if ($doc) {
            $type = $doc['type'];
            // Delete record
            $del = $db->prepare("DELETE FROM etudiant_documents WHERE id = :id");
            if ($del->execute([':id' => $doc_id])) {
                // Synchronize with profils table
                $col = ($type === 'cv') ? 'cv_path' : 'lettre_motivation_path';
                
                // Get the latest remaining document of this type
                $next = $db->prepare("SELECT file_path FROM etudiant_documents WHERE user_id = :uid AND type = :type ORDER BY created_at DESC LIMIT 1");
                $next->execute([':uid' => $user_id, ':type' => $type]);
                $next_path = $next->fetchColumn() ?: null;
                
                $upd = $db->prepare("UPDATE profils SET $col = :path WHERE user_id = :uid");
                $upd->execute([':path' => $next_path, ':uid' => $user_id]);
                
                echo json_encode(['success' => true, 'message' => 'Document supprimé']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
            }
        } else {

            echo json_encode(['success' => false, 'message' => 'Document non trouvé']);
        }
    } 
    else {
        // Upload logic
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Erreur upload']);
            exit;
        }
        
        $type = $_POST['type'] ?? 'cv'; // 'cv' or 'motivation'
        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
            echo json_encode(['success' => false, 'message' => 'Format non supporté']);
            exit;
        }
        
        $upload_dir = __DIR__ . '/../uploads/cvs/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $prefix = ($type === 'cv') ? 'cv_' : 'lm_';
        $filename = $prefix . $user_id . '_' . time() . '.' . $ext;
        $db_path = 'uploads/cvs/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $ins = $db->prepare("INSERT INTO etudiant_documents (user_id, type, file_path, file_name) VALUES (:uid, :type, :path, :name)");
            $ins->execute([
                ':uid' => $user_id,
                ':type' => $type,
                ':path' => $db_path,
                ':name' => $file['name']
            ]);
            
            // Also update profils table to keep the 'current' one compatible with old code
            $col = ($type === 'cv') ? 'cv_path' : 'lettre_motivation_path';
            $upd = $db->prepare("UPDATE profils SET $col = :path WHERE user_id = :uid");
            $upd->execute([':path' => $db_path, ':uid' => $user_id]);
            
            echo json_encode(['success' => true, 'message' => 'Document ajouté']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur système']);
        }
    }
}
?>
