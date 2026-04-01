/* js/support_system.js */
document.addEventListener("DOMContentLoaded", () => {
    const mainContainer = document.querySelector('.max-w-2xl');
    if (!mainContainer) return;

    // 1. Notification Logic
    async function updateNotifications() {
        try {
            const resp = await fetch('../api/get_conversation.php');
            const data = await resp.json();
            if (data.success) {
                const unreadCount = data.demands.filter(d => d.has_new_reply == 1).length;
                const notifDot = document.getElementById('notifDot');
                if (notifDot) {
                    notifDot.classList.toggle('hidden', unreadCount === 0);
                }

                const sidebarNoContact = document.querySelector('a[href*="contact.php"]');
                if (sidebarNoContact) {
                    let dot = sidebarNoContact.querySelector('.sidebar-dot');
                    if (unreadCount > 0) {
                        if (!dot) {
                            dot = document.createElement('span');
                            dot.className = 'sidebar-dot ml-auto w-2 h-2 bg-red-500 rounded-full inline-block';
                            sidebarNoContact.appendChild(dot);
                        }
                    } else if (dot) {
                        dot.remove();
                    }
                }
            }
        } catch (err) { console.error(err); }
    }

    updateNotifications();
    setInterval(updateNotifications, 30000);
});
