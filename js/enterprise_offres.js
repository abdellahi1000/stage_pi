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
    if (!grid) return;

    if (offres.length === 0) {
      grid.innerHTML = `
        <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-gray-200">
          <p class="text-gray-400 italic">Aucune offre ne correspond à vos critères.</p>
        </div>
      `;
      return;
    }

    grid.innerHTML = offres
      .map(
        (o) => `
        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden flex flex-col h-full">
          <div class="flex justify-between items-start mb-6">
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
              <i class="fas fa-briefcase"></i>
            </div>
            <div class="flex flex-col items-end gap-2">
                <span class="px-3 py-1 ${o.statut === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'} rounded-full text-[8px] font-black uppercase tracking-widest">
                    ${o.statut === 'active' ? 'ACTIVE' : 'ARCHIVÉE'}
                </span>
            </div>
          </div>
          
          <div class="flex-1">
            <h3 class="text-xl font-bold text-gray-900 mb-2 truncate" title="${o.titre}">${o.titre}</h3>
            <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-1 truncate">${o.specialization || 'Spécialisation non définie'}</p>
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4">
                ${o.localisation || 'S.L'} • 
                <span class="${o.type_contrat === 'Candidate' ? 'text-rose-500' : (o.type_contrat === 'Alternance' ? 'text-purple-500' : 'text-blue-500')}">
                    ${o.type_contrat === 'Stage' ? 'Stagiaire' : (o.type_contrat === 'Candidate' ? 'Candidat (Simple)' : o.type_contrat)}
                </span>
            </p>
            
            ${(o.technologies || o.tags) ? `
            <div class="flex flex-wrap gap-2 mb-6">
              ${(o.technologies ? o.technologies.split(',') : []).map(t => `<span class="px-2 py-0.5 bg-gray-100 text-gray-400 rounded-md text-[9px] font-black uppercase tracking-wider">${t.trim()}</span>`).join('')}
              ${(o.tags ? o.tags.split(',') : []).map(t => `<span class="px-2 py-0.5 bg-blue-50 text-blue-500 rounded-md text-[9px] font-black uppercase tracking-wider">${t.trim()}</span>`).join('')}
            </div>
            ` : ''}

            <!-- Dual Document Cards -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div onclick="openDescriptionModal(${o.id})" class="p-3 bg-gray-50 rounded-2xl border border-gray-100 flex flex-col gap-2 cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-all group/doc">
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-blue-600 shadow-sm group-hover/doc:bg-blue-600 group-hover/doc:text-white transition-all">
                        <i class="fas fa-file-alt text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] font-black text-gray-900 uppercase tracking-tighter truncate">DESCRIPTION OFFRE.PDF</p>
                    </div>
                </div>

                <div onclick="openQuestionsModal(${o.id})" class="p-3 bg-gray-50 rounded-2xl border border-gray-100 flex flex-col gap-2 cursor-pointer hover:bg-red-50 hover:border-red-200 transition-all group/doc ${!o.questions ? 'opacity-40 grayscale cursor-not-allowed' : ''}">
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-red-500 shadow-sm group-hover/doc:bg-red-500 group-hover/doc:text-white transition-all">
                        <i class="fas fa-file-pdf text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] font-black text-gray-900 uppercase tracking-tighter truncate">QUESTIONS CANDIDATS.PDF</p>
                    </div>
                </div>
            </div>
          </div>

          <div class="pt-6 border-t border-gray-50 mt-auto">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <!-- Box Stage -->
                <div class="bg-blue-50/50 p-3 rounded-2xl border border-blue-100/50 flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">Stage</span>
                        <div class="px-2 py-0.5 bg-blue-600 text-white rounded-lg text-[10px] font-bold">${o.nombre_candidatures || 0}</div>
                    </div>
                    <p class="text-sm font-black text-gray-900">
                        <span class="text-lg">${o.nombre_stagiaires || 0}</span>
                        <span class="text-[9px] font-bold text-gray-400 uppercase ml-1">Places</span>
                    </p>
                </div>
                <!-- Box Alternance -->
                <div class="bg-purple-50/50 p-3 rounded-2xl border border-purple-100/50 flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black text-purple-400 uppercase tracking-widest">Alternance</span>
                        <div class="px-2 py-0.5 bg-purple-600 text-white rounded-lg text-[10px] font-bold">${o.total_alternances || 0}</div>
                    </div>
                    <p class="text-sm font-black text-gray-900">
                        <span class="text-lg">${o.places_alternances || 0}</span>
                        <span class="text-[9px] font-bold text-gray-400 uppercase ml-1">Places</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex gap-2">
                    <button onclick="editOffre(${o.id})" class="flex-1 py-2.5 bg-gray-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-edit text-[10px]"></i> Modifier
                    </button>
                    <button onclick="deleteOffre(${o.id})" class="flex-1 py-2.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-trash text-[10px]"></i> Supprimer
                    </button>
                </div>
                <button onclick="exportOfferPDF(${o.id})" class="w-full py-2.5 bg-white text-gray-900 border border-gray-200 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-file-pdf text-[10px] text-red-500"></i> Export PDF
                </button>
            </div>
          </div>
        </div>
      `
      )
      .join("");
  }

  window.openDescriptionModal = (id) => {
    const o = allOffres.find(offre => offre.id == id);
    if (!o) return;

    let modal = document.getElementById('modalDescriptionDocs');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'modalDescriptionDocs';
        modal.className = 'fixed inset-0 bg-black/60 backdrop-blur-md z-[100] flex items-center justify-center hidden opacity-0 transition-opacity duration-300';
        modal.innerHTML = `
            <div class="bg-white rounded-[2.5rem] w-full max-w-2xl p-10 shadow-2xl transform scale-95 transition-all duration-300">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                        <i class="fas fa-file-alt text-blue-600"></i> Description de l'offre
                    </h2>
                    <button onclick="closeDescriptionModal()" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="descriptionDocsContent" class="space-y-6 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                </div>
                <div class="mt-8 pt-8 border-t border-gray-50">
                    <button onclick="closeDescriptionModal()" class="w-full py-4 bg-gray-900 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-blue-600 transition-all">Fermer la prévisualisation</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeDescriptionModal(); });
    }

    const content = modal.querySelector('#descriptionDocsContent');
    content.innerHTML = `
        <div class="space-y-4">
            <h4 class="text-xl font-bold text-gray-900">${o.titre}</h4>
            <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-widest">${o.type_contrat}</span>
                <span class="px-3 py-1 bg-gray-50 text-gray-500 rounded-full text-[10px] font-black uppercase tracking-widest">${o.localisation}</span>
            </div>
            <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed bg-gray-50/50 p-6 rounded-2xl border border-gray-100">
                ${o.description.replace(/\n/g, '<br>')}
            </div>
        </div>
    `;

    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('opacity-100');
        modal.querySelector('.transform').classList.remove('scale-95');
        modal.querySelector('.transform').classList.add('scale-100');
    }, 10);
  };

  window.closeDescriptionModal = () => {
    const modal = document.getElementById('modalDescriptionDocs');
    if (!modal) return;
    modal.classList.remove('opacity-100');
    modal.querySelector('.transform').classList.remove('scale-100');
    modal.querySelector('.transform').classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
  };

  window.openQuestionsModal = (id) => {
    const o = allOffres.find(offre => offre.id == id);
    if (!o || !o.questions) return;

    let modal = document.getElementById('modalQuestions');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'modalQuestions';
        modal.className = 'fixed inset-0 bg-black/60 backdrop-blur-md z-[100] flex items-center justify-center hidden opacity-0 transition-opacity duration-300';
        modal.innerHTML = `
            <div class="bg-white rounded-[2.5rem] w-full max-w-lg p-10 shadow-2xl transform scale-95 transition-all duration-300">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                        <i class="fas fa-file-pdf text-red-500"></i> Questions pour les candidats
                    </h2>
                    <button onclick="closeQuestionsModal()" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="questionsContent" class="space-y-4 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                </div>
                <div class="mt-8 pt-8 border-t border-gray-50">
                    <button onclick="closeQuestionsModal()" class="w-full py-4 bg-gray-900 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-blue-600 transition-all">Fermer le document</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeQuestionsModal(); });
    }

    const content = modal.querySelector('#questionsContent');
    const questionsList = o.questions.split('\n').filter(q => q.trim() !== '');
    content.innerHTML = questionsList.map((q, i) => `
        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
            <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-2">Question #${i+1}</p>
            <p class="text-sm font-bold text-gray-900 leading-relaxed">${q}</p>
        </div>
    `).join('');

    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('opacity-100');
        modal.querySelector('.transform').classList.remove('scale-95');
        modal.querySelector('.transform').classList.add('scale-100');
    }, 10);
  };

  window.closeQuestionsModal = () => {
    const modal = document.getElementById('modalQuestions');
    if (!modal) return;
    modal.classList.remove('opacity-100');
    modal.querySelector('.transform').classList.remove('scale-100');
    modal.querySelector('.transform').classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
  };

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
    if (document.querySelector('input[name="places_alternances"]')) document.querySelector('input[name="places_alternances"]').value = o.places_alternances || "0";

    const validTypes = ["Stage", "Alternance", "Candidate"];
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
    } else {
      if (value === 'Stage') label.textContent = "Stagiaire";
      else if (value === 'Candidate') label.textContent = "Candidat (Simple)";
      else label.textContent = value || "Choisir...";
      hiddenInput.value = value;
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

  window.exportOfferPDF = (id) => {
    const o = allOffres.find(offre => offre.id == id);
    if (!o) return;

    const printWin = window.open('', '_blank');
    printWin.document.write(`
        <html>
        <head>
            <title>Offre - ${o.titre}</title>
            <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
            <style>
                @media print {
                    .no-print { display: none; }
                    body { -webkit-print-color-adjust: exact; }
                }
                body { background: white; color: #1a202c; font-family: 'Inter', sans-serif; }
            </style>
        </head>
        <body class="p-10">
            <div class="max-w-4xl mx-auto border border-gray-200 p-12 rounded-3xl">
                <div class="flex justify-between items-start mb-10 pb-10 border-b border-gray-100">
                    <div>
                        <h1 class="text-4xl font-black text-gray-900 mb-2">${o.titre}</h1>
                        <p class="text-blue-600 font-bold uppercase tracking-widest text-sm">${o.entreprise || 'Entreprise'}</p>
                    </div>
                    <div class="text-right text-gray-400 font-bold text-xs">
                        <p>PUBLIÉ LE</p>
                        <p class="text-gray-900">${new Date(o.date_publication).toLocaleDateString()}</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-8 mb-10">
                    <div class="p-5 bg-gray-50 rounded-2xl">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Localisation</p>
                        <p class="text-sm font-bold text-gray-900">${o.localisation || 'Non défini'}</p>
                    </div>
                    <div class="p-5 bg-gray-50 rounded-2xl">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Type de Contrat</p>
                        <p class="text-sm font-bold text-blue-600">${o.type_contrat === 'Stage' ? 'Stagiaire' : (o.type_contrat === 'Candidate' ? 'Candidat' : o.type_contrat)}</p>
                    </div>
                    <div class="p-5 bg-gray-50 rounded-2xl">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Durée / Rémunération</p>
                        <p class="text-sm font-bold text-gray-900">${o.duree || '-'} • ${o.remuneration || '-'}</p>
                    </div>
                </div>

                <div class="mb-10">
                    <h3 class="text-lg font-black text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-align-left text-blue-600"></i> Description du poste
                    </h3>
                    <div class="text-gray-600 leading-relaxed space-y-4">
                        ${o.description.replace(/\n/g, '<br>')}
                    </div>
                </div>

                ${o.technologies || o.tags ? `
                <div class="mb-10">
                    <h3 class="text-lg font-black text-gray-900 mb-4">Technologies & Compétences</h3>
                    <div class="flex flex-wrap gap-2 text-sm">
                        ${(o.technologies ? o.technologies.split(',') : []).map(t => `<span class="px-3 py-1 border border-gray-200 rounded-lg">${t.trim()}</span>`).join('')}
                    </div>
                </div>
                ` : ''}

                <div class="mt-12 pt-10 border-t border-gray-100 flex justify-between items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                    <p>StageMatch • Document Officiel</p>
                    <p>ID: ${o.id}</p>
                </div>
            </div>
            <script>window.onload = () => { window.print(); window.close(); }</script>
        </body>
        </html>
    `);
    printWin.document.close();
  };
});
