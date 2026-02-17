<?php
// offres.php - Gestion des offres de stage

session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

// GET : Récupérer toutes les offres ou les offres d'une entreprise
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id_raw = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    $user_id = ($user_id_raw === 'me') ? (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null) : intval($user_id_raw);

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $statut_filter = isset($_GET['statut']) ? trim($_GET['statut']) : '';
    $type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    try {
        $query = "SELECT 
                    o.id,
                    o.titre,
                    o.entreprise,
                    o.description,
                    o.localisation,
                    o.type_contrat,
                    o.duree,
                    o.remuneration,
                    o.competences_requises,
                    o.date_publication,
                    o.date_limite,
                    o.statut,
                    o.user_id,
                    o.categorie_id,
                    u.nom as recruteur_nom,
                    u.prenom as recruteur_prenom,
                    u.email as recruteur_email,
                    (SELECT COUNT(*) FROM candidatures WHERE offre_id = o.id) as nombre_candidatures
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

        if (!empty($search)) {
            $query .= " AND (o.titre LIKE :search OR o.entreprise LIKE :search OR o.localisation LIKE :search)";
        }
        if (!empty($statut_filter)) {
            $query .= " AND o.statut = :statut";
        }
        if (!empty($type_filter)) {
            $query .= " AND o.type_contrat = :type_contrat";
        }
        
        $query .= " ORDER BY o.date_publication DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($query);
        
        if ($user_id) $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(':search', $search_param);
        }
        if (!empty($statut_filter)) $stmt->bindParam(':statut', $statut_filter);
        if (!empty($type_filter)) $stmt->bindParam(':type_contrat', $type_filter);
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'count' => count($offres),
            'offres' => $offres
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// POST : Créer une nouvelle offre
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'entreprise') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $titre = trim($_POST['titre']);
    $entreprise = $_SESSION['user_nom'];
    $description = trim($_POST['description']);
    $localisation = trim($_POST['localisation']);
    $type_contrat = $_POST['type_contrat'] ?? 'Stage';
    $duree = $_POST['duree'] ?? '';
    $remuneration = $_POST['remuneration'] ?? '';
    $categorie_id = isset($_POST['categorie_id']) ? intval($_POST['categorie_id']) : null;
    $statut = $_POST['statut'] ?? 'active';
    
    try {
        $stmt = $db->prepare("INSERT INTO offres_stage (user_id, titre, entreprise, description, localisation, type_contrat, duree, remuneration, statut, categorie_id) VALUES (:user_id, :titre, :entreprise, :description, :localisation, :type_contrat, :duree, :remuneration, :statut, :categorie_id)");
        
        $stmt->execute([
            ':user_id' => $user_id,
            ':titre' => $titre,
            ':entreprise' => $entreprise,
            ':description' => $description,
            ':localisation' => $localisation,
            ':type_contrat' => $type_contrat,
            ':duree' => $duree,
            ':remuneration' => $remuneration,
            ':statut' => $statut,
            ':categorie_id' => $categorie_id
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
    
    $user_id = $_SESSION['user_id'];
    $id = isset($_PUT['id']) ? intval($_PUT['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID invalide']);
        exit;
    }
    
    try {
        // Build dynamic update
        $fields = [];
        $params = [':id' => $id, ':user_id' => $user_id];
        
        $allowed = ['titre', 'description', 'localisation', 'type_contrat', 'duree', 'remuneration', 'statut', 'categorie_id'];
        foreach ($allowed as $f) {
            if (isset($_PUT[$f])) {
                $fields[] = "$f = :$f";
                $params[":$f"] = $_PUT[$f];
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
    if (!isset($_SESSION['user_id'])) exit;
    
    $id = intval($_DELETE['offre_id']);
    try {
        $stmt = $db->prepare("DELETE FROM offres_stage WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $id, ':user_id' => $_SESSION['user_id']]);
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>