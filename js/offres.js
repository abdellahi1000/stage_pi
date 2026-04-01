/* js/offres.js */
document.addEventListener("DOMContentLoaded", () => {
  let activeFilters = {
    search: "",
    localisation: "",
    type: "",
    favoris: new URLSearchParams(window.location.search).get("favoris") || "",
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
    if (activeFilters.favoris) params.append("favoris", activeFilters.favoris);

    fetch(`../api/offres.php?${params.toString()}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          renderOffres(data.offres, data.total_active_candidatures, data.is_accepted_globally);
        } else {
          grid.innerHTML = `<p class="col-span-full text-center py-10 text-red-500">${data.message}</p>`;
        }
      })
      .catch((err) => {
        console.error("Erreur chargement offres:", err);
        grid.innerHTML = `<p class="col-span-full text-center py-10 text-red-500">Une erreur est survenue lors du chargement des offres.</p>`;
      });
  }

  function renderOffres(offres, totalActive, isAccepted) {
    const grid = document.getElementById("offresGrid");
    grid.innerHTML = "";

    if (!offres || offres.length === 0) {
      if (activeFilters.favoris) {
          grid.innerHTML = `
                <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-gray-200 shadow-sm">
                    <div class="bg-red-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-heart text-3xl text-red-200"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Aucun favori</h3>
                    <p class="text-gray-500 max-w-sm mx-auto">Vous n'avez pas encore ajouté d'offres à vos favoris.</p>
                </div>`;
      } else {
          grid.innerHTML = `
                <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-gray-200 shadow-sm">
                    <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-search text-3xl text-gray-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Aucune offre trouvée</h3>
                    <p class="text-gray-500 max-w-sm mx-auto">Il n'y a pas encore d'offres correspondant à vos critères. Essayez de modifier vos filtres.</p>
                </div>`;
      }
      return;
    }

    if (isAccepted) {
      const warning = document.createElement("div");
      warning.className = "col-span-full mb-8 p-6 bg-green-50 border border-green-100 rounded-[2rem] flex items-center gap-4 shadow-sm";
      warning.innerHTML = `
            <div class="w-12 h-12 bg-green-500 rounded-2xl flex items-center justify-center text-white shrink-0">
                <i class="fas fa-check-double text-xl"></i>
            </div>
            <div>
                <h4 class="text-green-800 font-black">Félicitations ! Votre stage est confirmé.</h4>
                <p class="text-green-600 text-sm font-medium">Vous avez été accepté. Toutes les autres candidatures sont désormais verrouillées.</p>
            </div>
        `;
      grid.appendChild(warning);
    } else if (totalActive >= 3) {
      const warning = document.createElement("div");
      warning.className = "col-span-full mb-8 p-6 bg-orange-50 border border-orange-100 rounded-[2rem] flex items-center gap-4 shadow-sm";
      warning.innerHTML = `
            <div class="w-12 h-12 bg-orange-500 rounded-2xl flex items-center justify-center text-white shrink-0">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <div>
                <h4 class="text-orange-800 font-black">Limite de candidatures atteinte (3/3)</h4>
                <p class="text-orange-600 text-sm font-medium">Vous avez atteint le maximum de 3 candidatures actives. Attendez une réponse avant d'en envoyer de nouvelles.</p>
            </div>
        `;
      grid.appendChild(warning);
    }

    offres.forEach((o) => {
      const card = document.createElement("div");
      card.className =
        "bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-blue-100 transition-all duration-300 group";

      let buttonHtml = "";
      if (isAccepted) {
        buttonHtml = `<button disabled class="w-full py-3 bg-gray-100 text-gray-400 cursor-not-allowed rounded-xl font-bold border border-gray-200">
                            Session de stage verrouillée
                         </button>`;
      } else if (o.deja_postule_entreprise > 0) {
        buttonHtml = `<button disabled class="w-full py-3 bg-gray-50 text-gray-400 cursor-not-allowed rounded-xl font-bold border border-gray-200">
                            Candidature en cours (1/Entr.)
                         </button>`;
      } else if (totalActive >= 3) {
        buttonHtml = `<button disabled class="w-full py-3 bg-gray-50 text-gray-400 cursor-not-allowed rounded-xl font-bold border border-gray-200">
                            Limite de 3 candidatures
                         </button>`;
      } else {
        buttonHtml = `<button onclick="postuler(${o.id})" class="w-full py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-blue-600 transition-all shadow-lg shadow-gray-200 hover:shadow-blue-200 btn-postuler">
                            Postuler maintenant
                         </button>`;
      }

      card.innerHTML = `
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3 cursor-pointer hover:bg-blue-50 rounded-xl p-2 -m-2 transition-colors" onclick="openCompanyProfile(${o.entreprise_id || 'null'}, '${esc(o.entreprise)}', '${esc(o.entreprise_photo || '')}')">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <i class="fas fa-building text-xl"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-semibold text-base text-gray-500 hover:text-blue-600 transition-colors">${esc(o.entreprise)}</span>
                            ${parseInt(o.verified_status) === 1 ? `<i class="fas fa-check-circle text-yellow-500 text-sm" title="This company is officially verified"></i>` : ''}
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-wider group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        ${esc(o.type_contrat)}
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-1 group-hover:text-blue-600 transition-colors uppercase tracking-tight">${esc(o.titre)}</h3>
                ${o.specialization ? `<p class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2"><i class="fas fa-microchip text-[9px]"></i> ${esc(o.specialization)}</p>` : ''}
                
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

                <div class="flex items-center justify-between mb-4">
                    <p class="text-[10px] font-black uppercase tracking-wider text-blue-500 flex items-center gap-1.5 bg-blue-50/50 w-fit px-2 py-1.5 rounded-lg border border-blue-100/50 mb-0">
                        <i class="fas fa-users-viewfinder text-xs"></i> <span>Recherche : <strong>${o.nombre_stagiaires || 1}</strong> stagiaire(s)</span>
                    </p>
                    <button onclick="toggleFavorite(${o.id}, this)" class="flex items-center gap-2 px-3 py-1.5 rounded-lg border transition-all duration-300 ${parseInt(o.is_favorited) > 0 ? 'bg-red-50 border-red-100 text-red-500 shadow-sm' : 'bg-gray-50 border-gray-100 text-gray-400 hover:bg-red-50 hover:text-red-500 hover:border-red-100'}" title="${parseInt(o.is_favorited) > 0 ? 'Retirer des favoris' : 'Ajouter aux favoris'}">
                        <i class="${parseInt(o.is_favorited) > 0 ? 'fas' : 'far'} fa-heart"></i>
                        <span class="text-xs font-black fav-count">${o.total_favorites || 0}</span>
                    </button>
                </div>

                <p class="text-gray-600 text-sm line-clamp-3 mb-6">${esc(o.description)}</p>

                ${o.technologies || o.tags ? `
                <div class="flex flex-wrap gap-2 mb-6">
                    ${(o.technologies ? o.technologies.split(',') : []).map(t => `<span class="px-2.5 py-1 bg-gray-50 text-gray-400 rounded-lg text-[9px] font-black uppercase tracking-wider border border-gray-100 hover:border-blue-200 hover:text-blue-600 transition-all cursor-default"><i class="fas fa-code text-[8px] mr-1.5 opacity-50"></i>${esc(t.trim())}</span>`).join('')}
                    ${(o.tags ? o.tags.split(',') : []).map(t => `<span class="px-2.5 py-1 bg-blue-50/30 text-blue-400 rounded-lg text-[9px] font-black uppercase tracking-wider border border-blue-100/30 hover:border-blue-400 transition-all cursor-default"><i class="fas fa-hashtag text-[8px] mr-1.5 opacity-50"></i>${esc(t.trim())}</span>`).join('')}
                </div>
                ` : ''}

                ${buttonHtml}
            `;
      grid.appendChild(card);
    });
  }
  // --- Company Profile Modal Logic ---
  const modalCompanyProfile = document.getElementById("modalCompanyProfile");

  window.openCompanyProfile = (entrepriseId, entrepriseName, entreprisePhoto) => {
    // Show modal
    modalCompanyProfile.classList.remove("hidden");
    setTimeout(() => modalCompanyProfile.classList.add("opacity-100"), 10);

    // Load company details
    loadCompanyProfileDetails(entrepriseId, entrepriseName);
  };


  window.closeCompanyProfile = () => {
    modalCompanyProfile.classList.remove("opacity-100");
    setTimeout(() => {
      modalCompanyProfile.classList.add("hidden");
      // Reset content
      document.getElementById("companyProfileContent").innerHTML = `
        <div class="flex justify-center items-center py-20">
          <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-blue-600 border-t-transparent mb-4"></div>
          <p class="text-gray-500 font-medium ml-4">Chargement des informations...</p>
        </div>
      `;
    }, 300);
  };

  function loadCompanyProfileDetails(entrepriseId, entrepriseName) {
    // Fetch company details from API
    fetch(`../api/entreprise_profile.php?entreprise_id=${entrepriseId}&entreprise_name=${encodeURIComponent(entrepriseName)}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          renderCompanyProfile(data.company);
        } else {
          // Show error message
          document.getElementById("companyProfileContent").innerHTML = `
            <div class="text-center py-20">
              <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-exclamation-triangle text-3xl text-gray-400"></i>
              </div>
              <h3 class="text-xl font-bold text-gray-800 mb-2">Informations non disponibles</h3>
              <p class="text-gray-500 max-w-sm mx-auto">Les informations détaillées de cette entreprise ne sont pas temporairement accessibles.</p>
            </div>
          `;
        }
      })
      .catch(error => {
        console.error("Error loading company profile:", error);
        // Show error message
        document.getElementById("companyProfileContent").innerHTML = `
          <div class="text-center py-20">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <i class="fas fa-exclamation-triangle text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Erreur de chargement</h3>
            <p class="text-gray-500 max-w-sm mx-auto">Une erreur est survenue lors du chargement des informations de l'entreprise.</p>
          </div>
        `;
      });
  }

  function renderCompanyProfile(company) {
    const content = document.getElementById("companyProfileContent");

    // Clear loading state
    content.innerHTML = '';

    const logoPlaceholder = `https://ui-avatars.com/api/?name=${encodeURIComponent(company.nom || 'Entreprise')}&background=random&size=200&bold=true`;
    const websiteUrl = company.website ? (company.website.startsWith('http') ? company.website : 'http://' + company.website) : null;


    // Attach contact details globally to allow the popups to access them
    window.currentCompanyEmails = company.emails || [];
    window.currentCompanyPhones = company.phones || [];

    let emailsHtml = '';
    if (window.currentCompanyEmails.length > 0) {
      emailsHtml = `
        <div class="flex items-center justify-between gap-3 text-sm text-gray-600 bg-gray-50 p-3 rounded-xl border border-gray-100/50 group hover:border-blue-200 transition-all cursor-pointer" onclick="openEmailsListPopup()">
          <div class="flex items-center gap-3">
            <i class="fas fa-envelope text-blue-500 text-xl"></i>
            <div class="flex flex-col">
              <span class="font-black text-gray-800 text-xs">${window.currentCompanyEmails.length} Email(s) de contact</span>
              <span class="text-[9px] font-black uppercase tracking-wider text-blue-400 mt-1">Cliquez pour voir</span>
            </div>
          </div>
          <button class="px-4 py-2 bg-white shadow-sm border border-gray-100 rounded-lg text-[10px] font-black text-blue-600 uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all">
            Ouvrir
          </button>
        </div>
      `;
    }

    let phonesHtml = '';
    if (window.currentCompanyPhones.length > 0) {
      phonesHtml = `
        <div class="flex items-center justify-between gap-3 text-sm text-gray-600 bg-gray-50 p-3 rounded-xl border border-gray-100/50 group hover:border-blue-200 transition-all cursor-pointer" onclick="openPhonesListPopup()">
          <div class="flex items-center gap-3">
            <i class="fas fa-phone-alt text-blue-500 text-xl"></i>
            <div class="flex flex-col">
              <span class="font-black text-gray-800 text-xs">${window.currentCompanyPhones.length} Numéro(s) de contact</span>
              <span class="text-[9px] font-black uppercase tracking-wider text-blue-400 mt-1">Cliquez pour voir</span>
            </div>
          </div>
          <button class="px-4 py-2 bg-white shadow-sm border border-gray-100 rounded-lg text-[10px] font-black text-blue-600 uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all">
            Ouvrir
          </button>
        </div>
      `;
    }



    let achievementsItemsHtml = '';
    if (company.achievements && company.achievements.length > 0) {
      achievementsItemsHtml = company.achievements.map(achievement => {
        const url = achievement.url ? (achievement.url.startsWith('http') ? achievement.url : 'http://' + achievement.url) : null;
        return `
          <div class="bg-white border border-gray-100 rounded-[2rem] p-6 hover:border-blue-200 hover:shadow-xl transition-all duration-300 group relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50/30 rounded-full -mr-12 -mt-12 transition-all group-hover:scale-150"></div>
            <div class="flex items-start gap-5 relative z-10">
              <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-xl shadow-blue-200 shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                <i class="fas fa-rocket text-2xl"></i>
              </div>

              <div class="flex-1 min-w-0 pt-1">
                <h4 class="font-black text-gray-900 group-hover:text-blue-600 transition-colors uppercase text-xs tracking-widest mb-2">${achievement.title}</h4>
                <p class="text-sm text-gray-500 line-clamp-2 mb-4 leading-relaxed font-medium">${achievement.description || 'Présentation du projet.'}</p>
                ${url ? `
                  <a href="${url}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 group-hover:bg-blue-600 group-hover:text-white rounded-xl text-[10px] font-black text-gray-500 uppercase tracking-widest transition-all">
                    Découvrir le projet <i class="fas fa-external-link-alt text-[8px]"></i>
                  </a>
                ` : `
                  <span class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 rounded-xl text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <i class="fas fa-lock text-[8px]"></i> Détails internes
                  </span>
                `}
              </div>
            </div>
          </div>
        `;

      }).join('');
    }

    content.innerHTML = `
      <div class="space-y-10 -mt-8 overflow-x-hidden">
        <!-- 1. HEADER SECTION (Banner & Logo Overlay) -->
        <div class="relative bg-white">
          <!-- Banner (Clean & Balanced Background) -->
          <div class="h-52 w-full bg-gradient-to-br from-blue-700 via-indigo-600 to-blue-800 relative">
             <div class="absolute inset-0 bg-white/5 backdrop-blur-[1px]"></div>
          </div>
          
          <!-- Logo & Stats Overlay -->
          <div class="px-8 -mt-24 relative z-20">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 pb-4">
              <div class="flex flex-col md:flex-row md:items-end gap-10">
                <!-- Profile Image / Logo -->
                <div class="w-44 h-44 rounded-[2.5rem] bg-white p-2 shadow-2xl shadow-blue-900/10 border-4 border-white overflow-hidden relative z-30 shrink-0 ring-8 ring-blue-50/5">
                  <img src="${company.photo_profil ? '../' + company.photo_profil : logoPlaceholder}" class="w-full h-full object-contain bg-gray-50 rounded-[2rem]" alt="Logo">
                </div>
                
                  <!-- Company Info -->
                <div class="pb-6">
                  <h1 class="text-5xl font-black text-gray-900 tracking-tight mb-3 leading-tight">${company.nom}</h1>
                  <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2 text-sm">
                      <span class="font-black text-blue-600 uppercase tracking-widest text-[11px]">Secteur :</span>
                      <span class="font-bold text-gray-500 uppercase tracking-widest text-[11px]">${company.industry_sector || 'Non spécifié'}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                      <span class="font-black text-blue-600 uppercase tracking-widest text-[11px]">Localisation :</span>
                      <span class="font-bold text-gray-500 uppercase tracking-widest text-[11px]">
                        ${company.location_type === 'Workshop' ? 'Workshop' : (company.ville ? company.ville + (company.pays ? ', ' + company.pays : '') : 'Information non disponible')}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="px-8 space-y-12 pb-12">
          <!-- 2. STATISTICS BOXES (Dynamic) -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Offres publiées -->
            <div class="bg-white p-7 rounded-[2.5rem] border border-gray-100 flex items-center gap-6 group hover:shadow-2xl hover:shadow-blue-500/5 transition-all duration-500 shadow-sm relative overflow-hidden">
              <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50/20 rounded-full translate-x-8 -translate-y-8"></div>
              <div class="w-16 h-16 rounded-[1.25rem] bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-briefcase text-2xl"></i>
              </div>
              <div>
                <p class="text-3xl font-black text-gray-900">${company.total_offers || 0}</p>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Offres publiées</p>
              </div>
            </div>
            
            <!-- Réalisations -->
            <div class="bg-white p-7 rounded-[2.5rem] border border-gray-100 flex items-center gap-6 group hover:shadow-2xl hover:shadow-purple-500/5 transition-all duration-500 shadow-sm relative overflow-hidden">
              <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50/20 rounded-full translate-x-8 -translate-y-8"></div>
              <div class="w-16 h-16 rounded-[1.25rem] bg-purple-50 flex items-center justify-center text-purple-600 shadow-inner group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-award text-2xl"></i>
              </div>
              <div>
                <p class="text-3xl font-black text-gray-900">${company.achievements ? company.achievements.length : 0}</p>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Réalisations</p>
              </div>
            </div>

            <!-- Team / Employees Box -->
            <div class="bg-white p-7 rounded-[2.5rem] border border-gray-100 flex items-center gap-6 group hover:shadow-2xl hover:shadow-orange-500/5 transition-all duration-500 shadow-sm relative overflow-hidden">
              <div class="absolute top-0 right-0 w-24 h-24 bg-orange-50/20 rounded-full translate-x-8 -translate-y-8"></div>
              <div class="w-16 h-16 rounded-[1.25rem] bg-orange-50 flex items-center justify-center text-orange-600 shadow-inner group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-users text-2xl"></i>
              </div>
              <div>
                <p class="text-3xl font-black text-gray-900">
                  ${company.company_size || company.taille || '1-10'}
                </p>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Équipes / Employees</p>
              </div>
            </div>
          </div>

          <!-- 3. DESCRIPTION & BIO SECTION -->
          <div class="bg-white rounded-[2.5rem] p-10 border border-gray-100 shadow-sm border-l-[10px] border-blue-600 relative overflow-hidden group">
             <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
               <i class="fas fa-quote-right text-8xl text-blue-900"></i>
             </div>
             <h3 class="text-xs font-black text-blue-600 uppercase tracking-[0.4em] mb-6 flex items-center gap-3">
               <i class="fas fa-fingerprint"></i> engagement de l'entreprise
             </h3>
             <div class="relative z-10">
               <p class="text-gray-600 text-lg leading-9 font-medium">${company.bio || 'Aucune biographie renseignée.'}</p>
             </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- 4. À PROPOS DE L'ENTREPRISE -->
            <div class="space-y-6">
               <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.4em] pl-4">À propos de l'entreprise</h3>
               <div class="bg-white border border-gray-100 rounded-[2.5rem] p-8 shadow-sm space-y-4">
                  <!-- Sector -->
                  <div class="flex items-center gap-6 p-5 bg-gray-50/50 rounded-2xl border border-gray-100/50 hover:bg-white hover:border-blue-100 transition-all duration-300">
                    <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm border border-gray-100 shrink-0">
                      <i class="fas fa-tag text-lg"></i>
                    </div>
                    <div>
                      <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Sector</p>
                      <p class="text-sm font-black text-gray-900 underline decoration-blue-500/30 decoration-2 underline-offset-4">${company.industry_sector || 'Non spécifié'}</p>
                    </div>
                  </div>

                  <!-- Date de création -->
                  <div class="flex items-center gap-6 p-5 bg-gray-50/50 rounded-2xl border border-gray-100/50 hover:bg-white hover:border-blue-100 transition-all duration-300">
                    <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm border border-gray-100 shrink-0">
                      <i class="fas fa-history text-lg"></i>
                    </div>
                    <div>
                      <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Date de création</p>
                      <p class="text-sm font-black text-gray-900 underline decoration-blue-500/30 decoration-2 underline-offset-4">${company.date_creation || 'Non renseignée'}</p>
                    </div>
                  </div>

                  <!-- Location -->
                  <div class="flex items-center gap-6 p-5 bg-gray-50/50 rounded-2xl border border-gray-100/50 hover:bg-white hover:border-blue-100 transition-all duration-300">
                    <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm border border-gray-100 shrink-0">
                      <i class="fas fa-map-pin text-lg"></i>
                    </div>
                    <div>
                      <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Location</p>
                      <p class="text-sm font-black text-gray-900 underline decoration-blue-500/30 decoration-2 underline-offset-4">${company.ville || 'Nouakchott'}</p>
                    </div>
                  </div>

                  <!-- Employees Count -->
                  <div class="flex items-center gap-6 p-5 bg-gray-50/50 rounded-2xl border border-gray-100/50 hover:bg-white hover:border-blue-100 transition-all duration-300">
                      <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm border border-gray-100 shrink-0">
                        <i class="fas fa-users-cog text-lg"></i>
                      </div>
                      <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Nombre d'employés</p>
                        <p class="text-sm font-black text-gray-900 underline decoration-blue-500/30 decoration-2 underline-offset-4">
                           ${company.company_size || company.taille || '1-10'} Employés
                        </p>
                      </div>
                  </div>
               </div>
            </div>


            <!-- RIGHT COLUMN: Contacts & Website -->
            <div class="flex flex-col gap-12">
               <!-- 5. COORDONNÉES SECTION -->
               <div class="space-y-6">
                  <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.4em] pl-4">Coordonnées</h3>
                  <div class="bg-white border border-gray-100 rounded-[2.5rem] p-10 space-y-8 shadow-sm flex flex-col justify-between">
                     <div class="space-y-6">
                       <div class="space-y-4">
                         <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em]">Numéros de téléphone</p>
                         <div class="grid grid-cols-1 gap-3">
                           ${phonesHtml || '<p class="text-xs text-gray-400 italic">Aucun numéro enregistré.</p>'}
                         </div>
                       </div>

                       <div class="space-y-4">
                         <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em]">Emails de contact</p>
                         <div class="grid grid-cols-1 gap-3">
                           ${emailsHtml || '<p class="text-xs text-gray-400 italic">Aucun email enregistré.</p>'}
                         </div>
                       </div>
                     </div>
                  </div>
               </div>

               <!-- 6. SITE WEB OFFICIEL SECTION (Dynamic) -->
               ${websiteUrl ? `
               <div class="space-y-6">
                  <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.4em] pl-4">🌐 Site web officiel</h3>
                  <div class="bg-white border border-gray-100 rounded-[2.5rem] p-10 shadow-sm">
                      <a href="${websiteUrl}" target="_blank" class="flex items-center justify-between p-5 bg-gray-50/50 rounded-2xl border border-gray-100/50 hover:bg-white hover:border-blue-100 hover:shadow-md transition-all duration-300 group cursor-pointer">
                          <div class="flex items-center gap-6">
                            <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm border border-gray-100 shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                              <i class="fas fa-link text-lg"></i>
                            </div>
                            <div class="overflow-hidden">
                              <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Lien Officiel</p>
                              <p class="text-sm font-black text-blue-600 truncate underline decoration-blue-500/30 decoration-2 underline-offset-4">
                                 ${websiteUrl.replace(/^https?:\/\//, '')}
                              </p>
                            </div>
                          </div>
                          <i class="fas fa-external-link-alt text-gray-300 group-hover:text-blue-500 transition-colors"></i>
                      </a>
                  </div>
               </div>
               ` : ''}
            </div>
          </div>

          <!-- 6. PROJECTS / RÉALISATIONS -->
          ${achievementsItemsHtml ? `
            <div class="space-y-8">
              <div class="flex items-center justify-between px-4">
                <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.4em]">Projets / Réalisations</h3>
                <span class="px-4 py-1.5 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-blue-100">${company.achievements.length} Projets</span>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                ${achievementsItemsHtml}
              </div>
            </div>
          ` : ''}
        </div>
      </div>
    `;


  }

  // Close on backdrop click
  if (modalCompanyProfile) {
    modalCompanyProfile.addEventListener("click", (e) => {
      if (e.target === modalCompanyProfile) {
        closeCompanyProfile();
      }
    });
  }

  // --- Postuler Modal Logic ---
  const modalPostuler = document.getElementById("modalPostuler");
  const formPostuler = document.getElementById("formPostuler");
  const closePostuler = document.getElementById("closePostuler");

  const closePostulerAction = () => {
    modalPostuler.classList.remove("opacity-100");
    setTimeout(() => {
      modalPostuler.classList.add("hidden");
      formPostuler.reset();
      document.getElementById("cv_file_name_display").textContent = "Choisir un fichier";
      document.getElementById("cv_file_name_display").classList.remove("text-blue-600");
    }, 300);
  };

  if (closePostuler) {
    closePostuler.addEventListener("click", closePostulerAction);
  }

  // Close on backdrop
  if (modalPostuler) {
    modalPostuler.addEventListener("click", (e) => {
      if (e.target === modalPostuler) {
        closePostulerAction();
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

    // Reset state
    document.getElementById("postulerDocumentId").value = "";
    document.getElementById("postulerLmId").value = "";
    document.getElementById("new_cv_upload_container").classList.add("hidden");
    document.getElementById("new_lm_upload_container").classList.add("hidden");
    document.querySelector('input[name="cv_option"][value="new"]').checked = false;
    document.querySelector('input[name="lm_option"][value="new"]').checked = false;

    // Load offer questions if any
    const questionsContainer = document.getElementById("offer_questions_container");
    const questionsList = document.getElementById("questions_list");
    questionsContainer.classList.add("hidden");
    questionsList.innerHTML = "";

    const currentOffer = window.allOffres ? window.allOffres.find(o => o.id == offreId) : null;
    if (currentOffer && currentOffer.questions) {
        const questions = currentOffer.questions.split("\n").filter(q => q.trim() !== "");
        if (questions.length > 0) {
            questionsContainer.classList.remove("hidden");
            questionsList.innerHTML = questions.map((q, i) => `
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">${esc(q)}</label>
                    <textarea name="reponses_questions[${i}]" required rows="2" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium focus:bg-white focus:border-blue-500 transition-all outline-none resize-none" placeholder="Votre réponse..."></textarea>
                </div>
            `).join("");
        }
    }

    loadApplicationCVs();
    loadApplicationLMs();
  };

  window.toggleFavorite = (offerId, btnElement) => {
    e = window.event;
    if (e) {
        e.stopPropagation();
        e.preventDefault();
    }
    
    const formData = new FormData();
    formData.append('offer_id', offerId);

    fetch('../api/favoris.php', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const icon = btnElement.querySelector('i.fa-heart');
        const countSpan = btnElement.querySelector('.fav-count');
        const currentCount = parseInt(data.total_favorites) || 0;
        
        countSpan.textContent = currentCount;
        
        if (data.action === 'added') {
          // Change to filled heart red
          btnElement.className = 'flex items-center gap-2 px-3 py-1.5 rounded-lg border transition-all duration-300 bg-red-50 border-red-100 text-red-500 shadow-sm';
          icon.className = 'fas fa-heart';
          btnElement.title = 'Retirer des favoris';
        } else if (data.action === 'removed') {
          // Change to outline heart gray
          btnElement.className = 'flex items-center gap-2 px-3 py-1.5 rounded-lg border transition-all duration-300 bg-gray-50 border-gray-100 text-gray-400 hover:bg-red-50 hover:text-red-500 hover:border-red-100';
          icon.className = 'far fa-heart';
          btnElement.title = 'Ajouter aux favoris';
        }
      } else {
        showMessage(data.message, "error");
      }
    })
    .catch(err => {
      console.error(err);
      showMessage("Erreur lors de l'ajout aux favoris", "error");
    });
  };

  function loadApplicationCVs() {
    const list = document.getElementById("application_cv_selector");
    if (!list) return;

    fetch("../api/documents.php")
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          const cvs = data.documents.filter(d => d.type === 'cv');
          if (cvs.length === 0) {
            list.innerHTML = `
              <div class="py-6 text-center border border-dashed border-gray-100 rounded-2xl">
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Aucun CV dans votre profil</p>
              </div>`;
            // Default to 'new' if nothing in profile
            document.querySelector('input[name="cv_option"][value="new"]').checked = true;
            document.getElementById("new_cv_upload_container").classList.remove("hidden");
          } else {
            list.innerHTML = cvs.map((cv, i) => `
              <label class="flex items-center gap-4 p-4 bg-white border border-gray-100 rounded-[1.5rem] cursor-pointer hover:border-blue-500 hover:shadow-md transition-all group relative">
                <input type="radio" name="cv_option" value="profile" ${i === 0 ? 'checked' : ''} data-doc-id="${cv.id}" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500 transition-all">
                <div class="flex-1 overflow-hidden">
                  <p class="text-[11px] font-black text-gray-800 truncate" title="${cv.file_name}">${cv.file_name}</p>
                  <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">${new Date(cv.created_at).toLocaleDateString()}</p>
                </div>
                <div class="w-8 h-8 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                  <i class="fas fa-file-pdf"></i>
                </div>
              </label>
            `).join('');

            // Set initial document_id if first is checked
            const firstRadio = list.querySelector('input[name="cv_option"]:checked');
            if (firstRadio) {
              document.getElementById("postulerDocumentId").value = firstRadio.dataset.docId;
            }
          }
          checkFormValidity();
        } else {
          // Default to 'new' on failure
          document.querySelector('input[name="cv_option"][value="new"]').checked = true;
          document.getElementById("new_cv_upload_container").classList.remove("hidden");
        }
      })
      .catch(() => {
        document.querySelector('input[name="cv_option"][value="new"]').checked = true;
        document.getElementById("new_cv_upload_container").classList.remove("hidden");
      });
  }

  function loadApplicationLMs() {
    const list = document.getElementById("application_lm_selector");
    if (!list) return;

    fetch("../api/documents.php")
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          const lms = data.documents.filter(d => d.type === 'motivation');
          if (lms.length === 0) {
            list.innerHTML = `
              <div class="py-6 text-center border border-dashed border-gray-100 rounded-2xl">
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Aucune lettre dans votre profil</p>
              </div>`;
            // Default to 'new' if nothing in profile
            document.querySelector('input[name="lm_option"][value="new"]').checked = true;
            document.getElementById("new_lm_upload_container").classList.remove("hidden");
          } else {
            list.innerHTML = lms.map((lm, i) => `
              <label class="flex items-center gap-4 p-4 bg-white border border-gray-100 rounded-[1.5rem] cursor-pointer hover:border-blue-500 hover:shadow-md transition-all group relative">
                <input type="radio" name="lm_option" value="profile" ${i === 0 ? 'checked' : ''} data-doc-id="${lm.id}" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500 transition-all">
                <div class="flex-1 overflow-hidden">
                  <p class="text-[11px] font-black text-gray-800 truncate" title="${lm.file_name}">${lm.file_name}</p>
                  <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">${new Date(lm.created_at).toLocaleDateString()}</p>
                </div>
                <div class="w-8 h-8 flex items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                  <i class="fas fa-file-alt"></i>
                </div>
              </label>
            `).join('');

            // Set initial lm_id if first is checked
            const firstRadio = list.querySelector('input[name="lm_option"]:checked');
            if (firstRadio) {
              document.getElementById("postulerLmId").value = firstRadio.dataset.docId;
            }
          }
          checkFormValidity();
        } else {
          document.querySelector('input[name="lm_option"][value="new"]').checked = true;
          document.getElementById("new_lm_upload_container").classList.remove("hidden");
        }
      })
      .catch(() => {
        document.querySelector('input[name="lm_option"][value="new"]').checked = true;
        document.getElementById("new_lm_upload_container").classList.remove("hidden");
      });
  }

  // Event delegation for radio buttons to avoid re-attaching
  document.addEventListener('change', (e) => {
    if (e.target && e.target.name === 'cv_option') {
      const uploadContainer = document.getElementById("new_cv_upload_container");
      const docIdInput = document.getElementById("postulerDocumentId");

      if (e.target.value === 'new') {
        uploadContainer.classList.remove("hidden");
        docIdInput.value = "";
      } else {
        uploadContainer.classList.add("hidden");
        docIdInput.value = e.target.dataset.docId || "";
      }
      checkFormValidity();
    }

    if (e.target && e.target.name === 'lm_option') {
      const uploadContainer = document.getElementById("new_lm_upload_container");
      const lmIdInput = document.getElementById("postulerLmId");

      if (e.target.value === 'new') {
        uploadContainer.classList.remove("hidden");
        lmIdInput.value = "";
      } else {
        uploadContainer.classList.add("hidden");
        lmIdInput.value = e.target.dataset.docId || "";
      }
    }
  });

  function checkFormValidity() {
    const btn = document.getElementById("btnSubmitApplication");
    const cvOption = document.querySelector('input[name="cv_option"]:checked');
    const cvFile = document.getElementById("cv_file_input").files.length > 0;
    const docId = document.getElementById("postulerDocumentId").value;
    const message = document.querySelector('textarea[name="message_motivation"]').value.trim();

    let isValid = false;
    if (cvOption) {
      if (cvOption.value === 'new') {
        isValid = cvFile;
      } else {
        isValid = docId !== "";
      }
    }

    // Must have a message too
    if (message === "") isValid = false;

    btn.disabled = !isValid;
  }

  // Add listener for message motivation too
  document.querySelector('textarea[name="message_motivation"]').addEventListener('input', checkFormValidity);

  // Handle file input display
  const cvFileInput = document.getElementById("cv_file_input");
  const cvFileNameDisplay = document.getElementById("cv_file_name_display");
  if (cvFileInput) {
    cvFileInput.addEventListener("change", (e) => {
      if (e.target.files.length > 0) {
        cvFileNameDisplay.textContent = e.target.files[0].name;
        cvFileNameDisplay.classList.add("text-blue-600");
      } else {
        cvFileNameDisplay.textContent = "Choisir un fichier";
        cvFileNameDisplay.classList.remove("text-blue-600");
      }
      checkFormValidity();
    });
  }

  const lmFileInput = document.getElementById("lm_file_input");
  const lmFileNameDisplay = document.getElementById("lm_file_name_display");
  if (lmFileInput) {
    lmFileInput.addEventListener("change", (e) => {
      if (e.target.files.length > 0) {
        lmFileNameDisplay.textContent = e.target.files[0].name;
        lmFileNameDisplay.classList.add("text-purple-600");
      } else {
        lmFileNameDisplay.textContent = "Choisir un fichier";
        lmFileNameDisplay.classList.remove("text-purple-600");
      }
    });
  }
});

// --- Contact Modal Helpers (Instruction 1 & 2) ---

window.openPhonesListPopup = () => {
  const container = document.getElementById("phones_list_container");
  container.innerHTML = '';

  if (!window.currentCompanyPhones || window.currentCompanyPhones.length === 0) {
    container.innerHTML = '<p class="text-xs text-gray-400 italic text-center py-4">Aucun numéro enregistré.</p>';
  } else {
    container.innerHTML = window.currentCompanyPhones.map(p => {
      const number = p.phone_number || p.number || '';
      const icon = p.type === 'WhatsApp' ? 'fab fa-whatsapp' : (p.type === 'Mobile' ? 'fas fa-mobile-alt' : 'fas fa-phone-alt');
      const color = p.type === 'WhatsApp' ? 'text-green-500 bg-green-50' : 'text-blue-500 bg-blue-50';
      const isWhatsApp = p.type === 'WhatsApp';
      const actionLink = isWhatsApp ? `https://wa.me/${number.replace(/\s+/g, '')}` : `tel:${number.replace(/\s+/g, '')}`;
      const btnColor = isWhatsApp ? 'bg-green-500 hover:bg-green-600' : 'bg-blue-600 hover:bg-blue-700';

      return `
        <div class="flex items-center justify-between gap-3 bg-gray-50 p-3 rounded-2xl border border-gray-100 group transition-all hover:bg-white hover:shadow-md hover:border-gray-200">
          <div class="flex items-center gap-3 shrink-0">
            <div class="w-10 h-10 rounded-xl ${color} flex items-center justify-center shrink-0 shadow-sm">
              <i class="${icon}"></i>
            </div>
            <div class="flex flex-col">
               <span class="font-black text-gray-800 tracking-wider text-sm whitespace-nowrap">${number}</span>
               <span class="text-[9px] font-black uppercase tracking-wider text-gray-400">${p.type || 'Téléphone'}</span>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0 ml-auto">
            <button onclick="copyContact('${number}')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-gray-400 hover:text-blue-600 hover:shadow-md transition-all shadow-sm border border-gray-100" title="Copier">
                <i class="far fa-copy text-xs"></i>
            </button>
            <a href="${actionLink}" target="${isWhatsApp ? '_blank' : '_self'}" class="w-8 h-8 flex items-center justify-center rounded-lg ${btnColor} text-white shadow-sm hover:shadow-md transition-all" title="${isWhatsApp ? 'Envoyer WhatsApp' : 'Appeler'}">
                <i class="${isWhatsApp ? 'fas fa-paper-plane' : 'fas fa-phone'} text-xs"></i>
            </a>
          </div>
        </div>
      `;
    }).join('');
  }

  const modal = document.getElementById("modalPhonesList");
  modal.classList.remove("hidden");
  setTimeout(() => modal.classList.add("opacity-100"), 10);
  modal.querySelector('div').classList.remove("scale-95");
};

window.openEmailsListPopup = () => {
  const container = document.getElementById("emails_list_container");
  container.innerHTML = '';

  if (!window.currentCompanyEmails || window.currentCompanyEmails.length === 0) {
    container.innerHTML = '<p class="text-xs text-gray-400 italic text-center py-4">Aucun email enregistré.</p>';
  } else {
    container.innerHTML = window.currentCompanyEmails.map(e => {
      const email = typeof e === 'string' ? e : (e.email || '');
      return `
        <div class="flex items-center justify-between gap-3 bg-gray-50 p-3 rounded-2xl border border-gray-100 group transition-all hover:bg-white hover:shadow-md hover:border-gray-200">
          <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 shadow-sm">
              <i class="fas fa-envelope"></i>
            </div>
            <div class="flex flex-col overflow-hidden min-w-0">
               <span class="font-black text-gray-800 text-sm truncate" title="${email}">${email}</span>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0 ml-auto">
            <button onclick="copyContact('${email}')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-gray-400 hover:text-blue-600 hover:shadow-md transition-all shadow-sm border border-gray-100" title="Copier">
                <i class="far fa-copy text-xs"></i>
            </button>
            <a href="mailto:${email}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-purple-600 hover:bg-purple-700 text-white shadow-sm hover:shadow-md transition-all" title="Envoyer">
                <i class="fas fa-paper-plane text-xs"></i>
            </a>
          </div>
        </div>
      `;
    }).join('');
  }

  const modal = document.getElementById("modalEmailsList");
  modal.classList.remove("hidden");
  setTimeout(() => modal.classList.add("opacity-100"), 10);
  modal.querySelector('div').classList.remove("scale-95");
};

window.closeContactModal = (type) => {
  const modalId = type === 'phonesList' ? "modalPhonesList" : (type === 'emailsList' ? "modalEmailsList" : "modalPhonesList");
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.classList.remove("opacity-100");
  modal.querySelector('div').classList.add("scale-95");
  setTimeout(() => modal.classList.add("hidden"), 300);
};

window.copyContact = (valueToCopy) => {
  console.log('Copying contact:', valueToCopy);
  if (!valueToCopy) return;
  navigator.clipboard.writeText(valueToCopy).then(() => {
    console.log('Successfully copied to clipboard');
    showMessage("Copié dans le presse-papier !", "success");
  }).catch(err => {
    console.error('Failed to copy:', err);
    showMessage("Erreur lors de la copie", "error");
  });
};

// Missing showMessage function
window.showMessage = (message, type = "info") => {
  // Create toast notification
  const toast = document.createElement("div");
  toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-medium z-50 transform translate-x-full transition-transform duration-300 ${type === "success" ? "bg-green-600" :
    type === "error" ? "bg-red-600" :
      "bg-blue-600"
    }`;
  toast.textContent = message;

  document.body.appendChild(toast);

  // Show toast
  setTimeout(() => {
    toast.classList.remove("translate-x-full");
  }, 10);

  // Hide toast after 3 seconds
  setTimeout(() => {
    toast.classList.add("translate-x-full");
    setTimeout(() => {
      if (document.body.contains(toast)) {
        document.body.removeChild(toast);
      }
    }, 300);
  }, 3000);
};

function debounce(func, wait) {
  let timeout;
  return function () {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, arguments), wait);
  };
}

