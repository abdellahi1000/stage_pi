/* js/admin_company_dashboard.js */
document.addEventListener("DOMContentLoaded", () => {
  loadDashboardData();

  function loadDashboardData() {
    fetch("../api/admin_company_dashboard.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          updateStats(data.stats);
          renderChart(data.chart_data);
          renderRecentApplications(data.recent_apps);
        }
      })
      .catch((err) => console.error("Error loading dashboard data:", err));
  }

  function updateStats(stats) {
    if (document.getElementById("stat-total-offers")) document.getElementById("stat-total-offers").textContent = stats.total_offers || 0;
    if (document.getElementById("stat-total-apps")) document.getElementById("stat-total-apps").textContent = stats.total_apps || 0;
    if (document.getElementById("stat-accepted-stagiaires")) document.getElementById("stat-accepted-stagiaires").textContent = stats.accepted_stagiaires || 0;
    if (document.getElementById("stat-accepted-alternances")) document.getElementById("stat-accepted-alternances").textContent = stats.accepted_alternances || 0;
    if (document.getElementById("stat-rejected")) document.getElementById("stat-rejected").textContent = stats.rejected || 0;
  }

  function renderChart(chartData) {
    const ctx = document.getElementById("activityChart").getContext("2d");
    
    // Process data for the full year
    const appsData = [];
    const acceptedData = [];
    const labels = [];
    
    const now = new Date();
    const currentYear = now.getFullYear();
    
    // Generate labels for every day of the year
    for (let month = 0; month < 12; month++) {
      const daysInMonth = new Date(currentYear, month + 1, 0).getDate();
      for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${currentYear}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
        labels.push(dateStr);
        appsData.push(chartData.apps[dateStr] || 0);
        acceptedData.push(chartData.accepted[dateStr] || 0);
      }
    }

    const gradientApps = ctx.createLinearGradient(0, 0, 0, 400);
    gradientApps.addColorStop(0, "rgba(59, 130, 246, 0.4)");
    gradientApps.addColorStop(1, "rgba(59, 130, 246, 0)");

    const gradientAccepted = ctx.createLinearGradient(0, 0, 0, 400);
    gradientAccepted.addColorStop(0, "rgba(34, 197, 94, 0.4)");
    gradientAccepted.addColorStop(1, "rgba(34, 197, 94, 0)");

    new Chart(ctx, {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Candidatures",
            data: appsData,
            borderColor: "#3b82f6",
            backgroundColor: gradientApps,
            fill: true,
            tension: 0.4,
            pointRadius: 0,
            pointHitRadius: 10,
            borderWidth: 3,
          },
          {
            label: "Acceptations",
            data: acceptedData,
            borderColor: "#22c55e",
            backgroundColor: gradientAccepted,
            fill: true,
            tension: 0.4,
            pointRadius: 0,
            pointHitRadius: 10,
            borderWidth: 3,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          intersect: false,
          mode: "index",
        },
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            backgroundColor: "rgba(17, 24, 39, 0.9)",
            padding: 12,
            titleFont: { size: 14, weight: "bold" },
            bodyFont: { size: 13 },
            displayColors: true,
            callbacks: {
              title: function(context) {
                const date = new Date(context[0].label);
                return date.toLocaleDateString("fr-FR", { day: "numeric", month: "long", year: "numeric" });
              }
            }
          },
        },
        scales: {
          x: {
            grid: {
              display: false,
            },
            ticks: {
              maxRotation: 0,
              autoSkip: true,
              maxTicksLimit: 12,
              callback: function(val, index) {
                const date = new Date(this.getLabelForValue(val));
                if (date.getDate() === 1) {
                  return date.toLocaleDateString("fr-FR", { month: "short" });
                }
                return "";
              },
              color: "#9ca3af",
              font: {
                weight: "bold",
                size: 11,
              }
            },
          },
          y: {
            beginAtZero: true,
            grid: {
              color: "rgba(243, 244, 246, 1)",
            },
            ticks: {
              stepSize: 1,
              color: "#9ca3af",
              font: {
                weight: "bold",
                size: 11,
              }
            },
          },
        },
        animations: {
          y: {
            easing: "easeInOutElastic",
            duration: 1500,
            from: (ctx) => (ctx.index === 0 ? 0 : ctx.chart.scales.y.getPixelForValue(100)),
          },
        },
      },
    });
  }

  function renderRecentApplications(apps) {
    const container = document.getElementById("recent-applications");
    if (!apps || apps.length === 0) {
      container.innerHTML = '<div class="text-center py-10"><p class="text-gray-400 italic">Aucune candidature récente à afficher.</p></div>';
      return;
    }

    container.innerHTML = apps
      .map(
        (app) => `
          <div class="flex flex-col md:flex-row items-start md:items-center justify-between p-6 bg-gray-50 rounded-2xl border border-gray-100 group hover:border-blue-200 transition-all gap-4">
              <div class="flex items-center gap-4">
                  <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-blue-600 font-bold border border-gray-100">
                      ${app.prenom ? app.prenom[0] : ""}${app.nom ? app.nom[0] : ""}
                  </div>
                  <div>
                      <h4 class="font-bold text-gray-900">${app.prenom} ${app.nom}</h4>
                      <p class="text-sm text-gray-500">Poste : <span class="font-medium text-blue-600">${app.offre_titre}</span></p>
                  </div>
              </div>
              <div class="flex flex-row md:flex-col items-center md:items-end justify-between w-full md:w-auto gap-2">
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em]">${new Date(app.date_candidature).toLocaleDateString()}</p>
                  <span class="px-4 py-1.5 ${getStatusClass(app.statut)} rounded-full text-[10px] font-black uppercase tracking-wider">
                    ${getStatusLabel(app.statut)}
                  </span>
              </div>
          </div>
        `
      )
      .join("");
  }

  function getStatusClass(status) {
    switch (status) {
      case "pending": return "bg-blue-100 text-blue-700";
      case "accepted": return "bg-green-100 text-green-700";
      case "rejected": return "bg-red-100 text-red-700";
      case "closed": return "bg-gray-100 text-gray-700";
      default: return "bg-gray-100 text-gray-700";
    }
  }

  function getStatusLabel(status) {
    switch (status) {
      case "pending": return "En Attente";
      case "accepted": return "Accepté";
      case "rejected": return "Refusé";
      case "closed": return "Clôturé";
      default: return status;
    }
  }
});
