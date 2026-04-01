<?php 
require_once '../include/session.php';
require_once '../include/lookups.php';
require_once '../include/db_connect.php';
check_auth('entreprise', 'Administrator');

$company_id = $_SESSION['company_id'] ?? $_SESSION['user_id'] ?? 0;
$db = (new Database())->getConnection();

// Load settings for badges
$stmt_s = $db->prepare("SELECT * FROM company_settings WHERE company_id = ?");
$stmt_s->execute([$company_id]);
$settings = $stmt_s->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    $settings = ['mode_alternance' => 1, 'mode_statsy' => 1];
}

$sm_sectors = sm_get_industry_sectors();
$sm_sizes = sm_get_company_sizes();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Entreprise - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/admin_company_profile.js?v=<?php echo time(); ?>" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto bg-gray-50 md:ml-64">
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <span class="font-bold text-blue-600">StageMatch</span>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>

            <div class="max-w-4xl mx-auto px-6 py-10">
                <div class="mb-10 text-center">
                    <h1 class="text-4xl font-extrabold text-gray-900 mb-1">Mon Entreprise</h1>
                    <p class="text-gray-500 font-medium">Panel central pour gérer votre entreprise, offres, et permissions.</p>
                </div>

                <!-- Central Control Hub -->
                <div class="flex flex-wrap justify-center gap-6 mb-12">
                     <a href="accepted_students.php" class="w-full sm:w-[160px] bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all flex flex-col items-center text-center group">
                        <div class="w-14 h-14 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:bg-green-600 group-hover:text-white transition-colors">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1 leading-tight text-sm">Gérer les candidats</h3>
                        <p class="text-[9px] text-gray-500 font-semibold tracking-tight">Talents recrutés</p>
                    </a>

                    <a href="offers.php" class="w-full sm:w-[160px] bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all flex flex-col items-center text-center group">
                        <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1 leading-tight text-sm">Les Offres</h3>
                        <p class="text-[9px] text-gray-500 font-semibold tracking-tight">Gérer vos annonces</p>
                    </a>


                    
                    <a href="permissions.php" class="w-full sm:w-[160px] bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all flex flex-col items-center text-center group">
                        <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                            <i class="fas fa-key"></i>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1 leading-tight text-sm">Permissions</h3>
                        <p class="text-[9px] text-gray-500 font-semibold tracking-tight">Gestion des accès</p>
                    </a>

                    <a href="settings.php" class="w-full sm:w-[160px] bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all flex flex-col items-center text-center group">
                        <div class="w-14 h-14 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:bg-gray-600 group-hover:text-white transition-colors">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1 leading-tight text-sm">Paramètres</h3>
                        <p class="text-[9px] text-gray-500 font-semibold tracking-tight">Configuration</p>
                    </a>
                </div>

                <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-sm border border-gray-100 mx-auto max-w-2xl">
                    <h2 class="text-2xl font-black text-center text-gray-900 mb-8 border-b border-gray-50 pb-4">Profil de l'Entreprise</h2>
                    <form id="formCompany" class="space-y-10">
                        <!-- Profile Header -->
                        <div class="flex flex-col items-center gap-6 pb-8 border-b border-gray-50">
                            <div class="relative group">
                                <div class="w-32 h-32 bg-gray-100 rounded-[2.5rem] overflow-hidden border-4 border-white shadow-xl relative">
                                    <img id="companyLogoPreview" src="../img/default_company.png" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white cursor-pointer">
                                        <i class="fas fa-camera text-2xl"></i>
                                    </div>
                                </div>
                                <input type="file" id="companyLogoInput" name="logo" class="hidden" accept="image/*">
                            </div>
                            <div class="text-center">
                                <h1 class="text-3xl font-black text-gray-900 leading-tight" id="displayCompanyName">Chargement...</h1>
                                <div class="flex items-center gap-4 mt-1">
                                    <p class="text-gray-500 font-medium" id="displayCompanySlogan">Slogan de l'entreprise</p>
                                    <div class="flex gap-2">
                                        <?php if ($settings['mode_alternance'] ?? 1): ?>
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded-md text-[9px] font-black uppercase tracking-wider border border-emerald-100">Alternance Active</span>
                                        <?php endif; ?>
                                        <?php if ($settings['mode_statsy'] ?? 1): ?>
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-md text-[9px] font-black uppercase tracking-wider border border-blue-100">Statsy Pro</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">Nom de l'entreprise</label>
                                <input type="text" name="nom" required class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-bold">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">Slogan / Baseline</label>
                                <input type="text" name="slogan" class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">Secteur d'activité</label>
                                <select name="secteur" class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                                    <?php foreach ($sm_sectors as $s): ?>
                                        <option value="<?php echo htmlspecialchars($s['code']); ?>"><?php echo htmlspecialchars($s['label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">Taille de l'entreprise</label>
                                <select name="taille" class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                                    <?php foreach ($sm_sizes as $sz): ?>
                                        <option value="<?php echo htmlspecialchars($sz['code']); ?>"><?php echo htmlspecialchars($sz['label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">Site Web</label>
                                <input type="url" name="site_web" placeholder="https://..." class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">Siège Social</label>
                                <input type="text" name="siege" class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-2">À propos de l'entreprise</label>
                            <textarea name="description" rows="5" class="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all resize-none"></textarea>
                        </div>

                        <div class="pt-6 flex justify-center">
                            <button type="submit" class="px-12 py-4 bg-blue-600 text-white rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 hover:-translate-y-1 transition-all">
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
