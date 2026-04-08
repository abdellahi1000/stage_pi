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
                            o.title AS titre,
                            COALESCE(e.name, o.entreprise) as entreprise,
                            o.localisation,
                            o.type_contrat
                          FROM candidatures c
                          INNER JOIN offres o ON c.offre_id = o.id
                          LEFT JOIN entreprises e ON o.entreprise_id = e.id
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
                            o.title AS titre,
                            COALESCE(e.name, o.entreprise) as entreprise,
                            o.localisation,
                            o.type_contrat,
                            o.duree
                          FROM candidatures c
                          INNER JOIN offres o ON c.offre_id = o.id
                          LEFT JOIN entreprises e ON o.entreprise_id = e.id
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
                                o.title AS titre,
                                COALESCE(e.name, o.entreprise) as entreprise,
                                o.localisation,
                                o.duree,
                                o.date_publication,
                                o.statut,
                                (SELECT COUNT(*) FROM candidatures WHERE offre_id = o.id) as nb_candidatures
                             FROM offres o
                             LEFT JOIN entreprises e ON o.entreprise_id = e.id
                             WHERE o.user_id = :user_id
                             ORDER BY o.date_publication DESC";
            
            $offres_stmt = $db->prepare($offres_query);
            $offres_stmt->bindParam(':user_id', $user_id);
            $offres_stmt->execute();
            $offres_deposees = $offres_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Defensive stats counting
        $total_messages = 0;
        $total_favorites = 0;
        $total_candidatures = 0;

        try {
            // Ensure status column exists and count unread support messages
            try {
                $chkStatus = $db->query("SHOW COLUMNS FROM support_messages LIKE 'status'");
                if ($chkStatus && $chkStatus->rowCount() === 0) {
                    $db->exec("ALTER TABLE support_messages ADD COLUMN status VARCHAR(20) DEFAULT 'unread'");
                }
            } catch (PDOException $e) {}

            $msg_count_stmt = $db->prepare("SELECT COUNT(*) FROM support_messages WHERE user_id = :user_id AND sender_type IN ('support', 'admin') AND status = 'unread'");
            $msg_count_stmt->execute([':user_id' => $user_id]);
            $total_messages = (int)$msg_count_stmt->fetchColumn();
        } catch (PDOException $e) {}

        try {
            // Count favorites
            $fav_count_stmt = $db->prepare("SELECT COUNT(*) FROM offer_favorites WHERE student_id = :user_id");
            $fav_count_stmt->execute([':user_id' => $user_id]);
            $total_favorites = (int)$fav_count_stmt->fetchColumn();
        } catch (PDOException $e) {}

        // Count all candidatures
        $cand_all_stmt = $db->prepare("SELECT COUNT(*) FROM candidatures WHERE user_id = :user_id");
        $cand_all_stmt->execute([':user_id' => $user_id]);
        $total_candidatures = (int)$cand_all_stmt->fetchColumn();

        $stats = [
            'total_candidatures' => $total_candidatures,
            'candidatures_attente' => count($candidatures_attente),
            'candidatures_acceptees' => count($candidatures_acceptees),
            'stages_completes' => count($stages_completes),
            'offres_deposees' => count($offres_deposees),
            'total_messages' => $total_messages,
            'total_favorites' => $total_favorites
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

        // Password change logic (with current password)
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if ($newPassword || $confirmPassword) {
            if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
                echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs de mot de passe.']);
                $db->rollBack();
                exit;
            }
            if (strlen($newPassword) < 8) {
                echo json_encode(['success' => false, 'message' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.']);
                $db->rollBack();
                exit;
            }
            if ($newPassword !== $confirmPassword) {
                echo json_encode(['success' => false, 'message' => 'Les nouveaux mots de passe ne correspondent pas.']);
                $db->rollBack();
                exit;
            }

            $stmt = $db->prepare("SELECT password FROM users WHERE id = :uid");
            $stmt->execute([':uid' => $user_id]);
            $hash = $stmt->fetchColumn();
            if (!password_verify($oldPassword, $hash)) {
                echo json_encode(['success' => false, 'message' => 'Le mot de passe actuel est incorrect.']);
                $db->rollBack();
                exit;
            }

            $new_hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $upd_pass = $db->prepare("UPDATE users SET password = :pw WHERE id = :uid");
            $upd_pass->execute([':pw' => $new_hash, ':uid' => $user_id]);
        }

        // 2. Update profils info
        $titre = $_POST['titre_professionnel'] ?? null; // Optionally map titre to something. Could save mapped to skills momentarily or add column later. Let's save in specialite temporarily or skip if no column.
        $domaine = $_POST['domaine_formation'] ?? '';
        $skills = $_POST['skills'] ?? '';
        $statut = $_POST['statut_disponibilite'] ?? 'disponible';
        $niveau = $_POST['niveau_etudes'] ?? '';
        $specialite = $_POST['specialite'] ?? '';
        $universite = $_POST['universite'] ?? '';

        // Documents (CV and Letter) are now managed by api/documents.php instantly.
        // Form inputs in compte.php no longer have "name" for files, so $_FILES will be empty here.


        $sql_profils = "UPDATE profils SET domaine_formation = :domaine, skills = :skills, statut_disponibilite = :statut, niveau_etudes = :niveau, specialite = :specialite, universite = :univ WHERE user_id = :uid";
        $update_profils = $db->prepare($sql_profils);
        $params_prof = [
            ':domaine' => $domaine, ':skills' => $skills, ':statut' => $statut,
            ':niveau' => $niveau, ':specialite' => $specialite, ':univ' => $universite, ':uid' => $user_id
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

// POST: update enterprise notifications preferences
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_enterprise_notifications') {
    if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'employee' && $_SESSION['user_role'] !== 'admin')) {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }

    $newApps = !empty($_POST['new_applications']) ? 1 : 0;
    $interviews = !empty($_POST['interview_alerts']) ? 1 : 0;
    $messages = !empty($_POST['internal_messages']) ? 1 : 0;

    try {
        $db->exec("CREATE TABLE IF NOT EXISTS entreprise_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            notif_new_applications TINYINT(1) DEFAULT 1,
            notif_interview_alerts TINYINT(1) DEFAULT 1,
            notif_internal_messages TINYINT(1) DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $stmt = $db->prepare("INSERT INTO entreprise_notifications (user_id, notif_new_applications, notif_interview_alerts, notif_internal_messages)
                              VALUES (:uid, :n1, :n2, :n3)
                              ON DUPLICATE KEY UPDATE
                                notif_new_applications = VALUES(notif_new_applications),
                                notif_interview_alerts = VALUES(notif_interview_alerts),
                                notif_internal_messages = VALUES(notif_internal_messages)");
        $stmt->execute([
            ':uid' => $user_id,
            ':n1' => $newApps,
            ':n2' => $interviews,
            ':n3' => $messages,
        ]);

        echo json_encode(['success' => true, 'message' => 'Préférences de notification mises à jour.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

// POST: reset password for logged-in user without old password (enterprise or student)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password_logged_in') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez saisir et confirmer le nouveau mot de passe.']);
        exit;
    }
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères.']);
        exit;
    }
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
        exit;
    }

    try {
        $new_hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $upd = $db->prepare("UPDATE users SET password = :pw, reset_token = NULL, reset_token_expiry = NULL WHERE id = :uid");
        $upd->execute([':pw' => $new_hash, ':uid' => $user_id]);
        echo json_encode(['success' => true, 'message' => 'Votre mot de passe a été réinitialisé.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

// POST: upload_company_signature (entreprise only) - secure storage for acceptance message signature
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_company_signature') {
    if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'employee' && $_SESSION['user_role'] !== 'admin')) {
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

// POST: delete_photo (remove profile photo - set to NULL in database)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_photo') {
    try {
        $stmt = $db->prepare("SELECT photo_profil FROM users WHERE id = :uid");
        $stmt->execute([':uid' => $user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $old_path = $row['photo_profil'] ?? null;

        $upd = $db->prepare("UPDATE users SET photo_profil = NULL WHERE id = :uid");
        $upd->execute([':uid' => $user_id]);

        if ($upd->rowCount() > 0) {
            $_SESSION['photo_profil'] = null;
            if (!empty($old_path)) {
                $physical_path = __DIR__ . '/../' . $old_path;
                if (file_exists($physical_path)) {
                    @unlink($physical_path);
                }
            }
            echo json_encode(['success' => true, 'message' => 'Photo supprimée.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Aucune photo à supprimer.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_company_signature') {
    if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'employee' && $_SESSION['user_role'] !== 'admin')) {
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
            'visibilite_entreprise',
            'year_established', 'additional_emails',
            'industry_sector', 'company_size', 'website_url',
            'tax_identification_number'
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

// POST: update_emails (entreprise only)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_emails') {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'entreprise') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }

    try {
        $emails = $_POST['emails'] ?? [];
        
        // Validate emails
        $valid_emails = [];
        foreach ($emails as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $valid_emails[] = $email;
            }
        }
        
        // Store as JSON string in additional_emails field
        $emails_json = json_encode($valid_emails);
        
        $stmt = $db->prepare("UPDATE users SET additional_emails = :emails WHERE id = :user_id");
        $stmt->bindParam(':emails', $emails_json);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Emails mis à jour avec succès'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des emails'
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}

// GET: get_emails (entreprise only)
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_emails') {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'entreprise') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }

    try {
        $stmt = $db->prepare("SELECT additional_emails FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $additional_emails = [];
        
        if ($result && !empty($result['additional_emails'])) {
            $additional_emails = json_decode($result['additional_emails'], true) ?: [];
        }
        
        echo json_encode([
            'success' => true,
            'emails' => $additional_emails
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile_enterprise') {
    $entreprise_id = (int)($_SESSION['entreprise_id'] ?? 0);
    if ($entreprise_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Aucune entreprise associée à votre compte.']);
        exit;
    }

    try {
        $nom = trim($_POST['nom'] ?? '');
        $industry = trim($_POST['industry'] ?? '');
        $size = trim($_POST['size'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $hr_manager = trim($_POST['hr_manager'] ?? '');

        // 1. Update the 'entreprises' table (Source of Truth)
        $q_ent = $db->prepare("UPDATE entreprises SET 
                                 name = :nom, 
                                 secteur = :industry, 
                                 taille = :size, 
                                 website_url = :website, 
                                 bio = :bio, 
                                 adresse = :address,
                                 hr_manager = :hr
                               WHERE id = :eid");
        $q_ent->execute([
            ':nom' => $nom,
            ':industry' => $industry,
            ':size' => $size,
            ':website' => $website,
            ':bio' => $bio,
            ':address' => $address,
            ':hr' => $hr_manager,
            ':eid' => $entreprise_id
        ]);

        // 2. Update all users belonging to this enterprise to sync shared fields
        // We sync 'nom' (as user_nom) and 'industry_sector' for compatibility
        $q_users = $db->prepare("UPDATE users SET 
                                    nom = :nom,
                                    industry_sector = :industry,
                                    company_size = :size,
                                    website_url = :website
                                  WHERE entreprise_id = :eid");
        $q_users->execute([
            ':nom' => $nom,
            ':industry' => $industry,
            ':size' => $size,
            ':website' => $website,
            ':eid' => $entreprise_id
        ]);

        $_SESSION['user_nom'] = $nom;
        echo json_encode(['success' => true, 'message' => 'Profil de l\'entreprise mis à jour avec succès.']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
    }
}

// POST: update_enterprise_security (email, phone, password for enterprise)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_enterprise_security') {
    if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'employee' && $_SESSION['user_role'] !== 'admin')) {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }
    
    try {
        $db->beginTransaction();
        
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        
        if ($email === '') {
            echo json_encode(['success' => false, 'message' => 'L\'email est obligatoire.']);
            if ($db->inTransaction()) $db->rollBack();
            exit;
        }

        // Update basic user info
        $update_user = $db->prepare("UPDATE users SET email = :email, telephone = :tel WHERE id = :uid");
        $update_user->execute([':email' => $email, ':tel' => $telephone, ':uid' => $user_id]);
        
        $_SESSION['user_email'] = $email;
        $_SESSION['user_tel'] = $telephone;

        // Password change logic
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($newPassword || $confirmPassword) {
            if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
                echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs de mot de passe.']);
                if ($db->inTransaction()) $db->rollBack();
                exit;
            }
            if (strlen($newPassword) < 8) {
                echo json_encode(['success' => false, 'message' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.']);
                if ($db->inTransaction()) $db->rollBack();
                exit;
            }
            if ($newPassword !== $confirmPassword) {
                echo json_encode(['success' => false, 'message' => 'Les nouveaux mots de passe ne correspondent pas.']);
                if ($db->inTransaction()) $db->rollBack();
                exit;
            }

            $stmt = $db->prepare("SELECT password FROM users WHERE id = :uid");
            $stmt->execute([':uid' => $user_id]);
            $hash = $stmt->fetchColumn();
            if (!password_verify($oldPassword, $hash)) {
                echo json_encode(['success' => false, 'message' => 'Le mot de passe actuel est incorrect.']);
                if ($db->inTransaction()) $db->rollBack();
                exit;
            }

            $new_hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $upd_pass = $db->prepare("UPDATE users SET password = :pw WHERE id = :uid");
            $upd_pass->execute([':pw' => $new_hash, ':uid' => $user_id]);
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Sécurité et contact mis à jour avec succès.']);
    } catch (PDOException $e) {
        if ($db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}
?>