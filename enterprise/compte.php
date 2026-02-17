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
    <script src="../js/global.js" defer></script>
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
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Compte Entreprise</h1>
                <p class="text-gray-600 mb-8">Informations de votre entreprise.</p>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
                            <div class="relative w-32 h-32 mx-auto mb-4">
                                <img id="profil-img" class="w-full h-full rounded-full object-cover border-4 border-blue-50" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_nom'] ?? 'Entreprise'); ?>&background=random" alt="Logo entreprise">
                                <label for="photo-input" class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full cursor-pointer shadow-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-camera text-sm"></i>
                                    <input type="file" id="photo-input" accept="image/*" class="hidden">
                                </label>
                            </div>
                            <h2 class="font-bold text-xl text-gray-800"><?php echo htmlspecialchars($_SESSION['user_nom'] ?? 'Entreprise'); ?></h2>
                            <p class="text-gray-500 text-sm mb-4"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-8">
                        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Détails de l'Entreprise</h2>
                            <form class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nom de l'entreprise</label>
                                    <p class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-100 text-gray-600"><?php echo htmlspecialchars($_SESSION['user_nom'] ?? '...'); ?></p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email de contact</label>
                                        <p class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-100 text-gray-600"><?php echo htmlspecialchars($_SESSION['user_email'] ?? '...'); ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Téléphone</label>
                                        <p class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-100 text-gray-600"><?php echo htmlspecialchars($_SESSION['user_tel'] ?? '...'); ?></p>
                                    </div>
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
