/* js/compte.js */
document.addEventListener("DOMContentLoaded", () => {
  // UI elements
  const searchInputHeader = document.getElementById("searchInputHeader");
  const searchBoxHeader = document.getElementById("searchBoxHeader");
  const photoInput = document.getElementById("photo-input");
  const profilImg = document.getElementById("profil-img");

  // Search box animation
  if (searchInputHeader && searchBoxHeader) {
    searchInputHeader.addEventListener("focus", () => {
      searchBoxHeader.classList.add("focused");
    });
    searchInputHeader.addEventListener("blur", () => {
      if (searchInputHeader.value === "") {
        searchBoxHeader.classList.remove("focused");
      }
    });
  }

  // Profile photo upload preview
  if (photoInput && profilImg) {
    photoInput.addEventListener("change", function (e) {
      if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function (event) {
          profilImg.src = event.target.result;
          profilImg.classList.remove("profil-photo-placeholder");
        };
        reader.readAsDataURL(e.target.files[0]);

        // Optionnel: Envoyer vers le serveur
        uploadPhoto(e.target.files[0]);
      }
    });
  }

  function uploadPhoto(file) {
    const formData = new FormData();
    formData.append("photo", file);

    fetch("api/user.php", {
      // endpoint supposed to handle photo upload
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showMessage("Photo mise à jour !", "success");
        }
      })
      .catch((err) => console.error("Erreur upload photo:", err));
  }

  // Fetch account data
  loadAccountData();
});

function loadAccountData() {
  const listAttente = document.getElementById("list-attente");
  const listAcceptees = document.getElementById("list-acceptees");
  const listCompletes = document.getElementById("list-completes");

  fetch("api/user.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateUserInfo(data.user);
        renderCandidatures(
          data.candidatures_attente,
          listAttente,
          "En attente",
        );
        renderCandidatures(
          data.candidatures_acceptees,
          listAcceptees,
          "Acceptée",
        );
        renderStages(data.stages_completes, listCompletes);
      } else {
        showMessage(data.message, "error");
      }
    })
    .catch((err) => {
      console.error("Erreur chargement données compte:", err);
      // showMessage('Erreur lors du chargement des données', 'error');
    });
}

function updateUserInfo(user) {
  if (!user) return;
  document.getElementById("nom").textContent = user.nom || "";
  document.getElementById("prenom").textContent = user.prenom || "";
  if (user.photo_profil) {
    const profilImg = document.getElementById("profil-img");
    profilImg.src = user.photo_profil;
    profilImg.classList.remove("profil-photo-placeholder");
  }
}

function renderCandidatures(candidatures, container, typeLabel) {
  if (!container) return;
  container.innerHTML = "";

  if (!candidatures || candidatures.length === 0) {
    container.innerHTML = `<p class="text-gray-500 italic">Aucune candidature ${typeLabel.toLowerCase()}.</p>`;
    return;
  }

  candidatures.forEach((c) => {
    const card = document.createElement("div");
    card.className = "entreprise-card";
    card.innerHTML = `
            <h3>${esc(c.entreprise)}</h3>
            <p><strong>Offre :</strong> ${esc(c.titre)}</p>
            <p>Lieu : ${esc(c.localisation)}</p>
            <p>Postulé le : ${new Date(c.date_candidature).toLocaleDateString()}</p>
        `;
    container.appendChild(card);
  });
}

function renderStages(stages, container) {
  if (!container) return;
  container.innerHTML = "";

  if (!stages || stages.length === 0) {
    container.innerHTML =
      '<p class="text-gray-500 italic">Aucun stage déjà effectué.</p>';
    return;
  }

  stages.forEach((s) => {
    const card = document.createElement("div");
    card.className = "entreprise-card";
    card.innerHTML = `
            <h3>${esc(s.entreprise)}</h3>
            <p><strong>Poste :</strong> ${esc(s.poste)}</p>
            <p>Lieu : ${esc(s.localisation)}</p>
            <p>Période : ${new Date(s.date_debut).toLocaleDateString()} - ${new Date(s.date_fin).toLocaleDateString()}</p>
        `;
    container.appendChild(card);
  });
}
