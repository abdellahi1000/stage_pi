<?php 
require_once '../include/session.php';
require_once '../include/lookups.php';
check_auth('entreprise', 'Administrator');

// Note: $_SESSION['user_id'] is the ID of the manager
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - Espace Administrateur</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/admin_account.js?v=<?php echo time(); ?>" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto bg-gray-50 md:ml-64">
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <span class="font-bold text-blue-600">StageMatch Admin</span>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>

            <div class="max-w-4xl mx-auto px-6 py-10">
                <div class="mb-10 flex items-center justify-between bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm px-8">
                    <a href="my_company.php" class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-all border border-gray-100" title="Retour">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="text-center flex-1">
                        <h1 class="text-3xl font-black text-gray-900 leading-tight">Mon Compte Personnel</h1>
                        <p class="text-[10px] text-gray-400 font-extrabold uppercase tracking-[0.2em] mt-1">Paramètres de l'administrateur</p>
                    </div>
                    <a href="settings.php" class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center hover:bg-blue-100 transition-all border border-blue-100" title="Paramètres Système">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-8">
                    <!-- Personal Info Card -->
                    <div class="bg-white rounded-[2.5rem] p-10 border border-gray-100 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                            <i class="fas fa-user-shield text-8xl"></i>
                        </div>

                        <div class="relative z-10">
                            <h2 class="text-2xl font-black text-gray-900 mb-8 flex items-center gap-3">
                                <span class="w-2 h-8 bg-blue-600 rounded-full"></span>
                                Informations de Connexion
                            </h2>

                            <form id="formAccount" class="space-y-6">
                                <div class="flex flex-col md:flex-row items-center gap-10 mb-10 pb-10 border-b border-gray-50">
                                    <div class="relative group">
                                        <div class="w-32 h-32 bg-gray-100 rounded-[2.5rem] overflow-hidden border-4 border-white shadow-xl relative">
                                            <img id="accPhotoPreview" src="../img/default_avatar.png" class="w-full h-full object-cover">
                                            <label for="accPhotoInput" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white cursor-pointer">
                                                <i class="fas fa-camera text-2xl"></i>
                                            </label>
                                        </div>
                                        <input type="file" id="accPhotoInput" name="photo" class="hidden" accept="image/*">
                                    </div>
                                    <div class="text-center md:text-left">
                                        <h3 id="accDisplayName" class="text-3xl font-black text-gray-900 mb-2">Chargement...</h3>
                                        <p class="text-gray-500 font-medium">Administrateur de l'entreprise</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">Prénom Personnel</label>
                                        <input type="text" name="prenom" id="accPrenom" class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-bold">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">Nom de Famille (Personnel)</label>
                                        <input type="text" name="nom" id="accNom" class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-bold">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">Adresse Email</label>
                                        <input type="email" name="email" id="accEmail" required class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-bold">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">Numéro de Téléphone</label>
                                        <input type="tel" name="telephone" id="accTel" class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-bold">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">À propos de vous</label>
                                    <textarea name="bio" id="accBio" rows="3" class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all resize-none"></textarea>
                                </div>

                                <div class="pt-6 border-t border-gray-50">
                                    <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                                        <i class="fas fa-key text-blue-600 text-sm"></i>
                                        Changer votre Mot de Passe Administrateur
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Mot de Passe Actuel</label>
                                            <input type="password" name="current_password" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Nouveau Mot de Passe</label>
                                            <input type="password" name="new_password" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Confirmation</label>
                                            <input type="password" name="confirm_password" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                                        </div>
                                    </div>
                                    
                                    <p class="mt-4 text-[10px] text-gray-400 font-medium">Laissez les champs de mot de passe vides si vous souhaitez uniquement modifier votre email.</p>
                                </div>

                                <div class="flex justify-end pt-8">
                                    <button type="submit" class="px-10 py-4 bg-gray-900 text-white rounded-2xl font-black shadow-xl shadow-gray-200 hover:bg-black hover:-translate-y-1 transition-all">
                                        Enregistrer les Modifications
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
