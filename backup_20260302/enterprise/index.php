<?php 
require_once '../include/session.php';
check_auth('entreprise');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Entreprise - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/enterprise_dashboard.js?v=<?php echo time(); ?>" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto bg-gray-50 md:ml-64">
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30 px-6">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-blue-600">StageMatch</span>
                </div>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>

            <!-- Welcome Banner -->
            <div class="max-w-5xl mx-auto px-6 pt-10 pb-6">
                <div class="bg-gradient-to-br from-indigo-700 to-purple-900 rounded-[2rem] p-8 md:p-12 text-white shadow-2xl relative overflow-hidden">
                    <!-- Decorative shapes -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-20 -mt-20"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-white opacity-5 rounded-full -ml-16 -mb-16"></div>
                    
                    <div class="relative z-10">
                        <span class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-xs font-black uppercase tracking-widest mb-6">Espace Recrutement</span>
                        <h1 class="text-4xl md:text-5xl font-black mb-4 leading-tight">
                            Bonjour, <br>
                            <span class="text-purple-200"><?php echo $_SESSION['user_nom'] ?? 'Entreprise'; ?></span> !
                        </h1>
                        <p class="text-purple-100/80 text-lg max-w-lg font-medium leading-relaxed">
                            Trouvez les meilleurs talents pour vos projets de stage et contribuez à la formation de demain.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats & Quick Actions -->
            <div class="max-w-5xl mx-auto px-6 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <!-- Stat Card 1 -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-6 group hover:border-blue-500 transition-all duration-300">
                        <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <i class="fas fa-briefcase text-xl"></i>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-gray-900" id="stat-offres">0</p>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Offres actives</p>
                        </div>
                    </div>
                    <!-- Stat Card 2 -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-6 group hover:border-purple-500 transition-all duration-300">
                        <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-all">
                            <i class="fas fa-user-tie text-xl"></i>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-gray-900" id="stat-candidats">0</p>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Candidatures</p>
                        </div>
                    </div>
                    <!-- Stat Card 3 -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-6 group hover:border-green-500 transition-all duration-300">
                        <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center text-green-600 group-hover:bg-green-600 group-hover:text-white transition-all">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-gray-900" id="stat-recrutements">0</p>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Recrutements</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-2xl font-black text-gray-900">Actions Rapides</h2>
                        <p class="text-gray-500 font-medium">Gérez votre processus de recrutement efficacement.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
                    <!-- Tool 1 -->
                    <a href="offres.php" class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl hover:border-blue-100 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-6 group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus-circle text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Publier une Offre</h3>
                        <p class="text-gray-500 text-sm leading-relaxed mb-6">Créez une nouvelle opportunité de stage pour attirer les candidats.</p>
                        <span class="text-blue-600 font-black text-sm uppercase tracking-wider flex items-center gap-2">
                            Commencer <i class="fas fa-arrow-right text-xs"></i>
                        </span>
                    </a>
                    <!-- Tool 2 -->
                    <a href="gerer_candidatures.php" class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl hover:border-purple-100 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 mb-6 group-hover:scale-110 transition-transform">
                            <i class="fas fa-users-cog text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Suivi des Candidats</h3>
                        <p class="text-gray-500 text-sm leading-relaxed mb-6">Examinez les profils, CV et gérez les étapes de sélection.</p>
                        <span class="text-purple-600 font-black text-sm uppercase tracking-wider flex items-center gap-2">
                            Gérer <i class="fas fa-arrow-right text-xs"></i>
                        </span>
                    </a>
                </div>

                <!-- Recent Activities Section -->
                <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-gray-100 shadow-sm">
                    <h3 class="text-2xl font-black text-gray-900 mb-8">Candidatures Récentes</h3>
                    <div id="recent-candidatures" class="space-y-6">
                        <!-- Dynamic content will be loaded here -->
                        <div class="text-center py-10">
                            <p class="text-gray-400 italic">Aucune candidature récente à afficher.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <footer class="bg-white text-gray-400 py-10 px-6 border-t border-gray-100 mt-20">
                <div class="max-w-5xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-2 font-black text-gray-400 opacity-50">
                        <span>STAGEMATCH</span>
                        <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
                        <span>2025</span>
                    </div>
                    <div class="flex space-x-10 text-xs font-black uppercase tracking-widest">
                        <a href="#" class="hover:text-blue-600 transition-colors">Aide Entreprise</a>
                        <a href="#" class="hover:text-blue-600 transition-colors">Tarifs</a>
                        <a href="#" class="hover:text-blue-600 transition-colors">Confidentialité</a>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</body>
</html>




