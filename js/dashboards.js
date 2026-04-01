/* js/dashboards.js */
document.addEventListener("DOMContentLoaded", () => {
  // Sidebar toggle (already in global.js, but let's ensure it's here if needed)
  // Actually global.js handles it if IDs match.

  // Load Stats if elements exist
  const statCandidatures = document.getElementById("stat-candidatures");
  const statFavorites = document.getElementById("stat-favorites");
  const statMessages = document.getElementById("stat-messages");
  const statOffres = document.getElementById("stat-offres");

  function loadStats() {
    if (statCandidatures || statOffres) {
      fetch("../api/user.php")
        .then((response) => response.json())
        .then((data) => {
          if (data.success && data.statistiques) {
            if (statCandidatures)
              statCandidatures.textContent =
                data.statistiques.total_candidatures || 0;
            if (statOffres)
              statOffres.textContent = data.statistiques.total_offres || 0;

            const count = data.statistiques.total_messages || 0;
            if (statMessages) statMessages.textContent = count;

            const notifDot = document.getElementById("notifDot");
            if (notifDot) {
              if (count > 0) notifDot.classList.remove("hidden");
              else notifDot.classList.add("hidden");
            }

            if (statFavorites)
              statFavorites.textContent = data.statistiques.total_favorites || 0;
          }
        })
        .catch((err) => console.error("Erreur chargement stats:", err));
    }
  }

  loadStats();
  setInterval(loadStats, 30000); // 30 seconds refresh
});
