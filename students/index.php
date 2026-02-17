<?php 
require_once '../include/session.php';
check_auth('etudiant');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Étudiant - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/dashboards.js" defer></script>
</head>
<body class="bg-gray-50">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto bg-gray-50 md:ml-64">
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-blue-600">StageMatch</span>
                </div>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>

            <!-- Welcome Banner -->
            <div class="max-w-5xl mx-auto px-6 pt-10 pb-6">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-900 rounded-[2rem] p-8 md:p-12 text-white shadow-2xl relative overflow-hidden">
                    <!-- Decorative shapes -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-20 -mt-20"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-white opacity-5 rounded-full -ml-16 -mb-16"></div>
                    
                    <div class="relative z-10">
                        <span class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-xs font-black uppercase tracking-widest mb-6">Tableau de bord</span>
                        <h1 class="text-4xl md:text-5xl font-black mb-4 leading-tight">
                            Ravi de vous revoir, <br>
                            <span class="text-blue-200"><?php echo $_SESSION['user_prenom'] ?? 'Étudiant'; ?></span> !
                        </h1>
                        <p class="text-blue-100/80 text-lg max-w-lg font-medium leading-relaxed">
                            Prêt à franchir une nouvelle étape ? Découvrez les opportunités qui correspondent à vos aspirations.
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
                            <i class="fas fa-paper-plane text-xl"></i>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-gray-900" id="stat-candidatures">0</p>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Candidatures</p>
                        </div>
                    </div>
                    <!-- Stat Card 2 -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-6 group hover:border-purple-500 transition-all duration-300">
                        <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-all">
                            <i class="fas fa-heart text-xl"></i>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-gray-900" id="stat-favorites">0</p>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Favoris</p>
                        </div>
                    </div>
                    <!-- Stat Card 3 -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-6 group hover:border-indigo-500 transition-all duration-300">
                        <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <i class="fas fa-comment-dots text-xl"></i>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-gray-900" id="stat-messages">0</p>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Messages</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-2xl font-black text-gray-900">Vos outils de succès</h2>
                        <p class="text-gray-500 font-medium">Tout ce qu'il vous faut pour décrocher votre stage.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    <!-- Tool 1 -->
                    <a href="create_cv.php" class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl hover:border-blue-100 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-6 group-hover:scale-110 transition-transform">
                            <i class="fas fa-file-signature text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Créateur de CV</h3>
                        <p class="text-gray-500 text-sm leading-relaxed mb-6">Générez un CV professionnel impeccablement formaté en quelques clics.</p>
                        <span class="text-blue-600 font-black text-sm uppercase tracking-wider flex items-center gap-2">
                            Essayer <i class="fas fa-arrow-right text-xs"></i>
                        </span>
                    </a>
                    <!-- Tool 2 -->
                    <a href="offres.php" class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl hover:border-purple-100 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 mb-6 group-hover:scale-110 transition-transform">
                            <i class="fas fa-search-location text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Trouver des Offres</h3>
                        <p class="text-gray-500 text-sm leading-relaxed mb-6">Explorez des opportunités qualifiées correspondant à vos compétences.</p>
                        <span class="text-purple-600 font-black text-sm uppercase tracking-wider flex items-center gap-2">
                            Explorer <i class="fas fa-arrow-right text-xs"></i>
                        </span>
                    </a>
                    <!-- Tool 3 -->
                    <a href="mes_candidatures.php" class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl hover:border-indigo-100 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform">
                            <i class="fas fa-tasks text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Suivi Candidatures</h3>
                        <p class="text-gray-500 text-sm leading-relaxed mb-6">Gardez un œil sur l'avancement de vos demandes en temps réel.</p>
                        <span class="text-indigo-600 font-black text-sm uppercase tracking-wider flex items-center gap-2">
                            Consulter <i class="fas fa-arrow-right text-xs"></i>
                        </span>
                    </a>
                </div>

                <!-- Recommended Section Card -->
                <div class="bg-white rounded-[2rem] p-8 md:p-12 border border-gray-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-48 h-48 bg-purple-50 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative z-10 flex flex-col md:flex-row items-center gap-10">
                        <div class="flex-1">
                            <h3 class="text-2xl font-black text-gray-900 mb-4">Offres Recommandées</h3>
                            <p class="text-gray-600 font-medium text-lg leading-relaxed mb-6">
                                Notre algorithme prépare des recommandations personnalisées pour vous. 
                                <span class="text-blue-600 font-bold italic">Complétez votre CV</span> pour plus de précision.
                            </p>
                            <div class="flex items-center gap-4">
                                <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-xl font-black text-xs uppercase">Bientôt disponible</span>
                                <span class="text-gray-400 text-sm flex items-center gap-2"><i class="fas fa-clock"></i> Analyse en cours...</span>
                            </div>
                        </div>
                        <div class="w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-3xl flex items-center justify-center text-indigo-500 text-5xl">
                            <i class="fas fa-magic"></i>
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
                        <a href="faq.php" class="hover:text-blue-600 transition-colors">Aide</a>
                        <a href="#" class="hover:text-blue-600 transition-colors">Conditions</a>
                        <a href="#" class="hover:text-blue-600 transition-colors">Confidentialité</a>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</body>
</html>
