<?php
require_once 'include/db_connect.php';
$db = (new Database())->getConnection();

// Fix offres entreprise name mapping to the actual company name
$stmt = $db->query("SELECT o.id, o.user_id, o.entreprise, u.company_id, u.nom, c.name as c_name 
                    FROM offres o 
                    JOIN users u ON o.user_id = u.id 
                    LEFT JOIN companies c ON u.company_id = c.id");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Determine target company name. If manager, company_id links to companies (or the administrator's user record, which should match).
    // The previous migration set up companies.id = admin's user.id
    
    // Actually, in the last session we created 'companies' table but users don't seem to be referencing companies.name if we didn't migrate that properly.
    // wait, I did create a $db->query("CREATE TABLE IF NOT EXISTS companies ...") inside migrate_architecture.php.
    
    // It's probably simpler to just do: if the user is a manager, their company name is the admin's 'nom'.
    // Let's use the current company_id (which is admin's user id) to get the admin's name.
    $cid = $row['company_id'] ? $row['company_id'] : $row['user_id'];
    
    $stmt_admin = $db->prepare("SELECT nom FROM users WHERE id = ?");
    $stmt_admin->execute([$cid]);
    $admin_name = $stmt_admin->fetchColumn();
    
    if ($admin_name && $admin_name !== $row['entreprise']) {
        $update = $db->prepare("UPDATE offres SET entreprise = ?, user_id = ? WHERE id = ?");
        $update->execute([$admin_name, $cid, $row['id']]);
        echo "Updating offer " . $row['id'] . " to company: " . $admin_name . " (user_id changed to $cid)\n";
    }
}
echo "Migration complete.\n";
?>
