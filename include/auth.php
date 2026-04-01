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
        $query = "SELECT id, nom, prenom, email, password, type_compte, telephone, photo_profil, actif, account_status, verified_status FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
            exit;
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user['actif'] && $user['type_compte'] !== 'entreprise') {
            echo json_encode(['success' => false, 'message' => 'Compte désactivé. Contactez l\'administrateur']);
            exit;
        }

        $login_success = false;

        // Check Password (Hashed)
        if (password_verify($password, $user['password'])) {
            $login_success = true;
        }
        
        if ($login_success) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_type'] = $user['type_compte'];
            
            $_SESSION['user_role'] = ($user['type_compte'] === 'admin') ? 'Administrator' : 'user';
            $_SESSION['user_tel'] = $user['telephone'];
            $_SESSION['photo_profil'] = $user['photo_profil'];
            $_SESSION['verified_status'] = $user['verified_status'] ?? 0;
            $_SESSION['logged_in'] = true;

            // Skip fetching company_id from session as it's not in the users table
            $_SESSION['company_name'] = $user['nom']; 

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
                $token_stmt->bindValue(':token', hash('sha256', $token)); // bindValue for expression result
                $token_stmt->bindParam(':expiry', $expiry);
                $token_stmt->execute();
                
                setcookie('remember_token', $token, $expiry, '/', '', false, true);
            }
            
            // Mettre à jour la dernière connexion
            $update_stmt = $db->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = :user_id");
            $update_stmt->bindParam(':user_id', $user['id']);
            $update_stmt->execute();
            
            // Redirection logic
            if ($user['type_compte'] === 'entreprise') {
                $redirect = ($_SESSION['user_role'] === 'Administrator') ? 'administrator/index.php' : 'enterprise/index.php';
            } else {
                $redirect = 'students/index.php';
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
    $nom = trim($_POST['nom']);
    $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $type_compte = isset($_POST['type_compte']) ? $_POST['type_compte'] : 'etudiant';
    $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : null;
    
    if (empty($nom) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
        exit;
    }
    if ($type_compte === 'etudiant' && empty($prenom)) {
        echo json_encode(['success' => false, 'message' => 'Le prénom est obligatoire pour les étudiants']);
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

    if (!empty($telephone) && !preg_match('/^[0-9\+\-\s\(\)]{8,20}$/', $telephone)) {
        echo json_encode(['success' => false, 'message' => 'Format de numéro de téléphone invalide']);
        exit;
    }

    // Enterprise Specific Validations
    $commercial_reg_num = null;
    $tax_id = null;
    $address = null;
    $industry_sector = null;
    $company_size = null;
    $year_established = null;
    $doc_registry_path = null;
    $doc_tax_path = null;
    $doc_stamp_path = null;
    $account_status = 'pending';

    if ($type_compte === 'entreprise') {
        $commercial_reg_num = trim($_POST['commercial_reg_num'] ?? '');
        $tax_id = trim($_POST['tax_id'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $industry_sector = $_POST['industry_sector'] ?? '';
        $company_size = $_POST['company_size'] ?? '';
        $year_established = $_POST['year_established'] ?? '';

        /*
        if (empty($commercial_reg_num) || empty($tax_id) || empty($address) || empty($industry_sector) || empty($company_size) || empty($year_established)) {
            echo json_encode(['success' => false, 'message' => 'Veuillez remplir toutes les informations de l\'entreprise.']);
            exit;
        }

        // Domain Validation
        $domain = substr(strrchr($email, "@"), 1);
        $personal_domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'live.com', 'aol.com', 'icloud.com'];
        if (in_array(strtolower($domain), $personal_domains)) {
            echo json_encode(['success' => false, 'message' => 'Les adresses e-mail personnelles ne sont pas autorisées pour les entreprises.']);
            exit;
        }
        $company_name_normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nom));
        $domain_base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('.', $domain)[0]));
        
        if (strpos($domain_base, $company_name_normalized) === false && strpos($company_name_normalized, $domain_base) === false) {
            echo json_encode(['success' => false, 'message' => "Le domaine de l'e-mail (@$domain) ne correspond pas au nom de l'entreprise ($nom)."]);
            exit;
        }

        // uniqueness of Business IDs
        $check_biz = $db->prepare("SELECT id FROM users WHERE commercial_registration_number = :crn OR tax_identification_number = :tax");
        $check_biz->execute([':crn' => $commercial_reg_num, ':tax' => $tax_id]);
        if ($check_biz->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Numéro de registre ou NIF déjà utilisé par une autre entreprise.']);
            exit;
        }

        // Uploads
        $upload_dir = '../uploads/entreprise_docs/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $files = ['doc_registry' => 'Registre', 'doc_tax' => 'NIF', 'doc_stamp' => 'Cachet'];
        $uploaded_paths = [];
        foreach ($files as $input_name => $label) {
            if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => "Le document $label est obligatoire."]);
                exit;
            }
            $fileActExt = strtolower(pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION));
            if ($fileActExt !== 'pdf') {
                echo json_encode(['success' => false, 'message' => "Le document $label doit être au format PDF."]);
                exit;
            }
            if ($_FILES[$input_name]['size'] > 5000000) {
                echo json_encode(['success' => false, 'message' => "Le document $label est trop volumineux (max 5MB)."]);
                exit;
            }
            $fileNameNew = uniqid('', true) . "_" . $input_name . ".pdf";
            $fileDestination = $upload_dir . $fileNameNew;
            if (!move_uploaded_file($_FILES[$input_name]['tmp_name'], $fileDestination)) {
                echo json_encode(['success' => false, 'message' => "Erreur lors du téléchargement de $label."]);
                exit;
            }
            $uploaded_paths[$input_name] = 'uploads/entreprise_docs/' . $fileNameNew;
        }

        $doc_registry_path = $uploaded_paths['doc_registry'];
        $doc_tax_path = $uploaded_paths['doc_tax'];
        $doc_stamp_path = $uploaded_paths['doc_stamp'];
        */
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
        
        // Include new columns
        $insert_query = "INSERT INTO users (nom, prenom, email, password, type_compte, telephone, verification_token, actif, account_status, adresse, commercial_registration_number, tax_identification_number, industry_sector, company_size, year_established, commercial_registry_doc, tax_document, official_stamp_doc) 
        VALUES (:nom, :prenom, :email, :password, :type, :telephone, :token, :actif, :account_status, :address, :crn, :tax, :ind, :csz, :year, :d_reg, :d_tax, :d_stamp)";
        $insert_stmt = $db->prepare($insert_query);
        $actif = ($type_compte === 'etudiant') ? 1 : 0;
        
        $insert_stmt->bindParam(':nom', $nom);
        $insert_stmt->bindParam(':prenom', $prenom);
        $insert_stmt->bindParam(':email', $email);
        $insert_stmt->bindParam(':password', $password_hash);
        $insert_stmt->bindParam(':type', $type_compte);
        $insert_stmt->bindParam(':telephone', $telephone);
        $insert_stmt->bindParam(':token', $verification_token);
        $insert_stmt->bindParam(':actif', $actif, PDO::PARAM_INT);
        $insert_stmt->bindParam(':account_status', $account_status);
        $insert_stmt->bindParam(':address', $address);
        $insert_stmt->bindParam(':crn', $commercial_reg_num);
        $insert_stmt->bindParam(':tax', $tax_id);
        $insert_stmt->bindParam(':ind', $industry_sector);
        $insert_stmt->bindParam(':csz', $company_size);
        $insert_stmt->bindParam(':year', $year_established);
        $insert_stmt->bindParam(':d_reg', $doc_registry_path);
        $insert_stmt->bindParam(':d_tax', $doc_tax_path);
        $insert_stmt->bindParam(':d_stamp', $doc_stamp_path);
        
        if ($insert_stmt->execute()) {
            $user_id = $db->lastInsertId();
            if ($type_compte === 'etudiant') {
                $db->exec("INSERT INTO profils (user_id) VALUES ($user_id)");
                echo json_encode(['success' => true, 'message' => 'Inscription réussie ! Vous pouvez maintenant vous connecter.']);
            } else {
                // Send email verification with PHPMailer (Disabled for testing)
                /*
                try {
                    require_once '../PHPMailer/src/Exception.php';
                    require_once '../PHPMailer/src/PHPMailer.php';
                    require_once '../PHPMailer/src/SMTP.php';
                    
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    
                    // You would put your SMTP config here. Since it's local, we might just mock it or configure with Mailtrap.
                    // To avoid errors, we'll try to use standard local mail or disable exceptions.
                    $mail->isSMTP();
                    $mail->Host       = '127.0.0.1'; // Use local or mailpit
                    $mail->SMTPAuth   = false;
                    $mail->Port       = 1025; // typically Mailhog port
                    
                    $mail->setFrom('no-reply@stagematch.com', 'StageMatch Enterprise');
                    $mail->addAddress($email, $nom);
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Verify your Enterprise Account';
                    $verify_link = "http://" . $_SERVER['HTTP_HOST'] . "/stage_pi/api/verify.php?token=" . $verification_token;
                    $mail->Body    = "Hello $nom,<br><br>Please click the link to verify your email:<br><a href='$verify_link'>$verify_link</a><br><br>After verification, an admin will review your official documents.";
                    
                    $mail->send();
                    echo json_encode(['success' => true, 'message' => 'Inscription réussie ! Un email de vérification vous a été envoyé.']);
                } catch (Exception $e) {
                    echo json_encode(['success' => true, 'message' => 'Inscription enregistrée, mais l\'email n\'a pas pu être envoyé.']);
                }
                */
                echo json_encode(['success' => true, 'message' => 'Inscription réussie ! Vous pouvez maintenant vous connecter (Mode Test).']);
            }
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
