<?php
require 'include/db_connect.php';
$db = (new Database())->getConnection();
$q = $db->query('SHOW COLUMNS FROM offres');
print_r($q->fetchAll(PDO::FETCH_ASSOC));
?>
