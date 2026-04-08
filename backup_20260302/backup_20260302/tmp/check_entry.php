<?php
$db = new mysqli('localhost', 'root', '', 'stagematch');
$res = $db->query("SELECT * FROM candidatures WHERE user_id = 18 AND offre_id = 41");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
