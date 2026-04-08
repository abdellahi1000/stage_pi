<?php
require_once __DIR__ . '/db_connect.php';
$is_entreprise = isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'employee');
$current_page = basename($_SERVER['PHP_SELF']);

// Notification system
$notif_count = 0;
if (isset($_SESSION['user_id'])) {
    $db_notif = (new Database())->getConnection();
    if ($is_entreprise) {
        // Count new candidatures (pending AND not viewed) for company
        $stmt_notif = $db_notif->prepare("SELECT COUNT(*) FROM candidatures c INNER JOIN offres o ON c.offre_id = o.id WHERE o.entreprise_id = ? AND c.statut = 'pending' AND c.vu_par_entreprise = 0");
        $stmt_notif->execute([$_SESSION['entreprise_id']]);
        $notif_count = $stmt_notif->fetchColumn();
    } else {
        // Count unread responses for student (statut changed from pending)
        $stmt_notif = $db_notif->prepare("SELECT COUNT(*) FROM candidatures WHERE user_id = ? AND vu_par_etudiant = 0 AND statut != 'pending'");
        $stmt_notif->execute([$_SESSION['user_id']]);
        $notif_count = $stmt_notif->fetchColumn();
    }
}

// Define links based on role
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$profile_link = $is_admin ? 'account.php' : ($is_entreprise ? 'compte.php' : 'compte.php');
$offres_link = $is_admin ? 'offers.php' : 'offres.php';
$apps_link = $is_admin ? 'applications.php' : ($is_entreprise ? 'gerer_candidatures.php' : 'mes_candidatures.php');
?>
<!-- Overlay for mobile -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-[900] hidden transition-opacity duration-300"></div>

<div id="sidebar" class="sidebar fixed left-0 top-0 h-screen w-64 bg-gradient-to-b from-blue-900 to-purple-900 text-white shadow-2xl z-40 transition-transform duration-300">
    <div class="sidebar-nav-container">
        <div class="flex items-center justify-between px-6 py-8 border-b border-white border-opacity-10">
            <h1 class="text-2xl font-black tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-blue-200">StageMatch</h1>
            <!-- Close Button (Mobile Only) -->
            <button id="sidebarClose" class="md:hidden text-white/70 hover:text-white transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="flex items-center px-6 py-5 border-b border-white border-opacity-10">
            <div class="w-11 h-11 bg-white bg-opacity-10 rounded-xl flex items-center justify-center mr-3 shadow-inner overflow-hidden border border-white/10">
                <?php 
                $photo_path = !empty($_SESSION['photo_profil']) ? $_SESSION['photo_profil'] : '';
                $display_photo = !empty($photo_path) ? '../' . $photo_path : '';
                ?>
                <?php if (!empty($photo_path)): ?>
                    <img id="sidebarProfilePhoto" src="<?php echo htmlspecialchars($display_photo); ?>" alt="Profile" class="w-full h-full object-cover">
                <?php else: ?>
                    <i class="fas <?php echo $is_entreprise ? 'fa-building' : 'fa-user-graduate'; ?> text-blue-300 text-lg"></i>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-black truncate text-white"><?php echo $is_entreprise ? ucwords($_SESSION['company_name'] ?? 'Entreprise') : ucwords($_SESSION['user_prenom'] ?? 'Étudiant'); ?></p>
                <p class="text-[10px] uppercase tracking-wider text-blue-200 opacity-60 font-bold"><?php echo $is_admin ? 'ESPACE ADMINISTRATION' : ($is_entreprise ? 'ESPACE ENTREPRISE' : 'ESPACE ÉTUDIANT'); ?></p>
            </div>
        </div>

        <nav class="p-4 space-y-1 mt-2 overflow-y-auto custom-scrollbar" style="max-height: calc(100vh - 220px);">
            <div class="mb-4">
                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 px-4 mb-2 font-black opacity-50">Navigation</p>
                
                <a href="index.php" class="flex items-center group px-4 py-2.5 rounded-xl <?php echo $current_page === 'index.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200">
                    <div class="w-7 flex justify-center">
                        <i class="fas fa-chart-pie text-base <?php echo $current_page === 'index.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i>
                    </div>
                    <span class="ml-3 font-semibold text-sm">Dashboard</span>
                </a>

                <?php if ($is_admin): ?>
                    <a href="offers.php" class="flex items-center group px-4 py-2.5 rounded-xl <?php echo $current_page === 'offers.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-7 flex justify-center"><i class="fas fa-briefcase text-base <?php echo $current_page === 'offers.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                        <span class="ml-3 font-semibold text-sm">Manage Offers</span>
                    </a>
                    <a href="company_management.php" class="flex items-center group px-4 py-2.5 rounded-xl <?php echo $current_page === 'company_management.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-7 flex justify-center"><i class="fas fa-users-cog text-base <?php echo $current_page === 'company_management.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                        <span class="ml-3 font-semibold text-sm">Company Management</span>
                    </a>
                    <a href="permissions.php" class="flex items-center group px-4 py-2.5 rounded-xl <?php echo $current_page === 'permissions.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-7 flex justify-center"><i class="fas fa-key text-base <?php echo $current_page === 'permissions.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                        <span class="ml-3 font-semibold text-sm">Permissions Management</span>
                    </a>
                    <a href="applications.php" class="flex items-center group px-4 py-2.5 rounded-xl <?php echo $current_page === 'applications.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-7 flex justify-center"><i class="fas fa-file-invoice text-base <?php echo $current_page === 'applications.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                        <span class="ml-3 font-semibold text-sm">Candidatures</span>
                    </a>
                    <a href="my_company.php" class="flex items-center group px-4 py-2.5 rounded-xl <?php echo $current_page === 'my_company.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-7 flex justify-center"><i class="fas fa-building text-base <?php echo $current_page === 'my_company.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                        <span class="ml-3 font-semibold text-sm">My Company</span>
                    </a>
                <?php elseif ($is_entreprise): ?>
                   <a href="<?php echo $offres_link; ?>" class="flex items-center group px-4 py-3 rounded-xl <?php echo $current_page === $offres_link ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-8 flex justify-center">
                            <i class="fas fa-plus-circle text-lg <?php echo $current_page === $offres_link ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i>
                        </div>
                        <span class="ml-3 font-semibold text-sm">Déposer une Offre</span>
                    </a>
                    <a href="<?php echo $apps_link; ?>" class="flex items-center group px-4 py-3 rounded-xl <?php echo $current_page === $apps_link ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-8 flex justify-center relative">
                            <i class="fas fa-users text-lg <?php echo $current_page === $apps_link ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i>
                            <?php if ($notif_count > 0): ?>
                                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full border-2 border-blue-900"></span>
                            <?php endif; ?>
                        </div>
                        <span class="ml-3 font-semibold text-sm">Gérer les Candidats</span>
                    </a>
                <?php else: ?>
                    <a href="offres.php" class="flex items-center group px-4 py-3 rounded-xl <?php echo $current_page === 'offres.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-8 flex justify-center"><i class="fas fa-search text-lg <?php echo $current_page === 'offres.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                        <span class="ml-3 font-semibold text-sm">Rechercher des Offres</span>
                    </a>
                    <a href="create_cv.php" class="flex items-center group px-4 py-3 rounded-xl <?php echo $current_page === 'create_cv.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-8 flex justify-center"><i class="fas fa-file-alt text-lg <?php echo $current_page === 'create_cv.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                        <span class="ml-3 font-semibold text-sm">Créer/Modifier CV</span>
                    </a>
                    <a href="mes_candidatures.php" class="flex items-center group px-4 py-3 rounded-xl <?php echo $current_page === 'mes_candidatures.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-8 flex justify-center relative">
                            <i class="fas fa-paper-plane text-lg <?php echo $current_page === 'mes_candidatures.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i>
                            <?php if ($notif_count > 0): ?>
                                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full border-2 border-blue-900"></span>
                            <?php endif; ?>
                        </div>
                        <span class="ml-3 font-semibold text-sm">Mes Candidatures</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="border-t border-white border-opacity-10 my-4"></div>
            
            <div class="mb-2">
                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 px-4 mb-2 font-black opacity-50">Paramètres</p>
                <?php if (!$is_admin): ?>
                <a href="<?php echo $profile_link; ?>" class="flex items-center group px-4 py-2.5 rounded-xl <?php echo $current_page === $profile_link ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200">
                    <div class="w-7 flex justify-center"><i class="fas fa-user-cog text-base <?php echo $current_page === $profile_link ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                    <span class="ml-3 font-semibold text-sm">Mon Compte</span>
                </a>
                <?php endif; ?>

                <?php if ($is_admin): ?>
                    <a href="contact.php" class="flex items-center group px-4 py-2.5 rounded-xl <?php echo $current_page === 'contact.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-7 flex justify-center"><i class="fas fa-comments text-base <?php echo $current_page === 'contact.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                        <span class="ml-3 font-semibold text-sm">Messages Étudiants</span>
                    </a>
                <?php else: ?>
                    <a href="contact.php" class="flex items-center group px-4 py-2.5 rounded-xl <?php echo $current_page === 'contact.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                        <div class="w-7 flex justify-center"><i class="fas fa-headset text-base <?php echo $current_page === 'contact.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                        <span class="ml-3 font-semibold text-sm">Mesaj / Support</span>
                    </a>
                <?php endif; ?>
                <a href="faq.php" class="flex items-center group px-4 py-2.5 rounded-xl <?php echo $current_page === 'faq.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                    <div class="w-7 flex justify-center"><i class="fas fa-question-circle text-base <?php echo $current_page === 'faq.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                    <span class="ml-3 font-semibold text-sm">FAQ</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Bottom Container (Logout Button) -->
    <div class="p-4 border-t border-white border-opacity-10 bg-black bg-opacity-10">
        <a href="../logout.php" class="flex items-center group px-4 py-3 rounded-xl hover:bg-red-500 hover:bg-opacity-20 text-red-300 transition-all duration-200 border border-transparent hover:border-red-500/30">
            <div class="w-8 flex justify-center">
                <i class="fas fa-sign-out-alt text-lg group-hover:scale-110 transition-transform"></i>
            </div>
            <span class="ml-3 font-bold text-sm">Déconnexion</span>
        </a>
    </div>
</div>
