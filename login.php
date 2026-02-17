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
    <title>StageMatch - Connexion</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="css/global.css"/>
    <link rel="stylesheet" href="css/login.css"/>
    <script src="js/global.js" defer></script>
    <script src="js/login.js" defer></script>
</head>
<body>

<div class="main-container">
    <!-- Section Gauche - Connexion -->
    <div class="login-section">
        <div class="login-content">
            <h2>Bon retour !</h2>
            <p class="subtitle">Veuillez vous connecter à votre compte StageMatch.</p>

            <form id="loginForm">
                <input type="hidden" id="userType" name="userType" value="etudiant">
                
                <input type="hidden" id="userType" name="userType" value="etudiant">

                <div class="input-group">
                    <input type="email" id="email" placeholder="Adresse e-mail" required />
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="input-group">
                    <input type="password" id="password" placeholder="Mot de passe" required />
                    <i class="fas fa-lock"></i>
                </div>
                
                <div class="options">
                    <label class="custom-checkbox">
                        <input type="checkbox" id="remember">
                        <span class="checkmark"></span>
                        Se souvenir de moi
                    </label>
                    <a href="#">Mot de passe oublié ?</a>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Se connecter</button>
            </form>
            
            <p class="switch-text">Pas encore de compte ? <a href="signup.php">Créer un compte</a></p>
        </div>
    </div>

    <!-- Section Droite - Design/Photo -->
    <div class="image-section">
        <h1>Connectez-vous à<br>votre futur.</h1>
        <p>StageMatch simplifie la rencontre entre les talents de demain et les entreprises innovantes en Mauritanie.</p>
    </div>
</div>

</body>
</html>

