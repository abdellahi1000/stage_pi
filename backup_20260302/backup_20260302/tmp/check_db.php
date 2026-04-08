<?php
$db = new mysqli('localhost', 'root', '', 'stagematch');
if ($db->connect_error) die('Connect Error: ' . $db->connect_error);

echo "--- profils ---\n";
$res = $db->query("DESCRIBE profils");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}

echo "\n--- etudiant_documents ---\n";
$res = $db->query("SHOW TABLES LIKE 'etudiant_documents'");
if ($res->num_rows > 0) {
    echo "Exists\n";
    $res2 = $db->query("DESCRIBE etudiant_documents");
    while ($row = $res2->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . "\n";
    }
} else {
    echo "Doesn't Exist\n";
}
?>
