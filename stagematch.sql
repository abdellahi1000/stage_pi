-- StageMatch Refined Database Dump
-- Generated on 2026-04-01 07:53:29

SET FOREIGN_KEY_CHECKS=0;



CREATE TABLE `candidatures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `offre_id` int NOT NULL,
  `message_motivation` text,
  `cv_specifique` varchar(255) DEFAULT NULL,
  `lm_specifique` varchar(255) DEFAULT NULL,
  `date_candidature` datetime DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('pending','accepted','rejected','closed') DEFAULT 'pending',
  `vu_par_etudiant` tinyint(1) DEFAULT '0',
  `acceptance_message` text,
  `acceptance_date` datetime DEFAULT NULL,
  `company_contact_email` varchar(255) DEFAULT NULL,
  `company_contact_phone` varchar(50) DEFAULT NULL,
  `company_whatsapp` varchar(50) DEFAULT NULL,
  `vu_par_entreprise` tinyint(1) DEFAULT '0',
  `company_signature_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_application` (`user_id`,`offre_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_offre` (`offre_id`),
  KEY `idx_statut` (`statut`),
  CONSTRAINT `fk_offre_c_new` FOREIGN KEY (`offre_id`) REFERENCES `offres_stage` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_c_new` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fa-folder',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `nom`, `icone`) VALUES ('1', 'Informatique', 'fa-code');
INSERT INTO `categories` (`id`, `nom`, `icone`) VALUES ('2', 'Mines & Ressources', 'fa-gem');
INSERT INTO `categories` (`id`, `nom`, `icone`) VALUES ('3', 'Télécommunications', 'fa-broadcast-tower');
INSERT INTO `categories` (`id`, `nom`, `icone`) VALUES ('4', 'Commerce & Marketing', 'fa-shopping-cart');
INSERT INTO `categories` (`id`, `nom`, `icone`) VALUES ('5', 'Finance & Comptabilité', 'fa-coins');
INSERT INTO `categories` (`id`, `nom`, `icone`) VALUES ('6', 'Ressources Humaines', 'fa-users');
INSERT INTO `categories` (`id`, `nom`, `icone`) VALUES ('7', 'Ingénierie', 'fa-cogs');
INSERT INTO `categories` (`id`, `nom`, `icone`) VALUES ('8', 'Santé', 'fa-notes-medical');


CREATE TABLE `categories_offres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fa-folder',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories_offres` (`id`, `nom`, `icone`) VALUES ('1', 'Informatique', 'fa-code');
INSERT INTO `categories_offres` (`id`, `nom`, `icone`) VALUES ('2', 'Mines & Ressources', 'fa-gem');
INSERT INTO `categories_offres` (`id`, `nom`, `icone`) VALUES ('3', 'Télécommunications', 'fa-broadcast-tower');
INSERT INTO `categories_offres` (`id`, `nom`, `icone`) VALUES ('4', 'Commerce & Marketing', 'fa-shopping-cart');
INSERT INTO `categories_offres` (`id`, `nom`, `icone`) VALUES ('5', 'Finance & Comptabilité', 'fa-coins');
INSERT INTO `categories_offres` (`id`, `nom`, `icone`) VALUES ('6', 'Ressources Humaines', 'fa-users');
INSERT INTO `categories_offres` (`id`, `nom`, `icone`) VALUES ('7', 'Ingénierie', 'fa-cogs');
INSERT INTO `categories_offres` (`id`, `nom`, `icone`) VALUES ('8', 'Santé', 'fa-notes-medical');


CREATE TABLE `color_theme` (
  `id` int NOT NULL AUTO_INCREMENT,
  `template_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_config` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `color_theme` (`id`, `template_id`, `name`, `color_config`, `class_name`) VALUES ('1', NULL, 'Bleu Nuit', '{\"bg\":\"#cbd5e1\",\"sidebar\":\"#e2e8f0\",\"accent\":\"#475569\"}', 'theme-blue-night');
INSERT INTO `color_theme` (`id`, `template_id`, `name`, `color_config`, `class_name`) VALUES ('2', NULL, 'Rouge Bordeaux', '{\"bg\":\"#fecaca\",\"sidebar\":\"#fee2e2\",\"accent\":\"#991b1b\"}', 'theme-red-wine');
INSERT INTO `color_theme` (`id`, `template_id`, `name`, `color_config`, `class_name`) VALUES ('3', NULL, 'Vert Forêt', '{\"bg\":\"#bbf7d0\",\"sidebar\":\"#dcfce7\",\"accent\":\"#166534\"}', 'theme-green-forest');
INSERT INTO `color_theme` (`id`, `template_id`, `name`, `color_config`, `class_name`) VALUES ('4', NULL, 'Gris Anthracite', '{\"bg\":\"#d1d5db\",\"sidebar\":\"#e5e7eb\",\"accent\":\"#374151\"}', 'theme-gray-slate');
INSERT INTO `color_theme` (`id`, `template_id`, `name`, `color_config`, `class_name`) VALUES ('5', NULL, 'Orange Coucher', '{\"bg\":\"#fed7aa\",\"sidebar\":\"#ffedd5\",\"accent\":\"#c2410c\"}', 'theme-orange-sunset');


CREATE TABLE `company_emails` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_company` (`company_id`),
  CONSTRAINT `company_emails_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `company_emails` (`id`, `company_id`, `email`, `created_at`) VALUES ('1', '19', 'abdellahiseyid10@gmail.com', '2026-03-05 01:40:52');
INSERT INTO `company_emails` (`id`, `company_id`, `email`, `created_at`) VALUES ('2', '23', 'abdellahiseyid10@gmail.com', '2026-03-05 11:05:58');
INSERT INTO `company_emails` (`id`, `company_id`, `email`, `created_at`) VALUES ('3', '23', '24038@supnum.mr', '2026-03-05 11:06:23');


CREATE TABLE `company_phones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `phone_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Téléphone','WhatsApp','Mobile') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Téléphone',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_company` (`company_id`),
  CONSTRAINT `company_phones_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `company_phones` (`id`, `company_id`, `phone_number`, `type`, `created_at`) VALUES ('1', '19', '48287378', 'Téléphone', '2026-03-05 01:45:18');
INSERT INTO `company_phones` (`id`, `company_id`, `phone_number`, `type`, `created_at`) VALUES ('2', '23', '48287378', 'Téléphone', '2026-03-05 11:05:12');
INSERT INTO `company_phones` (`id`, `company_id`, `phone_number`, `type`, `created_at`) VALUES ('3', '23', '22607455', 'Téléphone', '2026-03-05 11:05:23');
INSERT INTO `company_phones` (`id`, `company_id`, `phone_number`, `type`, `created_at`) VALUES ('4', '23', '22665315', 'WhatsApp', '2026-03-06 00:50:12');


CREATE TABLE `company_social_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `platform` enum('Facebook','X','Instagram','LinkedIn') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_company_platform` (`company_id`,`platform`),
  CONSTRAINT `company_social_links_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `company_social_links` (`id`, `company_id`, `platform`, `url`, `created_at`) VALUES ('3', '19', 'Facebook', 'www.facebook.com', '2026-03-05 01:56:12');
INSERT INTO `company_social_links` (`id`, `company_id`, `platform`, `url`, `created_at`) VALUES ('6', '19', 'Instagram', 'www.instegram.com', '2026-03-05 10:59:40');
INSERT INTO `company_social_links` (`id`, `company_id`, `platform`, `url`, `created_at`) VALUES ('7', '23', 'Facebook', 'www.facebook.com', '2026-03-05 11:06:51');
INSERT INTO `company_social_links` (`id`, `company_id`, `platform`, `url`, `created_at`) VALUES ('8', '23', 'Instagram', 'www.instagram.com', '2026-03-05 11:06:51');


CREATE TABLE `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `contact_messages` (`id`, `user_id`, `subject`, `message`, `category`, `created_at`) VALUES ('1', '18', 'cv', 'aaaaa', 'Autre', '2026-03-06 01:45:11');
INSERT INTO `contact_messages` (`id`, `user_id`, `subject`, `message`, `category`, `created_at`) VALUES ('2', '18', 'cv', 'aaaaaaaaaa', 'Problème Entreprise', '2026-03-06 20:22:20');


CREATE TABLE `contact_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `problem_type` varchar(100) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text,
  `admin_reply` text,
  `status` enum('pending','answered') DEFAULT 'pending',
  `has_new_reply` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_message_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `cv_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `preview_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cv_templates` (`id`, `name`, `class_name`, `preview_image`, `description`, `created_at`) VALUES ('1', 'Classique', 'cv-template-classic', 'assets/cv_templates/tpl_classic.png', 'Design original Donna Stroupe.', '2026-03-02 23:54:31');
INSERT INTO `cv_templates` (`id`, `name`, `class_name`, `preview_image`, `description`, `created_at`) VALUES ('2', 'Minimaliste', 'cv-template-minimal', 'assets/cv_templates/tpl_minimal.png', 'Lignes épurées et design minimaliste.', '2026-03-02 23:54:31');
INSERT INTO `cv_templates` (`id`, `name`, `class_name`, `preview_image`, `description`, `created_at`) VALUES ('3', 'Créatif', 'cv-template-creative', 'assets/cv_templates/tpl_creative.png', 'Mise en page dynamique.', '2026-03-02 23:54:31');
INSERT INTO `cv_templates` (`id`, `name`, `class_name`, `preview_image`, `description`, `created_at`) VALUES ('4', 'Professionnel Épuré', 'cv-template-pro', 'assets/cv_templates/tpl_pro.png', 'Structure traditionnelle.', '2026-03-02 23:54:31');
INSERT INTO `cv_templates` (`id`, `name`, `class_name`, `preview_image`, `description`, `created_at`) VALUES ('5', 'Corporate', 'cv-template-corporate', 'assets/cv_templates/tpl_corporate.png', 'Pour les profils cadres et direction.', '2026-03-02 23:54:31');


CREATE TABLE `cv_user_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `photo_base64` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `poste` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lieu_naissance` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationalite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permis_travail` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `about` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `experiences` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `education` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `skills` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `languages` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `references_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `linkedin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `projets` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_cv_data_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cv_user_data` (`id`, `user_id`, `photo_base64`, `poste`, `id_number`, `date_naissance`, `lieu_naissance`, `nationalite`, `sexe`, `permis_travail`, `ville`, `adresse`, `about`, `experiences`, `education`, `skills`, `languages`, `references_data`, `linkedin`, `whatsapp`, `projets`, `created_at`, `updated_at`, `nom`, `tel`, `email`) VALUES ('1', '18', NULL, 'rftgyhujik', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, 'rtgyhuj', 'erftgyh', 'dcfvghjuiok', 'fgyhuij', 'edfgyhuj', 'derftgyhuj', NULL, NULL, NULL, '2026-03-02 23:56:49', '2026-03-02 23:57:50', NULL, NULL, NULL);
INSERT INTO `cv_user_data` (`id`, `user_id`, `photo_base64`, `poste`, `id_number`, `date_naissance`, `lieu_naissance`, `nationalite`, `sexe`, `permis_travail`, `ville`, `adresse`, `about`, `experiences`, `education`, `skills`, `languages`, `references_data`, `linkedin`, `whatsapp`, `projets`, `created_at`, `updated_at`, `nom`, `tel`, `email`) VALUES ('3', '25', NULL, '', 'efsgrthyjkuiljht', '52254524', 'dhtfyhvnhht', 'fgjkhgjhgrnh', 'Masculin', 'ghjrtghtfhfg', '', 'fsgdhfjgkytrgdhfjhgjhhdgnvmb,jjhgsdhgfh', 'ertykuil;oiulkyjthgrefafegrjlk;lkjdfsad;l\'klkhlyjthdgrkjhljthgrhkjlk', 'rkuli;oi\';kuyjthrhtyjkuhiljihkuyjthdgrhtyjkuhihujyfhtdghyjklkj', 'gsdhfjgkjhgrrthyjgkuhyjthgrdthyjkij', 'fegrthyukhjhgdhjkhgfhjkuilkuyjtfhyjkljgyjkhgjkhgh', 'dfshfgyjkhuilkgjhdg', 'rhtfyjgkuhiltyrgthfyjgku', 'dgfghfghmgbfn', '5454543', 'ertyuuttjkyl;olkyjhtyjvkubillkyztcyvubillxhgyjvklbjhgjkhljyuilb;ooid6uty', '2026-03-03 00:43:09', '2026-03-03 00:43:09', NULL, NULL, NULL);


CREATE TABLE `entreprise_achievements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'website',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `needs_improvement` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `entreprise_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `entreprise_achievements` (`id`, `user_id`, `type`, `title`, `description`, `url`, `needs_improvement`, `sort_order`, `created_at`) VALUES ('1', '19', 'website', 'stage app', 'qwertyuiuytrewwertuytrewqwerttrewqertrtyrtyrtrt', 'https://google.com', '0', '0', '2026-02-28 18:34:05');
INSERT INTO `entreprise_achievements` (`id`, `user_id`, `type`, `title`, `description`, `url`, `needs_improvement`, `sort_order`, `created_at`) VALUES ('2', '23', 'project', 'stage app', 'web facebook', 'https://facebook.com', '0', '0', '2026-03-02 15:32:12');
INSERT INTO `entreprise_achievements` (`id`, `user_id`, `type`, `title`, `description`, `url`, `needs_improvement`, `sort_order`, `created_at`) VALUES ('3', '23', 'project', 'stage', 'erty', 'https://google.com', '0', '0', '2026-03-02 15:32:47');
INSERT INTO `entreprise_achievements` (`id`, `user_id`, `type`, `title`, `description`, `url`, `needs_improvement`, `sort_order`, `created_at`) VALUES ('4', '23', 'other', 'stage', 'e4rtgyhu', 'https://chroom.com', '0', '0', '2026-03-02 15:33:32');
INSERT INTO `entreprise_achievements` (`id`, `user_id`, `type`, `title`, `description`, `url`, `needs_improvement`, `sort_order`, `created_at`) VALUES ('5', '23', 'linkedin', 'stage app', 'ertfygu', 'https://google.com', '0', '0', '2026-03-02 15:33:56');
INSERT INTO `entreprise_achievements` (`id`, `user_id`, `type`, `title`, `description`, `url`, `needs_improvement`, `sort_order`, `created_at`) VALUES ('6', '19', 'achievement', 'stage', 'ertyuijko', 'https://chroom.com', '0', '0', '2026-03-05 03:52:48');
INSERT INTO `entreprise_achievements` (`id`, `user_id`, `type`, `title`, `description`, `url`, `needs_improvement`, `sort_order`, `created_at`) VALUES ('7', '19', 'project', 'stage app', 'aaaaaaaaaaaaaaaaaaaaaa', 'https://facebook.com', '0', '0', '2026-03-05 03:53:03');


CREATE TABLE `entreprise_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `notif_new_applications` tinyint(1) DEFAULT '1',
  `notif_interview_alerts` tinyint(1) DEFAULT '1',
  `notif_internal_messages` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `entreprise_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `entreprise_notifications` (`id`, `user_id`, `notif_new_applications`, `notif_interview_alerts`, `notif_internal_messages`, `updated_at`) VALUES ('1', '23', '1', '1', '1', '2026-03-02 15:40:06');


CREATE TABLE `etudiant_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` enum('cv','motivation') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `etudiant_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `etudiant_documents` (`id`, `user_id`, `type`, `file_path`, `file_name`, `created_at`) VALUES ('6', '24', 'cv', 'uploads/cvs/cv_24_1772431923.pdf', 'mon-cv (5).pdf', '2026-03-02 06:12:03');
INSERT INTO `etudiant_documents` (`id`, `user_id`, `type`, `file_path`, `file_name`, `created_at`) VALUES ('7', '24', 'motivation', 'uploads/cvs/lm_24_1772431932.pdf', 'mon-cv (5).pdf', '2026-03-02 06:12:12');
INSERT INTO `etudiant_documents` (`id`, `user_id`, `type`, `file_path`, `file_name`, `created_at`) VALUES ('8', '18', 'cv', 'uploads/cvs/cv_app_18_1772433321.pdf', 'mon-cv (6).pdf', '2026-03-02 06:35:21');
INSERT INTO `etudiant_documents` (`id`, `user_id`, `type`, `file_path`, `file_name`, `created_at`) VALUES ('11', '18', 'motivation', 'uploads/cvs/lm_app_18_1772434763.pdf', 'Confirmation_Stage_SEYID (3).pdf', '2026-03-02 06:59:23');


CREATE TABLE `etudiant_motivation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `service_rh` varchar(255) DEFAULT NULL,
  `email_entreprise` varchar(255) DEFAULT NULL,
  `signature_base64` longtext,
  `cloture` varchar(255) DEFAULT NULL,
  `civilite` varchar(50) DEFAULT NULL,
  `adresse_entreprise` varchar(255) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `adresse_complet` varchar(255) DEFAULT NULL,
  `date_lettre` varchar(50) DEFAULT NULL,
  `entreprise` varchar(255) DEFAULT NULL,
  `objet` varchar(255) NOT NULL,
  `message` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `historique_stages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `entreprise` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `poste` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `localisation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `competences_acquises` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `certificat_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_dates` (`date_debut`,`date_fin`),
  CONSTRAINT `historique_stages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `message_feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message_id` int NOT NULL,
  `user_id` int NOT NULL,
  `feedback_type` enum('like','dislike') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_id` (`message_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `message_feedback` (`id`, `message_id`, `user_id`, `feedback_type`, `created_at`) VALUES ('1', '6', '18', 'like', '2026-03-07 04:40:45');
INSERT INTO `message_feedback` (`id`, `message_id`, `user_id`, `feedback_type`, `created_at`) VALUES ('2', '8', '18', 'dislike', '2026-03-07 04:55:48');
INSERT INTO `message_feedback` (`id`, `message_id`, `user_id`, `feedback_type`, `created_at`) VALUES ('3', '17', '18', 'like', '2026-03-07 05:28:52');


CREATE TABLE `messagerie_conversations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user1_id` int NOT NULL,
  `user2_id` int NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'open',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `messagerie_conversations` (`id`, `user1_id`, `user2_id`, `subject`, `category`, `status`, `updated_at`, `created_at`) VALUES ('1', '18', '0', 'cv', 'Problème Entreprise', 'open', '2026-03-06 02:19:40', '2026-03-06 02:19:39');


CREATE TABLE `messagerie_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conversation_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `messagerie_messages` (`id`, `conversation_id`, `sender_id`, `message`, `is_read`, `created_at`) VALUES ('1', '1', '18', 'Sujet initial : cv \nCatégorie : Problème Entreprise \n\naaaaaaaaaaaaa', '0', '2026-03-06 02:19:39');


CREATE TABLE `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `sender_type` enum('user','support') NOT NULL,
  `message_text` text NOT NULL,
  `reply_to_message_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reply_to_message_id` (`reply_to_message_id`),
  KEY `idx_messages_ticket_id` (`ticket_id`),
  KEY `idx_messages_sender_type` (`sender_type`),
  KEY `idx_messages_created_at` (`created_at`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`reply_to_message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `messages` (`id`, `ticket_id`, `sender_type`, `message_text`, `reply_to_message_id`, `created_at`) VALUES ('7', '5', 'user', 'This is a test message from the support system setup.', NULL, '2026-03-06 20:27:41');


CREATE TABLE `notes_candidatures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `candidature_id` int NOT NULL,
  `user_id` int NOT NULL,
  `note` text NOT NULL,
  `date_note` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `candidature_id` (`candidature_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notes_candidatures_ibfk_1` FOREIGN KEY (`candidature_id`) REFERENCES `candidatures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notes_candidatures_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `offres_stage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entreprise` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `localisation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_contrat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Stage',
  `duree` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remuneration` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `competences_requises` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date_publication` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_limite` datetime DEFAULT NULL,
  `date_expiration` datetime DEFAULT NULL,
  `statut` enum('active','archivee','pourvue','expiree','brouillon') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `categorie_id` int DEFAULT NULL,
  `nombre_stagiaires` int DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `offres_stage_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `offres_stage` (`id`, `user_id`, `titre`, `description`, `entreprise`, `localisation`, `type_contrat`, `duree`, `remuneration`, `competences_requises`, `date_publication`, `date_limite`, `date_expiration`, `statut`, `categorie_id`, `nombre_stagiaires`) VALUES ('50', '23', 'python', 'qwerty', 'TASIASS', 'Nouakchott', 'Stage', '5 mois', '', NULL, '2026-03-05 11:01:35', NULL, NULL, 'active', '1', '6');
INSERT INTO `offres_stage` (`id`, `user_id`, `titre`, `description`, `entreprise`, `localisation`, `type_contrat`, `duree`, `remuneration`, `competences_requises`, `date_publication`, `date_limite`, `date_expiration`, `statut`, `categorie_id`, `nombre_stagiaires`) VALUES ('51', '23', 'dev java', 'qwerty', 'TASIASS', 'Nouakchott', 'Stage', '2 mois', '', NULL, '2026-03-05 11:01:53', NULL, NULL, 'active', '1', '45');


CREATE TABLE `preferences_utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `notifications_email` tinyint(1) DEFAULT '1',
  `notifications_push` tinyint(1) DEFAULT '1',
  `visibilite_profil` enum('public','prive','entreprises_seulement') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'entreprises_seulement',
  `langue` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fr',
  `theme` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'light',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `alertes_offres` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `preferences_utilisateur_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `preferences_utilisateur` (`id`, `user_id`, `notifications_email`, `notifications_push`, `visibilite_profil`, `langue`, `theme`, `created_at`, `updated_at`, `alertes_offres`) VALUES ('4', '18', '1', '1', 'entreprises_seulement', 'fr', 'light', '2026-02-26 14:59:44', '2026-03-06 15:48:59', '0');
INSERT INTO `preferences_utilisateur` (`id`, `user_id`, `notifications_email`, `notifications_push`, `visibilite_profil`, `langue`, `theme`, `created_at`, `updated_at`, `alertes_offres`) VALUES ('5', '19', '1', '1', 'entreprises_seulement', 'fr', 'light', '2026-03-01 05:23:05', '2026-03-05 01:26:50', '1');


CREATE TABLE `profils` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `cv_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `niveau_etudes` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `universite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skills` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut_disponibilite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'disponible',
  `lettre_motivation_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domaine_formation` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `profils_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `profils` (`id`, `user_id`, `cv_path`, `niveau_etudes`, `specialite`, `universite`, `skills`, `statut_disponibilite`, `lettre_motivation_path`, `domaine_formation`) VALUES ('1', '1', NULL, NULL, NULL, NULL, NULL, 'disponible', NULL, NULL);
INSERT INTO `profils` (`id`, `user_id`, `cv_path`, `niveau_etudes`, `specialite`, `universite`, `skills`, `statut_disponibilite`, `lettre_motivation_path`, `domaine_formation`) VALUES ('8', '18', 'uploads/cvs/cv_app_18_1772433321.pdf', 'L2', 'DSI', NULL, 'med sidi mohamed', 'disponible', 'uploads/cvs/lm_app_18_1772434763.pdf', 'Developpement Web');
INSERT INTO `profils` (`id`, `user_id`, `cv_path`, `niveau_etudes`, `specialite`, `universite`, `skills`, `statut_disponibilite`, `lettre_motivation_path`, `domaine_formation`) VALUES ('9', '20', NULL, NULL, NULL, NULL, NULL, 'disponible', NULL, NULL);
INSERT INTO `profils` (`id`, `user_id`, `cv_path`, `niveau_etudes`, `specialite`, `universite`, `skills`, `statut_disponibilite`, `lettre_motivation_path`, `domaine_formation`) VALUES ('11', '24', 'uploads/cvs/cv_24_1772431923.pdf', NULL, NULL, NULL, NULL, 'disponible', 'uploads/cvs/lm_24_1772431932.pdf', NULL);
INSERT INTO `profils` (`id`, `user_id`, `cv_path`, `niveau_etudes`, `specialite`, `universite`, `skills`, `statut_disponibilite`, `lettre_motivation_path`, `domaine_formation`) VALUES ('12', '25', NULL, NULL, NULL, NULL, NULL, 'disponible', NULL, NULL);
INSERT INTO `profils` (`id`, `user_id`, `cv_path`, `niveau_etudes`, `specialite`, `universite`, `skills`, `statut_disponibilite`, `lettre_motivation_path`, `domaine_formation`) VALUES ('13', '26', NULL, NULL, NULL, NULL, NULL, 'disponible', NULL, NULL);


CREATE TABLE `remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=371 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('154', '18', '424c75e0c27e4c7bc3c874cc71d719cb6c1107d343e7e61c11eec696a6d05b6f', '2026-03-28 14:58:49', '2026-02-26 14:58:49');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('155', '19', '97878732ec3b42ceed4185a79ec3057b36e8a2d1a977930a4a79a64ac40d8369', '2026-03-28 15:01:47', '2026-02-26 15:01:47');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('156', '18', 'f2efe6d16052023eb0a089d2ad4cc57d44e689c10fa93ea97d508b8d34bfc1e7', '2026-03-28 15:03:00', '2026-02-26 15:03:00');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('157', '19', '9f27e4fd24cc2c2b298887042343b59e7dfbb9ec6d5ed28dc35d78f230313ac3', '2026-03-28 15:04:52', '2026-02-26 15:04:52');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('158', '18', '89c1b508dfd93d9d093a5c5e9c199503664b248a1d7204d060f9abae22d8a893', '2026-03-28 15:32:35', '2026-02-26 15:32:35');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('159', '19', '4e837814473a020ac0e56bcaf4955f5ff377bb588f727c2bead6f0441bd1c6c4', '2026-03-28 15:43:21', '2026-02-26 15:43:21');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('160', '19', '68dc7a6a81fc5761867f3fc667f071a6937f782fa61247547a634459056419ce', '2026-03-28 15:50:54', '2026-02-26 15:50:54');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('161', '18', '78494036b2f2f85e4aeeca7a068ecce780079865936819f794fe8e607abf9cb5', '2026-03-28 15:58:02', '2026-02-26 15:58:02');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('162', '18', '31878d52ec7f3b3e55a67ac12768c8a06b87452d97602869bb6a514947013a28', '2026-03-28 15:58:47', '2026-02-26 15:58:47');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('163', '19', '16bc40edbbe41499e9e6c4345e8ac725a831def6013457138e9d0e377a2c9e79', '2026-03-28 16:17:49', '2026-02-26 16:17:49');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('164', '18', '30facf8181b4c493e1f5026666f170b3967c540ae967231c8aa4ba7c1abb0e8c', '2026-03-28 16:42:44', '2026-02-26 16:42:44');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('165', '19', '2b49b9a59674c6f0489ca1d2225a8e1ebb43950819296c369618db34d0c01151', '2026-03-28 16:47:10', '2026-02-26 16:47:10');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('166', '18', '4f9d387a26d436fb3ed6b990971c16feaa60cb100cc9c6bf440e8fcb782bd62d', '2026-03-28 16:54:48', '2026-02-26 16:54:48');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('167', '19', '65e281eec641449a75269ccf006d50ca9d224bded97fe3505be3fafbb208ce1c', '2026-03-28 16:56:37', '2026-02-26 16:56:37');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('168', '19', '6320db5b40d873c201804757c2e74e8f0685808a7eaacda4cd28a1efa43976e6', '2026-03-28 17:16:15', '2026-02-26 17:16:15');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('169', '18', '31c37a6b3e9dadcc3a1aa5130a13877fb856c6618a1bbdbd3b191de5f61ab634', '2026-03-28 17:23:58', '2026-02-26 17:23:58');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('170', '19', 'cc88ced5dd23fc8e1ba3bef4b300d70c8b0b518f4fee74e31ed9d95422be8a43', '2026-03-28 17:24:52', '2026-02-26 17:24:52');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('171', '18', '44016bc018148d7881a8ae7c0ec423c57186a5c85316d737600df1785e441725', '2026-03-28 17:29:47', '2026-02-26 17:29:47');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('172', '19', 'ea2a269111d503927a7eed4205f005c03d444e1b5886ee130a9f91ad421751e1', '2026-03-28 17:30:24', '2026-02-26 17:30:24');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('173', '18', '181a2def49405b79149099e130985c5cd4dde30b6db76e9d458eed316e82b79d', '2026-03-28 17:39:57', '2026-02-26 17:39:57');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('174', '19', 'f2aae6f8a054e0b1f038b9639e8e5d217633589e32a1fce258fa056486f599ba', '2026-03-28 17:40:46', '2026-02-26 17:40:46');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('175', '19', 'a297ddefd43a400308f6f6a3e76e6ec6c41d8f83765bc3f86663fe7a7d382206', '2026-03-29 01:01:30', '2026-02-27 01:01:30');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('176', '18', '94aeeddf4905039a50e174a0f37a8aa18c2c4777d0166f73bff3bb82cc8c7523', '2026-03-29 01:02:16', '2026-02-27 01:02:16');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('177', '19', '7b43c147ce2441daffdfd01bd798a780b7dc62cf1f97079b1440c3b7d5c9e64a', '2026-03-29 01:03:05', '2026-02-27 01:03:05');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('178', '19', '7a4e9e63f7ed57ad1c5e84b9478cdd8941d6bc6b79493d3e797c26b18539ce4b', '2026-03-29 01:03:52', '2026-02-27 01:03:52');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('179', '19', '839bc133f8a64cacae209b195bcb80d387572d136ae9529d273469c70a2443e4', '2026-03-29 17:14:39', '2026-02-27 17:14:39');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('180', '18', '614b9c0d48742d7b7b439043db342d0c7ddb4cf29a1ffba6b7bcd1c33acff8f6', '2026-03-29 17:15:42', '2026-02-27 17:15:42');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('181', '19', 'b609ae73f67debd3c7c496dd59bdb219adc6ef30b32999fd6ffe62c0ce3fabb0', '2026-03-29 17:16:07', '2026-02-27 17:16:07');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('182', '19', '61417dcdc447af852671afe792301a2f5294495acaeae8ef47a8adb78128fb3c', '2026-03-29 17:28:42', '2026-02-27 17:28:42');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('183', '19', '8a1d265a3b9c1f7db0f05e143be771a19dede5414e8855b5f526226377835c35', '2026-03-29 17:36:52', '2026-02-27 17:36:52');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('184', '18', '3ddbbdeab6e8adcee1986e440cead8f4248fd4a11f5fe74b7b3ac62778ed6e8e', '2026-03-29 17:39:37', '2026-02-27 17:39:37');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('185', '19', '1240fbd3cf95e0f70a110fc548a4ecd3ad24fa41458a7629898e7399fbadea54', '2026-03-29 17:40:36', '2026-02-27 17:40:36');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('186', '18', 'ac0389561674f20d864cdcddbb431ffc0f17835770c79a9e64c42d2cd64f6908', '2026-03-29 17:41:09', '2026-02-27 17:41:09');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('187', '18', '334af9c7e95765bfed75563cb8c14bc6849ce899960d8e90e89ab5a117e70d1c', '2026-03-29 22:28:22', '2026-02-27 22:28:22');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('188', '19', 'b423447e3ed5ca25011047a2e77e6c29775feb391d120e7718c2a755437e6aaa', '2026-03-29 22:40:18', '2026-02-27 22:40:18');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('189', '18', '17ebfe1b45b1263a67888d5137a52e3bbd5b213758ecf0089333bce1d64d4a06', '2026-03-29 22:47:09', '2026-02-27 22:47:09');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('190', '19', '83f29c2893109bf7296cb237c13aaedfe4f7fc89b109f9c55d1ffb714cf1bf5e', '2026-03-29 22:47:32', '2026-02-27 22:47:32');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('191', '18', '0cc5ee02c7a3e36cbd084407a3a8861cf264b630bfa236e2ec9ac47e9f6b227f', '2026-03-29 22:48:08', '2026-02-27 22:48:08');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('192', '19', 'c3ab39c32c7b52adaef4199d30f6068a630464ee9dc5a95d6b7873c20376c8f9', '2026-03-29 22:51:34', '2026-02-27 22:51:34');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('193', '18', '5c4ffb56c125606b0f037b8af2f962d603915f4cc01aa3c09e3b91deaaf5bc31', '2026-03-30 12:53:35', '2026-02-28 12:53:35');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('194', '18', '6c588cfaa79a4b4055e54ba5f77de19e73b5cd9f4b70e3bb59f60ec248e8897e', '2026-03-30 12:59:40', '2026-02-28 12:59:40');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('195', '19', '621bf836660c94a80c6dc1f5918634ff1a934a7ba416642fa955ee6fcad797b8', '2026-03-30 13:18:16', '2026-02-28 13:18:16');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('196', '18', '659495618bdbae76201f50e6d2ad92cb92fcc43e98cc6c2410e20c0f446a9e09', '2026-03-30 13:19:58', '2026-02-28 13:19:58');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('197', '18', '0e5a006baa8858eef5414a424343ecc0fb8b6ee97d12384fa88e3be7c05eb5bb', '2026-03-30 13:20:12', '2026-02-28 13:20:12');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('198', '19', 'e3026ed0bf26b28033028de8606e94cda9bb2b6320e7bd09418160d3f0dfb629', '2026-03-30 13:20:31', '2026-02-28 13:20:31');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('199', '18', '81f9d6a7941320cc4763bf8d349ed2a99fe1e53b41ba6d5b1077d5200353c932', '2026-03-30 13:21:45', '2026-02-28 13:21:45');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('201', '18', '6e2e69e71bc71e2632589c5f57fbd9dab1bdc60e8a636702200ff7cd11f7590c', '2026-03-30 13:35:28', '2026-02-28 13:35:28');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('203', '19', 'c34a0c0e8ec3cbcc9e29042262e6704060600e68e158cddd7a7887c66dc0eeaa', '2026-03-30 13:36:06', '2026-02-28 13:36:06');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('208', '19', '7e054cadfc78fa1dc7d3b139812e83686c18431e9496272d4c287e1a422ac870', '2026-03-30 13:39:47', '2026-02-28 13:39:47');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('209', '19', '7371bf3ca1a780bfa05db8c382099db696649ec5e209c61efdbaaba5d2e71e3b', '2026-03-30 16:07:39', '2026-02-28 16:07:39');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('210', '18', 'f088a6965c1099fddb3208dbbf373cb9edffee8e4b6324586a5a93ca175cc500', '2026-03-30 16:40:39', '2026-02-28 16:40:39');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('211', '19', '7c9f07490cf5c598f8abeca7b5f8e2fd3dc5f1d6b8a5f7ee8bab8d95c736281f', '2026-03-30 17:14:52', '2026-02-28 17:14:52');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('212', '19', 'e7455bddc8f799b9a2b670960e318c18ec9a045f0feba27be8fa71e203423a1c', '2026-03-30 18:10:52', '2026-02-28 18:10:52');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('213', '18', '5bd94c2d919cb22d2fef3da058cd340075d13db8d8039093a4f7b88524dfb5a7', '2026-03-30 18:19:16', '2026-02-28 18:19:16');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('214', '19', 'ac6493dda68bda8a5c35eb6b6aa0fd4ae7acd76c33389f20d235bd7da583b7ab', '2026-03-30 18:19:41', '2026-02-28 18:19:41');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('215', '18', 'f5d6cfcae4239f218801080ca45a0d09ac6380f220a009a2bf88b49970b77fab', '2026-03-30 18:20:25', '2026-02-28 18:20:25');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('216', '19', '33c0235fad23d45bc2cbe02df2d04eac4ecd1940f9932a154bc211ae5ad46639', '2026-03-30 18:22:13', '2026-02-28 18:22:13');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('217', '18', 'abecee2800cf2e909afcd97d43353f481d9a995633ad045073767629521bba12', '2026-03-30 18:35:50', '2026-02-28 18:35:50');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('218', '19', '501a11ce99e809c05bf34a08e867d48aca41d6d505870e773c63646a83e5b5b9', '2026-03-30 18:40:35', '2026-02-28 18:40:35');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('219', '19', '33698e836d01862023710cdebef79aab5502e84ddc30e367e774f0c4d76367e2', '2026-03-31 02:21:50', '2026-03-01 02:21:50');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('220', '18', 'a1f242b6b9367f6f43232a49058bf802b929fea4382c846913cbf788f083321b', '2026-03-31 02:46:01', '2026-03-01 02:46:01');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('221', '19', '6240589bf05cfd23ac18ba009d972af2afa1f95294e97bbb41cc3b2dac048213', '2026-03-31 02:46:26', '2026-03-01 02:46:26');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('222', '18', '61074b4fc58c01ced36a6cde4b159fe759bb3d3b8dd1d81c39c2f0bad05d4ca0', '2026-03-31 02:56:39', '2026-03-01 02:56:39');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('223', '19', '00d57d9cc4d70d442197fa4b224123cdaf466951d867a91cbdd4133fc5573fcf', '2026-03-31 02:57:21', '2026-03-01 02:57:21');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('224', '18', 'bdc4773609e99474db0118c696ea37404b44244e125d6ac52565facd940f2432', '2026-03-31 03:15:43', '2026-03-01 03:15:43');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('225', '18', 'bcadc5204414637c5eba51409d12a84977c3ea032d41bc4ea9c70b538c4738ad', '2026-03-31 03:18:10', '2026-03-01 03:18:10');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('226', '19', '538d3031433173adfdd11b2bb833721b3f6c703f40ef125585db362b3b2adf48', '2026-03-31 03:41:28', '2026-03-01 03:41:28');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('227', '18', 'ab8b04ab0bcb9fd936340d7b6ab4118800efda0e4339b37f391a27145004b3dd', '2026-03-31 03:42:49', '2026-03-01 03:42:49');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('228', '19', 'e29c0640c11b682a2dac167af0695a82ee6c5204017821d038351d21fa6c12f6', '2026-03-31 03:45:56', '2026-03-01 03:45:56');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('229', '18', 'b3f94ddb943db718643f3ae2bf5c3770136efb5df63edf37ebe56006a24dd09a', '2026-03-31 04:27:56', '2026-03-01 04:27:56');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('230', '19', 'dd913ceb7b401ac9c6562adf9c88a3f9cc83f134a74f64390c5d71975385cdb4', '2026-03-31 04:30:19', '2026-03-01 04:30:19');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('231', '18', 'f16ed556d560c3c5f740f6fb3789ff96903574e50524f87df807ed1012c12774', '2026-03-31 04:30:51', '2026-03-01 04:30:51');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('232', '19', 'b0566bc79e647e11238b20151323b0e4c4d4bd138d2ba627c1c6edf5708a987e', '2026-03-31 04:33:31', '2026-03-01 04:33:31');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('233', '18', 'b73e846a545c93a7f58f1b02502ca7c285b4791db4ad0a702c4391d6e6cf851f', '2026-03-31 04:35:23', '2026-03-01 04:35:23');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('234', '19', '7a021465260fddc54456a934b2192c871cdcfad7cbb47cdcc37a031f964a29af', '2026-03-31 04:48:57', '2026-03-01 04:48:57');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('235', '18', 'bfe345becf4cafeeac3af519594e21488f9dd80a764d4fa47eefb58798bf96cc', '2026-03-31 05:02:01', '2026-03-01 05:02:01');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('236', '18', 'cec039cf8293ee34f619810fdbb87f42b5b07c8196c27342f3c3eec091e3aeac', '2026-03-31 05:03:03', '2026-03-01 05:03:03');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('237', '19', '9bd77393326994fec725fc3c9694ad17d0889704dded77a7b86ebb97bc6b7318', '2026-03-31 05:03:24', '2026-03-01 05:03:24');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('238', '18', 'c2ff3e18f1285fe8cb845a7d24b16c76e41f0447c9bb2ecf85c419193a9c11dc', '2026-03-31 05:03:54', '2026-03-01 05:03:54');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('239', '19', '37f9e2cf356bb8ff699a0ddb7328808fc9f28f30132a57fad0f89f0966d2b400', '2026-03-31 05:22:35', '2026-03-01 05:22:35');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('240', '18', '254bbd6c5ce09bd5e7d3882284c0c5d325c7f9f756c7dfd732ff0cb02e6df010', '2026-03-31 05:23:53', '2026-03-01 05:23:53');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('241', '19', '00c5dec669e34411cb473b75df90f9571405e3edfc45415bf77e7f0b913321f8', '2026-03-31 05:25:03', '2026-03-01 05:25:03');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('242', '19', 'f52efce52cc8e8b38274fa5a892e7d05f8d70150d4f28c5e56114a3ba9dad83e', '2026-04-01 06:00:50', '2026-03-02 06:00:50');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('243', '23', '098f21b7cc3836abc12196eb3a1e80964c027f4811db54cace41d91b894722c5', '2026-04-01 06:05:58', '2026-03-02 06:05:58');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('244', '18', '3169b028831c5bfccd2ea70ddcb2b6f0328e5812adcb831b6761fde476738d9f', '2026-04-01 06:06:50', '2026-03-02 06:06:50');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('245', '24', '987065f38028b53dff689830b53bee824df4c36b7fae290aa1e2413f6e18c72d', '2026-04-01 06:09:06', '2026-03-02 06:09:06');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('246', '23', 'b12055fd969867f445c4db3706ef43cff3d7d69a22f0a1ef678ff75d07a6eff7', '2026-04-01 06:12:52', '2026-03-02 06:12:52');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('247', '23', 'c06ee43b03dc3a096c81b3acb308fcd48363d71e38ac405c45967cd1e0ac7450', '2026-04-01 06:14:22', '2026-03-02 06:14:22');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('248', '19', 'e646d139caf1881d2126d08d30b5356819a0ecc4ce6d3173733a1c333ee6d92d', '2026-04-01 06:14:44', '2026-03-02 06:14:44');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('249', '18', 'f1868d95a1030ea03372396babb823bbc74dfe54d97ff230893123959a1f3c7c', '2026-04-01 06:15:45', '2026-03-02 06:15:45');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('250', '23', 'e751e1d47b91abf8b15dbfb32e05694cf69d109ebf76848843ed98c12d7237c1', '2026-04-01 06:25:15', '2026-03-02 06:25:15');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('251', '18', 'f732a83fa3c1802b4c6486b39c090f98e79f2a01da9e27327711ce8ef77e1938', '2026-04-01 06:25:42', '2026-03-02 06:25:42');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('252', '24', 'cc846e89a0a422b9de5878288daf0dc06154128398ed5aa63f715170dc368695', '2026-04-01 06:40:49', '2026-03-02 06:40:49');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('253', '23', '6c8d95896389fe43e70fae0e5c2a976664cd0e1891e9176339aba49f11eda3ff', '2026-04-01 06:41:13', '2026-03-02 06:41:13');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('254', '19', '8b1d954c73ec9c8324a19febc0ef453aa045db4de989898915ecb896790cf864', '2026-04-01 06:41:33', '2026-03-02 06:41:33');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('255', '18', '2701d3f6485586192fe5583bbf08466e76ba277ecd9a8a186b2c00dab3733768', '2026-04-01 06:43:03', '2026-03-02 06:43:03');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('256', '19', 'd19e6d7278561cee0e9a66d62f3ef62daedd4e10cea3fa264b1515c9ce73c764', '2026-04-01 06:43:36', '2026-03-02 06:43:36');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('257', '18', '01801f6bec16828d57d48670a948834ff9fc701bf25e36602ed357477cdf9a14', '2026-04-01 06:44:54', '2026-03-02 06:44:54');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('258', '19', 'cdfa9e4f17b323520d15652e0089b1534d507d7c026285df6cecb9f06d016220', '2026-04-01 06:45:18', '2026-03-02 06:45:18');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('259', '18', 'd8af42ed9eaf0f0c32a04a08f7428faddcaa0ef1c3c9cf478df9ad19cf31f97b', '2026-04-01 06:46:09', '2026-03-02 06:46:09');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('260', '23', '3ac7c73e02b73cdcb430e46ffaf98db046559a470bdf88cff3190d7fcf66f334', '2026-04-01 06:46:36', '2026-03-02 06:46:36');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('261', '18', 'd8b7e7ec492f7bd54ed2ff483d0158772e9abc60106d827c976889133d6cdada', '2026-04-01 06:47:14', '2026-03-02 06:47:14');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('262', '19', '1c0fb50b5f4a80aae5452177ca197e0bc1d1d2c91f1e873a58c8ef233100c804', '2026-04-01 06:49:10', '2026-03-02 06:49:10');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('263', '18', 'ea0198c03ff915b3db0365534fdc0338b488f28e76cb5556f9a6adccd7c70b22', '2026-04-01 06:50:28', '2026-03-02 06:50:28');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('264', '18', '7ab211473e3370c73741330fb6ec410f35b0adbbfc7623575d230b7dd747e3c3', '2026-04-01 07:14:18', '2026-03-02 07:14:18');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('265', '18', 'ad9b0ccb5a35212031c40bebd763ecaabc2ad45253f4a26dea4d27f374ea60a8', '2026-04-01 13:25:15', '2026-03-02 13:25:15');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('266', '19', '67667a6897a2367f59b5a936d9bef848e8f38f25641f7425aef9e596bae6133b', '2026-04-01 13:26:47', '2026-03-02 13:26:47');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('267', '18', '31f4d918d498caf50e70803617d7f87385cb1f610ad1be454bcf658db59689f8', '2026-04-01 13:34:33', '2026-03-02 13:34:33');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('268', '23', 'dac36802414a9c50861172ad90aaa601a0e27da0f94c194a7f31c4e09bab1e1d', '2026-04-01 13:35:32', '2026-03-02 13:35:32');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('269', '18', '9b62df1a92fd56a8266a6ae74828dd13a4f8acdddd4e4be7bd9da57b978b9661', '2026-04-01 13:36:57', '2026-03-02 13:36:57');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('270', '19', 'fdbd75607d3943897247d74cc16aa225b84dc34c09798c26727ed4bc1cce69a9', '2026-04-01 13:39:55', '2026-03-02 13:39:55');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('271', '23', 'e68f3ebe809bc33cf9770743e43d15e944020004fadccdf274db050a4639897b', '2026-04-01 13:40:16', '2026-03-02 13:40:16');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('272', '18', 'a9100d9ad4d32dc563ffe43ba77807dd9eb396398816753898e9a82b2bac9f37', '2026-04-01 13:55:17', '2026-03-02 13:55:17');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('273', '23', '4acb1e367cc8a656dbd00bf0191a4416598425050d4e017d8b79847ab2910bfd', '2026-04-01 13:55:57', '2026-03-02 13:55:57');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('274', '23', '4001d141899551637647522d8aa6791b96398c40a98a04f93c411c0d5e2bd372', '2026-04-01 15:43:11', '2026-03-02 15:43:11');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('275', '18', 'aa98a3bc2e3f2dc9d0eb90c5030d86479fba68659226ea9dbf9292d544e83761', '2026-04-01 16:48:06', '2026-03-02 16:48:06');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('276', '19', '0f445f6499bb4a1e3c7eea5a51b1efeac573b066c3035ade435e70dbc3127b3d', '2026-04-01 16:56:44', '2026-03-02 16:56:44');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('277', '18', '7bc1a042ceedf011f6d2c94dfeb2c3ba972c4f4768f1d1a684e9b9189cbf94a4', '2026-04-01 16:59:40', '2026-03-02 16:59:40');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('278', '19', '0f58ac16f041bcfbdfd7c258594ab528f65703f7f7292d322284d7a33aa2630f', '2026-04-01 17:00:07', '2026-03-02 17:00:07');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('279', '18', '4dd5af8cba43ae2f7d705ff0f59aed43c8a2d24f1b881dbc487f0bbc73d3e9e3', '2026-04-01 17:00:33', '2026-03-02 17:00:33');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('280', '19', 'd17436f06e071a88f4542a9462d3a19338226e08c85f5a01d1e483e4d2e639e2', '2026-04-01 17:11:27', '2026-03-02 17:11:27');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('281', '18', '61a4197a2186f8a4feb4d24b93f531881cf9269cd5d009c96e8d7f3dd4fb4290', '2026-04-01 17:18:45', '2026-03-02 17:18:45');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('282', '19', 'e2482804a4ae9002d3d1f6f291d024d54632bfdea7dfed468a7f9a07fec2cf45', '2026-04-01 17:21:07', '2026-03-02 17:21:07');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('283', '18', '68f2574a8b4cd3c1c7f1ae1d748045b151bf75e8a8cb34414f6faf3afebf382b', '2026-04-01 22:52:24', '2026-03-02 22:52:24');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('284', '18', 'db98357a0ba850b81dd956d1a1b3e57433516a935724d2b0858ea10dad38748f', '2026-04-01 22:53:59', '2026-03-02 22:53:59');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('285', '18', '974ef3e821aa9d219154984b6abd35a21f2c6577d42b617c9b4434a044a2cdf4', '2026-04-01 22:54:59', '2026-03-02 22:54:59');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('286', '23', '010ab6f245c00c4750570a72d473b497899f20d2ab3b38e5f103e63678004802', '2026-04-01 22:56:40', '2026-03-02 22:56:40');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('287', '18', 'd51d42e36434e32eb819d3f1156b7438e30593316ac190638022842f7f37686f', '2026-04-01 22:59:17', '2026-03-02 22:59:17');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('288', '18', 'a5d1de9f810991f0d446040c9f8d48e690e4f2a6944466200562f52ddc63a7a2', '2026-04-02 00:53:00', '2026-03-03 00:53:00');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('289', '19', '28346e3b0bbfdb29d442e56c435c28dd2a2ae57c2f4201c5ff680c3807228d9e', '2026-04-02 22:22:00', '2026-03-03 22:22:00');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('290', '18', '2fc7a5d39066420da0b3a93be7508e80d11d6875f95586c6496fb0d2a77058b6', '2026-04-02 22:22:54', '2026-03-03 22:22:54');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('291', '19', '28e1674b3816e0d1fdf82959d2042ebe1c60276ae18e1d7ced3ae4d32a08f07e', '2026-04-02 22:28:38', '2026-03-03 22:28:38');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('292', '18', 'bc8a0fa8746c4511da37aa0f3820dd95948f5e47e281aec35c0e6eb13fdda161', '2026-04-02 22:30:03', '2026-03-03 22:30:03');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('293', '18', '36186768def8d1869be5a735c818b2228471183163ac45a002d81525d4c51517', '2026-04-03 11:35:53', '2026-03-04 11:35:53');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('294', '24', 'dedeef9d97756474354d7f5637ccb1a9ec559902ba4e8bee4e22003a26878941', '2026-04-03 11:42:03', '2026-03-04 11:42:03');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('295', '19', '3afb853dd1b66e40bb59d729cdf08a36a0d6855616635a4ba7ff21d1e25d3363', '2026-04-03 11:44:15', '2026-03-04 11:44:15');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('296', '18', '8ec94ef9de3fb69271326712ab40712534ea4525f53337d7a8c89d92eb4bc404', '2026-04-03 11:45:09', '2026-03-04 11:45:09');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('297', '18', '2562bfdb1abf6564431e0d6b55582de318208ac6f4e5ae98f531eb9bd82b4b79', '2026-04-03 12:53:58', '2026-03-04 12:53:58');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('298', '19', '2d4761d0370433abe422d7ecf0d1721fa9a29f729595b5c5791c9edb35ccb4ec', '2026-04-03 12:55:11', '2026-03-04 12:55:11');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('299', '18', '03bb679b9152f6efa072d598bb2f71bcf6e708cbb0dbe13b59247359cf7341be', '2026-04-03 12:55:43', '2026-03-04 12:55:43');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('300', '18', '76a2f639374ed9b11180af11199c3c5c74ec655a49179ae99aeffb327c00eefe', '2026-04-03 19:53:12', '2026-03-04 19:53:12');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('301', '19', 'acff6c543323785b953b2230a5cf29a657f73d406917de79b15b7b87ed016fbd', '2026-04-03 19:54:36', '2026-03-04 19:54:36');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('302', '18', '722a97bee23a6a83001627506222cbd48286feb5640ac6e355db6ce8cba260a9', '2026-04-03 20:14:46', '2026-03-04 20:14:46');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('303', '18', 'becf313b712a4cb712f15055064979b71c861fdddc46d9757e750aef247f10c3', '2026-04-03 20:24:59', '2026-03-04 20:24:59');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('304', '18', 'edc71c9d0eb7bd174c920948f752369d35de450045728a9a9c98010368fdc039', '2026-04-03 23:56:23', '2026-03-04 23:56:23');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('305', '18', 'c2231ce07e7825d1b68d5d25c440c73fe279cd6a46ac11d00d6a7f9b52838735', '2026-04-03 23:57:31', '2026-03-04 23:57:31');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('306', '18', '0360050b573b3d38d7e3336dc28da435f21588c6828f724e516eb0e45085ea47', '2026-04-04 01:31:26', '2026-03-05 01:31:26');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('307', '19', '0ba035ac9318c9f83cbbdef26d808c67dadcd0c0271b51cf01f98473032c9b74', '2026-04-04 01:37:12', '2026-03-05 01:37:12');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('308', '18', 'e6ec31eacf0babadffe744a99e1e59c80f47d64329e343250b50e983b46602b9', '2026-04-04 01:59:11', '2026-03-05 01:59:11');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('309', '19', '724fe5aca4f48ba0f31ced8906f43247a29dc5e14e651e49733066355cb3e06c', '2026-04-04 03:20:37', '2026-03-05 03:20:37');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('310', '18', '2224cb18d03d5c13a6814b2dcfacf5b7e8963cff9aeb39276c4c304bd077d7a2', '2026-04-04 03:29:00', '2026-03-05 03:29:00');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('311', '19', 'bc1c2eb2e8d07e70daa37eb6b21e0aae5cc432ae9201648bda8434d35b9640d2', '2026-04-04 03:38:16', '2026-03-05 03:38:16');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('312', '18', 'aa2f94013b8d2dc22b934dceef79544f4d96373bf7f2906a0799f725b555833d', '2026-04-04 03:45:57', '2026-03-05 03:45:57');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('313', '24', '341c7ef40d7fa5d2a1811deae54a3920612c4560a94488a2afdc04b7e979bdd7', '2026-04-04 03:51:54', '2026-03-05 03:51:54');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('314', '19', 'f9ec0923557391a380fb31a8375e0c4297c6f5182b359be7bea4485951fe56ea', '2026-04-04 03:52:24', '2026-03-05 03:52:24');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('315', '18', 'ae37b9062d1184313c6374ca3f6a36d35745a2f10f4e1df38d777ed3232b011c', '2026-04-04 03:53:53', '2026-03-05 03:53:53');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('316', '19', 'ef12c87881f3c048a69575802022c8f6a3ef5f0b955f56c8a865754e1f4f0831', '2026-04-04 03:55:02', '2026-03-05 03:55:02');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('317', '18', 'b232f6071cb20e59f862442ddaf653698e10469a9f4dad2c21e29cb6bf79a868', '2026-04-04 03:55:55', '2026-03-05 03:55:55');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('318', '19', '8745695b7cb110fbcf4458ecdf740e20dcdb0fd6578f452062f864972009e1ce', '2026-04-04 03:56:32', '2026-03-05 03:56:32');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('319', '18', 'df37fd88b1ff71ad2c7b9a09b1d1cc1c1574fc4451db8055fd57f0f8b16181fe', '2026-04-04 03:57:10', '2026-03-05 03:57:10');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('320', '19', '30a8f160e74fa724645804ab7f8dc4f504aaab2b5e9aeea20da4cc0659bc32d8', '2026-04-04 03:58:19', '2026-03-05 03:58:19');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('321', '19', '405e5f74749b20cedbf91ab3c8c31aa4ef3d9e2bb48f2452efb4618353d43a4e', '2026-04-04 10:58:48', '2026-03-05 10:58:48');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('322', '19', '0ee8e8e599ca9423b4f09657f944931dc50b406bfd58f0a99df89ca3803a8fb5', '2026-04-04 11:00:16', '2026-03-05 11:00:16');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('323', '23', 'bc0f8c8a8f22be859d9613ba0854debcc904e1f1dd47a89337ad1d3af6754895', '2026-04-04 11:00:55', '2026-03-05 11:00:55');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('324', '18', '3c04bafb88d02e08682e510c675db5230916078837ed6e2dea94872d70f45d54', '2026-04-04 11:02:35', '2026-03-05 11:02:35');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('325', '19', '223f3a02cfbbd95d4ee1281f4de2b4179cc9503760f8854c3896a6d32927ba26', '2026-04-04 11:03:59', '2026-03-05 11:03:59');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('326', '23', 'b965413873af80697550c37522cb6b48c9f5deb892a7e7e91ca466cc1015c5b8', '2026-04-04 11:04:16', '2026-03-05 11:04:16');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('327', '18', 'c5277d74526424da98d9c6d0cae1b3eb80e2f6130b91d423c7550028f249c704', '2026-04-04 11:08:37', '2026-03-05 11:08:37');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('328', '18', '0320c7d761420b6b5534077edd719f66052de6d6ddad525882e6cf2be2fc2862', '2026-04-04 11:37:29', '2026-03-05 11:37:29');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('329', '18', '2e8bb84ce531d8009a897dd995e706c6e54bf3ffe05a770b624996a37a099a06', '2026-04-04 15:50:17', '2026-03-05 15:50:17');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('330', '18', '6827b1a9767e43d0c246ef5208ee9809698d008bc05c2ff490739dfcd68aa059', '2026-04-04 23:35:34', '2026-03-05 23:35:34');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('331', '23', 'e882f953b636baff08ce74e151ea2c9cc82a82205ccf7f432690ef1529c6fe43', '2026-04-05 00:11:00', '2026-03-06 00:11:00');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('332', '18', 'a19a146f98b96fe4d29a62d70eb551fbd5b5bb95695dbd589d3eeaf35aee8767', '2026-04-05 00:48:57', '2026-03-06 00:48:57');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('333', '23', '3927ba84c4664da1e627b6bcb09c56eb58f78a816682dac5a077004e68eb4f99', '2026-04-05 00:49:17', '2026-03-06 00:49:17');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('334', '18', 'd1199554f1f1ac7fe94830aed93863c57fabebdc3b43d304ea976a04bc3c169e', '2026-04-05 00:50:41', '2026-03-06 00:50:41');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('335', '23', 'b123955eba5d46d5d17ba725a386240cd324b6fa742a3738275063843f7ca446', '2026-04-05 01:03:09', '2026-03-06 01:03:09');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('336', '18', '6df4acb127ffce5f47edfd88b6a9ceee17834c587fc4026ee7b064443602e7e9', '2026-04-05 01:04:41', '2026-03-06 01:04:41');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('337', '23', '9c1f93f9572913effbf607b2ad56072e7356f5e8014bf151058430397dd371c1', '2026-04-05 01:06:00', '2026-03-06 01:06:00');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('338', '18', '98ba2ed6a3dafc29fd76adb2bc17c0c1363c97e5acc4cb0722f2ac4d2bbff263', '2026-04-05 01:14:54', '2026-03-06 01:14:54');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('339', '18', '0d4b7b8a41b44d261143a007eaeb58e1c72c8530e3078a14426dd085d050227e', '2026-04-05 09:51:01', '2026-03-06 09:51:01');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('340', '18', '71b978d3effa6c4d3d6fa5f83ce415a5bd212a033efb5da09b79f84edcb37df8', '2026-04-05 14:56:01', '2026-03-06 14:56:01');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('341', '18', 'ed4a41f1c540e4add98a0e43e89e896d31512c5812e7a10b728b503402ed6b20', '2026-04-05 15:43:01', '2026-03-06 15:43:01');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('342', '18', '28ace6de27b0fbc91c895e34cddea244050793321b4422b3424c6b3ef2702c71', '2026-04-05 20:21:28', '2026-03-06 20:21:28');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('343', '19', '2329d191270e49cfa3ede97a3387eb290d96d86fb63900bfc046878d849f74e9', '2026-04-05 22:04:20', '2026-03-06 22:04:20');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('344', '18', '5403cc3abc104a977030d12cbcaabf2767f56dceaa82410c9fcea8eaa5a116b2', '2026-04-05 22:16:50', '2026-03-06 22:16:50');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('345', '19', 'f4f809be3a6d710e1707301c6e151ca3d6d6ea9882adc59fbeb99e87fd4c23b5', '2026-04-05 22:18:59', '2026-03-06 22:18:59');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('346', '18', '2c30f174fc51f7234cc6e04b66e2b686bcc43cfee08a43d0913889862f5ee3c6', '2026-04-05 22:24:23', '2026-03-06 22:24:23');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('347', '19', '596e52aa4cb6c97c9744a81f9efe95b9e3e89212319ab86da9b8a67caaf5fd5d', '2026-04-05 22:26:24', '2026-03-06 22:26:24');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('348', '18', '7453e2148af9462e040dc6fcd5a6b033bf7fa5843648fd63ce96523f6fe6ec98', '2026-04-05 23:20:55', '2026-03-06 23:20:55');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('349', '19', 'e086d6ff249b8336ec419f4f6cde6263c94d7ead6a63f10c0881ef0a0e4f7d8f', '2026-04-05 23:41:39', '2026-03-06 23:41:39');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('350', '18', 'f1660952defa42397591a887dba2a2eb7f390cab53bf79d91764afb84e822e8d', '2026-04-05 23:49:17', '2026-03-06 23:49:17');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('351', '19', '11194f32b775d892da7da4a8c726241c3666007bc67efac5a20f8f5e767ce743', '2026-04-06 00:02:20', '2026-03-07 00:02:20');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('352', '18', 'ef4785bccc4a3d6ac83ed07110320c15da95eb22e439b683ca14b160e1fee924', '2026-04-06 00:09:53', '2026-03-07 00:09:53');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('353', '18', '353ddfe256f8bb5166139ff667c78bee0a1347a44eac27b6d0b7eb70917fa8d1', '2026-04-06 00:17:01', '2026-03-07 00:17:01');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('354', '19', '9a5e9cd5ac581e2bffae5be5a5280c98df5fcbf81d2fd37816f7b568490f7d01', '2026-04-06 00:17:28', '2026-03-07 00:17:28');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('355', '18', '367c9db200d67d70cc5ea0346030bd6705787368eb8d825b5eea1965d0ea5a6e', '2026-04-06 02:50:20', '2026-03-07 02:50:20');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('356', '19', '908a934c3958331e864f7eedb99aaa4f834faa1c7bbb931beafc6d8012655a0d', '2026-04-06 02:51:19', '2026-03-07 02:51:19');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('357', '19', 'ecf2e463197995aa477cd47dda4aba6ad87f8d2dc4839e23f190ed8595fe440c', '2026-04-06 03:24:20', '2026-03-07 03:24:20');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('358', '24', '07058d4848b6aef7436eabf94710ff6e7299c1c945de1b3f5c629d131ac1f5da', '2026-04-06 03:36:36', '2026-03-07 03:36:36');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('359', '19', '0e35d33aa6ba032133c97bfe5eabe2cef4c3ff1caaaee69f14dd2656e7d5c74d', '2026-04-06 03:37:46', '2026-03-07 03:37:46');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('360', '18', '7e2bd503b63e1749d8f6189f8ba77b399272fed9875ef244e2d6fd5e3a16d0f9', '2026-04-06 03:49:38', '2026-03-07 03:49:38');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('361', '19', '5a90aa1dadd4fbb6ae78d8d8887b2bc04c6e30e8367d775a338af3ea4cbc1529', '2026-04-06 03:50:59', '2026-03-07 03:50:59');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('362', '19', '430a3e2cf87c024440934963aced7aa4194e78f6cb2f8f76955af46f33045bca', '2026-04-06 04:08:24', '2026-03-07 04:08:24');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('363', '18', 'f5413a17e6978f2aa95b4c5fbfc69ee5e45155dd86a8a733e57b5ab318978488', '2026-04-06 04:08:42', '2026-03-07 04:08:42');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('364', '18', '2bda8c5b2112b459414144598eb9e2109c5a43b9d7bd64cde1cc2b7d1ec70fec', '2026-04-06 04:10:57', '2026-03-07 04:10:57');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('365', '18', '139749f1dc56cfa89f589ad5792a98bcdd038754b9cf2798965d6d058a9e4e0a', '2026-04-06 04:16:40', '2026-03-07 04:16:40');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('366', '18', '9c7989614cf60f94c32cc04e914c6e98d51e81603ec0dd864d51754f1a3c1883', '2026-04-06 04:40:31', '2026-03-07 04:40:31');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('367', '18', '6e7747091ead8635028bf35dd7a170eda15b5c4ebbc7cb89ec642a68a05e3bae', '2026-04-06 04:55:33', '2026-03-07 04:55:33');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('368', '18', '9e9ba069c51edef7589a1073c71d05b6e999b621c01f805add1bb12b89582f07', '2026-04-06 05:27:28', '2026-03-07 05:27:28');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('369', '18', 'd66d8743803ae2aeeee183511983c93924b6e64287a092c6283e6e626c8636aa', '2026-04-06 05:28:41', '2026-03-07 05:28:41');
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES ('370', '18', '5bac54b6c41656a992bf5b5ba5becc549de875937150d768767bc49039b4a831', '2026-04-06 05:30:41', '2026-03-07 05:30:41');


CREATE TABLE `saved_companies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `company_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_save` (`student_id`,`company_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `saved_companies_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `saved_companies_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `selected_template` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `template_id` int NOT NULL,
  `color_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `fk_sel_tpl_tpl` (`template_id`),
  KEY `fk_sel_tpl_col` (`color_id`),
  CONSTRAINT `fk_sel_tpl_col` FOREIGN KEY (`color_id`) REFERENCES `color_theme` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sel_tpl_tpl` FOREIGN KEY (`template_id`) REFERENCES `cv_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sel_tpl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `selected_template` (`id`, `user_id`, `template_id`, `color_id`, `created_at`, `updated_at`) VALUES ('1', '18', '1', '1', '2026-03-02 23:56:49', '2026-03-02 23:56:49');
INSERT INTO `selected_template` (`id`, `user_id`, `template_id`, `color_id`, `created_at`, `updated_at`) VALUES ('3', '25', '2', '5', '2026-03-03 00:43:09', '2026-03-03 01:29:41');


CREATE TABLE `support_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `sender_type` enum('user','admin','support') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `message_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `like_count` int DEFAULT '0',
  `dislike_count` int DEFAULT '0',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unread',
  PRIMARY KEY (`id`),
  KEY `demand_id` (`request_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `support_tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('ouvert','en cours','résolu') NOT NULL DEFAULT 'ouvert',
  `admin_reply` text,
  `admin_replied_at` datetime DEFAULT NULL,
  `student_reply` text,
  `student_replied_at` datetime DEFAULT NULL,
  `admin_reply_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('OPEN','CLOSED') DEFAULT 'OPEN',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tickets_user_id` (`user_id`),
  KEY `idx_tickets_status` (`status`),
  KEY `idx_tickets_created_at` (`created_at`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `tickets` (`id`, `user_id`, `category`, `title`, `status`, `created_at`, `updated_at`) VALUES ('5', '1', 'Technical', 'Test Support Ticket', 'OPEN', '2026-03-06 20:27:41', '2026-03-06 20:27:41');


CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_compte` enum('etudiant','entreprise','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'etudiant',
  `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_profil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `adresse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `ville` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pays` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Mauritanie',
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `services` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `technologies` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `linkedin_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portfolio_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `verification_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `derniere_connexion` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `visibilite_entreprise` tinyint(1) DEFAULT '1',
  `commercial_registration_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_identification_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `industry_sector` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_size` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_established` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commercial_registry_doc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_document` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `official_stamp_doc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_status` tinyint(1) DEFAULT '0',
  `account_status` enum('pending','email_verified','admin_approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `company_signature_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_emails` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `additional_phones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `website_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `type_compte`, `telephone`, `photo_profil`, `date_naissance`, `adresse`, `latitude`, `longitude`, `ville`, `pays`, `bio`, `services`, `technologies`, `linkedin_url`, `facebook_url`, `twitter_url`, `instagram_url`, `portfolio_url`, `actif`, `verification_token`, `reset_token`, `reset_token_expiry`, `derniere_connexion`, `created_at`, `visibilite_entreprise`, `commercial_registration_number`, `tax_identification_number`, `industry_sector`, `company_size`, `year_established`, `commercial_registry_doc`, `tax_document`, `official_stamp_doc`, `verified_status`, `account_status`, `company_signature_path`, `additional_emails`, `additional_phones`, `website_url`) VALUES ('1', 'Kaber', 'Sidi', 'kaber@hi.com', '$2y$10$Xz1FJtKTmkl6jL5ipRMobObzkADkUz8AlfTthjCvUssVlQSLCXpvG', 'etudiant', '36133585', NULL, NULL, NULL, NULL, NULL, NULL, 'Mauritanie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '73df7638a606ea7cf164995a1bbe74ed4fed52828d76c9e3bdd928751f5a54db', NULL, NULL, '2026-02-14 21:12:18', '2026-02-13 16:30:19', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'pending', NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `type_compte`, `telephone`, `photo_profil`, `date_naissance`, `adresse`, `latitude`, `longitude`, `ville`, `pays`, `bio`, `services`, `technologies`, `linkedin_url`, `facebook_url`, `twitter_url`, `instagram_url`, `portfolio_url`, `actif`, `verification_token`, `reset_token`, `reset_token_expiry`, `derniere_connexion`, `created_at`, `visibilite_entreprise`, `commercial_registration_number`, `tax_identification_number`, `industry_sector`, `company_size`, `year_established`, `commercial_registry_doc`, `tax_document`, `official_stamp_doc`, `verified_status`, `account_status`, `company_signature_path`, `additional_emails`, `additional_phones`, `website_url`) VALUES ('18', 'Abdellahi', 'A', 'Abdellahi@g.com', '$2y$10$7YoL1bnNqI4vpw5YR0kpoucQsxKGjR27D2kWUcO3UI1o.ZgKD94Oy', 'etudiant', '48287378', NULL, NULL, NULL, NULL, NULL, NULL, 'Mauritanie', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '8646b22287e5fc08a57ee43ed100211983efdc1780e627bfaf8e91795e681040', NULL, NULL, '2026-03-07 05:30:41', '2026-02-26 14:58:38', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'pending', NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `type_compte`, `telephone`, `photo_profil`, `date_naissance`, `adresse`, `latitude`, `longitude`, `ville`, `pays`, `bio`, `services`, `technologies`, `linkedin_url`, `facebook_url`, `twitter_url`, `instagram_url`, `portfolio_url`, `actif`, `verification_token`, `reset_token`, `reset_token_expiry`, `derniere_connexion`, `created_at`, `visibilite_entreprise`, `commercial_registration_number`, `tax_identification_number`, `industry_sector`, `company_size`, `year_established`, `commercial_registry_doc`, `tax_document`, `official_stamp_doc`, `verified_status`, `account_status`, `company_signature_path`, `additional_emails`, `additional_phones`, `website_url`) VALUES ('19', 'SEYID', '', 'seyid@g.com', '$2y$10$frwO79qP7PIlFrVmLOMi1uLM18AKVlzS5hTcZPDipfdzuGJOP9KXC', 'entreprise', '36315846', 'uploads/profiles/user_19_69a3cd3067991.png', NULL, 'aa1245', NULL, NULL, NULL, 'Mauritanie', 'TASIASS', NULL, NULL, 'qwerty', NULL, NULL, NULL, 'https://google.com', '0', '5787cf55a75208480c5ca0abacd942b0491f1000be67f5ae69728079d40f8ce6', NULL, NULL, '2026-03-07 04:08:24', '2026-02-26 15:01:36', '1', '12345678910', '7429559607', 'Finance & Banque', '500+', '2015', NULL, NULL, NULL, '0', 'pending', NULL, '[\"abdellahiseyid10@gmail.com\"]', NULL, 'https://google.com');
INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `type_compte`, `telephone`, `photo_profil`, `date_naissance`, `adresse`, `latitude`, `longitude`, `ville`, `pays`, `bio`, `services`, `technologies`, `linkedin_url`, `facebook_url`, `twitter_url`, `instagram_url`, `portfolio_url`, `actif`, `verification_token`, `reset_token`, `reset_token_expiry`, `derniere_connexion`, `created_at`, `visibilite_entreprise`, `commercial_registration_number`, `tax_identification_number`, `industry_sector`, `company_size`, `year_established`, `commercial_registry_doc`, `tax_document`, `official_stamp_doc`, `verified_status`, `account_status`, `company_signature_path`, `additional_emails`, `additional_phones`, `website_url`) VALUES ('20', 'Student', 'Test', 'teststudent123@gmail.com', '$2y$10$AYB2RWO.doDTRnXMq/fwEeMAD6jVsOSowKeP0zYL3owLcWRyHW18a', 'etudiant', '12345678', NULL, NULL, NULL, NULL, NULL, NULL, 'Mauritanie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '589598964cd130403287cf8d5b6f78bb6d984ea3de62accad10b19c20144cb48', NULL, NULL, '2026-02-26 15:48:25', '2026-02-26 15:44:47', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'pending', NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `type_compte`, `telephone`, `photo_profil`, `date_naissance`, `adresse`, `latitude`, `longitude`, `ville`, `pays`, `bio`, `services`, `technologies`, `linkedin_url`, `facebook_url`, `twitter_url`, `instagram_url`, `portfolio_url`, `actif`, `verification_token`, `reset_token`, `reset_token_expiry`, `derniere_connexion`, `created_at`, `visibilite_entreprise`, `commercial_registration_number`, `tax_identification_number`, `industry_sector`, `company_size`, `year_established`, `commercial_registry_doc`, `tax_document`, `official_stamp_doc`, `verified_status`, `account_status`, `company_signature_path`, `additional_emails`, `additional_phones`, `website_url`) VALUES ('23', 'TASIASS', '', 'seyid2@g.com', '$2y$10$OW3yanELYOAeK/sYCZrD.u1JWsvoH/9aglJ0iXA1WZBQiysmP5e5S', 'entreprise', '46314975', NULL, NULL, 'aa1245', NULL, NULL, NULL, 'Mauritanie', '', NULL, NULL, '', NULL, NULL, NULL, NULL, '0', '09f4329e0e35ee8b489d4f6c67803fdeac2e0b3254322eaab5635951f3a85d11', NULL, NULL, '2026-03-06 01:06:00', '2026-03-02 06:05:10', '1', '12345678910', '7429559607', 'Commerce & Distribution', '201-500', '2015', NULL, NULL, NULL, '0', 'pending', NULL, NULL, NULL, 'https://microsoft.com');
INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `type_compte`, `telephone`, `photo_profil`, `date_naissance`, `adresse`, `latitude`, `longitude`, `ville`, `pays`, `bio`, `services`, `technologies`, `linkedin_url`, `facebook_url`, `twitter_url`, `instagram_url`, `portfolio_url`, `actif`, `verification_token`, `reset_token`, `reset_token_expiry`, `derniere_connexion`, `created_at`, `visibilite_entreprise`, `commercial_registration_number`, `tax_identification_number`, `industry_sector`, `company_size`, `year_established`, `commercial_registry_doc`, `tax_document`, `official_stamp_doc`, `verified_status`, `account_status`, `company_signature_path`, `additional_emails`, `additional_phones`, `website_url`) VALUES ('24', 'med', 'sidi mohamed', 'med@g.com', '$2y$10$qIWCMtsTuxlYs3jM.pm2MumyKH5yKxJPqVI4HkgT6Av9RtfrNFASe', 'etudiant', '48287378', NULL, NULL, NULL, NULL, NULL, NULL, 'Mauritanie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '1d2238d96d76e7fc991172d0d41107e87ec4bd21b5e81721a77da59c57a7c2ec', NULL, NULL, '2026-03-07 03:36:36', '2026-03-02 06:08:49', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'pending', NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `type_compte`, `telephone`, `photo_profil`, `date_naissance`, `adresse`, `latitude`, `longitude`, `ville`, `pays`, `bio`, `services`, `technologies`, `linkedin_url`, `facebook_url`, `twitter_url`, `instagram_url`, `portfolio_url`, `actif`, `verification_token`, `reset_token`, `reset_token_expiry`, `derniere_connexion`, `created_at`, `visibilite_entreprise`, `commercial_registration_number`, `tax_identification_number`, `industry_sector`, `company_size`, `year_established`, `commercial_registry_doc`, `tax_document`, `official_stamp_doc`, `verified_status`, `account_status`, `company_signature_path`, `additional_emails`, `additional_phones`, `website_url`) VALUES ('25', 'Test', 'Student', 'test@student.com', '$2y$10$QWVCSmFl4Hfo3pi3.3jT/.QSwl2YhUuPbcqmAGEeANZTyfK61Vkya', 'etudiant', '123456789', NULL, NULL, NULL, NULL, NULL, NULL, 'Mauritanie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '25ec5d06324fa1af7e07b956ca3b0ef3bbe96517db2b428255dee7491be305ff', NULL, NULL, '2026-03-03 00:02:17', '2026-03-03 00:01:55', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'pending', NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `type_compte`, `telephone`, `photo_profil`, `date_naissance`, `adresse`, `latitude`, `longitude`, `ville`, `pays`, `bio`, `services`, `technologies`, `linkedin_url`, `facebook_url`, `twitter_url`, `instagram_url`, `portfolio_url`, `actif`, `verification_token`, `reset_token`, `reset_token_expiry`, `derniere_connexion`, `created_at`, `visibilite_entreprise`, `commercial_registration_number`, `tax_identification_number`, `industry_sector`, `company_size`, `year_established`, `commercial_registry_doc`, `tax_document`, `official_stamp_doc`, `verified_status`, `account_status`, `company_signature_path`, `additional_emails`, `additional_phones`, `website_url`) VALUES ('26', 'Test', 'StudentStudent', 'student@test.com', '$2y$10$RD24vO5LXU7scnqFcg6YQ.xmpWqLRWDlfdSmqDA7Fd5eNgD/pRzkC', 'etudiant', '12345678', NULL, NULL, NULL, NULL, NULL, NULL, 'Mauritanie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', 'cc802a2b54912420bf12d1587baa1c30a49b00680e6b2231a1e0826d7bf6c606', NULL, NULL, NULL, '2026-03-05 18:08:08', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'pending', NULL, NULL, NULL, NULL);


SET FOREIGN_KEY_CHECKS=1;
