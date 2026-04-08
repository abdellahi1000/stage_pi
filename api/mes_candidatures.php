<?php
// candidatures.php - Gestion des candidatures

session_start();

// Réutiliser la classe Database de config.php
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

/**
 * S'assure que les colonnes d'acceptation existent dans `candidatures`
 * afin d'éviter les erreurs MySQL « Unknown column acceptance_message ».
 */
function ensureAcceptanceColumns($db)
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    try {
        $cols = [
            'acceptance_message'    => "TEXT NULL",
            'acceptance_date'       => "DATETIME NULL",
            'company_contact_email' => "VARCHAR(255) NULL",
            'company_contact_phone' => "VARCHAR(50) NULL",
            'company_whatsapp'      => "VARCHAR(50) NULL",
            'type_contrat'          => "VARCHAR(50) DEFAULT 'Stage'",
        ];

        foreach ($cols as $col => $type) {
            $check = $db->prepare("SHOW COLUMNS FROM candidatures LIKE :col");
            $check->execute([':col' => $col]);
            if ($check->rowCount() === 0) {
                try {
                    $db->exec("ALTER TABLE candidatures ADD COLUMN $col $type");
                } catch (PDOException $e) {
                    // Ne jamais bloquer l'API étudiant si l'ajout échoue
                }
            }
        }
        
        // Ensure lm_specifique and reponses_questions columns
        $check = $db->prepare("SHOW COLUMNS FROM candidatures LIKE 'lm_specifique'");
        $check->execute();
        if ($check->rowCount() === 0) {
            try { $db->exec("ALTER TABLE candidatures ADD COLUMN lm_specifique VARCHAR(255) NULL AFTER cv_specifique"); } catch (PDOException $e) {}
        }
        
        $check = $db->prepare("SHOW COLUMNS FROM candidatures LIKE 'reponses_questions'");
        $check->execute();
        if ($check->rowCount() === 0) {
            try { $db->exec("ALTER TABLE candidatures ADD COLUMN reponses_questions TEXT NULL AFTER message_motivation"); } catch (PDOException $e) {}
        }
    } catch (Exception $e) {
        // Filet de sécurité : on continue même si la vérification échoue
    }
}

ensureAcceptanceColumns($db);

/**
 * Ensure company signature column exists in users (for acceptance display).
 */
function ensureCompanySignatureColumn($db)
{
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $check = $db->query("SHOW COLUMNS FROM users LIKE 'company_signature_path'");
        if ($check->rowCount() === 0) {
            $db->exec("ALTER TABLE users ADD COLUMN company_signature_path VARCHAR(255) NULL");
        }
    } catch (Exception $e) { /* non-blocking */ }
}
ensureCompanySignatureColumn($db);

// GET : Récupérer toutes les candidatures de l'utilisateur (étudiant uniquement en pratique)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT 
                    c.id,
                    c.offre_id,
                    c.date_candidature,
                    c.statut,
                    c.message_motivation,
                    c.acceptance_message,
                    c.acceptance_date,
                    c.company_contact_email,
                    c.company_contact_phone,
                    c.company_whatsapp,
                    o.title AS titre,
                    o.entreprise,
                    o.localisation,
                    o.type_contrat,
                    o.nombre_stagiaires,
                    u.verified_status,
                    u.company_signature_path
                  FROM candidatures c
                  INNER JOIN offres o ON c.offre_id = o.id
                  INNER JOIN users u ON o.user_id = u.id
                  WHERE c.user_id = :user_id
                  ORDER BY c.date_candidature DESC";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $candidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($candidatures as &$c) {
            $c['statut'] = strtolower($c['statut']);
        }

        // Mark as viewed by student (clearing notifications)
        $update = $db->prepare("UPDATE candidatures SET vu_par_etudiant = 1 WHERE user_id = :user_id AND statut != 'pending' AND vu_par_etudiant = 0");
        $update->execute([':user_id' => $user_id]);


        echo json_encode([
            'success' => true,
            'count' => count($candidatures),
            'candidatures' => $candidatures
        ]);

    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}

// POST : Créer une nouvelle candidature
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offre_id = isset($_POST['offre_id']) ? intval($_POST['offre_id']) : 0;
    $message_motivation = isset($_POST['message_motivation']) ? trim($_POST['message_motivation']) : '';
    $cv_option = isset($_POST['cv_option']) ? $_POST['cv_option'] : 'profile'; // 'profile' or 'new'
    $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
    
    $lm_option = isset($_POST['lm_option']) ? $_POST['lm_option'] : 'profile';
    $lm_id = isset($_POST['lm_id']) ? intval($_POST['lm_id']) : 0;

    if ($offre_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID offre invalide']);
        exit;
    }

    try {
        // Obtenir l'entreprise (user_id de l'offre)
        $offre_stmt = $db->prepare("SELECT user_id, entreprise FROM offres WHERE id = :offre_id");
        $offre_stmt->execute([':offre_id' => $offre_id]);
        $offre = $offre_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offre) {
            echo json_encode(['success' => false, 'message' => 'L\'offre n\'existe pas']);
            exit;
        }

        $entreprise_user_id = $offre['user_id'];

        // rule check
        // 1. Is the student already accepted somewhere?
        $check_accepted = $db->prepare("SELECT id FROM candidatures WHERE user_id = :user_id AND statut = 'accepted'");
        $check_accepted->execute([':user_id' => $user_id]);
        if ($check_accepted->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Félicitations ! Vous avez déjà été accepté pour un stage. Vous ne pouvez plus postuler à d\'autres offres.'
            ]);
            exit;
        }

        // 2. Total active applications check (Max 3)
        // Active means 'pending' (formerly 'en_attente'/'vue') or 'accepted'.
        // Rejected or closed applications must NOT count.
        $count_stmt = $db->prepare("SELECT COUNT(id) FROM candidatures WHERE user_id = :user_id AND statut IN ('pending', 'accepted')");
        $count_stmt->execute([':user_id' => $user_id]);
        $total_active = intval($count_stmt->fetchColumn());

        if ($total_active >= 3) {
            echo json_encode([
                'success' => false, 
                'message' => 'Limite atteinte : Vous ne pouvez pas postuler à plus de 3 entreprises en même temps.'
            ]);
            exit;
        }

        // 3. One application per company PERMANENTLY (even if refused or closed by another acceptance)
        // If they have any existing application, block them.
        $check_company_query = "SELECT c.id FROM candidatures c
                                INNER JOIN offres o ON c.offre_id = o.id
                                WHERE c.user_id = :user_id 
                                AND o.user_id = :entreprise_user_id";
        $check_company_stmt = $db->prepare($check_company_query);
        $check_company_stmt->execute([':user_id' => $user_id, ':entreprise_user_id' => $entreprise_user_id]);

        if ($check_company_stmt->rowCount() > 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Vous ne pouvez plus postuler à cette entreprise. Une décision a déjà été prise ou une candidature est en cours.'
            ]);
            exit;
        }

        // Gestion du CV
        $cv_path_to_save = null;

        if ($cv_option === 'new') {
            if (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Veuillez uploader un CV valide (PDF, DOC, DOCX)']);
                exit;
            }
            $ext = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
                echo json_encode(['success' => false, 'message' => 'Format de CV non accepté. Utilisez PDF, DOC ou DOCX.']);
                exit;
            }

            $upload_dir = __DIR__ . '/../uploads/cvs/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $filename = 'cv_app_' . $user_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $upload_dir . $filename)) {
                $cv_path_to_save = 'uploads/cvs/' . $filename;
                
                // Mettre à jour le profil si on souhaite le sauvegarder
                if (isset($_POST['save_recent_cv']) && $_POST['save_recent_cv'] == '1') {
                    // Update latest profile link
                    $upd = $db->prepare("UPDATE profils SET cv_path = :cv WHERE user_id = :uid");
                    $upd->execute([':cv' => $cv_path_to_save, ':uid' => $user_id]);
                    
                    // ALSO add to history table
                    $hist = $db->prepare("INSERT INTO etudiant_documents (user_id, type, file_path, file_name) VALUES (:uid, 'cv', :path, :name)");
                    $hist->execute([':uid' => $user_id, ':path' => $cv_path_to_save, ':name' => $_FILES['cv_file']['name']]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload du fichier.']);
                exit;
            }
        } else {
            // Option 'profile' -> get from etudiant_documents if ID provided, else fallback to latest profil cv
            if ($document_id > 0) {
                $check_doc = $db->prepare("SELECT file_path FROM etudiant_documents WHERE id = :id AND user_id = :uid");
                $check_doc->execute([':id' => $document_id, ':uid' => $user_id]);
                $cv_path_to_save = $check_doc->fetchColumn();
            }
            
            if (empty($cv_path_to_save)) {
                // Last fallback: latest profile CV (backward compatibility)
                $prof_stmt = $db->prepare("SELECT cv_path FROM profils WHERE user_id = :uid");
                $prof_stmt->execute([':uid' => $user_id]);
                $cv_path_to_save = $prof_stmt->fetchColumn();
            }

            if (empty($cv_path_to_save)) {
                echo json_encode(['success' => false, 'message' => 'Veuillez sélectionner un CV de votre profil ou en uploader un nouveau.']);
                exit;
            }
        }

        // Gestion de la Lettre de Motivation
        $lm_path_to_save = null;
        if ($lm_option === 'new' && isset($_FILES['lm_file']) && $_FILES['lm_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['lm_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'doc', 'docx'])) {
                $upload_dir = __DIR__ . '/../uploads/cvs/';
                $filename = 'lm_app_' . $user_id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['lm_file']['tmp_name'], $upload_dir . $filename)) {
                    $lm_path_to_save = 'uploads/cvs/' . $filename;
                    
                    if (isset($_POST['save_recent_lm']) && $_POST['save_recent_lm'] == '1') {
                        $upd = $db->prepare("UPDATE profils SET lettre_motivation_path = :lm WHERE user_id = :uid");
                        $upd->execute([':lm' => $lm_path_to_save, ':uid' => $user_id]);
                        
                        $hist = $db->prepare("INSERT INTO etudiant_documents (user_id, type, file_path, file_name) VALUES (:uid, 'motivation', :path, :name)");
                        $hist->execute([':uid' => $user_id, ':path' => $lm_path_to_save, ':name' => $_FILES['lm_file']['name']]);
                    }
                }
            }
        } elseif ($lm_option === 'profile') {
            if ($lm_id > 0) {
                $check_doc = $db->prepare("SELECT file_path FROM etudiant_documents WHERE id = :id AND user_id = :uid");
                $check_doc->execute([':id' => $lm_id, ':uid' => $user_id]);
                $lm_path_to_save = $check_doc->fetchColumn();
            }
            if (empty($lm_path_to_save)) {
                $prof_stmt = $db->prepare("SELECT lettre_motivation_path FROM profils WHERE user_id = :uid");
                $prof_stmt->execute([':uid' => $user_id]);
                $lm_path_to_save = $prof_stmt->fetchColumn();
            }
        }

        // Questions reponses: gather all reponses_questions[] into JSON
        $reponses_questions = null;
        if (isset($_POST['reponses_questions']) && is_array($_POST['reponses_questions'])) {
            $reponses_questions = json_encode($_POST['reponses_questions'], JSON_UNESCAPED_UNICODE);
        }

        // Insérer la nouvelle candidature
        $check_col = $db->query("SHOW COLUMNS FROM candidatures LIKE 'cv_specifique'");
        $has_cv_col = ($check_col->rowCount() > 0);
        $check_lm = $db->query("SHOW COLUMNS FROM candidatures LIKE 'lm_specifique'");
        $has_lm_col = ($check_lm->rowCount() > 0);
        $check_rep = $db->query("SHOW COLUMNS FROM candidatures LIKE 'reponses_questions'");
        $has_rep_col = ($check_rep->rowCount() > 0);

        $cols = "user_id, offre_id, message_motivation, statut, type_contrat";
        $vals = ":user_id, :offre_id, :message, 'pending', :type_contrat";
        $params = [
            ':user_id' => $user_id,
            ':offre_id' => $offre_id,
            ':message' => $message_motivation,
            ':type_contrat' => $_POST['type_contrat'] ?? 'Stage'
        ];

        if ($has_cv_col) { $cols .= ", cv_specifique"; $vals .= ", :cv_path"; $params[':cv_path'] = $cv_path_to_save; }
        if ($has_lm_col) { $cols .= ", lm_specifique"; $vals .= ", :lm_path"; $params[':lm_path'] = $lm_path_to_save; }
        if ($has_rep_col) { $cols .= ", reponses_questions"; $vals .= ", :rep"; $params[':rep'] = $reponses_questions; }

        $insert_query = "INSERT INTO candidatures ($cols) VALUES ($vals)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->execute($params);

        echo json_encode([
            'success' => true,
            'message' => 'Candidature envoyée avec succès ! L\'entreprise a été notifiée.',
            'candidature_id' => $db->lastInsertId()
        ]);

    } catch(PDOException $e) {
        if ($e->getCode() == '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
            echo json_encode([
                'success' => false,
                'message' => 'Vous avez déjà envoyé une candidature à cette entreprise (ou cette offre précise).'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur technique : ' . $e->getMessage()
            ]);
        }
    }
}

// DELETE : Retirer une candidature
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $candidature_id = isset($_DELETE['candidature_id']) ? intval($_DELETE['candidature_id']) : 0;

    if ($candidature_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID candidature invalide']);
        exit;
    }

    try {
        $delete_query = "DELETE FROM candidatures 
                        WHERE id = :candidature_id AND user_id = :user_id";
        
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':candidature_id', $candidature_id);
        $delete_stmt->bindParam(':user_id', $user_id);
        $delete_stmt->execute();

        if ($delete_stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Candidature supprimée'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Candidature introuvable'
            ]);
        }

    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}

// auth.php - Gestion de l'authentification simple
/*
<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, nom, prenom, email, password, type_compte 
                 FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_type'] = $user['type_compte'];
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'user' => [
                        'id' => $user['id'],
                        'nom' => $user['nom'],
                        'prenom' => $user['prenom'],
                        'type' => $user['type_compte']
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Mot de passe incorrect'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Email non trouvé'
            ]);
        }
    }
    
    elseif ($action === 'register') {
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $type_compte = $_POST['type_compte']; // 'etudiant' ou 'entreprise'
        
        $database = new Database();
        $db = $database->getConnection();
        
        // Vérifier si l'email existe déjà
        $check = "SELECT id FROM users WHERE email = :email";
        $check_stmt = $db->prepare($check);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Email déjà utilisé'
            ]);
            exit;
        }
        
        $insert = "INSERT INTO users (nom, prenom, email, password, type_compte) 
                  VALUES (:nom, :prenom, :email, :password, :type)";
        $insert_stmt = $db->prepare($insert);
        $insert_stmt->bindParam(':nom', $nom);
        $insert_stmt->bindParam(':prenom', $prenom);
        $insert_stmt->bindParam(':email', $email);
        $insert_stmt->bindParam(':password', $password);
        $insert_stmt->bindParam(':type', $type_compte);
        
        if ($insert_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Inscription réussie'
            ]);
        }
    }
    
    elseif ($action === 'logout') {
        session_destroy();
        echo json_encode([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }
}
?>
*/

// create_tables_candidatures.sql - Tables supplémentaires
/*
USE stagematch;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    type_compte ENUM('etudiant', 'entreprise', 'admin') DEFAULT 'etudiant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des candidatures
CREATE TABLE IF NOT EXISTS candidatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    offre_id INT NOT NULL,
    date_candidature DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'accepte', 'refuse', 'retiree') DEFAULT 'en_attente',
    message_motivation TEXT,
    cv_file VARCHAR(255),
    lettre_motivation_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (offre_id) REFERENCES offres(id) ON DELETE CASCADE,
    UNIQUE KEY unique_candidature (user_id, offre_id),
    INDEX idx_user_id (user_id),
    INDEX idx_offre_id (offre_id),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de test
INSERT INTO users (nom, prenom, email, password, type_compte) VALUES
('Dupont', 'Jean', 'jean.dupont@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant'),
('Martin', 'Marie', 'marie.martin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant');
-- Mot de passe : "password"

INSERT INTO candidatures (user_id, offre_id, statut, message_motivation, date_candidature) VALUES
(1, 1, 'en_attente', 'Je suis très motivé pour ce poste...', '2025-12-15 10:00:00'),
(1, 2, 'accepte', 'Passionné par le marketing digital...', '2025-12-10 14:30:00'),
(1, 3, 'refuse', 'Designer créatif avec expérience...', '2025-12-05 09:15:00'),
(2, 1, 'en_attente', 'Compétences en développement web...', '2026-01-01 11:20:00');
*/
?>