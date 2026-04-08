<?php 
require_once '../include/session.php';
require_once '../include/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
    die("Accès refusé. Réservé aux administrateurs.");
}

$database = new Database();
$db = $database->getConnection();

// Fetch companies pending approval (email_verified)
$query = "SELECT id, nom, email, telephone, address, commercial_registration_number, tax_identification_number, industry_sector, company_size, year_established, commercial_registry_doc, tax_document, official_stamp_doc, account_status 
          FROM users 
          WHERE type_compte = 'entreprise' AND account_status IN ('email_verified', 'pending')
          ORDER BY created_at DESC";
$stmt = $db->query($query);
$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Approbation Entreprises</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <h1 class="text-3xl font-extrabold text-blue-900"><i class="fas fa-shield-alt mr-3"></i>Administration - Validation des Entreprises</h1>
            <a href="messages.php" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all">
                <i class="fas fa-envelope mr-3"></i> Messages Support
            </a>
        </div>

        <?php if (count($companies) === 0): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded text-green-700">
                Aucune entreprise en attente de validation.
            </div>
        <?php else: ?>
            <div class="bg-white shadow rounded-lg overflow-hidden overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entreprise</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Infos Légales</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documents</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut DB</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($companies as $c): ?>
                            <tr id="row-<?= $c['id'] ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($c['nom']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($c['industry_sector']) ?> • <?= htmlspecialchars($c['company_size']) ?> employés</div>
                                    <div class="text-xs text-gray-400">Créée en <?= htmlspecialchars($c['year_established']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($c['email']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($c['telephone']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">RC: <span class="font-mono"><?= htmlspecialchars($c['commercial_registration_number']) ?></span></div>
                                    <div class="text-sm text-gray-900">NIF: <span class="font-mono"><?= htmlspecialchars($c['tax_identification_number']) ?></span></div>
                                    <div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($c['address']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                    <a href="../<?= htmlspecialchars($c['commercial_registry_doc']) ?>" target="_blank" class="block hover:underline mb-1"><i class="fas fa-file-pdf text-red-500 mr-1"></i>Registre</a>
                                    <a href="../<?= htmlspecialchars($c['tax_document']) ?>" target="_blank" class="block hover:underline mb-1"><i class="fas fa-file-pdf text-red-500 mr-1"></i>NIF Doc</a>
                                    <a href="../<?= htmlspecialchars($c['official_stamp_doc']) ?>" target="_blank" class="block hover:underline"><i class="fas fa-file-pdf text-red-500 mr-1"></i>Cachet/Signature</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($c['account_status'] === 'email_verified'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Email Vérifié</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente email</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="updateCompanyStatus(<?= $c['id'] ?>, 'approve')" class="bg-green-600 text-white px-3 py-1 rounded shadow hover:bg-green-700 mr-2 transition">Approuver & Vérifier</button>
                                    <button onclick="updateCompanyStatus(<?= $c['id'] ?>, 'reject')" class="bg-red-600 text-white px-3 py-1 rounded shadow hover:bg-red-700 transition">Rejeter</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function updateCompanyStatus(companyId, action) {
        if (!confirm('Êtes-vous sûr de vouloir ' + (action === 'approve' ? 'approuver' : 'rejeter') + ' cette entreprise ?')) {
            return;
        }
        
        const fd = new FormData();
        fd.append('id', companyId);
        fd.append('action', action);

        fetch('approve_company.php', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Statut mis à jour avec succès.');
                document.getElementById('row-' + companyId).style.display = 'none';
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erreur de communication avec le serveur.');
        });
    }
    </script>
</body>
</html>
