<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

// Check if logged in. Allow 'admin' or 'entreprise' types.
if (!isset($_SESSION['logged_in']) || ($_SESSION['user_type'] !== 'entreprise' && $_SESSION['user_type'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$is_global_admin = $_SESSION['user_type'] === 'admin';
$company_id = $_SESSION['entreprise_id'] ?? $_SESSION['user_id'];
$user_id = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? 'list';

require_once '../include/check_permission.php';

if ($action === 'delete') {
    require_permission('can_delete_offers', $db);
}
// `save` handles both Create and Edit, permissions checked inside.

try {
    if ($action === 'list') {
        // Global admin sees ALL offers; enterprise sees only their own
        if ($is_global_admin) {
            $stmt = $db->prepare("SELECT o.*, o.title as titre, u.nom as company_name, 
                                  (SELECT COUNT(*) FROM candidatures c WHERE c.offre_id = o.id AND c.type_contrat = 'Stage') as total_stagiaires,
                                  (SELECT COUNT(*) FROM candidatures c WHERE c.offre_id = o.id AND (c.type_contrat = 'Alternance' OR c.type_contrat = 'Apprentissage')) as total_alternances,
                                  (SELECT COUNT(*) FROM candidatures c WHERE c.offre_id = o.id) as total_candidatures 
                                  FROM offres o 
                                  LEFT JOIN users u ON o.user_id = u.id
                                  ORDER BY o.date_publication DESC");
            $stmt->execute();
        } else {
            $stmt = $db->prepare("SELECT o.*, o.title as titre, 
                                  (SELECT COUNT(*) FROM candidatures c WHERE c.offre_id = o.id AND c.type_contrat = 'Stage') as total_stagiaires,
                                  (SELECT COUNT(*) FROM candidatures c WHERE c.offre_id = o.id AND (c.type_contrat = 'Alternance' OR c.type_contrat = 'Apprentissage')) as total_alternances,
                                  (SELECT COUNT(*) FROM candidatures c WHERE c.offre_id = o.id) as total_candidatures 
                                  FROM offres o 
                                  WHERE o.entreprise_id = :cid 
                                  ORDER BY o.date_publication DESC");
            $stmt->execute([':cid' => $_SESSION['entreprise_id']]);
        }
        $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'offres' => $offres]);

    } elseif ($action === 'save') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) $data = $_POST;

        $id = $data['id'] ?? null;
        $titre = $data['titre'] ?? '';
        $description = $data['description'] ?? '';
        $localisation = $data['localisation'] ?? '';
        $type_contrat = $data['type_contrat'] ?? 'Stage';
        $duree = $data['duree'] ?? '';
        $categorie_id = $data['categorie_id'] ?? null;
        $nombre_stagiaires = $data['nombre_stagiaires'] ?? 1;
        $statut = $data['statut'] ?? 'active';
        $specialization = $data['specialization'] ?? '';
        $technologies = $data['technologies'] ?? '';
        $questions = $data['questions'] ?? '';
        $tags = $data['tags'] ?? '';
        $places_alternances = $data['places_alternances'] ?? 0;

        // Specific permissions for type
        if ($type_contrat === 'Stage') require_permission('can_create_stagiaire', $db);
        if ($type_contrat === 'Alternance') require_permission('can_create_alternance', $db);

        // Check permission if not admin
        if (!$is_global_admin && !empty($id)) {
             // For employees, we might check if they are the owner OR if they have can_delete/edit permission
             // For now assume if not admin, only owner can edit.
        }

        // Get company name - use entreprises table instead of users
        $stmt_comp = $db->prepare("SELECT name FROM entreprises WHERE id = :cid");
        $stmt_comp->execute([':cid' => $_SESSION['entreprise_id']]);
        $entreprise_name = $stmt_comp->fetchColumn();

        if ($id) {
            require_permission('can_edit_offers', $db);
            // Update - check by enterprise_id
            if (!$is_global_admin) {
                $stmt_v = $db->prepare("SELECT id FROM offres WHERE id = :id AND entreprise_id = :eid");
                $stmt_v->execute([':id' => $id, ':eid' => $_SESSION['entreprise_id']]);
                if ($stmt_v->rowCount() === 0) {
                     echo json_encode(['success' => false, 'message' => "Offre non trouvée ou accès refusé"]);
                     exit;
                }
            }
            $stmt = $db->prepare("UPDATE offres SET 
                                    title = :titre, 
                                    description = :description, 
                                    localisation = :localisation, 
                                    type_contrat = :type_contrat, 
                                    duree = :duree, 
                                    categorie_id = :categorie_id, 
                                    nombre_stagiaires = :nombre_stagiaires, 
                                    statut = :statut,
                                    specialization = :specialization,
                                    technologies = :technologies,
                                    questions = :questions,
                                    tags = :tags,
                                    places_alternances = :places_alternances,
                                    entreprise_id = :eid
                                  WHERE id = :id");
            $stmt->execute([
                ':titre' => $titre,
                ':description' => $description,
                ':localisation' => $localisation,
                ':type_contrat' => $type_contrat,
                ':duree' => $duree,
                ':categorie_id' => $categorie_id ?: null,
                ':nombre_stagiaires' => $nombre_stagiaires ?: 1,
                ':statut' => $statut,
                ':specialization' => $specialization,
                ':technologies' => $technologies,
                ':questions' => $questions,
                ':tags' => $tags,
                ':places_alternances' => $places_alternances,
                ':eid' => $_SESSION['entreprise_id'],
                ':id' => $id
            ]);
            echo json_encode(['success' => true, 'message' => 'Offre mise à jour']);
        } else {
            require_permission('can_create_offers', $db);
            // Crucial Fix: user_id references users.id (must be valid user), entreprise_id groups them
            $stmt = $db->prepare("INSERT INTO offres (user_id, entreprise_id, title, description, entreprise, localisation, type_contrat, duree, categorie_id, nombre_stagiaires, places_alternances, statut, specialization, technologies, questions, tags) 
                                  VALUES (:uid, :eid, :titre, :description, :entreprise, :localisation, :type_contrat, :duree, :categorie_id, :nombre_stagiaires, :places_alternances, :statut, :specialization, :technologies, :questions, :tags)");
            $stmt->execute([
                ':uid' => $_SESSION['user_id'], // Valid User ID for foreign key
                ':eid' => $_SESSION['entreprise_id'], // Enterprise grouping ID
                ':titre' => $titre,
                ':description' => $description,
                ':entreprise' => $entreprise_name ?: 'Entreprise',
                ':localisation' => $localisation,
                ':type_contrat' => $type_contrat,
                ':duree' => $duree,
                ':categorie_id' => $categorie_id ?: null,
                ':nombre_stagiaires' => $nombre_stagiaires ?: 1,
                ':places_alternances' => $places_alternances,
                ':statut' => $statut,
                ':specialization' => $specialization,
                ':technologies' => $technologies,
                ':questions' => $questions,
                ':tags' => $tags
            ]);
            echo json_encode(['success' => true, 'message' => 'Offre créée']);
        }

    } elseif ($action === 'delete') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Global admin can delete any offer
            $can_delete = $is_global_admin;
            if (!$can_delete) {
                $stmt_v = $db->prepare("SELECT id FROM offres WHERE id = :id AND (user_id = :cid OR entreprise_id = :cid)");
                $stmt_v->execute([':id' => $id, ':cid' => $company_id]);
                $can_delete = ($stmt_v->rowCount() > 0);
            }
            if ($can_delete) {
                $stmt = $db->prepare("DELETE FROM offres WHERE id = :id");
                $stmt->execute([':id' => $id]);
                echo json_encode(['success' => true, 'message' => 'Offre supprimée']);
            } else {
                 echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
        }
    }

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
