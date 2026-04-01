<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

// Check if logged in. Allow 'admin' or 'entreprise' types.
if (!isset($_SESSION['logged_in']) || ($_SESSION['user_type'] !== 'entreprise' && $_SESSION['user_type'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Administrator';
$company_id = $_SESSION['company_id'];
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
        // Find all users belonging to this company
        $stmt = $db->prepare("SELECT o.*, (SELECT COUNT(*) FROM candidatures c WHERE c.offre_id = o.id) as total_candidatures 
                              FROM offres_stage o 
                              WHERE o.user_id = :cid 
                              ORDER BY o.date_publication DESC");
        $stmt->execute([':cid' => $company_id]);
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

        // Check permission if not admin
        if (!$is_admin && !empty($id)) {
             // For employees, we might check if they are the owner OR if they have can_delete/edit permission
             // For now assume if not admin, only owner can edit.
        }

        // Get company name for the 'entreprise' column (from the manager's record or company record)
        $stmt_comp = $db->prepare("SELECT nom FROM users WHERE id = :cid");
        $stmt_comp->execute([':cid' => $company_id]);
        $entreprise_name = $stmt_comp->fetchColumn();

        if ($id) {
            require_permission('can_edit_offers', $db);
            // Update - only if offer belongs to someone in the same company
            $stmt_v = $db->prepare("SELECT id FROM offres_stage WHERE id = :id AND user_id = :cid");
            $stmt_v->execute([':id' => $id, ':cid' => $company_id]);
            if ($stmt_v->rowCount() === 0) {
                 echo json_encode(['success' => false, 'message' => "Offre non trouvée ou accès refusé"]);
                 exit;
            }

            $stmt = $db->prepare("UPDATE offres_stage SET 
                                    titre = :titre, 
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
                                    archived_by_admin = :archived_by_admin
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
                ':archived_by_admin' => ($is_admin && $statut === 'archivee') ? 1 : 0,
                ':id' => $id
            ]);
            echo json_encode(['success' => true, 'message' => 'Offre mise à jour']);
        } else {
            require_permission('can_create_offers', $db);
            // Create
            $stmt = $db->prepare("INSERT INTO offres_stage (user_id, titre, description, entreprise, localisation, type_contrat, duree, categorie_id, nombre_stagiaires, statut, specialization, technologies, questions, tags) 
                                  VALUES (:uid, :titre, :description, :entreprise, :localisation, :type_contrat, :duree, :categorie_id, :nombre_stagiaires, :statut, :specialization, :technologies, :questions, :tags)");
            $stmt->execute([
                ':uid' => $company_id,
                ':titre' => $titre,
                ':description' => $description,
                ':entreprise' => $entreprise_name ?: 'Entreprise',
                ':localisation' => $localisation,
                ':type_contrat' => $type_contrat,
                ':duree' => $duree,
                ':categorie_id' => $categorie_id ?: null,
                ':nombre_stagiaires' => $nombre_stagiaires ?: 1,
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
            $stmt_v = $db->prepare("SELECT id FROM offres_stage WHERE id = :id AND user_id = :cid");
            $stmt_v->execute([':id' => $id, ':cid' => $company_id]);
            if ($stmt_v->rowCount() > 0) {
                $stmt = $db->prepare("DELETE FROM offres_stage WHERE id = :id");
                $stmt->execute([':id' => $id]);
                echo json_encode(['success' => true, 'message' => 'Offre supprimée']);
            } else {
                 echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
        }
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
