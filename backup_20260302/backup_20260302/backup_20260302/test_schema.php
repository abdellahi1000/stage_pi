<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach($tables as $t) {
    echo "Table: $t\n";
    foreach($db->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        echo "  $c[Field] : $c[Type]\n";
    }
}
