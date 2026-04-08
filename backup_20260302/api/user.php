<?php
// compte.php - Gestion du compte utilisateur

session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

// GET : Récupérer toutes les informations du compte
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Informations utilisateur de base
        $user_query = "SELECT 
                        u.*, 
                        p.cv_path, p.niveau_etudes, p.specialite, p.universite, p.skills, p.domaine_formation, p.statut_disponibilite, p.lettre_motivation_path,
                        pref.notifications_email, pref.alertes_offres, pref.langue
                      FROM users u 
                      LEFT JOIN profils p ON u.id = p.user_id
                      LEFT JOIN preferences_utilisateur pref ON u.id = pref.user_id
                      WHERE u.id = :user_id";
        
        $user_stmt = $db->prepare($user_query);
        $user_stmt->bindParam(':user_id', $user_id);
        $user_stmt->execute();
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Candidatures en attente (pending)
        $attente_query = "SELECT 
                            c.id,
                            c.date_candidature,
                            o.titre,
                            o.entreprise,
                            o.localisation,
                            o.type_contrat
                          FROM candidatures c
                          INNER JOIN offres o ON c.offre_id = o.id
                          WHERE c.user_id = :user_id AND c.statut = 'pending'
                          ORDER BY c.date_candidature DESC";
        
        $attente_stmt = $db->prepare($attente_query);
        $attente_stmt->bindParam(':user_id', $user_id);
        $attente_stmt->execute();
        $candidatures_attente = $attente_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Candidatures acceptées (accepted)
        $accepte_query = "SELECT 
                            c.id,
                            c.date_candidature,
                            o.titre,
                            o.entreprise,
                            o.localisation,
                            o.type_contrat,
                            o.duree
                          FROM candidatures c
                          INNER JOIN offres o ON c.offre_id = o.id
                          WHERE c.user_id = :user_id AND c.statut = 'accepted'
                          ORDER BY c.date_candidature DESC";
        
        $accepte_stmt = $db->prepare($accepte_query);
        $accepte_stmt->bindParam(':user_id', $user_id);
        $accepte_stmt->execute();
        $candidatures_acceptees = $accepte_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Stages complétés (historique)
        $complete_query = "SELECT 
                            entreprise,
                            poste,
                            date_debut,
                            date_fin,
                            localisation,
                            description
                          FROM historique_stages
                          WHERE user_id = :user_id
                          ORDER BY date_fin DESC";
        
        $complete_stmt = $db->prepare($complete_query);
        $complete_stmt->bindParam(':user_id', $user_id);
        $complete_stmt->execute();
        $stages_completes = $complete_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si l'utilisateur est une entreprise, récupérer ses offres
        $offres_deposees = [];
        if ($user['type_compte'] === 'entreprise') {
            $offres_query = "SELECT 
                                o.id,
                                o.titre,
                                o.entreprise,
                                o.localisation,
                                o.duree,
                                o.date_publication,
                                o.statut,
                                (SELECT COUNT(*) FROM candidatures WHERE offre_id = o.id) as nb_candidatures
                             FROM offres o
                             WHERE o.user_id = :user_id
                             ORDER BY o.date_publication DESC";
            
            $offres_stmt = $db->prepare($offres_query);
            $offres_stmt->bindParam(':user_id', $user_id);
            $offres_stmt->execute();
            $offres_deposees = $offres_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Statistiques
        $stats = [
            'total_candidatures' => count($candidatures_attente) + count($candidatures_acceptees),
            'candidatures_attente' => count($candidatures_attente),
            'candidatures_acceptees' => count($candidatures_acceptees),
            'stages_completes' => count($stages_completes),
            'offres_deposees' => count($offres_deposees)
        ];
        
// ... (previous logic) ...
        echo json_encode([
            'success' => true,
            'user' => $user,
            'candidatures_attente' => $candidatures_attente,
            'candidatures_acceptees' => $candidatures_acceptees,
            'stages_completes' => $stages_completes,
            'offres_deposees' => $offres_deposees,
            'statistiques' => $stats
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}

// ACTION specific GET requests
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'enterprise_stats') {
        if ($_SESSION['user_type'] !== 'entreprise') {
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }
        
        try {
            $stats = [];
            // Offres actives
            $q1 = $db->prepare("SELECT COUNT(*) FROM offres WHERE user_id = :uid AND statut = 'active'");
            $q1->execute([':uid' => $user_id]);
            $stats['offres_actives'] = $q1->fetchColumn();
            
            // Total candidatures reçues
            $q2 = $db->prepare("SELECT COUNT(*) FROM candidatures c INNER JOIN offres o ON c.offre_id = o.id WHERE o.user_id = :uid");
            $q2->execute([':uid' => $user_id]);
            $stats['total_candidatures'] = $q2->fetchColumn();
            
            // Recrutements (candidatures acceptées)
            $q3 = $db->prepare("SELECT COUNT(*) FROM candidatures c INNER JOIN offres o ON c.offre_id = o.id WHERE o.user_id = :uid AND c.statut = 'accepte'");
            $q3->execute([':uid' => $user_id]);
            $stats['recrutements'] = $q3->fetchColumn();
            
            echo json_encode(['success' => true, 'stats' => $stats]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

// POST: update_profile_student (with files)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile_student') {
    try {
        $db->beginTransaction();

        // 1. Update basic user info
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $bio = $_POST['bio'] ?? '';

        $update_user = $db->prepare("UPDATE users SET nom = :nom, prenom = :prenom, email = :email, telephone = :tel, bio = :bio WHERE id = :uid");
        $update_user->execute([':nom' => $nom, ':prenom' => $prenom, ':email' => $email, ':tel' => $telephone, ':bio' => $bio, ':uid' => $user_id]);
        
        // Update session to reflect name changes instantly
        $_SESSION['user_nom'] = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_tel'] = $telephone;

        // Password change logic
        if (!empty($_POST['new_password']) && !empty($_POST['old_password'])) {
            $stmt = $db->prepare("SELECT password FROM users WHERE id = :uid");
            $stmt->execute([':uid' => $user_id]);
            $hash = $stmt->fetchColumn();
            if (password_verify($_POST['old_password'], $hash)) {
                $new_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $upd_pass = $db->prepare("UPDATE users SET password = :pw WHERE id = :uid");
                $upd_pass->execute([':pw' => $new_hash, ':uid' => $user_id]);
            }
        }

        // 2. Update profils info
        $titre = $_POST['titre_professionnel'] ?? null; // Optionally map titre to something. Could save mapped to skills momentarily or add column later. Let's save in specialite temporarily or skip if no column.
        $domaine = $_POST['domaine_formation'] ?? '';
        $skills = $_POST['skills'] ?? '';
        $statut = $_POST['statut_disponibilite'] ?? 'disponible';
        $niveau = $_POST['niveau_etudes'] ?? '';
        $specialite = $_POST['specialite'] ?? '';

        // Documents (CV and Letter) are now managed by api/documents.php instantly.
        // Form inputs in compte.php no longer have "name" for files, so $_FILES will be empty here.


        $sql_profils = "UPDATE profils SET domaine_formation = :domaine, skills = :skills, statut_disponibilite = :statut, niveau_etudes = :niveau, specialite = :specialite WHERE user_id = :uid";
        $update_profils = $db->prepare($sql_profils);
        $params_prof = [
            ':domaine' => $domaine, ':skills' => $skills, ':statut' => $statut,
            ':niveau' => $niveau, ':specialite' => $specialite, ':uid' => $user_id
        ];
        $update_profils->execute($params_prof);


        if ($update_profils->rowCount() === 0) {
            // Might not exist initially, let's insert if 0 and actually missing
            $check_prof = $db->prepare("SELECT id FROM profils WHERE user_id = :uid");
            $check_prof->execute([':uid' => $user_id]);
            if ($check_prof->rowCount() === 0) {
                // simple insert fallback
                $db->prepare("INSERT INTO profils (user_id) VALUES (:uid)")->execute([':uid' => $user_id]);
                $update_profils->execute($params_prof);
            }
        }

        // 3. Update Preferences
        $alertes = isset($_POST['alertes_offres']) ? (int)$_POST['alertes_offres'] : 1;
        $langue = $_POST['langue'] ?? 'fr';

        $check_pref = $db->prepare("SELECT id FROM preferences_utilisateur WHERE user_id = :uid");
        $check_pref->execute([':uid' => $user_id]);
        if ($check_pref->rowCount() > 0) {
            $upd_pref = $db->prepare("UPDATE preferences_utilisateur SET alertes_offres = :alertes, langue = :langue WHERE user_id = :uid");
            $upd_pref->execute([':alertes' => $alertes, ':langue' => $langue, ':uid' => $user_id]);
        } else {
            $ins_pref = $db->prepare("INSERT INTO preferences_utilisateur (user_id, alertes_offres, langue) VALUES (:uid, :alertes, :langue)");
            $ins_pref->execute([':alertes' => $alertes, ':langue' => $langue, ':uid' => $user_id]);
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Profil mis à jour']);

    } catch (PDOException $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
    }
}

// POST: upload_company_signature (entreprise only) - secure storage for acceptance message signature
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_company_signature') {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'entreprise') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }
    try {
        $db->exec("ALTER TABLE users ADD COLUMN company_signature_path VARCHAR(255) NULL");
    } catch (PDOException $e) { /* column may already exist */ }

    if (!isset($_FILES['signature']) || $_FILES['signature']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Veuillez sélectionner une image (signature).']);
        exit;
    }
    $file = $_FILES['signature'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB
    if (!in_array($file['type'], $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Format non accepté. Utilisez JPG, PNG, GIF ou WEBP.']);
        exit;
    }
    if ($file['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'Fichier trop volumineux (max 2 Mo).']);
        exit;
    }
    $upload_dir = __DIR__ . '/../uploads/signatures/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'png';
    $filename = 'sig_' . $user_id . '_' . time() . '.' . $ext;
    $path = $upload_dir . $filename;
    $db_path = 'uploads/signatures/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $path)) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.']);
        exit;
    }
    $stmt = $db->prepare("UPDATE users SET company_signature_path = :p WHERE id = :uid");
    $stmt->execute([':p' => $db_path, ':uid' => $user_id]);
    echo json_encode(['success' => true, 'message' => 'Signature enregistrée.', 'signature_url' => '../' . $db_path]);
}

// POST: remove_company_signature (entreprise only)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_company_signature') {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'entreprise') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }
    try {
        $stmt = $db->prepare("UPDATE users SET company_signature_path = NULL WHERE id = :uid");
        $stmt->execute([':uid' => $user_id]);
        echo json_encode(['success' => true, 'message' => 'Signature supprimée.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// PUT : Mettre à jour les informations du profil
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
// ...
    parse_str(file_get_contents("php://input"), $_PUT);
    
    try {
        $update_fields = [];
        $params = [':user_id' => $user_id];
        
        $allowed_fields = [
            'nom', 'prenom', 'email', 'telephone', 
            'date_naissance', 'adresse', 'ville', 'pays', 
            'bio', 'linkedin_url', 'portfolio_url',
            'visibilite_entreprise'
        ];

        
        foreach ($allowed_fields as $field) {
            if (isset($_PUT[$field])) {
                $update_fields[] = "$field = :$field";
                $params[":$field"] = trim($_PUT[$field]);
            }
        }
        
        if (empty($update_fields)) {
            echo json_encode(['success' => false, 'message' => 'Aucun champ à mettre à jour']);
            exit;
        }
        
        $update_query = "UPDATE users SET " . implode(', ', $update_fields) . 
                       " WHERE id = :user_id";
        
        $update_stmt = $db->prepare($update_query);
        foreach ($params as $key => $value) {
            $update_stmt->bindValue($key, $value);
        }
        
        if ($update_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Profil mis à jour avec succès'
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}
?>