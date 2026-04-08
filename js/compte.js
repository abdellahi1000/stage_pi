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

  // Delete profile photo
  const deletePhotoBtn = document.getElementById("deletePhotoBtn");
  if (deletePhotoBtn) {
    deletePhotoBtn.addEventListener("click", function () {
      const formData = new FormData();
      formData.append("action", "delete_photo");
      fetch("../api/user.php", {
        method: "POST",
        body: formData,
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            showMessage("Photo supprimée.", "success");
            const defaultAvatar = "https://ui-avatars.com/api/?name=" + encodeURIComponent((document.querySelector('[name="prenom"]')?.value || "") + " " + (document.querySelector('[name="nom"]')?.value || "")) + "&background=random";
            const settingsPreview = document.getElementById("settings-profil-preview");
            if (settingsPreview) settingsPreview.src = defaultAvatar;
            if (profilImg) profilImg.src = defaultAvatar;
            document.querySelectorAll(".sidebar img").forEach((img) => (img.src = defaultAvatar));
            deletePhotoBtn.classList.add("hidden");
            window.location.reload();
          } else {
            showMessage(data.message || "Erreur.", "error");
          }
        })
        .catch((err) => {
          console.error("Erreur suppression photo:", err);
          showMessage("Erreur lors de la suppression.", "error");
        });
    });
  }

  function uploadPhoto(file) {
    const formData = new FormData();
    formData.append("photo", file);

    fetch("../include/upload_photo.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showMessage("Photo mise à jour !", "success");
          const settingsPreview = document.getElementById("settings-profil-preview");
          if (settingsPreview) settingsPreview.src = data.photo_url;
          if (profilImg) profilImg.src = data.photo_url;
          const sidebarImgs = document.querySelectorAll(".sidebar img");
          if (sidebarImgs.length > 0) {
            sidebarImgs.forEach(img => img.src = data.photo_url);
          } else {
            window.location.reload();
          }
          const deletePhotoBtn = document.getElementById("deletePhotoBtn");
          if (deletePhotoBtn) deletePhotoBtn.classList.remove("hidden");
        } else {
          showMessage(data.message || "Erreur de mise à jour", "error");
        }
      })
      .catch((err) => console.error("Erreur upload photo:", err));
  }

  // Settings Sidebar Logic
  const settingsToggle = document.getElementById("settingsToggle");
  const settingsSidebar = document.getElementById("settingsSidebar");
  const closeSettings = document.getElementById("closeSettings");
  const cancelSettings = document.getElementById("cancelSettings");
  const settingsOverlay = document.getElementById("settingsOverlay");
  const saveSettings = document.getElementById("saveSettings");
  const settingsTabs = document.querySelectorAll(".settings-tab");

  function openSettings() {
    settingsSidebar.classList.remove("translate-x-full");
    settingsOverlay.classList.remove("hidden");
    setTimeout(() => settingsOverlay.classList.remove("opacity-0"), 10);
  }

  function closeSettingsSidebar() {
    settingsSidebar.classList.add("translate-x-full");
    settingsOverlay.classList.add("opacity-0");
    setTimeout(() => settingsOverlay.classList.add("hidden"), 300);
  }

  if (settingsToggle) settingsToggle.addEventListener("click", openSettings);
  if (closeSettings) closeSettings.addEventListener("click", closeSettingsSidebar);
  if (cancelSettings) cancelSettings.addEventListener("click", closeSettingsSidebar);
  if (settingsOverlay) settingsOverlay.addEventListener("click", closeSettingsSidebar);

  // Tabs switching
  settingsTabs.forEach(tab => {
    tab.addEventListener("click", (e) => {
      e.preventDefault();
      // Remove active from all tabs
      settingsTabs.forEach(t => {
        t.classList.remove("active", "bg-white", "text-blue-600", "border-l-4");
        t.classList.add("text-gray-600", "border-transparent");
      });
      // Set active on clicked
      tab.classList.add("active", "bg-white", "text-blue-600", "border-l-4");
      tab.classList.remove("text-gray-600", "border-transparent");

      // Hide all panes
      document.querySelectorAll(".settings-pane").forEach(pane => {
        pane.classList.remove("block");
        pane.classList.add("hidden");
      });

      // Show target pane
      const targetId = tab.getAttribute("data-tab");
      document.getElementById(targetId).classList.remove("hidden");
      document.getElementById(targetId).classList.add("block");
    });
  });

  // Save Settings Fake/Real Submit logic placeholder
  if (saveSettings) {
    saveSettings.addEventListener("click", (e) => {
      e.preventDefault();
      const form = document.getElementById("form-settings");
      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());

      // Basic 2FA checkbox handling
      data.auth_2fa = formData.has('auth_2fa') ? 1 : 0;
      data.alertes_offres = formData.has('alertes_offres') ? 1 : 0;

      // Password change validation
      const oldPw = formData.get('old_password') || '';
      const newPw = formData.get('new_password') || '';
      const confirmPw = formData.get('confirm_password') || '';
      if (newPw || confirmPw) {
        if (!oldPw || !newPw || !confirmPw) {
          showMessage("Veuillez remplir tous les champs de mot de passe.", "warning");
          return;
        }
        if (String(newPw).length < 8) {
          showMessage("Le nouveau mot de passe doit contenir au moins 8 caractères.", "warning");
          return;
        }
        if (newPw !== confirmPw) {
          showMessage("Les nouveaux mots de passe ne correspondent pas.", "error");
          return;
        }
      }

      // Handle file upload separately or convert everything into a PUT request
      // We'll update api/user.php to handle this.
      saveUserSettings(formData);
    });
  }

  function saveUserSettings(formData) {
    const submitBtn = document.getElementById("saveSettings");
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
    submitBtn.disabled = true;

    // We can POST this to user.php action=update_profile_student
    formData.append("action", "update_profile_student");

    fetch("../api/user.php", {
      method: "POST", // Sending as POST since we have files (CV/Motivation)
      body: formData
    })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          showMessage("Profil mis à jour avec succès !", "success");
          setTimeout(() => {
            closeSettingsSidebar();
            window.location.reload();
          }, 1000);
        } else {
          showMessage(res.message || "Erreur de mise à jour.", "error");
        }
      })
      .catch(err => {
        console.error(err);
        showMessage("Erreur serveur.", "error");
      })
      .finally(() => {
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Enregistrer les modifications';
        submitBtn.disabled = false;
      });
  }

  // Fetch account data
  loadAccountData();

  // Student password reset (without current password)
  const studentResetBtn = document.getElementById("studentResetPasswordBtn");
  if (studentResetBtn) {
    studentResetBtn.addEventListener("click", () => {
      const newPw = document.getElementById("student_reset_pw").value;
      const confirmPw = document.getElementById("student_reset_pw_confirm").value;

      if (!newPw || !confirmPw) {
        showMessage("Veuillez saisir et confirmer le nouveau mot de passe.", "warning");
        return;
      }
      if (newPw.length < 8) {
        showMessage("Le mot de passe doit contenir au moins 8 caractères.", "warning");
        return;
      }
      if (newPw !== confirmPw) {
        showMessage("Les mots de passe ne correspondent pas.", "error");
        return;
      }

      const params = new URLSearchParams();
      params.append("action", "reset_password_logged_in");
      params.append("new_password", newPw);
      params.append("confirm_password", confirmPw);

      fetch("../api/user.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
      })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showMessage(res.message || "Mot de passe réinitialisé.", "success");
            document.getElementById("student_reset_pw").value = "";
            document.getElementById("student_reset_pw_confirm").value = "";
          } else {
            showMessage(res.message || "Impossible de réinitialiser le mot de passe.", "error");
          }
        })
        .catch(() => {
          showMessage("Erreur serveur lors de la réinitialisation du mot de passe.", "error");
        });
    });
  }

  // Document Manager Logic
  const cvUploadInput = document.getElementById("cv_upload_input");
  const lmUploadInput = document.getElementById("lm_upload_input");

  if (cvUploadInput) {
    cvUploadInput.addEventListener("change", (e) => handleFileUpload(e.target.files[0], "cv"));
  }
  if (lmUploadInput) {
    lmUploadInput.addEventListener("change", (e) => handleFileUpload(e.target.files[0], "motivation"));
  }

  function handleFileUpload(file, type) {
    if (!file) return;
    const formData = new FormData();
    formData.append("file", file);
    formData.append("type", type);

    fetch("../api/documents.php", {
      method: "POST",
      body: formData
    })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          showMessage(type === "cv" ? "CV ajouté !" : "Lettre ajoutée !", "success");
          loadDocuments();
        } else {
          showMessage(res.message, "error");
        }
      })
      .catch(err => console.error(err));
  }

  function showConfirmModal(title, message, onConfirm) {
    const modalId = "confirm-modal-" + Date.now();
    const modalHtml = `
      <div id="${modalId}" class="fixed inset-0 z-[10000] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity opacity-0" id="${modalId}-backdrop"></div>
        <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-sm p-8 transform scale-95 opacity-0 transition-all duration-300 border border-gray-100" id="${modalId}-content">
          <div class="w-16 h-16 ${message.toLowerCase().includes('cv') || message.toLowerCase().includes('lettre') ? 'bg-blue-50 text-blue-500' : 'bg-red-50 text-red-500'} rounded-2xl flex items-center justify-center mb-6 mx-auto">
            <i class="fas fa-trash-alt text-2xl"></i>
          </div>
          <h3 class="text-2xl font-black text-center text-gray-900 mb-2">${title}</h3>
          <p class="text-center text-gray-500 text-sm mb-8 leading-relaxed font-medium">${message}</p>
          <div class="flex gap-4">
            <button class="flex-1 bg-gray-50 border border-gray-100 text-gray-600 py-3.5 rounded-xl font-bold hover:bg-gray-100 transition-colors" id="${modalId}-cancel">Annuler</button>
            <button class="flex-1 bg-red-600 text-white py-3.5 rounded-xl font-black shadow-lg shadow-red-200 hover:bg-red-700 transition-all hover:-translate-y-1" id="${modalId}-confirm">Supprimer</button>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML("beforeend", modalHtml);

    const modalEl = document.getElementById(modalId);
    const backdrop = document.getElementById(`${modalId}-backdrop`);
    const content = document.getElementById(`${modalId}-content`);
    const btnCancel = document.getElementById(`${modalId}-cancel`);
    const btnConfirm = document.getElementById(`${modalId}-confirm`);

    // Animate in
    setTimeout(() => {
      backdrop.classList.remove("opacity-0");
      content.classList.remove("scale-95", "opacity-0");
    }, 10);

    const close = () => {
      backdrop.classList.add("opacity-0");
      content.classList.add("scale-95", "opacity-0");
      setTimeout(() => modalEl.remove(), 300);
    };

    btnCancel.addEventListener("click", close);
    backdrop.addEventListener("click", close);

    btnConfirm.addEventListener("click", () => {
      close();
      if (typeof onConfirm === "function") {
        onConfirm();
      }
    });
  }

  window.deleteDocument = function (id) {
    showConfirmModal(
      "Supprimer le document",
      "Êtes-vous sûr de vouloir supprimer ce document de votre dossier ? Cette action est irréversible.",
      () => {
        const formData = new FormData();
        formData.append("action", "delete");
        formData.append("id", id);

        fetch("../api/documents.php", {
          method: "POST",
          body: formData
        })
          .then(r => r.json())
          .then(res => {
            if (res.success) {
              showMessage("Document supprimé", "success");
              loadDocuments();
            } else {
              showMessage(res.message, "error");
            }
          })
          .catch(err => console.error(err));
      }
    );
  };

  function loadDocuments() {
    const cvList = document.getElementById("cv_manager_list");
    const lmList = document.getElementById("lm_manager_list");

    fetch("../api/documents.php")
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          renderDocs(data.documents.filter(d => d.type === 'cv'), cvList, 'cv');
          renderDocs(data.documents.filter(d => d.type === 'motivation'), lmList, 'motivation');
        }
      });
  }

  function renderDocs(docs, container, type) {
    if (!container) return;
    if (docs.length === 0) {
      container.innerHTML = `<div class="p-8 text-center bg-gray-50/50 rounded-2xl border border-dashed border-gray-100"><p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Aucun ${type.toUpperCase()} choisi</p></div>`;
      return;
    }

    container.innerHTML = docs.map(doc => `
      <div class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md transition-all group">
        <div class="flex items-center gap-3 overflow-hidden">
          <div class="w-10 h-10 flex items-center justify-center rounded-xl ${type === 'cv' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600'} shrink-0">
            <i class="fas ${type === 'cv' ? 'fa-file-pdf' : 'fa-file-alt'} text-lg"></i>
          </div>
          <div class="overflow-hidden">
            <p class="text-[11px] font-black text-gray-800 truncate" title="${doc.file_name}">${doc.file_name}</p>
            <p class="text-[9px] text-gray-400 font-medium">${new Date(doc.created_at).toLocaleDateString()}</p>
          </div>
        </div>
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
          <a href="../${doc.file_path}" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition" title="Voir">
            <i class="fas fa-eye text-xs"></i>
          </a>
          <button type="button" onclick="deleteDocument(${doc.id})" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition" title="Supprimer">
            <i class="fas fa-trash-alt text-xs"></i>
          </button>
        </div>
      </div>
    `).join('');
  }

  // Initial load
  loadDocuments();
});

function loadAccountData() {
  const listAttente = document.getElementById("list-attente");
  const listAcceptees = document.getElementById("list-acceptees");
  const listCompletes = document.getElementById("list-completes");

  fetch("../api/user.php")
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

  // Populate display elements
  const dispName = document.getElementById('display-name');
  const dispBio = document.getElementById('display-bio');
  const dispNiveau = document.getElementById('display-niveau');
  const dispStatut = document.getElementById('display-statut');
  const dispSkills = document.getElementById('display-skills');
  const dispSpecialite = document.getElementById('display-specialite');
  const dispUniversite = document.getElementById('display-universite');

  if (dispName) dispName.textContent = (user.prenom || '') + ' ' + (user.nom || '');
  if (dispBio) dispBio.textContent = user.bio || "Aucune description fournie.";
  if (dispNiveau) dispNiveau.textContent = user.niveau_etudes || "Non précisé";
  if (dispUniversite) dispUniversite.textContent = user.universite || "Non précisée";
  if (dispDomaine) dispDomaine.textContent = user.domaine_formation || "Non renseigné";
  if (dispSpecialite) dispSpecialite.textContent = user.titre_professionnel || "Étudiant Passionné";

  if (dispStatut) {
    const statusMap = {
      'disponible': { text: 'Disponible', class: 'bg-green-100 text-green-700' },
      'en_formation': { text: 'En Formation', class: 'bg-orange-100 text-orange-700' },
      'recherche_active': { text: 'Recherche active', class: 'bg-blue-100 text-blue-700' }
    };
    const s = statusMap[user.statut_disponibilite] || { text: 'Non défini', class: 'bg-gray-100 text-gray-700' };
    dispStatut.textContent = s.text;
    dispStatut.className = `inline-block px-3 py-1 rounded-lg text-xs font-bold uppercase ${s.class}`;
  }

  if (dispVille) {
    const loc = [user.ville, user.pays].filter(x => x).join(', ');
    dispVille.textContent = loc || "Localisation non définie";
  }

  if (dispSkills) {
    if (user.skills) {
      const skillsArr = user.skills.split(',').map(s => s.trim()).filter(s => s);
      dispSkills.innerHTML = skillsArr.map(s => `<span class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-xl text-xs font-bold border border-blue-100 hover:bg-blue-600 hover:text-white transition-all cursor-default">${s}</span>`).join('');
    } else {
      dispSkills.innerHTML = '<span class="px-3 py-1.5 bg-gray-50 text-gray-500 rounded-xl text-xs font-bold border border-gray-100">Aucune compétence listée</span>';
    }
  }

  // Populate preview in settings
  const settingsPreview = document.getElementById('settings-profil-preview');
  if (settingsPreview && user.photo_profil) {
    settingsPreview.src = user.photo_profil.startsWith("http") ? user.photo_profil : "../" + user.photo_profil;
  }

  // Update profile image on main page (without camera icon)
  const profilImg = document.getElementById("profil-img");
  if (profilImg && user.photo_profil) {
    profilImg.src = user.photo_profil.startsWith("http") ? user.photo_profil : "../" + user.photo_profil;
  }

  // Show/hide delete photo button
  const deletePhotoBtn = document.getElementById("deletePhotoBtn");
  if (deletePhotoBtn) {
    if (user.photo_profil) {
      deletePhotoBtn.classList.remove("hidden");
    } else {
      deletePhotoBtn.classList.add("hidden");
    }
  }

  // Populate settings modal if it exists
  const formSettings = document.getElementById('form-settings');
  if (formSettings) {
    if (formSettings.elements['nom']) formSettings.elements['nom'].value = user.nom || '';
    if (formSettings.elements['prenom']) formSettings.elements['prenom'].value = user.prenom || '';
    if (formSettings.elements['email']) formSettings.elements['email'].value = user.email || '';
    if (formSettings.elements['telephone']) formSettings.elements['telephone'].value = user.telephone || '';
    if (formSettings.elements['bio']) formSettings.elements['bio'].value = user.bio || '';
    if (formSettings.elements['skills']) formSettings.elements['skills'].value = user.skills || '';
    if (formSettings.elements['specialite']) formSettings.elements['specialite'].value = user.specialite || '';
    if (formSettings.elements['domaine_formation']) formSettings.elements['domaine_formation'].value = user.domaine_formation || '';
    if (formSettings.elements['titre_professionnel']) formSettings.elements['titre_professionnel'].value = user.titre_professionnel || '';
    if (formSettings.elements['statut_disponibilite']) formSettings.elements['statut_disponibilite'].value = user.statut_disponibilite || 'disponible';
    if (formSettings.elements['niveau_etudes']) formSettings.elements['niveau_etudes'].value = user.niveau_etudes || '';
    if (formSettings.elements['universite']) formSettings.elements['universite'].value = user.universite || '';
    if (formSettings.elements['langue']) formSettings.elements['langue'].value = user.langue || 'fr';
    if (formSettings.elements['alertes_offres']) formSettings.elements['alertes_offres'].checked = (user.alertes_offres == '1' || user.alertes_offres === null);

    // Calculate Completion
    const fieldsToTrack = [
      user.nom, user.prenom, user.email, user.telephone, user.bio,
      user.photo_profil, user.cv_path, user.skills, user.niveau_etudes, user.specialite
    ];
    let filled = 0;
    fieldsToTrack.forEach(f => {
      if (f && f.toString().trim() !== '') filled++;
    });
    const percentage = Math.round((filled / fieldsToTrack.length) * 100);

    const progressText = document.getElementById('profileProgressText');
    const progressBar = document.getElementById('profileProgressBar');
    if (progressText) progressText.textContent = percentage + '%';
    if (progressBar) progressBar.style.width = percentage + '%';
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
