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
    // Reset visual appearance of form dropdowns
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
      }
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

  // Custom Dropdowns Logic
  document.querySelectorAll(".custom-dropdown").forEach((dropdown) => {
    const button = dropdown.querySelector("button");
    const menu = dropdown.querySelector(".dropdown-menu");
    const label = button.querySelector("span");
    const icon = button.querySelector(".fa-chevron-down");
    const hiddenInput = dropdown.querySelector('input[type="hidden"]');

    button.addEventListener("click", (e) => {
      e.stopPropagation();
      document.querySelectorAll(".dropdown-menu").forEach((m) => {
        if (m !== menu) m.classList.remove("active");
      });
      document.querySelectorAll(".fa-chevron-down").forEach((i) => {
        if (i !== icon) i.classList.remove("rotate-180");
      });

      menu.classList.toggle("active");
      if (icon) icon.classList.toggle("rotate-180");
    });

    menu.querySelectorAll(".dropdown-item").forEach((item) => {
      item.addEventListener("click", () => {
        const value = item.dataset.value;
        const text = item.textContent;

        label.textContent = text;
        menu.classList.remove("active");
        if (icon) icon.classList.remove("rotate-180");

        if (hiddenInput) {
          hiddenInput.value = value;
        }

        if (dropdown.id === "dropdownStatut") {
          activeFilters.statut = value;
          loadOffres();
        } else if (dropdown.id === "dropdownType") {
          activeFilters.type = value;
          loadOffres();
        }
      });
    });
  });

  window.addEventListener("click", () => {
    document
      .querySelectorAll(".dropdown-menu")
      .forEach((m) => m.classList.remove("active"));
    document
      .querySelectorAll(".fa-chevron-down")
      .forEach((i) => i.classList.remove("rotate-180"));
  });

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
                <p class="text-gray-500 text-sm mb-6 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-blue-200"></i> ${o.localisation}
                </p>
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
    document.querySelector('input[name="localisation"]').value = o.localisation;
    document.querySelector('input[name="duree"]').value = o.duree || "";
    document.querySelector('textarea[name="description"]').value =
      o.description;

    // Update custom dropdowns visuals
    updateFormDropdown("formDropdownType", o.type_contrat);
    updateFormDropdown("formDropdownCategory", o.categorie_id);
    updateFormDropdown("formDropdownStatut", o.statut);

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
    if (confirm("Supprimer définitivement cette offre ?")) {
      fetch("../api/offres.php", {
        method: "DELETE",
        body: new URLSearchParams({ offre_id: id }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            loadOffres();
            showMessage("Offre supprimée", "info");
          } else alert(data.message);
        });
    }
  };

  function debounce(func, wait) {
    let timeout;
    return function () {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, arguments), wait);
    };
  }
});
