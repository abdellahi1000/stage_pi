/* js/login.js */
document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  const userTypeInput = document.getElementById("userType");
  const changeTypeBtn = document.getElementById("changeTypeBtn");
  const selectedTypeIcon = document.getElementById("selectedTypeIcon");
  const selectedTypeText = document.getElementById("selectedTypeText");

  // Handle Login Submission
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const email = document.getElementById("email").value;
      const password = document.getElementById("password").value;
      const remember = document.getElementById("remember").checked;
      const submitBtn = document.querySelector('button[type="submit"]');

      submitBtn.disabled = true;
      submitBtn.textContent = "Connexion...";

      const formData = new FormData();
      formData.append("action", "login");
      formData.append("email", email);
      formData.append("password", password);
      formData.append("remember", remember ? "1" : "0");
      formData.append("type_compte", userTypeInput.value); // Ensure account type is sent

      fetch("include/auth.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showMessage("Connexion réussie ! Redirection...", "success");
            setTimeout(() => {
              window.location.href = data.redirect;
            }, 1000);
          } else {
            showMessage(data.message, "error");
            submitBtn.disabled = false;
            submitBtn.textContent = "Se connecter";
          }
        })
        .catch((error) => {
          console.error("Erreur:", error);
          showMessage("Erreur serveur", "error");
          submitBtn.disabled = false;
          submitBtn.textContent = "Se connecter";
        });
    });
  }
});
