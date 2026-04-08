<?php
require 'include/db_connect.php';
$stmt = $pdo->query("SELECT email, password_admin FROM users WHERE password_admin IS NOT NULL AND password_admin != '' LIMIT 5");
while ($row = $stmt->fetch()) {
    echo "Email: {$row['email']} - PWD_ADMIN: {$row['password_admin']}\n";
}
