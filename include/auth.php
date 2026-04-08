<?php
// include/auth.php - Gestion de l'authentification (Login/Signup) via AJAX
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Si ce n'est pas une requête POST, on arrête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit;
}

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
        // Updated query to use 'role' and check 'users' table
        $query = "SELECT id, nom, prenom, email, password, role as type_compte, role, entreprise_id, telephone, photo_profil, actif, account_status, verified_status FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
            exit;
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user['actif'] && $user['role'] !== 'admin' && $user['role'] !== 'employee') {
            echo json_encode(['success' => false, 'message' => 'Compte désactivé. Contactez l\'administrateur']);
            exit;
        }

        // Check Password (Hashed)
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_role'] = $user['role']; // admin, employee, student
            $_SESSION['user_type'] = ($user['role'] === 'student') ? 'etudiant' : 'entreprise';
            $_SESSION['entreprise_id'] = $user['entreprise_id'];
            $_SESSION['user_tel'] = $user['telephone'];
            $_SESSION['photo_profil'] = $user['photo_profil'];
            $_SESSION['verified_status'] = $user['verified_status'] ?? 0;
            $_SESSION['logged_in'] = true;

            // Fetch company name if part of an enterprise
            if (!empty($user['entreprise_id'])) {
                $ent_stmt = $db->prepare("SELECT name FROM entreprises WHERE id = :eid");
                $ent_stmt->execute([':eid' => $user['entreprise_id']]);
                $_SESSION['company_name'] = $ent_stmt->fetchColumn();
            } else {
                $_SESSION['company_name'] = null;
            }

            $th_stmt = $db->prepare("SELECT theme FROM preferences_utilisateur WHERE user_id = :uid");
            $th_stmt->bindParam(':uid', $user['id']);
            $th_stmt->execute();
            $th_row = $th_stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['user_theme'] = ($th_row && !empty($th_row['theme'])) ? $th_row['theme'] : 'light';

            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (30 * 24 * 60 * 60);
                $token_query = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, FROM_UNIXTIME(:expiry))";
                $token_stmt = $db->prepare($token_query);
                $token_stmt->bindParam(':user_id', $user['id']);
                $token_stmt->bindValue(':token', hash('sha256', $token));
                $token_stmt->bindParam(':expiry', $expiry);
                $token_stmt->execute();
                
                setcookie('remember_token', $token, $expiry, '/', '', false, true);
            }
            
            $update_stmt = $db->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = :user_id");
            $update_stmt->bindParam(':user_id', $user['id']);
            $update_stmt->execute();
            
            // Redirection logic based on requirements
            if ($user['role'] === 'admin') {
                $redirect = 'administrator/index.php'; // S-PLUS Administrator Dashboard
            } elseif ($user['role'] === 'employee') {
                $redirect = 'enterprise/index.php'; // Espace Entreprise
            } else {
                $redirect = 'students/index.php'; // Student Interface
            }
         
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
    $nom = trim($_POST['nom']); // For students: last name. For enterprise: company name.
    $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $type_compte = isset($_POST['type_compte']) ? $_POST['type_compte'] : 'etudiant'; // 'etudiant' or 'entreprise'
    $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : null;
    
    if (empty($nom) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
        exit;
    }
    
    if ($password !== $password_confirm) {
        echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
        exit;
    }

    try {
        $db->beginTransaction();

        $check_stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            exit;
        }
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $entreprise_id = null;
        $role = 'student';

        if ($type_compte === 'entreprise') {
            // 1. Create Enterprise record first
            $company_name = isset($_POST['company_name']) ? strtoupper(trim($_POST['company_name'])) : strtoupper($nom);
            
            // File Upload Logic
            $upload_dir = '../uploads/company_docs/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $paths = ['doc_registry' => null, 'doc_tax' => null, 'doc_stamp' => null];
            foreach ($paths as $key => &$path) {
                if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION);
                    $new_name = $key . '_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES[$key]['tmp_name'], $upload_dir . $new_name)) {
                        $path = 'uploads/company_docs/' . $new_name;
                    }
                }
            }

            $stmt = $db->prepare("INSERT INTO entreprises (
                name, registration_number, identification_number, full_address, activity_sector, company_type, creation_year, official_document,
                secteur, taille, adresse, registre, num_fiscal, doc_registry, doc_tax, doc_stamp, document_pdf
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $company_name, 
                $_POST['commercial_reg_num'] ?? null,
                $_POST['tax_id'] ?? null,
                $_POST['address'] ?? null,
                $_POST['industry_sector'] ?? null,
                $_POST['company_size'] ?? null,
                $_POST['year_established'] ?? null,
                $paths['doc_registry'],
                // Legacy
                $_POST['industry_sector'] ?? null,
                $_POST['company_size'] ?? null,
                $_POST['address'] ?? null,
                $_POST['commercial_reg_num'] ?? null,
                $_POST['tax_id'] ?? null,
                $paths['doc_registry'],
                $paths['doc_tax'],
                $paths['doc_stamp'],
                $paths['doc_registry']
            ]);
            $entreprise_id = $db->lastInsertId();
            $role = 'admin'; // Creator of enterprise account is admin
        }

        // 2. Create User linked to enterprise if needed
        $insert_query = "INSERT INTO users (nom, prenom, email, password, role, entreprise_id, telephone, actif) 
                         VALUES (:nom, :prenom, :email, :password, :role, :ent_id, :telephone, :actif)";
        $insert_stmt = $db->prepare($insert_query);
        $actif = ($type_compte === 'etudiant') ? 1 : 0; // Enterprise accounts pending review
        
        $insert_stmt->execute([
            ':nom' => $nom, 
            ':prenom' => $prenom,
            ':email' => $email,
            ':password' => $password_hash,
            ':role' => $role,
            ':ent_id' => $entreprise_id,
            ':telephone' => $telephone,
            ':actif' => $actif
        ]);

        $user_id = $db->lastInsertId();
        if ($type_compte === 'etudiant') {
            $db->exec("INSERT INTO profils (user_id) VALUES ($user_id)");
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Compte créé avec succès !']);

    } catch(PDOException $e) {
        if ($db->inTransaction()) $db->rollBack();
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
                'role' => $_SESSION['user_role'],
                'entreprise_id' => $_SESSION['entreprise_id']
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
