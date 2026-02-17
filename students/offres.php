<?php 
require_once '../include/session.php';
check_auth('etudiant');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offres de Stage - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <link rel="stylesheet" href="../css/offres.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/offres.js" defer></script>
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

            <div class="max-w-5xl mx-auto px-6 py-10">
                <div class="mb-10">
                    <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Offres de Stage</h1>
                    <p class="text-gray-600">Explorez les opportunités qui correspondent à votre profil.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
                    <div class="lg:col-span-2 relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="searchOffre" placeholder="Titre, entreprise, mots-clés..." class="w-full pl-12 pr-4 py-4 rounded-2xl border border-gray-200 focus:border-blue-500 outline-none transition-all">
                    </div>
                    
                    <!-- Custom Dropdown Localisation -->
                    <div class="relative custom-dropdown" id="dropdownLocalisation">
                        <button class="w-full px-5 py-4 rounded-2xl border border-gray-200 bg-white text-left flex justify-between items-center hover:border-blue-500 transition-all">
                            <span class="truncate text-gray-700 font-medium">Localisation</span>
                            <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                        </button>
                        <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                            <div class="dropdown-item" data-value="">Tout</div>
                            <div class="dropdown-item" data-value="Nouakchott">Nouakchott</div>
                            <div class="dropdown-item" data-value="Nouadhibou">Nouadhibou</div>
                        </div>
                    </div>

                    <!-- Custom Dropdown Type -->
                    <div class="relative custom-dropdown" id="dropdownType">
                        <button class="w-full px-5 py-4 rounded-2xl border border-gray-200 bg-white text-left flex justify-between items-center hover:border-blue-500 transition-all">
                            <span class="truncate text-gray-700 font-medium">Type de contrat</span>
                            <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                        </button>
                        <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                            <div class="dropdown-item" data-value="">Tout</div>
                            <div class="dropdown-item" data-value="Stage">Stage</div>
                            <div class="dropdown-item" data-value="Alternance">Alternance</div>
                        </div>
                    </div>
                </div>

                <div class="offres-grid grid grid-cols-1 md:grid-cols-2 gap-6" id="offresGrid">
                    <div class="col-span-full py-20 text-center">
                        <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-blue-600 border-t-transparent mb-4"></div>
                        <p class="text-gray-500 font-medium">Chargement des meilleures offres pour vous...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- Modal Postuler -->
    <div id="modalPostuler" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-[2.5rem] w-full max-w-lg p-10 shadow-2xl transform scale-95 transition-all duration-300">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black text-gray-900">Postuler</h2>
                <button id="closePostuler" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form id="formPostuler" class="space-y-6">
                <input type="hidden" name="offre_id" id="postulerOffreId">
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Message de motivation</label>
                    <textarea name="message_motivation" required rows="5" placeholder="Pourquoi souhaitez-vous rejoindre cette entreprise ?" class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all resize-none"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Télécharger mon CV</label>
                    <div class="relative">
                        <input type="file" name="cv_specifique" id="cvSpecifique" accept=".pdf,.doc,.docx" class="hidden" onchange="document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : 'Aucun fichier choisi'">
                        <label for="cvSpecifique" class="w-full flex items-center justify-between px-6 py-4 rounded-2xl border border-dashed border-gray-300 bg-gray-50 cursor-pointer hover:bg-white hover:border-blue-500 transition-all">
                            <span id="fileName" class="text-gray-500 font-medium truncate">Choisir un fichier (PDF, DOC, DOCX)</span>
                            <i class="fas fa-cloud-upload-alt text-blue-500 text-xl"></i>
                        </label>
                    </div>
                </div>
                <button type="submit" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">Envoyer ma candidature</button>
            </form>
        </div>
    </div>
</body>
</html>


