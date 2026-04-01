document.addEventListener("DOMContentLoaded", () => {
    const formAddFAQ = document.getElementById("formAddFAQ");
    const faqList = document.getElementById("companyFaqList");

    if (faqList) {
        loadCompanyFaqs();
    }

    if (formAddFAQ) {
        formAddFAQ.addEventListener("submit", (e) => {
            e.preventDefault();
            const formData = new FormData(formAddFAQ);
            
            fetch("../api/admin_company_faq.php?action=add", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    question: formData.get("question"),
                    answer: formData.get("answer")
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, "success");
                    document.getElementById("modalAddFAQ").classList.add("hidden");
                    formAddFAQ.reset();
                    loadCompanyFaqs();
                } else {
                    showToast(data.message, "error");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Erreur système", "error");
            });
        });
    }

    function loadCompanyFaqs() {
        if (!faqList) return;
        faqList.innerHTML = `<p class="text-gray-500 italic text-sm">Chargement des questions...</p>`;

        fetch("../api/admin_company_faq.php?action=list")
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.faqs.length === 0) {
                        faqList.innerHTML = `<p class="text-gray-400 font-medium">Vous n'avez pas encore ajouté de questions personnalisées.</p>`;
                        return;
                    }
                    faqList.innerHTML = data.faqs.map(faq => `
                        <div class="faq-item group bg-white p-4 rounded-xl border border-gray-100 shadow-sm relative pr-12">
                            <h4 class="font-bold text-gray-900 mb-2">${escapeHtml(faq.question)}</h4>
                            <p class="text-gray-600 text-sm">${escapeHtml(faq.answer)}</p>
                            <button class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100" onclick="deleteFaq(${faq.id})">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </div>
                    `).join("");
                } else {
                    faqList.innerHTML = `<p class="text-red-500">${data.message}</p>`;
                }
            })
            .catch(err => console.error(err));
    }

    window.deleteFaq = (id) => {
        if (!confirm("Voulez-vous vraiment supprimer cette FAQ ?")) return;

        fetch("../api/admin_company_faq.php?action=delete", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, "success");
                loadCompanyFaqs();
            } else {
                showToast(data.message, "error");
            }
        })
        .catch(err => console.error(err));
    };

    function escapeHtml(unsafe) {
        return (unsafe || "").replace(/&/g, "&amp;")
                             .replace(/</g, "&lt;")
                             .replace(/>/g, "&gt;")
                             .replace(/"/g, "&quot;")
                             .replace(/'/g, "&#039;");
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
