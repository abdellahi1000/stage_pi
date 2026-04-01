<?php 
require_once '../include/session.php';
require_once '../include/lookups.php';
check_auth('entreprise', 'Administrator');

$sm_cities = sm_get_cities();
$sm_contract_types = sm_get_contract_types();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Offres - Espace Administrateur</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/admin_company_offers.js?v=<?php echo time(); ?>" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto bg-gray-50 md:ml-64">
            <!-- Mobile -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <span class="font-bold text-blue-600">StageMatch Admin</span>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>

            <div class="max-w-5xl mx-auto px-6 py-10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                    <div>
                        <h1 class="text-4xl font-extrabold text-gray-900 mb-1">Gérer les Offres</h1>
                        <p class="text-gray-500 font-medium">Contrôlez toutes les offres publiées par votre entreprise.</p>
                    </div>
                    
                    <button id="btnNewOffre" class="px-7 py-4 bg-blue-600 text-white rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 hover:-translate-y-1 transition-all flex items-center gap-3">
                        <i class="fas fa-plus-circle text-lg"></i> Créer une Offre
                    </button>
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
                    <div class="lg:col-span-2 relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="searchOffre" placeholder="Rechercher une offre..." class="w-full pl-12 pr-4 py-4 rounded-2xl border border-gray-100 bg-white focus:border-blue-500 outline-none shadow-sm transition-all">
                    </div>

                    <div class="relative custom-dropdown" id="dropdownLocalisation">
                        <button class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-white text-left flex justify-between items-center hover:border-blue-500 transition-all shadow-sm">
                            <span class="truncate text-gray-700 font-medium">Localisation</span>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>
                        <input type="hidden" name="filter_localisation" value="">
                        <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                            <div class="dropdown-item" data-value="">Toutes les Villes</div>
                            <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Capitale</div>
                            <div class="dropdown-item" data-value="Nouakchott">Nouakchott</div>
                            <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Autres Villes</div>
                            <?php foreach ($sm_cities as $city): if ($city['code'] === 'Nouakchott') continue; ?>
                                <div class="dropdown-item" data-value="<?php echo htmlspecialchars($city['code']); ?>"><?php echo htmlspecialchars($city['label']); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="relative custom-dropdown" id="dropdownCategory">
                        <button class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-white text-left flex justify-between items-center hover:border-blue-500 transition-all shadow-sm">
                            <span class="truncate text-gray-700 font-medium">Catégorie</span>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>
                        <input type="hidden" name="filter_category" value="">
                        <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                            <div class="dropdown-item" data-value="">Toutes les Catégories</div>
                            <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Secteurs Principaux</div>
                            <div class="dropdown-item" data-value="1">Informatique</div>
                            <div class="dropdown-item" data-value="2">Mines & Ressources</div>
                            <div class="dropdown-item" data-value="3">Télécommunications</div>
                            <div class="dropdown-item" data-value="4">Commerce & Marketing</div>
                            <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Support & Services</div>
                            <div class="dropdown-item" data-value="5">Finance & Comptabilité</div>
                            <div class="dropdown-item" data-value="6">Ressources Humaines</div>
                        </div>
                    </div>

                    <div class="relative custom-dropdown" id="dropdownType">
                        <button class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-white text-left flex justify-between items-center hover:border-blue-500 transition-all shadow-sm">
                            <span class="truncate text-gray-700 font-medium">Type</span>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>
                        <input type="hidden" name="filter_type" value="">
                        <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                            <div class="dropdown-item" data-value="">Tout</div>
                            <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Formats Disponibles</div>
                            <?php foreach ($sm_contract_types as $ct): ?>
                                <div class="dropdown-item" data-value="<?php echo htmlspecialchars($ct['code']); ?>"><?php echo htmlspecialchars($ct['label']); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="relative custom-dropdown" id="dropdownStatut">
                        <button class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-white text-left flex justify-between items-center hover:border-blue-500 transition-all shadow-sm">
                            <span class="truncate text-gray-700 font-medium">Statut</span>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>
                        <input type="hidden" name="filter_statut" value="">
                        <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                            <div class="dropdown-item" data-value="">Tout</div>
                            <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Options de Visibilité</div>
                            <div class="dropdown-item" data-value="active">Active</div>
                            <div class="dropdown-item" data-value="archivee">Archivée</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="offresGrid">
                    <!-- Dynamic -->
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Form -->
    <div id="modalOffre" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-[2.5rem] w-full max-w-2xl max-h-[90vh] overflow-y-auto m-4 shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="p-10">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-black text-gray-900" id="modalTitle">Nouvelle Offre</h2>
                    <button class="close-modal w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 transition-all"><i class="fas fa-times"></i></button>
                </div>

                <form id="formOffre" class="space-y-6">
                    <input type="hidden" name="id" id="offreId">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Titre de l'offre</label>
                            <input type="text" name="titre" required class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-medium">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Localisation</label>
                            <div class="relative custom-dropdown" id="formDropdownLocalisation">
                                <button type="button" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 text-left flex justify-between items-center focus:bg-white focus:border-blue-500 outline-none transition-all">
                                    <span class="truncate">Nouakchott</span>
                                    <i class="fas fa-chevron-down text-[10px] text-gray-400"></i>
                                </button>
                                <input type="hidden" name="localisation" value="Nouakchott" required>
                                <div class="dropdown-menu absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                                    <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Capitale</div>
                                    <div class="dropdown-item" data-value="Nouakchott">Nouakchott</div>
                                    <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Autres Villes</div>
                                    <?php foreach ($sm_cities as $city): if ($city['code'] === 'Nouakchott') continue; ?>
                                        <div class="dropdown-item" data-value="<?php echo htmlspecialchars($city['code']); ?>"><?php echo htmlspecialchars($city['label']); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Type de Contrat</label>
                            <div class="relative custom-dropdown" id="formDropdownType">
                                <button type="button" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 text-left flex justify-between items-center focus:bg-white focus:border-blue-500 outline-none transition-all">
                                    <span class="truncate">Stage</span>
                                    <i class="fas fa-chevron-down text-[10px] text-gray-400"></i>
                                </button>
                                <input type="hidden" name="type_contrat" value="Stage" required>
                                <div class="dropdown-menu absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                                    <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Formats Disponibles</div>
                                    <?php foreach ($sm_contract_types as $ct): ?>
                                        <div class="dropdown-item" data-value="<?php echo htmlspecialchars($ct['code']); ?>"><?php echo htmlspecialchars($ct['label']); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Catégorie</label>
                             <div class="relative custom-dropdown" id="formDropdownCategory">
                                <button type="button" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 text-left flex justify-between items-center focus:bg-white focus:border-blue-500 outline-none transition-all">
                                    <span class="truncate">Informatique</span>
                                    <i class="fas fa-chevron-down text-[10px] text-gray-400"></i>
                                </button>
                                <input type="hidden" name="categorie_id" value="1" required>
                                <div class="dropdown-menu absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                                    <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Secteurs Principaux</div>
                                    <div class="dropdown-item" data-value="1">Informatique</div>
                                    <div class="dropdown-item" data-value="2">Mines & Ressources</div>
                                    <div class="dropdown-item" data-value="3">Télécommunications</div>
                                    <div class="dropdown-item" data-value="4">Commerce & Marketing</div>
                                    <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Support & Services</div>
                                    <div class="dropdown-item" data-value="5">Finance & Comptabilité</div>
                                    <div class="dropdown-item" data-value="6">Ressources Humaines</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100">
                        <h3 class="text-xs font-black text-blue-600 uppercase tracking-[0.3em] mb-6 flex items-center gap-2">
                             <i class="fas fa-layer-group"></i> Détails Complémentaires (Group Optional)
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-6 mb-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Spécialisation</label>
                                <input type="text" name="specialization" placeholder="Ex: Développement Web" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-medium">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Tags (Séparez par des virgules)</label>
                                <input type="text" name="tags" placeholder="Ex: PHP, React, MySQL" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-medium">
                            </div>
                        </div>

                        <div class="space-y-2 mb-6">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Technologies (Séparez par des virgules)</label>
                            <input type="text" name="technologies" placeholder="Ex: React, Node.js, Docker" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-medium">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Questions pour le candidat (Une par ligne)</label>
                            <textarea name="questions" rows="3" placeholder="Ex: Pourquoi voulez-vous ce stage ?" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all resize-none font-medium"></textarea>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100">
                        <div class="grid grid-cols-2 gap-6 mb-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Durée / Période</label>
                                <input type="text" name="duree" placeholder="Ex: 3 mois" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-medium">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Statut individuel</label>
                                <div class="relative custom-dropdown" id="formDropdownStatutModal">
                                    <button type="button" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 text-left flex justify-between items-center focus:bg-white focus:border-blue-500 outline-none transition-all">
                                        <span class="truncate text-sm font-bold">Active (Visible)</span>
                                        <i class="fas fa-chevron-down text-[10px] text-gray-400"></i>
                                    </button>
                                    <input type="hidden" name="statut" value="active">
                                    <div class="dropdown-menu absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                                        <div class="dropdown-item bg-gray-50/50 py-1.5 px-4 text-[9px] font-black uppercase text-gray-400 tracking-widest pointer-events-none">Options de Visibilité</div>
                                        <div class="dropdown-item" data-value="active">Active (Visible)</div>
                                        <div class="dropdown-item" data-value="archivee">Archivée (Masquée)</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-1 space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Places</label>
                                <input type="number" name="nombre_stagiaires" min="1" value="1" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-bold">
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Description de l'offre</label>
                                <textarea name="description" required rows="1" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all resize-none font-medium" placeholder="Décrivez les missions..."></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-black shadow-xl hover:bg-blue-700 transition-all">
                        Enregistrer l'offre
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
