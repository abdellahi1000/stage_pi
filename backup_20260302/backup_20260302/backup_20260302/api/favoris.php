<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'etudiant') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offre_id = isset($_POST['offre_id']) ? intval($_POST['offre_id']) : 0;
    
    if ($offre_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Offre invalide']);
        exit;
    }

    try {
        // Check if already in favorites
        $check = $db->prepare("SELECT id FROM favoris WHERE user_id = :uid AND offre_id = :oid");
        $check->execute([':uid' => $user_id, ':oid' => $offre_id]);
        
        if ($check->rowCount() > 0) {
            // Remove from favorites
            $del = $db->prepare("DELETE FROM favoris WHERE user_id = :uid AND offre_id = :oid");
            $del->execute([':uid' => $user_id, ':oid' => $offre_id]);
            echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Retiré des favoris']);
        } else {
            // Add to favorites
            $ins = $db->prepare("INSERT INTO favoris (user_id, offre_id) VALUES (:uid, :oid)");
            $ins->execute([':uid' => $user_id, ':oid' => $offre_id]);
            echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Ajouté aux favoris']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Return list of favorite IDs for current user
    try {
        $stmt = $db->prepare("SELECT offre_id FROM favoris WHERE user_id = :uid");
        $stmt->execute([':uid' => $user_id]);
        $favs = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'favorites' => $favs]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
