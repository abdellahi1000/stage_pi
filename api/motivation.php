<?php
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

try {
    $database = new Database();
    $db = $database->getConnection();

    // Ensure table exists with all required columns
    $db->exec("
        CREATE TABLE IF NOT EXISTS etudiant_motivation (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            nom_complet VARCHAR(255) NOT NULL,
            telephone VARCHAR(50) NOT NULL,
            email VARCHAR(255) NOT NULL,
            adresse_complet VARCHAR(255),
            ville VARCHAR(100),
            date_lettre VARCHAR(50),
            entreprise VARCHAR(255),
            adresse_entreprise VARCHAR(255),
            objet VARCHAR(255) NOT NULL,
            civilite VARCHAR(50) DEFAULT 'Madame, Monsieur,',
            message LONGTEXT NOT NULL,
            cloture VARCHAR(255),
            email_entreprise VARCHAR(255),
            service_rh VARCHAR(255) DEFAULT 'Service des Ressources Humaines',
            signature_base64 LONGTEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Check for missing columns (Migration)
    $stmt = $db->query("SHOW COLUMNS FROM etudiant_motivation");
    $existing_cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $needed_cols = [
        'adresse_complet' => 'VARCHAR(255)',
        'ville' => 'VARCHAR(100)',
        'adresse_entreprise' => 'VARCHAR(255)',
        'civilite' => 'VARCHAR(50)',
        'cloture' => 'VARCHAR(255)',
        'email_entreprise' => 'VARCHAR(255)',
        'service_rh' => 'VARCHAR(255)',
        'signature_base64' => 'LONGTEXT'
    ];
    foreach ($needed_cols as $col => $type) {
        if (!in_array($col, $existing_cols)) {
            $db->exec("ALTER TABLE etudiant_motivation ADD COLUMN $col $type AFTER email");
        }
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $db->prepare("SELECT * FROM etudiant_motivation WHERE user_id = :uid");
        $stmt->execute([':uid' => $user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'motivation' => $row ?: null
        ]);
        exit;
    }

    if ($method === 'POST') {
        $nom = trim($_POST['nom_complet'] ?? '');
        $tel = trim($_POST['telephone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $addr = trim($_POST['adresse_complet'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $date_lettre = trim($_POST['date_lettre'] ?? '');
        $entreprise = trim($_POST['entreprise'] ?? '');
        $addr_ent = trim($_POST['adresse_entreprise'] ?? '');
        $objet = trim($_POST['objet'] ?? '');
        $civilite = trim($_POST['civilite'] ?? 'Madame, Monsieur,');
        $message = trim($_POST['message'] ?? '');
        $cloture = trim($_POST['cloture'] ?? '');
        $email_ent = trim($_POST['email_entreprise'] ?? '');
        $service_rh = trim($_POST['service_rh'] ?? 'Service des Ressources Humaines');
        $signature = $_POST['signature_base64'] ?? null;

        if ($nom === '' || $email === '' || $objet === '' || $message === '') {
            echo json_encode(['success' => false, 'message' => 'Le nom, l\'email, l\'objet et le message sont obligatoires.']);
            exit;
        }

        $sql = "
            INSERT INTO etudiant_motivation (
                user_id, nom_complet, telephone, email, adresse_complet, ville, 
                date_lettre, entreprise, adresse_entreprise, email_entreprise, service_rh,
                objet, civilite, message, cloture, signature_base64
            )
            VALUES (
                :uid, :nom, :tel, :email, :addr, :ville, 
                :date_lettre, :entreprise, :addr_ent, :email_ent, :service_rh,
                :objet, :civilite, :message, :cloture, :sig
            )
            ON DUPLICATE KEY UPDATE
                nom_complet = VALUES(nom_complet),
                telephone = VALUES(telephone),
                email = VALUES(email),
                adresse_complet = VALUES(adresse_complet),
                ville = VALUES(ville),
                date_lettre = VALUES(date_lettre),
                entreprise = VALUES(entreprise),
                adresse_entreprise = VALUES(adresse_entreprise),
                email_entreprise = VALUES(email_entreprise),
                service_rh = VALUES(service_rh),
                objet = VALUES(objet),
                civilite = VALUES(civilite),
                message = VALUES(message),
                cloture = VALUES(cloture),
                signature_base64 = VALUES(signature_base64)
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':uid' => $user_id,
            ':nom' => $nom,
            ':tel' => $tel,
            ':email' => $email,
            ':addr' => $addr,
            ':ville' => $ville,
            ':date_lettre' => $date_lettre,
            ':entreprise' => $entreprise,
            ':addr_ent' => $addr_ent,
            ':email_ent' => $email_ent,
            ':service_rh' => $service_rh,
            ':objet' => $objet,
            ':civilite' => $civilite,
            ':message' => $message,
            ':cloture' => $cloture,
            ':sig' => $signature
        ]);

        echo json_encode(['success' => true, 'message' => 'Lettre de motivation enregistrée.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}

