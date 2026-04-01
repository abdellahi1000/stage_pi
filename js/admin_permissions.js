/* js/admin_permissions.js */
document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("permissionsTableBody");
    const noUsersMsg = document.getElementById("noUsersMsg");
    
    let allUsers = [];

    loadPermissions();

    function loadPermissions() {
        if (!tableBody) return;
        tableBody.innerHTML = `<tr><td colspan="4" class="py-10 text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div></td></tr>`;
        noUsersMsg.classList.add("hidden");

        fetch("../api/admin_permissions.php?action=list")
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    allUsers = data.collaborators;
                    renderTable(allUsers);
                } else {
                    tableBody.innerHTML = `<tr><td colspan="4" class="py-10 text-center text-red-500">${data.message}</td></tr>`;
                }
            })
            .catch(err => {
                console.error("Error loading permissions:", err);
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
                        <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 font-black text-sm">
                            ${user.prenom ? user.prenom.charAt(0) : ''}${user.nom ? user.nom.charAt(0) : 'U'}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">${user.prenom || ''} ${user.nom || 'Utilisateur'}</p>
                            <p class="text-xs font-semibold text-gray-500">${user.email}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-5 text-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" ${user.can_create_offers == 1 ? 'checked' : ''} onchange="updatePermission(${user.id}, 'create', this.checked)">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </td>
                <td class="px-4 py-5 text-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" ${user.can_edit_offers == 1 ? 'checked' : ''} onchange="updatePermission(${user.id}, 'edit', this.checked)">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                    </label>
                </td>
                <td class="px-4 py-5 text-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" ${user.can_delete_offers == 1 ? 'checked' : ''} onchange="updatePermission(${user.id}, 'delete', this.checked)">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                    </label>
                </td>
                <td class="px-4 py-5 text-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" ${user.can_manage_candidates == 1 ? 'checked' : ''} onchange="updatePermission(${user.id}, 'manage_candidates', this.checked)">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-500"></div>
                    </label>
                </td>
                <td class="px-4 py-5 text-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" ${user.can_block_users == 1 ? 'checked' : ''} onchange="updatePermission(${user.id}, 'block_users', this.checked)">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-800"></div>
                    </label>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="inline-flex px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold whitespace-nowrap">Collaborateur</span>
                </td>
            </tr>
        `).join("");
    }

    window.updatePermission = (userId, type, isChecked) => {
        const user = allUsers.find(u => u.id == userId);
        if (!user) return;

        let canCreate = type === 'create' ? (isChecked ? 1 : 0) : parseInt(user.can_create_offers);
        let canEdit = type === 'edit' ? (isChecked ? 1 : 0) : parseInt(user.can_edit_offers);
        let canDelete = type === 'delete' ? (isChecked ? 1 : 0) : parseInt(user.can_delete_offers);
        let canManageCandidates = type === 'manage_candidates' ? (isChecked ? 1 : 0) : parseInt(user.can_manage_candidates);
        let canBlockUsers = type === 'block_users' ? (isChecked ? 1 : 0) : parseInt(user.can_block_users);

        fetch("../api/admin_permissions.php?action=update", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                user_id: userId,
                can_create: canCreate,
                can_edit: canEdit,
                can_delete: canDelete,
                can_manage_candidates: canManageCandidates,
                can_block_users: canBlockUsers
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Keep local state in sync
                user.can_create_offers = canCreate;
                user.can_edit_offers = canEdit;
                user.can_delete_offers = canDelete;
                user.can_manage_candidates = canManageCandidates;
                user.can_block_users = canBlockUsers;
                showToast("Permissions mises à jour avec succès", "success");
            } else {
                showToast(data.message, "error");
                renderTable(allUsers); // Revert
            }
        })
        .catch(err => {
            console.error(err);
            showToast("Erreur système", "error");
            renderTable(allUsers); // Revert
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
