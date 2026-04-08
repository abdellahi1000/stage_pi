<?php
require_once 'include/db.php';
$stmt = $db->query("DESCRIBE profils");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
