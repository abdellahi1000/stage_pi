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

$action = isset($_POST['action']) ? $_POST['action'] : '';
$database = new Database();
$db = $database->getConnection();

if ($action === 'update_theme') {
    $theme = isset($_POST['theme']) ? $_POST['theme'] : 'light';
    
    // Validate theme
    if (!in_array($theme, ['light', 'dark'])) {
        $theme = 'light';
    }
    
    // Check if preference exists
    $check_stmt = $db->prepare("SELECT id FROM preferences_utilisateur WHERE user_id = :id");
    $check_stmt->bindParam(':id', $_SESSION['user_id']);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        // Update
        $stmt = $db->prepare("UPDATE preferences_utilisateur SET theme = :theme WHERE user_id = :id");
    } else {
        // Insert (fallback if something went wrong before)
        $stmt = $db->prepare("INSERT INTO preferences_utilisateur (user_id, theme) VALUES (:id, :theme)");
    }
    
    $stmt->bindParam(':theme', $theme);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['user_theme'] = $theme; // Update session
        echo json_encode(['success' => true, 'message' => 'Thème mis à jour', 'theme' => $theme]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}
?>
