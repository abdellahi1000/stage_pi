<?php
require_once '../include/session.php';
require_once '../include/db_connect.php';
check_auth('entreprise');

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];
$request_id = $_GET['id'] ?? null;

if (!$request_id) {
    header('Location: contact.php');
    exit;
}

// 1. Fetch request details and verify ownership/target
$stmt = $db->prepare("SELECT cr.*, 
                             u_sender.nom as sender_nom, u_sender.prenom as sender_prenom,
                             u_target.nom as target_nom
                      FROM contact_requests cr 
                      LEFT JOIN users u_sender ON cr.user_id = u_sender.id 
                      LEFT JOIN users u_target ON cr.target_company_id = u_target.id
                      WHERE cr.id = ? AND (cr.user_id = ? OR cr.target_company_id = ?)");
$stmt->execute([$request_id, $user_id, $user_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    echo "Accès refusé ou discussion introuvable.";
    exit;
}

// 2. Mark as read
if ($request['has_new_reply']) {
    $db->prepare("UPDATE contact_requests SET has_new_reply = 0 WHERE id = ?")->execute([$request_id]);
}

// 3. Fetch thread messages
$stmt_msg = $db->prepare("SELECT * FROM support_messages WHERE request_id = ? ORDER BY created_at ASC");
$stmt_msg->execute([$request_id]);
$messages = $stmt_msg->fetchAll(PDO::FETCH_ASSOC);

// 4. Handle duplication check
$has_original_in_thread = false;
foreach ($messages as $msg) {
    if (trim($msg['message_text']) === trim($request['message'])) {
        $has_original_in_thread = true;
        break;
    }
}

if (!$has_original_in_thread) {
    $first_msg = [
        'user_id' => $request['user_id'],
        'sender_type' => $request['user_type'] === 'student' ? 'user' : 'enterprise',
        'message_text' => $request['message'],
        'created_at' => $request['created_at']
    ];
    array_unshift($messages, $first_msg);
}

// 5. Check status
$has_reply = false;
foreach ($messages as $msg) {
    if ($msg['sender_type'] === 'support' || $msg['sender_type'] === 'admin') {
        $has_reply = true;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion Pro #<?php echo $request['id']; ?> - StageMatch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .chat-container { height: 500px; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .message-bubble { max-width: 75%; }
        
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .animate-msg { animation: fadeInScale 0.3s ease-out forwards; }
    </style>
</head>
<body class="bg-[#F8FAFC]">
    <div class="flex">
        <?php include '../include/sidebar.php'; ?>

        <main class="flex-1 min-h-screen md:ml-64 py-8 px-4 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <!-- Navigation -->
                <div class="flex items-center justify-between mb-8">
                    <a href="contact.php" class="flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors font-bold group">
                        <span class="w-10 h-10 bg-white shadow-sm border border-slate-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-chevron-left text-sm"></i>
                        </span>
                        Retour aux messages
                    </a>
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Discussion Pro: #<?php echo $request['id']; ?></span>
                        <div class="w-1 h-1 rounded-full bg-slate-200"></div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo date('d M Y', strtotime($request['created_at'])); ?></span>
                    </div>
                </div>

                <!-- Discussion Context -->
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100 mb-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4">
                        <?php if ($has_reply): ?>
                            <span class="px-4 py-2 bg-green-50 text-green-600 rounded-xl text-[10px] font-black uppercase tracking-widest border border-green-100/50">
                                <i class="fas fa-check-circle mr-1"></i> Traité
                            </span>
                        <?php else: ?>
                            <span class="px-4 py-2 bg-orange-50 text-orange-600 rounded-xl text-[10px] font-black uppercase tracking-widest border border-orange-100/50">
                                <i class="fas fa-clock mr-1"></i> En attente
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl shadow-inner border border-indigo-50">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-black text-slate-900 leading-tight"><?php echo htmlspecialchars($request['title']); ?></h1>
                            <p class="text-xs text-slate-500 font-bold mt-1 uppercase tracking-tight">
                                <?php if($request['user_type'] === 'student'): ?>
                                    <span class="text-blue-500">De:</span> <?php echo htmlspecialchars($request['sender_nom'] . ' ' . $request['sender_prenom']); ?> <span class="text-[10px] text-slate-300 ml-1">(Étudiant)</span>
                                <?php else: ?>
                                    <span class="text-indigo-500">Sujet:</span> Support Technique <span class="text-[10px] text-slate-300 ml-1">(Administration)</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Chat History -->
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 flex flex-col overflow-hidden">
                    <!-- Messages Area -->
                    <div class="chat-container overflow-y-auto p-8 custom-scrollbar space-y-6 bg-white" id="chatArea">
                        <?php foreach($messages as $msg): ?>
                            <?php 
                            $is_current_user = ($msg['user_id'] == $user_id);
                            $bubble_bg = $is_current_user ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/10' : 'bg-slate-100 text-slate-800 border border-slate-200';
                            $rounded = $is_current_user ? 'rounded-l-3xl rounded-tr-3xl' : 'rounded-r-3xl rounded-tl-3xl';
                            $alignment = $is_current_user ? 'flex justify-end' : 'flex justify-start';
                            ?>
                            <div class="<?php echo $alignment; ?> animate-msg">
                                <div class="message-bubble <?php echo $bubble_bg; ?> <?php echo $rounded; ?> p-5 relative group">
                                    <p class="text-sm font-medium leading-relaxed whitespace-pre-wrap"><?php echo htmlspecialchars($msg['message_text'] ?? $msg['message']); ?></p>
                                    <div class="flex items-center gap-2 mt-3 opacity-60">
                                        <span class="text-[9px] font-black uppercase tracking-widest"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
                                        <span class="w-1 h-1 bg-current opacity-30 rounded-full"></span>
                                        <span class="text-[9px] font-black uppercase tracking-widest">
                                            <?php 
                                            if ($is_current_user) echo 'Vous';
                                            elseif ($msg['sender_type'] === 'user') echo 'Étudiant';
                                            else echo 'Support';
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Input Section -->
                    <div class="p-6 bg-slate-50 border-t border-slate-100">
                        <form id="replyForm" class="relative">
                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                            <textarea name="message" rows="1" id="messageInput" required class="w-full pl-6 pr-32 py-4 bg-white border border-slate-200 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-sm placeholder:text-slate-400 resize-none overflow-hidden" placeholder="Écrire votre réponse..."></textarea>
                            
                            <div class="absolute right-3 top-2 bottom-2 flex items-center gap-2">
                                <button type="submit" class="h-full px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-lg shadow-indigo-500/20 transition-all flex items-center justify-center group">
                                    <i class="fas fa-paper-plane text-xs group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                                    <span class="ml-2 text-[10px] uppercase tracking-widest">Répondre</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const chatArea = document.getElementById('chatArea');
        const replyForm = document.getElementById('replyForm');
        const messageInput = document.getElementById('messageInput');

        const scrollToBottom = () => {
            chatArea.scrollTop = chatArea.scrollHeight;
        };

        window.onload = scrollToBottom;

        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        replyForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const msg = messageInput.value.trim();
            if (!msg) return;

            const btn = replyForm.querySelector('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
            btn.disabled = true;

            const formData = new FormData(replyForm);
            
            try {
                const res = await fetch('../api/send_reply.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                
                if (data.success) {
                    const now = new Date();
                    const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
                    
                    const newMsg = `
                        <div class="flex justify-end animate-msg">
                            <div class="message-bubble bg-indigo-600 text-white shadow-lg shadow-indigo-500/10 rounded-l-3xl rounded-tr-3xl p-5 relative group">
                                <p class="text-sm font-medium leading-relaxed whitespace-pre-wrap">${msg.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")}</p>
                                <div class="flex items-center gap-2 mt-3 opacity-60">
                                    <span class="text-[9px] font-black uppercase tracking-widest">${timeStr}</span>
                                    <span class="w-1 h-1 bg-current opacity-30 rounded-full"></span>
                                    <span class="text-[9px] font-black uppercase tracking-widest">Vous</span>
                                </div>
                            </div>
                        </div>
                    `;
                    chatArea.insertAdjacentHTML('beforeend', newMsg);
                    replyForm.reset();
                    messageInput.style.height = 'auto';
                    scrollToBottom();
                } else {
                    alert(data.message || 'Erreur lors de l\'envoi');
                }
            } catch (err) {
                alert('Erreur serveur');
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
