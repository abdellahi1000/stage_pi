<?php 
require_once '../include/session.php';
require_once '../include/db_connect.php';
check_auth('etudiant');

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT cv_path FROM profils WHERE user_id = :uid");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$cv_path = $stmt->fetchColumn();
$has_cv = !empty($cv_path);
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
    <script src="../js/global.js?v=<?= time() ?>" defer></script>
    <script src="../js/offres.js?v=<?= time() ?>" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto md:ml-64">
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30 px-6">
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
                            <div class="dropdown-item" data-value="Rosso">Rosso</div>
                            <div class="dropdown-item" data-value="Atar">Atar</div>
                            <div class="dropdown-item" data-value="Kaédi">Kaédi</div>
                            <div class="dropdown-item" data-value="Zouérat">Zouérat</div>
                            <div class="dropdown-item" data-value="Kiffa">Kiffa</div>
                            <div class="dropdown-item" data-value="Autre">Autre</div>
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
    <div id="modalPostuler" class="fixed inset-0 bg-black/60 backdrop-blur-md z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-[2.5rem] w-full max-w-lg p-10 shadow-2xl transform scale-95 transition-all duration-300 max-h-[90vh] overflow-y-auto custom-scrollbar">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                    <i class="fas fa-paper-plane text-blue-600"></i> Postuler
                </h2>
                <button id="closePostuler" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="formPostuler" class="space-y-8">
                <input type="hidden" name="offre_id" id="postulerOffreId">
                <input type="hidden" name="document_id" id="postulerDocumentId">
                
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-comment-dots"></i> Message de motivation
                    </label>
                    <textarea name="message_motivation" rows="3" placeholder="Pourquoi souhaitez-vous rejoindre cette entreprise ?" class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50/50 focus:bg-white focus:border-blue-500 outline-none transition-all resize-none text-sm font-medium" required></textarea>
                </div>

                <div class="space-y-6">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b pb-3 block w-full border-gray-100 flex items-center justify-between">
                        <span><i class="fas fa-file-pdf"></i> Curriculum Vitæ (Obligatoire)</span>
                        <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-[9px]">DOCUMENTS DU PROFIL</span>
                    </label>
                    
                    <!-- Existing CVs from Profile -->
                    <div id="application_cv_selector" class="space-y-3">
                        <div class="py-10 text-center border-2 border-dashed border-gray-100 rounded-3xl bg-gray-50/30">
                            <i class="fas fa-spinner fa-spin text-gray-200 text-xl"></i>
                        </div>
                    </div>

                    <div class="relative py-2">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-gray-100"></div>
                        </div>
                        <div class="relative flex justify-center text-[10px] uppercase font-black text-gray-300">
                            <span class="bg-white px-4">Ou</span>
                        </div>
                    </div>

                    <!-- New Upload Option -->
                    <div class="p-6 bg-blue-50/50 rounded-[2rem] border border-blue-100/50 group transition-all">
                        <label class="flex items-center gap-4 cursor-pointer">
                            <input type="radio" name="cv_option" value="new" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500 checked:bg-blue-600 transition-all">
                            <div class="flex-1">
                                <p class="text-sm font-black text-blue-900">Importer un nouveau CV externe</p>
                                <p class="text-[9px] text-blue-400 font-black uppercase tracking-widest mt-0.5">Fichier unique pour cette offre</p>
                            </div>
                        </label>
                        
                        <div id="new_cv_upload_container" class="mt-6 hidden animate-in fade-in slide-in-from-top-2 duration-300">
                            <input type="file" name="cv_file" id="cv_file_input" accept=".pdf,.doc,.docx" class="hidden">
                            <button type="button" onclick="document.getElementById('cv_file_input').click()" class="w-full flex flex-col items-center justify-center gap-2 py-6 border-2 border-dashed border-blue-200 rounded-2xl bg-white text-blue-300 hover:text-blue-600 hover:border-blue-600 transition-all group/btn">
                                <i class="fas fa-cloud-upload-alt text-2xl group-hover/btn:scale-110 transition-transform"></i>
                                <span id="cv_file_name_display" class="text-[10px] font-black uppercase tracking-widest">Choisir un fichier</span>
                            </button>
                            
                            <label class="flex items-center gap-3 cursor-pointer mt-4 group/box">
                                <div class="relative">
                                    <input type="checkbox" name="save_recent_cv" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition-all">
                                </div>
                                <span class="text-[10px] font-black text-gray-400 group-hover/box:text-blue-600 uppercase tracking-widest transition-colors">Ajouter ce CV à mon dossier profil</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- NEW Lettre de Motivation Section -->
                <div class="space-y-6 pt-6 border-t border-gray-50">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b pb-3 block w-full border-gray-100 flex items-center justify-between">
                        <span><i class="fas fa-file-contract"></i> Lettre de motivation (Facultatif)</span>
                        <span class="bg-purple-50 text-purple-600 px-3 py-1 rounded-full text-[9px]">DOCUMENTS DU PROFIL</span>
                    </label>
                    
                    <div id="application_lm_selector" class="space-y-3">
                        <div class="py-10 text-center border-2 border-dashed border-gray-100 rounded-3xl bg-gray-50/30">
                            <i class="fas fa-spinner fa-spin text-gray-200 text-xl"></i>
                        </div>
                    </div>

                    <div class="relative py-2">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-gray-100"></div>
                        </div>
                        <div class="relative flex justify-center text-[10px] uppercase font-black text-gray-300">
                            <span class="bg-white px-4">Ou</span>
                        </div>
                    </div>

                    <div class="p-6 bg-purple-50/50 rounded-[2rem] border border-purple-100/50 group transition-all">
                        <label class="flex items-center gap-4 cursor-pointer">
                            <input type="radio" name="lm_option" value="new" class="w-5 h-5 text-purple-600 border-gray-300 focus:ring-purple-500 checked:bg-purple-600 transition-all">
                            <div class="flex-1">
                                <p class="text-sm font-black text-purple-900">Importer une nouvelle lettre</p>
                                <p class="text-[9px] text-purple-400 font-black uppercase tracking-widest mt-0.5">Fichier unique pour cette offre</p>
                            </div>
                        </label>
                        
                        <div id="new_lm_upload_container" class="mt-6 hidden animate-in fade-in slide-in-from-top-2 duration-300">
                            <input type="file" name="lm_file" id="lm_file_input" accept=".pdf,.doc,.docx" class="hidden">
                            <button type="button" onclick="document.getElementById('lm_file_input').click()" class="w-full flex flex-col items-center justify-center gap-2 py-6 border-2 border-dashed border-purple-200 rounded-2xl bg-white text-purple-300 hover:text-purple-600 hover:border-purple-600 transition-all group/btn">
                                <i class="fas fa-cloud-upload-alt text-2xl group-hover/btn:scale-110 transition-transform"></i>
                                <span id="lm_file_name_display" class="text-[10px] font-black uppercase tracking-widest">Choisir un fichier</span>
                            </button>
                            
                            <label class="flex items-center gap-3 cursor-pointer mt-4 group/box">
                                <div class="relative">
                                    <input type="checkbox" name="save_recent_lm" value="1" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 transition-all">
                                </div>
                                <span class="text-[10px] font-black text-gray-400 group-hover/box:text-purple-600 uppercase tracking-widest transition-colors">Ajouter à mon dossier profil</span>
                            </label>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="lm_id" id="postulerLmId">

                <div class="pt-4">
                    <button type="submit" id="btnSubmitApplication" class="w-full py-5 bg-blue-600 text-white rounded-[1.5rem] font-black text-sm uppercase tracking-[0.2em] shadow-2xl shadow-blue-500/20 hover:bg-blue-700 hover:-translate-y-1 active:scale-95 transition-all duration-300 disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none disabled:translate-y-0 disabled:cursor-not-allowed">
                        Envoyer ma candidature
                    </button>
                    <p class="text-[9px] text-center text-gray-400 font-bold uppercase tracking-widest mt-4">
                        <i class="fas fa-shield-alt mr-1"></i> Vos documents sont transmis en toute sécurité
                    </p>
                </div>
            </form>
        </div>
    </div>

</body>
</html>






