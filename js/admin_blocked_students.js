/* js/admin_blocked_students.js */
document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("blockedTableBody");
    const noUsersMsg = document.getElementById("noUsersMsg");
    const searchInput = document.getElementById("searchInput");
    
    let allBlocked = [];

    loadBlocked();

    function loadBlocked() {
        if (!tableBody) return;
        tableBody.innerHTML = `<tr><td colspan="4" class="py-10 text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-rose-600 border-t-transparent"></div></td></tr>`;
        noUsersMsg.classList.add("hidden");

        fetch("../api/admin_company_applications.php?action=blocked_list")
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    allBlocked = data.blocked;
                    renderTable(allBlocked);
                } else {
                    tableBody.innerHTML = `<tr><td colspan="4" class="py-10 text-center text-red-500">${data.message}</td></tr>`;
                }
            })
            .catch(err => {
                console.error("Error loading blocked students:", err);
                tableBody.innerHTML = `<tr><td colspan="4" class="py-10 text-center text-red-500">Erreur réseau.</td></tr>`;
            });
    }

    function renderTable(users) {
        if (users.length === 0) {
            tableBody.innerHTML = "";
            noUsersMsg.classList.remove("hidden");
            return;
        }

        tableBody.innerHTML = users.map(user => `
            <tr class="group hover:bg-gray-50/50 transition-colors">
                <td class="px-4 py-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 font-black text-sm">
                            ${user.prenom ? user.prenom.charAt(0) : ''}${user.nom ? user.nom.charAt(0) : 'E'}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">${user.prenom || ''} ${user.nom || 'Étudiant'}</p>
                            <p class="text-xs font-semibold text-gray-400">Raison: ${user.reason}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-5 font-semibold text-sm text-gray-600">
                    ${user.email}
                </td>
                <td class="px-4 py-5 font-semibold text-sm text-gray-500">
                    ${new Date(user.blocked_at).toLocaleDateString('fr-FR')}
                </td>
                <td class="px-4 py-5 text-right">
                    <button onclick="unblockStudent(${user.student_id})" class="px-4 py-2 bg-rose-100/50 hover:bg-rose-100 text-rose-600 rounded-xl font-bold text-xs transition-colors border border-rose-200 hover:border-rose-300">
                        Débloquer
                    </button>
                </td>
            </tr>
        `).join("");
    }

    if(searchInput) {
        searchInput.addEventListener("input", (e) => {
            const val = e.target.value.toLowerCase();
            const filtered = allBlocked.filter(u => 
                (u.nom && u.nom.toLowerCase().includes(val)) ||
                (u.prenom && u.prenom.toLowerCase().includes(val)) ||
                (u.email && u.email.toLowerCase().includes(val)) ||
                (u.reason && u.reason.toLowerCase().includes(val))
            );
            renderTable(filtered);
        });
    }

    window.unblockStudent = (studentId) => {
        if(!confirm("Êtes-vous sûr de vouloir débloquer cet étudiant ? Il pourra à nouveau postuler à vos offres.")) return;
    

        fetch("../api/admin_company_applications.php?action=unblock_student", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ student_id: studentId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, "success");
                loadBlocked();
            } else {
                showToast(data.message, "error");
            }
        })
        .catch(err => {
            console.error(err);
            showToast("Erreur système", "error");
        });
    };

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
