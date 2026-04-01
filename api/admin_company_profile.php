<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'entreprise') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$company_id = $_SESSION['company_id'];
$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $db->prepare("SELECT id, nom, slogan, bio, industry_sector, company_size, website_url, adresse as siege, photo_profil, password_admin, email 
                              FROM users WHERE id = :cid");
        $stmt->execute([':cid' => $company_id]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Don't leak passwords obviously, but we might need to check if user has admin password set
        $company['has_admin_password'] = !empty($company['password_admin']);
        unset($company['password_admin']);
        
        echo json_encode(['success' => true, 'company' => $company]);

    } elseif ($method === 'POST') {
        // Check if it's a file upload (logo)
        if (isset($_FILES['logo'])) {
            $file = $_FILES['logo'];
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Format de fichier non autorisé']);
                exit;
            }
            
            $filename = 'logo_' . $company_id . '_' . time() . '.' . $ext;
            $path = '../uploads/logos/' . $filename;
            
            if (!is_dir('../uploads/logos/')) {
                mkdir('../uploads/logos/', 0777, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $path)) {
                $db_path = 'uploads/logos/' . $filename;
                $stmt = $db->prepare("UPDATE users SET photo_profil = :photo WHERE id = :cid");
                $stmt->execute([':photo' => $db_path, ':cid' => $company_id]);
                
                // Update session if it's the current user's company
                if ($_SESSION['user_id'] == $company_id) {
                    $_SESSION['photo_profil'] = $db_path;
                }
                
                echo json_encode(['success' => true, 'message' => 'Logo mis à jour', 'path' => $db_path]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du transfert du fichier']);
            }
            exit;
        }

        // Handle profile updates (JSON)
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data) {
             echo json_encode(['success' => false, 'message' => 'Données invalides']);
             exit;
        }

        $nom = $data['nom'] ?? '';
        $slogan = $data['slogan'] ?? '';
        $secteur = $data['secteur'] ?? '';
        $taille = $data['taille'] ?? '';
        $site_web = $data['site_web'] ?? '';
        $siege = $data['siege'] ?? '';
        $bio = $data['description'] ?? '';

        $stmt = $db->prepare("UPDATE users SET 
                                nom = :nom,
                                slogan = :slogan, 
                                bio = :bio, 
                                industry_sector = :secteur, 
                                company_size = :taille, 
                                website_url = :site, 
                                adresse = :siege 
                              WHERE id = :cid");
        $stmt->execute([
            ':nom' => $nom,
            ':slogan' => $slogan,
            ':bio' => $bio,
            ':secteur' => $secteur,
            ':taille' => $taille,
            ':site' => $site_web,
            ':siege' => $siege,
            ':cid' => $company_id
        ]);

        echo json_encode(['success' => true, 'message' => 'Profil de l\'entreprise mis à jour avec succès']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
