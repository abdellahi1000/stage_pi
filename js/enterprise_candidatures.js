/* js/enterprise_candidatures.js */
document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("candidaturesTableBody");
  const modal = document.getElementById("modalCandidat");
  const modalContent = document.getElementById("candidatDetailsContent");

  let activeFilters = {
    offre_id: "",
    statut: "",
  };

  loadCandidatures();
  loadOffresFilter();

  // --- Custom Dropdowns Logic ---
  document.querySelectorAll(".custom-dropdown").forEach((dropdown) => {
    const button = dropdown.querySelector("button");
    const menu = dropdown.querySelector(".dropdown-menu");
    const label = button.querySelector("span");
    const icon = button.querySelector(".fa-chevron-down");

    button.addEventListener("click", (e) => {
      e.stopPropagation();
      document.querySelectorAll(".dropdown-menu").forEach((m) => {
        if (m !== menu) m.classList.remove("active");
      });
      document.querySelectorAll(".fa-chevron-down").forEach((i) => {
        if (i !== icon) i.classList.remove("rotate-180");
      });

      menu.classList.toggle("active");
      icon.classList.toggle("rotate-180");
    });

    // Use event delegation for dynamic items
    menu.addEventListener("click", (e) => {
      const item = e.target.closest(".dropdown-item");
      if (!item) return;

      const value = item.dataset.value;
      const text = item.textContent;

      label.textContent = text;
      menu.classList.remove("active");
      icon.classList.remove("rotate-180");

      if (dropdown.id === "dropdownOffre") {
        activeFilters.offre_id = value;
      } else if (dropdown.id === "dropdownStatut") {
        activeFilters.statut = value;
      }

      loadCandidatures();
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

  // Close modal on backdrop click
  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModalCandidat();
  });

  function loadOffresFilter() {
    const menuOffre = document.getElementById("menuOffre");
    fetch("../api/offres.php?user_id=me")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const items = data.offres
            .map(
              (o) =>
                `<div class="dropdown-item" data-value="${o.id}">${o.titre}</div>`,
            )
            .join("");
          menuOffre.innerHTML =
            '<div class="dropdown-item" data-value="">Toutes les offres</div>' +
            items;
        }
      });
  }

  function loadCandidatures() {
    tableBody.innerHTML =
      '<tr><td colspan="5" class="py-20 text-center bg-white rounded-[2rem]"><div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent mb-4"></div><p class="text-gray-500">Chargement...</p></td></tr>';

    let url = "../api/candidatures.php?action=enterprise_list";
    if (activeFilters.offre_id) url += "&offre_id=" + activeFilters.offre_id;
    if (activeFilters.statut) url += "&statut=" + activeFilters.statut;

    fetch(url)
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          if (data.candidatures.length === 0) {
            tableBody.innerHTML =
              '<tr><td colspan="5" class="py-20 text-center bg-white rounded-[2rem] text-gray-400 font-medium italic">Aucune candidature pour le moment.</td></tr>';
            return;
          }
          tableBody.innerHTML = data.candidatures
            .map(
              (c) => `
                            <tr class="bg-white group hover:shadow-lg transition-all">
                                <td class="py-6 px-6 first:rounded-l-[2.5rem]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 font-bold text-xs">
                                            ${c.prenom[0]}${c.nom[0]}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900">${c.prenom} ${c.nom}</p>
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">${c.email}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 px-6">
                                    <span class="font-bold text-gray-700 text-sm">${c.offre_titre}</span>
                                </td>
                                <td class="py-6 px-6 text-sm text-gray-500 font-medium">
                                    ${new Date(c.date_candidature).toLocaleDateString()}
                                </td>
                                <td class="py-6 px-6">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider ${getStatusClass(c.statut)}">
                                        ${c.statut}
                                    </span>
                                </td>
                                <td class="py-6 px-6 last:rounded-r-[2.5rem] text-right">
                                    <button onclick="viewCandidat(${c.id})" class="px-4 py-2 bg-gray-50 text-gray-700 rounded-xl font-bold text-xs hover:bg-blue-600 hover:text-white transition-all">Détails</button>
                                </td>
                            </tr>
                        `,
            )
            .join("");
        }
      });
  }

  function getStatusClass(statut) {
    switch (statut) {
      case "en_attente":
        return "bg-yellow-50 text-yellow-600";
      case "vue":
        return "bg-blue-50 text-blue-600";
      case "accepte":
        return "bg-green-50 text-green-600";
      case "refuse":
        return "bg-red-50 text-red-600";
      default:
        return "bg-gray-50 text-gray-600";
    }
  }

  window.viewCandidat = (id) => {
    fetch(`../api/candidatures.php?action=details&id=${id}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const c = data.candidature;
          modalContent.innerHTML = `
                        <div class="p-10">
                            <div class="flex justify-between items-start mb-10">
                                <div class="flex items-center gap-6">
                                    <div class="w-20 h-20 bg-blue-50 rounded-[2rem] flex items-center justify-center text-blue-600 text-2xl font-black">
                                        ${c.prenom[0]}${c.nom[0]}
                                    </div>
                                    <div>
                                        <h2 class="text-3xl font-black text-gray-900">${c.prenom} ${c.nom}</h2>
                                        <p class="text-blue-600 font-bold uppercase tracking-widest text-xs">${c.offre_titre}</p>
                                    </div>
                                </div>
                                <button onclick="closeModalCandidat()" class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                                <div>
                                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Message de motivation</h3>
                                    <div class="bg-gray-50 p-8 rounded-[2rem] border border-gray-100 text-gray-700 leading-relaxed italic">
                                        "${c.message_motivation || "Aucun message fourni."}"
                                    </div>
                                    ${c.cv_specifique ? `
                                    <div class="mt-8">
                                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Curriculum Vitae</h3>
                                        <a href="../${c.cv_specifique}" target="_blank" class="flex items-center gap-4 p-4 bg-blue-50 text-blue-600 rounded-2xl hover:bg-blue-100 transition-colors border border-blue-100">
                                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-blue-500 shadow-sm">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                            <span class="font-bold">Télécharger le CV</span>
                                            <i class="fas fa-download ml-auto"></i>
                                        </a>
                                    </div>
                                    ` : ''}
                                </div>
                                <div class="space-y-6">
                                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Décision</h3>
                                    <div class="flex flex-col gap-3">
                                        <button onclick="updateStatut(${c.id}, 'accepte')" class="w-full py-4 bg-green-600 text-white rounded-2xl font-black shadow-lg shadow-green-100 hover:bg-green-700 hover:-translate-y-1 transition-all flex items-center justify-center gap-3">
                                            <i class="fas fa-check"></i> Retenir ce profil
                                        </button>
                                        <button onclick="updateStatut(${c.id}, 'refuse')" class="w-full py-4 bg-white text-red-600 border border-red-100 rounded-2xl font-black hover:bg-red-50 transition-all flex items-center justify-center gap-3">
                                            <i class="fas fa-times"></i> Décliner
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
          modal.classList.remove("hidden");
          setTimeout(() => modal.classList.add("opacity-100"), 10);
        }
      });
  };

  window.closeModalCandidat = () => {
    modal.classList.remove("opacity-100");
    setTimeout(() => modal.classList.add("hidden"), 300);
  };

  window.updateStatut = (id, statut) => {
    fetch("../api/candidatures.php", {
      method: "PUT",
      body: new URLSearchParams({ candidature_id: id, statut: statut }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          closeModalCandidat();
          loadCandidatures();
        }
      });
  };
});
