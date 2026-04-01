<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'administrateur')) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$ticket_id = $_POST['ticket_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$ticket_id || !in_array($action, ['read', 'resolved'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    $stmt = $db->prepare("UPDATE support_tickets SET status = ? WHERE id = ?");
    $stmt->execute([$action, $ticket_id]);
    
    echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
