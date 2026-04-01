/* js/admin_company_profile.js */
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formCompany");
    const logoInput = document.getElementById("companyLogoInput");
    const logoPreview = document.getElementById("companyLogoPreview");
    const hdrProfilePicSidebar = document.getElementById("sidebarProfilePhoto"); // The one in sidebar if exists

    loadCompanyData();

    function loadCompanyData() {
        fetch("../api/admin_company_profile.php")
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const c = data.company;
                    
                    const nameDisplay = document.getElementById("displayCompanyName");
                    const sloganDisplay = document.getElementById("displayCompanySlogan");
                    
                    if (nameDisplay) nameDisplay.textContent = c.nom || "Entreprise";
                    if (sloganDisplay) sloganDisplay.textContent = c.slogan || "";
                    
                    if (form) {
                        form.nom.value = c.nom || "";
                        form.slogan.value = c.slogan || "";
                        form.secteur.value = c.industry_sector || "";
                        form.taille.value = c.company_size || "";
                        form.site_web.value = c.website_url || "";
                        form.siege.value = c.siege || "";
                        form.description.value = c.bio || "";
                    }

                    if (c.photo_profil && logoPreview) {
                        logoPreview.src = `../${c.photo_profil}?v=${Date.now()}`;
                    } else if (logoPreview) {
                        logoPreview.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(c.nom || 'E')}&background=random`;
                    }
                }
            })
            .catch(err => console.error("Error loading company data:", err));
    }

    if (form) {
        form.addEventListener("submit", (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const origText = btn.innerHTML;
            
            btn.disabled = true;
            btn.textContent = "C'est en cours...";

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch("../api/admin_company_profile.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast("Profil mis à jour avec succès !", "success");
                    loadCompanyData();
                } else {
                    showToast(data.message, "error");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Erreur lors de l'enregistrement.", "error");
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = origText;
            });
        });
    }

    if (logoInput) {
        // Trigger file input when clicking the image container
        const picWrapper = document.querySelector(".group.relative.w-32.h-32");
        if (picWrapper) {
            picWrapper.addEventListener("click", () => logoInput.click());
        }

        logoInput.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Preview local
            const reader = new FileReader();
            reader.onload = (re) => {
                if (logoPreview) logoPreview.src = re.target.result;
            };
            reader.readAsDataURL(file);

            // Upload
            const upData = new FormData();
            upData.append('logo', file);

            fetch("../api/admin_company_profile.php", {
                method: "POST",
                body: upData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast("Logo mis à jour !", "success");
                    // Update sidebar if possible
                    if (hdrProfilePicSidebar) hdrProfilePicSidebar.src = `../${data.path}?v=${Date.now()}`;
                } else {
                    showToast(data.message, "error");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Erreur lors du transfert du logo.", "error");
            });
        });
    }

    function showToast(msg, type) {
        // Check for existing toast container or use alert if not fancy
        // For now, let's just make it a nice div at the top
        let toast = document.getElementById("admin-toast");
        if (!toast) {
            toast = document.createElement("div");
            toast.id = "admin-toast";
            toast.className = "fixed top-5 left-1/2 -translate-x-1/2 z-[1000] px-6 py-3 rounded-2xl shadow-2xl transition-all duration-300 translate-y-[-100px] font-bold text-white";
            document.body.appendChild(toast);
        }

        toast.textContent = msg;
        toast.style.backgroundColor = type === "success" ? "#10b981" : "#ef4444";
        
        // Show
        toast.classList.remove("translate-y-[-100px]");
        toast.classList.add("translate-y-0");

        setTimeout(() => {
            toast.classList.remove("translate-y-0");
            toast.classList.add("translate-y-[-100px]");
        }, 3000);
    }
});
