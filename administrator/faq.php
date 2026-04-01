<?php 
require_once '../include/session.php';
check_auth('entreprise', 'Administrator');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide & FAQ Administrateur - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/dashboards.css">
    <link rel="stylesheet" href="../css/faq.css">
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/faq.js" defer></script>
    <script src="../js/admin_company_faq.js?v=<?php echo time(); ?>" defer></script>
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
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Aide Administrateur</h1>
                <p class="text-gray-600 mb-10">Gérez votre entreprise et vos collaborateurs en toute sérénité.</p>

                <div class="relative mb-12">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="faq-search-input" class="w-full pl-14 pr-6 py-5 rounded-2xl border border-gray-200 focus:border-blue-500 outline-none shadow-sm transition-all" placeholder="Une question ? (ex: offre, permissions, candidats)">
                </div>

                <div class="grid grid-cols-1 gap-12">
                    <div class="faq-category">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <i class="fas fa-user-shield text-blue-600"></i>
                            Rôles et Permissions
                        </h2>
                        <div class="space-y-4">
                            <div class="faq-item">
                                <div class="faq-question"><span>Quelle est la différence entre Administrateur et Entreprise ?</span><i class="fas fa-chevron-down text-xs"></i></div>
                                <div class="faq-answer"><div class="faq-answer-content"><p>L'Administrateur possède des droits étendus : gestion de tous les collaborateurs de l'entreprise, modification des permissions (droit de créer/supprimer des offres), et accès à toutes les candidatures de la société.</p></div></div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question"><span>Comment changer mon mot de passe administrateur ?</span><i class="fas fa-chevron-down text-xs"></i></div>
                                <div class="faq-answer"><div class="faq-answer-content"><p>Rendez-vous dans "Mon Compte", vous y trouverez une section dédiée au changement de votre mot de passe administrateur sécurisé.</p></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-category mt-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                                <i class="fas fa-question-circle text-purple-600"></i>
                                FAQ Personnalisée (Pour Étudiants)
                            </h2>
                            <button onclick="document.getElementById('modalAddFAQ').classList.remove('hidden')" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl transition-colors shadow-lg flex items-center gap-2">
                                <i class="fas fa-plus"></i>
                                Ajouter
                            </button>
                        </div>
                        <div class="space-y-4" id="companyFaqList">
                            <!-- Dynamics FAQs will be loaded here via JS -->
                            <p class="text-gray-500 italic text-sm">Chargement de vos questions...</p>
                        </div>
                    </div>

                    <div class="faq-category mt-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <i class="fas fa-briefcase text-green-600"></i>
                            Gestion Globale
                        </h2>
                        <div class="space-y-4">
                            <div class="faq-item">
                                <div class="faq-question"><span>Puis-je voir les offres créées par mes collaborateurs ?</span><i class="fas fa-chevron-down text-xs"></i></div>
                                <div class="faq-answer"><div class="faq-answer-content"><p>Oui, en tant qu'administrateur, la page "Gérer les Offres" affiche toutes les offres publiées par n'importe quel membre de votre entreprise.</p></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add FAQ Modal -->
            <div id="modalAddFAQ" class="fixed inset-0 z-[100] hidden items-center justify-center">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('modalAddFAQ').classList.add('hidden')"></div>
                <div class="bg-white w-[95%] max-w-lg mx-auto rounded-3xl shadow-2xl relative z-10 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-xl font-black text-gray-900">Nouvelle Question (FAQ)</h3>
                        <button onclick="document.getElementById('modalAddFAQ').classList.add('hidden')" class="text-gray-400 hover:text-rose-500 transition-colors w-8 h-8 flex justify-center items-center bg-white rounded-xl shadow-sm"><i class="fas fa-times"></i></button>
                    </div>
                    <form id="formAddFAQ" class="p-6 space-y-5">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">La Question</label>
                            <input type="text" name="question" required placeholder="Ex: Acceptez-vous les stages de moins de 2 mois ?" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-purple-500 outline-none transition-colors">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">La Réponse</label>
                            <textarea name="answer" required rows="4" placeholder="Votre réponse détaillée pour les étudiants..." class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-purple-500 outline-none transition-colors resize-none"></textarea>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 mt-4">
                            <button type="button" onclick="document.getElementById('modalAddFAQ').classList.add('hidden')" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors text-sm">
                                Annuler
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-purple-600/20 text-sm">
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
        </main>
    </div>
</body>
</html>
