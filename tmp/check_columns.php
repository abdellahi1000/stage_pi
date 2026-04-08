<?php
require_once dirname(__DIR__) . '/include/db_connect.php';
$stmt = $pdo->query("DESC cv_user_data");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}
?>
