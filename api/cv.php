<?php
// api/cv.php - Gestion des données CV (Table: cv_user_data)

session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Migration: Ensure all requested columns exist
        $cols_to_add = [
            'website' => 'VARCHAR(255)',
            'linkedin' => 'VARCHAR(255)',
            'philosophy' => 'TEXT',
            'strengths' => 'TEXT',
            'certifications' => 'TEXT',
            'projects' => 'TEXT',
            'skills_extra' => 'TEXT',
            'tools' => 'TEXT',
            'achievements' => 'TEXT',
            'volunteer' => 'TEXT',
            'workshops' => 'TEXT',
            'interests' => 'TEXT',
            'extra_info' => 'TEXT'
        ];
        foreach ($cols_to_add as $col => $type) {
            $db->exec("ALTER TABLE cv_user_data ADD COLUMN IF NOT EXISTS $col $type AFTER certifications");
        }

        $query = "SELECT cv.*, u.nom as user_nom, u.prenom as user_prenom, u.email as user_email, u.telephone as user_telephone
                  FROM cv_user_data cv 
                  LEFT JOIN users u ON cv.user_id = u.id
                  WHERE cv.user_id = :user_id 
                  LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $cv = $stmt->fetch(PDO::FETCH_ASSOC);
            $cv['experiences'] = json_decode($cv['experiences'] ?? '[]', true) ?: [];
            $cv['formations'] = json_decode($cv['education'] ?? '[]', true) ?: [];
            $cv['competences'] = json_decode($cv['skills'] ?? '[]', true) ?: [];
            
            echo json_encode(['success' => true, 'cv' => $cv]);
        } else {
            $user_query = "SELECT nom, prenom, email, telephone FROM users WHERE id = :user_id";
            $user_stmt = $db->prepare($user_query);
            $user_stmt->bindParam(':user_id', $user_id);
            $user_stmt->execute();
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'cv' => null, 'user' => $user]);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom_complet'] ?? '');
    $poste = trim($_POST['poste_souhaite'] ?? '');
    $tel = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $about = trim($_POST['resume_professionnel'] ?? '');
    $philosophy = trim($_POST['philosophy'] ?? '');
    $strengths = trim($_POST['strengths'] ?? '');
    $languages = trim($_POST['languages'] ?? '');
    $references_data = trim($_POST['references_data'] ?? '');
    $certifications = trim($_POST['certifications'] ?? '');
    $projects = trim($_POST['projects'] ?? '');
    
    // New 7 fields
    $skills_extra = trim($_POST['skills_extra'] ?? '');
    $tools = trim($_POST['tools'] ?? '');
    $achievements = trim($_POST['achievements'] ?? '');
    $volunteer = trim($_POST['volunteer'] ?? '');
    $workshops = trim($_POST['workshops'] ?? '');
    $interests = trim($_POST['interests'] ?? '');
    $extra_info = trim($_POST['extra_info'] ?? '');

    $photo_base64 = $_POST['photo_base64'] ?? null;
    
    $skills = array_filter(array_map('trim', explode("\n", $_POST['competences'] ?? '')));

    try {
        $sql = "INSERT INTO cv_user_data (user_id, nom, poste, tel, email, ville, website, linkedin, about, philosophy, strengths, experiences, education, skills, languages, references_data, certifications, projects, skills_extra, tools, achievements, volunteer, workshops, interests, extra_info" . ($photo_base64 ? ", photo_base64" : "") . ") 
                VALUES (:uid, :nom, :poste, :tel, :email, :ville, :web, :li, :about, :philo, :strengths, :exp, :edu, :skills, :lang, :ref, :cert, :proj, :se, :tools, :ach, :vol, :work, :inte, :extra" . ($photo_base64 ? ", :photo" : "") . ")
                ON DUPLICATE KEY UPDATE 
                nom = VALUES(nom), poste = VALUES(poste), tel = VALUES(tel), email = VALUES(email), ville = VALUES(ville), 
                website = VALUES(website), linkedin = VALUES(linkedin), about = VALUES(about), philosophy = VALUES(philosophy), 
                strengths = VALUES(strengths), experiences = VALUES(experiences), education = VALUES(education), skills = VALUES(skills), 
                languages = VALUES(languages), references_data = VALUES(references_data), certifications = VALUES(certifications), 
                projects = VALUES(projects), skills_extra = VALUES(skills_extra), tools = VALUES(tools), achievements = VALUES(achievements), 
                volunteer = VALUES(volunteer), workshops = VALUES(workshops), interests = VALUES(interests), extra_info = VALUES(extra_info), updated_at = NOW()" . ($photo_base64 ? ", photo_base64 = VALUES(photo_base64)" : "");
        
        $stmt = $db->prepare($sql);
        $params = [
            ':uid' => $user_id,
            ':nom' => $nom,
            ':poste' => $poste,
            ':tel' => $tel,
            ':email' => $email,
            ':ville' => $ville,
            ':web' => $website,
            ':li' => $linkedin,
            ':about' => $about,
            ':philo' => $philosophy,
            ':strengths' => $strengths,
            ':exp' => $_POST['experiences'] ?? '[]',
            ':edu' => $_POST['formations'] ?? '[]',
            ':skills' => json_encode($skills, JSON_UNESCAPED_UNICODE),
            ':lang' => $languages,
            ':ref' => $references_data,
            ':cert' => $certifications,
            ':proj' => $projects,
            ':se' => $skills_extra,
            ':tools' => $tools,
            ':ach' => $achievements,
            ':vol' => $volunteer,
            ':work' => $workshops,
            ':inte' => $interests,
            ':extra' => $extra_info
        ];
        if ($photo_base64) $params[':photo'] = $photo_base64;
        
        $stmt->execute($params);
        echo json_encode(['success' => true, 'message' => 'CV mis à jour']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}
?>
