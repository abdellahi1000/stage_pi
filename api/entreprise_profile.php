<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = (new Database())->getConnection();
    
    // Get parameters
    $entreprise_id = isset($_GET['entreprise_id']) && $_GET['entreprise_id'] !== 'null' ? (int)$_GET['entreprise_id'] : null;
    $entreprise_name = isset($_GET['entreprise_name']) ? trim($_GET['entreprise_name']) : '';
    
    // Debug logging
    error_log("entreprise_profile.php: ID=$entreprise_id, Name='$entreprise_name'");
    
    if (!$entreprise_id && empty($entreprise_name)) {
        throw new Exception('Missing company identifier');
    }
    
    // Get all available columns from users table
    try {
        $columns_query = $db->query("DESCRIBE users");
        $available_columns = $columns_query->fetchAll(PDO::FETCH_COLUMN);
        error_log("Available columns: " . implode(', ', $available_columns));
    } catch (Exception $e) {
        error_log("Error getting columns: " . $e->getMessage());
        $available_columns = [];
    }
    
    // Build safe query with only existing columns
    $safe_select_fields = [];
    $essential_fields = [
        'id' => 'id',
        'nom' => 'nom', 
        'email' => 'email',
        'telephone' => 'telephone',
        'photo_profil' => 'photo_profil',
        'bio' => 'bio',
        'website' => 'website',
        'website_url' => 'website_url',
        'industry_sector' => 'industry_sector',
        'company_size' => 'company_size',
        'adresse' => 'adresse',
        'ville' => 'ville',
        'pays' => 'pays',
        'location_type' => 'location_type',
        'organisation' => 'organisation',
        'verified_status' => 'verified_status'
    ];
    
    foreach ($essential_fields as $alias => $field) {
        if (in_array($field, $available_columns)) {
            $safe_select_fields[] = $field;
        }
    }
    
    // Add optional fields if they exist
    $optional_fields = [
        'year_established' => 'year_established',
        'hr_contact' => 'hr_contact',
        'linkedin_url' => 'linkedin_url',
        'facebook_url' => 'facebook_url', 
        'twitter_url' => 'twitter_url',
        'instagram_url' => 'instagram_url',
        'technologies' => 'technologies',
        'services' => 'services',
        'portfolio_url' => 'portfolio_url',
        'company_signature_path' => 'company_signature_path'
    ];
    
    foreach ($optional_fields as $alias => $field) {
        if (in_array($field, $available_columns)) {
            $safe_select_fields[] = $field;
        }
    }
    
    if (empty($safe_select_fields)) {
        throw new Exception('No valid columns found in users table');
    }
    
    // Build the query
    $query = "SELECT " . implode(', ', $safe_select_fields) . " FROM users WHERE ";
    
    if ($entreprise_id) {
        $query .= "id = :entreprise_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':entreprise_id', $entreprise_id, PDO::PARAM_INT);
        error_log("Executing query with ID: $entreprise_id");
    } else {
        // Check if 'role' column exists
        if (in_array('type_compte', $available_columns)) {
            $query .= "nom = :entreprise_name AND type_compte = 'entreprise'";
        } else {
            $query .= "nom = :entreprise_name";
        }
        $stmt = $db->prepare($query);
        $stmt->bindParam(':entreprise_name', $entreprise_name, PDO::PARAM_STR);
        error_log("Executing query with name: $entreprise_name");
    }
    
    $stmt->execute();
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Query result: " . ($company ? "Found company" : "No company found"));
    
    if (!$company) {
        throw new Exception('Company not found');
    }
    
    // Get company achievements (with error handling)
    $achievements = [];
    try {
        // Check if table exists
        $table_check = $db->query("SHOW TABLES LIKE 'entreprise_achievements'");
        if ($table_check->rowCount() > 0) {
            // Check if user_id column exists
            $ach_columns = $db->query("DESCRIBE entreprise_achievements")->fetchAll(PDO::FETCH_COLUMN);
            if (in_array('user_id', $ach_columns)) {
                $qa = $db->prepare("SELECT id, type, title, description, url, sort_order FROM entreprise_achievements WHERE user_id = :uid ORDER BY sort_order ASC, id ASC");
                $qa->execute([':uid' => $company['id']]);
                $achievements = $qa->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (PDOException $e) {
        error_log("Achievements query error: " . $e->getMessage());
    }
    
    // Get company emails (only from company_emails table - NOT the main email)
    $emails = [];
    try {
        $stEmail = $db->prepare("SELECT id, email FROM company_emails WHERE company_id = ? ORDER BY id ASC");
        $stEmail->execute([$company['id']]);
        while($row = $stEmail->fetch(PDO::FETCH_ASSOC)) {
            $emails[] = $row;
        }
    } catch (Exception $e) {
        error_log("Error fetching company emails: " . $e->getMessage());
    }
    
    // Get company phone numbers
    $phones = [];
    try {
        $stPhone = $db->prepare("SELECT id, phone_number, type FROM company_phones WHERE company_id = ? ORDER BY id ASC");
        $stPhone->execute([$company['id']]);
        while($row = $stPhone->fetch(PDO::FETCH_ASSOC)) {
            $phones[] = $row;
        }
    } catch (Exception $e) {
        error_log("Error fetching company phones: " . $e->getMessage());
    }

    // Get company counters (Interns, Applications, Total Offers)
    $total_offers = 0;
    $accepted_interns = 0;
    $total_applications = 0;
    $active_offers_count = 0;

    try {
        // Offres Stage
        $offers_table_check = $db->query("SHOW TABLES LIKE 'offres_stage'");
        if ($offers_table_check->rowCount() > 0) {
            // Active
            $stActive = $db->prepare("SELECT COUNT(*) FROM offres_stage WHERE user_id = :uid AND statut = 'active'");
            $stActive->execute([':uid' => $company['id']]);
            $active_offers_count = (int)$stActive->fetchColumn();

            // Total
            $stTotal = $db->prepare("SELECT COUNT(*) FROM offres_stage WHERE user_id = :uid");
            $stTotal->execute([':uid' => $company['id']]);
            $total_offers = (int)$stTotal->fetchColumn();
        }

        // Candidatures
        $candidatures_check = $db->query("SHOW TABLES LIKE 'candidatures'");
        if ($candidatures_check->rowCount() > 0) {
            // Accepted
            $stAccepted = $db->prepare("SELECT COUNT(*) FROM candidatures c INNER JOIN offres_stage o ON c.offre_id = o.id WHERE o.user_id = :uid AND c.statut IN ('accepted', 'accepté', 'accepte')");
            $stAccepted->execute([':uid' => $company['id']]);
            $accepted_interns = (int)$stAccepted->fetchColumn();

            // Total Apps
            $stApps = $db->prepare("SELECT COUNT(*) FROM candidatures c INNER JOIN offres_stage o ON c.offre_id = o.id WHERE o.user_id = :uid");
            $stApps->execute([':uid' => $company['id']]);
            $total_applications = (int)$stApps->fetchColumn();
        }
    } catch (Exception $e) {
        error_log("Counters error: " . $e->getMessage());
    }
    
    // Build comprehensive response
    $response = [
        'success' => true,
        'company' => [
            'id' => $company['id'] ?? null,
            'nom' => $company['nom'] ?? 'Entreprise',
            'email' => $company['email'] ?? '',
            'emails' => $emails,
            'telephone' => $company['telephone'] ?? '',
            'phones' => $phones,
            'photo_profil' => $company['photo_profil'] ?? '',
            'bio' => $company['bio'] ?? '',
            'website' => $company['website_url'] ?? $company['website'] ?? '',
            'industry_sector' => $company['industry_sector'] ?? '',
            'company_size' => $company['company_size'] ?? '',
            'adresse' => $company['adresse'] ?? '',
            'ville' => $company['ville'] ?? '',
            'pays' => $company['pays'] ?? '',
            'location_type' => $company['location_type'] ?? '',
            'organisation' => $company['organisation'] ?? '',
            'date_creation' => $company['year_established'] ?? $company['date_creation'] ?? '',
            'hr_contact' => $company['hr_contact'] ?? '',
            'verified_status' => $company['verified_status'] ?? 0,
            'achievements' => $achievements,
            'active_offers' => $active_offers_count,
            'total_offers' => $total_offers,
            'accepted_interns' => $accepted_interns,
            'total_applications' => $total_applications
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
