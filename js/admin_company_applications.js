/* js/admin_company_applications.js */
document.addEventListener("DOMContentLoaded", () => {
    const appsGrid = document.getElementById("applicationsGrid");
    const modalApp = document.getElementById("modalApp");
    const searchApp = document.getElementById("searchApp");
    const dropdownStatutApp = document.getElementById("dropdownStatutApp");
    
    let allApps = [];
    let currentApp = null;

    loadApps();

    function loadApps() {
        if (!appsGrid) return;
        appsGrid.innerHTML = `
            <div class="col-span-full py-20 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent mb-4"></div>
                <p class="text-gray-500 font-medium">Chargement des candidatures...</p>
            </div>`;

        fetch("../api/admin_company_applications.php?action=list")
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    allApps = data.applications;
                    filterLists();
                } else {
                    appsGrid.innerHTML = `<div class="col-span-full py-20 text-center text-red-500">${data.message}</div>`;
                }
            })
            .catch(err => {
                console.error("Error loading apps:", err);
                appsGrid.innerHTML = `<div class="col-span-full py-20 text-center text-red-500">Erreur lors du chargement.</div>`;
            });
    }

    function renderApps(apps) {
        if (!appsGrid) return;
        if (apps.length === 0) {
            appsGrid.innerHTML = '<div class="col-span-full py-20 text-center text-gray-400 font-medium">Aucune candidature trouvée.</div>';
            return;
        }

        appsGrid.innerHTML = apps.map(app => `
            <div class="bg-white p-6 md:p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all group flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-6 flex-1">
                    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 font-black text-xl group-hover:bg-blue-600 group-hover:text-white transition-all">
                        ${app.prenom ? app.prenom[0] : ""}${app.nom ? app.nom[0] : ""}
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 mb-1">${app.prenom} ${app.nom}</h3>
                        <p class="text-sm font-medium text-gray-500">A postulé pour : <span class="text-blue-600 font-bold">${app.offre_titre}</span></p>
                    </div>
                </div>

                <div class="flex items-center gap-6 w-full md:w-auto">
                    <div class="text-right hidden md:block">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">${new Date(app.date_candidature).toLocaleDateString()}</p>
                        <span class="px-3 py-1 ${getStatusClass(app.statut)} rounded-full text-[10px] font-black uppercase tracking-wider">
                            ${getStatusLabel(app.statut)}
                        </span>
                    </div>
                    <button onclick="viewAppDetails(${app.id})" class="flex-1 md:flex-none py-3 px-6 bg-gray-900 text-white rounded-xl font-black text-sm hover:bg-black transition-all">
                        Détails
                    </button>
                    ${app.statut === 'pending' ? `
                    <div class="flex gap-2">
                        <button onclick="quickUpdateStatus(${app.id}, 'accepted')" class="w-10 h-10 bg-green-50 text-green-600 rounded-xl flex items-center justify-center hover:bg-green-600 hover:text-white transition-all">
                            <i class="fas fa-check"></i>
                        </button>
                        <button onclick="quickUpdateStatus(${app.id}, 'rejected')" class="w-10 h-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    ` : ''}
                </div>
            </div>
        `).join("");
    }

    if (searchApp) {
        searchApp.addEventListener("input", filterLists);
    }

    function filterLists() {
        if (!allApps) return;
        const q = searchApp ? searchApp.value.toLowerCase() : "";
        const statusVal = document.querySelector("#dropdownStatutApp input") ? document.querySelector("#dropdownStatutApp input").value : "";
        const typeVal = document.querySelector("#dropdownTypeApp input") ? document.querySelector("#dropdownTypeApp input").value : "";
        
        let filtered = allApps;
        
        if (q) {
            filtered = filtered.filter(a => 
                (a.nom && a.nom.toLowerCase().includes(q)) || 
                (a.prenom && a.prenom.toLowerCase().includes(q)) || 
                (a.offre_titre && a.offre_titre.toLowerCase().includes(q)) || 
                (a.email && a.email.toLowerCase().includes(q))
            );
        }
        
        if (statusVal) {
            filtered = filtered.filter(a => a.statut === statusVal);
        }

        if (typeVal) {
            filtered = filtered.filter(a => (a.type_contrat || 'Stage') === typeVal);
        }
        
        renderApps(filtered);
    }

    // Modal Details logic
    window.viewAppDetails = (id) => {
        const app = allApps.find(a => a.id == id);
        if (!app) return;
        currentApp = app;

        const initialEl = document.getElementById("modalUserInitial");
        const nameEl = document.getElementById("modalUserName");
        const emailEl = document.getElementById("modalUserEmail");
        const titreEl = document.getElementById("modalOffreTitre");
        const motivationEl = document.getElementById("modalMotivation");
        
        const specialiteEl = document.getElementById("modalUserSpecialite");
        const universityEl = document.getElementById("modalUserUniversity");
        const domainesEl = document.getElementById("modalUserDomaine");
        const niveauEl = document.getElementById("modalUserNiveau");
        const skillsEl = document.getElementById("modalUserSkills");

        if (initialEl) initialEl.textContent = `${app.prenom ? app.prenom[0] : ""}${app.nom ? app.nom[0] : ""}`;
        if (nameEl) nameEl.textContent = `${app.prenom} ${app.nom}`;
        if (emailEl) emailEl.textContent = app.email;
        if (titreEl) titreEl.textContent = app.offre_titre;
        if (motivationEl) motivationEl.textContent = app.message_motivation || "Aucun message fourni.";
        
        if (specialiteEl) specialiteEl.textContent = app.specialite || "Non spécifiée";
        if (universityEl) universityEl.textContent = app.universite || "Non spécifiée";
        if (domainesEl) domainesEl.textContent = app.domaine_formation || "Non spécifié";
        if (niveauEl) niveauEl.textContent = app.niveau_etudes || "Non spécifié";
        if (skillsEl) skillsEl.textContent = app.skills || "Non spécifiées";

        // Questions & Answers
        const qContainer = document.getElementById("modalQuestionsContainer");
        const qList = document.getElementById("modalQuestionsList");
        if (qContainer && qList) {
            qContainer.classList.add("hidden");
            qList.innerHTML = "";
            if (app.offer_questions) {
                const questions = app.offer_questions.split("\n").filter(q => q.trim() !== "");
                let answers = [];
                try { answers = app.reponses_questions ? JSON.parse(app.reponses_questions) : []; } catch(e) {}
                
                if (questions.length > 0) {
                    qContainer.classList.remove("hidden");
                    qList.innerHTML = questions.map((q, i) => `
                        <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100/50">
                            <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-1">${q}</p>
                            <p class="text-sm font-bold text-gray-900">${answers[i] || '<span class="italic text-gray-400">Aucune réponse</span>'}</p>
                        </div>
                    `).join("");
                }
            }
        }
        
        const profileBtn = document.getElementById("btnViewProfile");
        const cvBtn = document.getElementById("btnViewCV");
        if (profileBtn) profileBtn.href = `student_profile.php?id=${app.user_id}`;
        if (cvBtn) cvBtn.href = app.cv_specifique ? `../uploads/cvs/${app.cv_specifique}` : `view_cv.php?id=${app.user_id}`;

        const acceptBtn = document.getElementById("btnAcceptApp");
        const rejectBtn = document.getElementById("btnRejectApp");

        if (app.statut !== 'pending') {
            if (acceptBtn) acceptBtn.classList.add("hidden");
            if (rejectBtn) rejectBtn.classList.add("hidden");
        } else {
            if (acceptBtn) acceptBtn.classList.remove("hidden");
            if (rejectBtn) rejectBtn.classList.remove("hidden");
        }

        openModal();
    };

    window.quickUpdateStatus = (id, status) => {
        if (!confirm(`Voulez-vous ${status === 'accepted' ? 'accepter' : 'refuser'} cette candidature ?`)) return;
        
        fetch("../api/admin_company_applications.php?action=update_status", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id, status })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadApps();
            } else {
                alert(data.message);
            }
        });
    };

    const btnAcceptAppModal = document.getElementById("btnAcceptApp");
    if (btnAcceptAppModal) {
        btnAcceptAppModal.addEventListener("click", () => {
            if (currentApp) quickUpdateStatus(currentApp.id, 'accepted');
            closeModal();
        });
    }

    const btnRejectAppModal = document.getElementById("btnRejectApp");
    if (btnRejectAppModal) {
        btnRejectAppModal.addEventListener("click", () => {
            if (currentApp) quickUpdateStatus(currentApp.id, 'rejected');
            closeModal();
        });
    }

    const btnBlockStudentModal = document.getElementById("btnBlockStudent");
    if (btnBlockStudentModal) {
        btnBlockStudentModal.addEventListener("click", () => {
            if (!currentApp) return;
            if (!confirm(`Voulez-vous vraiment bloquer l'étudiant ${currentApp.prenom} ${currentApp.nom} ? Celui-ci ne pourra plus postuler auprès de votre entreprise.`)) return;

            fetch("../api/admin_company_applications.php?action=block_student", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ student_id: currentApp.user_id })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Étudiant bloqué avec succès.");
                    closeModal();
                    loadApps();
                } else {
                    alert(data.message);
                }
            });
        });
    }

    // Modal Helpers
    function openModal() {
        if (!modalApp) return;
        modalApp.classList.remove("hidden");
        setTimeout(() => {
            modalApp.classList.remove("opacity-0");
            modalApp.classList.add("opacity-100");
            const transformEl = modalApp.querySelector(".transform");
            if (transformEl) {
                transformEl.classList.remove("scale-95");
                transformEl.classList.add("scale-100");
            }
        }, 10);
    }

    function closeModal() {
        if (!modalApp) return;
        modalApp.classList.remove("opacity-100");
        modalApp.classList.add("opacity-0");
        const transformEl = modalApp.querySelector(".transform");
        if (transformEl) {
            transformEl.classList.remove("scale-100");
            transformEl.classList.add("scale-95");
        }
        setTimeout(() => {
            modalApp.classList.add("hidden");
        }, 300);
    }

    document.querySelectorAll(".close-modal").forEach(btn => btn.addEventListener("click", closeModal));

    // Dropdown helpers (Generic for current page)
    document.querySelectorAll(".custom-dropdown").forEach((dropdown) => {
        const btn = dropdown.querySelector("button");
        const menu = dropdown.querySelector(".dropdown-menu");
        const input = dropdown.querySelector("input[type='hidden']");
        const label = btn ? btn.querySelector("span") : null;

        if (!btn || !menu) return;

        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            menu.classList.toggle("invisible");
            menu.classList.toggle("opacity-0");
            menu.classList.toggle("translate-y-2");
            menu.classList.toggle("scale-95");
        });

        menu.querySelectorAll(".dropdown-item").forEach((item) => {
            item.addEventListener("click", () => {
                const val = item.getAttribute("data-value");
                const txt = item.textContent;
                if (input) input.value = val;
                if (label) label.textContent = txt;
                
                menu.classList.add("invisible", "opacity-0", "translate-y-2", "scale-95");
                
                if (dropdown.id === 'dropdownStatutApp') {
                    filterLists();
                }
            });
        });
    });

    document.addEventListener("click", () => {
        document.querySelectorAll(".dropdown-menu").forEach((menu) => {
            menu.classList.add("invisible", "opacity-0", "translate-y-2", "scale-95");
        });
    });

    function getStatusClass(status) {
        switch (status) {
            case "pending": return "bg-blue-100 text-blue-700";
            case "accepted": return "bg-green-100 text-green-700";
            case "rejected": return "bg-rose-100 text-rose-700";
            default: return "bg-gray-100 text-gray-700";
        }
    }

    function getStatusLabel(status) {
        switch (status) {
            case "pending": return "En Attente";
            case "accepted": return "Accepté";
            case "rejected": return "Refusé";
            default: return status;
        }
    }
});
