<?php
/**
 * Outputs body class for theme (dark/light/transparent). Use in <body> tag.
 * Requires session.php to be loaded first (sets $_SESSION['user_theme'] from DB).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_dark = (!empty($_SESSION['user_theme']) && $_SESSION['user_theme'] === 'dark');
echo $is_dark ? 'theme-dark bg-gray-50' : 'theme-light bg-gray-50';
?>
