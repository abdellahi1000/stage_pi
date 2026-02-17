<?php
require_once __DIR__ . '/db_connect.php';
$is_entreprise = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'entreprise';
$current_page = basename($_SERVER['PHP_SELF']);

// Notification system
$notif_count = 0;
if (isset($_SESSION['user_id'])) {
    $db_notif = (new Database())->getConnection();
    if ($is_entreprise) {
        // Count new candidatures (en_attente) for company
        $stmt_notif = $db_notif->prepare("SELECT COUNT(*) FROM candidatures c INNER JOIN offres_stage o ON c.offre_id = o.id WHERE o.user_id = ? AND c.statut = 'en_attente'");
        $stmt_notif->execute([$_SESSION['user_id']]);
        $notif_count = $stmt_notif->fetchColumn();
    } else {
        // Count unread responses for student (statut changed from en_attente)
        $stmt_notif = $db_notif->prepare("SELECT COUNT(*) FROM candidatures WHERE user_id = ? AND vu_par_etudiant = 0 AND statut != 'en_attente'");
        $stmt_notif->execute([$_SESSION['user_id']]);
        $notif_count = $stmt_notif->fetchColumn();
    }
}
?>
<div id="sidebar" class="sidebar fixed left-0 top-0 h-screen w-64 bg-gradient-to-b from-blue-900 to-purple-900 text-white shadow-2xl z-40 transition-transform duration-300">
    <div class="flex items-center px-6 py-8 border-b border-white border-opacity-10">
        <h1 class="text-2xl font-black tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-blue-200">StageMatch</h1>
    </div>
    
    <div class="flex items-center px-6 py-5 border-b border-white border-opacity-10">
        <div class="w-10 h-10 bg-white bg-opacity-10 rounded-xl flex items-center justify-center mr-3 shadow-inner">
            <i class="fas <?php echo $is_entreprise ? 'fa-building' : 'fa-user-graduate'; ?> text-blue-300 text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold truncate"><?php echo $is_entreprise ? ($_SESSION['user_nom'] ?? 'Entreprise') : ($_SESSION['user_prenom'] ?? 'Étudiant'); ?></p>
            <p class="text-[10px] uppercase tracking-wider text-blue-200 opacity-60 font-bold"><?php echo $is_entreprise ? 'Espace Entreprise' : 'Espace Étudiant'; ?></p>
        </div>
    </div>

    <nav class="p-4 space-y-2 mt-2">
        <div class="mb-6">
            <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 px-4 mb-3 font-black opacity-50">Navigation</p>
            <a href="index.php" class="flex items-center group px-4 py-3 rounded-xl <?php echo $current_page === 'index.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200">
                <div class="w-8 flex justify-center">
                    <i class="fas fa-home text-lg <?php echo $current_page === 'index.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i>
                </div>
                <span class="ml-3 font-semibold text-sm">Accueil</span>
            </a>

            
            <?php if ($is_entreprise): ?>
                <a href="offres.php" class="flex items-center group px-4 py-3 rounded-xl <?php echo $current_page === 'offres.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                    <div class="w-8 flex justify-center">
                        <i class="fas fa-plus-circle text-lg <?php echo $current_page === 'offres.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i>
                    </div>
                    <span class="ml-3 font-semibold text-sm">Déposer une Offre</span>
                </a>
                <a href="gerer_candidatures.php" class="flex items-center group px-4 py-3 rounded-xl <?php echo $current_page === 'gerer_candidatures.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                    <div class="w-8 flex justify-center relative">
                        <i class="fas fa-users text-lg <?php echo $current_page === 'gerer_candidatures.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i>
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
        
        <div class="border-t border-white border-opacity-10 my-6"></div>
        
        <div>
            <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 px-4 mb-3 font-black opacity-50">Paramètres</p>
            <a href="compte.php" class="flex items-center group px-4 py-3 rounded-xl <?php echo $current_page === 'compte.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200">
                <div class="w-8 flex justify-center"><i class="fas fa-user-circle text-lg <?php echo $current_page === 'compte.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                <span class="ml-3 font-semibold text-sm">Mon Compte</span>
            </a>
            <a href="faq.php" class="flex items-center group px-4 py-3 rounded-xl <?php echo $current_page === 'faq.php' ? 'bg-gradient-to-r from-purple-600 to-purple-700 shadow-lg shadow-purple-900/20' : 'hover:bg-white hover:bg-opacity-10'; ?> transition-all duration-200 mt-1">
                <div class="w-8 flex justify-center"><i class="fas fa-question-circle text-lg <?php echo $current_page === 'faq.php' ? 'text-white' : 'text-blue-300 group-hover:text-white'; ?> transition-colors"></i></div>
                <span class="ml-3 font-semibold text-sm">Aide & FAQ</span>
            </a>
        </div>
        
        <div class="mt-8 pt-4 border-t border-white border-opacity-10">
            <a href="../logout.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-red-500 hover:bg-opacity-20 text-red-300 transition-all duration-200 font-medium">
                <i class="fas fa-sign-out-alt text-lg"></i><span>Déconnexion</span>
            </a>
        </div>
    </nav>
</div>
