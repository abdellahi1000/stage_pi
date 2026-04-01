/* js/login.js */
document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  const userTypeInput = document.getElementById("userType");
  const changeTypeBtn = document.getElementById("changeTypeBtn");
  const selectedTypeIcon = document.getElementById("selectedTypeIcon");
  const selectedTypeText = document.getElementById("selectedTypeText");

  // Handle Login Submission
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const email = document.getElementById("email").value;
      const password = document.getElementById("password").value;
      const remember = document.getElementById("remember").checked;
      const submitBtn = document.querySelector('button[type="submit"]');

      submitBtn.disabled = true;
      submitBtn.textContent = "Connexion...";

      const formData = new FormData();
      formData.append("action", "login");
      formData.append("email", email);
      formData.append("password", password);
      formData.append("remember", remember ? "1" : "0");
      formData.append("type_compte", userTypeInput.value); // Ensure account type is sent

      fetch("include/auth.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showMessage("Connexion réussie ! Redirection...", "success");
            setTimeout(() => {
              window.location.href = data.redirect;
            }, 1000);
          } else {
            showMessage(data.message, "error");
            submitBtn.disabled = false;
            submitBtn.textContent = "Se connecter";
          }
        })
        .catch((error) => {
          console.error("Erreur:", error);
          showMessage("Erreur serveur", "error");
          submitBtn.disabled = false;
          submitBtn.textContent = "Se connecter";
        });
    });
  }

  // --- Modal Contact Logic ---
  const openContactModalBtn = document.getElementById("openContactModal");
  const contactModal = document.getElementById("contactModal");
  const closeContactModalBtns = [
    document.getElementById("closeContactModal"),
    document.getElementById("closeModalBtnTop")
  ];
  const publicContactForm = document.getElementById("publicContactForm");
  const submitContactBtn = document.getElementById("submitContactBtn");

  // Ensure body is scrollable on the login page initially
  document.body.style.overflow = "auto";

  if (openContactModalBtn && contactModal) {
    openContactModalBtn.addEventListener("click", function (e) {
      e.preventDefault();
      contactModal.style.display = "flex";
      document.body.style.overflow = "hidden"; // Lock background scroll

      // ensure we are clearing old values
      if (document.getElementById("contactSubject")) document.getElementById("contactSubject").value = "";
      if (document.getElementById("contactMessage")) document.getElementById("contactMessage").value = "";
    });

    closeContactModalBtns.forEach(btn => {
      if (btn) {
        btn.addEventListener("click", function () {
          contactModal.style.display = "none";
          document.body.style.overflow = "auto"; // Restore scroll
        });
      }
    });

    // close on clicking outside
    window.addEventListener("click", function (e) {
      if (e.target === contactModal) {
        contactModal.style.display = "none";
        document.body.style.overflow = "auto"; // Restore scroll
      }
    });
  }

  if (publicContactForm) {
    const problemChatView = document.getElementById("problemChatView");
    const problemChatHistory = document.getElementById("problemChatHistory");
    const identifyUser = document.getElementById("identifyUser");
    const publicFollowUpForm = document.getElementById("publicFollowUpForm");
    const messagesBtn = document.querySelector(".btn-messages-modal");
    const chatEmailTag = document.getElementById("chatEmailTag");

    // Check for a saved email to show the notification badge on load
    const savedEmail = localStorage.getItem("support_email");
    if (savedEmail) {
      checkNotifications(savedEmail);
    }

    // Periodic check every 30s
    setInterval(() => {
      const email = localStorage.getItem("support_email");
      if (email) checkNotifications(email);
    }, 30000);

    // Toggle Chat View
    if (messagesBtn) {
      messagesBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        publicContactForm.style.display = "none";
        problemChatView.style.display = "flex";
        const currentEmail = localStorage.getItem("support_email");
        if (currentEmail) {
          loadConversation(currentEmail);
        } else {
          identifyUser.style.display = "block";
          problemChatHistory.style.display = "none";
          publicFollowUpForm.style.display = "none";
        }
      });
    }

    document.getElementById("backToForm").addEventListener("click", () => {
      problemChatView.style.display = "none";
      publicContactForm.style.display = "block";
    });

    // Lookup Logic
    document.getElementById("btnLookup").addEventListener("click", () => {
      const email = document.getElementById("lookupEmail").value.trim();
      if (email) {
        localStorage.setItem("support_email", email);
        loadConversation(email);
      }
    });

    async function checkNotifications(email) {
      try {
        const resp = await fetch(`api/get_public_conversation.php?email=${encodeURIComponent(email)}`);
        const data = await resp.json();
        if (data.success && data.demands) {
          const hasUnread = data.demands.some(d => d.has_new_reply == 1);
          if (messagesBtn) {
            if (hasUnread) {
              messagesBtn.style.border = "2px solid white";
              messagesBtn.innerHTML = 'MESSAGES <span style="display:inline-block; width:8px; height:8px; background:white; border-radius:50%; margin-left:4px; animation: pulse 1.5s infinite;"></span>';
            } else {
              messagesBtn.style.border = "none";
              messagesBtn.innerHTML = 'MESSAGES';
            }
          }
        }
      } catch (e) { }
    }

    async function loadConversation(email) {
      if (!email || !email.includes("@")) return;

      identifyUser.style.display = "none";
      problemChatHistory.style.display = "flex";
      problemChatHistory.innerHTML = '<div style="text-align:center; color:#9ca3af; font-size:12px; margin-top:50px;"><i class="fas fa-spinner fa-spin mr-2"></i> Chargement de vos messages...</div>';
      chatEmailTag.innerText = email;

      try {
        const resp = await fetch(`api/get_public_conversation.php?email=${encodeURIComponent(email)}`);
        if (!resp.ok) throw new Error("Network response was not ok");

        const data = await resp.json();
        if (data.success && data.demands && data.demands.length > 0) {
          // Find the most recent demand with new replies, or just the first one
          const demand = data.demands.find(d => d.has_new_reply == 1) || data.demands[0];

          document.getElementById("publicActiveId").value = demand.id;
          renderMessages(demand.messages);
          publicFollowUpForm.style.display = "flex";

          if (demand.has_new_reply == 1) {
            markAsRead(demand.id);
          }
        } else {
          problemChatHistory.innerHTML = `
            <div style="text-align:center; color:#9ca3af; font-size:13px; margin-top:50px;">
                <i class="fas fa-search mb-3" style="font-size: 24px; opacity: 0.5;"></i>
                <p style="font-weight:700;">Aucune conversation trouvée.</p>
                <p style="font-size: 11px; margin-top: 5px;">Vérifiez l'email: <b>${email}</b></p>
                <button type="button" onclick="localStorage.removeItem('support_email'); location.reload();" style="margin-top:15px; background:#f3f4f6; border:none; padding:6px 12px; border-radius:6px; font-size:10px; font-weight:700; cursor:pointer;">Changer d'email</button>
            </div>`;
          publicFollowUpForm.style.display = "none";
        }
      } catch (err) {
        console.error("Load conversation error:", err);
        problemChatHistory.innerHTML = '<div style="color:#ef4444; text-align:center; font-size:12px; margin-top:50px;"><i class="fas fa-exclamation-triangle mb-2"></i><br>Erreur de chargement. Veuillez réessayer.</div>';
      }
    }

    function renderMessages(messages) {
      if (!messages || messages.length === 0) {
        problemChatHistory.innerHTML = '<div style="text-align:center; color:#9ca3af; font-size:12px; margin-top:50px;">Aucun message dans cette discussion.</div>';
        return;
      }

      problemChatHistory.innerHTML = messages.map(m => {
        const content = (m.message_text || "").replace(/\n/g, '<br>');
        const isSupport = m.sender_type === 'support' || m.sender_type === 'admin';
        const time = m.created_at ? new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';

        return `
            <div style="display: flex; flex-direction: column; align-items: ${isSupport ? 'flex-start' : 'flex-end'}">
                <div style="max-width: 85%; padding: 10px 15px; border-radius: 12px; font-size: 13px; background: ${isSupport ? '#f3f4f6' : '#4f46e5'}; color: ${isSupport ? '#374151' : 'white'}; border-bottom-${isSupport ? 'left' : 'right'}-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    ${content}
                </div>
                <span style="font-size: 9px; color: #9ca3af; margin-top: 4px; font-weight: 700; text-transform: uppercase;">${isSupport ? 'Support' : 'Vous'} • ${time}</span>
            </div>
        `;
      }).join('');

      setTimeout(() => {
        problemChatHistory.scrollTop = problemChatHistory.scrollHeight;
      }, 50);
    }

    async function markAsRead(id) {
      await fetch(`api/mark_as_read.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `demand_id=${id}`
      });
      const email = localStorage.getItem("support_email");
      if (email) checkNotifications(email);
    }

    publicFollowUpForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const id = document.getElementById("publicActiveId").value;
      const msg = document.getElementById("publicFollowUpText").value;
      const email = localStorage.getItem("support_email");

      if (!msg.trim()) return;

      try {
        const fd = new FormData();
        fd.append("request_id", id);
        fd.append("message", msg);
        fd.append("email", email);

        const resp = await fetch("api/send_public_support_message.php", { method: "POST", body: fd });
        const data = await resp.json();
        if (data.success) {
          document.getElementById("publicFollowUpText").value = "";
          loadConversation(email);
        } else {
          alert(data.message);
        }
      } catch (e) { alert("Erreur d'envoi."); }
    });

    publicContactForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const emailEntered = document.getElementById("contactEmail").value.trim();
      const btn = submitContactBtn;
      const originalHtml = btn.innerHTML;
      const successMsg = document.getElementById("contactSuccessMsg");

      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
      if (successMsg) successMsg.style.display = "none";

      const formData = new FormData(publicContactForm);

      fetch("api/submit_contact.php", {
        method: "POST",
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            localStorage.setItem("support_email", emailEntered);
            if (successMsg) {
              successMsg.style.display = "block";
              publicContactForm.reset();
              setTimeout(() => {
                // Switch to chat view after submission
                messagesBtn.click();
              }, 2000);
            }
          } else {
            alert(data.message || "Erreur.");
          }
        })
        .finally(() => {
          btn.disabled = false;
          btn.innerHTML = originalHtml;
        });
    });
  }
});

// Pulse animation for notification dot
const style = document.createElement('style');
style.innerHTML = `
@keyframes pulse {
  0% { transform: scale(0.95); opacity: 0.9; }
  70% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(0.95); opacity: 0.9; }
}
`;
document.head.appendChild(style);
