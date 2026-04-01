document.addEventListener("DOMContentLoaded", () => {
    const formAdminSettings = document.getElementById("formAdminSettings");
    
    if (formAdminSettings) {
        formAdminSettings.addEventListener("submit", (e) => {
            e.preventDefault();
            const btn = document.getElementById("btnSaveSettings");
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.textContent = "Validation en cours...";

            const email_notifs = document.getElementById("email_notifs").checked;
            const weekly_reports = document.getElementById("weekly_reports").checked;
            const public_profile = document.getElementById("public_profile").checked;
            const mode_alternance = document.getElementById("mode_alternance").checked;
            const mode_statsy = document.getElementById("mode_statsy").checked;

            fetch("../api/admin_company_settings.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    email_notifs: email_notifs,
                    weekly_reports: weekly_reports,
                    public_profile: public_profile,
                    mode_alternance: mode_alternance,
                    mode_statsy: mode_statsy
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, "success");
                } else {
                    showToast(data.message, "error");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Erreur lors de l'enregistrement", "error");
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
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
