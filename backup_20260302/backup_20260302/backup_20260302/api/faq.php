<?php
// faq.php - Gestion de la FAQ

require_once '../include/db_connect.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

// GET : Récupérer toutes les questions FAQ
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    try {
        if (!empty($search_term)) {
            // Recherche dans la FAQ
            $query = "SELECT 
                        f.id,
                        f.question,
                        f.reponse,
                        f.categorie,
                        f.ordre,
                        f.vues,
                        f.utile_count
                      FROM faq f
                      WHERE f.actif = 1 
                        AND (f.question LIKE :search 
                         OR f.reponse LIKE :search 
                         OR f.categorie LIKE :search)
                      ORDER BY f.categorie, f.ordre";
            
            $stmt = $db->prepare($query);
            $search_param = "%{$search_term}%";
            $stmt->bindParam(':search', $search_param);
        } else {
            // Récupérer toutes les FAQs actives
            $query = "SELECT 
                        id,
                        question,
                        reponse,
                        categorie,
                        ordre,
                        vues,
                        utile_count
                      FROM faq
                      WHERE actif = 1
                      ORDER BY categorie, ordre";
            
            $stmt = $db->prepare($query);
        }
        
        $stmt->execute();
        $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organiser par catégorie
        $faq_by_category = [];
        foreach ($faqs as $faq) {
            $category = $faq['categorie'];
            if (!isset($faq_by_category[$category])) {
                $faq_by_category[$category] = [];
            }
            $faq_by_category[$category][] = $faq;
        }
        
        echo json_encode([
            'success' => true,
            'count' => count($faqs),
            'faqs' => $faqs,
            'by_category' => $faq_by_category
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}

// POST : Ajouter une nouvelle question (admin uniquement)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    // Vérifier si l'utilisateur est admin
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
        exit;
    }
    
    $question = trim($_POST['question']);
    $reponse = trim($_POST['reponse']);
    $categorie = trim($_POST['categorie']);
    $ordre = isset($_POST['ordre']) ? intval($_POST['ordre']) : 0;
    
    if (empty($question) || empty($reponse) || empty($categorie)) {
        echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
        exit;
    }
    
    try {
        $insert_query = "INSERT INTO faq (question, reponse, categorie, ordre) 
                        VALUES (:question, :reponse, :categorie, :ordre)";
        
        $stmt = $db->prepare($insert_query);
        $stmt->bindParam(':question', $question);
        $stmt->bindParam(':reponse', $reponse);
        $stmt->bindParam(':categorie', $categorie);
        $stmt->bindParam(':ordre', $ordre);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Question ajoutée avec succès',
            'faq_id' => $db->lastInsertId()
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}

// PUT : Incrémenter le compteur de vues
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $faq_id = isset($_PUT['faq_id']) ? intval($_PUT['faq_id']) : 0;
    $action = isset($_PUT['action']) ? $_PUT['action'] : '';
    
    if ($faq_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID invalide']);
        exit;
    }
    
    try {
        if ($action === 'view') {
            // Incrémenter les vues
            $update_query = "UPDATE faq SET vues = vues + 1 WHERE id = :id";
        } elseif ($action === 'helpful') {
            // Incrémenter le compteur "utile"
            $update_query = "UPDATE faq SET utile_count = utile_count + 1 WHERE id = :id";
        } else {
            echo json_encode(['success' => false, 'message' => 'Action invalide']);
            exit;
        }
        
        $stmt = $db->prepare($update_query);
        $stmt->bindParam(':id', $faq_id);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Compteur mis à jour'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}

// contact.php - Formulaire de contact pour questions non trouvées dans FAQ
/*
<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $sujet = trim($_POST['sujet']);
    $message = trim($_POST['message']);
    
    if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email invalide']);
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $insert = "INSERT INTO contacts (nom, email, sujet, message, statut) 
                  VALUES (:nom, :email, :sujet, :message, 'nouveau')";
        
        $stmt = $db->prepare($insert);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':sujet', $sujet);
        $stmt->bindParam(':message', $message);
        $stmt->execute();
        
        // Envoyer un email de confirmation (optionnel)
        $to = $email;
        $subject = "Confirmation de votre message - StageMatch";
        $body = "Bonjour $nom,\n\nNous avons bien reçu votre message concernant : $sujet\n\nNotre équipe vous répondra dans les plus brefs délais.\n\nCordialement,\nL'équipe StageMatch";
        $headers = "From: noreply@stagematch.com";
        
        mail($to, $subject, $body, $headers);
        
        echo json_encode([
            'success' => true,
            'message' => 'Message envoyé avec succès. Nous vous répondrons rapidement.'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage()
        ]);
    }
}
?>
*/

// create_tables_faq.sql - Tables pour FAQ et Contact
/*
USE stagematch;

-- Table FAQ
CREATE TABLE IF NOT EXISTS faq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    reponse TEXT NOT NULL,
    categorie VARCHAR(100) NOT NULL,
    ordre INT DEFAULT 0,
    actif BOOLEAN DEFAULT TRUE,
    vues INT DEFAULT 0,
    utile_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_categorie (categorie),
    INDEX idx_actif (actif),
    INDEX idx_ordre (ordre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Contacts
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    sujet VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    statut ENUM('nouveau', 'en_cours', 'resolu', 'ferme') DEFAULT 'nouveau',
    reponse TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_statut (statut),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des données FAQ
INSERT INTO faq (question, reponse, categorie, ordre) VALUES
-- Général
('Qu\'est-ce que StageMatch ?', 'StageMatch est une plateforme dédiée à la mise en relation entre étudiants à la recherche de stages et entreprises proposant des offres de stage, principalement en Mauritanie. Elle facilite la création de CV, la candidature et le matching automatique.', 'Général', 1),
('Comment créer un compte ?', 'Cliquez sur le bouton "Inscription" en haut à droite, remplissez le formulaire avec vos informations (nom, prénom, email, mot de passe), puis validez. Vous recevrez un email de confirmation.', 'Général', 2),
('Est-ce que StageMatch est gratuit ?', 'Oui, StageMatch est entièrement gratuit pour les étudiants. Les entreprises peuvent bénéficier d\'un compte gratuit avec des fonctionnalités de base, ou opter pour un compte premium avec des options avancées.', 'Général', 3),

-- Pour les Étudiants
('Comment créer ou modifier mon CV ?', 'Rendez-vous dans le menu "Créer/Modifier CV". Utilisez nos templates modernes pour remplir vos expériences, formations et compétences en quelques minutes.', 'Pour les Étudiants', 1),
('Comment postuler à une offre de stage ?', 'Allez dans "Trouver des Offres" ou sur la page d\'accueil, cliquez sur une offre qui vous intéresse, puis sur le bouton "+" pour déposer votre candidature.', 'Pour les Étudiants', 2),
('Où voir l\'état de mes candidatures ?', 'Cliquez sur "Mes Candidatures" dans le menu latéral. Vous verrez les statuts : En attente, Accepté ou Refusé.', 'Pour les Étudiants', 3),
('Puis-je postuler à plusieurs offres ?', 'Oui, vous pouvez postuler à autant d\'offres que vous le souhaitez. Nous recommandons de personnaliser votre lettre de motivation pour chaque candidature.', 'Pour les Étudiants', 4),
('Comment télécharger mon CV ?', 'Une fois votre CV créé, vous pouvez le télécharger au format PDF depuis la page "Créer/Modifier CV". Cliquez simplement sur le bouton "Télécharger PDF".', 'Pour les Étudiants', 5),

-- Pour les Entreprises
('Comment déposer une offre de stage ?', 'Connectez-vous avec un compte entreprise, puis cliquez sur "Déposer une Offre". Remplissez les informations (titre, description, durée, lieu) et publiez.', 'Pour les Entreprises', 1),
('Comment gérer les candidatures reçues ?', 'Allez dans "Gérer les Candidats". Vous verrez toutes vos offres avec la liste des étudiants ayant postulé. Vous pouvez accepter ou refuser chaque candidature.', 'Pour les Entreprises', 2),
('Combien d\'offres puis-je publier ?', 'Avec un compte gratuit, vous pouvez publier jusqu\'à 5 offres actives simultanément. Le compte premium permet un nombre illimité d\'offres.', 'Pour les Entreprises', 3),
('Comment modifier une offre déjà publiée ?', 'Allez dans "Mes Offres", cliquez sur l\'offre à modifier, puis sur le bouton "Modifier". Apportez vos changements et enregistrez.', 'Pour les Entreprises', 4),

-- Problèmes Techniques
('J\'ai oublié mon mot de passe', 'Sur la page de connexion, cliquez sur "Mot de passe oublié ?", entrez votre email et suivez les instructions pour le réinitialiser.', 'Problèmes Techniques', 1),
('Le site ne charge pas correctement', 'Essayez de vider le cache de votre navigateur (Ctrl + Shift + R) ou d\'utiliser un autre navigateur. Si le problème persiste, contactez-nous via le formulaire de contact.', 'Problèmes Techniques', 2),
('Je ne reçois pas les emails de notification', 'Vérifiez votre dossier spam/courrier indésirable. Ajoutez noreply@stagematch.com à vos contacts. Si le problème persiste, contactez le support.', 'Problèmes Techniques', 3),
('Comment supprimer mon compte ?', 'Allez dans "Mon Compte" > "Paramètres" > "Supprimer mon compte". Attention, cette action est irréversible et supprimera toutes vos données.', 'Problèmes Techniques', 4);
*/
?>