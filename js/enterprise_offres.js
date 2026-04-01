/* js/enterprise_offres.js */
document.addEventListener("DOMContentLoaded", () => {
  const grid = document.getElementById("offresGrid");
  const modal = document.getElementById("modalOffre");
  const form = document.getElementById("formOffre");
  const btnNew = document.getElementById("btnNewOffre");
  const closeBtns = document.querySelectorAll(".close-modal");
  const modalTitle = document.getElementById("modalTitle");
  const globalVisibilityToggle = document.getElementById(
    "global-visibility-toggle",
  );

  let activeFilters = {
    search: "",
    statut: "",
    type: "",
  };

  let allOffres = [];

  loadOffres();
  loadGlobalVisibility();

  // --- Global Visibility ---
  function loadGlobalVisibility() {
    fetch("../api/user.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success && globalVisibilityToggle) {
          globalVisibilityToggle.checked = data.user.visibilite_entreprise == 1;
        }
      });
  }

  if (globalVisibilityToggle) {
    globalVisibilityToggle.addEventListener("change", () => {
      const isVisible = globalVisibilityToggle.checked ? 1 : 0;
      fetch("../api/user.php", {
        method: "PUT",
        body: new URLSearchParams({ visibilite_entreprise: isVisible }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (!data.success) {
            alert("Erreur de mise à jour");
            globalVisibilityToggle.checked = !globalVisibilityToggle.checked;
          } else {
            showMessage("Visibilité globale mise à jour", "success");
          }
        });
    });
  }

  // --- Modal Logic ---
  btnNew.addEventListener("click", () => {
    modalTitle.textContent = "Nouvelle Offre";
    form.reset();
    document.getElementById("offreId").value = "";
    // Reset custom dropdowns in form
    resetFormDropdowns();
    openModal();
  });

  closeBtns.forEach((btn) => {
    btn.addEventListener("click", closeModal);
  });

  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  function openModal() {
    modal.classList.remove("hidden");
    setTimeout(() => {
      modal.classList.add("opacity-100");
      modal.querySelector("div").classList.remove("scale-95");
    }, 10);
  }

  function closeModal() {
    modal.classList.remove("opacity-100");
    modal.querySelector("div").classList.add("scale-95");
    setTimeout(() => {
      modal.classList.add("hidden");
    }, 300);
  }

  function resetFormDropdowns() {
    document.querySelectorAll("#modalOffre .custom-dropdown").forEach((d) => {
      const hiddenInput = d.querySelector('input[type="hidden"]');
      const label = d.querySelector("button span");
      if (d.id === "formDropdownType") {
        hiddenInput.value = "Stage";
        label.textContent = "Stage";
      } else if (d.id === "formDropdownCategory") {
        hiddenInput.value = "1";
        label.textContent = "Informatique";
      } else if (d.id === "formDropdownStatut") {
        hiddenInput.value = "active";
        label.textContent = "Active (Visible)";
      } else if (d.id === "formDropdownLocalisation") {
        hiddenInput.value = "Nouakchott";
        label.textContent = "Nouakchott";
      } else if (d.id === "formDropdownStatutModal") {
        hiddenInput.value = "active";
        label.textContent = "Active (Visible)";
      }
    });

    // Reset number input
    const nbInput = document.querySelector('input[name="nombre_stagiaires"]');
    if (nbInput) nbInput.value = "1";

    // Reset new fields
    const newFields = ['specialization', 'technologies', 'questions', 'tags'];
    newFields.forEach(f => {
      const el = document.querySelector(`[name="${f}"]`);
      if (el) el.value = "";
    });
  }

  // --- Search & Filters ---
  const searchInput = document.getElementById("searchOffre");
  if (searchInput) {
    searchInput.addEventListener(
      "input",
      debounce(() => {
        activeFilters.search = searchInput.value;
        loadOffres();
      }, 500),
    );
  }

  // Setup listeners for custom dropdown changes (handled by global.js)
  document.querySelectorAll('#dropdownStatut, #dropdownType, #dropdownLocalisation, #dropdownCategory').forEach(d => {
    d.addEventListener('change', () => filterLists());
  });

  function filterLists() {
    const q = searchInput ? searchInput.value.toLowerCase() : "";
    const statusVal = document.querySelector("#dropdownStatut input") ? document.querySelector("#dropdownStatut input").value : "";
    const typeVal = document.querySelector("#dropdownType input") ? document.querySelector("#dropdownType input").value : "";
    const locVal = document.querySelector("#dropdownLocalisation input") ? document.querySelector("#dropdownLocalisation input").value : "";
    const catVal = document.querySelector("#dropdownCategory input") ? document.querySelector("#dropdownCategory input").value : "";

    activeFilters.search = q;
    activeFilters.statut = statusVal;
    activeFilters.type = typeVal;
    activeFilters.localisation = locVal;
    activeFilters.categorie_id = catVal;

    loadOffres();
  }

  // --- Form Submission ---
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const id = formData.get("id");

    const method = id ? "PUT" : "POST";
    let body;

    if (id) {
      // Put request needs URLSearchParams for some PHP environments or JSON
      body = new URLSearchParams(formData);
    } else {
      body = formData;
    }

    fetch("../api/offres.php", {
      method: method,
      body: body,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          closeModal();
          loadOffres();
          showMessage(id ? "Offre mise à jour" : "Offre publiée", "success");
        } else {
          alert(data.message);
        }
      });
  });

  function loadOffres() {
    grid.innerHTML =
      '<div class="col-span-full py-20 text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent mb-4"></div><p class="text-gray-500 font-medium">Mise à jour...</p></div>';

    const params = new URLSearchParams();
    params.append("user_id", "me");
    if (activeFilters.search) params.append("search", activeFilters.search);
    if (activeFilters.statut) params.append("statut", activeFilters.statut);
    if (activeFilters.type) params.append("type", activeFilters.type);
    if (activeFilters.localisation) params.append("localisation", activeFilters.localisation);
    if (activeFilters.categorie_id) params.append("categorie_id", activeFilters.categorie_id);

    fetch(`../api/offres.php?${params.toString()}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          allOffres = data.offres;
          renderOffres(data.offres);
        }
      });
  }

  function renderOffres(offres) {
    if (offres.length === 0) {
      grid.innerHTML =
        '<div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-gray-200"><p class="text-gray-400 italic">Aucune offre ne correspond à vos critères.</p></div>';
      return;
    }
    grid.innerHTML = offres
      .map(
        (o) => `
            <div class="bg-white p-7 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl hover:border-blue-100 transition-all duration-300 group">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <span class="px-3 py-1 ${o.statut === "active" ? "bg-green-50 text-green-600" : "bg-gray-100 text-gray-500"} rounded-full text-[10px] font-black uppercase tracking-wider">
                        ${o.statut === "active" ? "Visible" : "Archivée"}
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 truncate group-hover:text-blue-600 transition-colors">${o.titre}</h3>
                <p class="text-gray-500 text-sm mb-2 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-blue-200"></i> ${o.localisation}
                </p>
                <div class="flex items-center justify-between mb-6">
                    <p class="text-[10px] font-black uppercase tracking-wider text-blue-500 flex items-center gap-1.5 bg-blue-50/50 w-fit px-2 py-1 rounded-lg border border-blue-100/50 mb-0">
                        <i class="fas fa-users-viewfinder text-xs"></i> <span>Recherche : <strong>${o.nombre_stagiaires || 1}</strong> stagiaire(s)</span>
                    </p>
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border bg-gray-50 border-gray-100 text-gray-500" title="Total Favoris">
                        <i class="fas fa-heart text-red-500"></i>
                        <span class="text-xs font-black">${o.total_favorites || 0}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-gray-50">
                    <div class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                        <span class="text-blue-600">${o.nombre_candidatures || 0}</span> Candidats
                    </div>
                    <div class="flex gap-2">
                        <button onclick="editOffre(${o.id})" class="w-9 h-9 flex items-center justify-center text-gray-300 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all"><i class="fas fa-edit text-sm"></i></button>
                        <button onclick="deleteOffre(${o.id})" class="w-9 h-9 flex items-center justify-center text-gray-300 hover:bg-red-50 hover:text-red-600 rounded-xl transition-all"><i class="fas fa-trash-alt text-sm"></i></button>
                    </div>
                </div>
            </div>
        `,
      )
      .join("");
  }

  window.editOffre = (id) => {
    const o = allOffres.find((item) => item.id == id);
    if (!o) return;

    modalTitle.textContent = "Modifier l'Offre";
    document.getElementById("offreId").value = o.id;
    document.querySelector('input[name="titre"]').value = o.titre;
    document.querySelector('input[name="duree"]').value = o.duree || "";
    document.querySelector('textarea[name="description"]').value = o.description || "";
    
    // New fields
    if (document.querySelector('input[name="specialization"]')) document.querySelector('input[name="specialization"]').value = o.specialization || "";
    if (document.querySelector('input[name="technologies"]')) document.querySelector('input[name="technologies"]').value = o.technologies || "";
    if (document.querySelector('textarea[name="questions"]')) document.querySelector('textarea[name="questions"]').value = o.questions || "";
    if (document.querySelector('input[name="tags"]')) document.querySelector('input[name="tags"]').value = o.tags || "";

    const validTypes = ["Stage", "Alternance"];
    updateFormDropdown("formDropdownType", validTypes.includes(o.type_contrat) ? o.type_contrat : "Stage");
    updateFormDropdown("formDropdownCategory", o.categorie_id);
    updateFormDropdown("formDropdownStatutModal", o.statut || "active");
    updateFormDropdown("formDropdownLocalisation", o.localisation || "Nouakchott");

    // Set number input
    const nbInput = document.querySelector('input[name="nombre_stagiaires"]');
    if (nbInput) nbInput.value = o.nombre_stagiaires || "1";

    openModal();
  };

  function updateFormDropdown(dropdownId, value) {
    const d = document.getElementById(dropdownId);
    if (!d) return;
    const hiddenInput = d.querySelector('input[type="hidden"]');
    const label = d.querySelector("button span");
    const item = d.querySelector(`.dropdown-item[data-value="${value}"]`);

    if (item) {
      hiddenInput.value = value;
      label.textContent = item.textContent;
    }
  }

  window.deleteOffre = (id) => {
    showConfirmModal(
      "Supprimer l'offre",
      "Êtes-vous sûr de vouloir supprimer définitivement cette offre ? Cette action est irréversible.",
      () => {
        fetch("../api/offres.php", {
          method: "DELETE",
          body: new URLSearchParams({ offre_id: id }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              loadOffres();
              // Assuming showMessage exists from global.js or elsewhere
              if (typeof showMessage === 'function') {
                showMessage("Offre supprimée", "info");
              } else {
                console.log("Offre supprimée");
              }
            } else alert(data.message);
          });
      }
    );
  };

  function showConfirmModal(title, message, onConfirm) {
    const modalId = 'confirm-modal-' + Date.now();
    const modalHtml = `
      <div id="${modalId}" class="fixed inset-0 z-[10000] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity opacity-0" id="${modalId}-backdrop"></div>
        <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-sm p-8 transform scale-95 opacity-0 transition-all duration-300 border border-gray-100" id="${modalId}-content">
          <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center text-red-500 mb-6 mx-auto">
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

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const modalEl = document.getElementById(modalId);
    const backdrop = document.getElementById(`${modalId}-backdrop`);
    const content = document.getElementById(`${modalId}-content`);
    const btnCancel = document.getElementById(`${modalId}-cancel`);
    const btnConfirm = document.getElementById(`${modalId}-confirm`);

    // Animate In
    setTimeout(() => {
      backdrop.classList.remove('opacity-0');
      content.classList.remove('scale-95', 'opacity-0');
    }, 10);

    const close = () => {
      backdrop.classList.add('opacity-0');
      content.classList.add('scale-95', 'opacity-0');
      setTimeout(() => modalEl.remove(), 300);
    };

    btnCancel.addEventListener('click', close);
    backdrop.addEventListener('click', close);

    btnConfirm.addEventListener('click', () => {
      close();
      onConfirm();
    });
  }

  function debounce(func, wait) {
    let timeout;
    return function () {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, arguments), wait);
    };
  }
});
