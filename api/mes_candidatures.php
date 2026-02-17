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

// GET : Récupérer toutes les candidatures de l'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT 
                    c.id,
                    c.offre_id,
                    c.date_candidature,
                    c.statut,
                    c.message_motivation,
                    o.titre,
                    o.entreprise,
                    o.localisation,
                    o.type_contrat
                  FROM candidatures c
                  INNER JOIN offres_stage o ON c.offre_id = o.id
                  WHERE c.user_id = :user_id
                  ORDER BY c.date_candidature DESC";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $candidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mark as viewed by student (clearing notifications)
        $update = $db->prepare("UPDATE candidatures SET vu_par_etudiant = 1 WHERE user_id = :user_id AND statut != 'en_attente' AND vu_par_etudiant = 0");
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

    if ($offre_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID offre invalide']);
        exit;
    }

    try {
        // Vérifier si une candidature existe déjà
        $check_query = "SELECT id FROM candidatures 
                       WHERE user_id = :user_id AND offre_id = :offre_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':user_id', $user_id);
        $check_stmt->bindParam(':offre_id', $offre_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Vous avez déjà postulé à cette offre'
            ]);
            exit;
        }

        // Gestion du fichier CV
        $cv_path = null;
        if (isset($_FILES['cv_specifique']) && $_FILES['cv_specifique']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $file_type = mime_content_type($_FILES['cv_specifique']['tmp_name']);
            
            if (!in_array($file_type, $allowed_types)) {
                echo json_encode(['success' => false, 'message' => 'Format de fichier non supporté (PDF, DOC, DOCX uniquement)']);
                exit;
            }

            // Créer le dossier s'il n'existe pas
            $upload_dir = '../uploads/cvs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Générer un nom unique
            $extension = pathinfo($_FILES['cv_specifique']['name'], PATHINFO_EXTENSION);
            $filename = 'cv_' . $user_id . '_' . $offre_id . '_' . time() . '.' . $extension;
            $target_file = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['cv_specifique']['tmp_name'], $target_file)) {
                $cv_path = 'uploads/cvs/' . $filename; // Chemin relatif pour la BDD
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement du CV']);
                exit;
            }
        }

        // Insérer la nouvelle candidature
        $insert_query = "INSERT INTO candidatures 
                        (user_id, offre_id, message_motivation, cv_specifique, statut) 
                        VALUES (:user_id, :offre_id, :message, :cv_path, 'en_attente')";
        
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':user_id', $user_id);
        $insert_stmt->bindParam(':offre_id', $offre_id);
        $insert_stmt->bindParam(':message', $message_motivation);
        $insert_stmt->bindParam(':cv_path', $cv_path);
        $insert_stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Candidature envoyée avec succès',
            'candidature_id' => $db->lastInsertId()
        ]);

    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
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
    FOREIGN KEY (offre_id) REFERENCES offres_stage(id) ON DELETE CASCADE,
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