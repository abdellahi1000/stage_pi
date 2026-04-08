<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$entreprise_id = $_SESSION['entreprise_id'] ?? 0;
$database = new Database();
$db = $database->getConnection();

try {
    // 1. Stats
    // Total Offers
    $stmt = $db->prepare("SELECT COUNT(*) FROM offres WHERE entreprise_id = :eid");
    $stmt->execute([':eid' => $entreprise_id]);
    $total_offers = $stmt->fetchColumn();

    // Total Applications
    $stmt = $db->prepare("SELECT COUNT(*) FROM candidatures c JOIN offres o ON c.offre_id = o.id WHERE o.entreprise_id = :eid");
    $stmt->execute([':eid' => $entreprise_id]);
    $total_apps = $stmt->fetchColumn();

    // Accepted Stagiaires
    $stmt = $db->prepare("SELECT COUNT(*) FROM candidatures c JOIN offres o ON c.offre_id = o.id WHERE o.entreprise_id = :eid AND c.statut = 'accepted' AND o.type_contrat = 'Stage'");
    $stmt->execute([':eid' => $entreprise_id]);
    $accepted_stagiaires = $stmt->fetchColumn();

    // Accepted Alternances
    $stmt = $db->prepare("SELECT COUNT(*) FROM candidatures c JOIN offres o ON c.offre_id = o.id WHERE o.entreprise_id = :eid AND c.statut = 'accepted' AND o.type_contrat = 'Alternance'");
    $stmt->execute([':eid' => $entreprise_id]);
    $accepted_alternances = $stmt->fetchColumn();

    // Accepted Total (fallback)
    $accepted = (int)$accepted_stagiaires + (int)$accepted_alternances;

    // Rejected Students
    $stmt = $db->prepare("SELECT COUNT(*) FROM candidatures c JOIN offres o ON c.offre_id = o.id WHERE o.entreprise_id = :eid AND c.statut = 'rejected'");
    $stmt->execute([':eid' => $entreprise_id]);
    $rejected = $stmt->fetchColumn();

    // Blocked Students (assuming table blocked_students now has entreprise_id)
    $stmt = $db->prepare("SELECT COUNT(*) FROM blocked_students WHERE entreprise_id = :eid");
    $stmt->execute([':eid' => $entreprise_id]);
    $blocked = $stmt->fetchColumn();

    // 2. Chart Data (Current Year)
    $year = date('Y');
    
    // Group apps by date
    $stmt = $db->prepare("SELECT DATE(c.date_candidature) as date, COUNT(*) as count 
                          FROM candidatures c 
                          JOIN offres o ON c.offre_id = o.id 
                          WHERE o.entreprise_id = :eid AND YEAR(c.date_candidature) = :year 
                          GROUP BY DATE(c.date_candidature)");
    $stmt->execute([':eid' => $entreprise_id, ':year' => $year]);
    $apps_by_date = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Group acceptances by date
    $stmt = $db->prepare("SELECT DATE(c.acceptance_date) as date, COUNT(*) as count 
                          FROM candidatures c 
                          JOIN offres o ON c.offre_id = o.id 
                          WHERE o.entreprise_id = :eid AND YEAR(c.acceptance_date) = :year AND c.statut = 'accepted'
                          GROUP BY DATE(c.acceptance_date)");
    $stmt->execute([':eid' => $entreprise_id, ':year' => $year]);
    $accepted_by_date = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // 3. Recent Applications
    $stmt = $db->prepare("SELECT c.*, u_etud.nom, u_etud.prenom, o.title as offre_titre 
                          FROM candidatures c 
                          JOIN users u_etud ON c.user_id = u_etud.id 
                          JOIN offres o ON c.offre_id = o.id 
                          WHERE o.entreprise_id = :eid 
                          ORDER BY c.date_candidature DESC LIMIT 5");
    $stmt->execute([':eid' => $entreprise_id]);
    $recent_apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_offers' => (int)$total_offers,
            'total_apps' => (int)$total_apps,
            'accepted' => (int)$accepted,
            'accepted_stagiaires' => (int)$accepted_stagiaires,
            'accepted_alternances' => (int)$accepted_alternances,
            'rejected' => (int)$rejected,
            'blocked' => (int)$blocked
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
