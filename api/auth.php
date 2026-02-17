<?php
// include/auth.php - Gestion de l'authentification (Login/Signup) via AJAX
session_start();
require_once '../include/db_connect.php';

header('Content-Type: application/json');

// Si ce n'est pas une requête POST, on arrête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$database = new Database();
$db = $database->getConnection();

// --- LOGIN ---
if ($action === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) && $_POST['remember'] === '1';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email invalide']);
        exit;
    }
    
    try {
        $query = "SELECT id, nom, prenom, email, password, type_compte, actif FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
            exit;
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user['actif']) {
            echo json_encode(['success' => false, 'message' => 'Compte désactivé. Contactez l\'administrateur']);
            exit;
        }
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_type'] = $user['type_compte'];
            $_SESSION['logged_in'] = true;
            
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (30 * 24 * 60 * 60);
                $token_query = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, FROM_UNIXTIME(:expiry))";
                $token_stmt = $db->prepare($token_query);
                $token_stmt->bindParam(':user_id', $user['id']);
                $token_stmt->bindValue(':token', hash('sha256', $token)); // bindValue for expression result
                $token_stmt->bindParam(':expiry', $expiry);
                $token_stmt->execute();
                
                setcookie('remember_token', $token, $expiry, '/', '', false, true);
            }
            
            // Mettre à jour la dernière connexion
            $update_stmt = $db->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = :user_id");
            $update_stmt->bindParam(':user_id', $user['id']);
            $update_stmt->execute();
            
            $redirect = $user['type_compte'] === 'entreprise' ? 'enterprise/index.php' : 'students/index.php';
            
            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => $user,
                'redirect' => $redirect
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur de connexion : ' . $e->getMessage()]);
    }
}

// --- REGISTER ---
elseif ($action === 'register') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $type_compte = isset($_POST['type_compte']) ? $_POST['type_compte'] : 'etudiant';
    $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : null;
    
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email invalide']);
        exit;
    }
    
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères']);
        exit;
    }
    
    if ($password !== $password_confirm) {
        echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
        exit;
    }
    
    try {
        $check_stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            exit;
        }
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(32));
        
        $insert_query = "INSERT INTO users (nom, prenom, email, password, type_compte, telephone, verification_token) VALUES (:nom, :prenom, :email, :password, :type, :telephone, :token)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':nom', $nom);
        $insert_stmt->bindParam(':prenom', $prenom);
        $insert_stmt->bindParam(':email', $email);
        $insert_stmt->bindParam(':password', $password_hash);
        $insert_stmt->bindParam(':type', $type_compte);
        $insert_stmt->bindParam(':telephone', $telephone);
        $insert_stmt->bindParam(':token', $verification_token);
        
        if ($insert_stmt->execute()) {
            $user_id = $db->lastInsertId();
            if ($type_compte === 'etudiant') {
                $db->exec("INSERT INTO profils (user_id) VALUES ($user_id)");
            }
            echo json_encode(['success' => true, 'message' => 'Inscription réussie ! Vous pouvez maintenant vous connecter.']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription : ' . $e->getMessage()]);
    }
}

// --- CHECK SESSION ---
elseif ($action === 'check_session') {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'nom' => $_SESSION['user_nom'],
                'type' => $_SESSION['user_type']
            ]
        ]);
    } else {
        echo json_encode(['success' => true, 'logged_in' => false]);
    }
}

// --- LOGOUT ---
elseif ($action === 'logout') {
    if (isset($_COOKIE['remember_token']) && isset($_SESSION['user_id'])) {
        $token_hash = hash('sha256', $_COOKIE['remember_token']);
        $del_stmt = $db->prepare("DELETE FROM remember_tokens WHERE user_id = :uid AND token = :token");
        $del_stmt->execute([':uid' => $_SESSION['user_id'], ':token' => $token_hash]);
        setcookie('remember_token', '', time() - 3600, '/');
    }
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Déconnexion réussie']);
}

else {
    echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}
?>
