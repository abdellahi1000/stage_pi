/* js/offres.js */
document.addEventListener("DOMContentLoaded", () => {
  let activeFilters = {
    search: "",
    localisation: "",
    type: "",
  };

  loadOffres();

  // --- Search Logic ---
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

  // --- Custom Dropdowns Logic ---
  document.querySelectorAll(".custom-dropdown").forEach((dropdown) => {
    const button = dropdown.querySelector("button");
    const menu = dropdown.querySelector(".dropdown-menu");
    const label = button.querySelector("span");
    const icon = button.querySelector(".fa-chevron-down");

    button.addEventListener("click", (e) => {
      e.stopPropagation();
      // Close other dropdowns
      document.querySelectorAll(".dropdown-menu").forEach((m) => {
        if (m !== menu) m.classList.remove("active");
      });
      document.querySelectorAll(".fa-chevron-down").forEach((i) => {
        if (i !== icon) i.classList.remove("rotate-180");
      });

      menu.classList.toggle("active");
      icon.classList.toggle("rotate-180");
    });

    menu.querySelectorAll(".dropdown-item").forEach((item) => {
      item.addEventListener("click", () => {
        const value = item.dataset.value;
        const text = item.textContent;

        label.textContent = text;
        menu.classList.remove("active");
        icon.classList.remove("rotate-180");

        if (dropdown.id === "dropdownLocalisation") {
          activeFilters.localisation = value;
        } else if (dropdown.id === "dropdownType") {
          activeFilters.type = value;
        }

        loadOffres();
      });
    });
  });

  // Close dropdowns on outside click
  window.addEventListener("click", () => {
    document
      .querySelectorAll(".dropdown-menu")
      .forEach((m) => m.classList.remove("active"));
    document
      .querySelectorAll(".fa-chevron-down")
      .forEach((i) => i.classList.remove("rotate-180"));
  });

  // --- Corporate Modal Logic (if exists) ---
  const addOffreBtn = document.getElementById("addOffreBtn");
  const addOffreModal = document.getElementById("addOffreModal");
  if (addOffreBtn && addOffreModal) {
    addOffreBtn.addEventListener("click", () => {
      addOffreModal.style.display = "block";
    });
  }

  function loadOffres() {
    const grid = document.getElementById("offresGrid");
    if (!grid) return;

    // Show Loading State
    grid.innerHTML = `
            <div class="col-span-full py-20 text-center">
                <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-blue-600 border-t-transparent mb-4"></div>
                <p class="text-gray-500 font-medium">Recherche des meilleures opportunités...</p>
            </div>`;

    const params = new URLSearchParams();
    if (activeFilters.search) params.append("search", activeFilters.search);
    if (activeFilters.localisation)
      params.append("localisation", activeFilters.localisation);
    if (activeFilters.type) params.append("type", activeFilters.type);

    fetch(`../api/offres.php?${params.toString()}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          renderOffres(data.offres);
        } else {
          grid.innerHTML = `<p class="col-span-full text-center py-10 text-red-500">${data.message}</p>`;
        }
      })
      .catch((err) => {
        console.error("Erreur chargement offres:", err);
        grid.innerHTML = `<p class="col-span-full text-center py-10 text-red-500">Une erreur est survenue lors du chargement des offres.</p>`;
      });
  }

  function renderOffres(offres) {
    const grid = document.getElementById("offresGrid");
    grid.innerHTML = "";

    if (!offres || offres.length === 0) {
      grid.innerHTML = `
                <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-gray-200 shadow-sm">
                    <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-search text-3xl text-gray-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Aucune offre trouvée</h3>
                    <p class="text-gray-500 max-w-sm mx-auto">Il n'y a pas encore d'offres correspondant à vos critères. Essayez de modifier vos filtres.</p>
                </div>`;
      return;
    }

    offres.forEach((o) => {
      const card = document.createElement("div");
      card.className =
        "bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-blue-100 transition-all duration-300 group";
      card.innerHTML = `
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="fas fa-building text-xl"></i>
                    </div>
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-wider group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        ${esc(o.type_contrat)}
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-1 group-hover:text-blue-600 transition-colors">${esc(
                  o.titre,
                )}</h3>
                <p class="text-gray-500 font-medium mb-4 flex items-center gap-2">
                    <i class="fas fa-at text-xs"></i> ${esc(o.entreprise)}
                </p>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="flex items-center text-sm text-gray-600 bg-gray-50 p-2 rounded-lg">
                        <i class="fas fa-map-marker-alt text-blue-400 mr-2"></i>
                        <span class="truncate">${esc(o.localisation)}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600 bg-gray-50 p-2 rounded-lg">
                        <i class="fas fa-clock text-blue-400 mr-2"></i>
                        <span class="truncate">${esc(o.duree || "N/A")}</span>
                    </div>
                </div>

                <p class="text-gray-600 text-sm line-clamp-3 mb-6">${esc(
                  o.description,
                )}</p>

                <button onclick="postuler(${o.id})" class="w-full py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-blue-600 transition-all shadow-lg shadow-gray-200 hover:shadow-blue-200">
                    Postuler maintenant
                </button>
            `;
      grid.appendChild(card);
    });
  }
  // --- Postuler Modal Logic ---
  const modalPostuler = document.getElementById("modalPostuler");
  const formPostuler = document.getElementById("formPostuler");
  const closePostuler = document.getElementById("closePostuler");

  if (closePostuler) {
    closePostuler.addEventListener("click", () => {
      modalPostuler.classList.remove("opacity-100");
      setTimeout(() => modalPostuler.classList.add("hidden"), 300);
    });
  }

  // Close on backdrop
  if (modalPostuler) {
    modalPostuler.addEventListener("click", (e) => {
      if (e.target === modalPostuler) {
        modalPostuler.classList.remove("opacity-100");
        setTimeout(() => modalPostuler.classList.add("hidden"), 300);
      }
    });
  }

  if (formPostuler) {
    formPostuler.addEventListener("submit", (e) => {
      e.preventDefault();
      const formData = new FormData(formPostuler);

      fetch("../api/mes_candidatures.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            showMessage("Candidature envoyée avec succès !", "success");
            modalPostuler.classList.remove("opacity-100");
            setTimeout(() => modalPostuler.classList.add("hidden"), 300);
            formPostuler.reset();
            const fileNameSpan = document.getElementById('fileName');
            if (fileNameSpan) fileNameSpan.textContent = "Choisir un fichier (PDF, DOC, DOCX)";
          } else {
            showMessage(data.message, "error");
          }
        });
    });
  }

  window.postuler = (offreId) => {
    document.getElementById("postulerOffreId").value = offreId;
    modalPostuler.classList.remove("hidden");
    setTimeout(() => modalPostuler.classList.add("opacity-100"), 10);
  };
});

function debounce(func, wait) {
  let timeout;
  return function () {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, arguments), wait);
  };
}
