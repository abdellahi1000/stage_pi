<?php
$db = new pdo('mysql:host=localhost;dbname=stagematch', 'root', '');
$res = $db->query('DESCRIBE offres');
while($row = $res->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>
