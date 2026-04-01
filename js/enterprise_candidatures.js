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

  // Setup listeners for custom dropdown changes (handled by global.js)
  document.querySelectorAll('#dropdownOffre, #dropdownStatut').forEach(d => {
      d.addEventListener('change', () => loadCandidatures());
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
              (c) => {
                const status = strtolower(c.statut);
                const displayStatus = c.display_status || status;
                const isClosed = status === 'closed';
                const displayName = isClosed ? '********' : `${c.prenom || ''} ${c.nom || ''}`;
                const displayEmail = isClosed ? '@.' : (c.email || '');
                const rowClass = isClosed ? 'closed-row' : '';
                const btnAttr = isClosed ? 'disabled class="px-4 py-2 bg-gray-100 text-gray-400 rounded-xl font-bold text-xs cursor-not-allowed"' : `onclick="viewCandidat(${c.id})" class="px-4 py-2 bg-gray-50 text-gray-700 rounded-xl font-bold text-xs hover:bg-blue-600 hover:text-white transition-all"`;
                const btnLabel = isClosed ? 'Indisponible' : 'Détails';

                return `
                            <tr class="bg-white group hover:shadow-lg transition-all ${rowClass}">
                                <td class="py-6 px-6 first:rounded-l-[2.5rem]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 font-bold text-xs">
                                            ${isClosed ? '?' : (c.prenom && c.nom ? c.prenom[0] + c.nom[0] : 'U')}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900">${displayName}</p>
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">${displayEmail}</p>
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
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider ${getStatusClass(status)}">
                                        ${displayStatus}
                                    </span>
                                </td>
                                <td class="py-6 px-6 last:rounded-r-[2.5rem] text-right">
                                    <button ${btnAttr}>${btnLabel}</button>
                                </td>
                            </tr>
                        `;
              }
            )
            .join("");
        } else {
            tableBody.innerHTML = `<tr><td colspan="5" class="py-20 text-center bg-white rounded-[2rem] text-red-500 font-medium">Erreur : ${data.message || 'Impossible de charger les candidatures.'}</td></tr>`;
        }
      })
      .catch((err) => {
          console.error("Fetch error:", err);
          tableBody.innerHTML = '<tr><td colspan="5" class="py-20 text-center bg-white rounded-[2rem] text-red-500 font-medium">Erreur serveur. Veuillez réessayer plus tard.</td></tr>';
      });
  }

  function getStatusClass(statut) {
    const s = statut.toLowerCase();
    switch (s) {
      case "pending":
      case "en_attente":
        return "bg-yellow-50 text-yellow-600";
      case "vue":
        return "bg-blue-50 text-blue-600";
      case "accepted":
      case "accepte":
        return "bg-green-50 text-green-600";
      case "rejected":
      case "refuse":
        return "bg-red-50 text-red-600";
      case "closed":
      case "CLOSED":
        return "bg-gray-200 text-gray-600 opacity-60";
      default:
        return "bg-gray-50 text-gray-600";
    }
  }

  function strtolower(str) {
    return str ? str.toLowerCase() : "";
  }

  window.viewCandidat = (id) => {
    fetch(`../api/candidatures.php?action=details&id=${id}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const c = data.candidature;
          const cvStr = c.cv_specifique || c.profil_cv_path;
          const lmStr = c.lm_specifique || c.profil_lm_path;

          let cvButtonHtml = "";
          if (cvStr) {
            const cvUrl = cvStr.startsWith("http") ? cvStr : "../" + cvStr;
            cvButtonHtml = `<a href="${cvUrl}" target="_blank" class="px-6 py-3 bg-blue-100 text-blue-700 font-bold rounded-xl hover:bg-blue-600 hover:text-white transition flex items-center gap-2 text-sm"><i class="fas fa-file-pdf"></i> Voir le CV</a>`;
          } else {
            cvButtonHtml = `<button disabled class="px-6 py-3 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed flex items-center gap-2 text-sm"><i class="fas fa-file-excel"></i> CV non fourni</button>`;
          }

          let lmButtonHtml = "";
          if (lmStr) {
            const lmUrl = lmStr.startsWith("http") ? lmStr : "../" + lmStr;
            lmButtonHtml = `<a href="${lmUrl}" target="_blank" class="px-6 py-3 bg-purple-100 text-purple-700 font-bold rounded-xl hover:bg-purple-600 hover:text-white transition flex items-center gap-2 text-sm"><i class="fas fa-file-alt"></i> Lettre de motivation</a>`;
          }

          let questionsHtml = "";
          if (c.offer_questions) {
              const questions = c.offer_questions.split("\n").filter(q => q.trim() !== "");
              const answers = c.reponses_questions ? JSON.parse(c.reponses_questions) : [];
              
              if (questions.length > 0) {
                  questionsHtml = `
                    <div class="mt-8 space-y-6">
                        <h3 class="text-[10px] font-black text-blue-600 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-question-circle"></i> RÉPONSES AUX QUESTIONS
                        </h3>
                        <div class="space-y-4">
                            ${questions.map((q, i) => `
                                <div class="bg-blue-50/50 p-6 rounded-2xl border border-blue-100/50">
                                    <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">${q}</p>
                                    <p class="text-gray-700 font-bold">${answers[i] || '<span class="italic text-gray-400">Aucune réponse</span>'}</p>
                                </div>
                            `).join("")}
                        </div>
                    </div>
                  `;
              }
          }

          modalContent.innerHTML = `
                        <div class="p-10">
                            <div class="flex justify-between items-start mb-10">
                                <div class="flex items-center gap-6">
                                    <div class="w-20 h-20 bg-blue-50 rounded-[2rem] flex items-center justify-center text-blue-600 text-2xl font-black">
                                        ${(c.prenom && c.nom ? c.prenom[0] + c.nom[0] : 'U')}
                                    </div>
                                    <div>
                                        <h2 class="text-3xl font-black text-gray-900">${c.prenom || ''} ${c.nom || ''}</h2>
                                        <p class="text-blue-600 font-bold uppercase tracking-widest text-xs">${c.offre_titre}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <button onclick="closeModalCandidat()" class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                                <div>
                                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Message de motivation</h3>
                                    <div class="bg-gray-50 p-8 rounded-[2rem] border border-gray-100 text-gray-700 leading-relaxed italic mb-6">
                                        "${c.message_motivation || "Aucun message fourni."}"
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        ${cvButtonHtml}
                                        ${lmButtonHtml}
                                    </div>
                                    ${questionsHtml}
                                </div>
                                <div class="space-y-6">
                                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Décision</h3>

                                    <div id="decisionButtonsContainer" class="flex flex-col gap-3">
                                        <button onclick="showAccepteForm()" class="w-full py-4 bg-green-600 text-white rounded-2xl font-black shadow-lg shadow-green-100 hover:bg-green-700 hover:-translate-y-1 transition-all flex items-center justify-center gap-3">
                                            <i class="fas fa-check"></i> Retenir ce profil
                                        </button>
                                        <button onclick="updateStatut(${c.id}, 'refuse')" class="w-full py-4 bg-white text-red-600 border border-red-100 rounded-2xl font-black hover:bg-red-50 transition-all flex items-center justify-center gap-3">
                                            <i class="fas fa-times"></i> Décliner
                                        </button>
                                    </div>

                                    <div id="accepteFormContainer" class="hidden">
                                        <div class="bg-white p-6 rounded-2xl border border-green-100 shadow-sm space-y-4">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h3 class="text-sm font-bold text-gray-900">Détails d'acceptation</h3>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Renseignez les informations de contact et un message personnalisé qui seront envoyés au candidat.
                                                    </p>
                                                </div>
                                                <button type="button" onclick="cancelAcceptance()" class="text-xs text-gray-400 hover:text-gray-600">
                                                    Annuler
                                                </button>
                                            </div>

                                            <p id="acc_error" class="text-xs text-red-500 mb-1 hidden"></p>

                                            <div class="space-y-3">
                                                <div>
                                                    <label for="acc_msg" class="block text-xs font-semibold text-gray-600 mb-1">Message d'acceptation</label>
                                                    <textarea id="acc_msg" rows="3" placeholder="Message d'acceptation et points importants..." class="w-full p-3 rounded-xl border border-gray-200 outline-none focus:border-green-500 text-sm"></textarea>
                                                </div>

                                                <div>
                                                    <label for="acc_email" class="block text-xs font-semibold text-gray-600 mb-1">Email de contact</label>
                                                    <input type="email" id="acc_email" placeholder="exemple@entreprise.com, contact@entreprise.com" class="w-full p-3 rounded-xl border border-gray-200 outline-none focus:border-green-500 text-sm">
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div>
                                                        <label for="acc_phone" class="block text-xs font-semibold text-gray-600 mb-1">Numéro de téléphone</label>
                                                        <input type="text" id="acc_phone" placeholder="+222" class="w-full p-3 rounded-xl border border-gray-200 outline-none focus:border-green-500 text-sm">
                                                    </div>
                                                    <div>
                                                        <label for="acc_whats" class="block text-xs font-semibold text-gray-600 mb-1">Numéro WhatsApp</label>
                                                        <input type="text" id="acc_whats" placeholder="+222" class="w-full p-3 rounded-xl border border-gray-200 outline-none focus:border-green-500 text-sm">
                                                    </div>
                                                </div>

                                                <div class="pt-1">
                                                    <input type="file" id="acc_signature_file" class="hidden" accept="image/*">
                                                    <button type="button" id="acc_signature_btn" class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-pen-nib mr-2 text-gray-400"></i>
                                                        Ajouter une signature
                                                    </button>
                                                    <p id="acc_signature_status" class="text-[11px] text-gray-500 mt-1"></p>
                                                </div>

                                                <div class="flex gap-3 pt-2">
                                                    <button type="button" onclick="cancelAcceptance()" class="flex-1 py-3 rounded-xl border border-gray-200 text-gray-700 font-semibold hover:bg-gray-50 text-sm">
                                                        Retour
                                                    </button>
                                                    <button type="button" onclick="submitAcceptance(${c.id}, event)" class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 text-sm">
                                                        Confirmer l'acceptation
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

          // Initialise signature upload handlers without changing layout
          const sigBtn = document.getElementById("acc_signature_btn");
          const sigInput = document.getElementById("acc_signature_file");
          const sigStatus = document.getElementById("acc_signature_status");

          if (sigBtn && sigInput) {
            sigBtn.addEventListener("click", function () {
              sigInput.click();
            });

            sigInput.addEventListener("change", function (e) {
              const file = e.target.files && e.target.files[0];
              if (!file) return;

              const formData = new FormData();
              formData.append("action", "upload_company_signature");
              formData.append("signature", file);

              fetch("../api/user.php", {
                method: "POST",
                body: formData,
              })
                .then((res) => res.json())
                .then((data) => {
                  if (data && data.success) {
                    if (sigStatus) {
                      sigStatus.textContent = "Signature enregistrée pour les acceptations.";
                    }
                    if (typeof showMessage === "function") {
                      showMessage("Signature enregistrée.", "success");
                    }
                  } else {
                    if (sigStatus) {
                      sigStatus.textContent =
                        (data && data.message) ||
                        "Impossible d'enregistrer la signature.";
                    }
                    if (typeof showMessage === "function") {
                      showMessage(
                        (data && data.message) ||
                          "Erreur lors de l'enregistrement de la signature.",
                        "error",
                      );
                    }
                  }
                })
                .catch((err) => {
                  console.error("Erreur upload signature", err);
                  if (sigStatus) {
                    sigStatus.textContent =
                      "Erreur de connexion. Réessayez plus tard.";
                  }
                });
            });
          }

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
        if (!data || !data.success) {
          console.error("Erreur de mise à jour du statut", data);
        }
      })
      .catch((err) => {
        console.error("Erreur technique de mise à jour du statut", err);
      })
      .finally(() => {
        // Fermer systématiquement la modale et rafraîchir la liste,
        // comme un bouton "normal" même en cas d'erreur réseau.
        closeModalCandidat();
        loadCandidatures();
      });
  };

  window.showAccepteForm = () => {
    const buttons = document.getElementById("decisionButtonsContainer");
    const form = document.getElementById("accepteFormContainer");
    if (!buttons || !form) return;
    buttons.classList.add("hidden");
    form.classList.remove("hidden");
  };

  window.cancelAcceptance = () => {
    const buttons = document.getElementById("decisionButtonsContainer");
    const form = document.getElementById("accepteFormContainer");
    const errorEl = document.getElementById("acc_error");
    if (buttons) buttons.classList.remove("hidden");
    if (form) form.classList.add("hidden");
    if (errorEl) {
      errorEl.textContent = "";
      errorEl.classList.add("hidden");
    }
  };

  window.submitAcceptance = (id, evt) => {
    if (evt && typeof evt.preventDefault === "function") {
      evt.preventDefault();
    }

    const msgEl = document.getElementById("acc_msg");
    const emailEl = document.getElementById("acc_email");
    const phoneEl = document.getElementById("acc_phone");
    const whatsEl = document.getElementById("acc_whats");
    const errorEl = document.getElementById("acc_error");

    const msg = (msgEl && msgEl.value ? msgEl.value : "").trim();
    const email = (emailEl && emailEl.value ? emailEl.value : "").trim();
    const phone = (phoneEl && phoneEl.value ? phoneEl.value : "").trim();
    const whats = (whatsEl && whatsEl.value ? whatsEl.value : "").trim();

    if (errorEl) {
      errorEl.textContent = "";
      errorEl.classList.add("hidden");
    }

    fetch("../api/candidatures.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        action: "accept_with_details",
        candidature_id: id,
        message: msg,
        email: email,
        phone: phone,
        whatsapp: whats,
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (!data || !data.success) {
          console.error("Réponse d'acceptation non réussie", data);
          if (errorEl) {
            errorEl.textContent =
              "Une erreur est survenue lors de l'acceptation. Veuillez réessayer.";
            errorEl.classList.remove("hidden");
          }
        }
      })
      .catch((err) => {
        console.error("Erreur technique lors de l'acceptation", err);
        if (errorEl) {
          errorEl.textContent =
            "Problème de connexion. Vérifiez votre réseau puis réessayez.";
          errorEl.classList.remove("hidden");
        }
      })
      .finally(() => {
        // Même comportement qu'un bouton normal : on ferme la modale
        // et on rafraîchit la liste une fois la requête terminée.
        closeModalCandidat();
        loadCandidatures();
      });
  };
});

function escJs(str) {
  if (!str) return "";
  return str.replace(/'/g, "\\'").replace(/"/g, '\\"');
}
