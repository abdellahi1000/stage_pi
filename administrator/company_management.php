<?php 
require_once '../include/session.php';
require_once '../include/db_connect.php';
check_auth('admin');

$database = new Database();
$db = $database->getConnection();

// --- Fix for missing enterprise_id for global admins or unlinked accounts ---
if (!isset($_SESSION['entreprise_id']) || empty($_SESSION['entreprise_id']) || $_SESSION['entreprise_id'] == 0) {
    // If user is admin but has no company, for convenience in this environment, 
    // we'll try to find the first company or the one that might belong to them
    $stmt_find = $db->query("SELECT id FROM entreprises LIMIT 1");
    $fallback_id = $stmt_find->fetchColumn();
    if ($fallback_id) {
        $_SESSION['entreprise_id'] = $fallback_id;
        // Also fetch name
        $stmt_name = $db->prepare("SELECT name FROM entreprises WHERE id = ?");
        $stmt_name->execute([$fallback_id]);
        $_SESSION['company_name'] = $stmt_name->fetchColumn();
        
        // PERSIST the fix in database for this admin so it doesn't happen again
        if (isset($_SESSION['user_id'])) {
            $stmt_upd = $db->prepare("UPDATE users SET entreprise_id = ? WHERE id = ? AND entreprise_id IS NULL");
            $stmt_upd->execute([$fallback_id, $_SESSION['user_id']]);
        }
    }
}
// --- End Fix ---

$entreprise_id = $_SESSION['entreprise_id'] ?? 0;

// Handling quick delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $del_id = $_POST['delete_user_id'];
    if ($del_id != $_SESSION['user_id']) {
        $stmt_del = $db->prepare("DELETE FROM users WHERE id = :id AND entreprise_id = :eid");
        $stmt_del->execute([':id' => $del_id, ':eid' => $entreprise_id]);
        $success_msg = "Collaborateur supprimé avec succès.";
    }
}

// Handling create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Check if email exists
    $stmt_check = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt_check->execute([$email]);
    if ($stmt_check->rowCount() > 0) {
        $error_msg = "Cet email est déjà utilisé.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_ins = $db->prepare("INSERT INTO users (nom, prenom, email, password, role, entreprise_id, actif) 
                                      VALUES (?, ?, ?, ?, 'employee', ?, 1)");
            $stmt_ins->execute([$nom, $prenom, $email, $hash, $entreprise_id]);
            $success_msg = "Collaborateur ajouté avec succès.";
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $error_msg = "Erreur : ID entreprise invalide ou inexistant. Impossible d'ajouter ce collaborateur.";
            } else {
                $error_msg = "Une erreur est survenue lors de la création de l'accès.";
            }
        }
    }
}

$stmt = $db->prepare("SELECT id, nom, prenom, email, role, actif FROM users WHERE entreprise_id = :eid ORDER BY role ASC, nom ASC");
$stmt->execute([':eid' => $entreprise_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Shared logic for Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $statut = ($_POST['statut_compte'] ?? 'active') === 'active' ? 1 : 0;
    
    try {
        if ($action === 'add_user') {
            $password = $_POST['password'] ?? '';
            // Check if email exists
            $stmt_check = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check->execute([$email]);
            if ($stmt_check->rowCount() > 0) {
                $error_msg = "Cet email est déjà utilisé.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt_ins = $db->prepare("INSERT INTO users (nom, prenom, email, password, role, entreprise_id, actif) 
                                          VALUES (?, ?, ?, ?, 'employee', ?, ?)");
                $stmt_ins->execute([$nom, $prenom, $email, $hash, $entreprise_id, $statut]);
                $success_msg = "Collaborateur ajouté avec succès.";
            }
        } 
        elseif ($action === 'edit_user') {
            $user_id = $_POST['user_id'] ?? 0;
            $stmt_upd = $db->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, actif = ? WHERE id = ? AND entreprise_id = ?");
            $stmt_upd->execute([$nom, $prenom, $email, $statut, $user_id, $entreprise_id]);
            $success_msg = "Collaborateur mis à jour avec succès.";
            
            // Email (Safe check)
            try {
                require_once '../PHPMailer/src/Exception.php'; require_once '../PHPMailer/src/PHPMailer.php'; require_once '../PHPMailer/src/SMTP.php';
                $mail = new PHPMailer\PHPMailer\PHPMailer(true); $mail->isSMTP(); $mail->Host = '127.0.0.1'; $mail->SMTPAuth = false; $mail->Port = 1025; 
                $mail->setFrom('no-reply@stagematch.com', 'StageMatch Admin'); $mail->addAddress($email);
                $mail->isHTML(true); $mail->Subject = 'Mise à jour accès - StageMatch';
                $mail->Body = "Bonjour $prenom,<br>Votre compte a été mis à jour. Statut : <b>" . ($statut ? 'Actif' : 'Inactif') . "</b>.";
                $mail->send();
            } catch (Exception $e) {}
        }
    } catch (PDOException $e) {
        $error_msg = "Erreur serveur : " . $e->getMessage();
    }
    
    // Refresh for the rest of the page
    $stmt->execute([':eid' => $entreprise_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Management - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto bg-gray-50 md:ml-64">
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <span class="font-bold text-blue-600">StageMatch Admin</span>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>

            <div class="max-w-5xl mx-auto px-6 py-10">
                <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-4xl font-extrabold text-gray-900 mb-1">Company Management</h1>
                        <p class="text-gray-500 font-medium">Gérez votre équipe de recruteurs.</p>
                    </div>
                    <button onclick="openFormModal('add')" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-all hover:-translate-y-0.5 active:scale-95 flex items-center gap-2 w-fit">
                        <i class="fas fa-plus"></i>
                        Ajouter un collaborateur
                    </button>
                </div>

                <?php if (!empty($success_msg)): ?>
                    <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-2xl border border-green-200 font-medium flex items-center gap-3">
                        <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-2xl border border-red-200 font-medium flex items-center gap-3">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                                    <th class="pb-6 px-4">Utilisateur</th>
                                    <th class="pb-6 px-4">Email</th>
                                    <th class="pb-6 px-4">Rôle</th>
                                    <th class="pb-6 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach($users as $u): ?>
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 font-bold text-sm">
                                                <?php echo substr($u['prenom'], 0, 1) . substr($u['nom'], 0, 1); ?>
                                            </div>
                                            <span class="font-bold text-gray-900"><?php echo htmlspecialchars($u['prenom'] . ' ' . $u['nom']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-5 font-medium text-gray-600">
                                        <?php echo htmlspecialchars($u['email']); ?>
                                    </td>
                                    <td class="px-4 py-5">
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="inline-flex px-3 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-bold">Manager (Admin)</span>
                                        <?php else: ?>
                                            <span class="inline-flex px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold">Collaborateur (Employee)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-5 text-right flex items-center justify-end gap-3">
                                        <a href="contact.php" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all duration-300 flex items-center justify-center shadow-sm" title="Voir les messages">
                                            <i class="fas fa-envelope text-sm"></i>
                                        </a>
                                        <?php if ($u['id'] != $_SESSION['user_id'] && $u['role'] !== 'admin'): ?>
                                        <button onclick='openFormModal("edit", <?php echo json_encode($u); ?>)' class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all duration-300 flex items-center justify-center shadow-sm hover:rotate-12 active:scale-90" title="Modifier">
                                            <i class="fas fa-pencil-alt text-sm"></i>
                                        </button>
                                        <form method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                            <input type="hidden" name="delete_user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all duration-300 flex items-center justify-center shadow-sm hover:scale-110 active:scale-90" title="Supprimer">
                                                <i class="fas fa-trash-alt text-sm"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="modalAddUser" class="fixed inset-0 z-[100] hidden items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeFormModal()"></div>
                <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-[0_30px_100px_rgba(0,0,0,0.2)] relative z-10 overflow-hidden transform transition-all duration-300" id="userFormDialog">
                    <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
                        <div>
                            <h3 id="formTitle" class="text-2xl font-black text-gray-900 tracking-tight">Accès Collaborateur</h3>
                            <p id="formSub" class="text-[10px] font-bold text-blue-600 uppercase tracking-[0.2em] mt-0.5">Configuration du compte</p>
                        </div>
                        <button onclick="closeFormModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-rose-500 hover:border-rose-100 transition-all shadow-sm"><i class="fas fa-times"></i></button>
                    </div>
                    <form method="POST" class="p-8 space-y-6">
                        <input type="hidden" name="action" id="formAction" value="add_user">
                        <input type="hidden" name="user_id" id="formUserId">
                        
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest">Informations Identité</label>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="prenom" id="f_prenom" placeholder="Prénom" required class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50/50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                                <input type="text" name="nom" id="f_nom" placeholder="Nom" required class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50/50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest">Email professionnel</label>
                            <input type="email" name="email" id="f_email" required placeholder="email@entreprise.com" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50/50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                        </div>
                        
                        <div class="space-y-2" id="pwdContainer">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest">Mot de passe temporaire</label>
                            <input type="password" name="password" id="f_password" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50/50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest">Statut & Permissions</label>
                            <select name="statut_compte" id="f_statut" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50/50 focus:bg-white focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer">
                                <option value="active">Actif (Accès autorisé)</option>
                                <option value="inactive">Inactif (Accès révoqué)</option>
                            </select>
                        </div>

                        <div class="pt-6 flex justify-end gap-3 border-t border-gray-50 mt-4">
                            <button type="button" onclick="closeFormModal()" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-2xl transition-all text-sm">Annuler</button>
                            <button type="submit" id="submitBtn" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl transition-all shadow-lg shadow-blue-200 text-sm active:scale-95">Créer l'accès</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function openFormModal(mode, data = null) {
                    const modal = document.getElementById('modalAddUser');
                    const action = document.getElementById('formAction');
                    const title = document.getElementById('formTitle');
                    const sub = document.getElementById('formSub');
                    const btn = document.getElementById('submitBtn');
                    const pwd = document.getElementById('pwdContainer');
                    
                    if (mode === 'edit' && data) {
                        action.value = 'edit_user';
                        document.getElementById('formUserId').value = data.id;
                        title.innerText = 'Modifier l\'Accès';
                        sub.innerText = 'Mise à jour du collaborateur';
                        btn.innerText = 'Enregistrer les modifications';
                        pwd.style.display = 'none';
                        
                        document.getElementById('f_nom').value = data.nom;
                        document.getElementById('f_prenom').value = data.prenom;
                        document.getElementById('f_email').value = data.email;
                        document.getElementById('f_statut').value = data.actif == 1 ? 'active' : 'inactive';
                    } else {
                        action.value = 'add_user';
                        document.getElementById('formUserId').value = '';
                        title.innerText = 'Nouveau Collaborateur';
                        sub.innerText = 'Création d\'un compte accès';
                        btn.innerText = 'Créer l\'accès';
                        pwd.style.display = 'block';
                        
                        document.getElementById('f_nom').value = '';
                        document.getElementById('f_prenom').value = '';
                        document.getElementById('f_email').value = '';
                        document.getElementById('f_password').value = '';
                    }

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeFormModal() {
                    document.getElementById('modalAddUser').classList.add('hidden');
                    document.getElementById('modalAddUser').classList.remove('flex');
                }
            </script>
        </main>
    </div>
</body>
</html>
