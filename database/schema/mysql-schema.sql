/*M!999999\- enable the sandbox mode */
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_journal_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `route_name` varchar(255) DEFAULT NULL,
  `method` varchar(10) NOT NULL,
  `url` varchar(255) NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `activity_journal_entries_user_id_foreign` (`user_id`),
  KEY `activity_journal_entries_route_name_created_at_index` (`route_name`,`created_at`),
  KEY `activity_journal_entries_action_created_at_index` (`action`,`created_at`),
  CONSTRAINT `activity_journal_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `badge_competency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `badge_competency` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `badge_id` bigint(20) unsigned NOT NULL,
  `competency_id` bigint(20) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_badge_comp` (`badge_id`,`competency_id`),
  KEY `badge_competency_competency_id_foreign` (`competency_id`),
  KEY `badge_competency_badge_id_position_index` (`badge_id`,`position`),
  CONSTRAINT `badge_competency_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `badge_competency_competency_id_foreign` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `badges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(80) DEFAULT NULL,
  `label` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `badges_code_unique` (`code`),
  KEY `badges_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `category_description` text DEFAULT NULL,
  `category_slug` varchar(255) NOT NULL,
  `category_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `competencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `competencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(80) DEFAULT NULL,
  `label` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `competencies_code_unique` (`code`),
  KEY `competencies_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `prenom` varchar(255) DEFAULT NULL,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `heure_appel` varchar(255) DEFAULT NULL,
  `type_utilisateur` enum('formateur','stagiaire') NOT NULL,
  `objet` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`objet`)),
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `scorm_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `formateur_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `formateur_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `formateur_id` bigint(20) unsigned NOT NULL,
  `stagiaire_id` bigint(20) unsigned NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `sent_as_notification` tinyint(1) NOT NULL DEFAULT 0,
  `sent_as_email` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `formateur_messages_formateur_id_foreign` (`formateur_id`),
  KEY `formateur_messages_stagiaire_id_foreign` (`stagiaire_id`),
  CONSTRAINT `formateur_messages_formateur_id_foreign` FOREIGN KEY (`formateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `formateur_messages_stagiaire_id_foreign` FOREIGN KEY (`stagiaire_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `formateur_parcours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `formateur_parcours` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `formateur_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `formateur_parcours_formateur_id_index` (`formateur_id`),
  CONSTRAINT `formateur_parcours_formateur_id_foreign` FOREIGN KEY (`formateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `formateur_parcours_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `formateur_parcours_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `formateur_parcours_id` bigint(20) unsigned NOT NULL,
  `position` smallint(5) unsigned NOT NULL DEFAULT 1,
  `type` varchar(20) NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `wc_title` varchar(255) DEFAULT NULL,
  `wc_questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`wc_questions`)),
  `wc_duration` smallint(5) unsigned DEFAULT NULL,
  `poll_questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`poll_questions`)),
  `poll_duration` smallint(5) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `formateur_parcours_items_module_id_foreign` (`module_id`),
  KEY `parcours_items_position_idx` (`formateur_parcours_id`,`position`),
  CONSTRAINT `formateur_parcours_items_formateur_parcours_id_foreign` FOREIGN KEY (`formateur_parcours_id`) REFERENCES `formateur_parcours` (`id`) ON DELETE CASCADE,
  CONSTRAINT `formateur_parcours_items_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_module` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_module_group_id_module_id_unique` (`group_id`,`module_id`),
  KEY `group_module_module_id_foreign` (`module_id`),
  CONSTRAINT `group_module_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_module_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_module_lectures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_module_lectures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned NOT NULL,
  `lecture_id` bigint(20) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gml_unique` (`group_id`,`module_id`,`lecture_id`),
  KEY `group_module_lectures_module_id_foreign` (`module_id`),
  KEY `group_module_lectures_lecture_id_foreign` (`lecture_id`),
  KEY `gml_group_module_idx` (`group_id`,`module_id`),
  CONSTRAINT `group_module_lectures_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_module_lectures_lecture_id_foreign` FOREIGN KEY (`lecture_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_module_lectures_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_timers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_timers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) unsigned NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `duration_seconds` int(10) unsigned NOT NULL DEFAULT 1500,
  `status` enum('idle','running','paused','finished') NOT NULL DEFAULT 'idle',
  `started_at` timestamp NULL DEFAULT NULL,
  `elapsed_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_timers_group_id_unique` (`group_id`),
  KEY `group_timers_created_by_foreign` (`created_by`),
  KEY `group_timers_updated_by_foreign` (`updated_by`),
  KEY `group_timers_group_id_status_index` (`group_id`,`status`),
  CONSTRAINT `group_timers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `group_timers_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_timers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_in_group` enum('stagiaire','formateur','observateur') NOT NULL DEFAULT 'stagiaire',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_user_group_id_user_id_unique` (`group_id`,`user_id`),
  KEY `group_user_user_id_foreign` (`user_id`),
  CONSTRAINT `group_user_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_whiteboard_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_whiteboard_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_whiteboard_id` bigint(20) unsigned NOT NULL,
  `client_uuid` varchar(80) NOT NULL,
  `type` varchar(32) NOT NULL,
  `x` decimal(10,2) NOT NULL DEFAULT 0.00,
  `y` decimal(10,2) NOT NULL DEFAULT 0.00,
  `width` decimal(10,2) NOT NULL DEFAULT 280.00,
  `height` decimal(10,2) NOT NULL DEFAULT 180.00,
  `rotation` decimal(8,2) NOT NULL DEFAULT 0.00,
  `z_index` int(10) unsigned NOT NULL DEFAULT 0,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `style` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`style`)),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_whiteboard_items_board_uuid_unique` (`group_whiteboard_id`,`client_uuid`),
  KEY `group_whiteboard_items_created_by_foreign` (`created_by`),
  KEY `group_whiteboard_items_updated_by_foreign` (`updated_by`),
  KEY `group_whiteboard_items_board_z_index` (`group_whiteboard_id`,`z_index`),
  CONSTRAINT `group_whiteboard_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `group_whiteboard_items_group_whiteboard_id_foreign` FOREIGN KEY (`group_whiteboard_id`) REFERENCES `group_whiteboards` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_whiteboard_items_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_whiteboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_whiteboards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `excalidraw_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`excalidraw_data`)),
  `version` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_whiteboards_group_id_unique` (`group_id`),
  KEY `group_whiteboards_created_by_foreign` (`created_by`),
  KEY `group_whiteboards_updated_by_foreign` (`updated_by`),
  KEY `group_whiteboards_group_id_updated_at_index` (`group_id`,`updated_at`),
  CONSTRAINT `group_whiteboards_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `group_whiteboards_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_whiteboards_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_sandbox` tinyint(1) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `temporary_password` text DEFAULT NULL,
  `instructor_id` bigint(20) unsigned NOT NULL,
  `formateur_parcours_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groups_name_unique` (`name`),
  KEY `groups_instructor_id_foreign` (`instructor_id`),
  KEY `groups_formateur_parcours_id_foreign` (`formateur_parcours_id`),
  CONSTRAINT `groups_formateur_parcours_id_foreign` FOREIGN KEY (`formateur_parcours_id`) REFERENCES `formateur_parcours` (`id`) ON DELETE SET NULL,
  CONSTRAINT `groups_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `learning_objectives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `learning_objectives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `progress` tinyint(4) NOT NULL DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `learning_objectives_module_id_foreign` (`module_id`),
  KEY `learning_objectives_user_id_foreign` (`user_id`),
  CONSTRAINT `learning_objectives_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learning_objectives_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lecture_objective_competency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lecture_objective_competency` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lecture_objective_id` bigint(20) unsigned NOT NULL,
  `competency_id` bigint(20) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_obj_comp` (`lecture_objective_id`,`competency_id`),
  KEY `lecture_objective_competency_competency_id_foreign` (`competency_id`),
  KEY `lecture_objective_competency_lecture_objective_id_position_index` (`lecture_objective_id`,`position`),
  CONSTRAINT `lecture_objective_competency_competency_id_foreign` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lecture_objective_competency_lecture_objective_id_foreign` FOREIGN KEY (`lecture_objective_id`) REFERENCES `lecture_objectives` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lecture_objectives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lecture_objectives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lecture_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lecture_objectives_lecture_id_position_index` (`lecture_id`,`position`),
  CONSTRAINT `lecture_objectives_lecture_id_foreign` FOREIGN KEY (`lecture_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lesson_feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_feedbacks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `comment` text NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `urgency` tinyint(4) DEFAULT NULL,
  `response` text DEFAULT NULL,
  `status` enum('en_attente','traite') NOT NULL DEFAULT 'en_attente',
  `rating` tinyint(3) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lesson_feedbacks_user_id_foreign` (`user_id`),
  KEY `lesson_feedbacks_lesson_id_foreign` (`lesson_id`),
  CONSTRAINT `lesson_feedbacks_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_feedbacks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lesson_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_resources` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lecture_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `is_visible_to_stagiaire` tinyint(1) NOT NULL DEFAULT 0,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lesson_resources_lecture_id_foreign` (`lecture_id`),
  KEY `lesson_resources_module_id_foreign` (`module_id`),
  CONSTRAINT `lesson_resources_lecture_id_foreign` FOREIGN KEY (`lecture_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_resources_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `live_quiz_session_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `live_quiz_session_participants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `live_quiz_session_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `attempt_id` bigint(20) unsigned NOT NULL,
  `joined_at` timestamp NULL DEFAULT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `live_quiz_session_participants_session_user_unique` (`live_quiz_session_id`,`user_id`),
  UNIQUE KEY `live_quiz_session_participants_session_attempt_unique` (`live_quiz_session_id`,`attempt_id`),
  KEY `live_quiz_session_participants_user_id_foreign` (`user_id`),
  KEY `live_quiz_session_participants_attempt_id_foreign` (`attempt_id`),
  CONSTRAINT `live_quiz_session_participants_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `live_quiz_session_participants_live_quiz_session_id_foreign` FOREIGN KEY (`live_quiz_session_id`) REFERENCES `live_quiz_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `live_quiz_session_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `live_quiz_session_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `live_quiz_session_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `live_quiz_session_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `live_quiz_session_questions_session_position_unique` (`live_quiz_session_id`,`position`),
  UNIQUE KEY `live_quiz_session_questions_session_question_unique` (`live_quiz_session_id`,`question_id`),
  KEY `live_quiz_session_questions_question_id_foreign` (`question_id`),
  CONSTRAINT `live_quiz_session_questions_live_quiz_session_id_foreign` FOREIGN KEY (`live_quiz_session_id`) REFERENCES `live_quiz_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `live_quiz_session_questions_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `live_quiz_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `live_quiz_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `formateur_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned DEFAULT NULL,
  `module_id` bigint(20) unsigned NOT NULL,
  `section_id` bigint(20) unsigned NOT NULL,
  `lecture_id` bigint(20) unsigned NOT NULL,
  `access_code` varchar(12) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'waiting',
  `current_position` int(10) unsigned NOT NULL DEFAULT 0,
  `total_questions` int(10) unsigned NOT NULL DEFAULT 0,
  `answer_revealed_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `live_quiz_sessions_access_code_unique` (`access_code`),
  KEY `live_quiz_sessions_module_id_foreign` (`module_id`),
  KEY `live_quiz_sessions_section_id_foreign` (`section_id`),
  KEY `live_quiz_sessions_lecture_id_status_index` (`lecture_id`,`status`),
  KEY `live_quiz_sessions_formateur_id_ended_at_index` (`formateur_id`,`ended_at`),
  KEY `live_quiz_sessions_group_id_foreign` (`group_id`),
  CONSTRAINT `live_quiz_sessions_formateur_id_foreign` FOREIGN KEY (`formateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `live_quiz_sessions_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `live_quiz_sessions_lecture_id_foreign` FOREIGN KEY (`lecture_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `live_quiz_sessions_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `live_quiz_sessions_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `module_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `module_completion_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_completion_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint(20) unsigned NOT NULL,
  `stagiaire_id` bigint(20) unsigned NOT NULL,
  `recipient_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_module_completion_notif` (`module_id`,`stagiaire_id`,`recipient_id`),
  KEY `module_completion_notifications_stagiaire_id_foreign` (`stagiaire_id`),
  KEY `module_completion_notifications_recipient_id_created_at_index` (`recipient_id`,`created_at`),
  CONSTRAINT `module_completion_notifications_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `module_completion_notifications_recipient_id_foreign` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `module_completion_notifications_stagiaire_id_foreign` FOREIGN KEY (`stagiaire_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `module_lectures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_lectures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint(20) unsigned NOT NULL,
  `section_id` bigint(20) unsigned NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `lecture_title` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `scorm_path` varchar(255) DEFAULT NULL,
  `html_content` longtext DEFAULT NULL,
  `content_blocks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content_blocks`)),
  `content_type` varchar(20) NOT NULL DEFAULT 'scorm',
  `slides_status` varchar(20) NOT NULL DEFAULT 'none',
  `slides_path` varchar(255) DEFAULT NULL,
  `slides_source_path` varchar(255) DEFAULT NULL,
  `slides_error` text DEFAULT NULL,
  `slides_converted_at` timestamp NULL DEFAULT NULL,
  `scorm_package_id` bigint(20) unsigned DEFAULT NULL,
  `scorm_package_version_id` bigint(20) unsigned DEFAULT NULL,
  `use_active_scorm_version` tinyint(1) NOT NULL DEFAULT 1,
  `slide_count` int(10) unsigned NOT NULL DEFAULT 0,
  `question_count` int(10) unsigned NOT NULL DEFAULT 0,
  `quiz_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `quiz_questions_per_attempt` int(10) unsigned NOT NULL DEFAULT 0,
  `live_quiz_entry_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Durée estimée en minutes',
  PRIMARY KEY (`id`),
  KEY `module_lectures_module_id_foreign` (`module_id`),
  KEY `module_lectures_section_id_foreign` (`section_id`),
  KEY `module_lectures_scorm_package_id_foreign` (`scorm_package_id`),
  KEY `module_lectures_scorm_package_version_id_foreign` (`scorm_package_version_id`),
  CONSTRAINT `module_lectures_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `module_lectures_scorm_package_id_foreign` FOREIGN KEY (`scorm_package_id`) REFERENCES `scorm_packages` (`id`),
  CONSTRAINT `module_lectures_scorm_package_version_id_foreign` FOREIGN KEY (`scorm_package_version_id`) REFERENCES `scorm_package_versions` (`id`),
  CONSTRAINT `module_lectures_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `module_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `module_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint(20) unsigned NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `objectif` text DEFAULT NULL,
  `methode` text DEFAULT NULL,
  `contexte` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `scorm_video_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `section_html` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `module_sections_module_id_foreign` (`module_id`),
  CONSTRAINT `module_sections_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `subcategory_id` bigint(20) unsigned NOT NULL,
  `formateur_id` bigint(20) unsigned NOT NULL,
  `is_trainer_authored` tinyint(1) NOT NULL DEFAULT 0,
  `evaluation_id` bigint(20) unsigned DEFAULT NULL,
  `module_image` varchar(255) DEFAULT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `module_title` text DEFAULT NULL,
  `module_name` text DEFAULT NULL,
  `module_name_slug` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `objectifs` text DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `duree` varchar(255) DEFAULT NULL,
  `estimated_question_seconds` smallint(5) unsigned NOT NULL DEFAULT 30,
  `module_video` varchar(255) DEFAULT NULL,
  `resources` varchar(255) DEFAULT NULL,
  `certificat` tinyint(1) NOT NULL DEFAULT 0,
  `prerequi` text DEFAULT NULL,
  `bestseller` tinyint(1) NOT NULL DEFAULT 0,
  `vedette` tinyint(1) NOT NULL DEFAULT 0,
  `surevalue` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=Inactive, 1=Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `modules_category_id_foreign` (`category_id`),
  KEY `modules_subcategory_id_foreign` (`subcategory_id`),
  KEY `modules_formateur_id_foreign` (`formateur_id`),
  KEY `modules_evaluation_id_foreign` (`evaluation_id`),
  CONSTRAINT `modules_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `modules_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `modules_formateur_id_foreign` FOREIGN KEY (`formateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `modules_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pilot_notification_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pilot_notification_preferences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `in_app_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `email_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `frequency` varchar(16) NOT NULL DEFAULT 'immediate',
  `event_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`event_types`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pilot_notification_preferences_user_id_unique` (`user_id`),
  CONSTRAINT `pilot_notification_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pilot_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pilot_projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pilot_projects_created_by_foreign` (`created_by`),
  KEY `pilot_projects_module_id_due_date_index` (`module_id`,`due_date`),
  CONSTRAINT `pilot_projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pilot_projects_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pilot_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pilot_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `task_id` bigint(20) unsigned DEFAULT NULL,
  `notify_in_app` tinyint(1) NOT NULL DEFAULT 1,
  `notify_mail` tinyint(1) NOT NULL DEFAULT 0,
  `frequency` varchar(16) NOT NULL DEFAULT 'immediate',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pilot_subscriptions_project_id_foreign` (`project_id`),
  KEY `pilot_subscriptions_task_id_foreign` (`task_id`),
  KEY `pilot_subscriptions_user_id_project_id_index` (`user_id`,`project_id`),
  KEY `pilot_subscriptions_user_id_task_id_index` (`user_id`,`task_id`),
  KEY `pilot_subscriptions_frequency_index` (`frequency`),
  CONSTRAINT `pilot_subscriptions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `pilot_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pilot_subscriptions_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `pilot_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pilot_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pilot_task_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pilot_task_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pilot_task_comments_task_id_foreign` (`task_id`),
  KEY `pilot_task_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `pilot_task_comments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `pilot_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pilot_task_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pilot_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pilot_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'todo',
  `priority` varchar(16) NOT NULL DEFAULT 'normal',
  `due_date` date DEFAULT NULL,
  `responsible_id` bigint(20) unsigned DEFAULT NULL,
  `task_type` varchar(32) NOT NULL,
  `internal_url` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pilot_tasks_project_id_foreign` (`project_id`),
  KEY `pilot_tasks_responsible_id_foreign` (`responsible_id`),
  KEY `pilot_tasks_created_by_foreign` (`created_by`),
  KEY `pilot_tasks_status_position_index` (`status`,`position`),
  KEY `pilot_tasks_task_type_priority_index` (`task_type`,`priority`),
  KEY `pilot_tasks_module_id_responsible_id_index` (`module_id`,`responsible_id`),
  KEY `pilot_tasks_due_date_index` (`due_date`),
  CONSTRAINT `pilot_tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pilot_tasks_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pilot_tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `pilot_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pilot_tasks_responsible_id_foreign` FOREIGN KEY (`responsible_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `poll_session_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `poll_session_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `poll_session_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `question_index` smallint(5) unsigned NOT NULL,
  `choice_index` smallint(5) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `poll_session_responses_unique_answer` (`poll_session_id`,`user_id`,`question_index`),
  KEY `poll_session_responses_user_id_foreign` (`user_id`),
  KEY `poll_session_responses_poll_session_id_question_index_index` (`poll_session_id`,`question_index`),
  CONSTRAINT `poll_session_responses_poll_session_id_foreign` FOREIGN KEY (`poll_session_id`) REFERENCES `poll_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poll_session_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `poll_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `poll_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `formateur_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`questions`)),
  `access_code` varchar(6) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `opened_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `poll_sessions_access_code_unique` (`access_code`),
  KEY `poll_sessions_formateur_id_created_at_index` (`formateur_id`,`created_at`),
  KEY `poll_sessions_group_id_is_active_index` (`group_id`,`is_active`),
  CONSTRAINT `poll_sessions_formateur_id_foreign` FOREIGN KEY (`formateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poll_sessions_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `progressions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `progressions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lecture_id` bigint(20) unsigned NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `progressions_user_id_foreign` (`user_id`),
  KEY `progressions_lecture_id_foreign` (`lecture_id`),
  CONSTRAINT `progressions_lecture_id_foreign` FOREIGN KEY (`lecture_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `progressions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `question_wall_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_wall_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_wall_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `body` text NOT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(16) NOT NULL DEFAULT 'open',
  `acted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question_wall_questions_user_id_foreign` (`user_id`),
  KEY `question_wall_questions_question_wall_id_status_index` (`question_wall_id`,`status`),
  KEY `question_wall_questions_question_wall_id_created_at_index` (`question_wall_id`,`created_at`),
  CONSTRAINT `question_wall_questions_question_wall_id_foreign` FOREIGN KEY (`question_wall_id`) REFERENCES `question_walls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `question_wall_questions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `question_wall_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_wall_votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_wall_question_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_wall_votes_question_user_unique` (`question_wall_question_id`,`user_id`),
  KEY `question_wall_votes_user_id_foreign` (`user_id`),
  CONSTRAINT `question_wall_votes_question_wall_question_id_foreign` FOREIGN KEY (`question_wall_question_id`) REFERENCES `question_wall_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `question_wall_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `question_walls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_walls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `formateur_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `access_code` varchar(6) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_walls_access_code_unique` (`access_code`),
  KEY `question_walls_formateur_id_created_at_index` (`formateur_id`,`created_at`),
  KEY `question_walls_group_id_is_active_index` (`group_id`,`is_active`),
  CONSTRAINT `question_walls_formateur_id_foreign` FOREIGN KEY (`formateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `question_walls_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_attempt_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_attempt_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attempt_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `given_answer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`given_answer`)),
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `question_started_at` timestamp NULL DEFAULT NULL,
  `answered_at` timestamp NULL DEFAULT NULL,
  `time_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `answer_option_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answer_option_ids`)),
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quiz_attempt_questions_attempt_id_question_id_unique` (`attempt_id`,`question_id`),
  KEY `quiz_attempt_questions_question_id_foreign` (`question_id`),
  KEY `quiz_attempt_questions_attempt_id_position_index` (`attempt_id`,`position`),
  CONSTRAINT `quiz_attempt_questions_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_attempt_questions_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lecture_id` bigint(20) unsigned NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `total_questions` int(10) unsigned NOT NULL DEFAULT 0,
  `score` int(10) unsigned NOT NULL DEFAULT 0,
  `percent` int(10) unsigned NOT NULL DEFAULT 0,
  `passed` tinyint(1) NOT NULL DEFAULT 0,
  `total_time_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_attempts_lecture_id_foreign` (`lecture_id`),
  KEY `quiz_attempts_user_id_lecture_id_passed_index` (`user_id`,`lecture_id`,`passed`),
  CONSTRAINT `quiz_attempts_lecture_id_foreign` FOREIGN KEY (`lecture_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_options` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) unsigned NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_options_question_id_is_correct_index` (`question_id`,`is_correct`),
  CONSTRAINT `quiz_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lecture_id` bigint(20) unsigned NOT NULL,
  `type` enum('single','multiple','boolean','cloze') NOT NULL,
  `question_text` text NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `image_path` varchar(255) DEFAULT NULL,
  `image_alt` varchar(255) DEFAULT NULL,
  `audio_path` varchar(255) DEFAULT NULL,
  `audio_transcript` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_questions_created_by_foreign` (`created_by`),
  KEY `quiz_questions_lecture_id_is_active_index` (`lecture_id`,`is_active`),
  CONSTRAINT `quiz_questions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quiz_questions_lecture_id_foreign` FOREIGN KEY (`lecture_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `random_wheel_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `random_wheel_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `formateur_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned NOT NULL,
  `access_code` varchar(6) NOT NULL,
  `entries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`entries`)),
  `active_entry_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`active_entry_ids`)),
  `picks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`picks`)),
  `current_pick_id` bigint(20) unsigned DEFAULT NULL,
  `spun_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `random_wheel_sessions_access_code_unique` (`access_code`),
  KEY `random_wheel_sessions_group_id_foreign` (`group_id`),
  KEY `random_wheel_sessions_formateur_id_created_at_index` (`formateur_id`,`created_at`),
  CONSTRAINT `random_wheel_sessions_formateur_id_foreign` FOREIGN KEY (`formateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `random_wheel_sessions_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scale_session_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scale_session_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scale_session_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `question_index` smallint(5) unsigned NOT NULL,
  `value` smallint(6) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scale_session_responses_unique_answer` (`scale_session_id`,`user_id`,`question_index`),
  KEY `scale_session_responses_user_id_foreign` (`user_id`),
  KEY `scale_session_responses_scale_session_id_question_index_index` (`scale_session_id`,`question_index`),
  CONSTRAINT `scale_session_responses_scale_session_id_foreign` FOREIGN KEY (`scale_session_id`) REFERENCES `scale_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scale_session_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scale_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scale_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `formateur_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`questions`)),
  `access_code` varchar(6) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `opened_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scale_sessions_access_code_unique` (`access_code`),
  KEY `scale_sessions_formateur_id_created_at_index` (`formateur_id`,`created_at`),
  KEY `scale_sessions_group_id_is_active_index` (`group_id`,`is_active`),
  CONSTRAINT `scale_sessions_formateur_id_foreign` FOREIGN KEY (`formateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scale_sessions_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scorm_evaluation_interactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scorm_evaluation_interactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `evaluation_id` bigint(20) unsigned NOT NULL,
  `interaction_id` varchar(255) DEFAULT NULL,
  `interaction_type` varchar(255) DEFAULT NULL,
  `interaction_weighting` varchar(255) DEFAULT NULL,
  `result` varchar(255) DEFAULT NULL,
  `response` varchar(255) DEFAULT NULL,
  `correct_response` varchar(255) DEFAULT NULL,
  `latency` varchar(255) DEFAULT NULL,
  `time` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scorm_evaluation_interactions_user_id_foreign` (`user_id`),
  KEY `scorm_evaluation_interactions_evaluation_id_foreign` (`evaluation_id`),
  CONSTRAINT `scorm_evaluation_interactions_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scorm_evaluation_interactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scorm_evaluation_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scorm_evaluation_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `evaluation_id` bigint(20) unsigned NOT NULL,
  `scorm_key` varchar(255) NOT NULL,
  `scorm_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scorm_evaluation_results_user_id_foreign` (`user_id`),
  KEY `scorm_evaluation_results_evaluation_id_foreign` (`evaluation_id`),
  CONSTRAINT `scorm_evaluation_results_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scorm_evaluation_results_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scorm_evaluation_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scorm_evaluation_scores` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `evaluation_id` bigint(20) unsigned NOT NULL,
  `first_score` int(11) DEFAULT NULL,
  `last_score` int(11) DEFAULT NULL,
  `best_score` int(11) DEFAULT NULL,
  `attempts_count` int(11) NOT NULL DEFAULT 0,
  `questions_answered` int(11) NOT NULL DEFAULT 0,
  `session_time` int(11) NOT NULL DEFAULT 0,
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `lesson_status` varchar(255) DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scorm_evaluation_scores_user_id_foreign` (`user_id`),
  KEY `scorm_evaluation_scores_evaluation_id_foreign` (`evaluation_id`),
  CONSTRAINT `scorm_evaluation_scores_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scorm_evaluation_scores_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scorm_interactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scorm_interactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lecture_id` bigint(20) unsigned NOT NULL,
  `interaction_id` varchar(255) DEFAULT NULL,
  `interaction_key` varchar(255) DEFAULT NULL,
  `interaction_type` varchar(255) DEFAULT NULL,
  `interaction_weighting` varchar(255) DEFAULT NULL,
  `result` varchar(255) DEFAULT NULL,
  `response` varchar(255) DEFAULT NULL,
  `correct_response` varchar(255) DEFAULT NULL,
  `latency` varchar(255) DEFAULT NULL,
  `time` varchar(255) DEFAULT NULL,
  `lesson_status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scorm_interactions_user_id_lecture_id_interaction_id_unique` (`user_id`,`lecture_id`,`interaction_id`),
  UNIQUE KEY `unique_scorm_interaction_key` (`user_id`,`lecture_id`,`interaction_key`),
  KEY `scorm_interactions_lecture_id_foreign` (`lecture_id`),
  CONSTRAINT `scorm_interactions_lecture_id_foreign` FOREIGN KEY (`lecture_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scorm_interactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scorm_package_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scorm_package_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scorm_package_id` bigint(20) unsigned NOT NULL,
  `version` varchar(255) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `index_path` varchar(255) NOT NULL,
  `size_bytes` bigint(20) unsigned NOT NULL DEFAULT 0,
  `api_injected` tinyint(1) NOT NULL DEFAULT 0,
  `imported_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scorm_package_versions_scorm_package_id_version_unique` (`scorm_package_id`,`version`),
  CONSTRAINT `scorm_package_versions_scorm_package_id_foreign` FOREIGN KEY (`scorm_package_id`) REFERENCES `scorm_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scorm_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scorm_packages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `active_version_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scorm_packages_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scorm_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scorm_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lecture_id` bigint(20) unsigned NOT NULL,
  `scorm_key` varchar(255) NOT NULL,
  `scorm_value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scorm_results_user_id_foreign` (`user_id`),
  KEY `scorm_results_lecture_id_foreign` (`lecture_id`),
  CONSTRAINT `scorm_results_lecture_id_foreign` FOREIGN KEY (`lecture_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scorm_results_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scorm_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scorm_scores` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lecture_id` bigint(20) unsigned NOT NULL,
  `lesson_status` varchar(255) DEFAULT NULL,
  `first_score` tinyint(3) unsigned DEFAULT NULL,
  `best_score` tinyint(3) unsigned DEFAULT NULL,
  `attempts_count` smallint(5) unsigned NOT NULL DEFAULT 1,
  `questions_answered` smallint(5) unsigned DEFAULT NULL,
  `last_score` tinyint(3) unsigned DEFAULT NULL,
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `session_time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scorm_scores_user_id_lecture_id_unique` (`user_id`,`lecture_id`),
  KEY `scorm_scores_lecture_id_foreign` (`lecture_id`),
  CONSTRAINT `scorm_scores_lecture_id_foreign` FOREIGN KEY (`lecture_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scorm_scores_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_foreign` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `skill_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `skill_domains` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `skill_referential_id` bigint(20) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `skill_domains_skill_referential_id_position_index` (`skill_referential_id`,`position`),
  CONSTRAINT `skill_domains_skill_referential_id_foreign` FOREIGN KEY (`skill_referential_id`) REFERENCES `skill_referentials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `skill_referentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `skill_referentials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skill_referentials_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `skills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `skill_referential_id` bigint(20) unsigned NOT NULL,
  `skill_domain_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skills_skill_referential_id_code_unique` (`skill_referential_id`,`code`),
  KEY `skills_skill_domain_id_foreign` (`skill_domain_id`),
  KEY `skills_skill_referential_id_skill_domain_id_position_index` (`skill_referential_id`,`skill_domain_id`,`position`),
  CONSTRAINT `skills_skill_domain_id_foreign` FOREIGN KEY (`skill_domain_id`) REFERENCES `skill_domains` (`id`) ON DELETE SET NULL,
  CONSTRAINT `skills_skill_referential_id_foreign` FOREIGN KEY (`skill_referential_id`) REFERENCES `skill_referentials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subcategories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `subcategory_name` varchar(255) NOT NULL,
  `subcategory_description` text DEFAULT NULL,
  `subcategory_slug` varchar(255) NOT NULL,
  `subcategory_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subcategories_category_id_foreign` (`category_id`),
  CONSTRAINT `subcategories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trainer_module_questionnaire_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `trainer_module_questionnaire_submissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `submission_uuid` char(36) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_number` tinyint(3) unsigned NOT NULL,
  `module_key` varchar(255) NOT NULL,
  `questionnaire_key` varchar(255) NOT NULL,
  `questionnaire_version` smallint(5) unsigned NOT NULL DEFAULT 1,
  `responses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responses`)),
  `submitted_at` timestamp NOT NULL,
  `emailed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trainer_module_questionnaire_submissions_submission_uuid_unique` (`submission_uuid`),
  KEY `tmqs_module_questionnaire_index` (`module_number`,`questionnaire_key`),
  KEY `tmqs_user_module_index` (`user_id`,`module_key`),
  CONSTRAINT `trainer_module_questionnaire_submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trainer_path_activity_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `trainer_path_activity_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_key` varchar(255) NOT NULL,
  `chapter_key` varchar(255) NOT NULL,
  `lesson_key` varchar(255) NOT NULL,
  `activity_key` varchar(255) NOT NULL,
  `activity_type` varchar(255) NOT NULL DEFAULT 'sorting',
  `attempt_number` int(10) unsigned NOT NULL DEFAULT 1,
  `total_items` int(10) unsigned NOT NULL DEFAULT 0,
  `correct_items` int(10) unsigned NOT NULL DEFAULT 0,
  `is_success` tinyint(1) NOT NULL DEFAULT 0,
  `submitted_answer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`submitted_answer`)),
  `expected_answer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`expected_answer`)),
  `wrong_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`wrong_items`)),
  `submitted_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trainer_path_activity_user_module_idx` (`user_id`,`module_key`,`chapter_key`,`lesson_key`),
  KEY `trainer_path_activity_status_idx` (`activity_key`,`is_success`),
  CONSTRAINT `trainer_path_activity_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `formateur_id` bigint(20) unsigned DEFAULT NULL,
  `prenom` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `societe` varchar(255) DEFAULT NULL,
  `role` enum('stagiaire','formateur','admin','observateur') NOT NULL DEFAULT 'stagiaire',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `adhesion_status` varchar(20) NOT NULL DEFAULT 'pending',
  `adhesion_valid_until` date DEFAULT NULL,
  `adhesion_verified_at` timestamp NULL DEFAULT NULL,
  `adhesion_verified_by` bigint(20) unsigned DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `total_site_time` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `code_acces` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_code_acces_unique` (`code_acces`),
  KEY `users_formateur_id_foreign` (`formateur_id`),
  KEY `users_adhesion_verified_by_foreign` (`adhesion_verified_by`),
  CONSTRAINT `users_adhesion_verified_by_foreign` FOREIGN KEY (`adhesion_verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_formateur_id_foreign` FOREIGN KEY (`formateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `video_segment_trackings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `video_segment_trackings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lecture_id` bigint(20) unsigned NOT NULL,
  `segment_start` int(11) NOT NULL,
  `segment_end` int(11) NOT NULL,
  `watch_count` int(11) NOT NULL DEFAULT 0,
  `total_watch_time` double NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `video_segment_trackings_user_id_foreign` (`user_id`),
  KEY `video_segment_trackings_lecture_id_foreign` (`lecture_id`),
  CONSTRAINT `video_segment_trackings_lecture_id_foreign` FOREIGN KEY (`lecture_id`) REFERENCES `module_lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `video_segment_trackings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `word_cloud_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `word_cloud_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `word_cloud_id` bigint(20) unsigned DEFAULT NULL,
  `formateur_parcours_item_id` bigint(20) unsigned DEFAULT NULL,
  `question_index` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `answer` varchar(150) NOT NULL,
  `normalized_answer` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_parcours_wc_answer` (`formateur_parcours_item_id`,`question_index`,`user_id`),
  KEY `word_cloud_entries_user_id_foreign` (`user_id`),
  KEY `word_cloud_entries_word_cloud_id_created_at_index` (`word_cloud_id`,`created_at`),
  KEY `word_cloud_entries_normalized_answer_index` (`normalized_answer`),
  CONSTRAINT `word_cloud_entries_formateur_parcours_item_id_foreign` FOREIGN KEY (`formateur_parcours_item_id`) REFERENCES `formateur_parcours_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `word_cloud_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `word_cloud_entries_word_cloud_id_foreign` FOREIGN KEY (`word_cloud_id`) REFERENCES `word_clouds` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `word_clouds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `word_clouds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `group_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `question` text NOT NULL,
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`questions`)),
  `access_code` varchar(12) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `opened_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `word_clouds_access_code_unique` (`access_code`),
  KEY `word_clouds_module_id_foreign` (`module_id`),
  KEY `word_clouds_group_id_foreign` (`group_id`),
  CONSTRAINT `word_clouds_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `word_clouds_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

/*M!999999\- enable the sandbox mode */
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_03_01_152713_create_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_03_09_143822_create_sub_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_03_10_214601_create_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_03_10_214611_create_group_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_03_16_195920_create_modules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_03_16_201045_create_learning_objectives_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_03_23_140006_add_formateur_id_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_03_28_053345_create_module_sections_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_03_28_053401_create_module_lectures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_04_06_071122_add_scorm_path_to_module_lectures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_04_06_112139_create_scorm_results_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_04_08_193327_create_group_module_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_04_16_130348_create_progressions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_04_20_070416_create_scorm_scores_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_04_21_163924_create_scorm_interactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_04_26_192429_add_position_to_module_lectures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_04_26_201930_create_lesson_feedbacks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_04_27_061903_add_type_response_status_to_lesson_feedbacks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_04_27_130314_add_urgency_to_lesson_feedbacks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_04_27_145704_add_deleted_at_to_lesson_feedbacks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_05_11_064950_add_section_html_to_module_sections_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_05_11_152452_add_code_acces_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_05_15_191643_create_evaluations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_05_18_053656_add_evaluation_id_to_modules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_05_18_182626_create_scorm_evaluation_results_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_05_18_182626_create_scorm_evaluation_scores_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_05_18_182635_create_scorm_evaluation_interactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_05_24_082057_add_pedagogie_fields_to_module_sections_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_05_25_142429_create_video_segment_trackings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_05_25_152848_add_video_url_to_module_sections_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_05_31_083505_create_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_07_13_000001_add_evaluation_fk_to_modules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_07_13_092820_add_total_site_time_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_07_13_093044_add_session_time_to_scorm_scores_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_07_14_083811_add_lesson_status_to_scorm_scores_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_07_20_143844_update_module_lectures_structure',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_07_26_085022_add_interaction_key_to_scorm_interactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_07_31_212521_add_module_video_to_modules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_09_13_132609_add_deleted_at_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_09_14_060149_add_objectifs_to_modules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_09_20_154729_alter_foreign_keys_for_user_cascade',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_01_04_103504_create_skill_referentials_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_01_04_113808_create_skill_domains_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_01_04_113808_create_skills_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_01_09_210826_add_quiz_settings_to_module_lectures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_01_09_210909_create_quiz_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_01_10_173719_add_given_answer_to_quiz_attempt_questions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_01_17_101000_create_scorm_packages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_01_17_101010_create_scorm_package_versions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_01_17_101020_add_scorm_package_version_to_module_lectures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_01_24_000000_create_group_module_lectures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_01_31_093451_create_lecture_objectives_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_01_31_095208_create_competencies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_01_31_095230_create_lecture_objective_competency_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_01_31_095301_create_badges_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_01_31_095322_create_badge_competency_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_02_01_111802_add_position_to_group_module_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_02_07_101855_add_password_changed_at_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_02_14_085759_add_duration_to_module_lectures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_02_22_160000_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_02_22_160100_create_pilotage_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_02_23_000000_add_cloze_payload_to_quiz_questions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_02_27_120000_create_word_clouds_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_02_27_120100_create_word_cloud_entries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_02_28_120000_create_module_completion_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_03_07_120000_add_slides_fields_to_module_lectures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_03_07_150000_add_slides_source_path_to_module_lectures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_03_08_120000_add_temporary_password_to_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_03_13_120000_create_lesson_resources_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_03_14_000000_create_live_quiz_session_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_03_14_010000_add_live_quiz_entry_enabled_to_module_lectures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2026_03_14_020000_create_group_whiteboard_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2026_03_20_000000_add_estimated_question_seconds_to_modules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_03_22_120000_add_observateur_role_support',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_03_27_000001_add_module_id_to_lesson_resources_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_03_27_000002_add_group_id_to_word_clouds_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2026_03_27_000003_add_group_id_to_live_quiz_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_03_30_120000_add_is_active_to_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2026_03_30_130000_add_start_date_to_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2026_03_30_131000_add_end_date_to_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2026_04_09_120000_create_trainer_path_activity_attempts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2026_04_21_100000_create_formateur_parcours_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2026_04_21_100001_create_formateur_parcours_modules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2026_04_21_100002_replace_formateur_parcours_modules_with_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2026_04_21_100003_add_poll_columns_to_formateur_parcours_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2026_04_21_100004_add_formateur_parcours_id_to_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2026_04_21_191700_add_wc_duration_to_formateur_parcours_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2026_04_21_192247_replace_wc_question_with_wc_questions_on_formateur_parcours_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2026_04_21_194532_replace_poll_fields_on_formateur_parcours_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2026_04_21_215512_add_parcours_item_to_word_cloud_entries',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2026_04_22_084722_add_questions_to_word_clouds',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2026_04_22_170432_create_random_wheel_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2026_04_23_120000_add_active_entry_ids_to_random_wheel_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2026_04_23_140000_create_question_wall_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2026_04_24_090000_create_poll_sessions_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2026_04_27_074532_add_excalidraw_data_to_group_whiteboards',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2026_04_27_075435_create_group_timers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2026_04_28_100000_create_scale_sessions_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2026_05_07_123018_add_adhesion_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2026_05_19_222419_create_formateur_messages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2026_06_02_120000_create_trainer_module_questionnaire_submissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2026_06_12_110207_add_attempt_number_to_trainer_path_activity_attempts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2026_06_12_110207_add_is_sandbox_to_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2026_07_01_000001_add_html_content_to_module_lectures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2026_07_01_000002_add_is_trainer_authored_to_modules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2026_07_01_000003_add_content_blocks_to_module_lectures_table',1);
