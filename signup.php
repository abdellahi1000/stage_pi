<?php
require_once 'include/session.php';
require_once 'include/lookups.php';
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['user_type'] === 'entreprise') {
        header("Location: enterprise/index.php");
    } else {
        header("Location: students/index.php");
    }
    exit;
}

$sm_industry_sectors = sm_get_industry_sectors();
$sm_company_sizes = sm_get_company_sizes();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>StageMatch - Inscription</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="css/global.css"/>
    <link rel="stylesheet" href="css/dashboards.css"/>
    <link rel="stylesheet" href="css/login.css?v=<?= filemtime('css/login.css'); ?>"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/global.js" defer></script>
    <script src="js/signup.js" defer></script>
</head>
<body>

<div class="main-container">
    <!-- Section Gauche - Inscription -->
    <div class="login-section">
        <div class="login-content" style="max-width: 450px;">
            <div class="mobile-branding">
                <img src="img/logo_mobile.svg" alt="StageMatch Logo" class="mobile-logo">
                <span class="mobile-app-name">Stage App</span>
            </div>
            <h2>Rejoignez-nous !</h2>
            <p class="subtitle">Créez votre compte StageMatch en quelques secondes.</p>

            <form id="signupForm" enctype="multipart/form-data">
                <input type="hidden" id="userType" name="userType" value="etudiant">
                
                <div class="role-switch-container">
                    <div class="role-info">
                        <i class="fas fa-user-graduate" id="selectedTypeIcon" style="font-size: 24px;"></i>
                        <span id="selectedTypeText" style="font-size: 16px;">Compte Étudiant</span>
                    </div>
                    <button type="button" class="switch-btn" id="changeTypeBtn" title="Changer de rôle">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path class="arrow-top" d="M17 2L21 6L17 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path class="line-top" d="M3 6H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path class="arrow-bottom" d="M7 22L3 18L7 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path class="line-bottom" d="M21 18H3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <div class="input-group" id="enterpriseNameGroup" style="display: none; margin-bottom: 20px;">
                    <input type="text" id="company_name" name="company_name" placeholder="NOM DE L'ENTREPRISE" />
                    <i class="fas fa-building"></i>
                </div>

                <!-- Previous inputs ... -->
                <div class="grid-form grid-2-cols" id="nameFields">
                    <div class="input-group">
                        <input type="text" id="nom" name="nom" placeholder="Votre Nom (Admin)" required />
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="input-group" id="prenomGroup">
                        <input type="text" id="prenom" name="prenom" placeholder="Votre Prénom" required />
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <div class="input-group">
                    <input type="email" id="email" name="email" placeholder="Adresse e-mail" required />
                    <i class="fas fa-envelope"></i>
                </div>

                <div class="input-group">
                    <input type="tel" id="telephone" name="telephone" placeholder="Numéro de téléphone" required />
                    <i class="fas fa-phone"></i>
                </div>

                <!-- BEGIN ENTERPRISE SPECIFIC FIELDS -->
                <div id="enterpriseFields" style="display: none; margin-top: 15px;">
                    <div class="grid-form grid-2-cols mb-4">
                        <div class="input-group">
                            <input type="text" id="commercial_reg_num" name="commercial_reg_num" placeholder="Numéro de Registre de Commerce" />
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="input-group">
                            <input type="text" id="tax_id" name="tax_id" placeholder="Numéro d'Identification Fiscale (NIF)" />
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>

                    <div class="input-group" style="margin-bottom: 15px;">
                        <input type="text" id="address" name="address" placeholder="Adresse complète de l'entreprise" />
                        <i class="fas fa-map-marker-alt"></i>
                    </div>

                    <div class="grid-form grid-2-cols mb-4">
                        <div class="input-group">
                            <div class="relative custom-dropdown w-full" id="dropdownSector" style="margin-bottom: 0;">
                                <input type="hidden" id="industry_sector" name="industry_sector" required>
                                <button type="button" class="w-full" style="padding: 12px 15px 12px 40px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; background: rgba(0, 0, 0, 0.2); color: rgba(255, 255, 255, 0.7); font-size: 14px; text-align: left; position: relative;">
                                    <span class="truncate">Secteur d'activité</span>
                                    <i class="fas fa-chevron-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.6); pointer-events: none;"></i>
                                </button>
                                <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                                    <?php foreach ($sm_industry_sectors as $sector): ?>
                                        <div class="dropdown-item" data-value="<?php echo htmlspecialchars($sector['code']); ?>" style="color: #333;">
                                            <?php echo htmlspecialchars($sector['label']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <i class="fas fa-briefcase" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.6); z-index: 10;"></i>
                        </div>
                        <div class="input-group">
                            <div class="relative custom-dropdown w-full" id="dropdownSize" style="margin-bottom: 0;">
                                <input type="hidden" id="company_size" name="company_size" required>
                                <button type="button" class="w-full" style="padding: 12px 15px 12px 40px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; background: rgba(0, 0, 0, 0.2); color: rgba(255, 255, 255, 0.7); font-size: 14px; text-align: left; position: relative;">
                                    <span class="truncate">Taille de l'entreprise</span>
                                    <i class="fas fa-chevron-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.6); pointer-events: none;"></i>
                                </button>
                                <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                                    <?php foreach ($sm_company_sizes as $size): ?>
                                        <div class="dropdown-item" data-value="<?php echo htmlspecialchars($size['code']); ?>" style="color: #333;">
                                            <?php echo htmlspecialchars($size['label']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <i class="fas fa-users" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.6); z-index: 10;"></i>
                        </div>
                    </div>

                    <div class="input-group" style="margin-bottom: 15px;">
                        <input type="number" id="year_established" name="year_established" placeholder="Année de création (ex: 2010)" min="1800" max="2099" />
                        <i class="fas fa-calendar-alt"></i>
                    </div>

                    <div class="doc-upload-section" style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <p style="margin-bottom: 10px; font-size: 14px; color: #fff; font-weight: bold;">
                            <i class="fas fa-file-pdf" style="margin-right: 5px; color: #ff5252;"></i> Documents Officiels (PDF uniquement)
                        </p>
                        <p style="font-size: 12px; color: rgba(255,255,255,0.7); margin-bottom: 15px;">
                          Seules les entreprises avec documents officiels pourront être vérifiées.
                        </p>

                        <div class="space-y-3">
                            <div class="file-input-wrapper">
                                <label for="doc_registry" class="file-label">
                                    <span class="file-label-text">Registre de Commerce *</span>
                                    <div class="file-input-container">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span class="file-name-display">Choisir un fichier PDF</span>
                                    </div>
                                    <input type="file" id="doc_registry" name="doc_registry" accept=".pdf" class="hidden-file-input" />
                                </label>
                            </div>

                            <div class="file-input-wrapper">
                                <label for="doc_tax" class="file-label">
                                    <span class="file-label-text">Numéro d'Identification Fiscale (NIF) *</span>
                                    <div class="file-input-container">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span class="file-name-display">Choisir un fichier PDF</span>
                                    </div>
                                    <input type="file" id="doc_tax" name="doc_tax" accept=".pdf" class="hidden-file-input" />
                                </label>
                            </div>

                            <div class="file-input-wrapper">
                                <label for="doc_stamp" class="file-label">
                                    <span class="file-label-text">Document cacheté/signé officiel *</span>
                                    <div class="file-input-container">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span class="file-name-display">Choisir un fichier PDF</span>
                                    </div>
                                    <input type="file" id="doc_stamp" name="doc_stamp" accept=".pdf" class="hidden-file-input" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END ENTERPRISE SPECIFIC FIELDS -->

                <div class="grid-form grid-2-cols">
                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder="Mot de passe" required />
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="input-group">
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirmer" required />
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
                
                <div class="options">
                    <label class="custom-checkbox">
                        <input type="checkbox" id="terms" required>
                        <span class="checkmark"></span>
                        J'accepte les conditions d'utilisation
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Créer mon compte</button>
            </form>
            
            <p class="switch-text">Déjà un compte ? <a href="login.php">Se connecter</a></p>
        </div>
    </div>

    <!-- Section Droite - Design/Photo -->
    <div class="image-section">
        <h1>Bâtissez votre<br>avenir aujourd'hui.</h1>
        <p>Rejoignez la plus grande communauté d'étudiants et d'entreprises en Mauritanie.</p>
        <p id="enterpriseWarningMsg" style="display: none; background: rgba(255,0,0,0.2); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,0,0,0.3); margin-top: 20px; font-weight: bold; color: #fff;">
            <i class="fas fa-exclamation-triangle" style="margin-right: 5px; color: #ffeb3b;"></i>
            Enterprise accounts are restricted to officially registered companies only.
        </p>
    </div>
</div>

</body>
</html>

