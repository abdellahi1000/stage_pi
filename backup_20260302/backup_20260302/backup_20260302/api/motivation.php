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

    // Ensure table exists (idempotent)
    $db->exec("
        CREATE TABLE IF NOT EXISTS etudiant_motivation (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            nom_complet VARCHAR(255) NOT NULL,
            telephone VARCHAR(50) NOT NULL,
            email VARCHAR(255) NOT NULL,
            objet VARCHAR(255) NOT NULL,
            message LONGTEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $db->prepare("SELECT nom_complet, telephone, email, objet, message FROM etudiant_motivation WHERE user_id = :uid");
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
        $objet = trim($_POST['objet'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($nom === '' || $tel === '' || $email === '' || $objet === '' || $message === '') {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
            exit;
        }

        $sql = "
            INSERT INTO etudiant_motivation (user_id, nom_complet, telephone, email, objet, message)
            VALUES (:uid, :nom, :tel, :email, :objet, :message)
            ON DUPLICATE KEY UPDATE
                nom_complet = VALUES(nom_complet),
                telephone = VALUES(telephone),
                email = VALUES(email),
                objet = VALUES(objet),
                message = VALUES(message)
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':uid' => $user_id,
            ':nom' => $nom,
            ':tel' => $tel,
            ':email' => $email,
            ':objet' => $objet,
            ':message' => $message
        ]);

        echo json_encode(['success' => true, 'message' => 'Lettre de motivation enregistrée.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}

