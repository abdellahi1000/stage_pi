<?php
require_once 'include/session.php';
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['user_type'] === 'entreprise') {
        header("Location: enterprise/index.php");
    } else {
        header("Location: students/index.php");
    }
    exit;
}
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
    <link rel="stylesheet" href="css/login.css"/>
    <script src="js/global.js" defer></script>
    <script src="js/signup.js" defer></script>
</head>
<body>

<div class="main-container">
    <!-- Section Gauche - Inscription -->
    <div class="login-section">
        <div class="login-content" style="max-width: 450px;">
            <h2>Rejoignez-nous !</h2>
            <p class="subtitle">Créez votre compte StageMatch en quelques secondes.</p>

            <form id="signupForm">
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

                <!-- Previous inputs ... -->
                <div class="grid-form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="input-group">
                        <input type="text" id="nom" placeholder="Nom" required />
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="input-group">
                        <input type="text" id="prenom" placeholder="Prénom" required />
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <div class="input-group">
                    <input type="email" id="email" placeholder="Adresse e-mail" required />
                    <i class="fas fa-envelope"></i>
                </div>

                <div class="input-group">
                    <input type="tel" id="telephone" placeholder="Numéro de téléphone" required />
                    <i class="fas fa-phone"></i>
                </div>

                <div class="grid-form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="input-group">
                        <input type="password" id="password" placeholder="Mot de passe" required />
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="input-group">
                        <input type="password" id="password_confirm" placeholder="Confirmer" required />
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
    </div>
</div>

</body>
</html>

