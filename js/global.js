/* js/global.js */

/**
 * Common Utility Functions
 */

// Initialiser le Mode Sombre au plus vite (server theme has priority after login)
(function () {
  const bodyDark = document.body.classList.contains("theme-dark");
  const bodyLight = document.body.classList.contains("theme-light");

  if (bodyLight) {
    localStorage.setItem("theme", "light");
    document.documentElement.classList.remove("dark-mode");
  } else if (bodyDark) {
    localStorage.setItem("theme", "dark");
    document.documentElement.classList.add("dark-mode");
  } else {
    if (localStorage.getItem("theme") === "dark") {
      document.documentElement.classList.add("dark-mode");
    }
  }
})();

// Escape HTML to prevent XSS
function esc(str) {
  if (!str) return "";
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// Show Alert Message
function showMessage(message, type) {
  const msgDiv = document.createElement("div");
  msgDiv.className = `alert-message alert-${type}`;
  msgDiv.textContent = message;
  document.body.appendChild(msgDiv);

  setTimeout(() => {
    msgDiv.style.animation = "slideUp 0.3s ease forwards";
    setTimeout(() => msgDiv.remove(), 300);
  }, 3000);
}

/**
 * Common Header/Sidebar interactions
 */
document.addEventListener("DOMContentLoaded", () => {
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebarClose = document.getElementById("sidebarClose");
  const sidebarOverlay = document.getElementById("sidebarOverlay");
  const sidebar = document.getElementById("sidebar");

  function toggleSidebar(forceClose = false) {
    if (sidebar) {
      if (forceClose) {
        sidebar.classList.remove("active");
      } else {
        sidebar.classList.toggle("active");
      }

      const active = sidebar.classList.contains("active");
      if (sidebarOverlay) {
        if (active) {
          sidebarOverlay.classList.remove("hidden");
          document.body.style.overflow = "hidden"; // Prevent background scroll
        } else {
          sidebarOverlay.classList.add("hidden");
          document.body.style.overflow = "auto"; // Restore scroll
        }
      }
    }
  }

  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", () => toggleSidebar());
  }
  if (sidebarClose) {
    sidebarClose.addEventListener("click", () => toggleSidebar());
  }
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", () => toggleSidebar(true));
  }

  // Close sidebar automatically when clicking a link (mobile feature)
  if (sidebar) {
    const sidebarLinks = sidebar.querySelectorAll('nav a');
    sidebarLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
          toggleSidebar(true);
        }
      });
    });
  }

  // Configuration du Mode Sombre (persisté en BDD, rechargement pour appliquer)
  const darkModeToggle = document.getElementById("darkModeToggle");
  const darkModeIcon = document.getElementById("darkModeIcon");
  const isDarkNow = document.body.classList.contains("theme-dark") || document.documentElement.classList.contains("dark-mode");

  if (darkModeIcon && darkModeToggle) {
    if (isDarkNow) {
      darkModeIcon.classList.replace("fa-moon", "fa-sun");
      darkModeToggle.querySelector("span").textContent = "Mode Clair";
    } else {
      darkModeIcon.classList.replace("fa-sun", "fa-moon");
      darkModeToggle.querySelector("span").textContent = "Mode Sombre";
    }
  }

  if (darkModeToggle) {
    darkModeToggle.addEventListener("click", () => {
      const currentlyDark = document.body.classList.contains("theme-dark") || document.documentElement.classList.contains("dark-mode");
      const newTheme = currentlyDark ? "light" : "dark";

      const form = new URLSearchParams({ action: "update_theme", theme: newTheme });
      fetch("../include/update_preference.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: form,
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            localStorage.setItem("theme", newTheme);
            window.location.reload();
          }
        })
        .catch(() => {
          localStorage.setItem("theme", newTheme);
          window.location.reload();
        });
    });
  }

  // --- Reusable Custom Dropdown Component ---
  setupCustomDropdowns();
});

/**
 * Custom Dropdown Global Implementation
 */
function setupCustomDropdowns() {
  document.querySelectorAll(".custom-dropdown").forEach((dropdown) => {
    const button = dropdown.querySelector("button");
    const menu = dropdown.querySelector(".dropdown-menu");
    const label = button.querySelector("span");
    const icon = button.querySelector(".fa-chevron-down");
    const hiddenInput = dropdown.querySelector('input[type="hidden"]');
    
    // Initial State Check
    let initialItem = null;
    if (hiddenInput && hiddenInput.value) {
        initialItem = menu.querySelector(`.dropdown-item[data-value="${hiddenInput.value}"]`);
    } else {
        // Fallback to matching label text
        const currentText = label.textContent.trim();
        initialItem = Array.from(menu.querySelectorAll('.dropdown-item')).find(item => item.textContent.trim() === currentText);
    }

    if (initialItem) {
        initialItem.classList.add('selected');
        if (initialItem.dataset.value !== "" && initialItem.dataset.value !== undefined) {
            button.classList.add('selected');
        }
    }

    button.addEventListener("click", (e) => {
      e.stopPropagation();
      // Close other dropdowns
      document.querySelectorAll(".dropdown-menu").forEach((m) => {
        if (m !== menu) m.classList.remove("active");
      });
      document.querySelectorAll(".fa-chevron-down").forEach((i) => {
        if (i && i !== icon) i.classList.remove("rotate-180");
      });

      menu.classList.toggle("active");
      if (icon) icon.classList.toggle("rotate-180");
    });

    menu.querySelectorAll(".dropdown-item").forEach((item) => {
      item.addEventListener("click", () => {
        const value = item.dataset.value;
        const text = item.textContent;

        label.textContent = text;
        
        // Handle visual selection
        menu.querySelectorAll(".dropdown-item").forEach(di => di.classList.remove('selected'));
        item.classList.add('selected');
        
        // Mark button as selected if not empty
        if (value !== "") {
            button.classList.add('selected');
        } else {
            button.classList.remove('selected');
        }

        menu.classList.remove("active");
        if (icon) icon.classList.remove("rotate-180");

        if (hiddenInput) {
            hiddenInput.value = value;
            // Trigger change event manually for hidden input
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Custom Event for pages to listen to
        dropdown.dispatchEvent(new CustomEvent('change', { 
            detail: { value: value, text: text } 
        }));
      });
    });
  });

  // Close all dropdowns on outside click
  window.addEventListener("click", () => {
    document.querySelectorAll(".dropdown-menu").forEach((m) => m.classList.remove("active"));
    document.querySelectorAll(".fa-chevron-down").forEach((i) => i.classList.remove("rotate-180"));
  });
}

/**
 * Helper to update dropdown programmatically
 */
function updateDropdown(dropdownId, value) {
    const d = document.getElementById(dropdownId);
    if (!d) return;
    const item = d.querySelector(`.dropdown-item[data-value="${value}"]`);
    if (item) item.click();
}

