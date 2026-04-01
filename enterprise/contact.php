<?php 
require_once '../include/session.php';
// Allows both etudiant and entreprise pages
check_auth(); // Allow any authenticated user to view the page
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support & Contact - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/dashboards.css">
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }
        .form-input {
            @apply w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all duration-300;
        }
    </style>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?> bg-gray-50">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto md:ml-64">
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-blue-600">StageMatch</span>
                </div>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>
            
            <div class="h-full flex flex-col items-center justify-center p-6 lg:p-12">
                <!-- Main Container -->
                <div class="w-full max-w-2xl bg-white rounded-[2.5rem] shadow-2xl shadow-blue-500/5 p-8 lg:p-12 border border-gray-100 overflow-hidden relative">
                        <!-- Header -->
                        <div class="flex items-center gap-6 mb-12">
                            <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 shadow-sm border border-blue-100/50">
                                <i class="fas fa-building text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-900 leading-tight">Demande Entreprise</h2>
                                <p class="text-sm text-slate-500 font-semibold tracking-wide mt-0.5 mb-4">Nous sommes là pour vous aider.</p>
                                <button id="inboxToggleBtn" onclick="showSupportInbox()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-900 hover:text-white text-gray-700 rounded-xl font-black text-[10px] uppercase tracking-[0.2em] transition-all shadow-sm flex items-center gap-2 relative border border-gray-200/50">
                                    <i class="fas fa-envelope"></i> Message
                                    <span id="notifDot" class="hidden absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white"></span>
                                </button>
                            </div>
                        </div>

                        <div id="contactFormContainer">

                        <form id="enterpriseRequestForm" class="space-y-8">
                            <input type="hidden" name="user_type" value="enterprise">
                            
                            <!-- Row 1 -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-sm font-extrabold text-slate-800 ml-1">Nom de l'entreprise</label>
                                    <input type="text" name="company_name" required class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all duration-300 placeholder:text-gray-300" placeholder="Ex: Ma Société Sarl">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-extrabold text-slate-800 ml-1">Email Professionnel</label>
                                    <input type="email" name="email" required class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all duration-300 placeholder:text-gray-300" placeholder="contact@entreprise.com">
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-sm font-extrabold text-slate-800 ml-1">Téléphone</label>
                                    <input type="tel" name="phone" required class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all duration-300 placeholder:text-gray-300" placeholder="+222 ...">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-extrabold text-slate-800 ml-1">Groupe / Option</label>
                                    <div class="relative group">
                                         <select name="problem_type" class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 appearance-none cursor-pointer font-bold text-gray-700">
                                             <option value="Problème de compte">Problème de compte</option>
                                             <option value="Publication d'offre">Publication d'offre</option>
                                             <option value="Gestion des candidats">Gestion des candidats</option>
                                             <option value="Problème de sécurité">Problème de sécurité</option>
                                             <option value="Facturation / Paiement">Facturation / Paiement</option>
                                             <option value="Autre">Autre</option>
                                         </select>
                                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 group-hover:text-blue-500 transition-colors">
                                            <i class="fas fa-chevron-down text-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- Row 3 -->
                        <div class="space-y-2">
                            <label class="text-sm font-extrabold text-slate-800 ml-1">Titre de la demande</label>
                            <input type="text" name="title" required class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 placeholder:text-gray-300" placeholder="Objet de votre demande">
                        </div>

                        <!-- Row 4 -->
                        <div class="space-y-2">
                            <label class="text-sm font-extrabold text-slate-800 ml-1">Description détaillée</label>
                            <textarea name="message" rows="4" required class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 placeholder:text-gray-300 resize-none" placeholder="Décrivez votre besoin ou problème..."></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="pt-4">
                            <button type="submit" class="w-full py-5 bg-[#2563eb] hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl shadow-blue-500/20 transition-all duration-300 flex items-center justify-center gap-3 active:scale-[0.98]">
                                Envoyer la demande
                            </button>
                        </div>
                        
                        <!-- Success Message -->
                        <div id="successMsg" class="hidden animate-in fade-in duration-500 p-6 bg-green-50 rounded-3xl border border-green-100 text-center">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 mx-auto mb-3">
                                <i class="fas fa-check"></i>
                            </div>
                            <p class="font-black text-green-800 text-lg">Message envoyé !</p>
                            <p class="text-green-600 font-medium text-sm mt-1">Notre équipe de support vous contactera prochainement.</p>
                        </div>
                    </form>
                    </div>

                    <!-- Inbox Section for Enterprise -->
                    <?php
                    $ent_user_id = $_SESSION['user_id'];
                    $stmt_req = $db->prepare("SELECT cr.*, u.nom as sender_name FROM contact_requests cr 
                                              LEFT JOIN users u ON cr.user_id = u.id 
                                              WHERE (cr.target_company_id = ? AND cr.user_type = 'student') 
                                              OR (cr.user_id = ? AND cr.user_type = 'enterprise')
                                              ORDER BY cr.created_at DESC");
                    $stmt_req->execute([$ent_user_id, $ent_user_id]);
                    $my_requests = $stmt_req->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div id="supportInbox" class="hidden animate-in fade-in duration-500">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-black text-slate-900 flex items-center gap-3">
                                <i class="fas fa-inbox text-blue-600"></i> Vos Messages
                            </h2>
                            <button onclick="showContactForm()" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all flex items-center gap-2 border border-blue-100">
                                <i class="fas fa-plus"></i> Nouveau Message
                            </button>
                        </div>

                    <?php if (empty($my_requests)): ?>
                        <div class="py-12 text-center">
                            <i class="fas fa-comments text-gray-200 text-5xl mb-4"></i>
                            <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Aucun message pour le moment</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach($my_requests as $msg): ?>
                                <div class="border border-gray-100 rounded-2xl p-6 bg-gray-50 hover:bg-white hover:shadow-md transition-all cursor-pointer group relative overflow-hidden" onclick="openChatModal(<?php echo $msg['id']; ?>)">
                                    <?php if ($msg['has_new_reply'] == 1): ?>
                                        <div class="absolute top-0 right-0 w-24 h-24 -mr-12 -mt-12 bg-blue-500/10 rounded-full animate-pulse"></div>
                                        <span class="absolute top-4 right-4 flex items-center gap-1.5 bg-blue-600 text-white text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-full shadow-lg shadow-blue-500/20 z-10">
                                            <span class="w-1.5 h-1.5 bg-white rounded-full animate-ping"></span> Nouveau
                                        </span>
                                    <?php endif; ?>

                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            <?php if ($msg['user_type'] === 'student'): ?>
                                                <span class="text-blue-500 mr-2 uppercase text-[10px] tracking-wider">Étudiant:</span> 
                                                <?php echo htmlspecialchars($msg['sender_name'] ?? 'Étudiant anonyme'); ?>
                                            <?php else: ?>
                                                <span class="text-indigo-500 mr-2 uppercase text-[10px] tracking-wider">Support Site:</span> 
                                                <?php echo htmlspecialchars($msg['title']); ?>
                                            <?php endif; ?>
                                        </h3>
                                        <?php if ($msg['status'] === 'pending' || $msg['status'] === 'unread'): ?>
                                            <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 bg-orange-100 text-orange-600 rounded-lg">En attente</span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 bg-green-100 text-green-600 rounded-lg">Répondu</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="text-[11px] text-gray-400 font-black uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                                        Objet : <span class="text-gray-700"><?php echo htmlspecialchars($msg['title']); ?></span>
                                    </p>
                                    
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-500 leading-relaxed italic line-clamp-2">
                                            "<?php echo htmlspecialchars($msg['message']); ?>"
                                        </p>
                                    </div>

                                    <div class="flex justify-end items-center gap-2 text-blue-600 font-black text-[10px] uppercase tracking-widest group-hover:translate-x-1 transition-transform">
                                        <span>Voir la discussion</span>
                                        <i class="fas fa-chevron-right text-[8px]"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
               </div>

            </div>
        </main>
    </div>

    <!-- Alert Component -->
    <div id="alertBox" class="fixed bottom-10 right-10 z-50 transform translate-y-20 opacity-0 transition-all duration-500 flex items-center gap-4 px-6 py-4 rounded-[1.5rem] shadow-2xl">
        <div id="alertIcon" class="w-8 h-8 rounded-full flex items-center justify-center text-white"></div>
        <p id="alertMessage" class="font-bold text-sm"></p>
    </div>

    <!-- Chat Modal -->
    <div id="chatModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeChatModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in duration-300">
            <!-- Modal Header -->
            <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-lg">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div>
                        <h3 id="modalTicketTitle" class="text-lg font-black text-slate-900 leading-tight">...</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                            <span id="modalCompanyName" class="text-indigo-500">...</span> • 
                            <span id="modalStatus" class="font-black">...</span>
                        </p>
                    </div>
                </div>
                <button onclick="closeChatModal()" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:bg-gray-50 rounded-xl transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Chat Area -->
            <div id="modalChatArea" class="h-[400px] overflow-y-auto p-8 space-y-4 custom-scrollbar bg-white">
                <!-- Messages will be injected here -->
            </div>

            <!-- Input Area -->
            <div class="p-6 bg-gray-50 border-t border-gray-100">
                <form id="modalReplyForm" class="flex gap-3">
                    <input type="hidden" id="modalRequestId" name="request_id">
                    <input type="text" name="message" required class="flex-1 px-6 py-4 bg-white border border-gray-200 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-sm" placeholder="Votre message...">
                    <button type="submit" class="w-14 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20 hover:scale-105 transition-all">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openChatModal(id) {
            const modal = document.getElementById('chatModal');
            const chatArea = document.getElementById('modalChatArea');
            document.getElementById('modalRequestId').value = id;
            
            modal.classList.remove('hidden');
            chatArea.innerHTML = '<div class="flex justify-center p-12"><i class="fas fa-spinner fa-spin text-2xl text-indigo-600"></i></div>';

            fetch(`../api/get_support_thread.php?request_id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('modalTicketTitle').textContent = data.request.title;
                        document.getElementById('modalCompanyName').textContent = data.request.company_name;
                        document.getElementById('modalStatus').textContent = data.request.status === 'answered' ? 'TRAITÉ' : 'EN ATTENTE';
                        document.getElementById('modalStatus').className = data.request.status === 'answered' ? 'text-green-500 font-black' : 'text-orange-500 font-black';
                        
                        renderModalMessages(data.messages);
                    }
                });
        }

        function renderModalMessages(messages) {
            const chatArea = document.getElementById('modalChatArea');
            chatArea.innerHTML = '';
            messages.forEach(msg => {
                const isUser = (msg.sender_type === 'user');
                const bubbleClass = isUser ? 'bg-indigo-600 text-white rounded-l-2xl rounded-tr-2xl ml-auto' : 'bg-gray-100 text-gray-800 rounded-r-2xl rounded-tl-2xl mr-auto';
                const alignment = isUser ? 'flex justify-end' : 'flex justify-start';
                chatArea.innerHTML += `
                    <div class="${alignment}">
                        <div class="max-w-[85%] ${bubbleClass} p-4 shadow-sm">
                            <p class="text-sm font-medium leading-relaxed">${msg.message_text}</p>
                            <span class="block text-[8px] mt-2 font-black uppercase tracking-widest opacity-60 text-right">${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                        </div>
                    </div>
                `;
            });
            chatArea.scrollTop = chatArea.scrollHeight;
        }

        function closeChatModal() {
            document.getElementById('chatModal').classList.add('hidden');
        }

        document.getElementById('modalReplyForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const btn = e.target.querySelector('button');
            btn.disabled = true;

            try {
                const res = await fetch('../api/send_reply.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    const id = document.getElementById('modalRequestId').value;
                    openChatModal(id); // Reload
                    e.target.reset();
                }
            } finally {
                btn.disabled = false;
            }
        });

        function showSupportInbox() {
            document.getElementById('contactFormContainer').classList.add('hidden');
            document.getElementById('supportInbox').classList.remove('hidden');
            document.getElementById('inboxToggleBtn').classList.add('hidden');
        }

        function showContactForm() {
            document.getElementById('contactFormContainer').classList.remove('hidden');
            document.getElementById('supportInbox').classList.add('hidden');
            document.getElementById('inboxToggleBtn').classList.remove('hidden');
        }

        function scrollToSupportList() {
            showSupportInbox();
        }

        document.getElementById('enterpriseRequestForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const successMsg = document.getElementById('successMsg');
            const originalHtml = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Envoi...';
            btn.disabled = true;
            successMsg.classList.add('hidden');
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('../api/submit_contact.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successMsg.classList.remove('hidden');
                    e.target.reset();
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    alert(data.message || "Une erreur est survenue.");
                }
            } catch (error) {
                alert("Une erreur serveur est survenue.");
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        });
    </script>
    <script src="../js/support_system.js" defer></script>
</body>
</html>
