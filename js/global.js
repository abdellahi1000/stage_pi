/* js/global.js */

/**
 * Common Utility Functions
 */

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
  const sidebar = document.getElementById("sidebar");

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", () => {
      sidebar.classList.toggle("hidden");
    });
  }
});
