// Composant de menu réutilisable pour StageMatch
// Ce fichier génère automatiquement le menu latéral avec le style cohérent

// Déterminer le type d'utilisateur (peut être stocké dans sessionStorage ou localStorage)
function getUserType() {
  // Par défaut, on détecte selon l'URL ou on utilise le localStorage
  const userType = localStorage.getItem("userType");
  if (userType) return userType;

  // Détection basée sur l'URL
  const path = window.location.pathname;
  if (
    path.includes("accueil-entreprise") ||
    path.includes("GereerLesCandudates")
  ) {
    return "entreprise";
  }
  return "etudiant"; // Par défaut
}

// Obtenir le chemin relatif vers la racine selon la page actuelle
function getBasePath() {
  const path = window.location.pathname;
  if (
    path.includes("/HOME/") ||
    path.includes("/Les_Offres/") ||
    path.includes("/CV/") ||
    path.includes("/Candudatures/") ||
    path.includes("/GereerLesCandudates/") ||
    path.includes("/MonCompte/") ||
    path.includes("/Aide")
  ) {
    return "../";
  }
  return "./";
}

// Générer le HTML du menu selon le type d'utilisateur
function generateMenuHTML(userType) {
  const basePath = getBasePath();
  const isEtudiant = userType === "etudiant";
  const icon = isEtudiant ? "fa-user-graduate" : "fa-building";
  const label = isEtudiant ? "Étudiant" : "Entreprise";
  const accueilPath = isEtudiant
    ? `${basePath}HOME/accueil-etudiant.html`
    : `${basePath}HOME/accueil-entreprise.html`;

  let menuHTML = `
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-screen w-64 bg-gradient-to-b from-blue-900 to-purple-900 text-white shadow-2xl z-40 md:relative md:top-0 md:translate-x-0 hidden md:block">
        <!-- En-tête du menu sur mobile -->
        <div class="h-16 flex items-center justify-center px-6 md:hidden bg-gradient-to-r from-blue-800 to-purple-800">
            <h2 class="text-xl font-bold text-white">Menu ${label}</h2>
        </div>
        
        <!-- Logo dans le menu (desktop) -->
        <div class="hidden md:flex items-center justify-center py-6 border-b border-white border-opacity-20">
            <div class="bg-white bg-opacity-10 backdrop-blur-sm p-3 rounded-xl">
                <i class="fas ${icon} text-3xl text-white"></i>
            </div>
        </div>
        
        <nav class="p-4 space-y-1 mt-2">
            <!-- Section Principale -->
            <div class="mb-4">
                <p class="text-xs uppercase tracking-wider text-gray-400 px-4 mb-2 font-semibold">Navigation</p>
                <a href="${accueilPath}" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-10 transition-all duration-200 font-medium" id="menu-accueil">
                    <i class="fas fa-home text-lg"></i>
                    <span>Accueil</span>
                </a>`;

  if (isEtudiant) {
    menuHTML += `
                <a href="${basePath}Les_Offres/Offres.html" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-10 transition-all duration-200 font-medium mt-1" id="menu-offres">
                    <i class="fas fa-search text-lg"></i>
                    <span>Rechercher des Offres</span>
                </a>
                <a href="${basePath}CV/createCV.html" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-10 transition-all duration-200 font-medium mt-1" id="menu-cv">
                    <i class="fas fa-file-alt text-lg"></i>
                    <span>Créer/Modifier CV</span>
                </a>
                <a href="${basePath}Candudatures/MesCandudatures.html" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-10 transition-all duration-200 font-medium mt-1" id="menu-candidatures">
                    <i class="fas fa-paper-plane text-lg"></i>
                    <span>Mes Candidatures</span>
                </a>`;
  } else {
    menuHTML += `
                <a href="${basePath}Les_Offres/Offres.html" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-10 transition-all duration-200 font-medium mt-1" id="menu-offres">
                    <i class="fas fa-plus-circle text-lg"></i>
                    <span>Déposer une Offre</span>
                </a>
                <a href="${basePath}GereerLesCandudates/GCandudate.html" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-10 transition-all duration-200 font-medium mt-1" id="menu-candidats">
                    <i class="fas fa-users text-lg"></i>
                    <span>Gérer les Candidats</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-10 transition-all duration-200 font-medium mt-1" id="menu-mes-offres">
                    <i class="fas fa-list text-lg"></i>
                    <span>Mes Offres</span>
                </a>`;
  }

  menuHTML += `
            </div>
            
            <!-- Séparateur -->
            <div class="border-t border-white border-opacity-20 my-4"></div>
            
            <!-- Section Compte -->
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-400 px-4 mb-2 font-semibold">Paramètres</p>
                <a href="${basePath}MonCompte/Compte.html" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-10 transition-all duration-200 font-medium" id="menu-compte">
                    <i class="fas fa-user-circle text-lg"></i>
                    <span>Mon Compte</span>
                </a>
                <a href="${basePath}Aide&FAQ/FAQ.html" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-10 transition-all duration-200 font-medium mt-1" id="menu-aide">
                    <i class="fas fa-question-circle text-lg"></i>
                    <span>Aide & FAQ</span>
                </a>
            </div>
        </nav>
    </div>`;

  return menuHTML;
}

// Marquer le lien actif selon la page actuelle
function setActiveMenuItem() {
  const path = window.location.pathname;
  let activeId = null;

  if (
    path.includes("accueil-etudiant") ||
    path.includes("accueil-entreprise")
  ) {
    activeId = "menu-accueil";
  } else if (path.includes("Offres")) {
    activeId = "menu-offres";
  } else if (path.includes("createCV")) {
    activeId = "menu-cv";
  } else if (path.includes("MesCandudatures")) {
    activeId = "menu-candidatures";
  } else if (path.includes("GCandudate")) {
    activeId = "menu-candidats";
  } else if (path.includes("Compte")) {
    activeId = "menu-compte";
  } else if (path.includes("FAQ")) {
    activeId = "menu-aide";
  }

  if (activeId) {
    const activeLink = document.getElementById(activeId);
    if (activeLink) {
      activeLink.classList.remove("hover:bg-white", "hover:bg-opacity-10");
      activeLink.classList.add(
        "bg-gradient-to-r",
        "from-purple-600",
        "to-purple-700",
        "shadow-lg",
        "transform",
        "hover:scale-105",
      );
    }
  }
}

// Initialiser le menu au chargement de la page
function initMenu() {
  const userType = getUserType();
  const menuContainer = document.getElementById("menu-container");

  if (menuContainer) {
    menuContainer.innerHTML = generateMenuHTML(userType);
    setActiveMenuItem();

    // Ajouter le toggle pour mobile
    const sidebarToggle = document.getElementById("sidebarToggle");
    const sidebar = document.getElementById("sidebar");

    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener("click", function () {
        sidebar.classList.toggle("hidden");
      });
    }
  }
}

// Lancer l'initialisation quand le DOM est prêt
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initMenu);
} else {
  initMenu();
}
