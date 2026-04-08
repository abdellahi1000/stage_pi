<?php 
require_once '../include/session.php';
check_auth('entreprise');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide & FAQ Entreprise - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/dashboards.css">
    <link rel="stylesheet" href="../css/faq.css">
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/faq.js" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto bg-gray-50 md:ml-64">
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-blue-600">StageMatch</span>
                </div>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>
<div class="max-w-5xl mx-auto px-6 py-10">
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Aide Entreprise</h1>
                <p class="text-gray-600 mb-10">Gérez vos offres et trouvez les meilleurs talents en toute simplicité.</p>

                <div class="relative mb-12">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="faq-search-input" class="w-full pl-14 pr-6 py-5 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm transition-all" placeholder="Une question ? (ex: offre, candidature, visibilité)">
                </div>

                <div class="grid grid-cols-1 gap-12">
                    <div class="faq-category">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-bold text-lg"><i class="fas fa-briefcase"></i></div>
                            Gestion des Offres
                        </h2>
                        <div class="space-y-4">
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment publier une nouvelle offre ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Rendez-vous dans la section "Déposer une Offre" et cliquez sur le bouton "Publier". Remplissez les détails (titre, localisation, type de contrat) et validez. Votre offre sera immédiatement visible par les étudiants.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Puis-je modifier une offre après publication ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Oui, dans la gestion de vos offres, cliquez sur l'icône d'édition (crayon) sur la carte de l'offre concernée pour modifier ses informations ou changer sa visibilité.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-category mt-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-xl flex items-center justify-center font-bold text-lg"><i class="fas fa-user-check"></i></div>
                            Candidatures
                        </h2>
                        <div class="space-y-4">
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Où voir les nouveaux candidats ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Toutes les candidatures reçues apparaissent dans "Gérer les Candidats". Vous pouvez les filtrer par offre ou par statut (En attente, Vue, etc.).</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment contacter un étudiant ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>En cliquant sur "Détails" d'une candidature, vous avez accès au CV de l'étudiant et à ses coordonnées (Email, Téléphone) pour organiser un entretien.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-category mt-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <div class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-xl flex items-center justify-center font-bold text-lg"><i class="fas fa-eye-slash"></i></div>
                            Confidentialité
                        </h2>
                        <div class="space-y-4">
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment masquer toutes mes offres temporairement ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Vous pouvez utiliser le commutateur "Visibilité Globale" en haut de votre page de gestion des offres pour masquer l'ensemble de votre profil et de vos annonces aux étudiants.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>





