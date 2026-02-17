/* js/candidatures.js */

document.addEventListener("DOMContentLoaded", () => {
  const candidaturesList = document.getElementById("candidaturesList");
  const offersCandidaturesContainer = document.getElementById(
    "offersCandidaturesContainer",
  );

  // --- Student View Logic ---
  if (candidaturesList) {
    fetch("../api/mes_candidatures.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          renderStudentCandidatures(data.candidatures);
        } else {
          candidaturesList.innerHTML = `<p class="col-span-full text-center py-10 text-red-500">${data.message}</p>`;
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        candidaturesList.innerHTML = `<p class="col-span-full text-center py-10 text-red-500">Erreur lors du chargement des candidatures.</p>`;
      });
  }

  function renderStudentCandidatures(candidatures) {
    if (candidatures.length === 0) {
      candidaturesList.innerHTML = `
                <div class="col-span-full py-20 text-center">
                    <div class="bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-paper-plane text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">Vous n'avez pas encore postulé à des offres.</p>
                    <a href="offres.php" class="inline-block mt-4 text-blue-600 font-semibold hover:underline">Voir les offres disponibles</a>
                </div>`;
      return;
    }

    candidaturesList.innerHTML = candidatures
      .map((c) => {
        const statusData = getStatusData(c.statut);
        const date = new Date(c.date_candidature).toLocaleDateString("fr-FR", {
          day: "numeric",
          month: "long",
          year: "numeric",
        });

        return `
                <div class="candidature-card bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-bold text-lg text-gray-800">${esc(c.titre)}</h3>
                        <span class="px-3 py-1 ${statusData.class} rounded-full text-xs font-bold uppercase">
                            ${statusData.label}
                        </span>
                    </div>
                    <div class="space-y-2 mb-6">
                        <p class="text-sm text-gray-600 flex items-center gap-2">
                            <i class="fas fa-building text-gray-400"></i> 
                            <strong>Entreprise :</strong> ${esc(c.entreprise)}
                        </p>
                        <p class="text-sm text-gray-600 flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-gray-400"></i> 
                            <strong>Date :</strong> ${date}
                        </p>
                        <p class="text-sm text-gray-600 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-gray-400"></i> 
                            ${esc(c.localisation)}
                        </p>
                    </div>
                </div>`;
      })
      .join("");
  }

  // --- Enterprise View Logic ---
  if (offersCandidaturesContainer) {
    fetch("../api/candidatures.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          renderEnterpriseOffers(data.offres);
        } else {
          offersCandidaturesContainer.innerHTML = `<p class="col-span-full text-center py-10 text-red-500">${data.message}</p>`;
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        offersCandidaturesContainer.innerHTML = `<p class="col-span-full text-center py-10 text-red-500">Erreur lors du chargement des offres.</p>`;
      });
  }

  function renderEnterpriseOffers(offres) {
    if (offres.length === 0) {
      offersCandidaturesContainer.innerHTML = `
                <div class="col-span-full py-20 text-center bg-white rounded-2xl border border-dashed border-gray-300">
                    <p class="text-gray-500">Vous n'avez pas encore publié d'offres.</p>
                    <a href="offres.php" class="inline-block mt-4 text-blue-600 font-semibold hover:underline">Déposer une offre</a>
                </div>`;
      return;
    }

    offersCandidaturesContainer.innerHTML = offres
      .map((offre) => {
        const candidatesHtml =
          offre.candidatures.length > 0
            ? offre.candidatures
                .map((c) => {
                  const statusData = getStatusData(c.statut);
                  const isPending =
                    c.statut === "en_attente" || c.statut === "vue";

                  return `
                        <div class="candidat-card flex flex-col md:flex-row md:items-center justify-between p-4 rounded-xl border border-gray-50 hover:bg-gray-50 transition" data-id="${
                          c.candidature_id
                        }">
                            <div class="candidat-info mb-4 md:mb-0">
                                <h4 class="font-bold text-gray-800">${esc(c.prenom)} ${esc(c.nom)}</h4>
                                <p class="text-sm text-gray-500">${esc(c.formation || "Profil non renseigné")} - ${esc(c.etablissement || "")}</p>
                            </div>
                            <div class="status-actions flex items-center space-x-3">
                                <span class="status-badge px-3 py-1 ${
                                  statusData.class
                                } rounded-full text-xs font-bold uppercase">
                                    ${statusData.label}
                                </span>
                                ${
                                  isPending
                                    ? `
                                    <button onclick="updateStatus(${c.candidature_id}, 'accepte', this)" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition">Accepter</button>
                                    <button onclick="updateStatus(${c.candidature_id}, 'refuse', this)" class="px-4 py-2 bg-red-100 text-red-600 rounded-lg text-sm font-bold hover:bg-red-200 transition">Refuser</button>
                                `
                                    : ""
                                }
                            </div>
                        </div>`;
                })
                .join("")
            : '<p class="text-center py-6 text-gray-400 italic">Aucune candidature pour le moment.</p>';

        return `
                <div class="offre-item bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    <div class="offre-header bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">${esc(offre.titre)}</h3>
                            <p class="text-sm text-gray-500">Lieu : ${esc(offre.localisation)}</p>
                        </div>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold uppercase">${
                          offre.nombre_candidatures
                        } candidature${offre.nombre_candidatures > 1 ? "s" : ""}</span>
                    </div>
                    <div class="candidats-list p-6 space-y-4">
                        ${candidatesHtml}
                    </div>
                </div>`;
      })
      .join("");
  }

  function getStatusData(status) {
    switch (status) {
      case "accepte":
        return { label: "Accepté", class: "bg-green-100 text-green-800" };
      case "refuse":
        return { label: "Refusé", class: "bg-red-100 text-red-800" };
      case "vue":
        return { label: "Vue", class: "bg-blue-100 text-blue-800" };
      default:
        return { label: "En attente", class: "bg-yellow-100 text-yellow-800" };
    }
  }

  // --- Global Actions ---

  window.updateStatus = function (candidatureId, status, btn) {
    const card = btn.closest(".candidat-card");
    const actionsContainer = card.querySelector(".status-actions");
    const oldHtml = actionsContainer.innerHTML;

    actionsContainer.innerHTML = `<div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-600 border-t-transparent"></div>`;

    const formData = new URLSearchParams();
    formData.append("candidature_id", candidatureId);
    formData.append("statut", status);

    fetch("../api/candidatures.php", {
      method: "PUT",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: formData.toString(),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const statusData = getStatusData(status);
          actionsContainer.innerHTML = `
                        <span class="status-badge px-3 py-1 ${statusData.class} rounded-full text-xs font-bold uppercase">
                            ${statusData.label}
                        </span>
                    `;
          showMessage("Statut mis à jour avec succès", "success");
        } else {
          actionsContainer.innerHTML = oldHtml;
          showMessage(data.message, "error");
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        actionsContainer.innerHTML = oldHtml;
        showMessage("Erreur lors de la mise à jour", "error");
      });
  };

  // Search Filtering
  const searchInputHeader = document.getElementById("searchInputHeader");
  if (searchInputHeader) {
    searchInputHeader.addEventListener("input", function () {
      const filter = this.value.toLowerCase();
      document
        .querySelectorAll(".candidature-card, .offre-item")
        .forEach((item) => {
          const textToSearch = item.innerText.toLowerCase();
          item.style.display = textToSearch.includes(filter) ? "" : "none";
        });
    });
  }
});
