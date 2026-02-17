<?php
// compte.php - Gestion du compte utilisateur

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

// GET : Récupérer toutes les informations du compte
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Informations utilisateur de base
        $user_query = "SELECT 
                        id, nom, prenom, email, telephone, 
                        type_compte, photo_profil, date_naissance,
                        adresse, ville, pays, bio,
                        linkedin_url, portfolio_url,
                        created_at, derniere_connexion
                      FROM users 
                      WHERE id = :user_id";
        
        $user_stmt = $db->prepare($user_query);
        $user_stmt->bindParam(':user_id', $user_id);
        $user_stmt->execute();
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Candidatures en attente
        $attente_query = "SELECT 
                            c.id,
                            c.date_candidature,
                            o.titre,
                            o.entreprise,
                            o.localisation,
                            o.type_contrat
                          FROM candidatures c
                          INNER JOIN offres_stage o ON c.offre_id = o.id
                          WHERE c.user_id = :user_id AND c.statut = 'en_attente'
                          ORDER BY c.date_candidature DESC";
        
        $attente_stmt = $db->prepare($attente_query);
        $attente_stmt->bindParam(':user_id', $user_id);
        $attente_stmt->execute();
        $candidatures_attente = $attente_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Candidatures acceptées
        $accepte_query = "SELECT 
                            c.id,
                            c.date_candidature,
                            o.titre,
                            o.entreprise,
                            o.localisation,
                            o.type_contrat,
                            o.duree
                          FROM candidatures c
                          INNER JOIN offres_stage o ON c.offre_id = o.id
                          WHERE c.user_id = :user_id AND c.statut = 'accepte'
                          ORDER BY c.date_candidature DESC";
        
        $accepte_stmt = $db->prepare($accepte_query);
        $accepte_stmt->bindParam(':user_id', $user_id);
        $accepte_stmt->execute();
        $candidatures_acceptees = $accepte_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Stages complétés (historique)
        $complete_query = "SELECT 
                            entreprise,
                            poste,
                            date_debut,
                            date_fin,
                            localisation,
                            description
                          FROM historique_stages
                          WHERE user_id = :user_id
                          ORDER BY date_fin DESC";
        
        $complete_stmt = $db->prepare($complete_query);
        $complete_stmt->bindParam(':user_id', $user_id);
        $complete_stmt->execute();
        $stages_completes = $complete_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si l'utilisateur est une entreprise, récupérer ses offres
        $offres_deposees = [];
        if ($user['type_compte'] === 'entreprise') {
            $offres_query = "SELECT 
                                o.id,
                                o.titre,
                                o.entreprise,
                                o.localisation,
                                o.duree,
                                o.date_publication,
                                o.statut,
                                (SELECT COUNT(*) FROM candidatures WHERE offre_id = o.id) as nb_candidatures
                             FROM offres_stage o
                             WHERE o.user_id = :user_id
                             ORDER BY o.date_publication DESC";
            
            $offres_stmt = $db->prepare($offres_query);
            $offres_stmt->bindParam(':user_id', $user_id);
            $offres_stmt->execute();
            $offres_deposees = $offres_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Statistiques
        $stats = [
            'total_candidatures' => count($candidatures_attente) + count($candidatures_acceptees),
            'candidatures_attente' => count($candidatures_attente),
            'candidatures_acceptees' => count($candidatures_acceptees),
            'stages_completes' => count($stages_completes),
            'offres_deposees' => count($offres_deposees)
        ];
        
// ... (previous logic) ...
        echo json_encode([
            'success' => true,
            'user' => $user,
            'candidatures_attente' => $candidatures_attente,
            'candidatures_acceptees' => $candidatures_acceptees,
            'stages_completes' => $stages_completes,
            'offres_deposees' => $offres_deposees,
            'statistiques' => $stats
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}

// ACTION specific GET requests
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'enterprise_stats') {
        if ($_SESSION['user_type'] !== 'entreprise') {
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }
        
        try {
            $stats = [];
            // Offres actives
            $q1 = $db->prepare("SELECT COUNT(*) FROM offres_stage WHERE user_id = :uid AND statut = 'active'");
            $q1->execute([':uid' => $user_id]);
            $stats['offres_actives'] = $q1->fetchColumn();
            
            // Total candidatures reçues
            $q2 = $db->prepare("SELECT COUNT(*) FROM candidatures c INNER JOIN offres_stage o ON c.offre_id = o.id WHERE o.user_id = :uid");
            $q2->execute([':uid' => $user_id]);
            $stats['total_candidatures'] = $q2->fetchColumn();
            
            // Recrutements (candidatures acceptées)
            $q3 = $db->prepare("SELECT COUNT(*) FROM candidatures c INNER JOIN offres_stage o ON c.offre_id = o.id WHERE o.user_id = :uid AND c.statut = 'accepte'");
            $q3->execute([':uid' => $user_id]);
            $stats['recrutements'] = $q3->fetchColumn();
            
            echo json_encode(['success' => true, 'stats' => $stats]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

// PUT : Mettre à jour les informations du profil
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
// ...
    parse_str(file_get_contents("php://input"), $_PUT);
    
    try {
        $update_fields = [];
        $params = [':user_id' => $user_id];
        
        $allowed_fields = [
            'nom', 'prenom', 'email', 'telephone', 
            'date_naissance', 'adresse', 'ville', 'pays', 
            'bio', 'linkedin_url', 'portfolio_url',
            'visibilite_entreprise'
        ];

        
        foreach ($allowed_fields as $field) {
            if (isset($_PUT[$field])) {
                $update_fields[] = "$field = :$field";
                $params[":$field"] = trim($_PUT[$field]);
            }
        }
        
        if (empty($update_fields)) {
            echo json_encode(['success' => false, 'message' => 'Aucun champ à mettre à jour']);
            exit;
        }
        
        $update_query = "UPDATE users SET " . implode(', ', $update_fields) . 
                       " WHERE id = :user_id";
        
        $update_stmt = $db->prepare($update_query);
        foreach ($params as $key => $value) {
            $update_stmt->bindValue($key, $value);
        }
        
        if ($update_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Profil mis à jour avec succès'
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}
?>