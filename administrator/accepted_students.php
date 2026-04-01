<?php 
require_once '../include/session.php';
check_auth('entreprise', 'Administrator');
$force_status = 'accepted';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidats Acceptés - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>const FORCED_STATUS = '<?php echo $force_status; ?>';</script>
    <script src="../js/global.js" defer></script>
    <script src="../js/admin_company_applications.js?v=<?php echo time(); ?>" defer></script>
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
                    <h1 class="text-4xl font-extrabold text-gray-900 mb-1">Candidats Acceptés</h1>
                    <p class="text-gray-500 font-medium">Consultez la liste des étudiants retenus pour vos offres.</p>
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="searchApp" placeholder="Rechercher un candidat ou une offre..." class="w-full pl-12 pr-4 py-4 rounded-2xl border border-gray-100 bg-white focus:border-blue-500 outline-none shadow-sm">
                    </div>
                    <!-- Hidden input to align with JS expectations -->
                    <input type="hidden" name="filter_status" value="<?php echo $force_status; ?>">
                    <div class="relative custom-dropdown hidden" id="dropdownStatutApp">
                          <button></button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6" id="applicationsGrid">
                    <!-- Dynamic Loading -->
                </div>
            </div>
            
            <!-- Modals handling in JS requires this to exist -->
            <div id="cvModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCvModal()"></div>
                <div class="bg-white w-full max-w-4xl mx-4 rounded-3xl shadow-2xl overflow-hidden relative z-10 flex flex-col max-h-[90vh]">
                    <div class="flex items-center justify-between p-6 border-b border-gray-100">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900" id="cvModalName">Profil du candidat</h3>
                            <p class="text-sm font-semibold text-gray-500 mt-1" id="cvModalOffer">Pour l'offre : ...</p>
                        </div>
                        <button onclick="closeCvModal()" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gray-100/50 hover:bg-rose-50 hover:text-rose-500 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 bg-gray-50 custom-scrollbar" id="cvModalContent">
                        <!-- Content -->
                    </div>

                    <div class="p-6 bg-white border-t border-gray-100 flex gap-4" id="cvModalActions">
                        <!-- Actions dynamically filled -->
                    </div>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
