<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

try {
    $db = (new Database())->getConnection();
    
    $entreprise_id = isset($_GET['entreprise_id']) ? (int)$_GET['entreprise_id'] : null;
    $entreprise_name = isset($_GET['entreprise_name']) ? trim($_GET['entreprise_name']) : '';
    
    if (!$entreprise_id && empty($entreprise_name)) {
        throw new Exception('Missing company identifier');
    }
    
    if ($entreprise_id) {
        $stmt = $db->prepare("SELECT id, name as nom, secteur as industry_sector, taille as company_size, adresse as siege, registre, num_fiscal, document_pdf, 
                                     slogan, website_url, bio, photo_profil, creation_year as date_creation, company_type as organisation
                              FROM entreprises WHERE id = :eid");
        $stmt->execute([':eid' => $entreprise_id]);
    } else {
        $stmt = $db->prepare("SELECT id, name as nom, secteur as industry_sector, taille as company_size, adresse as siege, registre, num_fiscal, document_pdf, 
                                     slogan, website_url, bio, photo_profil, creation_year as date_creation, company_type as organisation
                              FROM entreprises WHERE name = :ename");
        $stmt->execute([':ename' => $entreprise_name]);
    }
    
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        throw new Exception('Company not found');
    }
    
    // Counters
    $total_offers = 0;
    $accepted_interns = 0;
    $total_applications = 0;
    $active_offers_count = 0;

    try {
        // Active Offers
        $stActive = $db->prepare("SELECT COUNT(*) FROM offres WHERE entreprise_id = :eid AND statut = 'active'");
        $stActive->execute([':eid' => $company['id']]);
        $active_offers_count = (int)$stActive->fetchColumn();

        // Total Offers
        $stTotal = $db->prepare("SELECT COUNT(*) FROM offres WHERE entreprise_id = :eid");
        $stTotal->execute([':eid' => $company['id']]);
        $total_offers = (int)$stTotal->fetchColumn();

        // Candidatures
        $stAccepted = $db->prepare("SELECT COUNT(*) FROM candidatures c INNER JOIN offres o ON c.offre_id = o.id WHERE o.entreprise_id = :eid AND c.statut IN ('accepted', 'accepte')");
        $stAccepted->execute([':eid' => $company['id']]);
        $accepted_interns = (int)$stAccepted->fetchColumn();

        $stApps = $db->prepare("SELECT COUNT(*) FROM candidatures c INNER JOIN offres o ON c.offre_id = o.id WHERE o.entreprise_id = :eid");
        $stApps->execute([':eid' => $company['id']]);
        $total_applications = (int)$stApps->fetchColumn();
    } catch (Exception $e) {
        error_log("Counters error: " . $e->getMessage());
    }
    
    $response = [
        'success' => true,
        'company' => array_merge($company, [
            'active_offers' => $active_offers_count,
            'total_offers' => $total_offers,
            'accepted_interns' => $accepted_interns,
            'total_applications' => $total_applications
        ])
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
