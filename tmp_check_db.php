<?php
require_once 'include/db_connect.php';
$database = new Database();
$db = $database->getConnection();
$stmt = $db->query("DESCRIBE offres");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
