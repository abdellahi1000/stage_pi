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

/**
 * Stabilise de façon permanente le schéma de la table `candidatures`
 * pour éviter les erreurs MySQL/phpMyAdmin liées aux nouveaux champs
 * d'acceptation et à l'ancien ENUM de statut.
 */
function stabilizeCandidaturesSchema($db)
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    try {
        // 1) Assouplir temporairement le type de colonne pour permettre la migration
        try {
            $db->exec("ALTER TABLE candidatures MODIFY COLUMN statut VARCHAR(50) DEFAULT 'pending'");
        } catch (PDOException $e) {
            // Ignorer si déjà VARCHAR ou si l'hôte n'autorise pas cette opération
        }

        // 2) S'assurer que tous les nouveaux champs d'acceptation existent
        $acceptanceCols = [
            'acceptance_message'    => "TEXT NULL",
            'acceptance_date'       => "DATETIME NULL",
            'company_contact_email' => "VARCHAR(255) NULL",
            'company_contact_phone' => "VARCHAR(50) NULL",
            'company_whatsapp'      => "VARCHAR(50) NULL",
            'company_signature_path'=> "VARCHAR(255) NULL",
            'vu_par_entreprise'     => "TINYINT(1) DEFAULT 0",
            'vu_par_etudiant'       => "TINYINT(1) DEFAULT 0",
            'cv_specifique'         => "VARCHAR(255) NULL",
            'lm_specifique'         => "VARCHAR(255) NULL",
        ];

        foreach ($acceptanceCols as $col => $type) {
            $check = $db->prepare("SHOW COLUMNS FROM candidatures LIKE :col");
            $check->execute([':col' => $col]);
            if ($check->rowCount() === 0) {
                try {
                    $db->exec("ALTER TABLE candidatures ADD COLUMN $col $type");
                } catch (PDOException $e) {
                    // Ne jamais bloquer l'application si l'ajout échoue
                }
            }
        }

        // 3) Normaliser les anciennes valeurs de statut vers le nouveau schéma
        try {
            $db->exec("UPDATE candidatures SET statut = 'accepted' WHERE statut IN ('accepte', 'ACCEPTE', 'ACCEPTED')");
            $db->exec("UPDATE candidatures SET statut = 'rejected' WHERE statut IN ('refuse', 'REFUSE', 'REJECTED')");
            $db->exec("UPDATE candidatures SET statut = 'pending'  WHERE statut IN ('en_attente', 'PENDING', 'EN_ATTENTE')");
            $db->exec("UPDATE candidatures SET statut = 'closed'   WHERE statut IN ('CLOSED', 'cloturee')");
        } catch (PDOException $e) {
            // Tolérer une base déjà normalisée ou des valeurs exotiques
        }

        // 4) Reposer un ENUM propre pour les quatre statuts utilisés par l'appli
        try {
            $db->exec("ALTER TABLE candidatures MODIFY COLUMN statut ENUM('pending', 'accepted', 'rejected', 'closed') DEFAULT 'pending'");
        } catch (PDOException $e) {
            // Si l'hôte n'autorise pas cette modification, on garde VARCHAR(50)
        }

        // 5) S'assurer que la table des notes existe (utilisée par les « Dernières notes »)
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS notes_candidatures (
                id INT AUTO_INCREMENT PRIMARY KEY,
                candidature_id INT NOT NULL,
                user_id INT NOT NULL,
                note TEXT NOT NULL,
                date_note TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (candidature_id) REFERENCES candidatures(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (PDOException $e) {
            // Ignorer en mode hébergement restreint
        }
    } catch (Exception $e) {
        // Dernier filet de sécurité : ne jamais casser l'API pour une erreur de stabilisation
    }
}

// Stabiliser le schéma dès que l'API entreprise des candidatures est appelée
stabilizeCandidaturesSchema($db);

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
                      INNER JOIN offres o ON c.offre_id = o.id 
                      WHERE o.user_id = :user_id";
            
            if ($offre_id) $query .= " AND c.offre_id = :offre_id";
            
            if ($statut) {
                if ($statut === 'accepte') {
                    $query .= " AND c.statut = 'accepted'";
                } elseif ($statut === 'refuse') {
                    $query .= " AND c.statut = 'rejected'";
                } elseif ($statut === 'en_attente') {
                    $query .= " AND c.statut = 'pending' AND c.vu_par_entreprise = 0";
                } elseif ($statut === 'vue') {
                    $query .= " AND c.statut = 'pending' AND c.vu_par_entreprise = 1";
                } else {
                    $query .= " AND c.statut = :statut";
                }
            }
            
            $query .= " ORDER BY c.date_candidature DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            if ($offre_id) $stmt->bindParam(':offre_id', $offre_id);
            if ($statut && $statut !== 'accepte' && $statut !== 'refuse' && $statut !== 'en_attente' && $statut !== 'vue') {
                $stmt->bindParam(':statut', $statut);
            }
            $stmt->execute();
            
            $candidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Translate statuses for frontend display only
            foreach ($candidatures as &$c) {
                $s = strtolower($c['statut']);
                if ($s === 'accepted') $c['display_status'] = 'accepte';
                elseif ($s === 'rejected') $c['display_status'] = 'refuse';
                elseif ($s === 'closed') $c['display_status'] = 'CLOSED';
                elseif ($s === 'pending') {
                    if ($c['vu_par_entreprise'] == 1) $c['display_status'] = 'vue';
                    else $c['display_status'] = 'en_attente';
                }
                $c['statut'] = $s; // Ensure raw status is also available in predictable format
            }
            
            echo json_encode(['success' => true, 'candidatures' => $candidatures]);
            exit;
        }

        if ($action === 'recent_enterprise') {
            $query = "SELECT c.*, u.nom, u.prenom, o.titre as offre_titre 
                      FROM candidatures c 
                      INNER JOIN users u ON c.user_id = u.id 
                      INNER JOIN offres o ON c.offre_id = o.id 
                      WHERE o.user_id = :user_id 
                      ORDER BY c.date_candidature DESC LIMIT 5";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $candidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($candidatures as &$c) {
                $s = strtolower($c['statut']);
                if ($s === 'accepted') $c['display_status'] = 'accepte';
                elseif ($s === 'rejected') $c['display_status'] = 'refuse';
                elseif ($s === 'closed') $c['display_status'] = 'CLOSED';
                elseif ($s === 'pending') {
                    if ($c['vu_par_entreprise'] == 1) $c['display_status'] = 'vue';
                    else $c['display_status'] = 'en_attente';
                }
                $c['statut'] = $s;
            }
            
            echo json_encode(['success' => true, 'candidatures' => $candidatures]);
            exit;
        }

        if ($action === 'details') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $query = "SELECT c.*, u.nom, u.prenom, u.email, o.titre as offre_titre, 
                             p.cv_path as profil_cv_path, p.lettre_motivation_path as profil_lm_path
                      FROM candidatures c 
                      INNER JOIN users u ON c.user_id = u.id 
                      INNER JOIN offres o ON c.offre_id = o.id 
                      LEFT JOIN profils p ON u.id = p.user_id 
                      WHERE c.id = :id AND o.user_id = :user_id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $c = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($c) {
                // BLOCK DIRECT ACCESS if status is closed
                if (strtolower($c['statut']) === 'closed') {
                    echo json_encode(['success' => false, 'message' => 'Accès refusé : cette candidature est clôturée car l\'étudiant a été accepté par une autre entreprise.']);
                    exit;
                }

                if ($c['vu_par_entreprise'] == 0) {
                    $up = $db->prepare("UPDATE candidatures SET vu_par_entreprise = 1 WHERE id = :id");
                    $up->execute([':id' => $id]);
                    $c['vu_par_entreprise'] = 1;
                }
                
                $s = strtolower($c['statut']);
                if ($s === 'accepted') $c['display_status'] = 'accepte';
                elseif ($s === 'rejected') $c['display_status'] = 'refuse';
                elseif ($s === 'closed') $c['display_status'] = 'CLOSED';
                elseif ($s === 'pending') {
                    if ($c['vu_par_entreprise'] == 1) $c['display_status'] = 'vue';
                    else $c['display_status'] = 'en_attente';
                }
                $c['statut'] = $s;
                
                echo json_encode(['success' => true, 'candidature' => $c]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Candidature non trouvée']);
            }
            exit;
        }

        // Default: Récupérer les offres de l'entreprise avec leurs candidatures
        $query_offres = "SELECT id, titre, entreprise, localisation, type_contrat, nombre_stagiaires, date_publication
                         FROM offres 
                         WHERE user_id = :user_id 
                         ORDER BY date_publication DESC";
        
        $stmt_offres = $db->prepare($query_offres);
        $stmt_offres->bindParam(':user_id', $user_id);
        $stmt_offres->execute();
        $offres = $stmt_offres->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($offres as &$offre) {
            $query_candidats = "SELECT c.id as candidature_id, c.date_candidature, c.statut, c.message_motivation, c.vu_par_entreprise, u.id as user_id, u.nom, u.prenom, u.email, u.telephone
                                FROM candidatures c
                                INNER JOIN users u ON c.user_id = u.id
                                WHERE c.offre_id = :offre_id
                                ORDER BY c.date_candidature DESC";
            $stmt_candidats = $db->prepare($query_candidats);
            $stmt_candidats->bindParam(':offre_id', $offre['id']);
            $stmt_candidats->execute();
            $offre['candidatures'] = $stmt_candidats->fetchAll(PDO::FETCH_ASSOC);
            foreach ($offre['candidatures'] as &$c) {
                $s = strtolower($c['statut']);
                if ($s === 'accepted') $c['display_status'] = 'accepte';
                elseif ($s === 'rejected') $c['display_status'] = 'refuse';
                elseif ($s === 'closed') $c['display_status'] = 'CLOSED';
                elseif ($s === 'pending') {
                    if ($c['vu_par_entreprise'] == 1) $c['display_status'] = 'vue';
                    else $c['display_status'] = 'en_attente';
                }
                $c['statut'] = $s;
            }
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
    $nouveau_statut = isset($_PUT['statut']) ? strtolower($_PUT['statut']) : '';
    
    if ($candidature_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID candidature invalide']);
        exit;
    }
    
    try {
        $db->beginTransaction();

        // First, get the student ID for this candidature
        $get_student = $db->prepare("SELECT user_id FROM candidatures WHERE id = :id");
        $get_student->execute([':id' => $candidature_id]);
        $student_id = $get_student->fetchColumn();

        if (!$student_id) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Candidature non trouvée']);
            exit;
        }

        if ($nouveau_statut === 'accepted' || $nouveau_statut === 'accepte') {
            $nouveau_statut = 'accepted';
            // Lock all other applications: set others to 'closed'
            $lock_stmt = $db->prepare("UPDATE candidatures SET statut = 'closed' WHERE user_id = :sid AND id != :cid");
            $lock_stmt->execute([':sid' => $student_id, ':cid' => $candidature_id]);
        } elseif ($nouveau_statut === 'refuse' || $nouveau_statut === 'rejected') {
            $nouveau_statut = 'rejected';
        }

        $update_stmt = $db->prepare("UPDATE candidatures SET statut = :statut WHERE id = :id");
        $update_stmt->execute([':statut' => $nouveau_statut, ':id' => $candidature_id]);
        
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
    } catch(Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
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
    } elseif ($action === 'accept_with_details') {
        $candidature_id = isset($_POST['candidature_id']) ? intval($_POST['candidature_id']) : 0;
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $whatsapp = isset($_POST['whatsapp']) ? trim($_POST['whatsapp']) : '';

        if ($candidature_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID candidature invalide']);
            exit;
        }

        try {
            $db->beginTransaction();

            // Get student ID
            $get_student = $db->prepare("SELECT user_id FROM candidatures WHERE id = :id");
            $get_student->execute([':id' => $candidature_id]);
            $student_id = $get_student->fetchColumn();

            if (!$student_id) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Candidature non trouvée']);
                exit;
            }

            // Lock all other applications: set others to 'closed'
            $lock_stmt = $db->prepare("UPDATE candidatures SET statut = 'closed' WHERE user_id = :sid AND id != :cid");
            $lock_stmt->execute([':sid' => $student_id, ':cid' => $candidature_id]);

            // Retrieve current company signature path (if any)
            $sig_stmt = $db->prepare("SELECT company_signature_path FROM users WHERE id = :uid");
            $sig_stmt->execute([':uid' => $user_id]);
            $signaturePath = $sig_stmt->fetchColumn() ?: null;

            $update_stmt = $db->prepare("UPDATE candidatures SET statut = 'accepted', acceptance_message = :message, acceptance_date = NOW(), company_contact_email = :email, company_contact_phone = :phone, company_whatsapp = :whatsapp, company_signature_path = :signature WHERE id = :id");
            $update_stmt->execute([
                ':message' => $message,
                ':email' => $email,
                ':phone' => $phone,
                ':whatsapp' => $whatsapp,
                ':signature' => $signaturePath,
                ':id' => $candidature_id
            ]);

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Candidature acceptée avec succès']);
        } catch(Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>
?>