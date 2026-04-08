<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || ($_SESSION['user_role'] !== 'employee' && $_SESSION['user_role'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$entreprise_id = $_SESSION['entreprise_id'] ?? 0;
$database = new Database();
$db = $database->getConnection();

try {
    // 0. Fetch Company Info
    $stmt = $db->prepare("SELECT id, name as nom, secteur as industry_sector, taille as company_size, adresse as siege, registre, num_fiscal, document_pdf, 
                                 slogan, website_url, bio, photo_profil, creation_year as year_established, company_type as organisation, hr_manager
                          FROM entreprises WHERE id = :eid");
    $stmt->execute([':eid' => $entreprise_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    // Also get the main email from the users table (manager)
    $stmt_u = $db->prepare("SELECT email, telephone FROM users WHERE entreprise_id = :eid AND role = 'admin' LIMIT 1");
    $stmt_u->execute([':eid' => $entreprise_id]);
    $user_info = $stmt_u->fetch(PDO::FETCH_ASSOC);
    
    if ($company && $user_info) {
        $company['email'] = $user_info['email'];
        if (empty($company['telephone'])) $company['telephone'] = $user_info['telephone'];
    }

    // 1. Basic Stats
    $stats = [
        'total_applications' => 0,
        'accepted' => 0,
        'accepted_stagiaires' => 0,
        'accepted_alternances' => 0,
        'rejected' => 0,
        'pending' => 0,
        'total_offers' => 0,
        'hidden_offers' => 0,
    ];

    // Total Offers (all)
    $ql = $db->prepare("SELECT COUNT(*) FROM offres WHERE entreprise_id = :eid");
    $ql->execute([':eid' => $entreprise_id]);
    $stats['total_offers'] = (int)$ql->fetchColumn();

    // Total Favorites (all offers)
    $stats['total_favorites'] = 0;
    try {
        $qfav = $db->prepare("SELECT COUNT(*) FROM offer_favorites f INNER JOIN offres o ON f.offer_id = o.id WHERE o.entreprise_id = :eid");
        $qfav->execute([':eid' => $entreprise_id]);
        $stats['total_favorites'] = (int)$qfav->fetchColumn();
    } catch (PDOException $e) {}

    // Hidden/Archived Offers (Offres masquées)
    $qlh = $db->prepare("SELECT COUNT(*) FROM offres WHERE entreprise_id = :eid AND statut = 'archivee'");
    $qlh->execute([':eid' => $entreprise_id]);
    $stats['hidden_offers'] = (int)$qlh->fetchColumn();

    // Total pending messages
    $stats['total_messages'] = 0;
    try {
        $qmsg = $db->prepare("SELECT COUNT(*) FROM support_messages WHERE entreprise_id = :eid AND sender_type = 'admin' AND status = 'unread'");
        $qmsg->execute([':eid' => $entreprise_id]);
        $stats['total_messages'] = (int)$qmsg->fetchColumn();
    } catch (PDOException $e) {}

    // Active Offers
    $qla = $db->prepare("SELECT COUNT(*) FROM offres WHERE entreprise_id = :eid AND statut = 'active'");
    $qla->execute([':eid' => $entreprise_id]);
    $stats['active_offers'] = (int)$qla->fetchColumn();

    // Applications counts
    $q2 = $db->prepare("
        SELECT c.statut, o.type_contrat, COUNT(*) as count 
        FROM candidatures c 
        INNER JOIN offres o ON c.offre_id = o.id 
        WHERE o.entreprise_id = :eid 
        GROUP BY c.statut, o.type_contrat
    ");
    $q2->execute([':eid' => $entreprise_id]);
    $results = $q2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $count = (int)$row['count'];
        $stats['total_applications'] += $count;
        $s = strtolower($row['statut'] ?? '');
        $type = $row['type_contrat'] ?? 'Stage';

        if ($s === 'accepted' || $s === 'accépté' || $s === 'accepte') {
            $stats['accepted'] += $count;
            if ($type === 'Stage') $stats['accepted_stagiaires'] += $count;
            elseif ($type === 'Alternance') $stats['accepted_alternances'] += $count;
        } elseif ($s === 'rejected' || $s === 'refusé' || $s === 'refuse') {
            $stats['rejected'] += $count;
        } else {
            $stats['pending'] += $count;
        }
    }

    $stats['apps_per_offer'] = $stats['total_offers'] > 0 ? round($stats['total_applications'] / $stats['total_offers'], 1) : 0;

    // Activity Graph Data Data (Last 365 Days)
    $q3 = $db->prepare("
        SELECT DATE(c.date_candidature) as activity_date, c.statut, COUNT(*) as count 
        FROM candidatures c 
        INNER JOIN offres o ON c.offre_id = o.id 
        WHERE o.entreprise_id = :eid 
        AND c.date_candidature >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)
        GROUP BY DATE(c.date_candidature), c.statut
        ORDER BY activity_date ASC
    ");
    $q3->execute([':eid' => $entreprise_id]);
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

        if ($status === 'accepted' || $status === 'accépté' || $status === 'accepte') {
            $activity_map[$date]['accepte'] += $count;
        } elseif ($status === 'rejected' || $status === 'refusé' || $status === 'refuse') {
            $activity_map[$date]['refuse'] += $count;
        } else {
            $activity_map[$date]['en_attente'] += $count;
        }

        $activity_map[$date]['total'] += $count;
    }

    // Recent applications for the dashboard
    $q_recent = $db->prepare("
        SELECT c.*, u.nom, u.prenom, o.title as offre_titre 
        FROM candidatures c 
        INNER JOIN users u ON c.user_id = u.id 
        INNER JOIN offres o ON c.offre_id = o.id 
        WHERE o.entreprise_id = :eid 
        ORDER BY c.date_candidature DESC LIMIT 5
    ");
    $q_recent->execute([':eid' => $entreprise_id]);
    $recent_apps = $q_recent->fetchAll(PDO::FETCH_ASSOC);

    // Achievements
    $q_ach = $db->prepare("SELECT type, title, description, url FROM entreprise_achievements WHERE entreprise_id = :eid ORDER BY sort_order ASC");
    $q_ach->execute([':eid' => $entreprise_id]);
    $achievements = $q_ach->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'user' => $company,
        'stats' => $stats,
        'activity' => $activity_map,
        'recent_apps' => $recent_apps,
        'achievements' => $achievements
    ]);

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
