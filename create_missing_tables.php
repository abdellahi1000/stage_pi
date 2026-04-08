<?php
/**
 * Migration script to create missing enterprise contact tables
 * Run this once to set up the database schema
 */

require_once __DIR__ . '/include/db_connect.php';

try {
    $db = (new Database())->getConnection();
    
    echo "Creating missing tables...\n\n";
    
    // Create company_emails table
    $emailSQL = "CREATE TABLE IF NOT EXISTS `company_emails` (
      `id` int NOT NULL AUTO_INCREMENT,
      `company_id` int NOT NULL,
      `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `company_id` (`company_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $db->exec($emailSQL);
        echo "✓ company_emails table created/verified\n";
    } catch (PDOException $e) {
        echo "✗ Error creating company_emails: " . $e->getMessage() . "\n";
    }
    
    // Create company_phones table
    $phoneSQL = "CREATE TABLE IF NOT EXISTS `company_phones` (
      `id` int NOT NULL AUTO_INCREMENT,
      `company_id` int NOT NULL,
      `phone_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
      `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'telephone',
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `company_id` (`company_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $db->exec($phoneSQL);
        echo "✓ company_phones table created/verified\n";
    } catch (PDOException $e) {
        echo "✗ Error creating company_phones: " . $e->getMessage() . "\n";
    }
    
    // Create company_social_links table
    $socialSQL = "CREATE TABLE IF NOT EXISTS `company_social_links` (
      `id` int NOT NULL AUTO_INCREMENT,
      `company_id` int NOT NULL,
      `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
      `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `company_id` (`company_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $db->exec($socialSQL);
        echo "✓ company_social_links table created/verified\n";
    } catch (PDOException $e) {
        echo "✗ Error creating company_social_links: " . $e->getMessage() . "\n";
    }
    
    echo "\nDatabase migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
