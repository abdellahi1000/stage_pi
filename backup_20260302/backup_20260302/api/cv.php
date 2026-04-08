<?php
// cv_manager.php - Gestion des CV

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

// GET : Récupérer le CV de l'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT 
                    cv.*,
                    u.nom as user_nom,
                    u.prenom as user_prenom,
                    u.email as user_email,
                    u.telephone as user_telephone
                  FROM cv 
                  LEFT JOIN users u ON cv.user_id = u.id
                  WHERE cv.user_id = :user_id
                  ORDER BY cv.updated_at DESC
                  LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $cv = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Décoder les champs JSON
            $cv['experiences'] = json_decode($cv['experiences'], true) ?: [];
            $cv['formations'] = json_decode($cv['formations'], true) ?: [];
            $cv['competences'] = json_decode($cv['competences'], true) ?: [];
            
            echo json_encode([
                'success' => true,
                'cv' => $cv
            ]);
        } else {
            // Pas de CV existant, retourner les données de l'utilisateur
            $user_query = "SELECT nom, prenom, email, telephone FROM users WHERE id = :user_id";
            $user_stmt = $db->prepare($user_query);
            $user_stmt->bindParam(':user_id', $user_id);
            $user_stmt->execute();
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'cv' => null,
                'user' => $user
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}

// POST : Créer ou mettre à jour le CV
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_complet = trim($_POST['nom_complet']);
    $poste_souhaite = trim($_POST['poste_souhaite']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $ville = trim($_POST['ville']);
    $resume_professionnel = trim($_POST['resume_professionnel']);
    
    // Traiter les expériences (format JSON)
    $experiences_raw = isset($_POST['experiences']) ? $_POST['experiences'] : '';
    $experiences = [];
    if (!empty($experiences_raw)) {
        $exp_lines = explode("\n", $experiences_raw);
        foreach ($exp_lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $parts = explode("|", $line);
                if (count($parts) >= 3) {
                    $experiences[] = [
                        'poste' => trim($parts[0]),
                        'entreprise' => trim($parts[1]),
                        'periode' => trim($parts[2]),
                        'description' => isset($parts[3]) ? trim($parts[3]) : ''
                    ];
                }
            }
        }
    }
    
    // Traiter les formations (format JSON)
    $formations_raw = isset($_POST['formations']) ? $_POST['formations'] : '';
    $formations = [];
    if (!empty($formations_raw)) {
        $form_lines = explode("\n", $formations_raw);
        foreach ($form_lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $parts = explode("|", $line);
                if (count($parts) >= 3) {
                    $formations[] = [
                        'diplome' => trim($parts[0]),
                        'etablissement' => trim($parts[1]),
                        'annee' => trim($parts[2])
                    ];
                }
            }
        }
    }
    
    // Traiter les compétences (format JSON)
    $competences_raw = isset($_POST['competences']) ? $_POST['competences'] : '';
    $competences = [];
    if (!empty($competences_raw)) {
        $comp_array = explode(",", $competences_raw);
        foreach ($comp_array as $comp) {
            $comp = trim($comp);
            if (!empty($comp)) {
                $competences[] = $comp;
            }
        }
    }
    
    // Validation
    if (empty($nom_complet) || empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Le nom et l\'email sont obligatoires'
        ]);
        exit;
    }
    
    try {
        // Vérifier si un CV existe déjà
        $check_query = "SELECT id FROM cv WHERE user_id = :user_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':user_id', $user_id);
        $check_stmt->execute();
        
        $experiences_json = json_encode($experiences, JSON_UNESCAPED_UNICODE);
        $formations_json = json_encode($formations, JSON_UNESCAPED_UNICODE);
        $competences_json = json_encode($competences, JSON_UNESCAPED_UNICODE);
        
        if ($check_stmt->rowCount() > 0) {
            // Mettre à jour le CV existant
            $cv_id = $check_stmt->fetch(PDO::FETCH_ASSOC)['id'];
            
            $update_query = "UPDATE cv SET 
                            nom_complet = :nom,
                            poste_souhaite = :poste,
                            telephone = :tel,
                            email = :email,
                            ville = :ville,
                            resume_professionnel = :resume,
                            experiences = :exp,
                            formations = :form,
                            competences = :comp,
                            updated_at = NOW()
                            WHERE id = :cv_id";
            
            $stmt = $db->prepare($update_query);
            $stmt->bindParam(':cv_id', $cv_id);
            $message = 'CV mis à jour avec succès';
            
        } else {
            // Créer un nouveau CV
            $update_query = "INSERT INTO cv 
                            (user_id, nom_complet, poste_souhaite, telephone, email, ville, 
                             resume_professionnel, experiences, formations, competences) 
                            VALUES 
                            (:user_id, :nom, :poste, :tel, :email, :ville, 
                             :resume, :exp, :form, :comp)";
            
            $stmt = $db->prepare($update_query);
            $stmt->bindParam(':user_id', $user_id);
            $message = 'CV créé avec succès';
        }
        
        $stmt->bindParam(':nom', $nom_complet);
        $stmt->bindParam(':poste', $poste_souhaite);
        $stmt->bindParam(':tel', $telephone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':ville', $ville);
        $stmt->bindParam(':resume', $resume_professionnel);
        $stmt->bindParam(':exp', $experiences_json);
        $stmt->bindParam(':form', $formations_json);
        $stmt->bindParam(':comp', $competences_json);
        
        if ($stmt->execute()) {
            $cv_id = isset($cv_id) ? $cv_id : $db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'cv_id' => $cv_id
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}

// DELETE : Supprimer le CV
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $delete_query = "DELETE FROM cv WHERE user_id = :user_id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':user_id', $user_id);
        $delete_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'CV supprimé avec succès'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}
?>