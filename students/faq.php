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
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
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
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Aide & FAQ Étudiant</h1>
                <p class="text-gray-600 mb-10">Retrouvez ici les réponses aux questions les plus fréquentes sur StageMatch côté étudiant.</p>

                <div class="relative mb-8">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="faq-search-input" class="w-full pl-14 pr-6 py-5 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm transition-all" placeholder="Une question ? Cherchez ici... (ex: CV, candidature)">
                </div>

                <div class="grid grid-cols-1 gap-12">
                    <div id="faq-general" class="faq-category">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-bold text-lg"><i class="fas fa-info-circle"></i></div>
                            Général
                        </h2>
                        <div class="space-y-4">
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
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>À quoi servent les paramètres de mon compte (profil, thème, notifications) ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Dans <strong>Mon Compte</strong>, vous pouvez mettre à jour vos informations (nom, email, téléphone), choisir votre thème (clair / sombre) et activer certaines notifications. Ces paramètres sont immédiatement pris en compte dans votre Espace Étudiant et dans vos futures candidatures.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment sont gérées mes notifications de candidatures ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Lorsque vous postulez, StageMatch met automatiquement à jour le statut de vos candidatures. Vous pouvez consulter ces statuts dans <strong>Mes Candidatures</strong> et, selon les réglages configurés par l’entreprise, recevoir des emails de confirmation ou de décision.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="faq-espace" class="faq-category mt-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center font-bold text-lg"><i class="fas fa-user-graduate"></i></div>
                            Espace Étudiant
                        </h2>
                        <div class="space-y-4">
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Que puis-je faire depuis mon Espace Étudiant ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Votre tableau de bord étudiant vous permet de voir rapidement les offres recommandées, vos candidatures récentes et l’état de votre profil. Depuis le menu latéral, vous accédez au <strong>Créateur de CV</strong>, à la recherche d’offres, à vos candidatures et aux paramètres de compte.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment créer ou modifier mon CV avec l’outil intégré ?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Dans le menu latéral, ouvrez <strong>"Créateur de CV"</strong>. Remplissez vos informations personnelles, vos expériences, votre formation et vos compétences. L’aperçu se met à jour en temps réel et vous pouvez <strong>télécharger un PDF</strong> propre de votre CV. Vous pouvez revenir modifier chaque section à tout moment.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment importer un CV ou une lettre de motivation déjà existants&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Dans <strong>Mon Compte → Dossier Professionnel</strong>, vous pouvez importer vos fichiers CV et Lettres de motivation au format PDF ou DOC/DOCX. Ces documents seront ensuite proposés lors de la candidature à une offre afin que vous puissiez choisir entre un CV existant ou un nouveau fichier.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Les changements dans mes paramètres se reflètent-ils automatiquement dans mes candidatures&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Oui, lorsque vous mettez à jour votre email, votre numéro de téléphone ou votre CV dans les paramètres, ces informations sont utilisées pour vos prochaines candidatures. Les candidatures déjà envoyées restent inchangées, mais les entreprises voient toujours la version la plus récente de vos coordonnées dans votre profil.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="faq-cv-candidatures" class="faq-category mt-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-xl flex items-center justify-center font-bold text-lg"><i class="fas fa-file-alt"></i></div>
                            CV, Offres & Candidatures
                        </h2>
                        <div class="space-y-4">
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment postuler à une offre de stage&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Dans la section <strong>"Offres de Stage"</strong>, cliquez sur une annonce puis sur le bouton <strong>"Postuler"</strong>. Choisissez le CV et la lettre de motivation à transmettre (depuis votre dossier ou en important un nouveau fichier), rédigez votre message de motivation si nécessaire puis validez. Votre candidature est alors transmise à l’entreprise.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment suivre l’avancement de mes candidatures&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Accédez à <strong>"Mes Candidatures"</strong>. Pour chaque offre, vous voyez un statut mis à jour par l’entreprise (En attente, Vue, Acceptée, Refusée). Lorsque vous êtes accepté sur une offre, les autres candidatures peuvent être automatiquement clôturées selon les règles de la plateforme.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Puis-je utiliser du HTML ou des mises en forme avancées dans mes descriptions&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Les champs de texte (bio, message de motivation, etc.) acceptent surtout du texte brut avec retours à la ligne. Le HTML complexe est limité pour garantir la sécurité et une bonne lisibilité sur mobile. Utilisez des phrases claires, des listes simples et des paragraphes courts pour présenter vos projets.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Comment suis-je averti lorsqu’une entreprise répond à ma candidature&nbsp;?</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Lorsque le statut de votre candidature change, cela apparaît immédiatement dans <strong>"Mes Candidatures"</strong>. Selon les choix de l’entreprise, vous pouvez également recevoir un email ou un message externe (WhatsApp, téléphone) pour organiser la suite du processus (entretien, documents à fournir, etc.).</p>
                                    </div>
                                </div>
                            </div>
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







