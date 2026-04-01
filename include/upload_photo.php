<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement']);
    exit;
}

$file = $_FILES['photo'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Type de fichier non supporté. Utilisez JPG, PNG, GIF ou WEBP.']);
    exit;
}

if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Le fichier est trop volumineux (Max 5MB).']);
    exit;
}

// Ensure upload directory exists (path relative to this script)
$upload_dir = __DIR__ . '/../uploads/profiles/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'user_' . $_SESSION['user_id'] . '_' . uniqid() . '.' . $extension;
$filepath = $upload_dir . $filename;
$db_path = 'uploads/profiles/' . $filename; // Path to store in DB (relative to site root)

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $action = $_POST['action'] ?? 'profile';
        
        if ($action === 'upload_logo') {
            $company_id = $_SESSION['company_id'];
            if (!$company_id) {
                echo json_encode(['success' => false, 'message' => 'Aucune entreprise associée.']);
                exit;
            }
            $stmt = $db->prepare("UPDATE users SET photo_profil = :photo WHERE company_id = :cid");
            $stmt->bindParam(':photo', $db_path);
            $stmt->bindParam(':cid', $company_id);
            $stmt->execute();
            
            $_SESSION['photo_profil'] = $db_path;
            echo json_encode([
                'success' => true, 
                'message' => 'Logo de l\'entreprise mis à jour',
                'photo_url' => '../' . $db_path
            ]);
        } else {
            // Update user record
            $stmt = $db->prepare("UPDATE users SET photo_profil = :photo WHERE id = :id");
            $stmt->bindParam(':photo', $db_path);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['photo_profil'] = $db_path; // Update session
                echo json_encode([
                    'success' => true, 
                    'message' => 'Photo de profil mise à jour',
                    'photo_url' => '../' . $db_path
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde du fichier']);
}
?>
