<?php
/**
 * API: Réalisations & liens entreprise (achievements, sites, projets).
 * Stored in DB table entreprise_achievements.
 */
session_start();
require_once __DIR__ . '/../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'employee' && $_SESSION['user_role'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$entreprise_id = (int)($_SESSION['entreprise_id'] ?? 0);
$database = new Database();
$db = $database->getConnection();

// Ensure table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS entreprise_achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        entreprise_id INT NOT NULL,
        type VARCHAR(50) NOT NULL DEFAULT 'website',
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        url VARCHAR(500) NULL,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_entreprise (entreprise_id),
        FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: list achievements for current enterprise
if ($method === 'GET') {
    try {
        $stmt = $db->prepare("SELECT id, type, title, description, url, sort_order, created_at FROM entreprise_achievements WHERE entreprise_id = :eid ORDER BY sort_order ASC, id ASC");
        $stmt->execute([':eid' => $entreprise_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'achievements' => $rows]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// POST: add achievement
if ($method === 'POST') {
    $type = isset($_POST['type']) ? trim($_POST['type']) : 'website';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $url = isset($_POST['url']) ? trim($_POST['url']) : '';

    $allowed_types = ['website', 'project', 'achievement', 'linkedin', 'other'];
    if (!in_array($type, $allowed_types)) $type = 'website';

    if ($title === '') {
        echo json_encode(['success' => false, 'message' => 'Le titre est obligatoire.']);
        exit;
    }

    try {
        $stmt = $db->prepare("INSERT INTO entreprise_achievements (entreprise_id, type, title, description, url, sort_order) VALUES (:eid, :type, :title, :desc, :url, 0)");
        $stmt->execute([
            ':eid' => $entreprise_id,
            ':type' => $type,
            ':title' => $title,
            ':desc' => $description,
            ':url' => $url
        ]);
        $id = (int) $db->lastInsertId();
        echo json_encode(['success' => true, 'message' => 'Ajouté.', 'id' => $id]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// PUT: update achievement
if ($method === 'PUT') {
    parse_str(file_get_contents('php://input'), $_PUT);
    $id = isset($_PUT['id']) ? (int) $_PUT['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID invalide']);
        exit;
    }
    $type = isset($_PUT['type']) ? trim($_PUT['type']) : null;
    $title = isset($_PUT['title']) ? trim($_PUT['title']) : null;
    $description = isset($_PUT['description']) ? trim($_PUT['description']) : null;
    $url = isset($_PUT['url']) ? trim($_PUT['url']) : null;

    $allowed_types = ['website', 'project', 'achievement', 'linkedin', 'other'];
    if ($type !== null && !in_array($type, $allowed_types)) $type = 'website';

    try {
        $updates = [];
        $params = [':id' => $id, ':eid' => $entreprise_id];
        if ($type !== null) { $updates[] = 'type = :type'; $params[':type'] = $type; }
        if ($title !== null) { $updates[] = 'title = :title'; $params[':title'] = $title; }
        if ($description !== null) { $updates[] = 'description = :description'; $params[':description'] = $description; }
        if ($url !== null) { $updates[] = 'url = :url'; $params[':url'] = $url; }
        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'Rien à modifier']);
            exit;
        }
        $sql = "UPDATE entreprise_achievements SET " . implode(', ', $updates) . " WHERE id = :id AND entreprise_id = :eid";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Mis à jour']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// DELETE: remove achievement
if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $_DELETE);
    $id = isset($_DELETE['id']) ? (int) $_DELETE['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID invalide']);
        exit;
    }
    try {
        $stmt = $db->prepare("DELETE FROM entreprise_achievements WHERE id = :id AND entreprise_id = :eid");
        $stmt->execute([':id' => $id, ':eid' => $entreprise_id]);
        echo json_encode(['success' => true, 'message' => 'Supprimé']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
