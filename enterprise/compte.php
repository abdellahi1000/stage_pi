<?php 
require_once '../include/session.php';
check_auth('entreprise');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte Entreprise - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/compte.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/enterprise_compte.js" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?> bg-gray-50">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto md:ml-64 relative">
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30 px-6">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-blue-600">StageMatch</span>
                </div>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" id="mainDashboardView">
                <!-- ENTERPRISE PROFILE HEADER -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mb-8 relative">
                    <!-- Cover Image / Premium Gradient Header -->
                    <div class="h-48 md:h-56 w-full relative overflow-hidden" style="background: linear-gradient(135deg, #0a0f2e 0%, #1a0545 100%);">
                        <!-- Subtle Pattern Overlay (texture) -->
                        <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, #ffffff 0, #ffffff 1px, transparent 0, transparent 20px);"></div>
                        <!-- Soft Premium Glow behind Profile Pic -->
                        <div class="absolute -left-12 -bottom-24 w-96 h-96 bg-[#6c3fc5] opacity-25 blur-[100px] rounded-full"></div>
                        
                        <!-- Desktop Identity Overlay -->
                        <div class="absolute bottom-10 left-48 hidden md:block z-20">
                            <h1 class="text-4xl font-black text-white flex items-center gap-3 drop-shadow-2xl">
                                <span id="hdrCompanyNameOverlay"><?php echo htmlspecialchars($_SESSION['company_name'] ?? 'Entreprise'); ?></span>
                                <?php if (!empty($_SESSION['verified_status'])): ?>
                                    <i class="fas fa-check-circle text-yellow-400 text-2xl" title="Officiellement Vérifiée"></i>
                                <?php endif; ?>
                            </h1>
                            <p class="text-blue-200/90 font-bold text-sm tracking-[0.3em] uppercase mt-2 drop-shadow-md flex items-center gap-2">
                                <span class="w-8 h-[2px] bg-blue-400"></span>
                                <span id="hdrIndustryOverlay">TÉLÉCHARGEMENT...</span>
                            </p>
                        </div>

                    </div>

                    
                    <div class="px-8 pb-8 flex flex-col md:flex-row items-center md:items-start gap-6 relative -mt-16">
                        <!-- Profile Pic -->
                        <div class="w-32 h-32 rounded-2xl bg-white p-2 shadow-lg border border-gray-100 shrink-0">
                            <img id="hdrProfilePic" class="w-full h-full rounded-xl object-cover" src="<?php echo !empty($_SESSION['photo_profil']) ? htmlspecialchars('../' . $_SESSION['photo_profil']) : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['company_name'] ?? 'Entreprise') . '&background=random'; ?>" alt="Logo">
                        </div>
                        
                        <div class="flex-1 text-center md:text-left pt-4 md:pt-20">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="md:hidden"> <!-- Mobile only visible -->
                                    <h1 class="text-3xl font-extrabold text-gray-900 flex items-center justify-center md:justify-start gap-2">
                                        <span id="hdrCompanyName"><?php echo htmlspecialchars($_SESSION['company_name'] ?? 'Entreprise'); ?></span>
                                        <?php if (!empty($_SESSION['verified_status'])): ?>
                                            <i class="fas fa-check-circle text-yellow-500" title="This company is officially verified"></i>
                                        <?php endif; ?>
                                    </h1>
                                    <p class="text-blue-600 font-semibold text-sm tracking-wide uppercase mt-1" id="hdrIndustry">Téléchargement...</p>
                                </div>
                                <div class="hidden md:block"></div> <!-- Spacer for desktop since title moved up -->

                                <div class="flex space-x-3">
                                    <?php if ($_SESSION['user_role'] === 'Administrator'): ?>
                                    <a href="../administrator/settings.php" class="bg-blue-100 text-blue-700 px-4 py-2 rounded-xl font-bold hover:bg-blue-200 transition shadow-sm border border-blue-200" title="Paramètres Système (Admin)">
                                        <i class="fas fa-cogs"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button onclick="openSettings()" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl font-bold hover:bg-gray-200 transition shadow-sm border border-gray-200" title="Paramètres" id="settingsToggle">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button id="darkModeToggle" class="bg-gray-900 text-white px-5 py-2 rounded-xl font-bold hover:bg-blue-600 transition shadow-lg shadow-gray-200 hover:shadow-blue-200 flex items-center gap-2">
                                        <i id="darkModeIcon" class="fas fa-moon"></i> <span>Mode Sombre</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="mt-6 flex flex-wrap justify-center md:justify-start gap-4 text-sm text-gray-600 font-medium">
                                <span class="flex items-center gap-1.5 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100"><i class="fas fa-envelope text-gray-400"></i> <span id="hdrEmail"></span></span>
                                <span class="flex items-center gap-1.5 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100"><i class="fas fa-phone text-gray-400"></i> <span id="hdrPhone"></span></span>
                                <span class="flex items-center gap-1.5 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100"><i class="fas fa-map-marker-alt text-gray-400"></i> <span id="hdrLocation"></span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ENTERPRISE ACTIVITY ANALYTICS RECTANGLE -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 mb-8">
                    <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2"><i class="fas fa-chart-line text-blue-500"></i> Activité de Recrutement</h2>
                    
                    <!-- Stats Overview -->
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8 text-center auto-rows-fr">
                        <!-- Card 1 -->
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 flex flex-col justify-center items-center h-full w-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-inbox text-gray-400 mb-3 text-2xl"></i>
                            <p class="text-2xl font-black text-gray-900 leading-none" id="statTotalApps">0</p>
                            <p class="text-[10px] sm:text-[11px] font-bold text-gray-500 uppercase tracking-wider mt-2 break-words text-center w-full leading-snug min-h-[32px] flex items-center justify-center">Candidatures (Total)</p>
                        </div>
                        <!-- Card 2 -->
                        <div class="bg-green-50 p-5 rounded-2xl border border-green-100 flex flex-col justify-center items-center h-full w-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-check-circle text-green-500 mb-3 text-2xl"></i>
                            <p class="text-2xl font-black text-green-700 leading-none" id="statAccepted">0</p>
                            <p class="text-[10px] sm:text-[11px] font-bold text-green-600 uppercase tracking-wider mt-2 break-words text-center w-full leading-snug min-h-[32px] flex items-center justify-center">Acceptés</p>
                        </div>
                        <!-- Card 3 -->
                        <div class="bg-red-50 p-5 rounded-2xl border border-red-100 flex flex-col justify-center items-center h-full w-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-times-circle text-red-500 mb-3 text-2xl"></i>
                            <p class="text-2xl font-black text-red-700 leading-none" id="statRejected">0</p>
                            <p class="text-[10px] sm:text-[11px] font-bold text-red-600 uppercase tracking-wider mt-2 break-words text-center w-full leading-snug min-h-[32px] flex items-center justify-center">Refusés</p>
                        </div>
                        <!-- Card 4 -->
                        <div class="bg-orange-50 p-5 rounded-2xl border border-orange-100 flex flex-col justify-center items-center h-full w-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-clock text-orange-500 mb-3 text-2xl"></i>
                            <p class="text-2xl font-black text-orange-700 leading-none" id="statPending">0</p>
                            <p class="text-[10px] sm:text-[11px] font-bold text-orange-600 uppercase tracking-wider mt-2 break-words text-center w-full leading-snug min-h-[32px] flex items-center justify-center">En Attente</p>
                        </div>
                        <!-- Card 5 -->
                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 flex flex-col justify-center items-center h-full w-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-eye-slash text-slate-500 mb-3 text-2xl"></i>
                            <p class="text-2xl font-black text-slate-700 leading-none" id="statOffers">0</p>
                            <p class="text-[10px] sm:text-[11px] font-bold text-slate-600 uppercase tracking-wider mt-2 break-words text-center w-full leading-snug min-h-[32px] flex items-center justify-center">Offres Masquées</p>
                        </div>
                        <!-- Card 6 -->
                        <div class="bg-purple-50 p-5 rounded-2xl border border-purple-100 flex flex-col justify-center items-center h-full w-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-heart text-purple-500 mb-3 text-2xl"></i>
                            <p class="text-2xl font-black text-purple-700 leading-none" id="statFavorites">0</p>
                            <p class="text-[10px] sm:text-[11px] font-bold text-purple-600 uppercase tracking-wider mt-2 break-words text-center w-full leading-snug min-h-[32px] flex items-center justify-center">Favoris</p>
                        </div>
                    </div>

                    <!-- Flux Activity Heatmap -->
                    <div class="mt-10 pt-8 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-calendar-alt text-blue-500"></i>
                                    Flux de Recrutement
                                </h3>
                                <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider">Activité combinée des 12 derniers mois</p>
                            </div>
                            <div class="flex items-center gap-6">
                                <div id="activityLegend" class="hidden md:flex items-center gap-4 text-[10px] font-bold text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                                    <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-[2px] bg-green-500 shadow-sm shadow-green-200"></div> Acceptés</div>
                                    <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-[2px] bg-red-500 shadow-sm shadow-red-200"></div> Refusés</div>
                                    <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-[2px] bg-orange-400 shadow-sm shadow-orange-200"></div> En Attente</div>
                                </div>
                                <a href="offres.php" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                                    Voir détails <i class="fas fa-chevron-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <!-- Day Labels -->
                            <div class="flex flex-col gap-1 pt-[28px] text-[9px] font-black text-gray-400 uppercase tracking-tighter select-none w-6 shrink-0">
                                <div class="h-4 flex items-center">L</div>
                                <div class="h-4 flex items-center"></div>
                                <div class="h-4 flex items-center">M</div>
                                <div class="h-4 flex items-center"></div>
                                <div class="h-4 flex items-center">V</div>
                                <div class="h-4 flex items-center"></div>
                                <div class="h-4 flex items-center">D</div>
                            </div>

                            <!-- Scrollable Graph Area -->
                            <div class="flex-1 overflow-x-auto pb-4 custom-scrollbar no-scrollbar scroll-smooth">
                                <div class="flex flex-col gap-1" style="min-width: max-content;">
                                    <!-- Month Labels Row -->
                                    <div id="activityMonthRow" class="flex gap-1 h-6 relative items-end text-[9px] font-black text-gray-400 uppercase tracking-tighter select-none">
                                        <!-- JS fills this -->
                                    </div>
                                    <!-- Heatmap Grid -->
                                    <div id="activityGraph" class="flex gap-1" style="min-width: max-content;">
                                        <!-- JS fills this -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-2 px-1">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">Répartition par statut des candidatures sur les 52 dernières semaines.</p>
                            <div class="flex md:hidden items-center gap-3 text-[9px] font-bold text-gray-400">
                                <div class="flex items-center gap-1"><div class="w-2 h-2 rounded-[1px] bg-green-500"></div> ACC</div>
                                <div class="flex items-center gap-1"><div class="w-2 h-2 rounded-[1px] bg-red-500"></div> REF</div>
                                <div class="flex items-center gap-1"><div class="w-2 h-2 rounded-[1px] bg-orange-400"></div> ATT</div>
                            </div>
                        </div>
                    </div>

                    <!-- Bio & Contact Quick View -->
                    <div class="mt-12 pt-8 border-t border-gray-100">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                            <div>
                                <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-id-card text-blue-500"></i>
                                    Aperçu Bio & Coordonnées
                                </h3>
                                <p class="text-xs text-gray-400 font-medium">Visualisez vos informations telles qu'elles apparaissent sur votre profil public.</p>
                            </div>
                            <button onclick="openSettings(); switchTab('tab-profile', document.querySelector('.settings-tab-btn'))" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-blue-700 transition flex items-center gap-2 shadow-md shadow-blue-200 shrink-0">
                                <i class="fas fa-edit text-[10px]"></i> Modifier Bio & Site
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Bio & Official Site -->
                            <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
                                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                    <i class="fas fa-building text-6xl rotate-12"></i>
                                </div>
                                <div class="relative z-10 h-full flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                            <span class="w-1 h-3 bg-blue-500 rounded-full"></span>
                                            À propos de l'entreprise
                                        </h4>
                                        <p id="dash_bio_text" class="text-gray-600 leading-relaxed text-sm italic">Chargement de la bio...</p>
                                    </div>
                                    
                                    <div class="mt-12 pt-6 border-t border-gray-100 flex items-center justify-between">
                                        <div>
                                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                                                <i class="fas fa-globe text-blue-400"></i>
                                                Site officiel
                                            </h4>
                                            <a id="dash_website_link" href="#" target="_blank" class="inline-flex items-center gap-2 text-blue-600 font-black text-sm hover:underline">
                                                <span id="dash_website_text">Non spécifié</span>
                                                <i class="fas fa-external-link-alt text-[9px]"></i>
                                            </a>
                                        </div>
                                        <div id="dash_socials_list" class="flex items-center gap-2">
                                            <!-- JS fills -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Contacts -->
                            <div class="space-y-4">
                                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5 group hover:bg-white hover:shadow-lg transition-all duration-300">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Emails de contact</h4>
                                        <i class="fas fa-envelope text-blue-400/50"></i>
                                    </div>
                                    <div id="dash_emails_list" class="space-y-2">
                                        <!-- JS fills -->
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5 group hover:bg-white hover:shadow-lg transition-all duration-300">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Téléphones</h4>
                                        <i class="fas fa-phone-alt text-green-400/50"></i>
                                    </div>
                                    <div id="dash_phones_list" class="space-y-2">
                                        <!-- JS fills -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Achievements / Links -->
                    <div class="mt-12 pt-8 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-project-diagram text-indigo-500"></i>
                                Réalisations / Présentation
                            </h3>
                            <button onclick="openSettings(); switchTab('tab-achievements', document.getElementById('settingsTabAchievements'))" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-indigo-100 transition flex items-center gap-2">
                                <i class="fas fa-plus"></i> Gérer Réalisations
                            </button>
                        </div>
                        <div id="achievementsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-left">
                            <!-- Filled via JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- SETTINGS OVERLAY PANEL (Full view) -->
            <div id="settingsPanel" class="fixed inset-0 bg-white z-[100] transform translate-x-full transition-transform duration-500 overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between z-10 shadow-sm">
                    <h2 class="text-2xl font-black text-gray-900"><i class="fas fa-gear text-blue-600 mr-2"></i> Paramètres Entreprise</h2>
                    <button onclick="closeSettings()" class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="max-w-6xl mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">
                    <!-- Tabs Menu -->
                    <div class="w-full md:w-64 shrink-0">
                        <nav class="flex flex-col space-y-2">
                            <button onclick="switchTab('tab-profile', this)" class="settings-tab-btn active w-full text-left px-4 py-3 rounded-xl font-bold bg-gray-900 text-white transition">
                                <i class="fas fa-building w-6"></i> Profil Entreprise
                            </button>
                            <button onclick="switchTab('tab-security', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl font-bold text-gray-600 hover:bg-gray-100 transition">
                                <i class="fas fa-shield-alt w-6"></i> Compte & Sécurité
                            </button>
                            <button onclick="switchTab('tab-management', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl font-bold text-gray-600 hover:bg-gray-100 transition">
                                <i class="fas fa-briefcase w-6"></i> Gestion des Recrutements
                            </button>
                            <button onclick="switchTab('tab-notifications', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl font-bold text-gray-600 hover:bg-gray-100 transition">
                                <i class="fas fa-bell w-6"></i> Notifications
                            </button>
                            <button onclick="switchTab('tab-preferences', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl font-bold text-gray-600 hover:bg-gray-100 transition">
                                <i class="fas fa-sliders w-6"></i> Préférences
                            </button>
                            <button id="settingsTabAchievements" onclick="switchTab('tab-achievements', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl font-bold text-gray-600 hover:bg-gray-100 transition">
                                <i class="fas fa-trophy w-6"></i> Réalisations & liens
                            </button>
                            <?php if ($_SESSION['user_role'] === 'Administrator'): ?>
                            <button id="settingsTabSystem" onclick="switchTab('tab-admin-system', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl font-bold text-blue-600 hover:bg-blue-50 transition border border-transparent hover:border-blue-100">
                                <i class="fas fa-cogs w-6"></i> Paramètres Système
                            </button>
                            <?php endif; ?>
                        </nav>
                    </div>

                    <!-- Tabs Content -->
                    <div class="flex-1 min-w-0 pb-20">
                        <!-- 1. Profile Tab -->
                        <div id="tab-profile" class="settings-tab active">
                            <h3 class="text-xl font-black mb-6">Informations Publiques</h3>
                            <form id="formProfile" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="col-span-1 md:col-span-2 flex items-center gap-6">
                                        <div class="relative group">
                                            <div class="w-24 h-24 rounded-2xl bg-gray-100 border border-gray-200 overflow-hidden shrink-0">
                                                <img id="formProfileImg" class="w-full h-full object-cover" src="" alt="Photo">
                                            </div>
                                            <?php if ($_SESSION['user_role'] === 'Administrator'): ?>
                                                <label for="company_logo_input" class="absolute inset-0 bg-black/40 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded-2xl">
                                                    <i class="fas fa-camera text-xl"></i>
                                                </label>
                                                <input type="file" id="company_logo_input" class="hidden" accept="image/*">
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Logo de l'entreprise</label>
                                            <p class="text-xs text-gray-500"><?php echo $_SESSION['user_role'] === 'Administrator' ? 'Cliquez sur l\'image pour changer le logo.' : 'Seul l\'Administrateur peut modifier le logo de l\'entreprise.'; ?></p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Nom de l'entreprise</label>
                                        <input type="text" id="inp_nom" class="w-full px-4 py-3 <?php echo $_SESSION['user_role'] === 'Administrator' ? 'bg-gray-50 border border-gray-200' : 'bg-gray-100 border border-gray-200 text-gray-500 cursor-not-allowed'; ?> rounded-xl outline-none focus:border-blue-500 transition" <?php echo $_SESSION['user_role'] === 'Administrator' ? '' : 'readonly'; ?>>
                                        <p class="text-xs text-gray-400 mt-1">Géré par l'Administrateur de l'entreprise.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Secteur d'activité</label>
                                        <input type="text" id="inp_industry" class="w-full px-4 py-3 <?php echo $_SESSION['user_role'] === 'Administrator' ? 'bg-gray-50 border border-gray-200' : 'bg-gray-100 border border-gray-200 text-gray-500 cursor-not-allowed'; ?> rounded-xl outline-none focus:border-blue-500 transition" <?php echo $_SESSION['user_role'] === 'Administrator' ? '' : 'readonly'; ?>>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Employés</label>
                                        <select id="inp_size" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition">
                                            <option value="1-10">1-10 employés</option>
                                            <option value="11-50">11-50 employés</option>
                                            <option value="51-200">51-200 employés</option>
                                            <option value="201-500">201-500 employés</option>
                                            <option value="500+">500+ employés</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Lien du site Web</label>
                                        <input type="url" id="inp_website" placeholder="https://..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition">
                                    </div>
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Description entreprise</label>
                                        <textarea id="inp_bio" rows="4" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition" placeholder="Présentez votre entreprise..."></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Lieu / Adresse</label>
                                        <input type="text" id="inp_address" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Contact Manager RH</label>
                                        <input type="text" id="inp_hr" placeholder="Nom du responsable HR" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition">
                                    </div>
                                </div>
                                <div class="flex justify-end pt-4 border-t border-gray-100">
                                    <button type="button" onclick="saveProfile()" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200">Enregistrer les modifications</button>
                                </div>
                            </form>
                        </div>

                        <!-- 2. Account & Security Tab -->
                        <div id="tab-security" class="settings-tab hidden">
                            <h3 class="text-xl font-black mb-6">Sécurité du Compte</h3>
                            
                            <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-xl mb-6">
                                <h4 class="font-bold text-yellow-800 text-sm mb-1"><i class="fas fa-exclamation-triangle mr-2"></i>Email d'Entreprise</h4>
                                <p class="text-xs text-yellow-700">Votre email (<span id="sec_email" class="font-bold"></span>) est lié à votre vérification. Tout changement retirera votre badge "Vérifié" et nécessitera une ré-approbation administrative.</p>
                            </div>

                            <form id="formSecurity" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Changer l'adresse Email</label>
                                        <input type="email" id="inp_sec_email" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Numéro de Téléphone (Contact principal)</label>
                                        <input type="tel" id="inp_sec_phone" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition">
                                    </div>
                                    <div class="col-span-1 md:col-span-2 border-t border-gray-100 pt-6 mt-2">
                                        <h4 class="font-bold text-gray-800 mb-4">Modifier le mot de passe</h4>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Mot de passe actuel</label>
                                        <input type="password" id="inp_old_pw" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Nouveau mot de passe</label>
                                        <input type="password" id="inp_new_pw" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Confirmer le mot de passe</label>
                                        <input type="password" id="inp_new_pw_confirm" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition">
                                    </div>
                                </div>

                                <div class="mt-6 bg-red-50 border border-red-100 p-4 rounded-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div>
                                        <h4 class="font-bold text-red-800 text-sm mb-1"><i class="fas fa-unlock-alt mr-2"></i>Mot de passe oublié ?</h4>
                                        <p class="text-xs text-red-700">Si vous ne connaissez plus votre mot de passe actuel, vous pouvez en définir un nouveau ci-dessous.</p>
                                    </div>
                                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                                        <input type="password" id="inp_reset_pw" placeholder="Nouveau mot de passe" class="w-full sm:w-48 px-3 py-2 bg-white border border-red-200 rounded-lg text-sm focus:border-red-400 outline-none">
                                        <input type="password" id="inp_reset_pw_confirm" placeholder="Confirmer" class="w-full sm:w-48 px-3 py-2 bg-white border border-red-200 rounded-lg text-sm focus:border-red-400 outline-none">
                                        <button type="button" onclick="resetEnterprisePassword()" class="px-4 py-2 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 transition shadow-sm">Réinitialiser</button>
                                    </div>
                                </div>

                                <div class="bg-white p-6 rounded-xl border border-gray-200 mt-6 flex justify-between items-center shadow-sm">
                                    <div>
                                        <h4 class="font-bold text-gray-800">Authentification à deux facteurs (2FA)</h4>
                                        <p class="text-sm text-gray-500 mt-1">Sécurisez votre compte recruteur.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" class="sr-only peer" id="inp_2fa">
                                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <div class="flex justify-end pt-4 border-t border-gray-100">
                                    <button type="button" onclick="saveSecurity()" class="bg-gray-900 text-white px-6 py-3 rounded-xl font-bold hover:bg-black transition shadow-lg shadow-gray-200">Mettre à jour la sécurité</button>
                                </div>
                            </form>
                        </div>

                        <!-- 3. Internship & Recruitment Management -->
                        <div id="tab-management" class="settings-tab hidden">
                            <h3 class="text-xl font-black mb-6">Paramètres de Recrutement</h3>
                            
                            <div class="space-y-6">
                                <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="font-bold text-gray-800">Vos Offres de Stage</h4>
                                            <p class="text-sm text-gray-500">Gérez, éditez ou clôturez vos annonces.</p>
                                        </div>
                                        <a href="offres.php" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-bold hover:bg-blue-200 transition">Gérer les offres externes <i class="fas fa-external-link-alt ml-1"></i></a>
                                    </div>
                                    <div class="text-sm text-gray-600 bg-white p-4 rounded-lg border border-gray-100 italic">
                                        Les paramètres des offres (compétences définies, durées, etc.) se gèrent directement depuis votre espace "Mes Offres".
                                    </div>
                                </div>
                                
                                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                                    <h4 class="font-bold text-gray-800 mb-2">Signature pour les messages d'acceptation</h4>
                                    <p class="text-sm text-gray-500 mb-4">Cette signature sera affichée à côté du message d'acceptation envoyé aux étudiants. Format : JPG, PNG, GIF ou WEBP (max 2 Mo).</p>
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                        <div class="w-40 h-24 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                                            <img id="signaturePreview" class="max-w-full max-h-full object-contain" src="" alt="Signature" style="display: none;">
                                            <span id="signaturePlaceholder" class="text-xs text-gray-400">Aucune signature</span>
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <input type="file" id="signature_upload" class="hidden" accept="image/jpeg,image/png,image/gif,image/webp">
                                            <label for="signature_upload" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition cursor-pointer inline-block w-fit">Choisir une image</label>
                                            <button type="button" id="signatureRemoveBtn" class="text-sm text-gray-500 hover:text-red-600 hidden">Supprimer la signature</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                                    <h4 class="font-bold text-gray-800 mb-4">Modèles de Candidature par Défaut</h4>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Message d'Acceptation par défaut</label>
                                            <textarea class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-green-500 transition text-sm" rows="3">Félicitations, votre candidature a été retenue. Nous vous contacterons prochainement pour la signature de votre convention de stage.</textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Message de Refus par défaut</label>
                                            <textarea class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-red-500 transition text-sm" rows="3">Malgré la qualité de votre profil, nous avons le regret de ne pas pouvoir donner une suite favorable à votre candidature pour ce poste.</textarea>
                                        </div>
                                        <button class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-200 transition">Sauvegarder les modèles</button>
                                    </div>
                                </div>
                                
                                <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 flex justify-between items-center shadow-sm">
                                    <div>
                                        <h4 class="font-bold text-indigo-900">Activer le mode Alternance</h4>
                                        <p class="text-xs text-indigo-700 mt-1">Permettre la publication d'offres en alternance en plus des stages.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" id="mode_alternance" class="sr-only peer" checked>
                                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                                <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100 flex justify-between items-center shadow-sm">
                                    <div>
                                        <h4 class="font-bold text-blue-900">Activer les Statistiques (Statsy)</h4>
                                        <p class="text-xs text-blue-700 mt-1">Afficher les graphiques de performance et d'engagement sur le dashboard.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" id="mode_statsy" class="sr-only peer" checked>
                                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Notifications & Communication -->
                        <div id="tab-notifications" class="settings-tab hidden">
                            <h3 class="text-xl font-black mb-6">Préférences de Notification</h3>

                            <div class="space-y-4">
                                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex justify-between items-center">
                                    <div>
                                        <p class="font-bold text-gray-800">Nouvelles Candidatures</p>
                                        <p class="text-xs text-gray-500">M'alerter par email lorsqu'un étudiant postule.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" id="notif_new_applications" class="sr-only peer" checked>
                                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex justify-between items-center">
                                    <div>
                                        <p class="font-bold text-gray-800">Alertes Entretiens</p>
                                        <p class="text-xs text-gray-500">Rappels pour les rdv (Intégration Calendar).</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" id="notif_interview_alerts" class="sr-only peer" checked>
                                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex justify-between items-center">
                                    <div>
                                        <p class="font-bold text-gray-800">Messagerie Interne</p>
                                        <p class="text-xs text-gray-500">Permettre aux étudiants approuvés de m'envoyer des messages.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" id="notif_internal_messages" class="sr-only peer">
                                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                <div class="flex justify-end pt-4">
                                    <button id="btnNotificationsApply" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition shadow-md">Appliquer</button>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Enterprise Preferences -->
                        <div id="tab-preferences" class="settings-tab hidden">
                            <h3 class="text-xl font-black mb-6">Préférences Globales</h3>

                            <div class="grid grid-cols-1 gap-6">
                                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Visibilité du Profil</label>
                                    <select class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 transition">
                                        <option value="public">Public (Visible par tous les étudiants)</option>
                                        <option value="private">Privé (Uniquement ceux qui ont un lien d'offre)</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-2">Définit si les étudiants peuvent trouver votre entreprise sur l'annuaire.</p>
                                </div>

                                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Langue du Dashboard</label>
                                    <select class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 transition">
                                        <option value="fr">Français (Défaut)</option>
                                        <option value="en" disabled>English (Bientôt)</option>
                                    </select>
                                </div>

                                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold text-gray-800">Exporter les Rapports de Recrutement</h4>
                                        <p class="text-xs text-gray-500 mt-1">Télécharger les CVs et données statistiques en .csv ou .pdf</p>
                                    </div>
                                    <button id="btnExportData" class="bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-bold text-sm hover:bg-gray-200 transition border border-gray-200 flex items-center gap-2">
                                        <i class="fas fa-file-export"></i> Exporter Data
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Réalisations & liens -->
                        <div id="tab-achievements" class="settings-tab hidden">
                            <h3 class="text-xl font-black mb-2">Réalisations & Présence en ligne</h3>
                            <p class="text-sm text-gray-500 mb-6">Ces liens et réalisations s'affichent sur votre page Mon Compte (section Réalisations & Présence en ligne).</p>
                            <div class="space-y-6">
                                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="p-4 border-b border-gray-100"><span class="font-bold text-gray-800">Ajouter un lien ou une réalisation</span></div>
                                    <form id="formAchievement" class="p-6 space-y-4">
                                        <input type="hidden" id="achievementId" value="">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Type</label>
                                                <select id="achievementType" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition">
                                                    <option value="website">Site web</option>
                                                    <option value="project">Projet</option>
                                                    <option value="achievement">Réalisation / Distinction</option>
                                                    <option value="linkedin">LinkedIn</option>
                                                    <option value="other">Autre lien</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Titre <span class="text-red-500">*</span></label>
                                                <input type="text" id="achievementTitle" required placeholder="Ex: Site officiel, Projet X..." class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">URL (optionnel)</label>
                                            <input type="url" id="achievementUrl" placeholder="https://..." class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Description courte (optionnel)</label>
                                            <textarea id="achievementDesc" rows="2" placeholder="Une phrase pour décrire ce lien ou cette réalisation." class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition resize-none"></textarea>
                                        </div>
                                        <div class="flex gap-3">
                                            <button type="submit" id="btnAchievementSubmit" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition">Enregistrer</button>
                                            <button type="button" id="btnAchievementCancel" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-200 transition hidden">Annuler</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Multiple Emails Management -->
                                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                                        <span class="font-bold text-gray-800">Emails de contact</span>
                                        <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Multiple</span>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <p class="text-sm text-gray-500">Ajoutez plusieurs emails de contact pour votre entreprise. Ces emails seront visibles par les étudiants sur votre profil.</p>
                                        
                                        <!-- Add Email Form -->
                                        <div class="flex gap-3">
                                            <div class="relative flex-1">
                                                <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                                <input type="email" id="newEmailInput" placeholder="contact@entreprise.com" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition">
                                            </div>
                                            <button type="button" onclick="addCompanyEmail()" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition flex items-center gap-2 shrink-0">
                                                <i class="fas fa-plus"></i> <span class="hidden sm:inline">Ajouter un email</span>
                                            </button>
                                        </div>
                                        
                                        <!-- Emails List -->
                                        <div id="emailsList" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <!-- Loaded via JS -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Multiple Phones Management -->
                                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                                        <span class="font-bold text-gray-800">Numéros de téléphone</span>
                                        <span class="text-[10px] bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Multiple</span>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <p class="text-sm text-gray-500">Gérez vos numéros de contact par type (Téléphone, WhatsApp, Mobile).</p>
                                        
                                        <div class="flex flex-col sm:flex-row gap-3">
                                            <div class="relative flex-1">
                                                <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                                <input type="tel" id="newPhoneInput" placeholder="+222 XXXXXXXX" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition">
                                            </div>
                                            <div class="w-full sm:w-40">
                                                <select id="newPhoneType" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition">
                                                    <option value="Téléphone">Téléphone</option>
                                                    <option value="WhatsApp">WhatsApp</option>
                                                    <option value="Mobile">Mobile</option>
                                                </select>
                                            </div>
                                            <button type="button" onclick="addCompanyPhone()" class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold text-sm hover:bg-black transition flex items-center gap-2 shrink-0 justify-center">
                                                <i class="fas fa-plus"></i> <span class="hidden sm:inline">Ajouter numéro</span>
                                            </button>
                                        </div>
                                        
                                        <div id="phonesList" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <!-- Loaded via JS -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Social Media Links -->
                                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                                        <span class="font-bold text-gray-800">Réseaux sociaux</span>
                                        <span class="text-[10px] bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Officiel</span>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-sm text-gray-500 mb-6">Liez les comptes sociaux officiels de votre entreprise.</p>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-4">
                                                <!-- Facebook -->
                                                <div class="relative group">
                                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                        <i class="fab fa-facebook-f text-blue-600 text-lg"></i>
                                                    </div>
                                                    <input type="url" id="social_facebook" placeholder="Lien Facebook" class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition text-sm">
                                                </div>
                                                <!-- X / Twitter -->
                                                <div class="relative group">
                                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                        <i class="fab fa-x-twitter text-gray-900 text-lg"></i>
                                                    </div>
                                                    <input type="url" id="social_x" placeholder="Lien X (Twitter)" class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition text-sm">
                                                </div>
                                            </div>
                                            <div class="space-y-4">
                                                <!-- Instagram -->
                                                <div class="relative group">
                                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                        <i class="fab fa-instagram text-pink-600 text-lg"></i>
                                                    </div>
                                                    <input type="url" id="social_instagram" placeholder="Lien Instagram" class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition text-sm">
                                                </div>
                                                <!-- LinkedIn -->
                                                <div class="relative group">
                                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                        <i class="fab fa-linkedin-in text-blue-700 text-lg"></i>
                                                    </div>
                                                    <input type="url" id="social_linkedin" placeholder="Lien LinkedIn" class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition text-sm">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex justify-end mt-6">
                                            <button type="button" onclick="saveSocialLinks()" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-blue-700 transition shadow-md flex items-center gap-2">
                                                <i class="fas fa-save"></i> Enregistrer les liens
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Official Website -->
                                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                                        <span class="font-bold text-gray-800">Site web officiel</span>
                                        <span class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Lien Unique</span>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-sm text-gray-500 mb-4">C'est le lien principal qui apparaîtra sur le bouton "Visiter le site" de votre profil.</p>
                                        <div class="flex gap-3">
                                            <div class="relative flex-1">
                                                <i class="fas fa-globe absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                                <input type="url" id="mainWebsiteInput" placeholder="https://votre-entreprise.com" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition">
                                            </div>
                                            <button type="button" onclick="saveOfficialWebsite()" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition flex items-center gap-2 shrink-0">
                                                <i class="fas fa-check"></i> <span class="hidden sm:inline">Mettre à jour</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-3">Vos réalisations et liens additionnels</h4>
                                    <ul id="achievementsList" class="space-y-3"></ul>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Administrator System Settings -->
                        <?php if ($_SESSION['user_role'] === 'Administrator'): ?>
                        <div id="tab-admin-system" class="settings-tab hidden">
                            <h3 class="text-xl font-black mb-6">Paramètres du Système (Administrateur)</h3>
                            <div class="space-y-6">
                                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold text-gray-800">Notifications Hebdomadaires</h4>
                                        <p class="text-xs text-gray-500 mt-1">Recevoir un rapport de recrutement chaque semaine.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="sys_weekly_report" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold text-gray-800">Indexation par les moteurs de recherche</h4>
                                        <p class="text-xs text-gray-500 mt-1">Permettre à Google de trouver votre page entreprise.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="sys_seo_index" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                <div class="pt-6 border-t border-gray-100 flex justify-end">
                                    <button onclick="saveAdminPrefs()" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">Inscrire les préférences</button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            
        </main>
    </div>

    <!-- Logic Script -->
    <script>
        function saveAdminPrefs() {
            const weekly = document.getElementById('sys_weekly_report').checked;
            const seo = document.getElementById('sys_seo_index').checked;
            
            showToast('Succès', 'Vos préférences système ont été enregistrées avec succès.', 'success');
            
            // In a real app, we would fetch to an API here
            /*
            fetch('../api/admin_company_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ weekly_report: weekly, seo_index: seo })
            });
            */
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchDashboardData();

            // Logo Upload Handling
            const logoUpload = document.getElementById('logo_upload');
            const hdrProfilePic = document.getElementById('hdrProfilePic');
            const formProfileImg = document.getElementById('formProfileImg');

            if (logoUpload) {
                logoUpload.addEventListener('change', function(e) {
                    if (e.target.files && e.target.files[0]) {
                        const file = e.target.files[0];
                        
                        // Preview
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            if (hdrProfilePic) hdrProfilePic.src = event.target.result;
                            if (formProfileImg) formProfileImg.src = event.target.result;
                        };
                        reader.readAsDataURL(file);

                        // Upload to server
                        const formData = new FormData();
                        formData.append('photo', file);

                        fetch('../include/upload_photo.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Succès', 'Logo mis à jour avec succès.', 'success');
                            } else {
                                showToast('Erreur', data.message || 'Erreur lors de l\'upload.', 'error');
                            }
                        })
                        .catch(err => {
                            console.error('Upload error:', err);
                            showToast('Erreur', 'Une erreur est survenue lors de l\'upload.', 'error');
                        });
                    }
                });
            }

            // Signature upload (acceptance message)
            const signatureUpload = document.getElementById('signature_upload');
            if (signatureUpload) {
                signatureUpload.addEventListener('change', function(e) {
                    if (!e.target.files || !e.target.files[0]) return;
                    const file = e.target.files[0];
                    const formData = new FormData();
                    formData.append('action', 'upload_company_signature');
                    formData.append('signature', file);
                    fetch('../api/user.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.signature_url) {
                                const sigPreview = document.getElementById('signaturePreview');
                                const sigPlaceholder = document.getElementById('signaturePlaceholder');
                                const sigRemoveBtn = document.getElementById('signatureRemoveBtn');
                                if (sigPreview) { sigPreview.src = data.signature_url; sigPreview.style.display = 'block'; }
                                if (sigPlaceholder) sigPlaceholder.style.display = 'none';
                                if (sigRemoveBtn) sigRemoveBtn.classList.remove('hidden');
                                showToast('Succès', 'Signature enregistrée.', 'success');
                            } else {
                                showToast('Erreur', data.message || 'Erreur upload.', 'error');
                            }
                        })
                        .catch(() => showToast('Erreur', 'Une erreur est survenue.', 'error'));
                    e.target.value = '';
                });
            }
            const signatureRemoveBtn = document.getElementById('signatureRemoveBtn');
            if (signatureRemoveBtn) {
                signatureRemoveBtn.addEventListener('click', function() {
                    fetch('../api/user.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=remove_company_signature' })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                const sigPreview = document.getElementById('signaturePreview');
                                const sigPlaceholder = document.getElementById('signaturePlaceholder');
                                if (sigPreview) { sigPreview.src = ''; sigPreview.style.display = 'none'; }
                                if (sigPlaceholder) sigPlaceholder.style.display = 'inline';
                                signatureRemoveBtn.classList.add('hidden');
                                showToast('Succès', 'Signature supprimée.', 'success');
                            }
                        })
                        .catch(() => {});
                });
            }

            // Notifications Apply handler
            const notifApplyBtn = document.getElementById('btnNotificationsApply');
            if (notifApplyBtn) {
                notifApplyBtn.addEventListener('click', () => {
                    const newApps = document.getElementById('notif_new_applications')?.checked ? 1 : 0;
                    const interviews = document.getElementById('notif_interview_alerts')?.checked ? 1 : 0;
                    const messages = document.getElementById('notif_internal_messages')?.checked ? 1 : 0;

                    const params = new URLSearchParams();
                    params.append('action', 'update_enterprise_notifications');
                    params.append('new_applications', String(newApps));
                    params.append('interview_alerts', String(interviews));
                    params.append('internal_messages', String(messages));

                    fetch('../api/user.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: params.toString()
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Succès', data.message || 'Préférences mises à jour.', 'success');
                        } else {
                            showToast('Erreur', data.message || 'Impossible de mettre à jour les notifications.', 'error');
                        }
                    })
                    .catch(() => {
                        showToast('Erreur', 'Erreur réseau lors de la mise à jour des notifications.', 'error');
                    });
                });
            }

            // Load Company Contacts Info
            loadCompanyContacts();
        });

        function loadCompanyContacts() {
            fetch('../api/entreprise_contacts.php?action=get_all')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderEmails(data.emails);
                    renderPhones(data.phones);
                    populateSocials(data.socials);
                    document.getElementById('mainWebsiteInput').value = data.website || '';
                    // Also update the website in the profile tab
                    const inpWeb = document.getElementById('inp_website');
                    if (inpWeb) inpWeb.value = data.website || '';
                }
            });
        }

        function renderEmails(emails) {
            const list = document.getElementById('emailsList');
            const dashList = document.getElementById('dash_emails_list');
            
            if(list) list.innerHTML = '';
            if(dashList) dashList.innerHTML = '';

            if (!emails.length) {
                if(list) list.innerHTML = '<p class="col-span-full text-xs text-gray-400 italic">Aucun email additionnel.</p>';
                if(dashList) dashList.innerHTML = '<p class="text-xs text-gray-400 italic">Aucun email de contact.</p>';
                return;
            }
            emails.forEach(e => {
                if(list) {
                    const div = document.createElement('div');
                    div.className = "flex items-center justify-between p-3 bg-gray-50 border border-gray-100 rounded-xl group";
                    div.innerHTML = `
                        <span class="text-sm font-medium text-gray-700 truncate">${e.email}</span>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                            <button onclick="editEmail(${e.id}, '${e.email}')" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="Modifier"><i class="fas fa-edit text-xs"></i></button>
                            <button onclick="deleteEmail(${e.id})" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition" title="Supprimer"><i class="fas fa-trash text-xs"></i></button>
                        </div>
                    `;
                    list.appendChild(div);
                }
                
                if(dashList) {
                    const dashDiv = document.createElement('div');
                    dashDiv.className = "flex items-center gap-2 text-xs text-gray-600";
                    dashDiv.innerHTML = `<i class="fas fa-envelope text-blue-500 w-4"></i> <span class="truncate font-medium">${e.email}</span>`;
                    dashList.appendChild(dashDiv);
                }
            });
        }


        function renderPhones(phones) {
            const list = document.getElementById('phonesList');
            const dashList = document.getElementById('dash_phones_list');

            if(list) list.innerHTML = '';
            if(dashList) dashList.innerHTML = '';

            if (!phones.length) {
                if(list) list.innerHTML = '<p class="col-span-full text-xs text-gray-400 italic">Aucun numéro additionnel.</p>';
                if(dashList) dashList.innerHTML = '<p class="text-xs text-gray-400 italic">Aucun numéro de contact.</p>';
                return;
            }
            phones.forEach(p => {
                const icon = p.type === 'WhatsApp' ? 'fab fa-whatsapp' : (p.type === 'Mobile' ? 'fas fa-mobile-alt' : 'fas fa-phone');
                const color = p.type === 'WhatsApp' ? 'text-green-500' : 'text-blue-500';
                
                if(list) {
                    const div = document.createElement('div');
                    div.className = "flex items-center justify-between p-3 bg-gray-50 border border-gray-100 rounded-xl group";
                    div.innerHTML = `
                        <div class="flex items-center gap-3">
                            <i class="${icon} ${color} text-sm"></i>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-800">${p.phone_number}</span>
                                <span class="text-[10px] text-gray-400 font-bold uppercase">${p.type}</span>
                            </div>
                        </div>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                            <button onclick="editPhone(${p.id}, '${p.phone_number}', '${p.type}')" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="Modifier"><i class="fas fa-edit text-xs"></i></button>
                            <button onclick="deletePhone(${p.id})" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition" title="Supprimer"><i class="fas fa-trash text-xs"></i></button>
                        </div>
                    `;
                    list.appendChild(div);
                }

                if(dashList) {
                    const dashDiv = document.createElement('div');
                    dashDiv.className = "flex items-center gap-2 text-xs text-gray-600";
                    dashDiv.innerHTML = `<i class="${icon} ${color} w-4"></i> <span class="font-medium">${p.phone_number}</span> <span class="text-[9px] text-gray-400">(${p.type})</span>`;
                    dashList.appendChild(dashDiv);
                }
            });
        }


        function populateSocials(socials) {
            const mapping = {
                'Facebook': { input: 'social_facebook', icon: 'fab fa-facebook text-blue-600' },
                'X': { input: 'social_x', icon: 'fab fa-x-twitter text-gray-900' },
                'Instagram': { input: 'social_instagram', icon: 'fab fa-instagram text-pink-600' },
                'LinkedIn': { input: 'social_linkedin', icon: 'fab fa-linkedin text-blue-700' }
            };
            
            const dashList = document.getElementById('dash_socials_list');
            if(dashList) dashList.innerHTML = '';

            // Reset all inputs
            Object.values(mapping).forEach(m => {
                const el = document.getElementById(m.input);
                if (el) el.value = '';
            });

            // Fill
            socials.forEach(s => {
                const m = mapping[s.platform];
                if (m) {
                    const el = document.getElementById(m.input);
                    if (el) el.value = s.url;

                    if(dashList) {
                        const a = document.createElement('a');
                        a.href = s.url;
                        a.target = "_blank";
                        a.className = "w-8 h-8 rounded-lg bg-white border border-gray-100 flex items-center justify-center hover:bg-white hover:shadow-md transition-all";
                        a.innerHTML = `<i class="${m.icon} text-sm"></i>`;
                        dashList.appendChild(a);
                    }
                }
            });

            if(dashList && !socials.length) {
                dashList.innerHTML = '<span class="text-[10px] text-gray-400 italic">Aucun lien social.</span>';
            }
        }


        // --- Action Handlers ---

        function addCompanyEmail() {
            const email = document.getElementById('newEmailInput').value.trim();
            if (!email) return;
            
            const fd = new FormData();
            fd.append('action', 'add_email');
            fd.append('email', email);

            fetch('../api/entreprise_contacts.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Succès', 'Email ajouté.', 'success');
                    document.getElementById('newEmailInput').value = '';
                    loadCompanyContacts();
                } else {
                    showToast('Erreur', data.message, 'error');
                }
            });
        }

        function editEmail(id, oldEmail) {
            const newEmail = prompt("Modifier l'email :", oldEmail);
            if (!newEmail || newEmail === oldEmail) return;

            const fd = new FormData();
            fd.append('action', 'update_email');
            fd.append('id', id);
            fd.append('email', newEmail);

            fetch('../api/entreprise_contacts.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Succès', 'Email modifié.', 'success');
                    loadCompanyContacts();
                } else {
                    showToast('Erreur', data.message, 'error');
                }
            });
        }

        function deleteEmail(id) {
            showConfirmModal("Supprimer l'email", "Êtes-vous sûr de vouloir supprimer cet email ?", () => {
                const fd = new FormData();
                fd.append('action', 'delete_email');
                fd.append('id', id);

                fetch('../api/entreprise_contacts.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast('Succès', 'Email supprimé.', 'success');
                        loadCompanyContacts();
                    }
                });
            });
        }

        function addCompanyPhone() {
            const num = document.getElementById('newPhoneInput').value.trim();
            const type = document.getElementById('newPhoneType').value;
            if (!num) return;

            const fd = new FormData();
            fd.append('action', 'add_phone');
            fd.append('phone_number', num);
            fd.append('type', type);

            fetch('../api/entreprise_contacts.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Succès', 'Numéro ajouté.', 'success');
                    document.getElementById('newPhoneInput').value = '';
                    loadCompanyContacts();
                } else {
                    showToast('Erreur', data.message, 'error');
                }
            });
        }

        function editPhone(id, oldNum, oldType) {
            const newNum = prompt("Modifier le numéro :", oldNum);
            if (!newNum) return;
            
            // For simplicity, we only edit number via prompt here, type remains or we'd need a better UI
            const fd = new FormData();
            fd.append('action', 'update_phone');
            fd.append('id', id);
            fd.append('phone_number', newNum);
            fd.append('type', oldType);

            fetch('../api/entreprise_contacts.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Succès', 'Numéro modifié.', 'success');
                    loadCompanyContacts();
                }
            });
        }

        function deletePhone(id) {
            showConfirmModal("Supprimer le numéro", "Êtes-vous sûr de vouloir supprimer ce numéro ?", () => {
                const fd = new FormData();
                fd.append('action', 'delete_phone');
                fd.append('id', id);

                fetch('../api/entreprise_contacts.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast('Succès', 'Numéro supprimé.', 'success');
                        loadCompanyContacts();
                    }
                });
            });
        }

        function saveSocialLinks() {
            const fb = document.getElementById('social_facebook').value.trim();
            const x = document.getElementById('social_x').value.trim();
            const ig = document.getElementById('social_instagram').value.trim();
            const li = document.getElementById('social_linkedin').value.trim();

            const platforms = [
                { name: 'Facebook', url: fb },
                { name: 'X', url: x },
                { name: 'Instagram', url: ig },
                { name: 'LinkedIn', url: li }
            ];

            let promises = platforms.map(p => {
                const fd = new FormData();
                fd.append('action', 'save_social');
                fd.append('platform', p.name);
                fd.append('url', p.url);
                return fetch('../api/entreprise_contacts.php', { method: 'POST', body: fd }).then(r => r.json());
            });

            Promise.all(promises).then(results => {
                if (results.every(r => r.success)) {
                    showToast('Succès', 'Liens sociaux mis à jour.', 'success');
                } else {
                    showToast('Partiel', 'Certains liens n\'ont pu être enregistrés.', 'warning');
                }
                loadCompanyContacts();
            });
        }

        function saveOfficialWebsite() {
            const url = document.getElementById('mainWebsiteInput').value.trim();
            const fd = new FormData();
            fd.append('action', 'save_website');
            fd.append('url', url);

            fetch('../api/entreprise_contacts.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Succès', 'Site web officiel mis à jour.', 'success');
                    // Sync with profile tab
                    const inpWeb = document.getElementById('inp_website');
                    if (inpWeb) inpWeb.value = url;
                }
            });
        }

        function fetchDashboardData() {
            fetch('../api/enterprise_dashboard.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    populateHeader(data.user);
                    populateStats(data.stats);
                    buildActivityGraph(data.activity);
                    populateSettingsForm(data.user);
                    renderAchievements(data.achievements || [], data.user);
                } else {
                    console.error("Error fetching dashboard:", data.message);
                }
            })
            .catch(console.error);
        }

        function resetEnterprisePassword() {
            const newp = document.getElementById('inp_reset_pw').value;
            const confp = document.getElementById('inp_reset_pw_confirm').value;

            if (!newp || !confp) {
                showToast("Attention", "Veuillez saisir et confirmer le nouveau mot de passe.", "warning");
                return;
            }
            if (newp.length < 8) {
                showToast("Attention", "Le mot de passe doit contenir au moins 8 caractères.", "warning");
                return;
            }
            if (newp !== confp) {
                showToast("Erreur", "Les mots de passe ne correspondent pas.", "error");
                return;
            }

            const params = new URLSearchParams();
            params.append('action', 'reset_password_logged_in');
            params.append('new_password', newp);
            params.append('confirm_password', confp);

            fetch('../api/user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Succès', data.message || 'Mot de passe réinitialisé.', 'success');
                    document.getElementById('inp_reset_pw').value = '';
                    document.getElementById('inp_reset_pw_confirm').value = '';
                } else {
                    showToast('Erreur', data.message || 'Impossible de réinitialiser le mot de passe.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Erreur', 'Une erreur est survenue.', 'error');
            });
        }

        function renderAchievements(achievements, user) {
            const container = document.getElementById('achievementsContainer');
            if (!container) return;

            container.innerHTML = '';

            if (!achievements || achievements.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full bg-gray-50 border border-dashed border-gray-200 rounded-2xl p-5 text-sm text-gray-500 flex items-center gap-3">
                        <i class="fas fa-info-circle text-gray-400 text-lg"></i>
                        <span>Ajoutez vos sites web, projets et réalisations dans <strong>Paramètres → Réalisations & liens</strong> pour les afficher ici.</span>
                    </div>`;
                return;
            }

            const typeConfig = {
                website: { icon: 'fas fa-globe', bg: 'bg-indigo-50', color: 'text-indigo-600' },
                project: { icon: 'fas fa-folder-open', bg: 'bg-amber-50', color: 'text-amber-600' },
                achievement: { icon: 'fas fa-trophy', bg: 'bg-yellow-50', color: 'text-yellow-600' },
                linkedin: { icon: 'fab fa-linkedin', bg: 'bg-blue-50', color: 'text-blue-600' },
                other: { icon: 'fas fa-link', bg: 'bg-gray-100', color: 'text-gray-600' }
            };

            achievements.forEach((a) => {
                const cfg = typeConfig[a.type] || typeConfig.other;
                const url = (a.url || '').trim();
                const isLink = url && (url.startsWith('http') || url.startsWith('mailto:'));
                const title = (a.title || 'Sans titre').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                const desc = (a.description || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');

                if (isLink) {
                    container.insertAdjacentHTML('beforeend',
                        `<a href="${url.replace(/"/g, '&quot;')}" target="_blank" rel="noopener" class="group bg-white border border-gray-100 rounded-2xl p-4 flex items-center gap-4 hover:border-blue-200 hover:shadow-md transition">
                            <div class="w-12 h-12 rounded-xl ${cfg.bg} flex items-center justify-center ${cfg.color} shrink-0"><i class="${cfg.icon} text-lg"></i></div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-gray-900 group-hover:text-blue-700">${title}</p>
                                ${desc ? `<p class="text-xs text-gray-500 mt-0.5 line-clamp-2">${desc}</p>` : ''}
                                <p class="text-[11px] text-blue-500 mt-1 truncate group-hover:underline">${url}</p>
                            </div>
                        </a>`);
                } else {
                    container.insertAdjacentHTML('beforeend',
                        `<div class="bg-white border border-gray-100 rounded-2xl p-4 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl ${cfg.bg} flex items-center justify-center ${cfg.color} shrink-0"><i class="${cfg.icon} text-lg"></i></div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-gray-900">${title}</p>
                                ${desc ? `<p class="text-xs text-gray-500 mt-0.5 line-clamp-2">${desc}</p>` : ''}
                            </div>
                        </div>`);
                }
            });
        }

        function populateHeader(user) {
            document.getElementById('hdrCompanyName').textContent = user.nom;
            const overlayName = document.getElementById('hdrCompanyNameOverlay');
            if (overlayName) overlayName.textContent = user.nom;

            if (user.photo_profil) {
                document.getElementById('hdrProfilePic').src = '../' + user.photo_profil;
                document.getElementById('formProfileImg').src = '../' + user.photo_profil;
            } else {
                document.getElementById('formProfileImg').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.nom)}&background=random`;
            }
            
            const industry = user.industry_sector || 'Secteur Non Défini';
            document.getElementById('hdrIndustry').textContent = industry;
            const overlayInd = document.getElementById('hdrIndustryOverlay');
            if (overlayInd) overlayInd.textContent = industry.toUpperCase();

            document.getElementById('hdrEmail').textContent = user.email;

            document.getElementById('hdrPhone').textContent = user.telephone || 'Non renseigné';
            
            let loc = [];
            if(user.adresse) loc.push(user.adresse);
            if(user.ville) loc.push(user.ville);
            if(user.pays) loc.push(user.pays);
            document.getElementById('hdrLocation').textContent = loc.length > 0 ? loc.join(', ') : 'Lieu non renseigné';

            // dashboard preview sync
            const dashBio = document.getElementById('dash_bio_text');
            if(dashBio) dashBio.textContent = user.bio || 'Aucune bio renseignée pour le moment.';
            
            const dashWeb = document.getElementById('dash_website_text');
            const dashWebLink = document.getElementById('dash_website_link');
            if(dashWeb && dashWebLink) {
                dashWeb.textContent = user.website_url || 'Non spécifié';
                dashWebLink.href = user.website_url || '#';
            }
        }


        function populateStats(stats) {
            document.getElementById('statTotalApps').textContent = stats.total_applications;
            document.getElementById('statAccepted').textContent = stats.accepted;
            document.getElementById('statRejected').textContent = stats.rejected;
            document.getElementById('statPending').textContent = stats.pending;
            document.getElementById('statOffers').textContent = stats.hidden_offers !== undefined ? stats.hidden_offers : 0;
            const statFavElement = document.getElementById('statFavorites');
            if (statFavElement) {
                statFavElement.textContent = stats.total_favorites !== undefined ? stats.total_favorites : 0;
            }
        }

        function buildActivityGraph(activityMap) {
            const container = document.getElementById('activityGraph');
            const monthRow = document.getElementById('activityMonthRow');
            if (!container || !monthRow) return;

            container.innerHTML = '';
            monthRow.innerHTML = '';

            // Generate 53 weeks (371 days) ending today
            const dates = [];
            const today = new Date();
            
            // Start from the local Monday of 52 weeks ago
            const startDate = new Date(today);
            startDate.setDate(today.getDate() - 364);
            const dayShift = (startDate.getDay() === 0 ? 6 : startDate.getDay() - 1);
            startDate.setDate(startDate.getDate() - dayShift);

            for (let i = 0; i < 371; i++) {
                const d = new Date(startDate);
                d.setDate(startDate.getDate() + i);
                dates.push(d.toISOString().split('T')[0]);
            }

            const monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
            let lastMonthLabel = -1;

            for (let w = 0; w < 53; w++) {
                const weekCol = document.createElement('div');
                weekCol.className = 'flex flex-col gap-1 w-4';
                
                const firstDayIdx = w * 7;
                const weekDate = new Date(dates[firstDayIdx]);
                const curMonth = weekDate.getMonth();

                // Add month label ONLY if it's the start of a month or first week shown
                const monthLabelDiv = document.createElement('div');
                monthLabelDiv.className = 'w-4 h-full shrink-0 flex items-end overflow-visible';
                
                if (curMonth !== lastMonthLabel) {
                    const span = document.createElement('span');
                    span.textContent = monthNames[curMonth];
                    span.className = 'absolute'; // Prevent it from pushing other labels
                    monthLabelDiv.appendChild(span);
                    lastMonthLabel = curMonth;
                }
                monthRow.appendChild(monthLabelDiv);

                for (let d = 0; d < 7; d++) {
                    const idx = firstDayIdx + d;
                    if (idx >= dates.length) break;
                    
                    const dateStr = dates[idx];
                    const dayData = activityMap[dateStr] || { total: 0, accepte: 0, refuse: 0, en_attente: 0 };
                    
                    const square = document.createElement('div');
                    square.className = 'w-4 h-4 rounded-[3px] transition-all duration-200 hover:scale-110 hover:ring-2 hover:ring-blue-400/50 cursor-crosshair';
                    
                    let bgColor = 'bg-gray-100 dark:bg-gray-800/40'; // Zero activity
                    
                    if (dayData.total > 0) {
                        // Status Priority: Accepted > Refused > Pending
                        if (dayData.accepte > 0) {
                            // Green intensity (more accepted = darker/vibrant green)
                            if (dayData.accepte >= 5) bgColor = 'bg-green-600 shadow-sm shadow-green-200';
                            else if (dayData.accepte >= 2) bgColor = 'bg-green-500';
                            else bgColor = 'bg-green-400';
                        } else if (dayData.refuse > 0) {
                            // Red intensity
                            if (dayData.refuse >= 5) bgColor = 'bg-red-600 shadow-sm shadow-red-200';
                            else if (dayData.refuse >= 2) bgColor = 'bg-red-500';
                            else bgColor = 'bg-red-400';
                        } else {
                            // Orange (Pending)
                            if (dayData.en_attente >= 5) bgColor = 'bg-orange-500 shadow-sm shadow-orange-200';
                            else if (dayData.en_attente >= 2) bgColor = 'bg-orange-400';
                            else bgColor = 'bg-orange-300';
                        }
                    }
                    
                    square.className += ' ' + bgColor;
                    
                    // Tooltip
                    const dObj = new Date(dateStr);
                    const dateFmt = dObj.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' });
                    square.title = `${dateFmt} : ${dayData.total} candidature(s)\n────────────────\n✔ Acceptées : ${dayData.accepte}\n✘ Refusées : ${dayData.refuse}\n⌛ En attente : ${dayData.en_attente}`;
                    
                    weekCol.appendChild(square);
                }
                container.appendChild(weekCol);
            }

            // Auto-scroll to the end of the graph to show recent activity
            setTimeout(() => {
                const scrollParent = container.closest('.overflow-x-auto');
                if (scrollParent) {
                    scrollParent.scrollLeft = scrollParent.scrollWidth;
                }
            }, 300);
        }

        // --- Settings Panel Logic ---
        function openSettings() {
            document.body.style.overflow = 'hidden';
            document.getElementById('settingsPanel').classList.remove('translate-x-full');
        }

        function closeSettings() {
            document.body.style.overflow = 'auto';
            document.getElementById('settingsPanel').classList.add('translate-x-full');
        }

        function switchTab(tabId, btn) {
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.add('hidden'));
            document.getElementById(tabId).classList.remove('hidden');

            document.querySelectorAll('.settings-tab-btn').forEach(b => {
                b.classList.remove('bg-gray-900', 'text-white');
                b.classList.add('text-gray-600', 'hover:bg-gray-100');
            });
            btn.classList.add('bg-gray-900', 'text-white');
            btn.classList.remove('text-gray-600', 'hover:bg-gray-100');

            if (tabId === 'tab-achievements') {
                loadAchievementsList();
                loadCompanyEmails();
            }
        }

        let achievementsListData = [];

        function loadAchievementsList() {
            const listEl = document.getElementById('achievementsList');
            if (!listEl) return;
            listEl.innerHTML = '<li class="text-gray-400 text-sm py-4">Chargement...</li>';
            fetch('../api/entreprise_achievements.php')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) { listEl.innerHTML = '<li class="text-red-500 text-sm">Erreur</li>'; return; }
                    const items = data.achievements || [];
                    achievementsListData = items;
                    if (items.length === 0) {
                        listEl.innerHTML = '<li class="text-gray-500 text-sm py-4">Aucune réalisation ou lien. Ajoutez-en ci-dessus.</li>';
                        return;
                    }
                    const typeLabels = { website: 'Site web', project: 'Projet', achievement: 'Réalisation', linkedin: 'LinkedIn', other: 'Autre' };
                    listEl.innerHTML = items.map(a => {
                        const typeLabel = typeLabels[a.type] || a.type;
                        const title = (a.title || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        const url = (a.url || '').trim();
                        return `<li class="flex items-center justify-between gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-gray-800">${title}</p>
                                <p class="text-xs text-gray-500">${typeLabel}${url ? ' · ' + url : ''}</p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <button type="button" onclick="editAchievement(${a.id})" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-xs font-bold hover:bg-blue-200">Modifier</button>
                                <button type="button" onclick="deleteAchievement(${a.id})" class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-xs font-bold hover:bg-red-200">Supprimer</button>
                            </div>
                        </li>`;
                    }).join('');
                })
                .catch(() => { listEl.innerHTML = '<li class="text-red-500 text-sm">Erreur de chargement</li>'; });
        }

        window.editAchievement = function(id) {
            const a = achievementsListData.find(x => x.id == id);
            if (!a) return;
            document.getElementById('achievementId').value = a.id;
            document.getElementById('achievementType').value = a.type || 'website';
            document.getElementById('achievementTitle').value = a.title || '';
            document.getElementById('achievementUrl').value = a.url || '';
            document.getElementById('achievementDesc').value = a.description || '';
            document.getElementById('btnAchievementCancel').classList.remove('hidden');
        };

        window.deleteAchievement = function(id) {
            showConfirmModal("Supprimer l'élément", "Êtes-vous sûr de vouloir supprimer cette réalisation ou ce lien ?", () => {
                fetch('../api/entreprise_achievements.php', { method: 'DELETE', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { showToast('Succès', 'Supprimé.', 'success'); loadAchievementsList(); fetchDashboardData(); }
                        else showToast('Erreur', data.message, 'error');
                    })
                    .catch(() => showToast('Erreur', 'Erreur réseau', 'error'));
            });
        };

        // --- End of Dashboard & Contact Logic ---


        function loadCompanyEmails() {
            fetch('../api/user.php?action=get_emails')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        companyEmails = data.emails || [];
                        renderEmailsList();
                    }
                })
                .catch(() => {
                    // On error, try to get main email from current session
                    companyEmails = [];
                    renderEmailsList();
                });
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Add email input enter key handler
        document.addEventListener('DOMContentLoaded', () => {
            const emailInput = document.getElementById('newEmailInput');
            if (emailInput) {
                emailInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addCompanyEmail();
                    }
                });
            }
        });

        const formAchievement = document.getElementById('formAchievement');
        const btnAchievementCancel = document.getElementById('btnAchievementCancel');
        if (formAchievement) {
            formAchievement.addEventListener('submit', function(e) {
                e.preventDefault();
                const id = document.getElementById('achievementId').value.trim();
                const type = document.getElementById('achievementType').value;
                const title = document.getElementById('achievementTitle').value.trim();
                const url = document.getElementById('achievementUrl').value.trim();
                const description = document.getElementById('achievementDesc').value.trim();

                if (!title) { showToast('Attention', 'Le titre est obligatoire.', 'warning'); return; }

                const submitBtn = document.getElementById('btnAchievementSubmit');
                if (submitBtn) submitBtn.disabled = true;

                if (id) {
                    const body = new URLSearchParams({ id, type, title, url, description });
                    fetch('../api/entreprise_achievements.php', { method: 'PUT', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
                        .then(r => r.json())
                        .then(data => {
                            if (submitBtn) submitBtn.disabled = false;
                            if (data.success) {
                                showToast('Succès', 'Mis à jour.', 'success');
                                document.getElementById('achievementId').value = '';
                                formAchievement.reset();
                                if (btnAchievementCancel) btnAchievementCancel.classList.add('hidden');
                                loadAchievementsList();
                                fetchDashboardData();
                            } else showToast('Erreur', data.message, 'error');
                        })
                        .catch(() => { if (submitBtn) submitBtn.disabled = false; showToast('Erreur', 'Erreur réseau', 'error'); });
                } else {
                    const fd = new FormData();
                    fd.append('type', type);
                    fd.append('title', title);
                    fd.append('url', url);
                    fd.append('description', description);
                    fetch('../api/entreprise_achievements.php', { method: 'POST', body: fd })
                        .then(r => r.json())
                        .then(data => {
                            if (submitBtn) submitBtn.disabled = false;
                            if (data.success) {
                                showToast('Succès', 'Ajouté.', 'success');
                                formAchievement.reset();
                                loadAchievementsList();
                                fetchDashboardData();
                            } else showToast('Erreur', data.message, 'error');
                        })
                        .catch(() => { if (submitBtn) submitBtn.disabled = false; showToast('Erreur', 'Erreur réseau', 'error'); });
                }
            });
        }
        if (btnAchievementCancel) {
            btnAchievementCancel.addEventListener('click', function() {
                document.getElementById('achievementId').value = '';
                document.getElementById('formAchievement').reset();
                btnAchievementCancel.classList.add('hidden');
            });
        }

        function populateSettingsForm(user) {
            document.getElementById('inp_nom').value = user.nom;
            document.getElementById('inp_industry').value = user.industry_sector || '';
            document.getElementById('inp_size').value = user.company_size || user.taille || '1-10';
            document.getElementById('inp_website').value = user.website_url || user.portfolio_url || '';

            document.getElementById('inp_bio').value = user.bio || '';
            document.getElementById('inp_address').value = user.adresse || '';
            document.getElementById('inp_hr').value = user.linkedin_url || ''; 
            
            document.getElementById('inp_sec_email').value = user.email;
            document.getElementById('sec_email').textContent = user.email;
            document.getElementById('inp_sec_phone').value = user.telephone || '';

            const sigPreview = document.getElementById('signaturePreview');
            const sigPlaceholder = document.getElementById('signaturePlaceholder');
            const sigRemoveBtn = document.getElementById('signatureRemoveBtn');
            if (user.company_signature_path) {
                if (sigPreview) { sigPreview.src = '../' + user.company_signature_path; sigPreview.style.display = 'block'; }
                if (sigPlaceholder) sigPlaceholder.style.display = 'none';
                if (sigRemoveBtn) sigRemoveBtn.classList.remove('hidden');
            } else {
                if (sigPreview) { sigPreview.src = ''; sigPreview.style.display = 'none'; }
                if (sigPlaceholder) sigPlaceholder.style.display = 'inline';
                if (sigRemoveBtn) sigRemoveBtn.classList.add('hidden');
            }
        }

        function saveProfile() {
            const nom = document.getElementById('inp_nom').value;
            const industry = document.getElementById('inp_industry').value;
            const bio = document.getElementById('inp_bio').value;
            const address = document.getElementById('inp_address').value;
            const size = document.getElementById('inp_size').value;
            const website = document.getElementById('inp_website').value;
            const linkedin = document.getElementById('inp_hr').value;

            const params = new URLSearchParams();
            params.append('nom', nom);
            params.append('industry_sector', industry);
            params.append('company_size', size);
            params.append('bio', bio);
            params.append('adresse', address);
            params.append('website_url', website);
            params.append('linkedin_url', linkedin);

            fetch('../api/user.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Succès', 'Profil mis à jour avec succès.', 'success');
                    
                    // Update UI components
                    const hdrName = document.getElementById('hdrCompanyName');
                    if(hdrName) hdrName.textContent = nom;
                    const hdrInd = document.getElementById('hdrIndustry');
                    if(hdrInd) hdrInd.textContent = industry;
                    
                    // Sync dashboard preview
                    const dBio = document.getElementById('dash_bio_text');
                    if(dBio) dBio.textContent = bio || 'Aucune bio renseignée pour le moment.';
                    
                    const dWeb = document.getElementById('dash_website_text');
                    const dWebL = document.getElementById('dash_website_link');
                    if(dWeb && dWebL) {
                        dWeb.textContent = website || 'Non spécifié';
                        dWebL.href = website || '#';
                    }
                    
                    // Sync achievement website input if present
                    const mainWeb = document.getElementById('mainWebsiteInput');
                    if(mainWeb) mainWeb.value = website;

                } else {
                    showToast('Erreur', data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Erreur', 'Une erreur réseau est survenue.', 'error');
            });
        }


        function saveSecurity() {
            const oldp = document.getElementById('inp_old_pw').value;
            const newp = document.getElementById('inp_new_pw').value;
            const confp = document.getElementById('inp_new_pw_confirm').value;
            
            if (newp || oldp || confp) {
                if (!newp || !oldp || !confp) {
                    showToast("Attention", "Veuillez remplir tous les champs de mot de passe.", "warning"); 
                    return;
                }
                if (newp.length < 8) {
                    showToast("Attention", "Le nouveau mot de passe doit contenir au moins 8 caractères.", "warning");
                    return;
                }
                if (newp !== confp) {
                    showToast("Erreur", "Les nouveaux mots de passe ne correspondent pas.", "error");
                    return;
                }
            }

            const fd = new FormData();
            fd.append('action', 'update_profile_student');
            fd.append('email', document.getElementById('inp_sec_email').value);
            fd.append('telephone', document.getElementById('inp_sec_phone').value);
            
            if (newp && oldp && confp) {
                fd.append('old_password', oldp);
                fd.append('new_password', newp);
                fd.append('confirm_password', confp);
            }

            fetch('../api/user.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) showToast('Succès', 'Sécurité mise à jour !', 'success');
                else showToast('Erreur', data.message, 'error');
            })
            .catch(err => {
                console.error(err);
                showToast('Erreur', 'Une erreur est survenue.', 'error');
            });
        }

        // Modal confirm dialog system
        function showConfirmModal(title, message, onConfirm) {
            const modalId = 'confirm-modal-' + Date.now();
            const modalHtml = `
              <div id="${modalId}" class="fixed inset-0 z-[10000] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity opacity-0" id="${modalId}-backdrop"></div>
                <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-sm p-8 transform scale-95 opacity-0 transition-all duration-300 border border-gray-100" id="${modalId}-content">
                  <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center text-red-500 mb-6 mx-auto">
                    <i class="fas fa-trash-alt text-2xl"></i>
                  </div>
                  <h3 class="text-2xl font-black text-center text-gray-900 mb-2">${title}</h3>
                  <p class="text-center text-gray-500 text-sm mb-8 leading-relaxed font-medium">${message}</p>
                  <div class="flex gap-4">
                    <button type="button" class="flex-1 bg-gray-50 border border-gray-100 text-gray-600 py-3.5 rounded-xl font-bold hover:bg-gray-100 transition-colors" id="${modalId}-cancel">Annuler</button>
                    <button type="button" class="flex-1 bg-red-600 text-white py-3.5 rounded-xl font-black shadow-lg shadow-red-200 hover:bg-red-700 transition-all hover:-translate-y-1" id="${modalId}-confirm">Supprimer</button>
                  </div>
                </div>
              </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);

            const modalEl = document.getElementById(modalId);
            const backdrop = document.getElementById(`${modalId}-backdrop`);
            const content = document.getElementById(`${modalId}-content`);
            const btnCancel = document.getElementById(`${modalId}-cancel`);
            const btnConfirm = document.getElementById(`${modalId}-confirm`);

            // Animate In
            setTimeout(() => {
              backdrop.classList.remove('opacity-0');
              content.classList.remove('scale-95', 'opacity-0');
            }, 10);

            const close = () => {
              backdrop.classList.add('opacity-0');
              content.classList.add('scale-95', 'opacity-0');
              setTimeout(() => modalEl.remove(), 300);
            };

            btnCancel.addEventListener('click', close);
            backdrop.addEventListener('click', close);

            btnConfirm.addEventListener('click', () => {
              close();
              onConfirm();
            });
        }

        // Custom Toast Notification System
        function showToast(title, message, type = 'success') {
            const toastId = 'toast-' + Date.now();
            const colors = {
                'success': 'bg-green-50 border-green-200 text-green-800',
                'error': 'bg-red-50 border-red-200 text-red-800',
                'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800'
            };
            const icons = {
                'success': '<i class="fas fa-check-circle text-green-500"></i>',
                'error': '<i class="fas fa-exclamation-circle text-red-500"></i>',
                'warning': '<i class="fas fa-exclamation-triangle text-yellow-500"></i>'
            };

            const toastHtml = `
                <div id="${toastId}" class="flex items-start gap-4 p-4 mb-4 border rounded-xl shadow-lg ${colors[type]} transform translate-x-full transition-transform duration-300 ease-out" style="min-width: 300px; z-index: 10000;">
                    <div class="text-xl shrink-0 mt-0.5">${icons[type]}</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm mb-1">${title}</h4>
                        <p class="text-xs opacity-90">${message}</p>
                    </div>
                    <button onclick="document.getElementById('${toastId}').remove()" class="text-gray-400 hover:text-gray-700 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed bottom-6 right-6 z-[10000] flex flex-col gap-2';
                document.body.appendChild(container);
            }

            container.insertAdjacentHTML('beforeend', toastHtml);
            
            const toastEl = document.getElementById(toastId);
            // Trigger animation
            setTimeout(() => {
                toastEl.classList.remove('translate-x-full');
            }, 10);

            // Auto remove
            setTimeout(() => {
                if(document.getElementById(toastId)) {
                    toastEl.classList.add('translate-x-full');
                    setTimeout(() => toastEl.remove(), 300);
                }
            }, 4000);
        }
    </script>
</body>
</html>





