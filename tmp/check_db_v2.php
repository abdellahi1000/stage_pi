<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

echo "--- TABLES ---\n";
try {
    $res = $db->query("SHOW TABLES");
    while($row = $res->fetch(PDO::FETCH_NUM)) {
        echo $row[0] . "\n";
    }
} catch(Exception $e) { echo $e->getMessage(); }

echo "\n--- emails ---\n";
try { $db->query("DESCRIBE company_emails"); echo "EXISTS"; } catch(Exception $e) { echo "MISSING"; }

echo "\n--- phones ---\n";
try { $db->query("DESCRIBE company_phones"); echo "EXISTS"; } catch(Exception $e) { echo "MISSING"; }
