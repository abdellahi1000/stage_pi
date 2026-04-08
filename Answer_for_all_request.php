<?php
require_once 'include/session.php';
// Ensure only admins can access this page
if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'administrateur')) {
    // header('Location: login.php');
    // exit;
}

require_once 'include/db_connect.php';

$db = (new Database())->getConnection();

// Defensive: Ensure sender_type allows 'support'
try {
    $column = $db->query("SHOW COLUMNS FROM support_messages LIKE 'sender_type'")->fetch(PDO::FETCH_ASSOC);
    if ($column && strpos($column['Type'], "'support'") === false) {
        $db->exec("ALTER TABLE support_messages MODIFY COLUMN sender_type ENUM('user', 'admin', 'support') DEFAULT 'user'");
    }
    
    // Also ensure status exists
    $chkStatus = $db->query("SHOW COLUMNS FROM support_messages LIKE 'status'");
    if ($chkStatus && $chkStatus->rowCount() === 0) {
        $db->exec("ALTER TABLE support_messages ADD COLUMN status VARCHAR(20) DEFAULT 'unread'");
    }
} catch (PDOException $e) {}

// Handle Reply Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reply') {
        $request_id = $_POST['request_id'];
        $reply = $_POST['reply_content'];
        
        if (!empty($reply)) {
            $reqCheck = $db->prepare("SELECT user_id, user_type FROM contact_requests WHERE id = ?");
            $reqCheck->execute([$request_id]);
            $requestData = $reqCheck->fetch(PDO::FETCH_ASSOC);

            if ($requestData) {
                // Ensure status column exists and default to unread for support replies
                $ins = $db->prepare("INSERT INTO support_messages (request_id, user_id, sender_type, message_text, status) VALUES (?, ?, 'support', ?, 'unread')");
                if ($ins->execute([$request_id, $requestData['user_id'], $reply])) {
                    $upd = $db->prepare("UPDATE contact_requests SET status = 'answered', has_new_reply = 1, last_message_at = NOW() WHERE id = ?");
                    $upd->execute([$request_id]);
                    $_SESSION['success_msg'] = "Réponse envoyée avec succès !";
                }
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $request_id = $_POST['request_id'];
        $db->prepare("DELETE FROM support_messages WHERE request_id = ?")->execute([$request_id]);
        $db->prepare("DELETE FROM contact_requests WHERE id = ?")->execute([$request_id]);
        $_SESSION['success_msg'] = "Demande supprimée avec succès.";
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$success_msg = $_SESSION['success_msg'] ?? null;
unset($_SESSION['success_msg']);

// Fetch all requests
$stmt = $db->query("SELECT * FROM contact_requests ORDER BY created_at DESC");
$all_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For each request, fetch its messages
foreach ($all_requests as &$req) {
    $msgStmt = $db->prepare("SELECT * FROM support_messages WHERE request_id = ? ORDER BY created_at ASC");
    $msgStmt->execute([$req['id']]);
    $req['thread'] = $msgStmt->fetchAll(PDO::FETCH_ASSOC);
}

$problem_requests = array_filter($all_requests, function($r) { return $r['user_type'] === 'problem'; });
$student_requests = array_filter($all_requests, function($r) { return $r['user_type'] === 'student'; });
$enterprise_requests = array_filter($all_requests, function($r) { return $r['user_type'] === 'enterprise'; });
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Support - StageMatch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f3f4f6; 
        }
        .main-card {
            background: white;
            border-radius: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            padding: 3rem;
            border: 1px solid #f3f4f6;
        }
        .inner-request-card {
            padding-bottom: 2rem;
            margin-bottom: 3.5rem;
        }
        .inner-request-card:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .bubble {
            background-color: #f3f4f6;
            border-radius: 1.5rem;
            padding: 1.5rem 2rem;
            margin-bottom: 1rem;
        }
        .user-bubble { background-color: #f3f4f6; }
        .admin-bubble { background-color: #f3f4f6; margin-left: 2rem; }
        
        .tag {
            font-size: 9px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .tag-prob { background: #eef2ff; color: #6366f1; }
        .tag-sec { background: #f3f4f6; color: #4b5563; }
        .tag-pro { background: #1e293b; color: #fff; border-radius: 4px; padding: 2px 8px; font-size: 8px; }

        /* Custom Modal Styles - Match Native Dark Mode Browser Look */
        .modal-overlay { background: rgba(0,0,0,0.6); }
        .custom-modal {
            background: #202124;
            color: #bdc1c6;
            border-radius: 0.75rem;
            padding: 1.5rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 12px 15px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
        }
        .modal-title { color: #e8eaed; font-size: 1rem; font-weight: 700; margin-bottom: 0.75rem; }
        .modal-btn-ok { 
            background: #8ab4f8; 
            color: #202124; 
            padding: 8px 18px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 12px;
            transition: opacity 0.2s;
        }
        .modal-btn-cancel { 
            background: #174ea6; 
            color: #ffffff; 
            padding: 8px 18px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 12px;
            transition: opacity 0.2s;
        }
        
        /* Buttons Style */
        .btn-send-store { background: #312e81; color: white; border-radius: 12px; padding: 12px 24px; }
        .btn-delete { background: #fff1f2; color: #e11d48; border-radius: 12px; padding: 12px 24px; }
        
        /* Success Banner Style */
        .success-banner {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 15px;
        }
        .success-icon {
            width: 24px;
            height: 24px;
            background: #dcfce7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 pb-20">

    <div class="max-w-6xl mx-auto px-4 pt-12">
        <header class="mb-12">
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Réponses aux Demandes</h1>
            <p class="text-slate-500 font-medium italic">Gérez toutes les demandes de contact de la plateforme.</p>
        </header>

        <?php if ($success_msg): ?>
            <div class="mb-10 success-banner">
                <div class="success-icon">
                    <i class="fas fa-check text-[10px] text-green-700"></i>
                </div>
                <?= $success_msg ?>
            </div>
        <?php endif; ?>

        <!-- SECTION 1: DEMAND PROBLEM -->
        <?php if (!empty($problem_requests)): ?>
        <div class="mb-14">
            <div class="flex items-center gap-4 mb-6 px-4">
                <div class="w-10 h-10 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="text-2xl font-black text-slate-900">Demand Problem</h2>
                <span class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 text-xs font-black flex items-center justify-center ml-auto"><?= count($problem_requests) ?></span>
            </div>
            
            <div class="main-card">
                <?php foreach ($problem_requests as $req): ?>
                    <div class="inner-request-card">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-xl font-black text-slate-800 mb-2"><?= htmlspecialchars($req['title'] ?? 'security') ?></h3>
                                <div class="flex items-center gap-4 text-sm font-bold">
                                    <span class="text-rose-400 flex items-center gap-1"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($req['phone'] ?? '') ?></span>
                                    <span class="text-blue-400 flex items-center gap-1"><i class="fas fa-envelope"></i> <?= htmlspecialchars($req['email'] ?? '') ?></span>
                                    <span class="tag tag-sec">SÉCURITÉ</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 tracking-wider"><?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></span>
                        </div>
                        
                        <div class="bubble user-bubble">
                            <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($req['message'] ?? '')) ?></p>
                        </div>

                        <?php foreach ($req['thread'] as $msg): ?>
                            <div class="bubble <?= $msg['sender_type'] === 'support' || $msg['sender_type'] === 'admin' ? 'admin-bubble' : 'user-bubble' ?>">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                        <?= ($msg['sender_type'] === 'support' || $msg['sender_type'] === 'admin') ? 'Support Admin' : 'Utilisateur' ?> • <?= date('H:i', strtotime($msg['created_at'])) ?>
                                    </span>
                                </div>
                                <p class="text-slate-600 text-sm"><?= nl2br(htmlspecialchars($msg['message_text'] ?? '')) ?></p>
                            </div>
                        <?php endforeach; ?>
                        
                        <form method="POST" id="form-<?= $req['id'] ?>" class="mt-8">
                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                            <textarea name="reply_content" rows="2" placeholder="Répondre..." class="w-full px-8 py-5 bg-white border border-slate-100 rounded-[24px] outline-none focus:ring-4 focus:ring-rose-500/5 text-sm font-medium transition-all shadow-sm"></textarea>
                            <div class="flex justify-end gap-3 mt-4">
                                <button type="button" onclick="showCustomConfirm(<?= $req['id'] ?>)" class="btn-delete font-bold flex items-center gap-2">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                                <button type="submit" name="action" value="reply" class="btn-send-store font-bold flex items-center gap-2 shadow-lg">
                                    <i class="fas fa-paper-plane"></i> Send & Store
                                </button>
                            </div>
                            <input type="hidden" name="action" id="action-<?= $req['id'] ?>" value="reply">
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- SECTION 2: DEMAND ETUDIANT -->
        <?php if (!empty($student_requests)): ?>
        <div class="mb-14">
            <div class="flex items-center gap-4 mb-6 px-4">
                <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h2 class="text-2xl font-black text-slate-900">Demand Etudiant / Stagiaire</h2>
                <span class="w-8 h-8 rounded-full bg-blue-50 text-blue-500 text-xs font-black flex items-center justify-center ml-auto"><?= count($student_requests) ?></span>
            </div>
            
            <div class="main-card">
                <?php foreach ($student_requests as $req): ?>
                    <div class="inner-request-card">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-xl font-black text-slate-800 mb-1"><?= htmlspecialchars($req['name'] ?? '') ?></h3>
                                <div class="flex items-center gap-3 text-sm font-bold">
                                    <span class="text-slate-400 font-bold">cv</span>
                                    <span class="text-slate-500 font-bold"><?= htmlspecialchars($req['email'] ?? '') ?></span>
                                    <span class="tag tag-prob">APPLICATION PROBLEM</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 tracking-wider uppercase"><?= date('d M, Y', strtotime($req['created_at'])) ?></span>
                        </div>
                        
                        <div class="bubble user-bubble">
                            <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($req['message'] ?? '')) ?></p>
                        </div>

                        <?php foreach ($req['thread'] as $msg): ?>
                            <div class="bubble <?= $msg['sender_type'] === 'admin' ? 'admin-bubble' : 'user-bubble' ?>">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                        <?= $msg['sender_type'] === 'admin' ? 'Support Admin' : 'Etudiant' ?> • <?= date('H:i', strtotime($msg['created_at'])) ?>
                                    </span>
                                </div>
                                <p class="text-slate-600 text-sm"><?= nl2br(htmlspecialchars($msg['message_text'] ?? '')) ?></p>
                            </div>
                        <?php endforeach; ?>
                        
                        <form method="POST" id="form-<?= $req['id'] ?>" class="mt-8">
                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                            <textarea name="reply_content" rows="2" placeholder="Répondre à l'étudiant..." class="w-full px-8 py-5 bg-white border border-slate-100 rounded-[24px] outline-none focus:ring-4 focus:ring-blue-500/5 text-sm font-medium transition-all shadow-sm"></textarea>
                            <div class="flex justify-end gap-3 mt-4">
                                <button type="button" onclick="showCustomConfirm(<?= $req['id'] ?>)" class="btn-delete font-bold flex items-center gap-2">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                                <button type="submit" name="action" value="reply" class="btn-send-store font-bold flex items-center gap-2 shadow-lg">
                                    <i class="fas fa-paper-plane"></i> Send & Store
                                </button>
                            </div>
                            <input type="hidden" name="action" id="action-<?= $req['id'] ?>" value="reply">
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- SECTION 3: DEMAND ENTERPRISE -->
        <?php if (!empty($enterprise_requests)): ?>
        <div class="mb-14">
            <div class="flex items-center gap-4 mb-6 px-4">
                <div class="w-10 h-10 bg-indigo-50 text-indigo-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-building"></i>
                </div>
                <h2 class="text-2xl font-black text-slate-900">Demand Enterprise</h2>
                <span class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-500 text-xs font-black flex items-center justify-center ml-auto"><?= count($enterprise_requests) ?></span>
            </div>
            
            <div class="main-card">
                <?php foreach ($enterprise_requests as $req): ?>
                    <div class="inner-request-card">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="tag tag-pro">PRO</span>
                                    <h3 class="text-xl font-black text-slate-800"><?= htmlspecialchars($req['email'] ?? '') ?></h3>
                                </div>
                                <div class="flex items-center gap-3 text-sm font-bold">
                                    <span class="text-slate-400">cv</span>
                                    <span class="tag tag-sec">SÉCURITÉ</span>
                                    <span class="tag tag-prob">APPLICATION PROBLEM</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 tracking-wider uppercase"><?= date('d M, Y', strtotime($req['created_at'])) ?></span>
                        </div>

                        <div class="bubble user-bubble">
                            <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($req['message'] ?? '')) ?></p>
                        </div>

                        <?php foreach ($req['thread'] as $msg): ?>
                            <div class="bubble <?= $msg['sender_type'] === 'admin' ? 'admin-bubble' : 'user-bubble' ?>">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                        <?= $msg['sender_type'] === 'admin' ? 'Support Admin' : 'Enterprise' ?> • <?= date('H:i', strtotime($msg['created_at'])) ?>
                                    </span>
                                </div>
                                <p class="text-slate-600 text-sm"><?= nl2br(htmlspecialchars($msg['message_text'] ?? '')) ?></p>
                            </div>
                        <?php endforeach; ?>
                        
                        <form method="POST" id="form-<?= $req['id'] ?>" class="mt-8">
                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                            <textarea name="reply_content" rows="2" placeholder="Répondre à l'entreprise..." class="w-full px-8 py-5 bg-white border border-slate-100 rounded-[24px] outline-none focus:ring-4 focus:ring-indigo-500/5 text-sm font-medium transition-all shadow-sm"></textarea>
                            <div class="flex justify-end gap-3 mt-4">
                                <button type="button" onclick="showCustomConfirm(<?= $req['id'] ?>)" class="btn-delete font-bold flex items-center gap-2">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                                <button type="submit" name="action" value="reply" class="btn-send-store font-bold flex items-center gap-2 shadow-lg">
                                    <i class="fas fa-paper-plane"></i> Send & Store
                                </button>
                            </div>
                            <input type="hidden" name="action" id="action-<?= $req['id'] ?>" value="reply">
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Scroll to top -->
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-8 right-8 w-14 h-14 bg-white shadow-2xl rounded-2xl flex items-center justify-center text-slate-400 hover:text-slate-900 border border-slate-100 transition-all z-50">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Custom Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-[100] modal-overlay hidden flex items-center justify-center px-4">
        <div class="custom-modal">
            <h2 class="modal-title">localhost indique</h2>
            <p class="mb-8">Supprimer définitivement cette demande ?</p>
            <div class="flex justify-end gap-4">
                <button id="modalOk" class="modal-btn-ok">OK</button>
                <button id="modalCancel" class="modal-btn-cancel">Annuler</button>
            </div>
        </div>
    </div>

    <script>
        let pendingRequestId = null;

        function showCustomConfirm(id) {
            pendingRequestId = id;
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        document.getElementById('modalCancel').onclick = function() {
            document.getElementById('confirmModal').classList.add('hidden');
            pendingRequestId = null;
        }

        document.getElementById('modalOk').onclick = function() {
            if (pendingRequestId) {
                const form = document.getElementById('form-' + pendingRequestId);
                const actionInput = document.getElementById('action-' + pendingRequestId);
                actionInput.value = 'delete';
                form.submit();
            }
        }
    </script>
</body>
</html>
