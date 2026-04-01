<?php 
require_once '../include/session.php';
require_once '../include/lookups.php';
check_auth('entreprise', 'Administrator');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étudiants Bloqués - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/admin_blocked_students.js?v=<?php echo time(); ?>" defer></script>
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
                    <h1 class="text-4xl font-extrabold text-gray-900 mb-1">Étudiants Bloqués</h1>
                    <p class="text-gray-500 font-medium">Gérez la liste des étudiants interdits de postuler à vos offres.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="searchInput" placeholder="Rechercher par nom ou email..." class="w-full pl-12 pr-4 py-4 rounded-2xl border border-gray-100 bg-white focus:border-red-500 outline-none shadow-sm">
                    </div>
                </div>

                <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                                    <th class="pb-6 px-4">Étudiant</th>
                                    <th class="pb-6 px-4">Contact</th>
                                    <th class="pb-6 px-4">Date de blocage</th>
                                    <th class="pb-6 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="blockedTableBody" class="divide-y divide-gray-50">
                                <!-- Dynamic -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="noUsersMsg" class="hidden py-20 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mx-auto mb-4">
                            <i class="fas fa-shield-alt text-2xl"></i>
                        </div>
                        <p class="text-gray-400 font-medium">Aucun étudiant bloqué actuellement.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
