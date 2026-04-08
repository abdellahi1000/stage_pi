<?php
/**
 * Enterprise Profile Modal - Setup Verification Checklist
 * This file helps verify all changes are in place
 * 
 * Run this via: http://localhost/stage_pi/verify_enterprise_fix.php
 */

require_once __DIR__ . '/include/db_connect.php';

$checks = [];
$errors = [];
$warnings = [];

try {
    $db = (new Database())->getConnection();
    
    // Check 1: Database tables exist
    $tables_to_check = ['company_emails', 'company_phones', 'company_social_links'];
    foreach ($tables_to_check as $table) {
        try {
            $result = $db->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() > 0) {
                $checks[] = "✓ Table '$table' exists";
            } else {
                $errors[] = "✗ Table '$table' NOT FOUND - Run create_missing_tables.php";
            }
        } catch (Exception $e) {
            $errors[] = "✗ Error checking table '$table': " . $e->getMessage();
        }
    }
    
    // Check 2: Sample company has data
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM users WHERE type_compte = 'entreprise' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['cnt'] > 0) {
            $checks[] = "✓ Found " . $row['cnt'] . " enterprise accounts";
            
            // Get first enterprise ID
            $stmt = $db->prepare("SELECT id FROM users WHERE type_compte = 'entreprise' LIMIT 1");
            $stmt->execute();
            $enterprise = $stmt->fetch(PDO::FETCH_ASSOC);
            $ent_id = $enterprise['id'];
            
            // Check sample data
            $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM company_emails WHERE company_id = ?");
            $stmt->execute([$ent_id]);
            $emails = $stmt->fetch(PDO::FETCH_ASSOC);
            $email_count = $emails['cnt'] ?? 0;
            
            $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM company_phones WHERE company_id = ?");
            $stmt->execute([$ent_id]);
            $phones = $stmt->fetch(PDO::FETCH_ASSOC);
            $phone_count = $phones['cnt'] ?? 0;
            
            if ($email_count > 0) {
                $checks[] = "✓ Found " . $email_count . " email(s) in company_emails";
            } else {
                $warnings[] = "⚠ No sample emails found in company_emails (add via dashboard)";
            }
            
            if ($phone_count > 0) {
                $checks[] = "✓ Found " . $phone_count . " phone(s) in company_phones";
            } else {
                $warnings[] = "⚠ No sample phones found in company_phones (add via dashboard)";
            }
        } else {
            $warnings[] = "⚠ No enterprise accounts found";
        }
    } catch (Exception $e) {
        $errors[] = "✗ Error checking data: " . $e->getMessage();
    }
    
    // Check 3: File changes
    $files_to_check = [
        'api/entreprise_profile.php' => 'updated API',
        'students/company-profile.php' => 'updated UI',
        'create_missing_tables.php' => 'migration script'
    ];
    
    foreach ($files_to_check as $file => $desc) {
        if (file_exists(__DIR__ . '/' . $file)) {
            $checks[] = "✓ File exists: $file ($desc)";
        } else {
            $errors[] = "✗ File missing: $file";
        }
    }
    
} catch (Exception $e) {
    $errors[] = "Fatal error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Profile Modal - Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .check-item {
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Courier New', monospace;
        }
        .check-item.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .check-item.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .check-item.warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 15px;
        }
        .status-badge.passed {
            background: #d4edda;
            color: #155724;
        }
        .status-badge.failed {
            background: #f8d7da;
            color: #721c24;
        }
        .status-badge.warning {
            background: #fff3cd;
            color: #856404;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #1877f2;
            color: white;
        }
        .btn-primary:hover {
            background: #1665d3;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
        }
        @media (max-width: 600px) {
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Enterprise Profile Modal - Verification</h1>
            <p>Checking installation and setup status</p>
        </div>
        
        <div class="content">
            <?php if (!empty($errors) || !empty($warnings) || !empty($checks)): ?>
            
                <?php if (!empty($checks)): ?>
                <div class="section">
                    <div class="section-title">✓ Passed Checks (<?php echo count($checks); ?>)</div>
                    <?php foreach ($checks as $check): ?>
                        <div class="check-item success"><?php echo htmlspecialchars($check); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($warnings)): ?>
                <div class="section">
                    <div class="section-title">⚠ Warnings (<?php echo count($warnings); ?>)</div>
                    <?php foreach ($warnings as $warning): ?>
                        <div class="check-item warning"><?php echo htmlspecialchars($warning); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                <div class="section">
                    <div class="section-title">✗ Errors Found (<?php echo count($errors); ?>)</div>
                    <?php foreach ($errors as $error): ?>
                        <div class="check-item error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div style="text-align: center;">
                    <?php if (empty($errors)): ?>
                        <span class="status-badge passed">✓ All systems functional</span>
                    <?php else: ?>
                        <span class="status-badge failed">✗ Action required</span>
                    <?php endif; ?>
                </div>
                
                <div class="action-buttons">
                    <?php if (!empty($errors) || !empty($warnings)): ?>
                        <a href="create_missing_tables.php" class="btn btn-primary">Fix: Create Tables</a>
                    <?php endif; ?>
                    <a href="students/offres.php" class="btn btn-success">View Student Dashboard</a>
                </div>
                
            <?php else: ?>
                <p style="color: #666; text-align: center;">Unable to determine status. Please try again.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
