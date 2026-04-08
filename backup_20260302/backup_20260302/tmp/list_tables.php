<?php
require_once dirname(__DIR__) . '/include/db_connect.php';
$database = new Database();
$db = $database->getConnection();
$res = $db->query("SHOW TABLES");
while($row = $res->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>
