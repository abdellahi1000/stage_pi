<?php
/**
 * export_recrutement.php
 * Export all candidatures for the current enterprise in CSV / Excel-compatible CSV / printable HTML (for PDF),
 * and persist the exported snapshot into a dedicated MySQL table per export.
 */
session_start();
require_once __DIR__ . '/../include/db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'entreprise' && $_SESSION['user_type'] !== 'admin')) {
    http_response_code(403);
    echo 'Accès refusé';
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$format = isset($_GET['format']) ? strtolower(trim($_GET['format'])) : 'csv';
if (!in_array($format, ['csv', 'excel', 'pdf'], true)) {
    $format = 'csv';
}

$database = new Database();
$db = $database->getConnection();

// Fetch candidatures for this enterprise, with notes
$sql = "SELECT 
            c.id AS candidature_id,
            u.id AS student_user_id,
            u.nom,
            u.prenom,
            u.email,
            u.telephone,
            o.titre AS position_applied,
            c.date_candidature,
            LOWER(c.statut) AS raw_status,
            GROUP_CONCAT(n.note ORDER BY n.date_note SEPARATOR ' | ') AS notes
        FROM candidatures c
        INNER JOIN users u ON c.user_id = u.id
        INNER JOIN offres_stage o ON c.offre_id = o.id
        LEFT JOIN notes_candidatures n ON n.candidature_id = c.id
        WHERE o.user_id = :uid
        GROUP BY c.id, u.id, u.nom, u.prenom, u.email, u.telephone, o.titre, c.date_candidature, c.statut
        ORDER BY c.date_candidature DESC";

$stmt = $db->prepare($sql);
$stmt->execute([':uid' => $user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group rows by logical status
$groups = [
    'accepted' => [],
    'rejected' => [],
    'pending'  => [],
];

foreach ($rows as $row) {
    $s = $row['raw_status'];
    if ($s === 'accepted' || $s === 'accepte') {
        $group = 'accepted';
    } elseif ($s === 'rejected' || $s === 'refuse') {
        $group = 'rejected';
    } else {
        // pending / en_attente / vue / closed / others
        $group = 'pending';
    }

    $groups[$group][] = [
        'nom'               => $row['nom'],
        'prenom'            => $row['prenom'],
        'email'             => $row['email'],
        'telephone'         => $row['telephone'],
        'position'          => $row['position_applied'],
        'date_candidature'  => $row['date_candidature'],
        'source'            => 'StageMatch',
        'notes'             => $row['notes'] ?? '',
        'group_status'      => $group,
        'candidature_id'    => (int) $row['candidature_id'],
        'student_user_id'   => (int) $row['student_user_id'],
    ];
}

// Persist snapshot into its own table
$timestamp = date('Ymd_His');
$base_table = 'recruitment_export_' . $user_id . '_' . $timestamp;
$table_name = preg_replace('/[^A-Za-z0-9_]/', '_', $base_table);

try {
    $db->exec("CREATE TABLE `$table_name` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exported_at DATETIME NOT NULL,
        entreprise_user_id INT NOT NULL,
        group_status ENUM('accepted','rejected','pending') NOT NULL,
        candidature_id INT NOT NULL,
        student_user_id INT NOT NULL,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        telephone VARCHAR(50) DEFAULT NULL,
        position_applied VARCHAR(255) NOT NULL,
        date_candidature DATETIME NOT NULL,
        source VARCHAR(100) DEFAULT 'StageMatch',
        notes TEXT NULL,
        INDEX idx_group (group_status),
        INDEX idx_student (student_user_id),
        INDEX idx_candidature (candidature_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $insert_sql = "INSERT INTO `$table_name`
        (exported_at, entreprise_user_id, group_status, candidature_id, student_user_id, nom, prenom, email, telephone, position_applied, date_candidature, source, notes)
        VALUES
        (:exported_at, :entreprise_user_id, :group_status, :candidature_id, :student_user_id, :nom, :prenom, :email, :telephone, :position_applied, :date_candidature, :source, :notes)";
    $insert_stmt = $db->prepare($insert_sql);

    $exported_at = date('Y-m-d H:i:s');
    foreach ($groups as $gkey => $items) {
        foreach ($items as $r) {
            $insert_stmt->execute([
                ':exported_at'       => $exported_at,
                ':entreprise_user_id'=> $user_id,
                ':group_status'      => $gkey,
                ':candidature_id'    => $r['candidature_id'],
                ':student_user_id'   => $r['student_user_id'],
                ':nom'               => $r['nom'],
                ':prenom'            => $r['prenom'],
                ':email'             => $r['email'],
                ':telephone'         => $r['telephone'],
                ':position_applied'  => $r['position'],
                ':date_candidature'  => $r['date_candidature'],
                ':source'            => $r['source'],
                ':notes'             => $r['notes'],
            ]);
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Erreur lors de la création de la table d\'export : ' . htmlspecialchars($e->getMessage());
    exit;
}

// Helper to flatten groups into rows for export
$headers = ['Nom', 'Prénom', 'Email', 'Téléphone', 'Poste', 'Date Candidature', 'Source', 'Notes', 'Statut'];

function sm_output_csv(string $filename, array $groups, array $headers, bool $for_excel = false): void {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    // Optional BOM for Excel compatibility
    if ($for_excel) {
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    }

    foreach (['accepted' => 'ACCEPTED', 'rejected' => 'REJECTED', 'pending' => 'PENDING'] as $gkey => $label) {
        fputcsv($out, []);
        fputcsv($out, ["=== $label ==="]);
        fputcsv($out, $headers);
        foreach ($groups[$gkey] as $r) {
            fputcsv($out, [
                $r['nom'],
                $r['prenom'],
                $r['email'],
                $r['telephone'],
                $r['position'],
                $r['date_candidature'],
                $r['source'],
                $r['notes'],
                strtoupper($r['group_status']),
            ]);
        }
    }
    fclose($out);
    exit;
}

if ($format === 'csv') {
    sm_output_csv('recrutement_export_' . $timestamp . '.csv', $groups, $headers, false);
}

if ($format === 'excel') {
    if (!class_exists('ZipArchive')) {
        // Fallback: still export CSV but with .xlsx so user can open it manually
        sm_output_csv('recrutement_export_' . $timestamp . '.xlsx', $groups, $headers, true);
    }

    $zip = new ZipArchive();
    $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');
    if ($zip->open($tmpFile, ZipArchive::OVERWRITE) !== true) {
        sm_output_csv('recrutement_export_' . $timestamp . '.xlsx', $groups, $headers, true);
    }

    // [Content_Types].xml
    $contentTypes = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '</Types>';
    $zip->addFromString('[Content_Types].xml', $contentTypes);

    // _rels/.rels
    $rels = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>';
    $zip->addFromString('_rels/.rels', $rels);

    // xl/_rels/workbook.xml.rels
    $wbRels = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
        . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>'
        . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/>'
        . '</Relationships>';
    $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

    // xl/workbook.xml
    $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
        . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets>'
        . '<sheet name="Accepted" sheetId="1" r:id="rId1"/>'
        . '<sheet name="Rejected" sheetId="2" r:id="rId2"/>'
        . '<sheet name="Pending" sheetId="3" r:id="rId3"/>'
        . '</sheets>'
        . '</workbook>';
    $zip->addFromString('xl/workbook.xml', $workbookXml);

    // Helper to build sheet XML with inline strings
    $buildSheet = function (array $rows, array $headers) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>';

        // Header row
        $xml .= '<row r="1">';
        $colIndex = 0;
        foreach ($headers as $header) {
            $colIndex++;
            $xml .= '<c t="inlineStr"><is><t>' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</t></is></c>';
        }
        $xml .= '</row>';

        $rowNum = 1;
        foreach ($rows as $r) {
            $rowNum++;
            $cells = [
                $r['nom'],
                $r['prenom'],
                $r['email'],
                $r['telephone'],
                $r['position'],
                $r['date_candidature'],
                $r['source'],
                $r['notes'],
                strtoupper($r['group_status']),
            ];
            $xml .= '<row r="' . $rowNum . '">';
            foreach ($cells as $value) {
                $xml .= '<c t="inlineStr"><is><t>'
                    . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8')
                    . '</t></is></c>';
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData></worksheet>';
        return $xml;
    };

    $zip->addFromString('xl/worksheets/sheet1.xml', $buildSheet($groups['accepted'], $headers));
    $zip->addFromString('xl/worksheets/sheet2.xml', $buildSheet($groups['rejected'], $headers));
    $zip->addFromString('xl/worksheets/sheet3.xml', $buildSheet($groups['pending'], $headers));

    $zip->close();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="recrutement_export_' . $timestamp . '.xlsx"');
    header('Content-Length: ' . filesize($tmpFile));
    readfile($tmpFile);
    @unlink($tmpFile);
    exit;
}

// "PDF" export as printable HTML document (user can save as PDF via navigateur)
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="recrutement_export_' . $timestamp . '.html"');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Recrutement - <?= htmlspecialchars($exported_at) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin-bottom: 10px; }
        h2 { font-size: 16px; margin-top: 24px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        .group-accepted th { background: #ecfdf5; color: #166534; }
        .group-rejected th { background: #fef2f2; color: #991b1b; }
        .group-pending th  { background: #fffbeb; color: #92400e; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 999px; font-size: 10px; font-weight: bold; }
        .badge-accepted { background: #bbf7d0; color: #166534; }
        .badge-rejected { background: #fecaca; color: #991b1b; }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        hr { border: none; border-top: 2px solid #e5e7eb; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Rapport de recrutement</h1>
    <p>Date d'export : <?= htmlspecialchars($exported_at) ?> &mdash; Entreprise ID #<?= (int) $user_id ?></p>

    <?php
    $labels = [
        'accepted' => ['label' => 'Candidats Acceptés', 'class' => 'group-accepted', 'badge' => 'badge-accepted'],
        'rejected' => ['label' => 'Candidats Refusés', 'class' => 'group-rejected', 'badge' => 'badge-rejected'],
        'pending'  => ['label' => 'Candidats en Attente', 'class' => 'group-pending',  'badge' => 'badge-pending'],
    ];

    $first = true;
    foreach ($labels as $gkey => $cfg):
        if (!$first) {
            echo '<hr />';
        }
        $first = false;
        $items = $groups[$gkey];
    ?>
        <h2><?= htmlspecialchars($cfg['label']) ?> (<?= count($items) ?>)</h2>
        <table class="<?= $cfg['class'] ?>">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Poste</th>
                <th>Date Candidature</th>
                <th>Source</th>
                <th>Notes</th>
                <th>Statut</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="9">Aucun candidat dans ce groupe.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nom']) ?></td>
                        <td><?= htmlspecialchars($r['prenom']) ?></td>
                        <td><?= htmlspecialchars($r['email']) ?></td>
                        <td><?= htmlspecialchars($r['telephone']) ?></td>
                        <td><?= htmlspecialchars($r['position']) ?></td>
                        <td><?= htmlspecialchars($r['date_candidature']) ?></td>
                        <td><?= htmlspecialchars($r['source']) ?></td>
                        <td><?= htmlspecialchars($r['notes']) ?></td>
                        <td><span class="badge <?= $cfg['badge'] ?>"><?= strtoupper($r['group_status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</body>
</html>
