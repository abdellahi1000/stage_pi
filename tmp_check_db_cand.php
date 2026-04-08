<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();
$res = $db->query("DESCRIBE candidatures");
echo json_encode($res->fetchAll(PDO::FETCH_ASSOC));
?>
