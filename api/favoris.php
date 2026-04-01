<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'etudiant') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$student_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offer_id = isset($_POST['offer_id']) ? intval($_POST['offer_id']) : 0;
    
    if ($offer_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Offre invalide']);
        exit;
    }

    try {
        // Check if already in favorites
        $check = $db->prepare("SELECT id FROM offer_favorites WHERE student_id = :sid AND offer_id = :oid");
        $check->execute([':sid' => $student_id, ':oid' => $offer_id]);
        
        $action = '';
        if ($check->rowCount() > 0) {
            // Remove from favorites
            $del = $db->prepare("DELETE FROM offer_favorites WHERE student_id = :sid AND offer_id = :oid");
            $del->execute([':sid' => $student_id, ':oid' => $offer_id]);
            $action = 'removed';
        } else {
            // Add to favorites
            $ins = $db->prepare("INSERT INTO offer_favorites (student_id, offer_id) VALUES (:sid, :oid)");
            $ins->execute([':sid' => $student_id, ':oid' => $offer_id]);
            $action = 'added';
        }

        // Get new total favorites count for this offer
        $countStmt = $db->prepare("SELECT COUNT(*) FROM offer_favorites WHERE offer_id = :oid");
        $countStmt->execute([':oid' => $offer_id]);
        $total_favorites = $countStmt->fetchColumn();

        echo json_encode([
            'success' => true, 
            'action' => $action, 
            'total_favorites' => $total_favorites
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Return list of favorite offer IDs for current user
    try {
        $stmt = $db->prepare("SELECT offer_id FROM offer_favorites WHERE student_id = :sid");
        $stmt->execute([':sid' => $student_id]);
        $favs = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Also let's return all favorited offer total counts so we can prepopulate? 
        // Not necessary if PHP renders them, but helpful if fetched via JS.
        echo json_encode(['success' => true, 'favorites' => $favs]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
