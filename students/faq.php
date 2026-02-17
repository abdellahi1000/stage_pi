<?php 
require_once '../include/session.php';
check_auth('etudiant');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide & FAQ - StageMatch</title>
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
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Aide & FAQ</h1>
                <p class="text-gray-600 mb-10">Tout ce que vous devez savoir pour bien utiliser StageMatch.</p>

                <div class="relative mb-12">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="faq-search-input" class="w-full pl-14 pr-6 py-5 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm transition-all" placeholder="Une question ? Cherchez ici... (ex: CV, candidature)">
                </div>

                <div class="grid grid-cols-1 gap-12">
                    <div class="faq-category">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-bold text-lg"><i class="fas fa-info-circle"></i></div>
                            Général
                        </h2>
                        <div class="space-y-4">
                            <!-- Item 1 -->
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Qu'est-ce que StageMatch ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>StageMatch est une plateforme dédiée à la mise en relation entre étudiants à la recherche de stages et entreprises proposant des offres de stage en Mauritanie. Elle facilite la création de CV, la candidature et le suivi en temps réel.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Item 2 -->
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment créer un compte ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Il suffit de cliquer sur "S'inscrire", de choisir votre rôle (Étudiant ou Entreprise) et de remplir vos informations de base. C'est rapide et gratuit !</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Item 3 -->
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Puis-je changer de rôle après l'inscription ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Non, le rôle est fixé lors de la création du compte. Si vous avez besoin d'un autre type de compte, vous devrez vous inscrire avec une autre adresse email.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-category mt-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center font-bold text-lg"><i class="fas fa-user-graduate"></i></div>
                            Espace Étudiant
                        </h2>
                        <div class="space-y-4">
                            <!-- Item 4 -->
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment créer ou modifier mon CV ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Utilisez notre outil "Créateur de CV" dans le menu latéral. Remplissez vos informations et générez un PDF professionnel en un clic. Vous pouvez le mettre à jour à tout moment.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Item 5 -->
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment postuler à une offre ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Dans la section "Rechercher des Offres", cliquez sur l'offre qui vous intéresse puis sur "Postuler". Votre CV précédemment créé sera automatiquement joint à votre candidature.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Item 6 -->
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment suivre mes candidatures ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Rendez-vous dans la section "Mes Candidatures". Vous y verrez le statut en temps réel (En attente, Acceptée, Refusée) pour chaque offre postulée.</p>
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
