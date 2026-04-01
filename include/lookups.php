<?php
// Centralized lookup helpers for normalized reference data.
// All functions are DB-first with minimal in-memory fallbacks to avoid runtime errors.

require_once __DIR__ . '/db_connect.php';

function sm_get_db(): PDO {
    // Prefer shared Database wrapper if available
    if (class_exists('Database')) {
        $database = new Database();
        return $database->getConnection();
    }
    // Fallback to global $pdo from db_connect.php
    global $pdo;
    return $pdo;
}

function sm_get_cities(): array {
    try {
        $db = sm_get_db();
        $stmt = $db->query("SELECT code, label FROM cities ORDER BY sort_order, label");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        // Safe fallback
        return [
            ['code' => 'Nouakchott', 'label' => 'Nouakchott'],
            ['code' => 'Nouadhibou', 'label' => 'Nouadhibou'],
            ['code' => 'Rosso', 'label' => 'Rosso'],
            ['code' => 'Atar', 'label' => 'Atar'],
            ['code' => 'Kaédi', 'label' => 'Kaédi'],
            ['code' => 'Zouérat', 'label' => 'Zouérat'],
            ['code' => 'Kiffa', 'label' => 'Kiffa'],
            ['code' => 'Autre', 'label' => 'Autre'],
        ];
    }
}

function sm_get_contract_types(): array {
    try {
        $db = sm_get_db();
        $stmt = $db->query("SELECT code, label FROM contract_types ORDER BY sort_order, label");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [
            ['code' => 'Stage', 'label' => 'Stage'],
            ['code' => 'Alternance', 'label' => 'Alternance'],
        ];
    }
}

function sm_get_study_levels(): array {
    try {
        $db = sm_get_db();
        $stmt = $db->query("SELECT code, label FROM study_levels ORDER BY sort_order, label");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [
            ['code' => 'L1', 'label' => 'Licence 1 (L1)'],
            ['code' => 'L2', 'label' => 'Licence 2 (L2)'],
            ['code' => 'L3', 'label' => 'Licence 3 (L3)'],
            ['code' => 'M1', 'label' => 'Master 1 (M1)'],
            ['code' => 'M2', 'label' => 'Master 2 (M2)'],
        ];
    }
}

function sm_get_training_domains(): array {
    try {
        $db = sm_get_db();
        $stmt = $db->query("SELECT code, label FROM training_domains ORDER BY sort_order, label");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [
            ['code' => 'Developpement Web', 'label' => 'Développement Web'],
            ['code' => 'Intelligence Artificielle', 'label' => 'Intelligence Artificielle'],
            ['code' => 'Reseaux', 'label' => 'Réseaux / Télécoms'],
            ['code' => 'Cybersecurite', 'label' => 'Cybersécurité'],
            ['code' => 'Design', 'label' => 'Design / UI/UX'],
            ['code' => 'Autre', 'label' => 'Autre'],
        ];
    }
}

function sm_get_availability_statuses(): array {
    try {
        $db = sm_get_db();
        $stmt = $db->query("SELECT code, label FROM availability_statuses ORDER BY sort_order, label");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [
            ['code' => 'disponible', 'label' => 'Disponible immédiatement'],
            ['code' => 'en_formation', 'label' => 'En formation'],
            ['code' => 'recherche_active', 'label' => 'En recherche active'],
        ];
    }
}

function sm_get_industry_sectors(): array {
    try {
        $db = sm_get_db();
        $stmt = $db->query("SELECT code, label FROM industry_sectors ORDER BY sort_order, label");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [
            ['code' => 'Technologie & IT', 'label' => 'Technologie & IT'],
            ['code' => 'Finance & Banque', 'label' => 'Finance & Banque'],
            ['code' => 'Santé', 'label' => 'Santé'],
            ['code' => 'Éducation', 'label' => 'Éducation'],
            ['code' => 'Commerce & Distribution', 'label' => 'Commerce & Distribution'],
            ['code' => 'Industrie & Énergie', 'label' => 'Industrie & Énergie'],
            ['code' => 'Construction & BTP', 'label' => 'Construction & BTP'],
            ['code' => 'Télécommunications', 'label' => 'Télécommunications'],
            ['code' => 'Autre', 'label' => 'Autre'],
        ];
    }
}

function sm_get_company_sizes(): array {
    try {
        $db = sm_get_db();
        $stmt = $db->query("SELECT code, label FROM company_sizes_lookup ORDER BY sort_order, label");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [
            ['code' => '1-10', 'label' => '1-10 employés'],
            ['code' => '11-50', 'label' => '11-50 employés'],
            ['code' => '51-200', 'label' => '51-200 employés'],
            ['code' => '201-500', 'label' => '201-500 employés'],
            ['code' => '201+', 'label' => '201+ employés'],
            ['code' => '500+', 'label' => '500+ employés'],
        ];
    }
}

function sm_get_profile_visibility_options(): array {
    try {
        $db = sm_get_db();
        $stmt = $db->query("SELECT code, label FROM profile_visibility_options ORDER BY sort_order, label");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [
            ['code' => 'public', 'label' => 'Public (Visible par tous les étudiants)'],
            ['code' => 'private', 'label' => "Privé (Uniquement ceux qui ont un lien d'offre)"],
        ];
    }
}

function sm_get_dashboard_languages(): array {
    try {
        $db = sm_get_db();
        $stmt = $db->query("SELECT code, label, is_enabled FROM dashboard_languages ORDER BY sort_order, label");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [
            ['code' => 'fr', 'label' => 'Français (Défaut)', 'is_enabled' => 1],
            ['code' => 'en', 'label' => 'English (Bientôt)', 'is_enabled' => 0],
        ];
    }
}

function sm_get_achievement_types(): array {
    try {
        $db = sm_get_db();
        $stmt = $db->query("SELECT code, label FROM achievement_types ORDER BY sort_order, label");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [
            ['code' => 'website', 'label' => 'Site web'],
            ['code' => 'project', 'label' => 'Projet'],
            ['code' => 'achievement', 'label' => 'Réalisation / Distinction'],
            ['code' => 'linkedin', 'label' => 'LinkedIn'],
            ['code' => 'other', 'label' => 'Autre lien'],
        ];
    }
}

