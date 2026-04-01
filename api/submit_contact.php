<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    // Use account_type instead of user_type
    $user_id = $_SESSION['user_id'] ?? null;
    $user_type = $_POST['account_type'] ?? $_POST['user_type'] ?? 'student';
    
    // Map account_type back to legacy user_type values if necessary
    if ($user_type === 'etudiant') $user_type = 'student';
    if ($user_type === 'external') $user_type = 'problem';

    $name = !empty($_POST['nom']) ? trim($_POST['nom']) : (!empty($_POST['prenom']) ? trim($_POST['prenom']) : '');
    $company_name = $_POST['company_name'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $problem_type = $_POST['problem_type'] ?? null;
    $title = $_POST['contactSubject'] ?? $_POST['title'] ?? null;
    $message = $_POST['contactMessage'] ?? $_POST['message'] ?? null;

    $target_company_id = $_POST['company_id'] ?? null;

    if (!$title || !$message) {
        throw new Exception("Titre et message sont obligatoires.");
    }

    $stmt = $db->prepare("INSERT INTO contact_requests (user_id, user_type, name, company_name, email, phone, problem_type, title, message, target_company_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $user_type, $name, $company_name, $email, $phone, $problem_type, $title, $message, $target_company_id]);
    
    $request_id = $db->lastInsertId();
    
    // Also insert the initial message into the thread table support_messages
    $stmt_msg = $db->prepare("INSERT INTO support_messages (request_id, user_id, sender_type, message_text) VALUES (?, ?, 'user', ?)");
    $stmt_msg->execute([$request_id, $user_id, $message]);

    echo json_encode(['success' => true, 'message' => 'Votre demande a été envoyée avec succès.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message: ' . $e->getMessage()]);
}
