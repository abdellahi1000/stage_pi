<?php
require_once 'include/session.php';
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['user_type'] === 'admin') {
        header("Location: administrator/index.php");
    } elseif ($_SESSION['user_type'] === 'entreprise') {
        header("Location: " . (($_SESSION['user_role'] === 'Administrator') ? "administrator/index.php" : "enterprise/index.php"));
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
    <link rel="stylesheet" href="css/dashboards.css"/>
    <link rel="stylesheet" href="css/login.css?v=<?= filemtime('css/login.css'); ?>"/>
    <script src="js/global.js" defer></script>
    <script src="js/login.js?v=<?= filemtime('js/login.js'); ?>" defer></script>
</head>
<body>

<div class="main-container">
    <!-- Section Gauche - Connexion -->
    <div class="login-section">
        <div class="login-content">
            <div class="mobile-branding">
                <img src="img/logo_mobile.svg" alt="StageMatch Logo" class="mobile-logo">
                <span class="mobile-app-name">Stage App</span>
            </div>
            <h2>Bon retour !</h2>
            <p class="subtitle">Veuillez vous connecter à votre compte StageMatch.</p>

            <form id="loginForm">
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
            
            <div style="text-align: center; margin-top: 25px; padding-bottom: 20px;">
                <p style="color: #666; font-size: 14px; margin-bottom: 8px;">Si l'utilisateur rencontre un problème, il peut nous contacter ici.</p>
                <button type="button" id="openContactModal" class="btn-contact-link" style="background: none; border: none; padding: 0; color: var(--secondary-color); font-weight: 800; font-size: 15.5px; cursor: pointer; transition: all 0.2s; font-family: inherit; margin: 0 auto; outline: none; display: inline-block;" onmouseover="this.style.opacity='0.8'; this.style.textDecoration='underline';" onmouseout="this.style.opacity='1'; this.style.textDecoration='none';">Contacter Nous</button>
            </div>

        </div>
    </div>

    <!-- Section Droite - Design/Photo -->
    <div class="image-section">
        <h1>Connectez-vous à<br>votre futur.</h1>
        <p>StageMatch simplifie la rencontre entre les talents de demain et les entreprises innovantes en Mauritanie.</p>
    </div>
</div>

<!-- Contact Modal -->
<div id="contactModal" class="modal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="background-color: #f9fafb; padding: 40px; border-radius: 20px; width: 95%; max-width: 600px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); animation: fadeIn 0.3s ease; position: relative; max-height: 95vh; overflow-y: auto; border: 1px solid #fff;">
        
        <button type="button" id="closeModalBtnTop" style="position: absolute; top: 20px; right: 20px; background: white; border: 1px solid #e5e7eb; border-radius: 50%; width: 36px; height: 36px; font-size: 16px; cursor: pointer; color: #6b7280; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: all 0.2s;" onmouseover="this.style.backgroundColor='#f3f4f6';" onmouseout="this.style.backgroundColor='white';"><i class="fas fa-times"></i></button>

        <div style="text-align: center; margin-bottom: 25px; position: relative;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background-color: #fff; border-radius: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 1rem; border: 1px solid #f3f4f6; padding: 12px;">
                <img src="img/logo_mobile.svg" alt="StageMatch" style="width: 40px; height: 40px;">
            </div>
            
            <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 4px;">
                <h2 style="font-size: 28px; font-weight: 800; color: #111827; margin: 0;">Demande de Problème</h2>
                <button type="button" class="btn-messages-modal" style="background: #ef4444; color: white; border: none; padding: 4px 12px; border-radius: 8px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 15px rgba(239, 68, 68, 0.3)';" onmouseout="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 10px rgba(239, 68, 68, 0.2)';">MESSAGES</button>
            </div>
            <p style="color: #6b7280; font-size: 14px; margin-top: 0;">Sécurité et Support Entreprise ou Etudiant</p>
        </div>

        <div style="background-color: #fff; padding: 25px; border-radius: 20px; border: 1px solid #f3f4f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05); position: relative; overflow: hidden; min-height: 400px;">
            <!-- Form View -->
            <form id="publicContactForm" style="margin: 0;" class="space-y-4">
                <input type="hidden" name="user_type" value="problem">
                
                <div style="margin-bottom: 18px;">
                    <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: #374151;">Titre du problème <span style="color:#ef4444">*</span></label>
                    <input type="text" id="contactSubject" required name="title" style="width: 100%; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 12px; font-size: 14px; background-color: #f9fafb; color: #374151; outline: none; transition: all 0.2s; box-sizing: border-box;" placeholder="Ex: Problème d'accès..." onfocus="this.style.borderColor='#3b82f6'; this.style.backgroundColor='#fff';" onblur="this.style.borderColor='#e5e7eb'; this.style.backgroundColor='#f9fafb';">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 18px;">
                    <div>
                        <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: #374151;">Numéro de Téléphone <span style="color:#ef4444">*</span></label>
                        <input type="tel" id="contactPhone" required name="phone" style="width: 100%; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 12px; font-size: 14px; background-color: #f9fafb; color: #374151; outline: none; transition: all 0.2s; box-sizing: border-box;" placeholder="+222 ..." onfocus="this.style.borderColor='#3b82f6'; this.style.backgroundColor='#fff';" onblur="this.style.borderColor='#e5e7eb'; this.style.backgroundColor='#f9fafb';">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: #374151;">Email <span style="color:#ef4444">*</span></label>
                        <input type="email" id="contactEmail" required name="email" style="width: 100%; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 12px; font-size: 14px; background-color: #f9fafb; color: #374151; outline: none; transition: all 0.2s; box-sizing: border-box;" placeholder="votre@email.com" onfocus="this.style.borderColor='#3b82f6'; this.style.backgroundColor='#fff';" onblur="this.style.borderColor='#e5e7eb'; this.style.backgroundColor='#f9fafb';">
                    </div>
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: #374151;">Type de Problème / Group</label>
                    <div style="position: relative;" class="custom-dropdown" id="dropdownProblem">
                        <input type="hidden" name="problem_type" required value="Sécurité">
                        <button type="button" style="width: 100%; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 12px!important; font-size: 14px; background-color: #ffffff; color: #374151; appearance: none; outline: none; transition: all 0.2s; box-sizing: border-box; text-align: left; position: relative;" onfocus="this.style.borderColor='#3b82f6'; this.style.backgroundColor='#fff';" onblur="this.style.borderColor='#e5e7eb'; this.style.backgroundColor='#ffffff';">
                            <span>Problème de Sécurité</span>
                            <i class="fas fa-chevron-down" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none;"></i>
                        </button>
                        <div class="dropdown-menu absolute z-20 w-full mt-2 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden py-2 opacity-0 invisible pointer-events-none translate-y-2 scale-95 transition-all duration-300">
                            <div class="dropdown-item" data-value="Sécurité">Problème de Sécurité</div>
                            <div class="dropdown-item" data-value="Accès">Problème d'Accès</div>
                            <div class="dropdown-item" data-value="Technique">Problème Technique</div>
                            <div class="dropdown-item" data-value="Autre">Autre</div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: #374151;">Description du problème <span style="color:#ef4444">*</span></label>
                    <textarea id="contactMessage" name="message" required style="width: 100%; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 12px; font-size: 14px; min-height: 120px; resize: vertical; background-color: #f9fafb; color: #374151; outline: none; transition: all 0.2s; font-family: inherit; box-sizing: border-box;" placeholder="Décrivez votre problème en détail..." onfocus="this.style.borderColor='#3b82f6'; this.style.backgroundColor='#fff';" onblur="this.style.borderColor='#e5e7eb'; this.style.backgroundColor='#f9fafb';"></textarea>
                </div>
                
                <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <button type="submit" id="submitContactBtn" style="width: 100%; background: #4f46e5; color: #fff; padding: 14px; border-radius: 12px; border: none; cursor: pointer; font-weight: 800; font-size: 15px; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 20px rgba(79, 70, 229, 0.3)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 14px rgba(79, 70, 229, 0.2)';">Envoyer</button>
                    <button type="button" id="closeContactModal" style="width: 100%; background: #f3f4f6; color: #4b5563; padding: 14px; border-radius: 12px; border: none; cursor: pointer; font-weight: 700; font-size: 15px; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#e5e7eb';" onmouseout="this.style.backgroundColor='#f3f4f6';">Fermer</button>
                </div>
                
                <div id="contactSuccessMsg" style="display: none; margin-top: 15px; padding: 15px; background-color: #f0fdf4; border: 1px solid #dcfce7; border-radius: 12px; text-align: center;">
                    <p style="color: #166534; font-size: 14px; font-weight: 700; margin: 0;">Votre demande a été envoyée avec succès.</p>
                    <p style="color: #166534; font-size: 12px; margin: 5px 0 0 0;">Nous répondrons à votre problème dans les plus brefs délais.</p>
                </div>
            </form>

            <!-- Chat View -->
            <div id="problemChatView" style="display: none; height: 400px; flex-direction: column;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid #f3f4f6; margin-bottom: 15px;">
                    <button type="button" id="backToForm" style="background: none; border: none; color: #4f46e5; font-weight: 800; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px;"><i class="fas fa-arrow-left"></i> Nouveau Problème</button>
                    <span id="chatEmailTag" style="font-size: 11px; font-weight: 700; color: #9ca3af; background: #f9fafb; padding: 4px 10px; border-radius: 6px;">Lookup...</span>
                </div>
                
                <div id="problemChatHistory" style="flex: 1; overflow-y: auto; padding-right: 5px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 12px;" class="custom-scrollbar">
                    <div style="text-align: center; color: #9ca3af; font-size: 12px; margin-top: 50px;">Chargement de vos messages...</div>
                </div>

                <div id="identifyUser" style="display: none; text-align: center; padding: 20px;">
                    <p style="font-size: 13px; color: #374151; font-weight: 700; margin-bottom: 15px;">Entrez votre email pour voir vos conversations :</p>
                    <input type="email" id="lookupEmail" style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 10px; box-sizing: border-box;" placeholder="votre@email.com">
                    <button type="button" id="btnLookup" style="width: 100%; background: #4f46e5; color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 800; cursor: pointer;">Voir mes messages</button>
                </div>

                <form id="publicFollowUpForm" style="display: none; gap: 10px;">
                    <input type="hidden" id="publicActiveId">
                    <input type="text" id="publicFollowUpText" required placeholder="Votre message..." style="flex: 1; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; outline: none;">
                    <button type="submit" style="background: #4f46e5; color: white; border: none; width: 44px; height: 44px; border-radius: 10px; cursor: pointer;"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

</body>
</html>

