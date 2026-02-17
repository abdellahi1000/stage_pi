/* js/createCV.js */

function previewPhoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("cv-photo").src = e.target.result;
      document.getElementById("photoPreview").innerHTML =
        `<img src="${e.target.result}" class="w-full h-full object-cover">`;
      updatePreview();
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function adjustPreviewScale() {
  const container = document.getElementById("cv-container");
  const preview = document.getElementById("cv-preview");
  if (!container || !preview) return;

  // We want the CV to fit within the container's width
  const containerWidth = container.clientWidth - 48; // Account for padding
  const previewWidth = 794; // A4 width at 96 DPI

  const scale = containerWidth / previewWidth;
  const finalScale = Math.min(scale, 1);

  // Apply scaling
  preview.style.transform = `scale(${finalScale})`;

  // Center it vertically if there's space, or ensure it's at the top
  preview.style.transformOrigin = "top center";
}

function updatePreview() {
  // Update textual data
  document.getElementById("out-nom").textContent =
    document.getElementById("nom").value || "VOTRE NOM";
  document.getElementById("out-poste").textContent =
    document.getElementById("poste").value || "VOTRE TITRE";
  document.getElementById("out-tel").textContent =
    document.getElementById("tel").value || "+123-456-7890";
  document.getElementById("out-email").textContent =
    document.getElementById("email").value || "email@example.com";
  document.getElementById("out-ville").textContent =
    document.getElementById("ville").value || "Ville";
  document.getElementById("out-about").textContent =
    document.getElementById("about").value || "Description de votre profil...";

  // Education
  const eduRows = document.getElementById("education").value.split("\n");
  const eduContainer = document.getElementById("out-education");
  eduContainer.innerHTML = "";
  eduRows.forEach((row) => {
    if (row.trim()) {
      const parts = row.split("|");
      const item = document.createElement("div");
      item.className = "cv-item";
      item.innerHTML = `
                <h3>${parts[0] || ""}</h3>
                <p class="cv-subtitle">${parts[1] || ""}</p>
                <p class="cv-date">${parts[2] || ""}</p>
            `;
      eduContainer.appendChild(item);
    }
  });

  // Skills
  const skillsList = document.getElementById("skills").value.split("\n");
  const skillsContainer = document.getElementById("out-skills");
  skillsContainer.innerHTML = "";
  skillsList.forEach((skill) => {
    if (skill.trim()) {
      const li = document.createElement("li");
      li.textContent = skill.trim();
      skillsContainer.appendChild(li);
    }
  });

  // Languages
  const langList = document.getElementById("languages").value.split(",");
  const langContainer = document.getElementById("out-languages");
  langContainer.innerHTML = "";
  langList.forEach((lang) => {
    if (lang.trim()) {
      const li = document.createElement("li");
      li.textContent = lang.trim();
      langContainer.appendChild(li);
    }
  });

  // Experience
  const expRows = document.getElementById("experiences").value.split("\n");
  const expContainer = document.getElementById("out-experience");
  expContainer.innerHTML = "";
  expRows.forEach((row) => {
    if (row.trim()) {
      const parts = row.split("|");
      const item = document.createElement("div");
      item.className = "cv-item";
      item.innerHTML = `
                <p class="cv-date">${parts[2] || ""}</p>
                <p class="cv-subtitle">${parts[1] || ""}</p>
                <h3>${parts[0] || ""}</h3>
                <p class="cv-text mt-2">${parts[3] || ""}</p>
            `;
      expContainer.appendChild(item);
    }
  });

  // References
  const refList = document.getElementById("references").value.split(",");
  const refContainer = document.getElementById("out-references");
  refContainer.innerHTML = "";
  refList.forEach((ref) => {
    if (ref.trim()) {
      const parts = ref.split("|");
      const item = document.createElement("div");
      item.className = "reference-item";
      item.innerHTML = `
                <h4>${parts[0] || ""}</h4>
                <p>${parts[1] || ""}</p>
                <p class="text-gray-500 text-xs">${parts[2] || ""}</p>
            `;
      refContainer.appendChild(item);
    }
  });

  adjustPreviewScale();
}

async function telechargerPDF() {
  const preview = document.getElementById("cv-preview");

  // Save original styles to restore later
  const originalStyles = {
    transform: preview.style.transform,
    transformOrigin: preview.style.transformOrigin,
    position: preview.style.position,
    left: preview.style.left,
    top: preview.style.top,
    zIndex: preview.style.zIndex,
    margin: preview.style.margin,
    width: preview.style.width,
    height: preview.style.height,
  };

  // Apply "Print Mode" styles directly to the live element
  // Reset scaling and ensure it's natural size
  preview.style.transform = "none";
  preview.style.transformOrigin = "top left";
  preview.style.margin = "0 auto";
  preview.style.width = "210mm";
  preview.style.height = "296.5mm";
  preview.style.overflow = "hidden"; // Cut off any potential overflow which causes new page

  // Create options for html2pdf
  const opt = {
    margin: 0,
    filename: "mon-cv.pdf",
    image: { type: "jpeg", quality: 0.98 },
    html2canvas: {
      scale: 2,
      useCORS: true,
      letterRendering: true,
      scrollY: 0,
    },
    jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
  };

  try {
    // Scroll to top to help html2canvas capture everything
    window.scrollTo(0, 0);

    // Generate PDF from the LIVE element
    await html2pdf().set(opt).from(preview).save();
  } catch (error) {
    console.error("Erreur PDF:", error);
    alert("Une erreur est survenue lors de la génération du PDF.");
  } finally {
    // Restore original styles
    preview.style.transform = originalStyles.transform;
    preview.style.transformOrigin = originalStyles.transformOrigin;
    preview.style.position = originalStyles.position;
    preview.style.left = originalStyles.left;
    preview.style.top = originalStyles.top;
    preview.style.zIndex = originalStyles.zIndex;
    preview.style.margin = originalStyles.margin;
    preview.style.width = originalStyles.width;
    preview.style.height = originalStyles.height;

    // Trigger re-scale just in case
    adjustPreviewScale();
  }
}

// Global initialization
document.addEventListener("DOMContentLoaded", () => {
  updatePreview();

  const inputs = document.querySelectorAll("input, textarea");
  inputs.forEach((input) => {
    input.addEventListener("input", updatePreview);
  });

  window.addEventListener("resize", adjustPreviewScale);
  window.addEventListener("load", adjustPreviewScale);
  if (document.fonts) {
    document.fonts.ready.then(adjustPreviewScale);
  }
});
