<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';
check_auth('entreprise', 'Administrator');

if (!isset($_GET['id'])) {
    die("ID Etudiant manquant.");
}

$db = (new Database())->getConnection();
$student_id = intval($_GET['id']);

$stmt = $db->prepare("SELECT cv_path FROM profils WHERE user_id = ?");
$stmt->execute([$student_id]);
$profil = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profil || empty($profil['cv_path'])) {
    die("L'étudiant n'a pas mis en ligne de CV général.");
}

$cv_path = '../' . $profil['cv_path'];
if (!file_exists($cv_path)) {
    die("Le fichier CV est introuvable sur le serveur.");
}

$mime = mime_content_type($cv_path);
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($cv_path) . '"');
header('Content-Length: ' . filesize($cv_path));
readfile($cv_path);
exit;
