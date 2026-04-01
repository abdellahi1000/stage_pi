<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'entreprise') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

// Ensure company_signature_path and other enterprise fields exist
try {
    $chk = $db->query("SHOW COLUMNS FROM users LIKE 'company_signature_path'");
    if ($chk->rowCount() === 0) {
        $db->exec("ALTER TABLE users ADD COLUMN company_signature_path VARCHAR(255) NULL");
    }
    $chk2 = $db->query("SHOW COLUMNS FROM users LIKE 'additional_emails'");
    if ($chk2->rowCount() === 0) {
        $db->exec("ALTER TABLE users ADD COLUMN additional_emails TEXT NULL");
    }
    $chk3 = $db->query("SHOW COLUMNS FROM users LIKE 'year_established'");
    if ($chk3->rowCount() === 0) {
        $db->exec("ALTER TABLE users ADD COLUMN year_established VARCHAR(4) NULL");
    }
} catch (PDOException $e) { /* non-blocking */ }

try {
    // 1. Basic Stats
    $stats = [
        'total_applications' => 0,
        'accepted' => 0,
        'rejected' => 0,
        'pending' => 0,
        'total_offers' => 0,
        'hidden_offers' => 0,
    ];

    // Total Offers (all)
    $ql = $db->prepare("SELECT COUNT(*) FROM offres_stage WHERE user_id = :uid");
    $ql->execute([':uid' => $user_id]);
    $stats['total_offers'] = (int)$ql->fetchColumn();

    // Total Favorites (all offers)
    $stats['total_favorites'] = 0;
    try {
        $qfav = $db->prepare("SELECT COUNT(*) FROM offer_favorites f INNER JOIN offres_stage o ON f.offer_id = o.id WHERE o.user_id = :uid");
        $qfav->execute([':uid' => $user_id]);
        $stats['total_favorites'] = (int)$qfav->fetchColumn();
    } catch (PDOException $e) {}

    // Hidden/Archived Offers (Offres masquées)
    $qlh = $db->prepare("SELECT COUNT(*) FROM offres_stage WHERE user_id = :uid AND statut = 'archivee'");
    $qlh->execute([':uid' => $user_id]);
    $stats['hidden_offers'] = (int)$qlh->fetchColumn();

    // Total pending messages
    $stats['total_messages'] = 0;
    try {
        $qmsg = $db->prepare("SELECT COUNT(*) FROM support_messages WHERE user_id = :uid AND sender_type = 'admin' AND status = 'unread'");
        $qmsg->execute([':uid' => $user_id]);
        $stats['total_messages'] = (int)$qmsg->fetchColumn();
    } catch (PDOException $e) {}

    // Active Offers
    $qla = $db->prepare("SELECT COUNT(*) FROM offres_stage WHERE user_id = :uid AND statut = 'active'");
    $qla->execute([':uid' => $user_id]);
    $stats['active_offers'] = (int)$qla->fetchColumn();

    // Applications counts
    $q2 = $db->prepare("
        SELECT c.statut, COUNT(*) as count 
        FROM candidatures c 
        INNER JOIN offres_stage o ON c.offre_id = o.id 
        WHERE o.user_id = :uid 
        GROUP BY c.statut
    ");
    $q2->execute([':uid' => $user_id]);
    $results = $q2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $stats['total_applications'] += (int)$row['count'];
        $s = strtoupper($row['statut']);
        if ($s === 'ACCEPTED' || $s === 'ACCEPTE') $stats['accepted'] += (int)$row['count'];
        if ($s === 'REJECTED' || $s === 'REFUSE') $stats['rejected'] += (int)$row['count'];
        if ($s === 'PENDING' || $s === 'EN_ATTENTE') $stats['pending'] += (int)$row['count'];
    }

    $stats['apps_per_offer'] = $stats['total_offers'] > 0 ? round($stats['total_applications'] / $stats['total_offers'], 1) : 0;

    // Activity Graph Data (GitHub style) - Last 12 months
    $q3 = $db->prepare("
        SELECT DATE(c.date_candidature) as activity_date, c.statut, COUNT(*) as count 
        FROM candidatures c 
        INNER JOIN offres_stage o ON c.offre_id = o.id 
        WHERE o.user_id = :uid 
        AND c.date_candidature >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)
        GROUP BY DATE(c.date_candidature), c.statut
        ORDER BY activity_date ASC
    ");
    $q3->execute([':uid' => $user_id]);
    $activities = $q3->fetchAll(PDO::FETCH_ASSOC);

    // Pivot data by date
    $activity_map = [];
    foreach ($activities as $act) {
        $date = $act['activity_date'];
        if (!isset($activity_map[$date])) {
            $activity_map[$date] = ['accepte' => 0, 'refuse' => 0, 'en_attente' => 0, 'total' => 0];
        }

        $status = strtolower($act['statut']);
        $count  = (int)$act['count'];

        if ($status === 'accepted' || $status === 'accepte') {
            $activity_map[$date]['accepte'] += $count;
        } elseif ($status === 'rejected' || $status === 'refuse') {
            $activity_map[$date]['refuse'] += $count;
        } elseif ($status === 'pending' || $status === 'en_attente' || $status === 'vue') {
            $activity_map[$date]['en_attente'] += $count;
        }

        $activity_map[$date]['total'] += $count;
    }

    // Full User Info
    $q4 = $db->prepare("SELECT * FROM users WHERE id = :uid");
    $q4->execute([':uid' => $user_id]);
    $user_info = $q4->fetch(PDO::FETCH_ASSOC);
    unset($user_info['password']);

    // Achievements from DB (entreprise_achievements table)
    $achievements = [];
    try {
        $chk = $db->query("SHOW TABLES LIKE 'entreprise_achievements'");
        if ($chk->rowCount() > 0) {
            $qa = $db->prepare("SELECT id, type, title, description, url, sort_order FROM entreprise_achievements WHERE user_id = :uid ORDER BY sort_order ASC, id ASC");
            $qa->execute([':uid' => $user_id]);
            $achievements = $qa->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) { /* non-blocking */ }

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'activity' => $activity_map,
        'user' => $user_info,
        'achievements' => $achievements
    ]);

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
