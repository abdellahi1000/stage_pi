<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();
$tables = ['company_emails', 'company_phones', 'company_social_links', 'entreprise_achievements'];
foreach($tables as $t) {
    try {
        $q = $db->query("DESCRIBE $t");
        echo "Table $t:\n";
        print_r($q->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo "Table $t: ERROR " . $e->getMessage() . "\n";
    }
}
