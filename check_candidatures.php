<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();
echo "--- candidatures table ---\n";
print_r($db->query('DESCRIBE candidatures')->fetchAll(PDO::FETCH_ASSOC));
