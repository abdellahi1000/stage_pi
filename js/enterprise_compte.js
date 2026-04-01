document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btnExportData');
  if (!btn) return;

  btn.addEventListener('click', () => {
    openExportModal();
  });
});

function openExportModal() {
  // Prevent duplicate modals
  if (document.getElementById('smExportOverlay')) return;

  // Overlay
  const overlay = document.createElement('div');
  overlay.id = 'smExportOverlay';
  overlay.className =
    'fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[9999]';

  // Modal container
  const modal = document.createElement('div');
  modal.className =
    'bg-white max-w-sm w-full mx-4 rounded-2xl shadow-2xl border border-gray-100';

  modal.innerHTML = `
    <div class="px-6 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-black text-gray-900 flex items-center gap-2">
          <span class="inline-flex w-8 h-8 rounded-xl bg-blue-50 text-blue-600 items-center justify-center">
            <i class="fas fa-file-export"></i>
          </span>
          Exporter les données
        </h2>
        <p class="text-xs text-gray-500 mt-1">
          Choisissez le format du rapport de recrutement.
        </p>
      </div>
      <button type="button" id="smExportClose"
        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
        <i class="fas fa-times text-sm"></i>
      </button>
    </div>
    <div class="px-6 pt-4 pb-2 space-y-3">
      <div class="text-xs font-bold text-gray-500 uppercase tracking-[0.18em] mb-1">
        Format d'export
      </div>
      <div class="grid grid-cols-1 gap-2" id="smExportFormatList">
        <button type="button" data-format="csv"
          class="sm-export-option w-full flex items-center justify-between px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 hover:bg-blue-50 hover:border-blue-400 transition text-sm font-medium text-gray-800">
          <span class="flex items-center gap-2">
            <span class="inline-flex w-7 h-7 rounded-lg bg-white border border-gray-200 items-center justify-center text-blue-600">
              <i class="fas fa-file-csv"></i>
            </span>
            <span>
              CSV
              <span class="block text-[11px] text-gray-500 font-normal">Compatible avec Excel &amp; Google Sheets</span>
            </span>
          </span>
          <span class="text-[11px] font-black uppercase tracking-[0.18em] text-blue-500">Recommandé</span>
        </button>
        <button type="button" data-format="excel"
          class="sm-export-option w-full flex items-center justify-between px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 hover:bg-emerald-50 hover:border-emerald-400 transition text-sm font-medium text-gray-800">
          <span class="flex items-center gap-2">
            <span class="inline-flex w-7 h-7 rounded-lg bg-white border border-gray-200 items-center justify-center text-emerald-600">
              <i class="fas fa-file-excel"></i>
            </span>
            <span>
              Excel
              <span class="block text-[11px] text-gray-500 font-normal">Ouverture directe dans Microsoft Excel</span>
            </span>
          </span>
        </button>
        <button type="button" data-format="pdf"
          class="sm-export-option w-full flex items-center justify-between px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 hover:bg-red-50 hover:border-red-400 transition text-sm font-medium text-gray-800">
          <span class="flex items-center gap-2">
            <span class="inline-flex w-7 h-7 rounded-lg bg-white border border-gray-200 items-center justify-center text-red-600">
              <i class="fas fa-file-pdf"></i>
            </span>
            <span>
              PDF
              <span class="block text-[11px] text-gray-500 font-normal">Mise en page imprimable avec groupes séparés</span>
            </span>
          </span>
        </button>
      </div>
    </div>
    <div class="px-6 pt-2 pb-5 flex items-center justify-between border-t border-gray-100">
      <p class="text-[11px] text-gray-400">
        Un instantané complet sera aussi archivé dans la base de données.
      </p>
      <div class="flex items-center gap-2">
        <button type="button" id="smExportCancel"
          class="px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition">
          Annuler
        </button>
      </div>
    </div>
  `;

  overlay.appendChild(modal);
  document.body.appendChild(overlay);
  document.body.style.overflow = 'hidden';

  const close = () => {
    document.body.style.overflow = '';
    overlay.remove();
  };

  document.getElementById('smExportClose')?.addEventListener('click', close);
  document.getElementById('smExportCancel')?.addEventListener('click', close);

  // Disable click-through on background by not closing on overlay click.

  // Attach handlers to options
  const options = modal.querySelectorAll('.sm-export-option');
  options.forEach((btn) => {
    btn.addEventListener('click', () => {
      const format = btn.getAttribute('data-format');
      if (!format) return;
      const f = format.toLowerCase().trim();
      if (!['csv', 'excel', 'pdf'].includes(f)) return;
      close();
      window.location.href = `../api/export_recrutement.php?format=${encodeURIComponent(f)}`;
    });
  });
}


// Save Profile
function saveProfile() {
    const btn = document.querySelector('#formProfile button[type="button"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enregistrement...';
    btn.disabled = true;

    const data = {
        action: 'update_profile_enterprise',
        nom: document.getElementById('inp_nom').value,
        industry: document.getElementById('inp_industry').value,
        size: document.getElementById('inp_size').value,
        website: document.getElementById('inp_website').value,
        bio: document.getElementById('inp_bio').value,
        address: document.getElementById('inp_address').value,
        hr_manager: document.getElementById('inp_hr').value
    };

    fetch('../api/user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            if (typeof showMessage === 'function') {
                showMessage("Profil mis à jour avec succès !", "success");
            } else {
                alert("Profil mis à jour avec succès !");
            }
            setTimeout(() => window.location.reload(), 1500);
        } else {
            alert(res.message || "Erreur lors de la mise à jour.");
        }
    })
    .catch(err => {
        console.error(err);
        alert("Erreur serveur.");
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Logo Upload
document.addEventListener('DOMContentLoaded', () => {
    const logoInput = document.getElementById('company_logo_input');
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const formData = new FormData();
                formData.append('photo', e.target.files[0]);
                formData.append('action', 'upload_logo');

                fetch('../include/upload_photo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('formProfileImg').src = data.photo_url;
                        if (typeof showMessage === 'function') {
                            showMessage("Logo mis à jour !", "success");
                        } else {
                            alert("Logo mis à jour !");
                        }
                    } else {
                        alert(data.message || "Erreur d'upload.");
                    }
                })
                .catch(err => console.error(err));
            }
        });
    }
});
