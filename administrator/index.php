<?php 
require_once '../include/session.php';
// Check for manager role specifically
check_auth('entreprise', 'Administrator');


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrateur - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/admin_company_dashboard.js?v=<?php echo time(); ?>" defer></script>
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
                <div class="bg-gradient-to-br from-blue-700 to-indigo-900 rounded-[2rem] p-8 md:p-12 text-white shadow-2xl relative overflow-hidden">
                    <!-- Decorative shapes -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-20 -mt-20"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-white opacity-5 rounded-full -ml-16 -mb-16"></div>
                    
                    <div class="relative z-10">
                        <span class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-xs font-black uppercase tracking-widest mb-6">Administrateur Société</span>
                        <h1 class="text-4xl md:text-5xl font-black mb-4 leading-tight">
                            Tableau de Bord, <br>
                            <span class="text-blue-200"><?php echo $_SESSION['user_nom'] ?? 'Admin'; ?></span>
                        </h1>
                        <p class="text-blue-100/80 text-lg max-w-lg font-medium leading-relaxed">
                            Gérez les offres de stage et les candidatures de votre entreprise en toute simplicité.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="max-w-5xl mx-auto px-6 py-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                    <!-- Stat Card: Offers -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col gap-2 group hover:border-blue-500 transition-all duration-300">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-black text-gray-900" id="stat-total-offers">0</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Offres Créées</p>
                        </div>
                    </div>
                    <!-- Stat Card: Applications -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col gap-2 group hover:border-purple-500 transition-all duration-300">
                        <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-all">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-black text-gray-900" id="stat-total-apps">0</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Candidatures</p>
                        </div>
                    </div>
                    <!-- Stat Card: Accepted -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col gap-2 group hover:border-green-500 transition-all duration-300">
                        <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center text-green-600 group-hover:bg-green-600 group-hover:text-white transition-all">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-black text-gray-900" id="stat-accepted">0</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Étudiants Acceptés</p>
                        </div>
                    </div>
                    <!-- Stat Card: Rejected -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col gap-2 group hover:border-red-500 transition-all duration-300">
                        <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center text-red-600 group-hover:bg-red-600 group-hover:text-white transition-all">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-black text-gray-900" id="stat-rejected">0</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Étudiants Refusés</p>
                        </div>
                    </div>
                    <!-- Stat Card: Blocked -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col gap-2 group hover:border-gray-800 transition-all duration-300">
                        <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center text-gray-600 group-hover:bg-gray-800 group-hover:text-white transition-all">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-black text-gray-900" id="stat-blocked">0</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Étudiants Bloqués</p>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Activity Chart Section -->
                <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm mb-12">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900">Activité du Système</h3>
                            <p class="text-gray-500 font-medium">Suivi en temps réel des candidatures et acceptations.</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                                <span class="text-xs font-bold text-gray-400 uppercase">Candidatures</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                <span class="text-xs font-bold text-gray-400 uppercase">Acceptations</span>
                            </div>
                        </div>
                    </div>
                    <div class="relative h-[400px]">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>

                <!-- Recent Applications Section -->
                <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-2xl font-black text-gray-900">Candidatures Récentes</h3>
                        <a href="applications.php" class="text-blue-600 font-black text-xs uppercase tracking-widest hover:underline">Voir Tout</a>
                    </div>
                    <div id="recent-applications" class="space-y-6">
                        <!-- Dynamic content will be loaded here -->
                        <div class="text-center py-10">
                            <div class="animate-pulse flex flex-col items-center">
                                <div class="w-12 h-12 bg-gray-200 rounded-full mb-4"></div>
                                <div class="h-4 bg-gray-200 rounded w-48 mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded w-32"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <footer class="bg-white text-gray-400 py-10 px-6 border-t border-gray-100 mt-20">
                <div class="max-w-5xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-2 font-black text-gray-400 opacity-50">
                        <span>STAGEMATCH ADMIN</span>
                        <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
                        <span>2026</span>
                    </div>
                    <div class="flex space-x-10 text-xs font-black uppercase tracking-widest">
                        <a href="#" class="hover:text-blue-600 transition-colors">Documentation</a>
                        <a href="#" class="hover:text-blue-600 transition-colors">Support</a>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</body>
</html>
