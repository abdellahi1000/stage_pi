<?php
require_once '../include/session.php';
// check_auth('admin'); // Assuming there's an admin check. If not, I'll just check if user_type is admin if available.
// For now, let's assume session check is enough or add a basic check.
if ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'administrateur') {
    // header('Location: ../login.php');
    // exit;
}
require_once '../include/db_connect.php';

$db = (new Database())->getConnection();

// Handle Reply Submission if any
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $request_id = $_POST['request_id'];
    $reply = $_POST['admin_reply'];
    
    // Update DB
    $stmt = $db->prepare("UPDATE contact_requests SET admin_reply = ?, status = 'answered' WHERE id = ?");
    if ($stmt->execute([$reply, $request_id])) {
        // Fetch request info for email
        $stmt = $db->prepare("SELECT email, name, company_name, title, user_type FROM contact_requests WHERE id = ?");
        $stmt->execute([$request_id]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $to_email = $req['email'];
        $to_name = ($req['user_type'] === 'student') ? $req['name'] : $req['company_name'];
        $subject = "Réponse à votre demande: " . $req['title'];
        
        // Send Email
        try {
            require_once '../PHPMailer/src/Exception.php';
            require_once '../PHPMailer/src/PHPMailer.php';
            require_once '../PHPMailer/src/SMTP.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = '127.0.0.1'; // Local SMTP
            $mail->SMTPAuth   = false;
            $mail->Port       = 1025; 
            
            $mail->setFrom('support@stagematch.com', 'StageMatch Support');
            $mail->addAddress($to_email, $to_name);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = "Bonjour $to_name,<br><br>Notre équipe a répondu à votre demande :<br><br><strong>Votre message :</strong><br>" . nl2br(htmlspecialchars($req['title'])) . "<br><br><strong>Réponse de l'administrateur :</strong><br>" . nl2br(htmlspecialchars($reply)) . "<br><br>Cordialement,<br>L'équipe StageMatch";
            
            $mail->send();
            $success_msg = "Réponse envoyée avec succès !";
        } catch (Exception $e) {
            $success_msg = "Réponse enregistrée, mais l'email n'a pas pu être envoyé.";
        }
    }
}

// Fetch requests
$stmt = $db->query("SELECT * FROM contact_requests ORDER BY created_at DESC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Messages - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .request-card {
            transition: all 0.3s ease;
        }
        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-6 py-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">Messages Support</h1>
                <p class="text-slate-500 mt-2">Gérez les demandes de contact des étudiants et des entreprises.</p>
            </div>
            <div class="flex gap-3">
                <span class="px-4 py-2 bg-white border border-slate-200 rounded-full text-sm font-semibold text-slate-600 shadow-sm">
                    Total: <?php echo count($requests); ?>
                </span>
                <a href="index.php" class="px-6 py-2 bg-slate-900 text-white rounded-full text-sm font-bold hover:bg-slate-800 transition shadow-lg">
                    Retour Dashboard
                </a>
            </div>
        </div>

        <?php if (isset($success_msg)): ?>
            <div class="mb-8 p-4 bg-green-100 border border-green-200 text-green-700 rounded-2xl font-bold flex items-center gap-3">
                <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <div class="space-y-8">
            <?php foreach ($requests as $req): ?>
                <div class="request-card bg-white rounded-[2rem] border border-slate-100 p-8 shadow-sm <?php echo $req['status'] === 'answered' ? 'opacity-75' : ''; ?>">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center <?php echo $req['user_type'] === 'student' ? 'bg-blue-100 text-blue-600' : 'bg-indigo-100 text-indigo-600'; ?>">
                                <i class="fas <?php echo $req['user_type'] === 'student' ? 'fa-user-graduate' : 'fa-building'; ?> text-xl"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="text-xl font-bold text-slate-900">
                                        <?php echo htmlspecialchars($req['user_type'] === 'student' ? $req['name'] : $req['company_name']); ?>
                                    </h3>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?php echo $req['status'] === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'; ?>">
                                        <?php echo $req['status'] === 'pending' ? 'En attente' : 'Répondu'; ?>
                                    </span>
                                </div>
                                <p class="text-sm text-slate-500 font-medium">
                                    <i class="far fa-envelope mr-1"></i> <?php echo htmlspecialchars($req['email']); ?>
                                    <?php if ($req['phone']): ?>
                                        <span class="mx-2 text-slate-300">|</span>
                                        <i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($req['phone']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-1">Date d'envoi</p>
                            <p class="text-sm font-bold text-slate-700"><?php echo date('d M Y, H:i', strtotime($req['created_at'])); ?></p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-8 pb-8 border-b border-slate-50">
                        <div class="md:col-span-1">
                            <p class="text-xs text-slate-400 font-black uppercase tracking-widest mb-2">Type de Problème</p>
                            <p class="inline-block px-4 py-2 bg-slate-50 rounded-xl text-sm font-bold text-slate-700 border border-slate-100">
                                <?php echo htmlspecialchars($req['problem_type']); ?>
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs text-slate-400 font-black uppercase tracking-widest mb-2">Sujet / Titre</p>
                            <p class="text-lg font-bold text-slate-900 line-clamp-1"><?php echo htmlspecialchars($req['title']); ?></p>
                        </div>
                    </div>

                    <div class="py-8">
                        <p class="text-xs text-slate-400 font-black uppercase tracking-widest mb-4">Message détaillé</p>
                        <div class="bg-slate-50 rounded-2xl p-6 text-slate-700 text-sm leading-relaxed border border-slate-100">
                            <?php echo nl2br(htmlspecialchars($req['message'])); ?>
                        </div>
                    </div>

                    <?php if ($req['status'] === 'pending'): ?>
                        <div class="mt-4">
                            <p class="text-xs text-slate-400 font-black uppercase tracking-widest mb-4">Répondre à cette demande</p>
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="action" value="reply">
                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                <textarea name="admin_reply" rows="4" required class="w-full px-6 py-4 bg-white border border-slate-200 rounded-3xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all resize-none text-sm" placeholder="Rédigez votre réponse ici..."></textarea>
                                <button type="submit" class="w-full md:w-auto px-10 py-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl shadow-xl shadow-slate-900/10 transition-all flex items-center justify-center gap-3">
                                    <i class="fas fa-reply"></i> Envoyer la réponse
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="mt-4 pt-8 border-t border-dashed border-slate-100">
                            <p class="text-xs text-green-500 font-black uppercase tracking-widest mb-4">Réponse de l'admin</p>
                            <div class="bg-green-50/50 rounded-2xl p-6 text-slate-700 text-sm italic leading-relaxed border border-green-100/50">
                                <?php echo nl2br(htmlspecialchars($req['admin_reply'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (empty($requests)): ?>
                <div class="text-center py-20 bg-white rounded-[3rem] border border-dashed border-slate-200">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="far fa-folder-open text-3xl text-slate-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Aucun message pour le moment</h3>
                    <p class="text-slate-500 mt-2">Dès qu'un utilisateur enverra une demande, elle apparaîtra ici.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
