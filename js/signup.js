/* js/signup.js */
document.addEventListener("DOMContentLoaded", () => {
  const signupForm = document.getElementById("signupForm");
  const userTypeInput = document.getElementById("userType");
  const changeTypeBtn = document.getElementById("changeTypeBtn");
  const selectedTypeIcon = document.getElementById("selectedTypeIcon");
  const selectedTypeText = document.getElementById("selectedTypeText");

  // Direct Role Switch Logic (same as login)
  if (changeTypeBtn) {
    changeTypeBtn.addEventListener("click", function () {
      const currentType = userTypeInput.value;
      const newType = currentType === "etudiant" ? "entreprise" : "etudiant";

      userTypeInput.value = newType;

      // Update UI with classes and text
      if (newType === "etudiant") {
        selectedTypeIcon.className = "fas fa-user-graduate";
        selectedTypeText.textContent = "Compte Étudiant";
      } else {
        selectedTypeIcon.className = "fas fa-building";
        selectedTypeText.textContent = "Compte Entreprise";
      }
    });
  }

  if (signupForm) {
    signupForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const nom = document.getElementById("nom").value.trim();
      const prenom = document.getElementById("prenom").value.trim();
      const email = document.getElementById("email").value.trim();
      const telephone = document.getElementById("telephone").value.trim();
      const password = document.getElementById("password").value;
      const passwordConfirm = document.getElementById("password_confirm").value;
      const userType = userTypeInput.value;
      const terms = document.getElementById("terms").checked;
      const submitBtn = document.querySelector('button[type="submit"]');

      if (password !== passwordConfirm) {
        showMessage("Les mots de passe ne correspondent pas", "error");
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = "Inscription en cours...";

      const formData = new FormData();
      formData.append("action", "register");
      formData.append("nom", nom);
      formData.append("prenom", prenom);
      formData.append("email", email);
      formData.append("telephone", telephone);
      formData.append("password", password);
      formData.append("password_confirm", passwordConfirm);
      formData.append("type_compte", userType);

      fetch("include/auth.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showMessage("Inscription réussie ! Redirection...", "success");
            setTimeout(() => {
              window.location.href = "login.php";
            }, 1500);
          } else {
            showMessage(data.message, "error");
            submitBtn.disabled = false;
            submitBtn.textContent = "Créer le compte";
          }
        })
        .catch((error) => {
          console.error("Erreur:", error);
          showMessage("Erreur de connexion au serveur", "error");
          submitBtn.disabled = false;
          submitBtn.textContent = "Créer le compte";
        });
    });
  }
});
