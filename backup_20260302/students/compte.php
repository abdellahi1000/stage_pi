<?php 
require_once '../include/session.php';
check_auth('etudiant');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/compte.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js?v=<?= time() ?>" defer></script>
    <script src="../js/compte.js?v=<?= time() ?>" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto w-full md:ml-64 bg-gray-50/50">
            <!-- Mobile Navigation Bar -->
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-blue-600">StageMatch</span>
                </div>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>

            <div class="max-w-5xl mx-auto px-6 py-10">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-2">Mon Compte</h1>
                        <p class="text-gray-500 text-sm md:text-base font-medium leading-relaxed">Gérez votre profil numérique et vos préférences.</p>
                    </div>
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <button id="darkModeToggle" class="flex-1 md:flex-none flex items-center justify-center space-x-2 bg-white px-5 py-2.5 rounded-2xl shadow-sm border border-gray-100 hover:bg-gray-50 transition text-gray-700 font-bold active:scale-95">
                            <i id="darkModeIcon" class="fas fa-moon text-blue-600"></i>
                            <span class="text-sm">Mode Sombre</span>
                        </button>
                        <button id="settingsToggle" class="flex items-center justify-center w-11 h-11 bg-blue-600 rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition text-white active:scale-95">
                            <i class="fas fa-cog text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- STUDENT PROFILE HEADER: Space Adjusted & Fixed Visibility -->
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden mb-12 relative mt-4">
                    <!-- Premium Background Component: Increased height to H-64 -->
                    <div class="h-64 w-full relative overflow-hidden" style="background: linear-gradient(to right, #0d1b2a, #1b4f72);">
                         <!-- Texture: Small Modern Dots -->
                        <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(#fff 1px, transparent 0); background-size: 20px 20px;"></div>
                        
                        <!-- Light Bloom: Top Right Glowing Circle -->
                        <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full blur-[100px] opacity-[0.15]" style="background-color: #56ccf2;"></div>
                        
                        <!-- Profile Highlight: Left Radial Glow -->
                        <div class="absolute -bottom-10 left-10 w-64 h-64 rounded-full blur-[80px] opacity-[0.20]" style="background-color: #3498db;"></div>
                        
                        <!-- Dynamic Element: Soft Abstract Bottom Wave -->
                        <svg class="absolute bottom-0 left-0 w-full h-24 opacity-[0.15]" viewBox="0 0 1440 320" preserveAspectRatio="none">
                            <path fill="#2980b9" d="M0,224L48,213.3C96,203,192,181,288,186.7C384,192,480,224,576,213.3C672,203,768,149,864,128C960,107,1056,117,1152,144C1248,171,1344,213,1392,234.7L1440,256L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                        </svg>

                        <!-- Identity Info Overlay (For desktop readability) -->
                        <div class="absolute bottom-10 left-60 right-8 z-30 hidden md:block">
                            <div class="flex items-end justify-between gap-6">
                                <div>
                                    <h1 class="text-4xl font-black text-white drop-shadow-lg tracking-tight mb-2">
                                        <span id="display-name"><?php echo htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')); ?></span>
                                    </h1>
                                    <p class="text-blue-200 font-black text-sm uppercase tracking-[0.2em] flex items-center gap-2 drop-shadow-md">
                                        <i class="fas fa-graduation-cap text-blue-400"></i> <span id="display-specialite">Étudiant Passionné</span>
                                    </p>
                                </div>
                                <div class="flex gap-3">
                                    <div class="px-5 py-2.5 bg-white/5 backdrop-blur-md text-white rounded-2xl text-xs font-black tracking-wider border border-white/10 flex items-center gap-3 shadow-2xl">
                                        <i class="fas fa-envelope text-blue-400"></i> <?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>
                                    </div>
                                    <div class="px-5 py-2.5 bg-white/5 backdrop-blur-md text-white rounded-2xl text-xs font-black tracking-wider border border-white/10 flex items-center gap-3 shadow-2xl">
                                        <i class="fas fa-phone text-blue-400"></i> <?php echo htmlspecialchars($_SESSION['user_tel'] ?? 'Non défini'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-8 pb-8 flex flex-col md:flex-row items-center md:items-start gap-8 relative -mt-24">
                        <!-- Profile pic with shadow & glow -->
                        <div class="relative group">
                            <div class="w-44 h-44 rounded-3xl bg-white p-2 shadow-2xl border border-gray-100/30 shrink-0 relative z-40 overflow-hidden">
                                <img id="profil-img" class="w-full h-full rounded-2xl object-cover transition-transform duration-500 group-hover:scale-110" src="<?php echo !empty($_SESSION['photo_profil']) ? htmlspecialchars('../' . $_SESSION['photo_profil']) : 'https://ui-avatars.com/api/?name=' . urlencode(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) . '&background=random'; ?>" alt="Photo de profil">
                            </div>
                            <div class="absolute inset-0 bg-blue-400 blur-3xl opacity-20 group-hover:opacity-40 transition-opacity rounded-full z-10 scale-125"></div>
                        </div>
                        
                        <!-- Mobile Identity (Visible only on small screens) -->
                        <div class="flex-1 text-center md:hidden pt-4 z-30">
                            <h1 class="text-3xl font-black text-gray-900 leading-tight mb-2">
                                <span><?php echo htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')); ?></span>
                            </h1>
                            <p class="text-blue-600 font-bold text-sm tracking-widest uppercase mb-6">
                                <i class="fas fa-graduation-cap"></i> <span>Étudiant Passionné</span>
                            </p>
                            <div class="flex flex-col gap-3">
                                <div class="px-4 py-3 bg-gray-50 text-gray-700 rounded-xl text-xs font-bold border border-gray-100 flex items-center justify-center gap-2">
                                    <i class="fas fa-envelope text-blue-500"></i> <?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>
                                </div>
                                <div class="px-4 py-3 bg-gray-50 text-gray-700 rounded-xl text-xs font-bold border border-gray-100 flex items-center justify-center gap-2">
                                    <i class="fas fa-phone text-blue-500"></i> <?php echo htmlspecialchars($_SESSION['user_tel'] ?? 'Non défini'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Right Content (Details) -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Bio Section -->
                        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
                            <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2 uppercase tracking-wider text-sm">
                                <i class="fas fa-user-tag text-blue-500"></i> À propos de moi
                            </h2>
                            <p id="display-bio" class="text-gray-600 leading-relaxed italic">
                                "Ajoutez une description dans vos paramètres pour vous présenter aux recruteurs..."
                            </p>
                        </div>

                        <!-- Info Grid -->
                        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
                            <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2 uppercase tracking-wider text-sm">
                                <i class="fas fa-id-card text-blue-500"></i> Informations Détaillées
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Niveau d'études</label>
                                        <p id="display-niveau" class="text-lg font-bold text-gray-800">Non précisé</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Disponibilité</label>
                                        <span id="display-statut" class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold uppercase">Disponible</span>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Domaine</label>
                                        <p id="display-domaine" class="text-lg font-bold text-gray-800">Non précisé</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Localisation</label>
                                        <p id="display-ville" class="text-lg font-bold text-gray-800">Nouakchott</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Left Content (Skills & CV) -->
                    <div class="lg:col-span-1 space-y-8">
                         <!-- Skills Card -->
                        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
                            <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2 uppercase tracking-wider text-sm">
                                <i class="fas fa-bolt text-blue-500"></i> Compétences
                            </h2>
                            <div id="display-skills" class="flex flex-wrap gap-2">
                                <span class="px-3 py-1.5 bg-gray-50 text-gray-500 rounded-xl text-xs font-bold border border-gray-100">Aucune compétence listée</span>
                            </div>
                        </div>

                         <!-- Quick Actions / CV -->
                         <div class="bg-gradient-to-br from-gray-900 to-blue-900 p-8 rounded-[2rem] shadow-xl text-white relative overflow-hidden">
                            <div class="relative z-10">
                                <h2 class="text-xl font-black mb-4 flex items-center gap-2">
                                    <i class="fas fa-file-pdf"></i> Votre CV
                                </h2>
                                <p class="text-blue-200 text-sm mb-6 leading-relaxed">Assurez-vous que votre CV est à jour pour maximiser vos chances.</p>
                                <a href="create_cv.php" class="block w-full text-center py-3 bg-white text-gray-900 rounded-xl font-black text-sm uppercase tracking-widest hover:bg-blue-50 transition shadow-lg">
                                    Mettre à jour le CV
                                </a>
                            </div>
                            <i class="fas fa-file-alt absolute -bottom-4 -right-4 text-8xl text-white/5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Settings Sidebar/Modal -->
    <div id="settingsSidebar" class="fixed inset-y-0 right-0 w-full md:w-[600px] bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h2 class="text-xl font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-sliders-h text-blue-600"></i> Paramètres du Profil
            </h2>
            <button id="closeSettings" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-600 hover:bg-red-100 hover:text-red-600 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Progress Indicator -->
        <div class="px-6 py-4 border-b border-gray-100 bg-white">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Complétion du profil</span>
                <span class="text-sm font-black text-blue-600" id="profileProgressText">60%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full" id="profileProgressBar" style="width: 60%"></div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
            <!-- Tab Menu -->
            <div class="w-full md:w-56 bg-gray-50/50 border-b md:border-b-0 md:border-r border-gray-100 flex md:flex-col overflow-x-auto md:overflow-x-visible no-scrollbar">
                <button class="settings-tab active text-left px-5 py-4 text-xs font-black uppercase tracking-widest text-gray-500 hover:bg-white hover:text-blue-600 border-b-2 md:border-b-0 md:border-l-4 border-transparent flex items-center justify-center md:justify-start gap-4 whitespace-nowrap min-w-max transition-all" data-tab="tab-profile">
                    <i class="fas fa-user-circle text-lg w-5 text-center"></i> <span>Profil</span>
                </button>
                <button class="settings-tab text-left px-5 py-4 text-xs font-black uppercase tracking-widest text-gray-500 hover:bg-white hover:text-blue-600 border-b-2 md:border-b-0 md:border-l-4 border-transparent flex items-center justify-center md:justify-start gap-4 whitespace-nowrap min-w-max transition-all" data-tab="tab-security">
                    <i class="fas fa-shield-alt text-lg w-5 text-center"></i> <span>Sécurité</span>
                </button>
                <button class="settings-tab text-left px-5 py-4 text-xs font-black uppercase tracking-widest text-gray-500 hover:bg-white hover:text-blue-600 border-b-2 md:border-b-0 md:border-l-4 border-transparent flex items-center justify-center md:justify-start gap-4 whitespace-nowrap min-w-max transition-all" data-tab="tab-training">
                    <i class="fas fa-graduation-cap text-lg w-5 text-center"></i> <span>Dossier</span>
                </button>
                <button class="settings-tab text-left px-5 py-4 text-xs font-black uppercase tracking-widest text-gray-500 hover:bg-white hover:text-blue-600 border-b-2 md:border-b-0 md:border-l-4 border-transparent flex items-center justify-center md:justify-start gap-4 whitespace-nowrap min-w-max transition-all" data-tab="tab-academic">
                    <i class="fas fa-cog text-lg w-5 text-center"></i> <span>Préférences</span>
                </button>
            </div>

            <!-- Tab Contents -->
            <div class="flex-1 overflow-y-auto bg-white p-6" id="settingsForms">
                <form id="form-settings" class="space-y-6">
                    
                    <!-- TAB 1: Profil -->
                    <div id="tab-profile" class="settings-pane block">
                        <h3 class="text-lg font-black text-gray-800 mb-4 border-b pb-2">Modifier le Profil</h3>
                        
                        <!-- Photo Section -->
                        <div class="flex items-center gap-6 mb-6 p-4 bg-blue-50 rounded-2xl border border-blue-100">
                            <div class="relative w-20 h-20 shrink-0">
                                <img id="settings-profil-preview" class="w-full h-full rounded-2xl object-cover border-2 border-white shadow-md" src="<?php echo !empty($_SESSION['photo_profil']) ? htmlspecialchars('../' . $_SESSION['photo_profil']) : 'https://ui-avatars.com/api/?name=' . urlencode(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) . '&background=random'; ?>" alt="Preview">
                            </div>
                            <div>
                                <h4 class="font-black text-gray-900 text-sm mb-1">Photo de profil</h4>
                                <label for="photo-input" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold uppercase tracking-widest cursor-pointer hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                                    Changer la photo
                                </label>
                                <input type="file" id="photo-input" name="photo_profil" accept="image/*" class="hidden">
                            </div>
                        </div>

                        <div class="grid-2-cols mb-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nom de famille</label>
                                <input type="text" name="nom" class="w-full bg-gray-50/50 border border-gray-200 rounded-2xl px-4 py-3 text-sm font-bold focus:border-blue-500 focus:bg-white transition-all outline-none" value="<?php echo htmlspecialchars($_SESSION['user_nom'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Prénoms</label>
                                <input type="text" name="prenom" class="w-full bg-gray-50/50 border border-gray-200 rounded-2xl px-4 py-3 text-sm font-bold focus:border-blue-500 focus:bg-white transition-all outline-none" value="<?php echo htmlspecialchars($_SESSION['user_prenom'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Description / À propos (Bio)</label>
                            <textarea name="bio" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:bg-white transition" placeholder="Parlez brièvement de vous..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Titre professionnel</label>
                            <input type="text" name="titre_professionnel" id="titre_professionnel" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:bg-white transition" placeholder="Ex: Développeur Web Junior...">
                        </div>
                    </div>

                    <!-- TAB 2: Sécurité -->
                    <div id="tab-security" class="settings-pane hidden">
                        <h3 class="text-lg font-black text-gray-800 mb-4 border-b pb-2">Sécurité & Compte</h3>
                        
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Adresse Email</label>
                            <input type="email" name="email" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:bg-white transition" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Téléphone</label>
                            <input type="tel" name="telephone" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:bg-white transition" value="<?php echo htmlspecialchars($_SESSION['user_tel'] ?? ''); ?>">
                        </div>
                        <div class="border-t border-gray-100 my-4 pt-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Changer le mot de passe</label>
                            <div class="space-y-3">
                                <input type="password" name="old_password" placeholder="Mot de passe actuel" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:bg-white transition">
                                <input type="password" name="new_password" placeholder="Nouveau mot de passe" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:bg-white transition">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Dossier Professionnel (CV & Letters) -->
                    <div id="tab-training" class="settings-pane hidden">
                        <h3 class="text-lg font-black text-gray-800 mb-6 border-b pb-2 flex items-center gap-2">
                             <i class="fas fa-folder-open text-blue-600"></i> Dossier Professionnel
                        </h3>
                        
                        <!-- CV Manager -->
                        <div class="mb-8 p-6 bg-blue-50/50 rounded-[2rem] border border-blue-100/50">
                            <label class="block text-xs font-black text-blue-900 uppercase tracking-widest mb-4 flex items-center justify-between">
                                <span class="flex items-center gap-2"><i class="fas fa-file-pdf text-blue-500 text-lg"></i> Mes CV</span>
                                <span class="text-[10px] bg-blue-100 text-blue-600 px-3 py-1 rounded-full font-black">PDF / DOCX</span>
                            </label>
                            
                            <div id="cv_manager_list" class="space-y-2 mb-4">
                                <div class="py-10 text-center">
                                    <i class="fas fa-cloud-download-alt text-blue-200 text-4xl mb-3"></i>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Chargement des documents...</p>
                                </div>
                            </div>

                            <input type="file" id="cv_upload_input" class="hidden" accept=".pdf,.doc,.docx">
                            <button type="button" onclick="document.getElementById('cv_upload_input').click()" class="w-full flex flex-col items-center justify-center gap-1 py-4 border-2 border-dashed border-blue-200 rounded-2xl bg-white group hover:border-blue-600 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 active:scale-[0.98]">
                                <i class="fas fa-plus-circle text-blue-300 group-hover:text-blue-600 text-2xl transition-all"></i>
                                <span class="text-[10px] font-black text-blue-400 group-hover:text-blue-700 uppercase tracking-widest">Ajouter un nouveau CV</span>
                            </button>
                        </div>

                        <!-- Motivation Letter Manager -->
                        <div class="mb-8 p-6 bg-purple-50/50 rounded-[2rem] border border-purple-100/50">
                            <label class="block text-xs font-black text-purple-900 uppercase tracking-widest mb-4 flex items-center justify-between">
                                <span class="flex items-center gap-2"><i class="fas fa-file-contract text-purple-500 text-lg"></i> Lettres de motivation</span>
                                <span class="text-[10px] bg-purple-100 text-purple-600 px-3 py-1 rounded-full font-black">MODÈLES</span>
                            </label>
                            
                            <div id="lm_manager_list" class="space-y-2 mb-4">
                                <div class="py-10 text-center">
                                    <i class="fas fa-envelope-open-text text-purple-200 text-4xl mb-3"></i>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Chargement...</p>
                                </div>
                            </div>

                            <input type="file" id="lm_upload_input" class="hidden" accept=".pdf,.doc,.docx">
                            <button type="button" onclick="document.getElementById('lm_upload_input').click()" class="w-full flex flex-col items-center justify-center gap-1 py-4 border-2 border-dashed border-purple-200 rounded-2xl bg-white group hover:border-purple-600 hover:shadow-xl hover:shadow-purple-500/10 transition-all duration-300 active:scale-[0.98]">
                                <i class="fas fa-plus-circle text-purple-300 group-hover:text-purple-600 text-2xl transition-all"></i>
                                <span class="text-[10px] font-black text-purple-400 group-hover:text-purple-700 uppercase tracking-widest">Ajouter une lettre</span>
                            </button>
                        </div>
                    </div>

                    <!-- TAB 4: Profil & Formation Info -->
                    <div id="tab-academic" class="settings-pane hidden">
                        <h3 class="text-lg font-black text-gray-800 mb-6 border-b pb-2">Détails Académiques</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Niveau d'études</label>
                                <select name="niveau_etudes" id="niveau_etudes" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:border-blue-500 focus:bg-white transition-all outline-none">
                                    <option value="">Sélectionner...</option>
                                    <option value="L1">Licence 1 (L1)</option>
                                    <option value="L2">Licence 2 (L2)</option>
                                    <option value="L3">Licence 3 (L3)</option>
                                    <option value="M1">Master 1 (M1)</option>
                                    <option value="M2">Master 2 (M2)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Spécialité</label>
                                <input type="text" name="specialite" id="specialite" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:border-blue-500 focus:bg-white transition-all outline-none" placeholder="Ex: Informatique">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Domaine de formation</label>
                            <select name="domaine_formation" id="domaine_formation" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:border-blue-500 focus:bg-white transition-all outline-none">
                                <option value="">Choisir un domaine...</option>
                                <option value="Developpement Web">Développement Web</option>
                                <option value="Intelligence Artificielle">Intelligence Artificielle</option>
                                <option value="Reseaux">Réseaux / Télécoms</option>
                                <option value="Cybersecurite">Cybersécurité</option>
                                <option value="Design">Design / UI/UX</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Compétences Techniques</label>
                            <input type="text" name="skills" id="skills" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:border-blue-500 focus:bg-white transition-all outline-none" placeholder="PHP, React, MySQL...">
                        </div>

                        <div class="mb-6">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Statut de Disponibilité</label>
                            <select name="statut_disponibilite" id="statut_disponibilite" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:border-blue-500 focus:bg-white transition-all outline-none">
                                <option value="disponible">Disponible immédiatement</option>
                                <option value="en_formation">En formation</option>
                                <option value="recherche_active">En recherche active</option>
                            </select>
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                             <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Alertes Nouveaux Stages</p>
                                    <p class="text-xs text-gray-500">M'informer des nouvelles offres</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                  <input type="checkbox" name="alertes_offres" id="alertes_offres" class="sr-only peer" checked>
                                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 z-10 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)]">
            <button type="button" id="cancelSettings" class="px-5 py-2 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-200 transition">Annuler</button>
            <button type="button" id="saveSettings" class="px-6 py-3 rounded-2xl text-sm font-black bg-blue-600 text-white shadow-xl shadow-blue-200 hover:bg-blue-700 hover:-translate-y-1 transition-all duration-300 flex items-center gap-2 active:scale-95">
                <i class="fas fa-save text-lg"></i> SAUVEGARDER
            </button>
        </div>
    </div>

    <!-- Background Overlay -->
    <div id="settingsOverlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-40 hidden opacity-0 transition-all duration-300"></div>

</body>
</html>
