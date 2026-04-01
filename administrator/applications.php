<?php 
require_once '../include/session.php';
check_auth('entreprise', 'Administrator');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Candidatures - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/admin_company_applications.js?v=<?php echo time(); ?>" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto bg-gray-50 md:ml-64">
            <!-- Mobile -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <span class="font-bold text-blue-600">StageMatch</span>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>

            <div class="max-w-5xl mx-auto px-6 py-10">
                <div class="mb-10 flex items-start justify-between">
                    <div>
                        <h1 class="text-4xl font-extrabold text-gray-900 mb-1">Candidatures</h1>
                        <p class="text-gray-500 font-medium">Examinez les profils et gérez les processus de recrutement.</p>
                    </div>
                    <a href="my_company.php" class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> <span class="hidden sm:inline">Retour à Mon Entreprise</span>
                    </a>
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="md:col-span-2 relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="searchApp" placeholder="Rechercher un candidat ou une offre..." class="w-full pl-12 pr-4 py-4 rounded-2xl border border-gray-100 bg-white focus:border-blue-500 outline-none shadow-sm">
                    </div>
                    <div class="relative custom-dropdown" id="dropdownStatutApp">
                        <button class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-white text-left flex justify-between items-center hover:border-blue-500 transition-all shadow-sm">
                            <span class="truncate text-gray-700 font-medium">Statut</span>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>
                        <input type="hidden" name="filter_status" value="">
                        <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                            <div class="dropdown-item" data-value="">Tout</div>
                            <div class="dropdown-item" data-value="pending">En attente</div>
                            <div class="dropdown-item" data-value="accepted">Accepté</div>
                            <div class="dropdown-item" data-value="rejected">Refusé</div>
                            <div class="dropdown-item" data-value="stage">Stage</div>
                            <div class="dropdown-item" data-value="alternance">Alternance</div>
                        </div>
                    </div>

                </div>

                <div class="grid grid-cols-1 gap-6" id="applicationsGrid">
                    <!-- Dynamic Loading -->
                    <div class="col-span-full py-20 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent mb-4"></div>
                        <p class="text-gray-500 font-medium">Chargement des candidatures...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Détails Candidat -->
    <div id="modalApp" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-[2.5rem] w-full max-w-2xl max-h-[90vh] overflow-y-auto m-4 shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="p-8 md:p-12">
                <div class="flex justify-between items-start mb-10">
                    <div class="flex items-center gap-6">
                        <div id="modalUserInitial" class="w-20 h-20 bg-blue-600 text-white rounded-3xl flex items-center justify-center text-3xl font-black shadow-xl shadow-blue-100">
                            JS
                        </div>
                        <div>
                            <h2 id="modalUserName" class="text-3xl font-black text-gray-900 mb-1">Jane Smith</h2>
                            <p id="modalUserEmail" class="text-gray-500 font-medium">jane.smith@example.com</p>
                        </div>
                    </div>
                    <button class="close-modal w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 transition-all"><i class="fas fa-times"></i></button>
                </div>

                <div class="space-y-8">
                    <div>
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Offre Concernée</h3>
                        <p id="modalOffreTitre" class="text-xl font-bold text-blue-600"></p>
                    </div>

                    <div>
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Informations du Candidat</h3>
                        <div class="grid grid-cols-2 gap-6 bg-gray-50/50 p-6 rounded-3xl border border-gray-100">
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider mb-1">Spécialité / Option</p>
                                <p id="modalUserSpecialite" class="text-sm font-bold text-gray-900">-</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider mb-1">Université / École</p>
                                <p id="modalUserUniversity" class="text-sm font-bold text-gray-900">-</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider mb-1">Domaine</p>
                                <p id="modalUserDomaine" class="text-sm font-bold text-gray-900">-</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider mb-1">Niveau d'études</p>
                                <p id="modalUserNiveau" class="text-sm font-bold text-gray-900">-</p>
                            </div>
                            <div class="col-span-2 pt-4 border-t border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider mb-2">Compétences du Candidat</p>
                                <div class="bg-gray-100/50 p-4 rounded-2xl">
                                    <p id="modalUserSkills" class="text-sm font-bold text-gray-900 leading-relaxed">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="modalMotivationContainer">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Message de Motivation</h3>
                        <p id="modalMotivation" class="text-gray-700 leading-relaxed bg-gray-50 p-6 rounded-2xl border border-gray-100 italic"></p>
                    </div>

                    <!-- Additional Questions Section -->
                    <div id="modalQuestionsContainer" class="hidden">
                        <h3 class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                            <i class="fas fa-question-circle"></i> RÉPONSES AUX QUESTIONS
                        </h3>
                        <div id="modalQuestionsList" class="space-y-4"></div>
                    </div>

                    <div class="flex items-center gap-4">
                        <a id="btnViewProfile" target="_blank" href="#" class="flex-1 py-4 bg-gray-900 text-white rounded-2xl font-black text-center hover:bg-black transition-all">
                            Voir le Profil
                        </a>
                        <a id="btnViewCV" target="_blank" href="#" class="flex-1 py-4 bg-white border border-gray-100 text-gray-900 rounded-2xl font-black text-center hover:border-blue-600 hover:text-blue-600 transition-all">
                            Voir le CV
                        </a>
                    </div>

                    <div class="pt-8 border-t border-gray-50 flex flex-wrap gap-4">
                        <button id="btnAcceptApp" class="flex-1 py-4 bg-green-500 text-white rounded-2xl font-black hover:bg-green-600 transition-all shadow-lg shadow-green-100">
                            Accepter
                        </button>
                        <button id="btnRejectApp" class="flex-1 py-4 bg-rose-500 text-white rounded-2xl font-black hover:bg-rose-600 transition-all shadow-lg shadow-rose-100">
                            Refuser
                        </button>
                        <button id="btnBlockStudent" class="px-6 py-4 bg-gray-100 text-gray-700 rounded-2xl font-black hover:bg-gray-800 hover:text-white transition-all">
                            Bloquer l'étudiant
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
