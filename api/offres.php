<?php
// offres.php - Gestion des offres de stage

session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

// Ensure nombre_stagiaires column exists
try {
    $chk = $db->query("SHOW COLUMNS FROM offres_stage LIKE 'nombre_stagiaires'");
    if ($chk->rowCount() === 0) {
        $db->exec("ALTER TABLE offres_stage ADD COLUMN nombre_stagiaires INT DEFAULT 1");
    }
} catch (PDOException $e) { /* non-blocking */ }

// Valid offer types: Stage and Alternance only
define('VALID_TYPE_CONTRAT', ['Stage', 'Alternance']);

// GET : Récupérer toutes les offres ou les offres d'une entreprise
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once '../include/check_permission.php';
    $user_id_raw = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    $user_id = ($user_id_raw === 'me') ? get_enterprise_id() : intval($user_id_raw);

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $statut_filter = isset($_GET['statut']) ? trim($_GET['statut']) : '';
    $type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';
    $localisation_filter = isset($_GET['localisation']) ? trim($_GET['localisation']) : '';
    $categorie_filter = isset($_GET['categorie_id']) ? trim($_GET['categorie_id']) : '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    try {
        $current_user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        // Global rules check for the current user (if student)
        $total_active = 0;
        $is_accepted_globally = false;
        
        if ($current_user && $_SESSION['user_type'] === 'etudiant') {
            $check_rules = $db->prepare("SELECT 
                COUNT(*) as total, 
                SUM(CASE WHEN statut = 'accepted' THEN 1 ELSE 0 END) as accepted_count 
                FROM candidatures WHERE user_id = :uid AND statut IN ('pending', 'accepted')");
            $check_rules->execute([':uid' => $current_user]);
            $rules_res = $check_rules->fetch();
            $total_active = (int)$rules_res['total'];
            $is_accepted_globally = (int)$rules_res['accepted_count'] > 0;
        }

        $query = "SELECT 
                    o.id,
                    o.titre,
                    o.entreprise,
                    o.description,
                    o.localisation,
                    o.type_contrat,
                    o.nombre_stagiaires,
                    o.duree,
                    o.remuneration,
                    o.competences_requises,
                    o.date_publication,
                    o.date_limite,
                    o.statut,
                    o.user_id as entreprise_id,
                    o.categorie_id,
                    o.specialization,
                    o.technologies,
                    o.questions,
                    o.tags,
                    u.nom as recruteur_nom,
                    u.prenom as recruteur_prenom,
                    u.email as recruteur_email,
                    u.verified_status,
                    u.photo_profil as entreprise_photo,
                    (SELECT COUNT(*) FROM candidatures WHERE offre_id = o.id) as nombre_candidatures,
                    (SELECT COUNT(*) FROM candidatures c2 INNER JOIN offres_stage o2 ON c2.offre_id = o2.id 
                     WHERE c2.user_id = :current_user AND o2.user_id = o.user_id) as deja_postule_entreprise,
                    (SELECT COUNT(*) FROM offer_favorites WHERE offer_id = o.id) as total_favorites,
                    (SELECT COUNT(*) FROM offer_favorites WHERE offer_id = o.id AND student_id = :current_user) as is_favorited
                  FROM offres_stage o
                  LEFT JOIN users u ON o.user_id = u.id
                  WHERE 1=1";
        
        // Filtres
        if ($user_id) {
            $query .= " AND o.user_id = :user_id";
        } else {
            // Pour les étudiants, on ne montre que les offres actives
            // Et on vérifie la visibilité globale de l'entreprise
            $query .= " AND o.statut = 'active' AND u.visibilite_entreprise = 1";
        }

        $only_favoris = isset($_GET['favoris']) ? intval($_GET['favoris']) : 0;
        if ($only_favoris === 1 && $current_user) {
            $query .= " AND EXISTS (SELECT 1 FROM offer_favorites WHERE offer_id = o.id AND student_id = :current_user)";
        }

        if (!empty($search)) {
            $query .= " AND (o.titre LIKE :search OR o.entreprise LIKE :search OR o.localisation LIKE :search)";
        }
        if (!empty($statut_filter)) {
            $query .= " AND o.statut = :statut";
        }
        if (!empty($type_filter)) {
            $query .= " AND o.type_contrat = :type_contrat";
        }
        if (!empty($localisation_filter)) {
            $query .= " AND o.localisation = :localisation";
        }
        if (!empty($categorie_filter)) {
            $query .= " AND o.categorie_id = :categorie_id";
        }
        
        $query .= " ORDER BY o.date_publication DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':current_user', $current_user, PDO::PARAM_INT);
        
        if ($user_id) $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(':search', $search_param);
        }
        if (!empty($statut_filter)) $stmt->bindParam(':statut', $statut_filter);
        if (!empty($type_filter)) $stmt->bindParam(':type_contrat', $type_filter);
        if (!empty($localisation_filter)) $stmt->bindParam(':localisation', $localisation_filter);
        if (!empty($categorie_filter)) $stmt->bindParam(':categorie_id', $categorie_filter, PDO::PARAM_INT);
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'count' => count($offres),
            'offres' => $offres,
            'total_active_candidatures' => $total_active,
            'is_accepted_globally' => $is_accepted_globally
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// POST : Créer une nouvelle offre
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../include/check_permission.php';
    require_permission('can_create_offers', $db);
    
    $user_id = get_enterprise_id();
    $titre = trim($_POST['titre']);
    $entreprise = $_SESSION['company_name'] ?? $_SESSION['user_nom'];
    $description = trim($_POST['description']);
    $localisation = trim($_POST['localisation']);
    $type_contrat = $_POST['type_contrat'] ?? 'Stage';
    if (!in_array($type_contrat, VALID_TYPE_CONTRAT)) {
        $type_contrat = 'Stage';
    }
    $nombre_stagiaires = isset($_POST['nombre_stagiaires']) ? max(1, min(99, intval($_POST['nombre_stagiaires']))) : 1;

    $duree = $_POST['duree'] ?? '';
    $remuneration = $_POST['remuneration'] ?? '';
    $categorie_id = isset($_POST['categorie_id']) ? intval($_POST['categorie_id']) : null;
    $specialization = $_POST['specialization'] ?? '';
    $technologies = $_POST['technologies'] ?? '';
    $questions = $_POST['questions'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $statut = $_POST['statut'] ?? 'active';
    
    try {
        $stmt = $db->prepare("INSERT INTO offres_stage (user_id, titre, entreprise, description, localisation, type_contrat, nombre_stagiaires, duree, remuneration, statut, categorie_id, specialization, technologies, questions, tags) VALUES (:user_id, :titre, :entreprise, :description, :localisation, :type_contrat, :nombre_stagiaires, :duree, :remuneration, :statut, :categorie_id, :specialization, :technologies, :questions, :tags)");
        
        $stmt->execute([
            ':user_id' => $user_id,
            ':titre' => $titre,
            ':entreprise' => $entreprise,
            ':description' => $description,
            ':localisation' => $localisation,
            ':type_contrat' => $type_contrat,
            ':nombre_stagiaires' => $nombre_stagiaires,
            ':duree' => $duree,
            ':remuneration' => $remuneration,
            ':statut' => $statut,
            ':categorie_id' => $categorie_id,
            ':specialization' => $specialization,
            ':technologies' => $technologies,
            ':questions' => $questions,
            ':tags' => $tags
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Offre publiée']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// PUT : Modifier une offre
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Non connecté']);
        exit;
    }
    
    require_once '../include/check_permission.php';
    require_permission('can_edit_offers', $db);
    $user_id = get_enterprise_id();
    $id = isset($_PUT['id']) ? intval($_PUT['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID invalide']);
        exit;
    }
    
    try {
        // Build dynamic update
        $fields = [];
        $params = [':id' => $id, ':user_id' => $user_id];
        
        $allowed = ['titre', 'description', 'localisation', 'type_contrat', 'nombre_stagiaires', 'duree', 'remuneration', 'statut', 'categorie_id', 'specialization', 'technologies', 'questions', 'tags'];
        foreach ($allowed as $f) {
            if (isset($_PUT[$f])) {
                $val = $_PUT[$f];
                if ($f === 'type_contrat' && !in_array($val, VALID_TYPE_CONTRAT)) {
                    $val = 'Stage';
                }
                if ($f === 'nombre_stagiaires') {
                    $val = max(1, min(99, intval($val)));
                }

                $fields[] = "$f = :$f";
                $params[":$f"] = $val;
            }
        }
        
        if (empty($fields)) {
            echo json_encode(['success' => false, 'message' => 'Rien à modifier']);
            exit;
        }
        
        $sql = "UPDATE offres_stage SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Offre mise à jour']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// DELETE : Supprimer une offre
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Non connecté']);
        exit;
    }
    
    require_once '../include/check_permission.php';
    require_permission('can_delete_offers', $db);
    $user_id = get_enterprise_id();
    
    $id = intval($_DELETE['offre_id']);
    try {
        $stmt = $db->prepare("DELETE FROM offres_stage WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $id, ':user_id' => $user_id]);
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>