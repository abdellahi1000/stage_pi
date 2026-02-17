<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if the user is logged in.
 * If not, redirects to the login page.
 * If logged in but the user type is not allowed for the current folder, redirects to the correct dashboard.
 */
function check_auth($allowed_type = null) {
    $root_path = '/stage_pi/'; // Adjust this if the project is not in the root
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: " . $root_path . "login.php");
        exit;
    }

    if ($allowed_type !== null && $_SESSION['user_type'] !== $allowed_type) {
        if ($_SESSION['user_type'] === 'entreprise') {
            header("Location: " . $root_path . "enterprise/index.php");
        } else if ($_SESSION['user_type'] === 'etudiant') {
            header("Location: " . $root_path . "students/index.php");
        } else {
            // Default fallback
            header("Location: " . $root_path . "logout.php");
        }
        exit;
    }
}
?>
