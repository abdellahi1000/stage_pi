<?php
require_once '../include/db_connect.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Jeton manquant.");
}

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("SELECT id, nom, account_status FROM users WHERE verification_token = :token LIMIT 1");
$stmt->execute([':token' => $token]);

if ($stmt->rowCount() === 1) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user['account_status'] === 'pending') {
        $update = $db->prepare("UPDATE users SET account_status = 'email_verified', verification_token = NULL WHERE id = :id");
        $update->execute([':id' => $user['id']]);
        $msg = "Félicitations " . htmlspecialchars($user['nom']) . " ! Votre adresse e-mail a été vérifiée avec succès.<br><br>Votre compte est maintenant en attente de validation par un administrateur. Vous recevrez une notification une fois vos documents vérifiés.";
    } else {
        $msg = "Ce lien a déjà été utilisé ou le compte est déjà vérifié.";
    }
} else {
    $msg = "Lien de vérification invalide.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification de l'email</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f7f6; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 500px; text-align: center; }
        .card h1 { color: #007bff; }
        .card p { font-size: 16px; color: #555; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; color: white; background: #007bff; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Vérification de compte</h1>
        <p><?php echo $msg; ?></p>
        <a href="../login.php" class="btn">Aller à la page de connexion</a>
    </div>
</body>
</html>
