/* js/admin_account.js */
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formAccount");
    const photoInput = document.getElementById("accPhotoInput");
    const photoPreview = document.getElementById("accPhotoPreview");
    const displayName = document.getElementById("accDisplayName");
    const prenomInput = document.getElementById("accPrenom");
    const nomInput = document.getElementById("accNom");
    const telInput = document.getElementById("accTel");

    loadUserData();

    function loadUserData() {
        if (!emailInput) return;
        fetch("../api/admin_account.php")
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const u = data.user;
                    emailInput.value = u.email || "";
                    if (prenomInput) prenomInput.value = u.prenom || "";
                    if (nomInput) nomInput.value = u.nom || "";
                    if (telInput) telInput.value = u.telephone || "";
                    if (document.getElementById("accBio")) document.getElementById("accBio").value = u.bio || "";
                    if (displayName) displayName.textContent = (u.prenom || "") + " " + (u.nom || "");
                    if (photoPreview && u.photo_profil) {
                        photoPreview.src = "../" + u.photo_profil;
                    }
                }
            });
    }

    // Photo Upload Logic
    if (photoInput) {
        photoInput.addEventListener("change", function(e) {
            if (e.target.files && e.target.files[0]) {
                const formData = new FormData();
                formData.append("photo", e.target.files[0]);
                formData.append("action", "profile"); // profile photo update

                fetch("../include/upload_photo.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        photoPreview.src = data.photo_url;
                        showToast("Photo de profil mise à jour !", "success");
                    } else {
                        showToast(data.message, "error");
                    }
                })
                .catch(err => console.error(err));
            }
        });
    }

    if (form) {
        form.addEventListener("submit", (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const origText = btn.innerHTML;
            
            btn.disabled = true;
            btn.textContent = "Mise à jour...";

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch("../api/admin_account.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast("Vos informations ont été mises à jour.", "success");
                    loadUserData();
                    form.current_password.value = "";
                    form.new_password.value = "";
                    form.confirm_password.value = "";
                } else {
                    showToast(data.message, "error");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Erreur système.", "error");
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = origText;
            });
        });
    }

    function showToast(msg, type) {
        let toast = document.getElementById("admin-toast");
        if (!toast) {
            toast = document.createElement("div");
            toast.id = "admin-toast";
            toast.className = "fixed top-5 left-1/2 -translate-x-1/2 z-[1000] px-6 py-3 rounded-2xl shadow-2xl transition-all duration-300 translate-y-[-100px] font-bold text-white";
            document.body.appendChild(toast);
        }

        toast.textContent = msg;
        toast.style.backgroundColor = type === "success" ? "#10b981" : "#ef4444";
        
        toast.classList.remove("translate-y-[-100px]");
        toast.classList.add("translate-y-0");

        setTimeout(() => {
            toast.classList.remove("translate-y-0");
            toast.classList.add("translate-y-[-100px]");
        }, 3000);
    }
});
