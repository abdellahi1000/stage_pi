<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();
$cols = $db->query("DESCRIBE candidatures")->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}
