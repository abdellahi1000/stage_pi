<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'employee')) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$entreprise_id = $_SESSION['entreprise_id'] ?? 0;
$database = new Database();
$db = $database->getConnection();

if (!$entreprise_id) {
    echo json_encode(['success' => false, 'message' => 'Aucune entreprise associée à ce compte']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $db->prepare("SELECT id, name as nom, secteur as industry_sector, taille as company_size, adresse as siege, registre, num_fiscal, document_pdf, 
                                     slogan, website_url, bio, photo_profil
                              FROM entreprises WHERE id = :eid");
        $stmt->execute([':eid' => $entreprise_id]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        $company['has_admin_password'] = true;
        echo json_encode(['success' => true, 'company' => $company]);

    } elseif ($method === 'POST') {
        // multipart/form-data for logo upload
        if (isset($_FILES['logo'])) {
            $file = $_FILES['logo'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = "uploads/logos/company_" . $entreprise_id . "_" . time() . "." . $ext;
            
            if (!is_dir("../uploads/logos")) mkdir("../uploads/logos", 0777, true);
            
            if (move_uploaded_file($file['tmp_name'], "../" . $filename)) {
                $up = $db->prepare("UPDATE entreprises SET photo_profil = :path WHERE id = :eid");
                $up->execute([':path' => $filename, ':eid' => $entreprise_id]);
                echo json_encode(['success' => true, 'path' => $filename]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du transfert du fichier']);
            }
            exit;
        }

        // JSON for profile details
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data) {
             echo json_encode(['success' => false, 'message' => 'Données invalides']);
             exit;
        }

        $nom = strtoupper(trim($data['nom'] ?? ''));
        $secteur = $data['industry_sector'] ?? $data['secteur'] ?? '';
        $taille = $data['company_size'] ?? $data['taille'] ?? '';
        $siege = $data['siege'] ?? '';
        $slogan = $data['slogan'] ?? '';
        $site_web = $data['site_web'] ?? '';
        $description = $data['description'] ?? '';
        
        if (empty($nom)) {
            echo json_encode(['success' => false, 'message' => 'Le nom de l\'entreprise est obligatoire']);
            exit;
        }

        $stmt = $db->prepare("UPDATE entreprises SET 
                                name = :nom,
                                secteur = :secteur, 
                                taille = :taille, 
                                adresse = :siege,
                                slogan = :slogan,
                                website_url = :site,
                                bio = :bio
                              WHERE id = :eid");
        $stmt->execute([
            ':nom' => $nom,
            ':secteur' => $secteur,
            ':taille' => $taille,
            ':siege' => $siege,
            ':slogan' => $slogan,
            ':site' => $site_web,
            ':bio' => $description,
            ':eid' => $entreprise_id
        ]);

        $_SESSION['company_name'] = $nom;
        echo json_encode(['success' => true, 'message' => 'Profil de l\'entreprise mis à jour avec succès']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
