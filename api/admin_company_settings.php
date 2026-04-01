<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || ($_SESSION['user_type'] !== 'entreprise' && $_SESSION['user_type'] !== 'admin') || $_SESSION['user_role'] !== 'Administrator') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit;
}

$company_id = $_SESSION['company_id'];
$data = json_decode(file_get_contents('php://input'), true);

if ($data === null) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$email_notifs = isset($data['email_notifs']) && $data['email_notifs'] ? 1 : 0;
$weekly_reports = isset($data['weekly_reports']) && $data['weekly_reports'] ? 1 : 0;
$public_profile = isset($data['public_profile']) && $data['public_profile'] ? 1 : 0;
$mode_alternance = isset($data['mode_alternance']) && $data['mode_alternance'] ? 1 : 0;
$mode_statsy = isset($data['mode_statsy']) && $data['mode_statsy'] ? 1 : 0;

$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->prepare("INSERT INTO company_settings (company_id, email_notifs, weekly_reports, public_profile, mode_alternance, mode_statsy) 
                          VALUES (:cid, :en, :wr, :pp, :ma, :ms) 
                          ON DUPLICATE KEY UPDATE 
                          email_notifs = :en, weekly_reports = :wr, public_profile = :pp, mode_alternance = :ma, mode_statsy = :ms");
    $stmt->execute([
        ':cid' => $company_id,
        ':en' => $email_notifs,
        ':wr' => $weekly_reports,
        ':pp' => $public_profile,
        ':ma' => $mode_alternance,
        ':ms' => $mode_statsy
    ]);

    echo json_encode(['success' => true, 'message' => 'Préférences enregistrées avec succès !']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données.']);
}
?>
