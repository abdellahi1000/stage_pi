<?php
require_once 'include/db_connect.php';
$database = new Database();
$db = $database->getConnection();
$stmt = $db->query("SELECT DISTINCT statut FROM candidatures");
$statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Current statuses in DB: " . implode(", ", $statuses) . "\n";
?>
