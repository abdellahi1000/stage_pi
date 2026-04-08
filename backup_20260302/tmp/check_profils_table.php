<?php
require_once dirname(__DIR__) . '/include/db_connect.php';
$database = new Database();
$db = $database->getConnection();
$res = $db->query("DESC profils");
while($row = $res->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>
