<?php 
require_once '../include/session.php';
check_auth('etudiant');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Candidatures - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/dashboards.css">
    <link rel="stylesheet" href="../css/candidatures.css">
    <!-- Bibliothèque pour générer le PDF -->
    <script src="../js/html2pdf.bundle.js"></script>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/candidatures.js" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto md:ml-64">
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30 px-6">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-blue-600">StageMatch</span>
                </div>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>

            <div class="max-w-5xl mx-auto px-6 py-10">
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Mes Candidatures</h1>
                <p class="text-gray-600 mb-8">Suivez l'état de vos candidatures pour les stages en temps réel.</p>

                <div id="candidaturesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Dynamic content will be loaded here -->
                    <div class="col-span-full py-20 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent mb-4"></div>
                        <p class="text-gray-500">Chargement de vos candidatures...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal d'acceptation (interface type email, signature entreprise, coordonnées cliquables) -->
    <div id="acceptanceModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
        <div id="pdfContent" class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl relative overflow-hidden">
            <button onclick="closeAcceptanceModal()" class="absolute top-4 right-4 z-10 w-10 h-10 bg-white/90 rounded-full flex items-center justify-center text-gray-500 hover:text-gray-800 hover:bg-white transition no-print">
                <i class="fas fa-times"></i>
            </button>

            <!-- En-tête type email -->
            <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600 shrink-0">
                        <i class="fas fa-envelope-open-text text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-xl font-black text-gray-900">Dossier d'acceptation</h2>
                        <p class="text-sm text-gray-500 mt-0.5">De la part de <span id="accDocCompany" class="font-bold text-gray-800"></span></p>
                        <p class="text-xs text-gray-400 mt-1">Destinataire : <span id="accDocStudentName"><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></span></p>
                    </div>
                </div>
            </div>

            <!-- Corps du message (style email) : message + signature côte à côte -->
            <div class="px-6 py-6">
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Message de l'entreprise</p>
                        <div class="bg-gray-50/80 rounded-xl p-5 border border-gray-100">
                            <p id="accDocMessage" class="text-gray-700 leading-relaxed text-sm whitespace-pre-wrap"></p>
                        </div>
                    </div>
                    <div id="accDocSignatureContainer" class="lg:w-48 shrink-0 hidden">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Signature</p>
                        <div class="bg-white rounded-xl p-3 border border-gray-100 flex items-center justify-center min-h-[80px]">
                            <img id="accDocSignature" src="" alt="Signature" class="max-w-full max-h-24 object-contain">
                        </div>
                    </div>
                </div>

                <!-- Coordonnées (étudiant uniquement, cliquables) -->
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Contacter l'entreprise</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div id="accDocEmailContainer" class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50/50 transition" style="display: none;">
                            <i class="fas fa-envelope text-blue-500 shrink-0"></i>
                            <a id="accDocEmail" href="#" class="text-sm font-medium text-gray-700 hover:text-blue-600 line-clamp-1 break-all"></a>
                        </div>
                        <div id="accDocPhoneContainer" class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50/50 transition" style="display: none;">
                            <i class="fas fa-phone-alt text-blue-500 shrink-0"></i>
                            <a id="accDocPhone" href="#" class="text-sm font-medium text-gray-700 hover:text-blue-600"></a>
                        </div>
                        <div id="accDocWhatsContainer" class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100 hover:border-green-200 hover:bg-green-50/50 transition sm:col-span-2" style="display: none;">
                            <i class="fab fa-whatsapp text-green-500 text-xl shrink-0"></i>
                            <a id="accDocWhats" href="#" class="text-sm font-medium text-gray-700 hover:text-green-600" target="_blank" rel="noopener"></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 pb-6 pt-2 flex justify-center no-print">
                <button id="downloadAcceptancePdf" class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold text-sm hover:bg-black transition flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i> Télécharger ma confirmation PDF
                </button>
            </div>
        </div>
    </div>
</body>
</html>
