<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();
echo "--- users table ---\n";
print_r($db->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC));
echo "\n--- profils table ---\n";
print_r($db->query('DESCRIBE profils')->fetchAll(PDO::FETCH_ASSOC));
