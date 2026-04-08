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
                                <span id="hdrCompanyNameOverlay"><?php echo htmlspecialchars($_SESSION['user_nom'] ?? 'Entreprise'); ?></span>
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
                            <img id="hdrProfilePic" class="w-full h-full rounded-xl object-cover" src="<?php echo !empty($_SESSION['photo_profil']) ? htmlspecialchars('../' . $_SESSION['photo_profil']) : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_nom'] ?? 'Entreprise') . '&background=random'; ?>" alt="Logo">
                        </div>
                        
                        <div class="flex-1 text-center md:text-left pt-4 md:pt-20">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="md:hidden"> <!-- Mobile only visible -->
                                    <h1 class="text-3xl font-extrabold text-gray-900 flex items-center justify-center md:justify-start gap-2">
                                        <span id="hdrCompanyName"><?php echo htmlspecialchars($_SESSION['user_nom'] ?? 'Entreprise'); ?></span>
                                        <?php if (!empty($_SESSION['verified_status'])): ?>
                                            <i class="fas fa-check-circle text-yellow-500" title="This company is officially verified"></i>
                                        <?php endif; ?>
                                    </h1>
                                    <p class="text-blue-600 font-semibold text-sm tracking-wide uppercase mt-1" id="hdrIndustry">Téléchargement...</p>
                                </div>
                                <div class="hidden md:block"></div> <!-- Spacer for desktop since title moved up -->

                                <div class="flex space-x-3">
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
                    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-8 text-center">
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex flex-col justify-center items-center h-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-inbox text-gray-400 mb-2 text-xl"></i>
                            <p class="text-2xl font-black text-gray-900" id="statTotalApps">0</p>
                            <p class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wide mt-1 break-words w-full leading-tight">Candidatures (Total)</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-xl border border-green-100 flex flex-col justify-center items-center h-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-check-circle text-green-500 mb-2 text-xl"></i>
                            <p class="text-2xl font-black text-green-700" id="statAccepted">0</p>
                            <p class="text-[10px] sm:text-xs font-bold text-green-600 uppercase tracking-wide mt-1 break-words w-full leading-tight">Acceptés</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-xl border border-red-100 flex flex-col justify-center items-center h-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-times-circle text-red-500 mb-2 text-xl"></i>
                            <p class="text-2xl font-black text-red-700" id="statRejected">0</p>
                            <p class="text-[10px] sm:text-xs font-bold text-red-600 uppercase tracking-wide mt-1 break-words w-full leading-tight">Refusés</p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-xl border border-orange-100 flex flex-col justify-center items-center h-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-clock text-orange-500 mb-2 text-xl"></i>
                            <p class="text-2xl font-black text-orange-700" id="statPending">0</p>
                            <p class="text-[10px] sm:text-xs font-bold text-orange-600 uppercase tracking-wide mt-1 break-words w-full leading-tight">En Attente</p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col justify-center items-center h-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-eye-slash text-slate-500 mb-2 text-xl" title="Offres masquées"></i>
                            <p class="text-2xl font-black text-slate-700" id="statOffers">0</p>
                            <p class="text-[10px] sm:text-xs font-bold text-slate-600 uppercase tracking-wide mt-1 break-words w-full leading-tight">Offres Masquées</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-xl border border-purple-100 flex flex-col justify-center items-center h-full hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-chart-pie text-purple-500 mb-2 text-xl"></i>
                            <p class="text-2xl font-black text-purple-700" id="statRatio">0.0</p>
                            <p class="text-[10px] sm:text-xs font-bold text-purple-600 uppercase tracking-wide mt-1 break-words w-full leading-tight">Candidatures / Offre</p>
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

                    <!-- Company Achievements / Links -->
                    <div class="mt-12 pt-8 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-trophy text-yellow-500"></i>
                                Réalisations & Présence en ligne
                            </h3>
                            <button onclick="openSettings(); switchTab('tab-achievements', document.querySelector('[onclick*=\"tab-achievements\"]'))" class="text-xs font-bold text-gray-500 hover:text-blue-600 transition">
                                <i class="fas fa-edit mr-1"></i> Gérer
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
                    <h2 class="text-2xl font-black text-gray-900"><i class="fas fa-sliders-h text-blue-600 mr-2"></i> Paramètres Entreprise</h2>
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
                                <i class="fas fa-cog w-6"></i> Préférences
                            </button>
                            <button onclick="switchTab('tab-achievements', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl font-bold text-gray-600 hover:bg-gray-100 transition">
                                <i class="fas fa-trophy w-6"></i> Réalisations & liens
                            </button>
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
                                        <div class="w-24 h-24 rounded-2xl bg-gray-100 border border-gray-200 overflow-hidden shrink-0">
                                            <img id="formProfileImg" class="w-full h-full object-cover" src="" alt="Photo">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Logo de l'entreprise</label>
                                            <input type="file" id="logo_upload" class="hidden" accept="image/*">
                                            <label for="logo_upload" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold hover:bg-gray-50 cursor-pointer shadow-sm">Changer le logo</label>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Nom de l'entreprise</label>
                                        <input type="text" id="inp_nom" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition" readonly>
                                        <p class="text-xs text-gray-400 mt-1">Le nom légal ne peut être modifié que via le support admin (Vérifié).</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Secteur d'activité</label>
                                        <input type="text" id="inp_industry" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition">
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
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                
                                <div class="bg-white p-6 rounded-xl border border-gray-200 flex justify-between items-center shadow-sm">
                                    <div>
                                        <h4 class="font-bold text-gray-800">Assignation automatique des Superviseurs</h4>
                                        <p class="text-sm text-gray-500 mt-1">Assigne le créateur de l'offre comme recruteur par défaut.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" class="sr-only peer" checked disabled>
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
                                      <input type="checkbox" class="sr-only peer" checked>
                                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex justify-between items-center">
                                    <div>
                                        <p class="font-bold text-gray-800">Alertes Entretiens</p>
                                        <p class="text-xs text-gray-500">Rappels pour les rdv (Intégration Calendar).</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" class="sr-only peer" checked>
                                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex justify-between items-center">
                                    <div>
                                        <p class="font-bold text-gray-800">Messagerie Interne</p>
                                        <p class="text-xs text-gray-500">Permettre aux étudiants approuvés de m'envoyer des messages.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" class="sr-only peer">
                                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                <div class="flex justify-end pt-4">
                                    <button class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition shadow-md">Appliquer</button>
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
                                    <button class="bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-bold text-sm hover:bg-gray-200 transition border border-gray-200 flex items-center gap-2">
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
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-3">Vos réalisations et liens</h4>
                                    <ul id="achievementsList" class="space-y-3"></ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            
        </main>
    </div>

    <!-- Logic Script -->
    <script>
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
        });

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
        }

        function populateStats(stats) {
            document.getElementById('statTotalApps').textContent = stats.total_applications;
            document.getElementById('statAccepted').textContent = stats.accepted;
            document.getElementById('statRejected').textContent = stats.rejected;
            document.getElementById('statPending').textContent = stats.pending;
            document.getElementById('statOffers').textContent = stats.hidden_offers !== undefined ? stats.hidden_offers : 0;
            document.getElementById('statRatio').textContent = parseFloat(stats.apps_per_offer).toFixed(1);
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

            if (tabId === 'tab-achievements') loadAchievementsList();
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
            if (!confirm('Supprimer cette réalisation ou ce lien ?')) return;
            fetch('../api/entreprise_achievements.php', { method: 'DELETE', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { showToast('Succès', 'Supprimé.', 'success'); loadAchievementsList(); fetchDashboardData(); }
                    else showToast('Erreur', data.message, 'error');
                })
                .catch(() => showToast('Erreur', 'Erreur réseau', 'error'));
        };

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
            document.getElementById('inp_size').value = user.company_size || '1-10';
            document.getElementById('inp_website').value = user.portfolio_url || '';
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
            const fd = new FormData();
            fd.append('action', 'update_profile_student'); // reusing the same update endpoint in user.php for simplicity
            
            fd.append('nom', document.getElementById('inp_nom').value);
            // fd.append('industry_sector', document.getElementById('inp_industry').value); // user.php limits fields, let's map what we can
            fd.append('bio', document.getElementById('inp_bio').value);
            // We map HR to linkedin_url
            fd.append('linkedin_url', document.getElementById('inp_hr').value);
            // We map website to portfolio_url
            fd.append('portfolio_url', document.getElementById('inp_website').value);

            // We do a PUT request for the standard allowed fields as per api/user.php -> PUT 
            // the PUT only supports allowed_fields, but POST 'update_profile_student' does not update all. We use PUT to be clean.
            
            const params = new URLSearchParams();
            params.append('bio', document.getElementById('inp_bio').value);
            params.append('adresse', document.getElementById('inp_address').value);
            params.append('telephone', document.getElementById('inp_sec_phone').value);
            params.append('linkedin_url', document.getElementById('inp_hr').value);
            params.append('portfolio_url', document.getElementById('inp_website').value);

            fetch('../api/user.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Succès', 'Profil mis à jour ! Rechargez la page pour voir les modifications.', 'success');
                } else {
                    showToast('Erreur', data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Erreur', 'Une erreur est survenue.', 'error');
            });
        }

        function saveSecurity() {
            const oldp = document.getElementById('inp_old_pw').value;
            const newp = document.getElementById('inp_new_pw').value;
            
            if (newp && !oldp) {
                showToast("Attention", "Veuillez entrer votre mot de passe actuel pour le changer.", "warning"); return;
            }

            const fd = new FormData();
            fd.append('action', 'update_profile_student');
            fd.append('email', document.getElementById('inp_sec_email').value);
            fd.append('telephone', document.getElementById('inp_sec_phone').value);
            
            if (newp && oldp) {
                fd.append('old_password', oldp);
                fd.append('new_password', newp);
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





