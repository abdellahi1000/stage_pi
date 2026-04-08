<?php
$db = new mysqli('localhost', 'root', '', 'stagematch');
if ($db->connect_error) die('Connect Error: ' . $db->connect_error);

$sql = "CREATE TABLE IF NOT EXISTS etudiant_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('cv', 'motivation') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($db->query($sql)) {
    echo "Table etudiant_documents created successfully\n";
    
    // Migrate existing ones
    $res = $db->query("SELECT user_id, cv_path, lettre_motivation_path FROM profils WHERE cv_path IS NOT NULL OR lettre_motivation_path IS NOT NULL");
    while ($row = $res->fetch_assoc()) {
        if ($row['cv_path']) {
            $name = basename($row['cv_path']);
            $db->query("INSERT INTO etudiant_documents (user_id, type, file_path, file_name) VALUES ({$row['user_id']}, 'cv', '{$row['cv_path']}', '{$name}')");
        }
        if ($row['lettre_motivation_path']) {
            $name = basename($row['lettre_motivation_path']);
            $db->query("INSERT INTO etudiant_documents (user_id, type, file_path, file_name) VALUES ({$row['user_id']}, 'motivation', '{$row['lettre_motivation_path']}', '{$name}')");
        }
    }
    echo "Migration completed\n";
} else {
    echo "Error creating table: " . $db->error . "\n";
}
?>
