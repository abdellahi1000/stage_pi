<?php
require_once 'include/session.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['user_type'] === 'entreprise') {
    header("Location: enterprise/index.php");
} else if ($_SESSION['user_type'] === 'etudiant') {
    header("Location: students/index.php");
} else {
    // If unknown type, logout
    header("Location: logout.php");
}
exit;
?>
