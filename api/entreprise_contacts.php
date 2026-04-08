<?php
session_start();
require_once __DIR__ . '/../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'entreprise' && $_SESSION['user_type'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$entreprise_id = (int)($_SESSION['entreprise_id'] ?? 0);
$database = new Database();
$db = $database->getConnection();

if ($entreprise_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Entreprise non identifiée']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_all':
            // Emails
            $stmt = $db->prepare("SELECT id, email FROM company_emails WHERE entreprise_id = ?");
            $stmt->execute([$entreprise_id]);
            $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Phones
            $stmt = $db->prepare("SELECT id, phone_number, type FROM company_phones WHERE entreprise_id = ?");
            $stmt->execute([$entreprise_id]);
            $phones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Social links
            $stmt = $db->prepare("SELECT id, platform, url FROM company_social_links WHERE entreprise_id = ?");
            $stmt->execute([$entreprise_id]);
            $socials = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Website
            $stmt = $db->prepare("SELECT website_url FROM entreprises WHERE id = ?");
            $stmt->execute([$entreprise_id]);
            $website = $stmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'emails' => $emails,
                'phones' => $phones,
                'socials' => $socials,
                'website' => $website
            ]);
            break;

        case 'add_email':
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email invalide');
            }
            $stmt = $db->prepare("INSERT INTO company_emails (entreprise_id, email) VALUES (?, ?)");
            $stmt->execute([$entreprise_id, $email]);
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            break;

        case 'update_email':
            $id = (int)($_POST['id'] ?? 0);
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email invalide');
            }
            $stmt = $db->prepare("UPDATE company_emails SET email = ? WHERE id = ? AND entreprise_id = ?");
            $stmt->execute([$email, $id, $entreprise_id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_email':
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM company_emails WHERE id = ? AND entreprise_id = ?");
            $stmt->execute([$id, $entreprise_id]);
            echo json_encode(['success' => true]);
            break;

        case 'add_phone':
            $number = trim($_POST['phone_number'] ?? '');
            $type = $_POST['type'] ?? 'Téléphone';
            if (empty($number)) throw new Exception('Numéro obligatoire');
            if (!in_array($type, ['Téléphone', 'WhatsApp', 'Mobile'])) $type = 'Téléphone';

            $stmt = $db->prepare("INSERT INTO company_phones (entreprise_id, phone_number, type) VALUES (?, ?, ?)");
            $stmt->execute([$entreprise_id, $number, $type]);
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            break;

        case 'update_phone':
            $id = (int)($_POST['id'] ?? 0);
            $number = trim($_POST['phone_number'] ?? '');
            $type = $_POST['type'] ?? 'Téléphone';
            if (empty($number)) throw new Exception('Numéro obligatoire');
            if (!in_array($type, ['Téléphone', 'WhatsApp', 'Mobile'])) $type = 'Téléphone';

            $stmt = $db->prepare("UPDATE company_phones SET phone_number = ?, type = ? WHERE id = ? AND entreprise_id = ?");
            $stmt->execute([$number, $type, $id, $entreprise_id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_phone':
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM company_phones WHERE id = ? AND entreprise_id = ?");
            $stmt->execute([$id, $entreprise_id]);
            echo json_encode(['success' => true]);
            break;

        case 'save_social':
            $platform = $_POST['platform'] ?? '';
            $url = trim($_POST['url'] ?? '');
            if (!in_array($platform, ['Facebook', 'X', 'Instagram', 'LinkedIn'])) throw new Exception('Plateforme invalide');
            
            if (empty($url)) {
                $stmt = $db->prepare("DELETE FROM company_social_links WHERE entreprise_id = ? AND platform = ?");
                $stmt->execute([$entreprise_id, $platform]);
            } else {
                $stmt = $db->prepare("INSERT INTO company_social_links (entreprise_id, platform, url) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE url = VALUES(url)");
                $stmt->execute([$entreprise_id, $platform, $url]);
            }
            echo json_encode(['success' => true]);
            break;

        case 'save_website':
            $url = trim($_POST['url'] ?? '');
            // Simple validation
            if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL) && !str_contains($url, 'localhost')) {
                // allow some non-standard urls for dev but usually we want valid ones
            }
            $stmt = $db->prepare("UPDATE entreprises SET website_url = ? WHERE id = ?");
            $stmt->execute([$url, $entreprise_id]);
            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
