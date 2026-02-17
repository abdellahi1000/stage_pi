<?php
// gerer_candidats.php - Gestion des candidatures côté entreprise

session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté et est une entreprise
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

if ($_SESSION['user_type'] !== 'entreprise') {
    echo json_encode(['success' => false, 'message' => 'Accès réservé aux entreprises']);
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

// GET : Récupérer les candidatures selon l'action
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    try {
        if ($action === 'enterprise_list') {
            $offre_id = isset($_GET['offre_id']) ? intval($_GET['offre_id']) : null;
            $statut = isset($_GET['statut']) ? $_GET['statut'] : null;
            
            $query = "SELECT c.*, u.nom, u.prenom, u.email, o.titre as offre_titre 
                      FROM candidatures c 
                      INNER JOIN users u ON c.user_id = u.id 
                      INNER JOIN offres_stage o ON c.offre_id = o.id 
                      WHERE o.user_id = :user_id";
            
            if ($offre_id) $query .= " AND c.offre_id = :offre_id";
            if ($statut) $query .= " AND c.statut = :statut";
            
            $query .= " ORDER BY c.date_candidature DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            if ($offre_id) $stmt->bindParam(':offre_id', $offre_id);
            if ($statut) $stmt->bindParam(':statut', $statut);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'candidatures' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            exit;
        }

        if ($action === 'recent_enterprise') {
            $query = "SELECT c.*, u.nom, u.prenom, o.titre as offre_titre 
                      FROM candidatures c 
                      INNER JOIN users u ON c.user_id = u.id 
                      INNER JOIN offres_stage o ON c.offre_id = o.id 
                      WHERE o.user_id = :user_id 
                      ORDER BY c.date_candidature DESC LIMIT 5";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'candidatures' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            exit;
        }

        if ($action === 'details') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $query = "SELECT c.*, u.nom, u.prenom, u.email, o.titre as offre_titre 
                      FROM candidatures c 
                      INNER JOIN users u ON c.user_id = u.id 
                      INNER JOIN offres_stage o ON c.offre_id = o.id 
                      WHERE c.id = :id AND o.user_id = :user_id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $c = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($c) {
                if ($c['statut'] === 'en_attente') {
                    $up = $db->prepare("UPDATE candidatures SET statut = 'vue' WHERE id = :id");
                    $up->execute([':id' => $id]);
                    $c['statut'] = 'vue';
                }
                echo json_encode(['success' => true, 'candidature' => $c]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Candidature non trouvée']);
            }
            exit;
        }

        // Default: Récupérer les offres de l'entreprise avec leurs candidatures
        $query_offres = "SELECT id, titre, entreprise, localisation, type_contrat, date_publication
                         FROM offres_stage 
                         WHERE user_id = :user_id 
                         ORDER BY date_publication DESC";
        
        $stmt_offres = $db->prepare($query_offres);
        $stmt_offres->bindParam(':user_id', $user_id);
        $stmt_offres->execute();
        $offres = $stmt_offres->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($offres as &$offre) {
            $query_candidats = "SELECT c.id as candidature_id, c.date_candidature, c.statut, c.message_motivation, u.id as user_id, u.nom, u.prenom, u.email, u.telephone
                                FROM candidatures c
                                INNER JOIN users u ON c.user_id = u.id
                                WHERE c.offre_id = :offre_id
                                ORDER BY c.date_candidature DESC";
            $stmt_candidats = $db->prepare($query_candidats);
            $stmt_candidats->bindParam(':offre_id', $offre['id']);
            $stmt_candidats->execute();
            $offre['candidatures'] = $stmt_candidats->fetchAll(PDO::FETCH_ASSOC);
            $offre['nombre_candidatures'] = count($offre['candidatures']);
        }
        
        echo json_encode(['success' => true, 'count' => count($offres), 'offres' => $offres]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}

// PUT : Mettre à jour le statut d'une candidature
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $candidature_id = isset($_PUT['candidature_id']) ? intval($_PUT['candidature_id']) : 0;
    $nouveau_statut = isset($_PUT['statut']) ? $_PUT['statut'] : '';
    
    if ($candidature_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID candidature invalide']);
        exit;
    }
    
    try {
        $update_stmt = $db->prepare("UPDATE candidatures SET statut = :statut WHERE id = :id");
        $update_stmt->execute([':statut' => $nouveau_statut, ':id' => $candidature_id]);
        echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// POST : Ajouter une note
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'add_note') {
        $candidature_id = isset($_POST['candidature_id']) ? intval($_POST['candidature_id']) : 0;
        $note = isset($_POST['note']) ? trim($_POST['note']) : '';
        try {
            $insert_stmt = $db->prepare("INSERT INTO notes_candidatures (candidature_id, user_id, note) VALUES (:candidature_id, :user_id, :note)");
            $insert_stmt->execute([':candidature_id' => $candidature_id, ':user_id' => $user_id, ':note' => $note]);
            echo json_encode(['success' => true, 'message' => 'Note ajoutée']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>