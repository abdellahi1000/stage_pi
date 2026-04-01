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

      function uppercaseInput(e) {
        e.target.value = e.target.value.toUpperCase();
      }

      // Update UI with classes and text
      if (newType === "etudiant") {
        selectedTypeIcon.className = "fas fa-user-graduate";
        selectedTypeText.textContent = "Compte Étudiant";

        document.getElementById('nomIcon').className = "fas fa-user";
        document.getElementById('nom').placeholder = "Nom";
        document.getElementById('nom').removeEventListener("input", uppercaseInput);
        document.getElementById('prenomGroup').style.display = "block";
        document.getElementById('prenom').required = true;
        document.getElementById('nameFields').style.gridTemplateColumns = "1fr 1fr";

        document.getElementById('enterpriseFields').style.display = "none";
        document.getElementById('enterpriseWarningMsg').style.display = "none";
        const entInputs = document.querySelectorAll('#enterpriseFields input, #enterpriseFields select');
        entInputs.forEach(el => el.required = false);
      } else {
        selectedTypeIcon.className = "fas fa-building";
        selectedTypeText.textContent = "Compte Entreprise";

        document.getElementById('nomIcon').className = "fas fa-building";
        document.getElementById('nom').placeholder = "Nom de l'entreprise";
        document.getElementById('nom').addEventListener("input", uppercaseInput);
        document.getElementById('prenomGroup').style.display = "none";
        document.getElementById('prenom').required = false;
        document.getElementById('prenom').value = "";
        document.getElementById('nameFields').style.gridTemplateColumns = "1fr";

        document.getElementById('enterpriseFields').style.display = "block";
        document.getElementById('enterpriseWarningMsg').style.display = "block";
        const entInputs = document.querySelectorAll('#enterpriseFields input, #enterpriseFields select');
        entInputs.forEach(el => el.required = true);
      }
    });
  }

  // Handle Modern File Input display name
  document.querySelectorAll(".hidden-file-input").forEach((input) => {
    input.addEventListener("change", function () {
      const fileNameDisplay = this.parentElement.querySelector(".file-name-display");
      if (this.files && this.files.length > 0) {
        fileNameDisplay.textContent = this.files[0].name;
        fileNameDisplay.style.color = "var(--secondary-color)";
      } else {
        fileNameDisplay.textContent = "Choisir un fichier PDF";
        fileNameDisplay.style.color = "rgba(255, 255, 255, 0.6)";
      }
    });
  });

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

      // Final check for required fields if entreprise
      if (userType === 'entreprise') {
          const industry = document.getElementById('industry_sector').value;
          const size = document.getElementById('company_size').value;
          
          if (!industry || !size) {
              showMessage("Veuillez sélectionner le secteur et la taille de l'entreprise", "error");
              return;
          }
      }

      submitBtn.disabled = true;
      submitBtn.textContent = "Inscription en cours...";

      const formData = new FormData(signupForm);
      // fallback for fields without name attribute but needed
      formData.set("action", "register");
      formData.set("nom", nom);
      if (prenom) formData.set("prenom", prenom);
      else formData.set("prenom", "");
      formData.set("email", email);
      formData.set("telephone", telephone);
      formData.set("password", password);
      formData.set("password_confirm", passwordConfirm);
      formData.set("type_compte", userType);

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
