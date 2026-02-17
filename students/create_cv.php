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
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <script src="../js/createCV.js" defer></script>
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
                <div class="mb-10 lg:flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Créateur de CV</h1>
                        <p class="text-gray-600">Concevez un CV d'exception pour décrocher votre stage idéal.</p>
                    </div>
                    <div class="mt-6 lg:mt-0 flex gap-4">
                        <button onclick="telechargerPDF()" class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-black transition flex items-center gap-2">
                            <i class="fas fa-download"></i> Exporter PDF
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                    <!-- Formulaire de saisie (40%) -->
                    <div class="lg:col-span-5 form-section space-y-6">
                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex items-center gap-4 mb-6">
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
                                <p class="text-xs text-blue-700">Cliquez pour ajouter une photo professionnelle</p>
                            </div>
                        </div>

                        <div class="cv-form-container space-y-4">
                            <div class="input-field">
                                <label>Nom complet</label>
                                <div class="relative">
                                    <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    <input type="text" id="nom" class="pl-11" placeholder="Ex: Donna Stroupe" value="<?= htmlspecialchars($_SESSION['user_prenom'].' '.$_SESSION['user_nom']) ?>">
                                </div>
                            </div>
                            <div class="input-field">
                                <label>Titre / Poste</label>
                                <div class="relative">
                                    <i class="fas fa-briefcase absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    <input type="text" id="poste" class="pl-11" placeholder="Ex: Sales Representative">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="input-field">
                                    <label>Téléphone</label>
                                    <div class="relative">
                                        <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                        <input type="text" id="tel" class="pl-11" placeholder="+123-456-7890" value="<?= htmlspecialchars($_SESSION['user_tel'] ?? '') ?>">
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
                                <label>Email</label>
                                <div class="relative">
                                    <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    <input type="email" id="email" class="pl-11" placeholder="hello@reallygreatsite.com" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="input-field">
                                <label>À propos de moi</label>
                                <textarea id="about" placeholder="Décrivez votre profil professionnel..."></textarea>
                            </div>
                            <div class="input-field">
                                <label>Expérience Professionnelle (Titre | Entreprise | Date | Description)</label>
                                <textarea id="experiences" placeholder="Ex: Sales Agent | Timmerman Industries | 2014-2015 | Visited offices..."></textarea>
                            </div>
                            <div class="input-field">
                                <label>Éducation (Diplôme | Université | Date)</label>
                                <textarea id="education" placeholder="Ex: BA Sales | Wardiere University | 2011-2015"></textarea>
                            </div>
                            <div class="input-field">
                                <label>Compétences (une par ligne)</label>
                                <textarea id="skills" placeholder="Fast-moving Consumer Goods"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="input-field">
                                    <label>Langues</label>
                                    <input type="text" id="languages" placeholder="English, French">
                                </div>
                                <div class="input-field">
                                    <label>Références</label>
                                    <input type="text" id="references" placeholder="Nom | Poste | Contact">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aperçu (60%) -->
                    <div class="lg:col-span-7">
                        <div id="cv-container">
                            <div id="cv-preview">
                                <!-- Header -->
                                <div class="cv-header-layout">
                                    <div class="cv-photo-container">
                                        <img id="cv-photo" alt="P">
                                    </div>
                                    <div class="cv-name-block">
                                        <h1 id="out-nom">DONNA STROUPE</h1>
                                        <p id="out-poste">Sales Representative</p>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div class="cv-body-layout">
                                    <!-- Sidebar -->
                                    <div class="cv-left-col">
                                        <div class="contact-section">
                                            <div class="contact-item">
                                                <i class="fas fa-phone"></i>
                                                <span id="out-tel">+123-456-7890</span>
                                            </div>
                                            <div class="contact-item">
                                                <i class="fas fa-envelope"></i>
                                                <span id="out-email">hello@site.com</span>
                                            </div>
                                            <div class="contact-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span id="out-ville">Nouakchott</span>
                                            </div>
                                        </div>

                                        <h2 class="cv-section-title">Education</h2>
                                        <div id="out-education">
                                            <!-- Items injected here -->
                                        </div>

                                        <h2 class="cv-section-title">Skills</h2>
                                        <ul id="out-skills" class="skills-list">
                                            <!-- Items injected here -->
                                        </ul>

                                        <h2 class="cv-section-title">Language</h2>
                                        <ul id="out-languages" class="lang-list">
                                            <!-- Items injected here -->
                                        </ul>
                                    </div>

                                    <!-- Main -->
                                    <div class="cv-right-col">
                                        <h2 class="cv-section-title">About Me</h2>
                                        <p id="out-about" class="cv-text">
                                            I am a Sales Representative professional who initializes and manages relationships with customers.
                                        </p>

                                        <h2 class="cv-section-title">Work Experience</h2>
                                        <div id="out-experience">
                                            <!-- Items injected here -->
                                        </div>

                                        <h2 class="cv-section-title" style="margin-top: 50px;">References</h2>
                                        <div id="out-references" class="references-grid">
                                            <!-- Injected here -->
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
