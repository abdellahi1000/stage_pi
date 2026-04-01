/* js/createCV.js */

let activeTab = 'cv';
let saveTimeout;

/** 
 * TAB SYSTEM 
 */
function switchTab(tab) {
  console.log("Switching Tab to:", tab);
  activeTab = tab;

  const el = (id) => document.getElementById(id);
  const elements = {
    cvForm: el('cv-form-container'),
    motForm: el('motivation-form-container'),
    cvPreview: el('cv-container'),
    motPreview: el('motivation-container'),
    tabCV: el('tabCV'),
    tabMot: el('tabMotivation'),
    btnCV: el('btnExportCV'),
    btnMot: el('btnExportMotivation')
  };

  if (tab === 'cv') {
    if (elements.tabCV) elements.tabCV.className = "px-6 py-2 rounded-lg font-bold transition-all bg-blue-600 text-white shadow-lg";
    if (elements.tabMot) elements.tabMot.className = "px-6 py-2 rounded-lg font-bold transition-all bg-white text-gray-600 hover:bg-gray-100 border border-gray-200";

    if (elements.cvForm) elements.cvForm.classList.remove('hidden');
    if (elements.motForm) elements.motForm.classList.add('hidden');

    if (elements.cvPreview) {
      elements.cvPreview.classList.remove('hidden');
      elements.cvPreview.style.setProperty('display', 'flex', 'important');
    }
    if (elements.motPreview) {
      elements.motPreview.classList.add('hidden');
      elements.motPreview.style.setProperty('display', 'none', 'important');
    }

    if (elements.btnCV) elements.btnCV.classList.remove('hidden');
    if (elements.btnMot) elements.btnMot.classList.add('hidden');
  } else {
    if (elements.tabMot) elements.tabMot.className = "px-6 py-2 rounded-lg font-bold transition-all bg-blue-600 text-white shadow-lg";
    if (elements.tabCV) elements.tabCV.className = "px-6 py-2 rounded-lg font-bold transition-all bg-white text-gray-600 hover:bg-gray-100 border border-gray-200";

    if (elements.cvForm) elements.cvForm.classList.add('hidden');
    if (elements.motForm) elements.motForm.classList.remove('hidden');

    if (elements.cvPreview) {
      elements.cvPreview.classList.add('hidden');
      elements.cvPreview.style.setProperty('display', 'none', 'important');
    }
    if (elements.motPreview) {
      elements.motPreview.classList.remove('hidden');
      elements.motPreview.style.setProperty('display', 'flex', 'important');
    }

    if (elements.btnCV) elements.btnCV.classList.add('hidden');
    if (elements.btnMot) elements.btnMot.classList.remove('hidden');
  }

  setTimeout(adjustPreviewScale, 100);
}

// Make it absolutely global
window.switchTab = switchTab;

/** 
 * DYNAMIC SCALING 
 */
function adjustPreviewScale() {
  const parent = document.querySelector('.lg\\:col-span-7');
  if (!parent) return;

  const isMobile = window.innerWidth < 1024;

  const availWidth = parent.clientWidth - (isMobile ? 20 : 40);
  const availHeight = parent.clientHeight - 40;

  const cvPreview = document.getElementById("cv-preview");
  const motPreview = document.getElementById("motivation-preview");

  // Standard A4 dimensions at 96 DPI
  const a4Width = 794;
  const a4Height = 1123;

  const scaleX = availWidth / a4Width;
  const scaleY = availHeight / a4Height;

  // For mobile, scale only by width so height can organically scroll
  const finalScale = isMobile ? scaleX : Math.min(scaleX, scaleY);

  // Update parent logic for mobile scroll
  if (isMobile) {
    parent.classList.remove('overflow-hidden', 'items-center');
    parent.classList.add('overflow-y-auto', 'items-start');
    parent.style.alignItems = 'flex-start';
  } else {
    parent.classList.add('overflow-hidden', 'items-center');
    parent.classList.remove('overflow-y-auto', 'items-start');
    parent.style.alignItems = 'center';
  }

  const applyScale = (el) => {
    if (!el) return;
    el.style.transform = `scale(${finalScale})`;
    el.style.transformOrigin = "top center";

    // Adjust bottom margin to fix empty scroll space created by scaled element
    if (isMobile) {
      const h = Math.max(a4Height, el.scrollHeight);
      el.style.marginBottom = `-${h * (1 - finalScale)}px`;
    } else {
      el.style.marginBottom = "0px";
    }
  };

  applyScale(cvPreview);
  applyScale(motPreview);
}

/** 
 * DATA LOADING & SAVING 
 */
async function loadData() {
  try {
    // CV Data
    const cvRes = await fetch('../api/cv.php');
    const cvData = await cvRes.json();

    if (cvData.success && cvData.cv) {
      const cv = cvData.cv;
      if (document.getElementById('nom')) document.getElementById('nom').value = cv.nom || '';
      if (document.getElementById('poste')) document.getElementById('poste').value = cv.poste || '';
      if (document.getElementById('tel')) document.getElementById('tel').value = cv.tel || '';
      if (document.getElementById('ville')) document.getElementById('ville').value = cv.ville || '';
      if (document.getElementById('email')) document.getElementById('email').value = cv.email || '';
      if (document.getElementById('about')) document.getElementById('about').value = cv.about || '';

      if (Array.isArray(cv.experiences)) {
        document.getElementById('experiences').value = cv.experiences.map(e => `${e.poste}|${e.entreprise}|${e.periode}|${e.description}`).join('\n');
      }
      if (Array.isArray(cv.formations)) {
        document.getElementById('education').value = cv.formations.map(f => `${f.diplome}|${f.etablissement}|${f.annee}`).join('\n');
      }
      if (Array.isArray(cv.competences)) {
        document.getElementById('skills').value = cv.competences.join('\n');
      }
      if (document.getElementById('languages')) document.getElementById('languages').value = cv.languages || '';
      if (document.getElementById('references')) document.getElementById('references').value = cv.references_data || '';
      if (document.getElementById('certifications')) document.getElementById('certifications').value = cv.certifications || '';
      if (document.getElementById('projects')) document.getElementById('projects').value = cv.projects || '';
      if (document.getElementById('website')) document.getElementById('website').value = cv.website || '';
      if (document.getElementById('linkedin')) document.getElementById('linkedin').value = cv.linkedin || '';
      if (document.getElementById('philosophy')) document.getElementById('philosophy').value = cv.philosophy || '';
      if (document.getElementById('strengths')) document.getElementById('strengths').value = cv.strengths || '';
      if (document.getElementById('skills_extra')) document.getElementById('skills_extra').value = cv.skills_extra || '';
      if (document.getElementById('tools')) document.getElementById('tools').value = cv.tools || '';
      if (document.getElementById('achievements')) document.getElementById('achievements').value = cv.achievements || '';
      if (document.getElementById('volunteer')) document.getElementById('volunteer').value = cv.volunteer || '';
      if (document.getElementById('workshops')) document.getElementById('workshops').value = cv.workshops || '';
      if (document.getElementById('interests')) document.getElementById('interests').value = cv.interests || '';
      if (document.getElementById('extra_info')) document.getElementById('extra_info').value = cv.extra_info || '';

      if (cv.photo_base64) {
        const img = document.getElementById("cv-photo");
        if (img) img.src = cv.photo_base64;
        const prev = document.getElementById("photoPreview");
        if (prev) prev.innerHTML = `<img src="${cv.photo_base64}" class="w-full h-full object-cover">`;
      }
    }

    // Motivation Data
    const motRes = await fetch('../api/motivation.php');
    const motData = await motRes.json();
    if (motData.success && motData.motivation) {
      const m = motData.motivation;
      if (document.getElementById('mot-adresse')) document.getElementById('mot-adresse').value = m.adresse_complet || '';
      if (document.getElementById('mot-ville')) document.getElementById('mot-ville').value = m.ville || '';
      if (document.getElementById('mot-date')) document.getElementById('mot-date').value = m.date_lettre || '';
      if (document.getElementById('mot-service-rh')) document.getElementById('mot-service-rh').value = m.service_rh || 'À l’attention du Service des Ressources Humaines';
      if (document.getElementById('mot-entreprise')) document.getElementById('mot-entreprise').value = m.entreprise || '';
      if (document.getElementById('mot-email-entreprise')) document.getElementById('mot-email-entreprise').value = m.email_entreprise || '';
      if (document.getElementById('mot-adresse-ent')) document.getElementById('mot-adresse-ent').value = m.adresse_entreprise || '';
      if (document.getElementById('mot-objet')) document.getElementById('mot-objet').value = m.objet || '';
      if (document.getElementById('mot-civilite')) document.getElementById('mot-civilite').value = m.civilite || 'Madame, Monsieur,';
      if (document.getElementById('mot-message')) document.getElementById('mot-message').value = m.message || '';
      if (document.getElementById('mot-cloture')) document.getElementById('mot-cloture').value = m.cloture || '';

      if (m.signature_base64) {
        document.getElementById('signature-data').value = m.signature_base64;
        const sigImg = document.getElementById('out-mot-signature');
        if (sigImg) {
          sigImg.src = m.signature_base64;
          sigImg.style.display = 'block';
        }
      }
    }

    updatePreviews();
  } catch (e) {
    console.error("Load error:", e);
  }
}

function saveData() {
  clearTimeout(saveTimeout);
  saveTimeout = setTimeout(async () => {
    const cvData = new FormData();
    cvData.append('nom_complet', document.getElementById('nom')?.value || '');
    cvData.append('poste_souhaite', document.getElementById('poste')?.value || '');
    cvData.append('telephone', document.getElementById('tel')?.value || '');
    cvData.append('email', document.getElementById('email')?.value || '');
    cvData.append('ville', document.getElementById('ville')?.value || '');
    cvData.append('resume_professionnel', document.getElementById('about')?.value || '');
    cvData.append('experiences', document.getElementById('experiences')?.value || '');
    cvData.append('formations', document.getElementById('education')?.value || '');
    cvData.append('competences', document.getElementById('skills')?.value || '');
    cvData.append('languages', document.getElementById('languages')?.value || '');
    cvData.append('references_data', document.getElementById('references')?.value || '');
    cvData.append('certifications', document.getElementById('certifications')?.value || '');
    cvData.append('projects', document.getElementById('projects')?.value || '');
    cvData.append('website', document.getElementById('website')?.value || '');
    cvData.append('linkedin', document.getElementById('linkedin')?.value || '');
    cvData.append('philosophy', document.getElementById('philosophy')?.value || '');
    cvData.append('strengths', document.getElementById('strengths')?.value || '');
    cvData.append('skills_extra', document.getElementById('skills_extra')?.value || '');
    cvData.append('tools', document.getElementById('tools')?.value || '');
    cvData.append('achievements', document.getElementById('achievements')?.value || '');
    cvData.append('volunteer', document.getElementById('volunteer')?.value || '');
    cvData.append('workshops', document.getElementById('workshops')?.value || '');
    cvData.append('interests', document.getElementById('interests')?.value || '');
    cvData.append('extra_info', document.getElementById('extra_info')?.value || '');

    const photo = document.getElementById('cv-photo')?.src;
    if (photo && photo.startsWith('data:image')) {
      cvData.append('photo_base64', photo);
    }

    const motData = new FormData();
    motData.append('nom_complet', document.getElementById('nom')?.value || '');
    motData.append('telephone', document.getElementById('tel')?.value || '');
    motData.append('email', document.getElementById('email')?.value || '');
    motData.append('adresse_complet', document.getElementById('mot-adresse')?.value || '');
    motData.append('ville', document.getElementById('mot-ville')?.value || '');
    motData.append('date_lettre', document.getElementById('mot-date')?.value || '');
    motData.append('service_rh', document.getElementById('mot-service-rh')?.value || 'À l’attention du Service des Ressources Humaines');
    motData.append('entreprise', document.getElementById('mot-entreprise')?.value || '');
    motData.append('email_entreprise', document.getElementById('mot-email-entreprise')?.value || '');
    motData.append('adresse_entreprise', document.getElementById('mot-adresse-ent')?.value || '');
    motData.append('objet', document.getElementById('mot-objet')?.value || '');
    motData.append('civilite', document.getElementById('mot-civilite')?.value || 'Madame, Monsieur,');
    motData.append('message', document.getElementById('mot-message')?.value || '');
    motData.append('cloture', document.getElementById('mot-cloture')?.value || '');
    motData.append('signature_base64', document.getElementById('signature-data')?.value || '');

    try {
      await Promise.all([
        fetch('../api/cv.php', { method: 'POST', body: cvData }),
        fetch('../api/motivation.php', { method: 'POST', body: motData })
      ]);
      updatePreviews();
    } catch (e) {
      console.error("Save error:", e);
    }
  }, 1500);
}

/** 
 * UI UPDATE 
 */
function updatePreviews() {
  try {
    const nom = document.getElementById("nom")?.value || "VOTRE NOM";
    const tel = document.getElementById("tel")?.value || "+222 00 00 00 00";
    const email = document.getElementById("email")?.value || "votre@email.com";
    const ville = document.getElementById("ville")?.value || "Nouakchott";

    // Update CV
    if (document.getElementById("out-nom")) document.getElementById("out-nom").textContent = nom;
    if (document.getElementById("out-poste")) document.getElementById("out-poste").textContent = document.getElementById("poste")?.value || "VOTRE TITRE";
    if (document.getElementById("out-tel")) document.getElementById("out-tel").textContent = tel;
    if (document.getElementById("out-email")) document.getElementById("out-email").textContent = email;
    if (document.getElementById("out-ville")) document.getElementById("out-ville").textContent = ville;
    if (document.getElementById("out-website")) document.getElementById("out-website").textContent = document.getElementById("website")?.value || "https://site.com";
    if (document.getElementById("out-linkedin")) document.getElementById("out-linkedin").textContent = document.getElementById("linkedin")?.value || "linkedin.com/in/nom";
    if (document.getElementById("out-about")) document.getElementById("out-about").textContent = document.getElementById("about")?.value || "Description...";
    if (document.getElementById("out-philosophy")) document.getElementById("out-philosophy").textContent = document.getElementById("philosophy")?.value || "Ma philosophie...";

    // NEW 7 SECTIONS
    if (document.getElementById("out-skills-extra")) document.getElementById("out-skills-extra").textContent = document.getElementById("skills_extra")?.value || "";
    if (document.getElementById("out-tools")) document.getElementById("out-tools").textContent = document.getElementById("tools")?.value || "";
    if (document.getElementById("out-achievements")) document.getElementById("out-achievements").textContent = document.getElementById("achievements")?.value || "";
    if (document.getElementById("out-volunteer")) document.getElementById("out-volunteer").textContent = document.getElementById("volunteer")?.value || "";
    if (document.getElementById("out-workshops")) document.getElementById("out-workshops").textContent = document.getElementById("workshops")?.value || "";
    if (document.getElementById("out-interests")) document.getElementById("out-interests").textContent = document.getElementById("interests")?.value || "";
    if (document.getElementById("out-extra-info")) document.getElementById("out-extra-info").textContent = document.getElementById("extra_info")?.value || "";

    // Update Motivation
    const motVille = document.getElementById("mot-ville")?.value || ville;
    const motDateStr = document.getElementById("mot-date")?.value || "";

    if (document.getElementById("out-mot-nom-header")) document.getElementById("out-mot-nom-header").textContent = nom;
    if (document.getElementById("out-mot-tel-header")) document.getElementById("out-mot-tel-header").textContent = tel;
    if (document.getElementById("out-mot-email-header")) document.getElementById("out-mot-email-header").textContent = email;
    if (document.getElementById("out-mot-adresse-header")) document.getElementById("out-mot-adresse-header").textContent = document.getElementById("mot-adresse")?.value || "";

    if (document.getElementById("out-mot-service-preview")) document.getElementById("out-mot-service-preview").textContent = document.getElementById("mot-service-rh")?.value || "À l’attention du Service des Ressources Humaines";
    if (document.getElementById("out-mot-entreprise-dest")) document.getElementById("out-mot-entreprise-dest").textContent = document.getElementById("mot-entreprise")?.value || "ENTREPRISE";
    if (document.getElementById("out-mot-email-ent-dest")) document.getElementById("out-mot-email-ent-dest").textContent = document.getElementById("mot-email-entreprise")?.value || "";
    if (document.getElementById("out-mot-adresse-ent-dest")) document.getElementById("out-mot-adresse-ent-dest").textContent = document.getElementById("mot-adresse-ent")?.value || "";

    if (document.getElementById("out-mot-date-full")) document.getElementById("out-mot-date-full").textContent = `À ${motVille}, le ${motDateStr}`;
    if (document.getElementById("out-mot-objet-title")) document.getElementById("out-mot-objet-title").textContent = document.getElementById("mot-objet")?.value || "OBJET";
    if (document.getElementById("out-mot-civilite-preview")) document.getElementById("out-mot-civilite-preview").textContent = document.getElementById("mot-civilite")?.value || "Madame, Monsieur,";
    if (document.getElementById("out-mot-message-preview")) document.getElementById("out-mot-message-preview").textContent = document.getElementById("mot-message")?.value || "Votre message...";
    if (document.getElementById("out-mot-cloture-preview")) document.getElementById("out-mot-cloture-preview").textContent = document.getElementById("mot-cloture")?.value || "";
    if (document.getElementById("out-mot-nom-footer")) document.getElementById("out-mot-nom-footer").textContent = nom;
    const sigData = document.getElementById('signature-data')?.value;
    const sigImg = document.getElementById('out-mot-signature');
    if (sigImg && sigData) {
      sigImg.src = sigData;
      sigImg.style.display = 'block';
    }

    // Complex lists
    renderList('education', 'out-education', 'cv-item', (p) => `<h3>${p[0] || ""}</h3><p class="cv-subtitle">${p[1] || ""}</p><p class="cv-date">${p[2] || ""}</p>`);
    renderList('experiences', 'out-experience', 'cv-item', (p) => `<div class="mb-4"><h3>${p[0] || ""}</h3><p class="cv-subtitle">${p[1] || ""} • ${p[2] || ""}</p><p class="cv-text">${p[3] || ""}</p></div>`);
    renderList('certifications', 'out-certifications', 'cv-item', (p) => `<h3>${p[0] || ""}</h3><p class="cv-subtitle">${p[1] || ""}</p><p class="cv-date">${p[2] || ""}</p>`);
    renderList('projects', 'out-projects', 'cv-item', (p) => `<h3>${p[0] || ""}</h3><p class="cv-text">${p[1] || ""}</p><p class="cv-date">${p[2] || ""}</p>`);

    const skillsList = document.getElementById("out-skills");
    if (skillsList) {
      skillsList.innerHTML = "";
      (document.getElementById("skills")?.value || "").split("\n").forEach(s => {
        if (s.trim()) {
          const li = document.createElement("li");
          li.textContent = s.trim();
          skillsList.appendChild(li);
        }
      });
    }

    const langList = document.getElementById("out-languages");
    if (langList) {
      langList.innerHTML = "";
      (document.getElementById("languages")?.value || "").split(",").forEach(l => {
        if (l.trim()) {
          const li = document.createElement("li");
          li.textContent = l.trim();
          langList.appendChild(li);
        }
      });
    }

    const refGrid = document.getElementById("out-references");
    if (refGrid) {
      refGrid.innerHTML = "";
      (document.getElementById("references")?.value || "").split(",").forEach(r => {
        if (r.trim()) {
          const p = r.split("|");
          const div = document.createElement("div");
          div.className = "reference-item";
          div.innerHTML = `<h4>${p[0] || ""}</h4><p>${p[1] || ""}</p><p class="text-gray-500 text-xs">${p[2] || ""}</p>`;
          refGrid.appendChild(div);
        }
      });
    }

    adjustPreviewScale();
  } catch (err) {
    console.warn("Preview update error:", err);
  }
}

function renderList(inputId, outputId, className, template) {
  const input = document.getElementById(inputId);
  const output = document.getElementById(outputId);
  if (!input || !output) return;
  output.innerHTML = "";
  input.value.split("\n").forEach(row => {
    if (row.trim()) {
      const div = document.createElement("div");
      div.className = className;
      div.innerHTML = template(row.split("|"));
      output.appendChild(div);
    }
  });
}

function previewPhoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = (e) => {
      const imgEl = document.getElementById("cv-photo");
      if (imgEl) imgEl.src = e.target.result;
      const prev = document.getElementById("photoPreview");
      if (prev) prev.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
      updatePreviews();
      saveData();
    };
    reader.readAsDataURL(input.files[0]);
  }
}

/** 
 * EXPORT 
 */
async function telechargerPDF() {
  await exportPDF("cv-preview", "mon-cv.pdf", true);
}
async function telechargerMotivationPDF() {
  await exportPDF("motivation-preview", "ma-motivation.pdf", false);
}

async function exportPDF(elId, name, isCV = false) {
  const el = document.getElementById(elId);
  if (!el) return;

  // Store original state
  const oldT = el.style.transform;
  const oldH = el.style.height;
  const oldOverflow = el.style.overflow;
  const oldShadow = el.style.boxShadow;

  // Normalize for 1:1 capture
  el.style.transform = "none";
  el.style.boxShadow = "none";

  if (isCV) {
    el.style.height = "auto";
    el.style.overflow = "visible";
  } else {
    el.style.height = "297mm";
    el.style.overflow = "hidden";
  }


  // High-precision options
  const opt = {
    margin: 0,
    filename: name,
    image: { type: 'jpeg', quality: 1 },
    html2canvas: {
      scale: 3,
      useCORS: true,
      letterRendering: true,
      logging: false,
      scrollY: 0,
      scrollX: 0
    },
    jsPDF: {
      unit: "mm",
      format: "a4",
      orientation: "portrait",
      precision: 32
    },
    pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
  };

  try {
    const worker = html2pdf().set(opt).from(el).toPdf().get('pdf');
    const pdf = await worker;

    if (isCV) {
      if (typeof pdf.setDisplayMode === 'function') {
        pdf.setDisplayMode(80); // 80% zoom
      }
    } else {
      // Safety check: remove empty pages for motivation
      const totalPages = pdf.internal.getNumberOfPages();
      if (totalPages > 1) {
        for (let i = totalPages; i > 1; i--) {
          pdf.deletePage(i);
        }
      }
    }

    await pdf.save();
  } catch (err) {
    console.error("PDF Export Error:", err);
  } finally {
    // Restore original state
    el.style.transform = oldT;
    el.style.boxShadow = oldShadow;
    el.style.height = oldH;
    el.style.overflow = oldOverflow;
  }
}

/** 
 * SIGNATURE PAD 
 */
let isDrawing = false;
let sigCtx;

function initSignature() {
  const canvas = document.getElementById('signature-pad');
  if (!canvas) return;

  sigCtx = canvas.getContext('2d');

  // Scale for resolution
  const ratio = window.devicePixelRatio || 1;
  canvas.width = canvas.offsetWidth * ratio;
  canvas.height = canvas.offsetHeight * ratio;
  sigCtx.scale(ratio, ratio);

  sigCtx.lineWidth = 2;
  sigCtx.lineJoin = 'round';
  sigCtx.lineCap = 'round';
  sigCtx.strokeStyle = '#000';

  const getPos = (e) => {
    const rect = canvas.getBoundingClientRect();
    return {
      x: (e.clientX || e.touches[0].clientX) - rect.left,
      y: (e.clientY || e.touches[0].clientY) - rect.top
    };
  };

  const start = (e) => {
    isDrawing = true;
    const pos = getPos(e);
    sigCtx.beginPath();
    sigCtx.moveTo(pos.x, pos.y);
    e.preventDefault();
  };

  const move = (e) => {
    if (!isDrawing) return;
    const pos = getPos(e);
    sigCtx.lineTo(pos.x, pos.y);
    sigCtx.stroke();
    e.preventDefault();
  };

  const stop = () => {
    if (isDrawing) {
      isDrawing = false;
      document.getElementById('signature-data').value = canvas.toDataURL();
      updatePreviews();
      saveData();
    }
  };

  canvas.addEventListener('mousedown', start);
  canvas.addEventListener('mousemove', move);
  window.addEventListener('mouseup', stop);

  canvas.addEventListener('touchstart', start);
  canvas.addEventListener('touchmove', move);
  canvas.addEventListener('touchend', stop);
}

function clearSignature() {
  const canvas = document.getElementById('signature-pad');
  if (!canvas) return;
  sigCtx.clearRect(0, 0, canvas.width, canvas.height);
  document.getElementById('signature-data').value = '';
  const outSig = document.getElementById('out-mot-signature');
  if (outSig) outSig.style.display = 'none';
  updatePreviews();
  saveData();
}

function uploadSignature(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = (e) => {
      document.getElementById('signature-data').value = e.target.result;
      const sigImg = document.getElementById('out-mot-signature');
      if (sigImg) {
        sigImg.src = e.target.result;
        sigImg.style.display = 'block';
      }
      updatePreviews();
      saveData();
    };
    reader.readAsDataURL(input.files[0]);
  }
}

/** 
 * INIT 
 */
document.addEventListener("DOMContentLoaded", () => {
  loadData();
  initSignature();

  document.querySelectorAll("input, textarea").forEach(input => {
    input.addEventListener("input", () => {
      updatePreviews();
      saveData();
    });
  });

  window.addEventListener("resize", adjustPreviewScale);
});
