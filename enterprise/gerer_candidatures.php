<?php 
require_once '../include/session.php';
check_auth('entreprise');
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
    <link rel="stylesheet" href="../css/enterprise_candidatures.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/enterprise_candidatures.js" defer></script>
</head>
<body class="bg-gray-50">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto md:ml-64">
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-blue-600">StageMatch</span>
                </div>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>

            <div class="max-w-6xl mx-auto px-6 py-10">
                <div class="mb-10">
                    <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Gestion des Candidatures</h1>
                    <p class="text-gray-600">Analysez les profils des étudiants et gérez votre vivier de talents.</p>
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
                    <!-- Custom Dropdown Offre -->
                    <div class="relative custom-dropdown" id="dropdownOffre">
                        <button class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-white text-left flex justify-between items-center hover:border-blue-500 transition-all shadow-sm">
                            <span class="truncate text-gray-700 font-medium">Toutes les offres</span>
                            <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                        </button>
                        <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300" id="menuOffre">
                            <div class="dropdown-item" data-value="">Toutes les offres</div>
                            <!-- Dynamic items -->
                        </div>
                    </div>

                    <!-- Custom Dropdown Statut -->
                    <div class="relative custom-dropdown" id="dropdownStatut">
                        <button class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-white text-left flex justify-between items-center hover:border-blue-500 transition-all shadow-sm">
                            <span class="truncate text-gray-700 font-medium">Tous les statuts</span>
                            <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                        </button>
                        <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                            <div class="dropdown-item" data-value="">Tous les statuts</div>
                            <div class="dropdown-item" data-value="en_attente">En attente</div>
                            <div class="dropdown-item" data-value="vue">Consultée</div>
                            <div class="dropdown-item" data-value="accepte">Acceptée</div>
                            <div class="dropdown-item" data-value="refuse">Refusée</div>
                        </div>
                    </div>
                </div>


                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-4">
                        <thead>
                            <tr class="text-gray-400 text-xs font-black uppercase tracking-[0.2em] px-6">
                                <th class="py-4 px-6">Étudiant</th>
                                <th class="py-4 px-6">Offre</th>
                                <th class="py-4 px-6">Date</th>
                                <th class="py-4 px-6">Statut</th>
                                <th class="py-4 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="candidaturesTableBody">
                            <!-- Dynamic content -->
                            <tr class="bg-white">
                                <td colspan="5" class="py-20 text-center rounded-[2rem]">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent mb-4"></div>
                                    <p class="text-gray-500">Chargement des candidatures...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Détails Candidat -->
    <div id="modalCandidat" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-[2.5rem] w-full max-w-4xl max-h-[90vh] overflow-y-auto m-4 shadow-2xl">
            <div id="candidatDetailsContent">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>
</body>
</html>
