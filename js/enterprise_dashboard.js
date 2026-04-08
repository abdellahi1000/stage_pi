/* js/enterprise_dashboard.js */
document.addEventListener("DOMContentLoaded", () => {
  loadStats();
  loadRecentCandidatures();

  function loadStats() {
    fetch("../api/enterprise_dashboard.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          if (document.getElementById("stat-offres")) document.getElementById("stat-offres").textContent = data.stats.active_offers || 0;
          if (document.getElementById("stat-candidats")) document.getElementById("stat-candidats").textContent = data.stats.total_applications || 0;
          if (document.getElementById("stat-accepted-stagiaires")) document.getElementById("stat-accepted-stagiaires").textContent = data.stats.accepted_stagiaires || 0;
          if (document.getElementById("stat-accepted-alternances")) document.getElementById("stat-accepted-alternances").textContent = data.stats.accepted_alternances || 0;
          
          if (document.getElementById("stat-messages")) {
            const count = data.stats.total_messages || 0;
            document.getElementById("stat-messages").textContent = count;
            const notifDot = document.getElementById("notifDot");
            if (notifDot) {
              if (count > 0) notifDot.classList.remove("hidden");
              else notifDot.classList.add("hidden");
            }
          }
        }
      })
      .catch((err) => console.error("Erreur stats:", err));
  }

  function loadRecentCandidatures() {
    const container = document.getElementById("recent-candidatures");
    fetch("../api/candidatures.php?action=recent_enterprise")
      .then((res) => res.json())
      .then((data) => {
        if (data.success && data.candidatures.length > 0) {
          container.innerHTML = data.candidatures
            .map(
              (c) => `
                        <div class="flex items-center justify-between p-6 bg-gray-50 rounded-2xl border border-gray-100 group hover:border-blue-200 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-blue-600 font-bold">
                                    ${c.prenom[0]}${c.nom[0]}
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900">${c.prenom} ${c.nom}</h4>
                                    <p class="text-sm text-gray-500">A postulé pour : <span class="font-medium text-blue-600">${c.offre_titre}</span></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">${new Date(c.date_candidature).toLocaleDateString()}</p>
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px] font-black uppercase">${c.statut === 'pending' ? 'En attente' : (c.statut === 'accepted' ? 'Accepté' : c.statut)}</span>
                            </div>
                        </div>
                    `,
            )
            .join("");
        } else {
          container.innerHTML =
            '<div class="text-center py-10"><p class="text-gray-400 italic">Aucune candidature récente à afficher.</p></div>';
        }
      })
      .catch((err) => {
        console.error("Erreur candidatures:", err);
        container.innerHTML =
          '<div class="text-center py-10 text-red-500">Erreur lors du chargement.</div>';
      });
  }
});
