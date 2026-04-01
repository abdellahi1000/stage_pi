/* js/admin_company_offers.js */
document.addEventListener("DOMContentLoaded", () => {
  const offresGrid = document.getElementById("offresGrid");
  const modalOffre = document.getElementById("modalOffre");
  const formOffre = document.getElementById("formOffre");
  const btnNewOffre = document.getElementById("btnNewOffre");
  const searchInput = document.getElementById("searchOffre");
  
  let allOffres = [];

  loadOffres();

  function loadOffres() {
    if (!offresGrid) return;
    
    offresGrid.innerHTML = `
        <div class="col-span-full py-20 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent mb-4"></div>
            <p class="text-gray-500 font-medium">Récupération des offres...</p>
        </div>`;

    fetch("../api/admin_company_offers.php?action=list")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          allOffres = data.offres;
          renderOffres(allOffres);
        } else {
          offresGrid.innerHTML = `<div class="col-span-full py-20 text-center text-red-500">${data.message}</div>`;
        }
      })
      .catch((err) => {
          console.error("Error loading offers:", err);
          offresGrid.innerHTML = `<div class="col-span-full py-20 text-center text-red-500">Erreur lors du chargement des offres.</div>`;
      });
  }

  function renderOffres(offres) {
    if (!offresGrid) return;

    if (offres.length === 0) {
      offresGrid.innerHTML = `
        <div class="col-span-full py-20 text-center">
          <p class="text-gray-400 font-medium">Aucune offre trouvée.</p>
        </div>
      `;
      return;
    }

    offresGrid.innerHTML = offres
      .map(
        (o) => `
        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
          <div class="flex justify-between items-start mb-6">
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
              <i class="fas fa-briefcase"></i>
            </div>
            <div class="flex gap-2">
              <button onclick="editOffre(${o.id})" class="w-8 h-8 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 hover:text-blue-600 transition-all">
                <i class="fas fa-edit text-xs"></i>
              </button>
              <button onclick="deleteOffre(${o.id})" class="w-8 h-8 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 hover:text-red-600 transition-all">
                <i class="fas fa-trash text-xs"></i>
              </button>
            </div>
          </div>
          
          <h3 class="text-xl font-bold text-gray-900 mb-2 truncate" title="${o.titre}">${o.titre}</h3>
          <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-1 truncate">${o.specialization || 'Spécialisation non définie'}</p>
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">${o.localisation || 'S.L'} • ${o.type_contrat || 'Stage'}</p>
          
          <div class="flex items-center justify-between pt-6 border-t border-gray-50">
            <div>
              <p class="text-2xl font-black text-gray-900">${o.total_candidatures || 0}</p>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Candidats</p>
            </div>
            <div class="flex flex-col items-end gap-1">
                <span class="px-3 py-1 ${o.statut === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'} rounded-full text-[10px] font-black uppercase tracking-wider">
                  ${o.statut === 'active' ? 'Active' : 'Archivée'}
                </span>
                ${o.archived_by_admin == 1 ? '<span class="text-[8px] font-black text-rose-500 uppercase tracking-tighter">Archivée par l\'Admin</span>' : ''}
            </div>
          </div>
        </div>
      `
      )
      .join("");
  }

  if (btnNewOffre) {
      btnNewOffre.addEventListener("click", () => {
        formOffre.reset();
        document.getElementById("offreId").value = "";
        document.getElementById("modalTitle").textContent = "Nouvelle Offre";
        
        // Reset manual values not covered by reset() if any (like hidden inputs)
        formOffre.localisation.value = "";
        formOffre.type_contrat.value = "Stage";
        formOffre.categorie_id.value = "";
        formOffre.statut.value = "active";

        // Reset custom dropdowns displays
        document.querySelectorAll(".active-modal-form .custom-dropdown span, #modalOffre .custom-dropdown span").forEach(s => {
            const dropdownId = s.parentElement.parentElement.id;
            if (dropdownId.startsWith('formDropdown')) {
                if (dropdownId === 'formDropdownStatutModal') {
                    s.textContent = "Active (Visible)";
                } else if (dropdownId === 'formDropdownType') {
                    s.textContent = "Stage";
                } else {
                    s.textContent = "Choisir...";
                }
            }
        });

        openModal();
      });
  }

  window.editOffre = (id) => {
    const o = allOffres.find((offre) => offre.id == id);
    if (!o) return;

    document.getElementById("offreId").value = o.id;
    document.getElementById("modalTitle").textContent = "Modifier l'Offre";
    
    // Fill fields
    formOffre.titre.value = o.titre || "";
    formOffre.description.value = o.description || "";
    formOffre.nombre_stagiaires.value = o.nombre_stagiaires || 1;
    formOffre.duree.value = o.duree || "";
    formOffre.specialization.value = o.specialization || "";
    formOffre.technologies.value = o.technologies || "";
    formOffre.questions.value = o.questions || "";
    formOffre.tags.value = o.tags || "";
    
    // Hidden inputs for dropdowns
    formOffre.localisation.value = o.localisation || "";
    formOffre.type_contrat.value = o.type_contrat || "Stage";
    formOffre.categorie_id.value = o.categorie_id || "";
    formOffre.statut.value = o.statut || "active";

    // Update dropdown labels
    updateDropdownLabel('formDropdownLocalisation', o.localisation);
    updateDropdownLabel('formDropdownType', o.type_contrat);
    updateDropdownLabel('formDropdownCategory', o.categorie_id);
    updateDropdownLabel('formDropdownStatutModal', o.statut);

    openModal();
  };

  function updateDropdownLabel(id, val) {
      const drop = document.getElementById(id);
      if (!drop) return;
      const item = drop.querySelector(`.dropdown-item[data-value="${val}"]`);
      if (item) {
          const span = drop.querySelector("button span");
          if (span) span.textContent = item.textContent;
      }
  }

  window.deleteOffre = (id) => {
    if (confirm("Voulez-vous vraiment supprimer cette offre ?")) {
      fetch(`../api/admin_company_offers.php?action=delete&id=${id}`)
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            loadOffres();
          } else {
              alert(data.message);
          }
        });
    }
  };

  if (formOffre) {
      formOffre.addEventListener("submit", (e) => {
        e.preventDefault();
        const formData = new FormData(formOffre);
        const data = Object.fromEntries(formData.entries());

        const btn = formOffre.querySelector('button[type="submit"]');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.textContent = "Enregistrement...";

        fetch("../api/admin_company_offers.php?action=save", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              closeModal();
              loadOffres();
            } else {
              alert(data.message);
            }
          })
          .catch(err => {
              console.error(err);
              alert("Erreur lors de l'enregistrement.");
          })
          .finally(() => {
              btn.disabled = false;
              btn.innerHTML = origText;
          });
      });
  }

  if (searchInput) {
      searchInput.addEventListener("input", (e) => {
        filterLists();
      });
  }

  // Handle dropdown interactions
  document.querySelectorAll(".custom-dropdown").forEach((dropdown) => {
    const btn = dropdown.querySelector("button");
    const menu = dropdown.querySelector(".dropdown-menu");
    const input = dropdown.querySelector("input[type='hidden']");
    const label = btn ? btn.querySelector("span") : null;

    if (!btn || !menu) return;

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      // Close other menus
      document.querySelectorAll(".dropdown-menu").forEach(m => {
          if (m !== menu) m.classList.add("invisible", "opacity-0", "pointer-events-none", "translate-y-2", "scale-95");
      });
      
      menu.classList.toggle("invisible");
      menu.classList.toggle("opacity-0");
      menu.classList.toggle("pointer-events-none");
      menu.classList.toggle("translate-y-2");
      menu.classList.toggle("scale-95");
    });

    menu.querySelectorAll(".dropdown-item").forEach((item) => {
      item.addEventListener("click", () => {
        const val = item.getAttribute("data-value");
        const txt = item.textContent;
        if (input) {
            input.value = val;
            // Trigger change if needed
            const event = new Event('change');
            input.dispatchEvent(event);
        }
        if (label) label.textContent = txt;
        
        menu.classList.add("invisible", "opacity-0", "pointer-events-none", "translate-y-2", "scale-95");

        // Filter if it's a global filter dropdown
        if (dropdown.id === 'dropdownStatut' || dropdown.id === 'dropdownType' || dropdown.id === 'dropdownLocalisation' || dropdown.id === 'dropdownCategory') {
            filterLists();
        }
      });
    });
  });

  function filterLists() {
    const q = searchInput ? searchInput.value.toLowerCase() : "";
    const statusVal = document.querySelector("#dropdownStatut input") ? document.querySelector("#dropdownStatut input").value : "";
    const typeVal = document.querySelector("#dropdownType input") ? document.querySelector("#dropdownType input").value : "";
    const locVal = document.querySelector("#dropdownLocalisation input") ? document.querySelector("#dropdownLocalisation input").value : "";
    const catVal = document.querySelector("#dropdownCategory input") ? document.querySelector("#dropdownCategory input").value : "";
    
    let filtered = allOffres;
    
    if (q) {
        filtered = filtered.filter(o => 
            (o.titre && o.titre.toLowerCase().includes(q)) || 
            (o.localisation && o.localisation.toLowerCase().includes(q)) ||
            (o.entreprise && o.entreprise.toLowerCase().includes(q)) ||
            (o.type_contrat && o.type_contrat.toLowerCase().includes(q)) ||
            (o.description && o.description.toLowerCase().includes(q))
        );
    }
    
    if (statusVal) filtered = filtered.filter(o => o.statut === statusVal);
    if (typeVal) filtered = filtered.filter(o => o.type_contrat && o.type_contrat.toLowerCase() === typeVal.toLowerCase());
    if (locVal) filtered = filtered.filter(o => o.localisation === locVal);
    if (catVal) filtered = filtered.filter(o => o.categorie_id == catVal);
    
    renderOffres(filtered);
  }

  // Modal helpers
  function openModal() {
    if (!modalOffre) return;
    modalOffre.classList.remove("hidden");
    setTimeout(() => {
      modalOffre.classList.remove("opacity-0");
      modalOffre.classList.add("opacity-100");
      const transformEl = modalOffre.querySelector(".transform");
      if (transformEl) {
          transformEl.classList.remove("scale-95");
          transformEl.classList.add("scale-100");
      }
    }, 10);
  }

  function closeModal() {
    if (!modalOffre) return;
    modalOffre.classList.remove("opacity-100");
    modalOffre.classList.add("opacity-0");
    const transformEl = modalOffre.querySelector(".transform");
    if (transformEl) {
        transformEl.classList.remove("scale-100");
        transformEl.classList.add("scale-95");
    }
    setTimeout(() => {
      modalOffre.classList.add("hidden");
    }, 300);
  }

  document.querySelectorAll(".close-modal").forEach((btn) => {
    btn.addEventListener("click", closeModal);
  });

  document.addEventListener("click", () => {
    document.querySelectorAll(".dropdown-menu").forEach((menu) => {
      menu.classList.add("invisible", "opacity-0", "translate-y-2", "scale-95");
    });
  });
});
