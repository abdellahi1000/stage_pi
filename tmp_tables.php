<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();
$st = $db->query('SHOW TABLES');
echo json_encode($st->fetchAll(PDO::FETCH_COLUMN));
