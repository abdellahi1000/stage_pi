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
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Depuis votre espace entreprise, ouvrez le menu <strong>"Déposer une Offre"</strong> puis cliquez sur le bouton <strong>"Publier"</strong>. Renseignez toutes les informations : titre, catégorie, type de contrat, localisation, nombre de stagiaires, durée et description. Une fois enregistrée, l’offre apparaît immédiatement dans la section Offres pour les étudiants ciblés.</p>
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
                                        <p>Oui. Dans la page <strong>"Mes Offres"</strong>, utilisez le bouton <strong>"Gérer"</strong> puis l’icône de crayon sur la carte de l’offre pour modifier le titre, la description, le type de contrat, la catégorie ou le statut (Active / Archivée). Les modifications sont automatiquement prises en compte dans la liste des offres côté étudiants.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>À quoi servent les paramètres de visibilité et de statut individuel&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Le <strong>statut individuel</strong> contrôle si une offre précise est visible (Active) ou masquée (Archivée). La <strong>visibilité du profil</strong dans l’onglet Préférences applique un filtre global à l’ensemble de vos offres. Par exemple, mettre le profil en "Privé" tout en gardant des offres actives permet de ne montrer l’entreprise qu’aux étudiants qui ont déjà un lien direct vers une offre.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Puis-je utiliser du HTML ou des mises en forme dans la description de l’offre&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>La zone de description accepte le texte simple ainsi que certains retours à la ligne. Le HTML avancé est volontairement limité pour garantir la sécurité des étudiants et l’affichage homogène sur tous les appareils. Utilisez des phrases courtes, des listes à puces simples et des paragraphes clairs pour décrire les missions et les compétences attendues.</p>
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
                                        <p>Toutes les candidatures reçues apparaissent dans la section <strong>"Gérer les Candidats"</strong>. Vous pouvez filtrer par offre, par période ou par statut (En attente, Vue, Acceptée, Refusée). Les compteurs sur votre tableau de bord vous donnent une vue globale de l’activité.</p>
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
                                        <p>En cliquant sur <strong>"Détails"</strong> d'une candidature, vous accédez au CV de l'étudiant, à sa lettre de motivation (si fournie) et à ses coordonnées (Email, Téléphone, WhatsApp). Vous pouvez alors planifier un entretien, envoyer une réponse ou mettre à jour le statut de la candidature.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment générer un rapport de recrutement (CSV / Excel / PDF)&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Dans <strong>Mon Compte → Préférences</strong>, utilisez le bouton <strong>"Exporter Data"</strong>. Choisissez le format souhaité (CSV, Excel, PDF). Le système génère un rapport avec trois groupes distincts : Acceptées, Refusées et En attente. Un instantané de ce rapport est également archivé dans la base de données pour un suivi ultérieur.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Que deviennent les CV et lettres de motivation envoyés par les étudiants&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Les documents sont centralisés dans le profil de l’étudiant et liés à chaque candidature. Depuis l’écran de détail, vous pouvez télécharger les CV ou lettres de motivation au format PDF/DOCX, les conserver en interne ou les partager avec vos équipes RH dans le respect de votre politique de confidentialité.</p>
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
                                        <p>Vous pouvez utiliser le commutateur <strong>"Visibilité Globale"</strong> en haut de votre page de gestion des offres pour masquer l'ensemble de votre profil et de vos annonces aux étudiants. Cela peut être utile lors d’une pause de recrutement ou pendant une mise à jour de vos offres.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment mes données et celles des étudiants sont-elles protégées&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Les mots de passe sont stockés de manière chiffrée en base de données, les documents sont hébergés sur un espace sécurisé et seuls les utilisateurs authentifiés peuvent accéder aux informations qui les concernent. Nous vous recommandons de limiter le partage des CV en dehors de votre organisation et de supprimer régulièrement les exports locaux devenus inutiles.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Quelles bonnes pratiques appliquer pour la confidentialité des candidats&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Utilisez les données des étudiants uniquement dans le cadre du recrutement, limitez l’accès aux personnes directement impliquées, évitez de partager les liens de CV publics et supprimez les fichiers exportés lorsque le processus est terminé. Pensez aussi à informer les candidats retenus ou non pour maintenir une bonne image de votre entreprise.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Les Informations -->
                <div class="mt-16 border-t border-gray-200 pt-10">
                    <h2 class="text-2xl font-extrabold text-gray-900 mb-4">Les Informations</h2>
                    <p class="text-gray-600 mb-3">
                        StageMatch est une plateforme dédiée à la mise en relation entre <strong>entreprises</strong> et <strong>étudiants</strong> en recherche de stage. 
                        L’espace Entreprise vous permet de publier des offres, de suivre les candidatures reçues et de centraliser tous les documents utiles au recrutement.
                    </p>
                    <p class="text-gray-600 mb-3">
                        En tant qu’entreprise, vous interagissez avec les offres en les créant, en les modifiant ou en les archivant, et avec les candidatures en changeant leur statut 
                        (En attente, Vue, Acceptée, Refusée) et en contactant directement les étudiants. Les tableaux de bord et les exports de données vous aident à analyser votre flux de recrutement.
                    </p>
                    <p class="text-gray-600">
                        Le système met automatiquement à jour certaines informations entre les paramètres et les écrans de publication d’offres (visibilité, catégories, préférences de notification) afin 
                        d’éviter les ressaisies et d’assurer la cohérence de vos données. Vous gardez cependant la main à tout moment sur l’activation ou non de ces fonctionnalités via l’onglet 
                        <strong>Préférences</strong> de votre compte entreprise.
                    </p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>





