<?php
require_once 'include/db_connect.php';
$database = new Database();
$db = $database->getConnection();

function getColumns($db, $table) {
    try {
        $stmt = $db->query("DESCRIBE $table");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return ["error" => $e->getMessage()];
    }
}

echo "OFFRES SCHEMA:\n";
print_r(getColumns($db, 'offres'));
echo "\nCANDIDATURES SCHEMA:\n";
print_r(getColumns($db, 'candidatures'));
?>
