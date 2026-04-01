<?php 
require_once '../include/session.php';
check_auth('etudiant');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créateur de CV - StageMatch</title>
    <!-- Bibliothèque pour générer le PDF -->
    <script src="../js/html2pdf.bundle.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/createCV.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js?v=<?= time() ?>" defer></script>
    <script>
        // Critical tab switch function defined early to avoid "not defined" errors
        function switchTab(tab) {
            if (typeof window.switchTab === 'function') {
               window.switchTab(tab);
            } else {
               console.warn("Script still loading...");
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800">
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

            <div class="max-w-7xl mx-auto px-6 py-10 cv-creator-container">
                <div class="mb-6 lg:flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Créateur de Dossier</h1>
                        <p class="text-gray-600">Préparez votre CV et votre lettre de motivation en un clin d'œil.</p>
                    </div>
                    <div class="mt-6 lg:mt-0 flex gap-4">
                        <button id="btnExportCV" onclick="telechargerPDF()" class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-black transition flex items-center gap-2">
                            <i class="fas fa-download"></i> Exporter CV
                        </button>
                        <button id="btnExportMotivation" onclick="telechargerMotivationPDF()" class="hidden px-6 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-black transition flex items-center gap-2">
                            <i class="fas fa-download"></i> Exporter Motivation
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex space-x-4 mb-8">
                    <button type="button" onclick="switchTab('cv')" id="tabCV" class="px-6 py-2 rounded-lg font-bold transition-all bg-blue-600 text-white shadow-lg">CV</button>
                    <button type="button" onclick="switchTab('motivation')" id="tabMotivation" class="px-6 py-2 rounded-lg font-bold transition-all bg-white text-gray-600 hover:bg-gray-100 border border-gray-200">Lettre de Motivation</button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 h-[calc(100vh-250px)]">
                    <!-- Formulaire Section (40%) -->
                    <div class="lg:col-span-5 form-section overflow-y-auto pr-4 custom-scrollbar">
                        
                        <!-- CV Form -->
                        <div id="cv-form-container" class="space-y-8">
                            <!-- Photo Section -->
                            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex items-center gap-4">
                                <div class="relative group cursor-pointer" onclick="document.getElementById('photoInput').click()">
                                    <div id="photoPreview" class="w-16 h-16 rounded-full bg-white border-2 border-blue-200 overflow-hidden flex items-center justify-center">
                                        <i class="fas fa-camera text-blue-300"></i>
                                    </div>
                                    <div class="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                        <i class="fas fa-plus text-white text-xs"></i>
                                    </div>
                                    <input type="file" id="photoInput" class="hidden" accept="image/*" onchange="previewPhoto(this)">
                                </div>
                                <div>
                                    <h3 class="font-bold text-blue-900">Photo de profil</h3>
                                    <p class="text-xs text-blue-700">Format professionnel recommandé</p>
                                </div>
                            </div>

                            <!-- Basic Info -->
                            <section>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-user-circle text-blue-600"></i> Informations de base
                                </h3>
                                <div class="space-y-4">
                                    <div class="input-field">
                                        <label>Nom complet</label>
                                        <div class="relative">
                                            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                            <input type="text" id="nom" class="pl-11" placeholder="Ex: Donna Stroupe" value="<?= htmlspecialchars($_SESSION['user_prenom'].' '.$_SESSION['user_nom']) ?>">
                                        </div>
                                    </div>
                                    <div class="input-field">
                                        <label>Titre / Poste visé</label>
                                        <div class="relative">
                                            <i class="fas fa-briefcase absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                            <input type="text" id="poste" class="pl-11" placeholder="Ex: Développeur Full Stack">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="input-field">
                                            <label>Téléphone</label>
                                            <div class="relative">
                                                <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                                <input type="text" id="tel" class="pl-11" placeholder="+222 00 00 00 00" value="<?= htmlspecialchars($_SESSION['user_tel'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="input-field">
                                            <label>Ville</label>
                                            <div class="relative">
                                                <i class="fas fa-map-marker-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                                <input type="text" id="ville" class="pl-11" placeholder="Nouakchott">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-field">
                                         <label>Email professionnel</label>
                                         <div class="relative">
                                             <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                             <input type="email" id="email" class="pl-11" placeholder="votre@email.com" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                                         </div>
                                     </div>
                                     <div class="grid grid-cols-2 gap-4">
                                         <div class="input-field">
                                             <label>Portfolio / Website</label>
                                             <div class="relative">
                                                 <i class="fas fa-globe absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                                 <input type="url" id="website" class="pl-11" placeholder="https://votre-site.com">
                                             </div>
                                         </div>
                                         <div class="input-field">
                                             <label>LinkedIn URL</label>
                                             <div class="relative">
                                                 <i class="fab fa-linkedin absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                                 <input type="url" id="linkedin" class="pl-11" placeholder="https://linkedin.com/in/profil">
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </section>

                            <!-- Philosophy & Strengths -->
                            <section>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-brain text-blue-600"></i> Profil & Valeurs
                                </h3>
                                <div class="space-y-4">
                                    <div class="input-field">
                                        <label>Philosophie Personnelle / Profil</label>
                                        <textarea id="philosophy" placeholder="Votre approche du travail, vos valeurs..."></textarea>
                                    </div>
                                    <div class="input-field">
                                        <label>Forces (ex: Leadership, Organisation...)</label>
                                        <input type="text" id="strengths" placeholder="Leadership, Resolution de problèmes, Organisation">
                                    </div>
                                </div>
                            </section>

                            <!-- Profile Summary -->
                            <section>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-id-card text-blue-600"></i> Résumé du profil
                                </h3>
                                <div class="input-field">
                                    <label>À propos de moi</label>
                                    <textarea id="about" placeholder="Décrivez brièvement votre profil professionnel..."></textarea>
                                </div>
                            </section>

                            <!-- Professional Experience -->
                            <section>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-briefcase text-blue-600"></i> Expériences Professionnelles
                                </h3>
                                <div class="input-field">
                                    <label>Poste | Entreprise | Date | Description</label>
                                    <textarea id="experiences" style="height: 120px;" placeholder="Ex: Stagiaire | Mauritel | Juin 2025 | Développement d'une application..."></textarea>
                                </div>
                            </section>

                            <!-- Education -->
                            <section>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-graduation-cap text-blue-600"></i> Formations & Éducation
                                </h3>
                                <div class="input-field">
                                    <label>Diplôme | Établissement | Année</label>
                                    <textarea id="education" placeholder="Ex: Licence Informatique | Sup'Management | 2024"></textarea>
                                </div>
                            </section>

                            <!-- Skills -->
                            <section>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-tools text-blue-600"></i> Compétences & Outils
                                </h3>
                                <div class="input-field">
                                    <label>Compétences (une par ligne)</label>
                                    <textarea id="skills" placeholder="JavaScript, PHP, SQL..."></textarea>
                                </div>
                            </section>

                            <!-- Languages & References -->
                            <section>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-layer-group text-blue-600"></i> Langues & Références
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="input-field">
                                        <label>Langues (ex: Arabe, Français)</label>
                                        <input type="text" id="languages" placeholder="Arabe, Français">
                                    </div>
                                    <div class="input-field">
                                        <label>Refs (Nom | Poste | Contact)</label>
                                        <input type="text" id="references" placeholder="Nom | Poste | Contact">
                                    </div>
                                </div>
                            </section>

                            <!-- Certifications -->
                            <section>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-certificate text-blue-600"></i> Certifications
                                </h3>
                                <div class="input-field">
                                    <label>Titre | Organisme | Année</label>
                                    <textarea id="certifications" placeholder="Ex: Google Data Analytics | Coursera | 2024"></textarea>
                                </div>
                            </section>

                            <!-- Projects -->
                            <section>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-project-diagram text-blue-600"></i> Projets Réalisés
                                </h3>
                                <div class="input-field">
                                    <label>Titre | Description | Lien</label>
                                    <textarea id="projects" placeholder="Ex: Portfolio | Site web personnel..."></textarea>
                                </div>
                            </section>

                            <!-- New Custom Sections -->
                            <section>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-plus-circle text-blue-600"></i> Sections Complémentaires
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="input-field">
                                        <label>Compétences Additionnelles</label>
                                        <textarea id="skills_extra" placeholder="Autres compétences..."></textarea>
                                    </div>
                                    <div class="input-field">
                                        <label>Outils Techniques</label>
                                        <textarea id="tools" placeholder="Logiciels, outils..."></textarea>
                                    </div>
                                    <div class="input-field">
                                        <label>Réalisations Professionnelles</label>
                                        <textarea id="achievements" placeholder="Prix, distinctions..."></textarea>
                                    </div>
                                    <div class="input-field">
                                        <label>Expérience Bénévole</label>
                                        <textarea id="volunteer" placeholder="Associations, bénévolat..."></textarea>
                                    </div>
                                    <div class="input-field">
                                        <label>Ateliers / Formations</label>
                                        <textarea id="workshops" placeholder="Séminaires, workshops..."></textarea>
                                    </div>
                                    <div class="input-field">
                                        <label>Intérêts Professionnels</label>
                                        <textarea id="interests" placeholder="Sujets d'intérêt..."></textarea>
                                    </div>
                                </div>
                                <div class="input-field mt-4">
                                    <label>Informations Supplémentaires</label>
                                    <textarea id="extra_info" placeholder="Tout autre détail utile..."></textarea>
                                </div>
                            </section>
                        </div>

                         <!-- Motivation Form -->
                         <div id="motivation-form-container" class="hidden space-y-8">
                             <!-- Trainee Details -->
                             <section>
                                 <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                     <i class="fas fa-user-graduate text-blue-600"></i> Vos Coordonnées
                                 </h3>
                                 <div class="space-y-4">
                                     <div class="input-field">
                                         <label>Adresse Complète (Rue - Code Postal - Ville)</label>
                                         <input type="text" id="mot-adresse" placeholder="Ex: 12 Rue des Oliviers, 30000 Nema">
                                     </div>
                                     <div class="input-field">
                                         <label>Ville (Pour la date)</label>
                                         <input type="text" id="mot-ville" placeholder="Ex: Nouakchott">
                                     </div>
                                 </div>
                             </section>

                             <!-- Company Details -->
                             <section>
                                 <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                     <i class="fas fa-building text-blue-600"></i> Destinataire
                                 </h3>
                                 <div class="space-y-4">
                                     <div class="input-field">
                                         <label>Service RH (Cible)</label>
                                         <input type="text" id="mot-service-rh" placeholder="Ex: Service des Ressources Humaines Santé">
                                     </div>
                                     <div class="input-field">
                                         <label>Nom de l'Entreprise</label>
                                         <input type="text" id="mot-entreprise" placeholder="Ex: Mauritel SA">
                                     </div>
                                     <div class="input-field">
                                         <label>Email de l'Entreprise</label>
                                         <input type="email" id="mot-email-entreprise" placeholder="Ex: rh@mauritel.mr">
                                     </div>
                                     <div class="input-field">
                                         <label>Adresse de l'Entreprise</label>
                                         <input type="text" id="mot-adresse-ent" placeholder="Ex: Avenue Gamal Abdel Nasser, Nouakchott">
                                     </div>
                                 </div>
                             </section>

                             <!-- Letter Details -->
                             <section>
                                 <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                     <i class="fas fa-pen-fancy text-blue-600"></i> Détails de la lettre
                                 </h3>
                                 <div class="space-y-4">
                                     <div class="input-field">
                                         <label>Date de la lettre</label>
                                         <input type="text" id="mot-date" value="<?= date('d F Y') ?>">
                                     </div>
                                     <div class="input-field">
                                         <label>Objet</label>
                                         <input type="text" id="mot-objet" placeholder="Ex: Candidature pour le stage de photographe">
                                     </div>
                                     <div class="input-field">
                                         <label>Formule d'appel (Salutations)</label>
                                         <input type="text" id="mot-civilite" value="Madame, Monsieur,">
                                     </div>
                                     <div class="input-field">
                                         <label>Corps de la lettre</label>
                                         <textarea id="mot-message" style="height: 300px;"></textarea>
                                     </div>
                                     <div class="input-field">
                                         <label>Formule de politesse (Fin)</label>
                                         <textarea id="mot-cloture" placeholder="Dans l'attente de votre réponse... Cordialement,"></textarea>
                                     </div>
                                 </div>
                             </section>

                             <!-- Signature -->
                             <section>
                                 <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                     <i class="fas fa-signature text-blue-600"></i> Signature
                                 </h3>
                                 <div class="bg-white border-2 border-dashed border-gray-200 rounded-2xl p-6">
                                     <div class="flex justify-between items-center mb-4">
                                         <span class="text-sm font-medium text-gray-500">Signez ci-dessous</span>
                                         <div class="flex gap-2">
                                             <button type="button" onclick="clearSignature()" class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Effacer</button>
                                             <label class="text-xs px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition cursor-pointer">
                                                 Importer Image <input type="file" id="sig-upload" class="hidden" accept="image/*" onchange="uploadSignature(this)">
                                             </label>
                                         </div>
                                     </div>
                                     <canvas id="signature-pad" class="w-full h-40 bg-gray-50 rounded-xl cursor-crosshair border border-gray-100"></canvas>
                                     <input type="hidden" id="signature-data">
                                 </div>
                             </section>

                         </div>

                    </div>

                    <!-- Aperçu Section (60%) -->
                    <div class="lg:col-span-7 h-full flex items-center justify-center bg-gray-300 rounded-2xl overflow-hidden relative p-4">
                        
                        <!-- CV Preview -->
                        <div id="cv-container" class="preview-page-container">
                            <div id="cv-preview">
                                <!-- Header -->
                                <div class="cv-header-layout">
                                    <div class="cv-photo-container">
                                        <img id="cv-photo" alt="P" src="../assets/default-avatar.png">
                                    </div>
                                    <div class="cv-name-block">
                                        <h1 id="out-nom">VOTRE NOM</h1>
                                        <p id="out-poste">VOTRE TITRE</p>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div class="cv-body-layout">
                                    <!-- Sidebar -->
                                    <div class="cv-left-col">
                                        <div class="contact-section">
                                            <div class="contact-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span id="out-ville">Nouakchott</span>
                                            </div>
                                            <div class="contact-item">
                                                <i class="fas fa-globe"></i>
                                                <span id="out-website">https://site.com</span>
                                            </div>
                                            <div class="contact-item">
                                                <i class="fab fa-linkedin"></i>
                                                <span id="out-linkedin">linkedin.com/in/nom</span>
                                            </div>
                                        </div>

                                        <h2 class="cv-section-title">À Propos de moi</h2>
                                        <p id="out-philosophy" class="cv-text mb-4 italic" style="font-size: 0.85rem; line-height: 1.4;"></p>

                                        <h2 class="cv-section-title">Forces</h2>
                                        <ul id="out-strengths" class="skills-list mb-4"></ul>

                                        <h2 class="cv-section-title">Formations</h2>
                                        <div id="out-education"></div>

                                        <h2 class="cv-section-title">Compétences</h2>
                                        <ul id="out-skills" class="skills-list mb-4"></ul>

                                        <h2 class="cv-section-title">Langues</h2>
                                        <ul id="out-languages" class="lang-list mb-4"></ul>

                                        <h2 class="cv-section-title">Certifications</h2>
                                        <div id="out-certifications" class="cv-small-items"></div>
                                    </div>

                                    <!-- Main -->
                                    <div class="cv-right-col">
                                        <h2 class="cv-section-title">Résumé du Profil</h2>
                                        <p id="out-about" class="cv-text mb-6">Description...</p>

                                        <h2 class="cv-section-title">Expérience Professionnelle</h2>
                                        <div id="out-experience" class="mb-6"></div>

                                        <h2 class="cv-section-title">Projets Réalisés</h2>
                                        <div id="out-projects" class="mb-6"></div>

                                        <h2 class="cv-section-title">Références</h2>
                                        <div id="out-references" class="references-grid mb-6"></div>

                                        <!-- The 7 New Sections replacing red lines -->
                                        <div class="cv-custom-sections">
                                            <div class="mb-4">
                                                <h2 class="cv-section-title">Compétences Additionnelles</h2>
                                                <p id="out-skills-extra" class="cv-text"></p>
                                            </div>
                                            <div class="mb-4">
                                                <h2 class="cv-section-title">Outils Techniques</h2>
                                                <p id="out-tools" class="cv-text"></p>
                                            </div>
                                            <div class="mb-4">
                                                <h2 class="cv-section-title">Réalisations Professionnelles</h2>
                                                <p id="out-achievements" class="cv-text"></p>
                                            </div>
                                            <div class="mb-4">
                                                <h2 class="cv-section-title">Expérience Bénévole</h2>
                                                <p id="out-volunteer" class="cv-text"></p>
                                            </div>
                                            <div class="mb-4">
                                                <h2 class="cv-section-title">Ateliers / Formations</h2>
                                                <p id="out-workshops" class="cv-text"></p>
                                            </div>
                                            <div class="mb-4">
                                                <h2 class="cv-section-title">Intérêts Professionnels</h2>
                                                <p id="out-interests" class="cv-text"></p>
                                            </div>
                                            <div class="mb-4">
                                                <h2 class="cv-section-title">Informations Supplémentaires</h2>
                                                <p id="out-extra-info" class="cv-text"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Motivation Preview -->
                        <div id="motivation-container" class="preview-page-container hidden" style="display: none;">
                            <div id="motivation-preview" class="shadow-2xl flex flex-col bg-white text-gray-800" style="font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.5; height: 297mm; width: 210mm; position: relative; padding: 25mm; box-sizing: border-box; flex-shrink: 0;">
                                
                                <!-- Top Header Row: Trainee Left, Date Right -->
                                <div class="flex justify-between mb-8 items-start">
                                    <div class="text-left">
                                        <p class="font-bold text-lg" id="out-mot-nom-header"></p>
                                        <p class="text-sm" id="out-mot-tel-header"></p>
                                        <p class="text-sm" id="out-mot-email-header"></p>
                                        <p class="text-sm" id="out-mot-adresse-header"></p>
                                    </div>
                                    <div class="text-right flex flex-col items-end">
                                        <div class="border-b-2 border-gray-900 pb-1 mb-2 min-w-[200px]">
                                            <p id="out-mot-date-full" class="italic text-sm"></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Company Header: Shifted Right -->
                                <div class="self-end text-left mb-12" style="width: 50%;">
                                    <p class="font-bold" id="out-mot-service-preview">À l’attention du Service des Ressources Humaines</p>
                                    <p class="font-bold"><span class="font-normal italic">Entreprise :</span> <span id="out-mot-entreprise-dest" class="uppercase"></span></p>
                                    <p><span class="font-normal italic">Email :</span> <span id="out-mot-email-ent-dest"></span></p>
                                    <p id="out-mot-adresse-ent-dest" class="text-xs text-gray-600"></p>
                                </div>

                                <!-- Object -->
                                <div class="mb-10">
                                    <p class="text-xl font-black">Objet : <span id="out-mot-objet-title"></span></p>
                                </div>

                                <!-- Greeting -->
                                <div class="mb-6">
                                    <p id="out-mot-civilite-preview" class="font-bold"></p>
                                </div>

                                <!-- Body Text -->
                                <div class="text-justify mb-10 whitespace-pre-line flex-1">
                                    <p id="out-mot-message-preview"></p>
                                </div>

                                <!-- Closing Statement -->
                                <div class="mb-12">
                                    <p id="out-mot-cloture-preview"></p>
                                </div>

                                <!-- Signature Block: Right Aligned -->
                                <div class="self-end text-center min-w-[200px]">
                                    <p class="font-bold uppercase border-b border-gray-100 mb-2" id="out-mot-nom-footer"></p>
                                    <div class="h-24 flex items-center justify-center">
                                        <img id="out-mot-signature" src="" alt="Signature" class="max-h-full max-w-full object-contain" style="display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- Load main script at the end -->
    <script src="../js/createCV.js?v=<?= time() ?>"></script>
</body>
</html>
