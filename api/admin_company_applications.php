<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || ($_SESSION['user_type'] !== 'entreprise' && $_SESSION['user_type'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$company_id = $_SESSION['company_id'];
$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? 'list';

require_once '../include/check_permission.php';

if ($action === 'list' || $action === 'update_status') {
    require_permission('can_manage_candidates', $db);
}
if ($action === 'block_student' || $action === 'unblock_student' || $action === 'blocked_list') {
    require_permission('can_block_users', $db);
}

try {
    if ($action === 'blocked_list') {
        $stmt = $db->prepare("SELECT b.student_id, b.reason, b.blocked_at, u.nom, u.prenom, u.email
                              FROM blocked_students b
                              JOIN users u ON b.student_id = u.id
                              WHERE b.company_id = :cid
                              ORDER BY b.blocked_at DESC");
        $stmt->execute([':cid' => $company_id]);
        $blocked = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'blocked' => $blocked]);
        exit;
    }

    if ($action === 'unblock_student') {
        $data = json_decode(file_get_contents('php://input'), true);
        $student_id = $data['student_id'] ?? null;
        if ($student_id) {
            $stmt = $db->prepare("DELETE FROM blocked_students WHERE company_id = :cid AND student_id = :sid");
            $stmt->execute([':cid' => $company_id, ':sid' => $student_id]);
            echo json_encode(['success' => true, 'message' => 'Étudiant débloqué avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID étudiant manquant']);
        }
        exit;
    }
    if ($action === 'list') {
        $stmt = $db->prepare("SELECT c.*, u.nom, u.prenom, u.email, u.telephone, 
                                     o.titre as offre_titre, o.user_id as recruteur_id, o.type_contrat, o.questions as offer_questions,
                                     p.specialite, p.universite, p.skills, p.domaine_formation, p.niveau_etudes
                              FROM candidatures c 
                              JOIN users u ON c.user_id = u.id 
                              JOIN offres_stage o ON c.offre_id = o.id 
                              LEFT JOIN profils p ON u.id = p.user_id
                              WHERE o.user_id = :cid 
                              ORDER BY c.date_candidature DESC");
        $stmt->execute([':cid' => $company_id]);
        $apps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'applications' => $apps]);

    } elseif ($action === 'update_status') {
        $data = json_decode(file_get_contents('php://input'), true);
        $app_id = $data['id'] ?? null;
        $status = $data['status'] ?? '';
        
        if ($app_id && in_array($status, ['accepted', 'rejected', 'pending'])) {
            // Verify ownership within company
            $stmt_v = $db->prepare("SELECT c.id FROM candidatures c 
                                    JOIN offres_stage o ON c.offre_id = o.id 
                                    WHERE c.id = :id AND o.user_id = :cid");
            $stmt_v->execute([':id' => $app_id, ':cid' => $company_id]);
            
            if ($stmt_v->rowCount() > 0) {
                $stmt = $db->prepare("UPDATE candidatures SET statut = :status, vu_par_etudiant = 0, acceptance_date = (CASE WHEN :status = 'accepted' THEN NOW() ELSE acceptance_date END) WHERE id = :id");
                $stmt->execute([
                    ':status' => $status,
                    ':id' => $app_id
                ]);
                echo json_encode(['success' => true, 'message' => "Candidature mise à jour ($status)"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Candidature non trouvée ou accès refusé']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
        }

    } elseif ($action === 'block_student') {
        $data = json_decode(file_get_contents('php://input'), true);
        $student_id = $data['student_id'] ?? null;
        $reason = $data['reason'] ?? 'Bloqué par l\'administrateur de l\'entreprise';

        if ($student_id) {
            $stmt = $db->prepare("INSERT INTO blocked_students (company_id, student_id, reason) 
                                  VALUES (:cid, :sid, :reason) 
                                  ON DUPLICATE KEY UPDATE reason = VALUES(reason), blocked_at = NOW()");
            $stmt->execute([
                ':cid' => $company_id,
                ':sid' => $student_id,
                ':reason' => $reason
            ]);

            // Also reject all current pending applications from this student for this company
            $stmt_rej = $db->prepare("UPDATE candidatures c 
                                      JOIN offres_stage o ON c.offre_id = o.id 
                                      SET c.statut = 'rejected' 
                                      WHERE c.user_id = :sid AND o.user_id = :cid AND c.statut = 'pending'");
            $stmt_rej->execute([':sid' => $student_id, ':cid' => $company_id]);

            echo json_encode(['success' => true, 'message' => 'Étudiant bloqué avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID étudiant manquant']);
        }
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
