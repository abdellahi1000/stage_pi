<?php
require_once '../include/db_connect.php';

echo "--- DATABASE CHECK ---\n";
// Check Tables
$tables = ['cv_user_data', 'etudiant_motivation'];
foreach ($tables as $t) {
    $res = $conn->query("SHOW TABLES LIKE '$t'");
    if ($res->num_rows > 0) {
        echo "Table '$t' exists.\n";
        // Check columns for etudiant_motivation
        if ($t === 'etudiant_motivation') {
            $cols = $conn->query("SHOW COLUMNS FROM $t");
            while ($c = $cols->fetch_assoc()) {
                echo "  - Column: " . $c['Field'] . " (" . $c['Type'] . ")\n";
            }
        }
    } else {
        echo "Table '$t' DOES NOT EXIST!\n";
    }
}

echo "\n--- API CHECK ---\n";
$apis = ['api/cv.php', 'api/motivation.php'];
foreach ($apis as $api) {
    if (file_exists("../$api")) {
        echo "API file '$api' exists.\n";
    } else {
        echo "API file '$api' MISSING!\n";
    }
}

echo "\n--- FILE CHECK ---\n";
$files = ['js/createCV.js', 'css/createCV.css', 'students/create_cv.php'];
foreach ($files as $f) {
    if (file_exists("../$f")) {
        echo "File '$f' exists. Size: " . filesize("../$f") . " bytes\n";
    } else {
        echo "File '$f' MISSING!\n";
    }
}
?>
