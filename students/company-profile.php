<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Entreprise - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <style>
        /* Facebook-style Profile Layout */
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #f0f2f5;
            min-height: 100vh;
        }

        .profile-cover {
            height: 320px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .profile-cover-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.8;
        }

        .profile-header {
            position: relative;
            margin-top: -120px;
            padding: 0 20px;
            z-index: 10;
        }

        .profile-info {
            display: flex;
            align-items: flex-end;
            gap: 20px;
        }

        .profile-avatar {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            border: 4px solid white;
            background: white;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-details {
            flex: 1;
            padding-bottom: 20px;
        }

        .profile-name {
            font-size: 32px;
            font-weight: 700;
            color: white;
            margin: 0 0 8px 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .profile-bio {
            color: rgba(255,255,255,0.9);
            font-size: 16px;
            line-height: 1.4;
            margin: 0;
            max-width: 600px;
        }

        .profile-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }

        .profile-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .profile-btn.primary {
            background: #1877f2;
            color: white;
        }

        .profile-btn.primary:hover {
            background: #1665d3;
            transform: translateY(-1px);
        }

        .profile-btn.secondary {
            background: rgba(255,255,255,0.2);
            color: white;
            backdrop-filter: blur(10px);
        }

        .profile-btn.secondary:hover {
            background: rgba(255,255,255,0.3);
        }

        .content-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #1c1e21;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #1877f2;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1877f2;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1877f2;
            flex-shrink: 0;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 16px;
            color: #1c1e21;
            font-weight: 500;
        }

        .contact-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            background: #f8f9fa;
            transition: all 0.2s;
        }

        .contact-item:hover {
            background: #e9ecef;
        }

        .contact-icon {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            background: #1877f2;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .social-links {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #1877f2;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
        }

        .social-link:hover {
            background: #1665d3;
            transform: translateY(-2px);
        }

        .posts-section {
            margin-top: 32px;
        }

        .post-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .post-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .post-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #1877f2;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .post-meta {
            flex: 1;
        }

        .post-company {
            font-weight: 600;
            color: #1c1e21;
            margin-bottom: 4px;
        }

        .post-time {
            font-size: 13px;
            color: #6c757d;
        }

        .post-content {
            color: #1c1e21;
            line-height: 1.6;
        }

        .loading-skeleton {
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .error-message {
            text-align: center;
            padding: 60px 20px;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #f8f9fa;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }

        .error-title {
            font-size: 24px;
            font-weight: 700;
            color: #1c1e21;
            margin-bottom: 12px;
        }

        .error-description {
            font-size: 16px;
            color: #6c757d;
            max-width: 400px;
            margin: 0 auto;
        }

        .contact-action-btn {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            background: #1877f2;
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 14px;
        }

        .contact-action-btn:hover {
            background: #1665d3;
            transform: translateY(-2px);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fefefe;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #1c1e21;
        }

        .close-button {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
            transition: color 0.2s;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-button:hover {
            color: #1c1e21;
        }

        .modal-body {
            padding: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-info {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .profile-details {
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .profile-actions {
                flex-direction: column;
                width: 100%;
            }

            .profile-btn {
                width: 100%;
                justify-content: center;
            }

            .modal-content {
                width: 95%;
                max-height: 85vh;
            }
        }
    </style>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?>">
    <div class="profile-container">
        <!-- Header with Cover -->
        <div class="profile-cover">
            <img id="coverImage" class="profile-cover-image" src="" alt="Company Cover">
            <div class="profile-header">
                <div class="profile-info">
                    <div class="profile-avatar">
                        <img id="companyLogo" src="" alt="Company Logo">
                    </div>
                    <div class="profile-details">
                        <h1 id="companyName" class="profile-name">Chargement...</h1>
                        <p id="companyBio" class="profile-bio">Chargement des informations...</p>
                        <div class="profile-actions">
                            <button class="profile-btn primary" onclick="contactCompany()">
                                <i class="fas fa-envelope"></i>
                                Contacter
                            </button>
                            <button class="profile-btn secondary" onclick="viewOffers()">
                                <i class="fas fa-briefcase"></i>
                                Voir les offres
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div style="padding: 20px;">
            <!-- Statistics Section -->
            <div class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-chart-bar"></i>
                    Statistiques de l'entreprise
                </h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div id="statOffers" class="stat-number">-</div>
                        <div class="stat-label">Offres publiées</div>
                    </div>
                    <div class="stat-card">
                        <div id="statInterns" class="stat-number">-</div>
                        <div class="stat-label">Stagiaires acceptés</div>
                    </div>
                    <div class="stat-card">
                        <div id="statProjects" class="stat-number">-</div>
                        <div class="stat-label">Projets réalisés</div>
                    </div>
                    <div class="stat-card">
                        <div id="statViews" class="stat-number">-</div>
                        <div class="stat-label">Vues du profil</div>
                    </div>
                </div>
            </div>

            <!-- Information Section -->
            <div class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Informations sur l'entreprise
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Secteur d'activité</div>
                            <div id="companyIndustry" class="info-value">-</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Date de création</div>
                            <div id="companyFounded" class="info-value">-</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Taille de l'entreprise</div>
                            <div id="companySize" class="info-value">-</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Site web</div>
                            <div id="companyWebsite" class="info-value">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-address-book"></i>
                    Coordonnées
                </h2>
                <div class="info-grid">
                    <div class="info-item" id="emailsBox" style="cursor: pointer;">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Emails</div>
                            <div id="emailsDisplay" class="info-value">-</div>
                        </div>
                    </div>
                    <div class="info-item" id="phonesBox" style="cursor: pointer;">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Téléphones</div>
                            <div id="phonesDisplay" class="info-value">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Section with Map -->
            <div class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-map-marked-alt"></i>
                    Localisation
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Adresse complète</div>
                            <div id="companyFullAddress" class="info-value">-</div>
                        </div>
                    </div>
                </div>
                
                <!-- Map Container -->
                <div style="margin-top: 20px;">
                    <div id="companyMap" style="height: 300px; border-radius: 12px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                        <div style="text-align: center;">
                            <i class="fas fa-map-marked-alt" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <p>Carte en cours de chargement...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Links -->
            <div class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-share-alt"></i>
                    Réseaux sociaux
                </h2>
                <div class="social-links" id="socialLinks">
                    <!-- Social links will be populated here -->
                </div>
            </div>

            <!-- Posts/Activity Section -->
            <div class="content-section posts-section">
                <h2 class="section-title">
                    <i class="fas fa-newspaper"></i>
                    Actualités et annonces
                </h2>
                <div id="companyPosts">
                    <!-- Posts will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="content-section" style="display: none;">
        <div style="text-align: center; padding: 40px;">
            <div class="loading-skeleton" style="width: 60px; height: 60px; border-radius: 50%; background: #f8f9fa; margin: 0 auto 20px;"></div>
            <p style="color: #6c757d; font-size: 16px;">Chargement du profil...</p>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="content-section" style="display: none;">
        <div class="error-message">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="error-title">Profil non disponible</h2>
            <p class="error-description">Les informations de cette entreprise ne sont pas temporairement accessibles.</p>
        </div>
    </div>

    <!-- Contact Modal -->
    <div id="contactModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Contacter l'entreprise</h3>
                <button onclick="closeContactModal()" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
                <div id="contactOptions">
                    <!-- Contact options will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Emails Popup -->
    <div id="emailsPopup" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Emails de l'entreprise</h3>
                <button onclick="closeEmailsPopup()" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
                <div id="emailsList" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Emails will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Phones Popup -->
    <div id="phonesPopup" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Téléphones de l'entreprise</h3>
                <button onclick="closePhonesPopup()" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
                <div id="phonesList" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Phones will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let companyData = null;

        // Get company ID from URL
        function getCompanyIdFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('id') || urlParams.get('company');
        }

        // Load company data
        function loadCompanyProfile() {
            const companyId = getCompanyIdFromUrl();
            const companyName = new URLSearchParams(window.location.search).get('name');

            if (!companyId && !companyName) {
                showError('Aucune entreprise spécifiée');
                return;
            }

            // Show loading state
            document.getElementById('loadingState').style.display = 'block';

            let apiUrl = `../api/entreprise_profile.php?`;
            if (companyId) {
                apiUrl += `entreprise_id=${companyId}`;
            } else {
                apiUrl += `entreprise_name=${encodeURIComponent(companyName)}`;
            }

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Company data:', data);
                    if (data.success) {
                        companyData = data.company;
                        renderCompanyProfile(data.company);
                    } else {
                        showError(data.message || 'Erreur lors du chargement');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Erreur de connexion: ' + error.message);
                });
        }

        // Render company profile
        function renderCompanyProfile(company) {
            // Hide loading state
            document.getElementById('loadingState').style.display = 'none';

            // Header section
            document.getElementById('companyName').textContent = company.nom || 'Entreprise';
            document.getElementById('companyBio').textContent = company.bio || 'Découvrez cette entreprise innovante.';

            // Logo with fallback
            if (company.photo_profil) {
                const logoImg = document.getElementById('companyLogo');
                logoImg.src = '../' + company.photo_profil;
                logoImg.onerror = function() {
                    this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(company.nom)}&background=667eea&color=fff&bold=true&size=160`;
                };
            } else {
                document.getElementById('companyLogo').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(company.nom)}&background=667eea&color=fff&bold=true&size=160`;
            }

            // Cover image (use a gradient background if no cover)
            document.getElementById('coverImage').style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';

            // Statistics
            document.getElementById('statOffers').textContent = company.active_offers || '0';
            document.getElementById('statInterns').textContent = company.accepted_interns || '0';
            document.getElementById('statProjects').textContent = company.achievements ? company.achievements.length : '0';
            document.getElementById('statViews').textContent = '0'; // Would need tracking

            // Information
            document.getElementById('companyIndustry').textContent = company.industry_sector || 'Non spécifié';
            document.getElementById('companyFounded').textContent = company.date_creation || 'Non spécifié';
            
            // Team size display
            let teamSizeDisplay = 'Non spécifié';
            if (company.taille) {
                // Map database values to French labels
                const teamSizeMap = {
                    '1-10': '1-10 employés',
                    '11-50': '11-50 employés',
                    '51-200': '51-200 employés',
                    '201-500': '201-500 employés',
                    '501-1000': '501-1000 employés',
                    '1000+': '+1000 employés'
                };
                teamSizeDisplay = teamSizeMap[company.taille] || company.taille;
            }
            document.getElementById('companySize').textContent = teamSizeDisplay;
            document.getElementById('companyWebsite').textContent = company.website ? company.website : 'Non spécifié';

            // Contact
            const addressParts = [];
            if (company.adresse) addressParts.push(company.adresse);
            if (company.ville) addressParts.push(company.ville);
            if (company.pays) addressParts.push(company.pays);
            const fullAddress = addressParts.join(', ') || 'Non spécifiée';
            document.getElementById('companyFullAddress').textContent = fullAddress;

            // Display emails and phones
            renderEmails(company.emails);
            renderPhones(company.phones);

            // Initialize map if address is available
            if (fullAddress !== 'Non spécifiée') {
                initializeMap(fullAddress, company.nom);
            } else {
                document.getElementById('companyMap').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #6c757d;">
                        <i class="fas fa-map-marked-alt" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                        <p>Aucune adresse spécifiée</p>
                    </div>
                `;
            }

            // Social links
            const socialLinks = document.getElementById('socialLinks');
            socialLinks.innerHTML = '';

            if (company.linkedin_url) {
                socialLinks.innerHTML += `<a href="${company.linkedin_url}" target="_blank" class="social-link" title="LinkedIn">
                    <i class="fab fa-linkedin"></i>
                </a>`;
            }
            if (company.facebook_url) {
                socialLinks.innerHTML += `<a href="${company.facebook_url}" target="_blank" class="social-link" title="Facebook">
                    <i class="fab fa-facebook"></i>
                </a>`;
            }
            if (company.twitter_url) {
                socialLinks.innerHTML += `<a href="${company.twitter_url}" target="_blank" class="social-link" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>`;
            }
            if (company.instagram_url) {
                socialLinks.innerHTML += `<a href="${company.instagram_url}" target="_blank" class="social-link" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>`;
            }

            // Posts/Activity
            renderCompanyPosts(company);
        }

        // Render emails display
        function renderEmails(emails) {
            const emailsDisplay = document.getElementById('emailsDisplay');
            const emailsList = document.getElementById('emailsList');
            
            if (!emails || emails.length === 0) {
                emailsDisplay.textContent = 'Non disponible';
                document.getElementById('emailsBox').style.cursor = 'default';
                document.getElementById('emailsBox').onclick = null;
                return;
            }

            emailsDisplay.textContent = `${emails.length} email${emails.length > 1 ? 's' : ''}`;
            document.getElementById('emailsBox').style.cursor = 'pointer';
            document.getElementById('emailsBox').onclick = openEmailsPopup;

            // Build emails list for popup
            emailsList.innerHTML = '';
            emails.forEach(item => {
                const email = item.email || item;
                const emailItem = document.createElement('div');
                emailItem.style.cssText = 'display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid #e9ecef; border-radius: 8px; background: #f8f9fa;';
                
                emailItem.innerHTML = `
                    <div style="flex: 1; word-break: break-all;">
                        <div style="font-size: 14px; color: #1c1e21; font-weight: 500;">${escapeHtml(email)}</div>
                    </div>
                    <button onclick="copyToClipboard('${escapeHtml(email)}')" class="contact-action-btn" title="Copier">
                        <i class="fas fa-copy"></i>
                    </button>
                    <a href="mailto:${email}" class="contact-action-btn" title="Envoyer un email">
                        <i class="fas fa-paper-plane"></i>
                    </a>
                `;
                emailsList.appendChild(emailItem);
            });
        }

        // Render phones display
        function renderPhones(phones) {
            const phonesDisplay = document.getElementById('phonesDisplay');
            const phonesList = document.getElementById('phonesList');
            
            if (!phones || phones.length === 0) {
                phonesDisplay.textContent = 'Non disponible';
                document.getElementById('phonesBox').style.cursor = 'default';
                document.getElementById('phonesBox').onclick = null;
                return;
            }

            phonesDisplay.textContent = `${phones.length} numéro${phones.length > 1 ? 's' : ''}`;
            document.getElementById('phonesBox').style.cursor = 'pointer';
            document.getElementById('phonesBox').onclick = openPhonesPopup;

            // Build phones list for popup
            phonesList.innerHTML = '';
            phones.forEach(item => {
                const phone = item.phone_number || item;
                const type = item.type || 'Téléphone';
                const phoneItem = document.createElement('div');
                phoneItem.style.cssText = 'display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid #e9ecef; border-radius: 8px; background: #f8f9fa;';
                
                phoneItem.innerHTML = `
                    <div style="flex: 1;">
                        <div style="font-size: 12px; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">${escapeHtml(type)}</div>
                        <div style="font-size: 14px; color: #1c1e21; font-weight: 500;">${escapeHtml(phone)}</div>
                    </div>
                    <button onclick="copyToClipboard('${escapeHtml(phone)}')" class="contact-action-btn" title="Copier">
                        <i class="fas fa-copy"></i>
                    </button>
                    <a href="tel:${phone}" class="contact-action-btn" title="Appeler">
                        <i class="fas fa-phone"></i>
                    </a>
                    <a href="https://wa.me/${phone.replace(/\\D/g, '')}" target="_blank" class="contact-action-btn" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                `;
                phonesList.appendChild(phoneItem);
            });
        }

        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showNotification('Copié dans le presse-papiers');
            }).catch(err => {
                console.error('Erreur de copie:', err);
            });
        }

        // Show notification
        function showNotification(message) {
            const notif = document.createElement('div');
            notif.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 12px 20px; border-radius: 8px; z-index: 10000; animation: slideIn 0.3s ease-out; font-weight: 500;';
            notif.textContent = message;
            notif.innerHTML += `
                <style>
                @keyframes slideIn {
                    from { transform: translateX(400px); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                </style>
            `;
            document.body.appendChild(notif);
            setTimeout(() => {
                notif.style.animation = 'slideIn 0.3s ease-out reverse';
                setTimeout(() => notif.remove(), 300);
            }, 2000);
        }

        // Escape HTML for security
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize map with OpenStreetMap (free alternative to Google Maps)
        function initializeMap(address, companyName) {
            // Using Nominatim API for geocoding (free)
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = data[0].lat;
                        const lon = data[0].lon;
                        
                        // Create simple map using OpenStreetMap
                        document.getElementById('companyMap').innerHTML = `
                            <iframe 
                                width="100%" 
                                height="300" 
                                frameborder="0" 
                                style="border:0; border-radius: 12px;"
                                src="https://www.openstreetmap.org/export/embed.html?bbox=${lon-0.01},${lat-0.01},${lon+0.01},${lat+0.01}&layer=mapnik&marker=${lat},${lon}">
                            </iframe>
                        `;
                    } else {
                        document.getElementById('companyMap').innerHTML = `
                            <div style="text-align: center; padding: 40px; color: #6c757d;">
                                <i class="fas fa-map-marked-alt" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                <p>Adresse non trouvée sur la carte</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Map error:', error);
                    document.getElementById('companyMap').innerHTML = `
                        <div style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-map-marked-alt" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <p>Erreur de chargement de la carte</p>
                        </div>
                    `;
                });
        }

        // Render company posts
        function renderCompanyPosts(company) {
            const postsContainer = document.getElementById('companyPosts');
            postsContainer.innerHTML = '';

            if (company.achievements && company.achievements.length > 0) {
                company.achievements.forEach(achievement => {
                    const postCard = document.createElement('div');
                    postCard.className = 'post-card';
                    postCard.innerHTML = `
                        <div class="post-header">
                            <div class="post-avatar">${achievement.type === 'website' ? '<i class="fas fa-globe"></i>' : '<i class="fas fa-trophy"></i>'}</div>
                            <div class="post-meta">
                                <div class="post-company">${company.nom}</div>
                                <div class="post-time">Récemment</div>
                            </div>
                        </div>
                        <div class="post-content">
                            <h4 style="margin-bottom: 8px; font-weight: 600;">${escapeHtml(achievement.title)}</h4>
                            ${achievement.description ? `<p style="margin-bottom: 12px;">${escapeHtml(achievement.description)}</p>` : ''}
                            ${achievement.url ? `<a href="${achievement.url}" target="_blank" style="color: #1877f2; text-decoration: none; font-weight: 500;">
                                <i class="fas fa-external-link-alt"></i> Voir le projet
                            </a>` : ''}
                        </div>
                    `;
                    postsContainer.appendChild(postCard);
                });
            } else {
                postsContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #6c757d;">
                        <i class="fas fa-newspaper" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                        <p>Aucune actualité pour le moment</p>
                    </div>
                `;
            }
        }

        // Show error
        function showError(message) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
            const errorDesc = document.querySelector('.error-description');
            if (errorDesc) {
                errorDesc.textContent = message;
            }
        }

        // Contact functions
        function contactCompany() {
            if (!companyData) return;
            
            const modal = document.getElementById('contactModal');
            const options = document.getElementById('contactOptions');
            
            let contactHtml = '<div style="display: flex; flex-direction: column; gap: 16px;">';
            
            if (companyData.emails && companyData.emails.length > 0) {
                contactHtml += `
                    <div class="contact-item" style="cursor: pointer;" onclick="openEmailsPopup()">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <div class="info-label">Emails</div>
                            <div class="info-value">${companyData.emails.length} email${companyData.emails.length > 1 ? 's' : ''}</div>
                        </div>
                    </div>
                `;
            }
            
            if (companyData.phones && companyData.phones.length > 0) {
                contactHtml += `
                    <div class="contact-item" style="cursor: pointer;" onclick="openPhonesPopup()">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <div class="info-label">Téléphones</div>
                            <div class="info-value">${companyData.phones.length} numéro${companyData.phones.length > 1 ? 's' : ''}</div>
                        </div>
                    </div>
                `;
            }
            
            contactHtml += '</div>';
            options.innerHTML = contactHtml;
            modal.style.display = 'flex';
        }

        // Popup control functions
        function openEmailsPopup() {
            document.getElementById('emailsPopup').style.display = 'flex';
        }

        function closeEmailsPopup() {
            document.getElementById('emailsPopup').style.display = 'none';
        }

        function openPhonesPopup() {
            document.getElementById('phonesPopup').style.display = 'flex';
        }

        function closePhonesPopup() {
            document.getElementById('phonesPopup').style.display = 'none';
        }

        function closeContactModal() {
            document.getElementById('contactModal').style.display = 'none';
        }

        function viewOffers() {
            if (companyData) {
                window.location.href = `offres.php?search=${encodeURIComponent(companyData.nom)}`;
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', loadCompanyProfile);

        // Close modals on backdrop click
        document.getElementById('emailsPopup').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEmailsPopup();
            }
        });

        document.getElementById('phonesPopup').addEventListener('click', function(e) {
            if (e.target === this) {
                closePhonesPopup();
            }
        });

        document.getElementById('contactModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeContactModal();
            }
        });
    </script>
</body>
</html>
