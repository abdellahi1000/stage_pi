<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Administrator') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}


$company_id = $_SESSION['company_id'];
$database = new Database();
$db = $database->getConnection();

try {
    // 1. Stats
    // Total Offers
    $stmt = $db->prepare("SELECT COUNT(*) FROM offres_stage o JOIN users u ON o.user_id = u.id WHERE u.company_id = :cid");
    $stmt->execute([':cid' => $company_id]);
    $total_offers = $stmt->fetchColumn();

    // Total Applications
    $stmt = $db->prepare("SELECT COUNT(*) FROM candidatures c JOIN offres_stage o ON c.offre_id = o.id JOIN users u ON o.user_id = u.id WHERE u.company_id = :cid");
    $stmt->execute([':cid' => $company_id]);
    $total_apps = $stmt->fetchColumn();

    // Accepted Students
    $stmt = $db->prepare("SELECT COUNT(*) FROM candidatures c JOIN offres_stage o ON c.offre_id = o.id JOIN users u ON o.user_id = u.id WHERE u.company_id = :cid AND c.statut = 'accepted'");
    $stmt->execute([':cid' => $company_id]);
    $accepted = $stmt->fetchColumn();

    // Rejected Students
    $stmt = $db->prepare("SELECT COUNT(*) FROM candidatures c JOIN offres_stage o ON c.offre_id = o.id JOIN users u ON o.user_id = u.id WHERE u.company_id = :cid AND c.statut = 'rejected'");
    $stmt->execute([':cid' => $company_id]);
    $rejected = $stmt->fetchColumn();

    // Blocked Students
    $stmt = $db->prepare("SELECT COUNT(*) FROM blocked_students WHERE company_id = :cid");
    $stmt->execute([':cid' => $company_id]);
    $blocked = $stmt->fetchColumn();

    // 2. Chart Data (Current Year)
    $year = date('Y');
    
    // Group apps by date
    $stmt = $db->prepare("SELECT DATE(c.date_candidature) as date, COUNT(*) as count 
                          FROM candidatures c 
                          JOIN offres_stage o ON c.offre_id = o.id 
                          JOIN users u ON o.user_id = u.id
                          WHERE u.company_id = :cid AND YEAR(c.date_candidature) = :year 
                          GROUP BY DATE(c.date_candidature)");
    $stmt->execute([':cid' => $company_id, ':year' => $year]);
    $apps_by_date = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Group acceptances by date
    $stmt = $db->prepare("SELECT DATE(c.acceptance_date) as date, COUNT(*) as count 
                          FROM candidatures c 
                          JOIN offres_stage o ON c.offre_id = o.id 
                          JOIN users u ON o.user_id = u.id
                          WHERE u.company_id = :cid AND YEAR(c.acceptance_date) = :year AND c.statut = 'accepted'
                          GROUP BY DATE(c.acceptance_date)");
    $stmt->execute([':cid' => $company_id, ':year' => $year]);
    $accepted_by_date = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // 3. Recent Applications
    $stmt = $db->prepare("SELECT c.*, u_etud.nom, u_etud.prenom, o.titre as offre_titre 
                          FROM candidatures c 
                          JOIN users u_etud ON c.user_id = u_etud.id 
                          JOIN offres_stage o ON c.offre_id = o.id 
                          JOIN users u_owner ON o.user_id = u_owner.id
                          WHERE u_owner.company_id = :cid 
                          ORDER BY c.date_candidature DESC LIMIT 5");
    $stmt->execute([':cid' => $company_id]);
    $recent_apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_offers' => $total_offers,
            'total_apps' => $total_apps,
            'accepted' => $accepted,
            'rejected' => $rejected,
            'blocked' => $blocked
        ],
        'chart_data' => [
            'apps' => $apps_by_date,
            'accepted' => $accepted_by_date
        ],
        'recent_apps' => $recent_apps
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
