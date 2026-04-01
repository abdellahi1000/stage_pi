<?php 
require_once '../include/session.php';
require_once '../include/db_connect.php';
check_auth('entreprise', 'Administrator');

$database = new Database();
$db = $database->getConnection();
$company_id = $_SESSION['company_id'];

// Handling quick delete for now directly in PHP, since it's simple
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $del_id = $_POST['delete_user_id'];
    if ($del_id != $_SESSION['user_id']) {
        $stmt_del = $db->prepare("DELETE FROM users WHERE id = :id AND company_id = :cid");
        $stmt_del->execute([':id' => $del_id, ':cid' => $company_id]);
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
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt_ins = $db->prepare("INSERT INTO users (nom, prenom, email, email_employer, password, type_compte, role, company_id, can_create_offers, can_delete_offers) 
                                  VALUES (?, ?, ?, ?, ?, 'entreprise', 'manager', ?, 0, 0)");
        $stmt_ins->execute([$nom, $prenom, $email, $email, $hash, $company_id]);
        $success_msg = "Collaborateur ajouté avec succès.";
    }
}

// Fetch all users of this company
$stmt = $db->prepare("SELECT id, nom, prenom, email, role FROM users WHERE company_id = :cid ORDER BY role ASC, nom ASC");
$stmt->execute([':cid' => $company_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <button onclick="document.getElementById('modalAddUser').classList.remove('hidden')" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-colors flex items-center gap-2 w-fit">
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
                                        <?php if ($u['role'] === 'Administrator'): ?>
                                            <span class="inline-flex px-3 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-bold">Administrateur</span>
                                        <?php else: ?>
                                            <span class="inline-flex px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold">Manager</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-5 text-right flex items-center justify-end gap-2">
                                        <a href="contact.php" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors flex items-center justify-center" title="Voir les messages">
                                            <i class="fas fa-envelope text-sm"></i>
                                        </a>
                                        <?php if ($u['id'] != $_SESSION['user_id'] && $u['role'] !== 'Administrator'): ?>
                                        <form method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                            <input type="hidden" name="delete_user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors flex items-center justify-center">
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

            <!-- Add User Modal -->
            <div id="modalAddUser" class="fixed inset-0 z-[100] hidden items-center justify-center">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('modalAddUser').classList.add('hidden')"></div>
                <div class="bg-white w-[95%] max-w-lg mx-auto rounded-3xl shadow-2xl relative z-10 overflow-hidden mt-10 md:mt-0">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-xl font-black text-gray-900">Nouveau Collaborateur</h3>
                        <button onclick="document.getElementById('modalAddUser').classList.add('hidden')" class="text-gray-400 hover:text-rose-500 transition-colors w-8 h-8 flex justify-center items-center bg-white rounded-xl shadow-sm"><i class="fas fa-times"></i></button>
                    </div>
                    <form method="POST" class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                        <input type="hidden" name="action" value="add_user">
                        
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Utilisateur (Nom & Prénom)</label>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="prenom" placeholder="Prénom" required class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-colors">
                                <input type="text" name="nom" placeholder="Nom" required class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-colors">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Email professionnel</label>
                            <input type="email" name="email" required class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-colors">
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Rôle</label>
                            <select name="role" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-colors appearance-none cursor-pointer">
                                <option value="manager">Manager</option>
                            </select>
                            <p class="text-[10px] text-gray-400 font-semibold mt-1">Le rôle définit le type de permission. Les Managers peuvent gérer les offres mais ne peuvent pas modifier les paramètres de l'entreprise.</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Statut</label>
                            <select name="statut_compte" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-colors appearance-none cursor-pointer">
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Mot de passe temporaire</label>
                            <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-colors">
                        </div>

                        <div class="pt-4 flex justify-between gap-3 border-t border-gray-100 mt-4">
                            <div class="flex gap-2">
                                <button type="button" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition-colors" title="Modifier plus tard">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="w-10 h-10 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-500 flex items-center justify-center transition-colors" title="Annuler/Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="document.getElementById('modalAddUser').classList.add('hidden')" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors text-sm">
                                    Annuler
                                </button>
                                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-blue-600/20 text-sm">
                                    Créer l'accès
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
