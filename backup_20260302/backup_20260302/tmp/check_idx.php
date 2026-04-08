<?php
$db = new mysqli('localhost', 'root', '', 'stagematch');
$res = $db->query("SHOW INDEXES FROM candidatures");
while($row = $res->fetch_assoc()) {
    echo "Table: {$row['Table']}, Non_unique: {$row['Non_unique']}, Key_name: {$row['Key_name']}, Column_name: {$row['Column_name']}\n";
}
