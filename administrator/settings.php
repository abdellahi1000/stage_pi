<?php 
require_once '../include/session.php';
require_once '../include/db_connect.php';
check_auth('entreprise', 'Administrator');

$company_id = $_SESSION['company_id'];
$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM company_settings WHERE company_id = ?");
$stmt->execute([$company_id]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    $settings = [
        'email_notifs' => 1,
        'weekly_reports' => 0,
        'public_profile' => 1,
        'mode_alternance' => 1,
        'mode_statsy' => 1
    ];
} else {
    // Ensure keys exist
    if (!isset($settings['mode_alternance'])) $settings['mode_alternance'] = 1;
    if (!isset($settings['mode_statsy'])) $settings['mode_statsy'] = 1;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/admin_settings.js?v=<?php echo time(); ?>" defer></script>
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

            <div class="max-w-3xl mx-auto px-6 py-10">
                <div class="mb-10 flex items-center justify-between bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
                    <a href="my_company.php" class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-all border border-gray-100">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="text-center flex-1">
                        <h1 class="text-3xl font-black text-gray-900 leading-tight">Paramètres du Système</h1>
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1">Configuration de l'entreprise</p>
                    </div>
                    <div class="w-12"></div> <!-- Spacer for centering -->
                </div>

                <div class="space-y-8">
                    <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm overflow-hidden">
                        <h2 class="text-xl font-black text-gray-900 mb-8 flex items-center gap-3">
                            <i class="fas fa-bell text-blue-600"></i>
                            Notifications
                        </h2>
                        
                        <form id="formAdminSettings" class="space-y-0">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-gray-800">Email pour chaque candidature</h3>
                                <p class="text-sm text-gray-500 mt-1">Recevoir un email lorsqu'un étudiant postule.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="email_notifs" id="email_notifs" class="sr-only peer" <?php echo $settings['email_notifs'] ? 'checked' : ''; ?>>
                                <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between pb-8">
                            <div>
                                <h3 class="font-bold text-gray-800">Rapports Hebdomadaires</h3>
                                <p class="text-sm text-gray-500 mt-1">Résumé de l'activité de vos offres envoyé chaque lundi.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="weekly_reports" id="weekly_reports" class="sr-only peer" <?php echo $settings['weekly_reports'] ? 'checked' : ''; ?>>
                                <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm overflow-hidden">
                        <h2 class="text-xl font-black text-gray-900 mb-8 flex items-center gap-3">
                            <i class="fas fa-layer-group text-emerald-600"></i>
                            Modes de Recrutement
                        </h2>
                        
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-gray-800">Mode Alternance</h3>
                                    <p class="text-sm text-gray-500 mt-1">Activer les offres en contrat d'alternance.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="mode_alternance" id="mode_alternance" class="sr-only peer" <?php echo ($settings['mode_alternance'] ?? 1) ? 'checked' : ''; ?>>
                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between border-t border-gray-50 pt-6">
                                <div>
                                    <h3 class="font-bold text-gray-800">Mode Statsy (Statistiques)</h3>
                                    <p class="text-sm text-gray-500 mt-1">Activer l'analyse avancée des performances.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="mode_statsy" id="mode_statsy" class="sr-only peer" <?php echo ($settings['mode_statsy'] ?? 1) ? 'checked' : ''; ?>>
                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm overflow-hidden">
                        <h2 class="text-xl font-black text-gray-900 mb-8 flex items-center gap-3">
                            <i class="fas fa-eye text-indigo-600"></i>
                            Visibilité Globale
                        </h2>
                        
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-gray-800">Profil Entreprise Public</h3>
                                    <p class="text-sm text-gray-500 mt-1">Permettre aux étudiants de trouver votre page entreprise.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="public_profile" id="public_profile" class="sr-only peer" <?php echo $settings['public_profile'] ? 'checked' : ''; ?>>
                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-12 flex justify-center pb-20">
                    <button type="submit" id="btnSaveSettings" class="px-16 py-5 bg-blue-600 text-white rounded-2xl font-black hover:bg-blue-700 hover:-translate-y-1 transition-all shadow-xl shadow-blue-100 tracking-wide text-sm">
                        Enregistrer les performances
                    </button>
                </div>
                </form>

            </div>
        </main>
    </div>
</body>
</html>
