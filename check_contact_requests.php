<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();
echo "--- contact_requests table ---\n";
print_r($db->query('DESCRIBE contact_requests')->fetchAll(PDO::FETCH_ASSOC));
