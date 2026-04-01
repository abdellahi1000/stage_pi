<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Refresh theme from DB when user is logged in (so toggle + reload shows correct theme)
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/db_connect.php';
        $db = (new Database())->getConnection();
        $th = $db->prepare("SELECT u.photo_profil, p.theme FROM users u LEFT JOIN preferences_utilisateur p ON u.id = p.user_id WHERE u.id = :uid");
        $th->bindParam(':uid', $_SESSION['user_id']);
        $th->execute();
        $row = $th->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user_theme'] = ($row && !empty($row['theme'])) ? $row['theme'] : 'light';
        if ($row && !empty($row['photo_profil'])) {
            $_SESSION['photo_profil'] = $row['photo_profil'];
        }
    } catch (Exception $e) {
        $_SESSION['user_theme'] = isset($_SESSION['user_theme']) ? $_SESSION['user_theme'] : 'light';
    }
}

/**
 * Checks if the user is logged in.
 * If not, redirects to the login page.
 * If logged in but the user type is not allowed for the current folder, redirects to the correct dashboard.
 */
function check_auth($allowed_type = null, $required_role = null) {
    $root_path = '/stage_pi/'; // Adjust this if the project is not in the root
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: " . $root_path . "login.php");
        exit;
    }

    // Role check
    if ($required_role !== null) {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $required_role) {
            header("Location: " . $root_path . "login.php");
            exit;
        }
    }

    // Type check
    if ($allowed_type !== null && $_SESSION['user_type'] !== $allowed_type) {
        if ($_SESSION['user_type'] === 'entreprise') {
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Administrator') {
                header("Location: " . $root_path . "administrator/index.php");
            } else {
                header("Location: " . $root_path . "enterprise/index.php");
            }
        } else if ($_SESSION['user_type'] === 'etudiant') {
            header("Location: " . $root_path . "students/index.php");
        } else {
            header("Location: " . $root_path . "logout.php");
        }
        exit;
    }
}
