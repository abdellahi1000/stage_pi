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
                <div class="candidature-card bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition flex flex-col">
                    <div class="flex justify-between items-start gap-3 mb-2">
                        <h3 class="font-bold text-lg text-gray-800 flex-1 min-w-0">${esc(c.titre)}</h3>
                        <span class="px-3 py-1 ${statusData.class} rounded-full text-xs font-bold uppercase shrink-0">
                            ${statusData.label}
                        </span>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-wider text-blue-500 flex items-center gap-1.5 mb-4 bg-blue-50/50 w-fit px-2 py-1 rounded-lg border border-blue-100/50">
                        <i class="fas fa-users-viewfinder text-xs"></i> <span>Recherche : <strong>${c.nombre_stagiaires || 1}</strong> stagiaire(s)</span>
                    </p>

                    <div class="space-y-2 mb-4">
                        <p class="text-sm text-gray-600 flex items-center gap-2">
                            <i class="fas fa-building text-gray-400 shrink-0 w-4 text-center"></i>
                            <span><strong>Entreprise :</strong> ${esc(c.entreprise)}</span>
                            ${parseInt(c.verified_status) === 1 ? `<i class="fas fa-check-circle shrink-0" style="color: gold; text-shadow: 0 0 2px rgba(0,0,0,0.2); font-size: 14px;" title="Entreprise vérifiée"></i>` : ''}
                        </p>
                        <p class="text-sm text-gray-600 flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-gray-400 shrink-0 w-4 text-center"></i>
                            <span><strong>Date :</strong> ${date}</span>
                        </p>
                        <p class="text-sm text-gray-600 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-gray-400 shrink-0 w-4 text-center"></i>
                            <span>${esc(c.localisation)}</span>
                        </p>
                    </div>
                    ${(c.statut === 'accepted' || c.statut === 'accepte') ? `
                    <div class="mt-auto pt-4 border-t border-gray-100">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="text-[10px] font-black uppercase tracking-wider text-green-600 bg-green-50 px-3 py-2 rounded-lg inline-flex items-center gap-2 w-fit">
                                <i class="fas fa-check-circle text-xs"></i>
                                <span>Accepté le ${c.acceptance_date ? new Date(c.acceptance_date).toLocaleDateString("fr-FR") : date}</span>
                            </div>
                            <button onclick='viewAcceptanceDoc(${JSON.stringify(c).replace(/'/g, "&#39;")})' class="flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all font-bold text-[10px] shadow-sm group w-full sm:w-auto shrink-0">
                                <span class="bg-blue-600 text-white text-[8px] px-1.5 py-0.5 rounded group-hover:bg-white group-hover:text-blue-600 transition-colors uppercase">PDF</span>
                                <i class="fas fa-file-signature text-sm"></i> Voir le dossier
                            </button>
                        </div>
                    </div>` : ''}
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
                  c.statut === "pending";

                return `
                        <div class="candidat-card flex flex-col md:flex-row md:items-center justify-between p-4 rounded-xl border border-gray-50 hover:bg-gray-50 transition" data-id="${c.candidature_id
                  }">
                            <div class="candidat-info mb-4 md:mb-0">
                                <h4 class="font-bold text-gray-800">${esc(c.prenom)} ${esc(c.nom)}</h4>
                                <p class="text-sm text-gray-500">${esc(c.formation || "Profil non renseigné")} - ${esc(c.etablissement || "")}</p>
                            </div>
                            <div class="status-actions flex items-center space-x-3">
                                <span class="status-badge px-3 py-1 ${statusData.class
                  } rounded-full text-xs font-bold uppercase">
                                    ${statusData.label}
                                </span>
                                ${isPending
                    ? `
                                    <button onclick="updateStatus(${c.candidature_id}, 'accepted', this)" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition">Accepter</button>
                                    <button onclick="updateStatus(${c.candidature_id}, 'rejected', this)" class="px-4 py-2 bg-red-100 text-red-600 rounded-lg text-sm font-bold hover:bg-red-200 transition">Refuser</button>
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
                            <div class="flex items-center gap-4 mt-1">
                                <p class="text-xs text-gray-500 flex items-center gap-1"><i class="fas fa-map-marker-alt text-blue-300"></i> ${esc(offre.localisation)}</p>
                                <p class="text-[10px] font-black uppercase tracking-widest text-blue-600 bg-blue-100/50 px-2 py-0.5 rounded border border-blue-200/50">
                                    <i class="fas fa-users-viewfinder"></i> ${offre.nombre_stagiaires || 1} Requis
                                </p>
                            </div>
                        </div>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold uppercase">${offre.nombre_candidatures
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
    const s = status ? status.toLowerCase() : "";
    switch (s) {
      case "accepted":
      case "accepte":
        return { label: "Accepté", class: "bg-green-100 text-green-800" };
      case "rejected":
      case "refuse":
        return { label: "Refusé", class: "bg-red-100 text-red-800" };
      case "closed":
        return { label: "Clôturé", class: "bg-gray-100 text-gray-500" };
      case "pending":
      case "en_attente":
      case "vue":
        return { label: "En attente", class: "bg-yellow-100 text-yellow-800" };
      default:
        return { label: "Inconnu", class: "bg-gray-100 text-gray-500" };
    }
  }

  // --- Global Actions ---

  window.updateStatus = function (candidatureId, status, btn) {
    const card = btn.closest(".candidat-card");
    if (!card) return;
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
          if (typeof showMessage === "function") showMessage("Statut mis à jour avec succès", "success");
        } else {
          actionsContainer.innerHTML = oldHtml;
          if (typeof showMessage === "function") showMessage(data.message, "error");
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        actionsContainer.innerHTML = oldHtml;
        if (typeof showMessage === "function") showMessage("Erreur lors de la mise à jour", "error");
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

  window.viewAcceptanceDoc = function (c) {
    const modal = document.getElementById("acceptanceModal");
    if (!modal) return;

    document.getElementById("accDocCompany").textContent = c.entreprise || "";
    document.getElementById("accDocMessage").textContent = c.acceptance_message || "Félicitations ! Votre profil a été retenu pour ce stage.";
    document.getElementById("accDocStudentName").textContent = (c.prenom && c.nom) ? c.prenom + " " + c.nom : "Étudiant / Stagiaire";

    // Company signature (stored securely, shown only to student in this context)
    const sigContainer = document.getElementById("accDocSignatureContainer");
    const sigImg = document.getElementById("accDocSignature");
    if (sigContainer && sigImg) {
      if (c.company_signature_path) {
        sigImg.src = c.company_signature_path.indexOf("/") === 0 ? c.company_signature_path : "../" + c.company_signature_path;
        sigImg.alt = "Signature " + (c.entreprise || "");
        sigContainer.classList.remove("hidden");
      } else {
        sigContainer.classList.add("hidden");
      }
    }

    // Contact options (clickable, student-only use)
    const accDocEmail = document.getElementById("accDocEmail");
    const emailCont = document.getElementById("accDocEmailContainer");
    if (c.company_contact_email) {
      accDocEmail.textContent = c.company_contact_email;
      accDocEmail.href = "mailto:" + c.company_contact_email;
      if (emailCont) emailCont.style.display = "flex";
      else accDocEmail.parentElement.style.display = "flex";
    } else if (emailCont) {
      emailCont.style.display = "none";
    }

    const accDocPhone = document.getElementById("accDocPhone");
    const phoneCont = document.getElementById("accDocPhoneContainer");
    if (c.company_contact_phone) {
      accDocPhone.textContent = c.company_contact_phone;
      accDocPhone.href = "tel:" + c.company_contact_phone;
      if (phoneCont) phoneCont.style.display = "flex";
      else accDocPhone.parentElement.style.display = "flex";
    } else if (phoneCont) {
      phoneCont.style.display = "none";
    }

    const accDocWhats = document.getElementById("accDocWhats");
    const whatsCont = document.getElementById("accDocWhatsContainer");
    if (c.company_whatsapp) {
      accDocWhats.textContent = c.company_whatsapp;
      let waNumber = c.company_whatsapp.replace(/[^0-9]/g, "");
      accDocWhats.href = "https://wa.me/" + waNumber;
      if (whatsCont) whatsCont.style.display = "flex";
      else accDocWhats.parentElement.style.display = "flex";
    } else if (whatsCont) {
      whatsCont.style.display = "none";
    }

    // Set download action
    const downloadBtn = document.getElementById("downloadAcceptancePdf");
    if (downloadBtn) {
      downloadBtn.onclick = () => generateAcceptancePDF(c);
    }

    modal.classList.remove("hidden");
    setTimeout(() => modal.classList.add("opacity-100"), 10);
  };

  async function generateAcceptancePDF(c) {
    const element = document.getElementById("pdfContent");
    const opt = {
      margin: 10,
      filename: `Confirmation_Stage_${c.entreprise.replace(/\s+/g, "_")}.pdf`,
      image: { type: "jpeg", quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true },
      jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
    };

    // Temporarily hide close button and download button for PDF clear view
    const noPrint = element.querySelectorAll(".no-print, button:not(#downloadAcceptancePdf)");
    const downloadBtn = document.getElementById("downloadAcceptancePdf");

    if (downloadBtn) downloadBtn.style.display = "none";
    noPrint.forEach(el => el.style.visibility = "hidden");

    try {
      await html2pdf().set(opt).from(element).save();
    } catch (err) {
      console.error("PDF Error:", err);
      alert("Erreur lors de la génération du PDF.");
    } finally {
      if (downloadBtn) downloadBtn.style.display = "flex";
      noPrint.forEach(el => el.style.visibility = "visible");
    }
  }

  window.closeAcceptanceModal = function () {
    const modal = document.getElementById("acceptanceModal");
    if (!modal) return;
    modal.classList.remove("opacity-100");
    setTimeout(() => modal.classList.add("hidden"), 300);
  };

  function esc(str) {
    if (!str) return "";
    const div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
  }
});
