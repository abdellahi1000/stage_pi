<?php 
require_once '../include/session.php';
require_once '../include/db_connect.php';
check_auth('entreprise', 'Administrator');

if (!isset($_GET['id'])) {
    die("ID Etudiant manquant.");
}

$db = (new Database())->getConnection();
$student_id = intval($_GET['id']);

// Fetch user + profils
$stmt = $db->prepare("SELECT u.*, p.niveau_etudes, p.specialite, p.universite, p.skills, p.domaine_formation, p.cv_path, p.statut_disponibilite
                      FROM users u 
                      LEFT JOIN profils p ON u.id = p.user_id 
                      WHERE u.id = ? AND u.type_compte = 'etudiant'");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Etudiant non trouvé.");
}

$fullname = trim($student['prenom'] . ' ' . $student['nom']);
$initials = strtoupper(substr($student['prenom'], 0, 1) . substr($student['nom'], 0, 1));
$photo = $student['photo_profil'] ? '../' . $student['photo_profil'] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Etudiant: <?php echo htmlspecialchars($fullname); ?></title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
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
                <div class="mb-10 flex items-start justify-between">
                    <div>
                        <h1 class="text-4xl font-extrabold text-gray-900 mb-1">Profil du candidat</h1>
                        <p class="text-gray-500 font-medium">Informations détaillées sur le talent</p>
                    </div>
                    <a href="applications.php" class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> <span class="hidden sm:inline">Retour aux candidatures</span>
                    </a>
                </div>

                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 relative overflow-hidden">
                    <div class="flex flex-col md:flex-row gap-8 items-start relative z-10">
                        <!-- Left col -->
                        <div class="w-full md:w-1/3 flex flex-col items-center flex-shrink-0">
                            <div class="w-32 h-32 bg-blue-50 text-blue-600 rounded-[2.5rem] flex items-center justify-center text-4xl font-black mb-6 shadow-inner border border-blue-100">
                                <?php if ($photo): ?>
                                    <img src="<?php echo htmlspecialchars($photo); ?>" alt="Photo de profil" class="w-full h-full object-cover rounded-[2.5rem]">
                                <?php else: ?>
                                    <?php echo $initials; ?>
                                <?php endif; ?>
                            </div>
                            
                            <h2 class="text-2xl font-black text-gray-900 text-center mb-1"><?php echo htmlspecialchars($fullname); ?></h2>
                            <p class="text-[10px] font-black uppercase tracking-widest text-blue-600 bg-blue-50 px-3 py-1 rounded-full mb-6">
                                <?php echo htmlspecialchars($student['domaine_formation'] ?: 'Non spécifié'); ?>
                            </p>

                            <div class="w-full space-y-4 pt-6 border-t border-gray-100">
                                <div class="flex items-center gap-4 text-gray-600">
                                    <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 shrink-0">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Email</p>
                                        <p class="text-sm font-semibold truncate text-gray-800"><?php echo htmlspecialchars($student['email']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-gray-600">
                                    <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 shrink-0">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Téléphone</p>
                                        <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($student['telephone'] ?: 'Non renseigné'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right col -->
                        <div class="flex-1 w-full space-y-8">
                            <!-- Infos académiques -->
                            <div>
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <i class="fas fa-graduation-cap text-blue-500"></i> Parcours
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Niveau d'études</p>
                                        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($student['niveau_etudes'] ?: 'Non spécifié'); ?></p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Spécialité</p>
                                        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($student['specialite'] ?: 'Non spécifié'); ?></p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 sm:col-span-2">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Université / École</p>
                                        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($student['universite'] ?: 'Non spécifié'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Compétences -->
                            <div>
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <i class="fas fa-star text-orange-500"></i> Compétences
                                </h3>
                                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                                    <?php if ($student['skills']): ?>
                                        <p class="text-gray-700 font-medium leading-relaxed whitespace-pre-wrap"><?php echo htmlspecialchars($student['skills']); ?></p>
                                    <?php else: ?>
                                        <p class="text-gray-400 italic">Aucune compétence renseignée.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Biographie -->
                            <?php if ($student['bio']): ?>
                                <div>
                                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <i class="fas fa-user text-purple-500"></i> Biographie
                                    </h3>
                                    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                                        <p class="text-gray-700 font-medium leading-relaxed whitespace-pre-wrap"><?php echo htmlspecialchars($student['bio']); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</body>
</html>
