<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

try {
    echo "Adding blocked_students table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS blocked_students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        student_id INT NOT NULL,
        reason TEXT,
        blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (company_id),
        INDEX (student_id),
        UNIQUE KEY (company_id, student_id)
    )");
    echo "Table blocked_students created/verified.\n";
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
