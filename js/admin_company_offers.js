/* js/admin_company_offers.js */
document.addEventListener("DOMContentLoaded", () => {
  const offresGrid = document.getElementById("offresGrid");
  const modalOffre = document.getElementById("modalOffre");
  const formOffre = document.getElementById("formOffre");
  const btnNewOffre = document.getElementById("btnNewOffre");
  const searchInput = document.getElementById("searchOffre");
  
  let allOffres = [];

  function showToast(msg, type = "success") {
    let toast = document.getElementById("admin-toast");
    if (!toast) {
      toast = document.createElement("div");
      toast.id = "admin-toast";
      toast.className = "fixed top-10 left-1/2 -translate-x-1/2 z-[9999] px-7 py-4 rounded-[1.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.15)] transition-all duration-500 translate-y-[-150%] flex items-center gap-4 bg-white border border-gray-100 min-w-[320px]";
      document.body.appendChild(toast);
    }

    const iconColor = type === "success" ? "text-green-500" : "text-red-500";
    const icon = type === "success" ? "fa-check-circle" : "fa-circle-xmark";
    
    toast.innerHTML = `
      <div class="w-10 h-10 rounded-xl ${type === 'success' ? 'bg-green-50' : 'bg-red-50'} flex items-center justify-center ${iconColor} shrink-0">
        <i class="fas ${icon} text-lg"></i>
      </div>
      <div>
        <p class="text-sm font-black text-gray-900">${msg}</p>
      </div>
    `;

    toast.classList.remove("translate-y-[-150%]", "opacity-0");
    toast.classList.add("translate-y-0", "opacity-100");

    setTimeout(() => {
      toast.classList.add("translate-y-[-150%]", "opacity-0");
      toast.classList.remove("translate-y-0", "opacity-100");
    }, 3000);
  }

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
          window.allOffres = data.offres;
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
                        <div class="px-2 py-0.5 bg-blue-600 text-white rounded-lg text-[10px] font-bold">${o.total_stagiaires || 0}</div>
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

            <!-- Fix: TWO Buttons only in place of circular element -->
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
                    s.textContent = "Stagiaire";
                    formOffre.type_contrat.value = "Stage";
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
    formOffre.places_alternances.value = o.places_alternances || 0;
    
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

  function updateDropdownLabel(id, val) {
      const drop = document.getElementById(id);
      if (!drop) return;
      const item = drop.querySelector(`.dropdown-item[data-value="${val}"]`);
      if (item) {
          const span = drop.querySelector("button span");
          if (span) span.textContent = item.textContent;
      } else {
          // Defaults or manual mappings
          const span = drop.querySelector("button span");
          if (span) {
              if (val === 'Stage') span.textContent = "Stagiaire";
              else if (val === 'Candidate') span.textContent = "Candidat (Simple)";
              else if (val === 'active') span.textContent = "Active (Visible)";
              else span.textContent = val || "Choisir...";
          }
      }
  }

  window.deleteOffre = (id) => {
    fetch(`../api/admin_company_offers.php?action=delete&id=${id}`, {
      method: 'DELETE'
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          showToast(data.message || "Offre supprimée", "success");
          loadOffres();
        } else {
          showToast(data.message || "Erreur", "error");
        }
      });
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

        const isUpdate = data.id && data.id !== "";
        fetch("../api/admin_company_offers.php?action=save", {
          method: isUpdate ? "PUT" : "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              showToast(isUpdate ? "Offer mis à jour avec succès" : "Offer créé avec succès", "success");
              closeModal();
              loadOffres();
            } else {
              showToast(data.message || "Erreur", "error");
            }
          })
          .catch(err => {
              console.error(err);
              showToast("Erreur lors de l'enregistrement.", "error");
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
