<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();
print_r($db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
