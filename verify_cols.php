<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();
$cols = $db->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($cols);
echo "</pre>";
