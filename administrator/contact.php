<?php 
require_once '../include/session.php';
require_once '../include/db_connect.php';
check_auth('entreprise', 'Administrator');

$db = (new Database())->getConnection();
$company_id = $_SESSION['company_id'];

// Handling marks as read (or simple responses logic)
// Fetch all requests sent to this company
// Fetch all requests sent to this company (from students) OR from company members to system/manager
$stmt = $db->prepare("SELECT cr.*, u.nom as sender_name, u.prenom as sender_prenom 
                      FROM contact_requests cr 
                      LEFT JOIN users u ON cr.user_id = u.id
                      WHERE cr.target_company_id = :cid 
                      OR (u.company_id = :cid AND cr.user_type = 'enterprise')
                      ORDER BY cr.created_at DESC");
$stmt->execute([':cid' => $company_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Entreprise - StageMatch</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/global.css"/>
    <link rel="stylesheet" href="../css/dashboards.css"/>
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/global.js" defer></script>
</head>
<body class="<?php include __DIR__ . '/../include/theme_body.php'; ?> bg-gray-50">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen overflow-y-auto md:ml-64">
            <!-- Mobile Toggle -->
            <div class="md:hidden bg-white p-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <span class="font-bold text-blue-600">StageMatch Admin</span>
                <button id="sidebarToggle" class="text-gray-700 p-1"><i class="fas fa-bars text-xl"></i></button>
            </div>
            
            <div class="max-w-6xl mx-auto px-6 py-10">
                <div class="mb-10">
                    <h1 class="text-4xl font-extrabold text-gray-900 mb-2 flex items-center gap-3">
                        <i class="fas fa-headset text-blue-600"></i> Centre de Support
                    </h1>
                    <p class="text-gray-500 font-medium">Gérez et répondez aux messages des étudiants concernant vos offres.</p>
                </div>

                <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm min-h-[500px]">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-2xl font-black text-slate-900 flex items-center gap-3">
                            <i class="fas fa-inbox text-blue-600"></i> Dossiers & Messages Support
                        </h2>
                        <button onclick="scrollToMessagesList()" class="px-6 py-3 bg-gray-100 hover:bg-gray-900 hover:text-white text-gray-700 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-sm flex items-center gap-2">
                             <i class="fas fa-envelope"></i> Message
                        </button>
                    </div>

                    <?php if (empty($requests)): ?>
                        <div class="flex flex-col items-center justify-center p-12 text-center h-full">
                            <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                            <h3 class="text-xl font-bold text-gray-800">Aucun message</h3>
                            <p class="text-gray-500 mt-2">Vous n'avez reçu aucune demande de support pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- List -->
                            <div class="space-y-4 max-h-[600px] overflow-y-auto px-2 custom-scrollbar" id="messagesListContainer">
                                <?php foreach ($requests as $req): 
                                    $is_internal = ($req['user_type'] === 'enterprise');
                                    $sender_disp = $is_internal ? 'INTERNE' : 'ÉTUDIANT';
                                    $sender_color = $is_internal ? 'text-purple-600 bg-purple-50' : 'text-blue-600 bg-blue-50';
                                ?>
                                    <div class="p-5 border border-gray-100 rounded-2xl bg-gray-50 hover:bg-white hover:shadow-md transition-all cursor-pointer group relative <?php echo ($req['status'] === 'pending') ? 'border-l-4 border-l-red-400' : ''; ?>" 
                                         onclick="openAdminReplyInline(<?php echo $req['id']; ?>, '<?php echo htmlspecialchars($req['sender_prenom'] . ' ' . $req['sender_name']); ?>', this)">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-tighter <?php echo $sender_color; ?> mb-1 inline-block"><?php echo $sender_disp; ?></span>
                                                <h3 class="font-bold text-gray-900 text-sm group-hover:text-blue-600 transition-colors"><?php echo htmlspecialchars($req['title']); ?></h3>
                                            </div>
                                            <?php if ($req['status'] === 'pending'): ?>
                                                <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">
                                            De: <?php echo htmlspecialchars($req['sender_prenom'] . ' ' . $req['sender_name']); ?> • <?php echo date('d M Y', strtotime($req['created_at'])); ?>
                                        </p>
                                        <div class="text-[11px] text-gray-500 line-clamp-2 italic">"<?php echo htmlspecialchars($req['message']); ?>"</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Inline Reply Area -->
                            <div id="adminInlineReplyArea" class="hidden animate-in fade-in slide-in-from-right duration-500 border-l border-gray-100 pl-8">
                                <div class="flex justify-between items-start mb-6">
                                    <div>
                                        <h3 class="text-xl font-black text-gray-900 leading-tight">Répondre à <span id="displayTargetName" class="text-blue-600"></span></h3>
                                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1 italic">Votre message sera visible par l'étudiant</p>
                                    </div>
                                    <button onclick="closeAdminInlineReply()" class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 hover:text-red-500 transition-all flex items-center justify-center"><i class="fas fa-times"></i></button>
                                </div>

                                <div id="adminChatHistory" class="space-y-4 mb-6 max-h-[350px] overflow-y-auto px-2 custom-scrollbar"></div>

                                <form id="adminReplyFormInline" class="space-y-4">
                                    <input type="hidden" id="adminReplyRequestId" name="request_id">
                                    <div class="relative">
                                        <textarea name="reply_message" id="adminReplyTextarea" rows="4" required class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all resize-none shadow-sm" placeholder="Rédigez votre réponse ici..."></textarea>
                                        <div class="absolute bottom-4 right-4 text-[9px] text-gray-300 font-bold">Manager Mode</div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" id="btnAdminSendReplyInline" class="px-8 py-3.5 bg-gray-900 text-white font-black rounded-xl hover:bg-black hover:-translate-y-1 transition-all shadow-xl flex items-center gap-2 text-sm">
                                            <i class="fas fa-paper-plane"></i> Envoyer la réponse
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Reply Modal -->
            <div id="replyModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
                <div class="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl relative">
                    <button class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-gray-100 rounded-full transition-colors" onclick="closeReplyModal()"><i class="fas fa-times"></i></button>
                    <h3 class="text-xl font-black text-gray-900 mb-2">Répondre à <span id="replyStudentName" class="text-blue-600"></span></h3>
                    <p class="text-sm text-gray-500 mb-6">Votre réponse sera envoyée directement à l'étudiant.</p>
                    <form id="replyForm" class="space-y-4">
                        <input type="hidden" id="replyRequestId" name="request_id">
                        <textarea name="reply_message" id="replyTextarea" rows="5" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all resize-none" placeholder="Rédigez votre réponse ici..."></textarea>
                        <div class="flex justify-end pt-2">
                            <button type="submit" id="btnSendReply" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-black hover:-translate-y-1 transition-all shadow-xl flex items-center gap-2">
                                <i class="fas fa-paper-plane"></i> Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <script>
        function scrollToMessagesList() {
            const list = document.getElementById('messagesListContainer');
            if (list) {
                list.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function openAdminReplyInline(id, name, el) {
            const area = document.getElementById('adminInlineReplyArea');
            const history = document.getElementById('adminChatHistory');
            const targetName = document.getElementById('displayTargetName');
            const requestIdInput = document.getElementById('adminReplyRequestId');

            // Toggle active card
            document.querySelectorAll('#messagesListContainer .cursor-pointer').forEach(card => {
                card.classList.remove('ring-4', 'ring-blue-500/10', 'bg-white', 'shadow-xl');
                card.classList.add('bg-gray-50');
            });
            if (el && el.classList) {
                el.classList.add('ring-4', 'ring-blue-500/10', 'bg-white', 'shadow-xl');
                el.classList.remove('bg-gray-50');
            }

            targetName.textContent = name;
            requestIdInput.value = id;
            history.innerHTML = '<div class="flex justify-center p-8"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i></div>';
            area.classList.remove('hidden');

            fetch(`../api/get_support_thread.php?request_id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderAdminMessagesInline(data.messages);
                    }
                })
                .catch(err => {
                    history.innerHTML = '<p class="text-red-500 text-center font-bold">Erreur.</p>';
                });
        }

        function renderAdminMessagesInline(messages) {
            const history = document.getElementById('adminChatHistory');
            history.innerHTML = '';
            messages.forEach(msg => {
                const isAdmin = (msg.sender_type === 'admin');
                const bubbleClass = isAdmin ? 'bg-blue-600 text-white rounded-l-2xl rounded-tr-2xl ml-auto' : 'bg-gray-100 text-gray-800 rounded-r-2xl rounded-tl-2xl mr-auto';
                const alignmentClass = isAdmin ? 'flex justify-end' : 'flex justify-start';
                const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                history.innerHTML += `<div class="${alignmentClass} mb-4"><div class="max-w-[85%] ${bubbleClass} p-4 shadow-sm relative"><p class="text-sm font-medium leading-relaxed">${msg.message_text.replace(/\n/g, '<br>')}</p><span class="block text-[8px] mt-2 font-black uppercase tracking-widest text-right opacity-70">${time}</span></div></div>`;
            });
            history.scrollTop = history.scrollHeight;
        }

        function closeAdminInlineReply() {
            document.getElementById('adminInlineReplyArea').classList.add('hidden');
            document.querySelectorAll('#messagesListContainer .ring-4').forEach(card => {
                card.classList.remove('ring-4', 'ring-blue-500/10', 'shadow-xl');
                card.classList.add('bg-gray-50');
            });
        }

        document.getElementById('adminReplyFormInline').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnAdminSendReplyInline');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Envoi...';
            btn.disabled = true;

            const formData = new FormData(this);

            fetch('../api/admin_reply_support.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const id = document.getElementById('adminReplyRequestId').value;
                    const name = document.getElementById('displayTargetName').textContent;
                    const activeCard = document.querySelector('#messagesListContainer .ring-4');
                    openAdminReplyInline(id, name, activeCard); // Refresh
                    document.getElementById('adminReplyTextarea').value = '';
                } else {
                    alert(data.message || 'Erreur lors de l\'envoi.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erreur réseau.');
            })
            .finally(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>
