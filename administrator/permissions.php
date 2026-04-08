<?php 
require_once '../include/session.php';
require_once '../include/lookups.php';
check_auth('admin');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Permissions - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/admin_permissions.js?v=<?php echo time(); ?>" defer></script>
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

            <div class="max-w-5xl mx-auto px-6 py-10">
                <div class="mb-10">
                    <h1 class="text-4xl font-extrabold text-gray-900 mb-1">Gestion des Permissions</h1>
                    <p class="text-gray-500 font-medium">Contrôlez les droits d'accès de vos collaborateurs Entreprise.</p>
                </div>

                <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b border-gray-50">
                                    <th class="pb-6 text-[10px] font-black text-gray-400 uppercase tracking-widest px-4">Utilisateur</th>
                                    <th class="pb-6 text-[10px] font-black text-gray-400 uppercase tracking-widest px-4 text-center">Création Offres</th>
                                    <th class="pb-6 text-[10px] font-black text-gray-400 uppercase tracking-widest px-4 text-center">Modif. Offres</th>
                                    <th class="pb-6 text-[10px] font-black text-gray-400 uppercase tracking-widest px-4 text-center">Suppr. Offres</th>
                                    <th class="pb-6 text-[10px] font-black text-gray-400 uppercase tracking-widest px-4 text-center">Gestion Candidats</th>
                                    <th class="pb-6 text-[10px] font-black text-gray-400 uppercase tracking-widest px-4 text-center">Blocage Utilisateurs</th>
                                    <th class="pb-6 text-[10px] font-black text-gray-400 uppercase tracking-widest px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="permissionsTableBody" class="divide-y divide-gray-50">
                                <!-- Dynamic -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="noUsersMsg" class="hidden py-20 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mx-auto mb-4">
                            <i class="fas fa-users-slash text-2xl"></i>
                        </div>
                        <p class="text-gray-400 font-medium">Aucun autre collaborateur trouvé dans votre entreprise.</p>
                    </div>
                </div>
                
                <div class="mt-8 bg-blue-50 border border-blue-100 p-6 rounded-3xl">
                    <h3 class="font-black text-blue-900 mb-2 flex items-center gap-2 italic">
                        <i class="fas fa-info-circle"></i>
                        À propos des permissions
                    </h3>
                    <p class="text-blue-700 text-sm leading-relaxed">
                        En tant qu'administrateur, vous pouvez déléguer la gestion des offres à vos collaborateurs. 
                        Un utilisateur sans permission de création ne pourra pas voir le bouton "Publier". 
                        L'administrateur principal conserve toujours tous les droits sur le compte entreprise.
                    </p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
