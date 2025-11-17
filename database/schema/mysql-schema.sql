/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `browser` varchar(255) DEFAULT NULL,
  `device` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `platform` varchar(255) NOT NULL,
  `activity_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activities_user_id_foreign` (`user_id`),
  CONSTRAINT `activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `answer_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `answer_options` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) unsigned NOT NULL,
  `option_text_ar` varchar(255) NOT NULL,
  `option_text_en` varchar(255) NOT NULL,
  `option_info_ar` varchar(255) NOT NULL,
  `option_info_en` varchar(255) NOT NULL,
  `score` double(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `answer_options_question_id_foreign` (`question_id`),
  CONSTRAINT `answer_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessment_aspects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_aspects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assessment_aspects_assessment_id_foreign` (`assessment_id`),
  CONSTRAINT `assessment_aspects_assessment_id_foreign` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assessments_user_id_foreign` (`user_id`),
  CONSTRAINT `assessments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendance_training_offline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_training_offline` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `training_session_id` bigint(20) unsigned NOT NULL,
  `step_id` bigint(20) unsigned NOT NULL,
  `cycle_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `attendance` tinyint(1) NOT NULL DEFAULT 0,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_training_offline_user_id_foreign` (`user_id`),
  KEY `attendance_training_offline_training_session_id_foreign` (`training_session_id`),
  KEY `attendance_training_offline_step_id_foreign` (`step_id`),
  KEY `attendance_training_offline_cycle_id_foreign` (`cycle_id`),
  KEY `attendance_training_offline_company_id_foreign` (`company_id`),
  CONSTRAINT `attendance_training_offline_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_training_offline_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_training_offline_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_training_offline_training_session_id_foreign` FOREIGN KEY (`training_session_id`) REFERENCES `training_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_training_offline_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(250) DEFAULT NULL,
  `subject_id` bigint(20) DEFAULT NULL,
  `subject_type` varchar(250) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `properties` text DEFAULT NULL,
  `host` varchar(46) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `builder_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `builder_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `builder_form_id` bigint(20) unsigned NOT NULL,
  `field_id` bigint(20) unsigned NOT NULL,
  `options` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `builder_fields_builder_form_id_foreign` (`builder_form_id`),
  KEY `builder_fields_field_id_foreign` (`field_id`),
  CONSTRAINT `builder_fields_builder_form_id_foreign` FOREIGN KEY (`builder_form_id`) REFERENCES `builder_forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `builder_fields_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `builder_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `builder_forms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `form_name` varchar(255) DEFAULT NULL,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `builder_forms_organization_id_foreign` (`organization_id`),
  CONSTRAINT `builder_forms_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calendars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendars` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `calendarId` int(11) DEFAULT 1,
  `title` varchar(250) NOT NULL,
  `body` varchar(250) DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `allDay` tinyint(1) DEFAULT NULL,
  `category` varchar(250) DEFAULT NULL,
  `location` varchar(250) DEFAULT NULL,
  `isReadOnly` tinyint(1) DEFAULT NULL,
  `isPrivate` tinyint(1) DEFAULT NULL,
  `color` varchar(250) DEFAULT '#3a87ad',
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calendars_user_id_foreign` (`user_id`),
  CONSTRAINT `calendars_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calender_syncs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `calender_syncs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `calenderable_type` varchar(255) NOT NULL,
  `calenderable_id` bigint(20) unsigned NOT NULL,
  `provider` text NOT NULL,
  `event_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calender_syncs_user_id_foreign` (`user_id`),
  KEY `calender_syncs_calenderable_type_calenderable_id_index` (`calenderable_type`,`calenderable_id`),
  CONSTRAINT `calender_syncs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_name_ar` varchar(255) DEFAULT NULL,
  `category_name_en` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category_description` text DEFAULT NULL,
  `category_weight` decimal(8,2) DEFAULT NULL,
  `category_type` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories_training`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories_training` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `categories_id` bigint(20) unsigned NOT NULL,
  `training_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chat_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` bigint(20) unsigned NOT NULL,
  `state_id` bigint(20) unsigned NOT NULL,
  `name_ar` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cities_country_id_foreign` (`country_id`),
  KEY `cities_state_id_foreign` (`state_id`),
  CONSTRAINT `cities_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cities_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` bigint(20) unsigned DEFAULT NULL,
  `state_id` bigint(20) unsigned DEFAULT NULL,
  `city_id` bigint(20) unsigned DEFAULT NULL,
  `name_en` varchar(255) NOT NULL,
  `name_ar` varchar(255) NOT NULL,
  `sector` varchar(200) DEFAULT NULL,
  `business_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`business_categories`)),
  `business_phase` varchar(255) DEFAULT NULL,
  `address_en` varchar(255) NOT NULL,
  `address_ar` varchar(255) NOT NULL,
  `years_of_operations` varchar(50) DEFAULT NULL,
  `product_or_service` varchar(50) DEFAULT NULL,
  `occupation` varchar(75) DEFAULT NULL,
  `annual_revenue` varchar(25) DEFAULT NULL,
  `info_company` text DEFAULT NULL,
  `est_date` date DEFAULT NULL,
  `idea` mediumtext DEFAULT NULL,
  `problem` mediumtext DEFAULT NULL,
  `solution` mediumtext DEFAULT NULL,
  `team_num` varchar(255) DEFAULT NULL,
  `working_team_summary` text DEFAULT NULL,
  `stage` varchar(255) DEFAULT NULL,
  `funding_stage` varchar(255) DEFAULT NULL,
  `business_type` varchar(255) DEFAULT NULL,
  `institutions` text DEFAULT NULL,
  `development_stage` varchar(255) DEFAULT NULL,
  `revenue_generating` varchar(255) DEFAULT NULL,
  `currently_fundraising` varchar(255) DEFAULT NULL,
  `desired_funding_amount` varchar(255) DEFAULT NULL,
  `raised_fund` varchar(255) DEFAULT NULL,
  `equity_offered` varchar(25) DEFAULT NULL,
  `Currency` varchar(255) DEFAULT NULL,
  `Pre_money_Valuation` varchar(255) DEFAULT NULL,
  `Funding_Target` double(115,2) NOT NULL DEFAULT 0.00,
  `targeted_market` varchar(255) DEFAULT NULL,
  `Industries` varchar(255) DEFAULT NULL,
  `investors` varchar(255) DEFAULT NULL,
  `founder` varchar(255) DEFAULT NULL,
  `employees` varchar(255) DEFAULT NULL,
  `collaborators` text DEFAULT NULL,
  `country_code` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `country_code_w_a` varchar(255) DEFAULT NULL,
  `whatsapp` int(11) DEFAULT NULL,
  `fax` varchar(250) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `product_photo` text DEFAULT NULL,
  `product_video` varchar(250) DEFAULT NULL,
  `business_model` varchar(255) DEFAULT NULL,
  `other_business_model` varchar(255) DEFAULT NULL,
  `business_model_description` varchar(255) DEFAULT NULL,
  `business_plan` varchar(255) DEFAULT NULL,
  `business_plan_description` varchar(255) DEFAULT NULL,
  `pitch_deck` varchar(255) DEFAULT NULL,
  `pitch_deck_video` varchar(255) DEFAULT NULL,
  `pitch_deck_description` varchar(255) DEFAULT NULL,
  `pitch_deck_summary` varchar(255) DEFAULT NULL,
  `documents` longtext DEFAULT NULL,
  `financial_plan` varchar(255) DEFAULT NULL,
  `financial_plan_description` varchar(255) DEFAULT NULL,
  `website` varchar(250) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `google_map` text DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `tiktok` varchar(255) DEFAULT NULL,
  `logo` varchar(250) DEFAULT NULL,
  `cycle` bigint(20) unsigned DEFAULT NULL,
  `step` bigint(20) unsigned DEFAULT NULL,
  `approved` int(11) DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `hear_about_us` text DEFAULT NULL,
  `about_problem` longtext DEFAULT NULL,
  `about_market_opportunity` longtext DEFAULT NULL,
  `about_competitors` longtext DEFAULT NULL,
  `about_monetize` longtext DEFAULT NULL,
  `about_challenges` longtext DEFAULT NULL,
  `Sustainable_development` text DEFAULT NULL,
  `CreatedTax` int(11) NOT NULL DEFAULT 0 COMMENT 'هل الشركة مسجلة',
  `Commercial_Register` varchar(255) DEFAULT NULL COMMENT 'رقم السجل التجاري',
  `Tax_Card` varchar(255) DEFAULT NULL COMMENT 'رقم البطاقة الضريبية',
  `Country_Register_id` int(10) unsigned DEFAULT 0 COMMENT 'الدولة التي تم التسجيل فيها',
  `join_stock_company` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `companies_country_id_foreign` (`country_id`),
  KEY `companies_state_id_foreign` (`state_id`),
  KEY `companies_city_id_foreign` (`city_id`),
  KEY `companies_cycle_foreign` (`cycle`),
  KEY `companies_step_foreign` (`step`),
  KEY `companies_approved_by_foreign` (`approved_by`),
  CONSTRAINT `companies_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `companies_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `companies_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `companies_cycle_foreign` FOREIGN KEY (`cycle`) REFERENCES `cycles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `companies_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE SET NULL,
  CONSTRAINT `companies_step_foreign` FOREIGN KEY (`step`) REFERENCES `steps` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `company_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_relations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `industry_id` bigint(20) unsigned DEFAULT NULL,
  `sector_id` bigint(20) unsigned DEFAULT NULL,
  `technology_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_relations_company_id_foreign` (`company_id`),
  KEY `company_relations_user_id_foreign` (`user_id`),
  KEY `company_relations_industry_id_foreign` (`industry_id`),
  KEY `company_relations_sector_id_foreign` (`sector_id`),
  KEY `company_relations_technology_id_foreign` (`technology_id`),
  CONSTRAINT `company_relations_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `company_relations_industry_id_foreign` FOREIGN KEY (`industry_id`) REFERENCES `static_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `company_relations_sector_id_foreign` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `company_relations_technology_id_foreign` FOREIGN KEY (`technology_id`) REFERENCES `technologies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `company_relations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `countries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `iso3` varchar(255) DEFAULT NULL,
  `phone_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `currencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `currency_code` varchar(3) NOT NULL,
  `currency_name` varchar(100) NOT NULL,
  `currency_symbol` varchar(10) DEFAULT NULL,
  `exchange_rate_to_usd` decimal(10,6) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `flag` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cycle_custom_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cycle_custom_pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cycle_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `visibility` varchar(15) DEFAULT NULL,
  `row_no` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cycle_custom_pages_cycle_id_foreign` (`cycle_id`),
  CONSTRAINT `cycle_custom_pages_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cycle_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cycle_project` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cycle_id` bigint(20) unsigned NOT NULL,
  `project_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cycle_project_cycle_id_foreign` (`cycle_id`),
  KEY `cycle_project_project_id_foreign` (`project_id`),
  CONSTRAINT `cycle_project_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`),
  CONSTRAINT `cycle_project_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cycles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cycles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `img_url` varchar(255) DEFAULT NULL,
  `incubator_id` int(11) NOT NULL DEFAULT 1,
  `title` varchar(250) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `private` varchar(250) DEFAULT NULL,
  `organization_id` bigint(20) unsigned NOT NULL,
  `winners_announcement` tinyint(1) DEFAULT 0,
  `winners_visibility` enum('public','private') DEFAULT 'private',
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `public_slug` varchar(250) DEFAULT NULL,
  `step_type` varchar(25) DEFAULT 'cycle' COMMENT 'cycle, time',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `allow_non_company` tinyint(1) NOT NULL DEFAULT 0,
  `program_guideline` longtext DEFAULT NULL,
  `program_guideline_file` varchar(255) DEFAULT NULL,
  `financial_guideline` longtext DEFAULT NULL,
  `financial_guideline_file` varchar(255) DEFAULT NULL,
  `legal_guideline` longtext DEFAULT NULL,
  `legal_guideline_file` varchar(255) DEFAULT NULL,
  `delegation_file` varchar(255) DEFAULT NULL,
  `budget_allocation` decimal(15,2) DEFAULT NULL,
  `currency_type` tinytext DEFAULT NULL,
  `auto_approve_mentorship_requests` tinyint(1) NOT NULL DEFAULT 0,
  `type` varchar(255) DEFAULT NULL COMMENT 'Type of the cycle, e.g., Hackathon, General_Program',
  `hours_Rminder` int(11) NOT NULL DEFAULT 1 COMMENT 'hours for reminder',
  `is_active_Rminder` int(11) NOT NULL DEFAULT 0 COMMENT 'Active = 1 , Not Active = 0',
  `cohort` varchar(20) DEFAULT NULL COMMENT 'Cohort of the cycle',
  PRIMARY KEY (`id`),
  KEY `cycles_organization_id_foreign` (`organization_id`),
  CONSTRAINT `cycles_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cycles_mentors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cycles_mentors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cycle_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cycles_mentors_cycle_id_foreign` (`cycle_id`),
  KEY `cycles_mentors_user_id_foreign` (`user_id`),
  CONSTRAINT `cycles_mentors_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cycles_mentors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documentations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `documentations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_ar` varchar(191) NOT NULL,
  `menu_en` varchar(191) NOT NULL,
  `arr` int(11) NOT NULL DEFAULT 0,
  `content_ar` text NOT NULL,
  `content_en` text DEFAULT NULL,
  `role` varchar(10) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documentations_user_id_foreign` (`user_id`),
  CONSTRAINT `documentations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `employment_type` enum('full-time','part-time','contractor','intern') NOT NULL,
  `hiring_date` date NOT NULL,
  `documents` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employees_company_id_foreign` (`company_id`),
  CONSTRAINT `employees_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title_en` varchar(191) NOT NULL,
  `title_ar` varchar(191) NOT NULL,
  `image` varchar(191) DEFAULT NULL,
  `body_ar` text NOT NULL,
  `body_en` text NOT NULL,
  `showtimer` int(11) DEFAULT NULL,
  `timerdate` datetime DEFAULT NULL,
  `googlemap` text DEFAULT NULL,
  `published` varchar(5) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `events_user_id_foreign` (`user_id`),
  CONSTRAINT `events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(250) DEFAULT NULL,
  `employer` varchar(250) NOT NULL,
  `jobtitle` varchar(250) NOT NULL,
  `event_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `events_users_event_id_foreign` (`event_id`),
  CONSTRAINT `events_users_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `expertises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `expertises` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `title_ar` varchar(150) DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expertises_parent_id_foreign` (`parent_id`),
  CONSTRAINT `expertises_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `expertises` (`id`) ON DELETE CASCADE
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
DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title_en` varchar(250) NOT NULL,
  `title_ar` varchar(250) NOT NULL,
  `answer_ar` text NOT NULL,
  `answer_en` text NOT NULL,
  `published` int(11) DEFAULT NULL,
  `user_added` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faqs_user_added_foreign` (`user_added`),
  CONSTRAINT `faqs_user_added_foreign` FOREIGN KEY (`user_added`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `field_type` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `final_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `final_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `field_id` bigint(20) unsigned NOT NULL,
  `judge_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `step_id` bigint(20) unsigned NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `final_data_field_id_foreign` (`field_id`),
  KEY `final_data_judge_id_foreign` (`judge_id`),
  KEY `final_data_company_id_foreign` (`company_id`),
  KEY `final_data_step_id_foreign` (`step_id`),
  CONSTRAINT `final_data_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `final_data_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `form_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `final_data_judge_id_foreign` FOREIGN KEY (`judge_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `final_data_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `finalscreenings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `finalscreenings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `step_id` bigint(20) unsigned NOT NULL,
  `judges` varchar(500) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `zoom_id` varchar(250) DEFAULT NULL,
  `zoom_passcode` varchar(25) DEFAULT NULL,
  `start_url` varchar(1024) DEFAULT NULL,
  `join_url` varchar(1024) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `finalscreenings_company_id_foreign` (`company_id`),
  KEY `finalscreenings_step_id_foreign` (`step_id`),
  CONSTRAINT `finalscreenings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `finalscreenings_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `finance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `finance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `total_hours` double(8,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `month_year` varchar(7) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `finance_user_id_foreign` (`user_id`),
  CONSTRAINT `finance_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `form_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_data` (
  `field_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `form_id` bigint(20) unsigned NOT NULL,
  `step_id` bigint(20) unsigned DEFAULT NULL,
  `question` varchar(250) NOT NULL,
  `answer` text DEFAULT NULL,
  `screening` text DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  KEY `form_data_field_id_foreign` (`field_id`),
  KEY `form_data_company_id_foreign` (`company_id`),
  KEY `form_data_user_id_foreign` (`user_id`),
  KEY `form_data_form_id_foreign` (`form_id`),
  KEY `form_data_step_id_foreign` (`step_id`),
  CONSTRAINT `form_data_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_data_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `form_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_data_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_data_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_data_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `form_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `step_id` bigint(20) unsigned NOT NULL,
  `field_id` bigint(20) unsigned NOT NULL,
  `options` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `builder_forms_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_fields_step_id_foreign` (`step_id`),
  KEY `form_fields_field_id_foreign` (`field_id`),
  KEY `form_fields_builder_forms_id_foreign` (`builder_forms_id`),
  CONSTRAINT `form_fields_builder_forms_id_foreign` FOREIGN KEY (`builder_forms_id`) REFERENCES `builder_forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_fields_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_fields_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `funding_rounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `funding_rounds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `funding_stage` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `post_money_valuation` int(11) NOT NULL,
  `date_raised` date NOT NULL,
  `investment_vehicle` varchar(255) NOT NULL,
  `amount_raised` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `funding_rounds_company_id_foreign` (`company_id`),
  CONSTRAINT `funding_rounds_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inclusives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inclusives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `img_url` varchar(255) DEFAULT NULL,
  `en_title` varchar(255) NOT NULL,
  `ar_title` varchar(255) NOT NULL,
  `user_added` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inclusives_user_added_foreign` (`user_added`),
  CONSTRAINT `inclusives_user_added_foreign` FOREIGN KEY (`user_added`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `incubators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `incubators` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `establishmentdate` date DEFAULT NULL,
  `portfolio` varchar(250) DEFAULT NULL,
  `partners` varchar(500) DEFAULT NULL,
  `offering` varchar(500) DEFAULT NULL,
  `logo` varchar(250) DEFAULT NULL,
  `facebook` varchar(250) DEFAULT NULL,
  `twitter` varchar(250) DEFAULT NULL,
  `linkeded` varchar(250) DEFAULT NULL,
  `youtube` varchar(250) DEFAULT NULL,
  `website` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `industries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `industries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `industry_sector`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `industry_sector` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `industry_id` bigint(20) unsigned NOT NULL,
  `sector_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `industry_sector_industry_id_foreign` (`industry_id`),
  KEY `industry_sector_sector_id_foreign` (`sector_id`),
  CONSTRAINT `industry_sector_industry_id_foreign` FOREIGN KEY (`industry_id`) REFERENCES `static_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `industry_sector_sector_id_foreign` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invitations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invitations_email_unique` (`email`),
  KEY `invitations_organization_id_foreign` (`organization_id`),
  KEY `invitations_user_id_foreign` (`user_id`),
  CONSTRAINT `invitations_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invitations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
  `reserved_at` varchar(75) DEFAULT NULL,
  `available_at` varchar(75) DEFAULT NULL,
  `created_at` varchar(75) DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `labels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `labels_user_id_foreign` (`user_id`),
  KEY `labels_company_id_foreign` (`company_id`),
  CONSTRAINT `labels_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `labels_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `licenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `activated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `licenses_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentor_expertise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentor_expertise` (
  `expertise_id` bigint(20) unsigned NOT NULL,
  `mentor_id` bigint(20) unsigned NOT NULL,
  KEY `mentor_expertise_expertise_id_foreign` (`expertise_id`),
  KEY `mentor_expertise_mentor_id_foreign` (`mentor_id`),
  CONSTRAINT `mentor_expertise_expertise_id_foreign` FOREIGN KEY (`expertise_id`) REFERENCES `expertises` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentor_expertise_mentor_id_foreign` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentorship_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentorship_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentor_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `approved` varchar(3) DEFAULT NULL,
  `admin_comment` varchar(255) DEFAULT NULL,
  `admin_id` bigint(20) unsigned DEFAULT NULL,
  `fileurl` varchar(250) DEFAULT NULL,
  `start_url` varchar(1024) DEFAULT NULL,
  `join_url` varchar(1024) DEFAULT NULL,
  `zoom_id` varchar(191) DEFAULT NULL,
  `zoom_passcode` varchar(25) DEFAULT NULL,
  `zoom_date` datetime DEFAULT NULL,
  `period` int(11) NOT NULL DEFAULT 0,
  `rating` int(11) DEFAULT NULL,
  `rate_comment` varchar(250) DEFAULT NULL,
  `done` int(11) DEFAULT NULL,
  `cycle_id` bigint(20) unsigned NOT NULL,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `override_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentorship_requests_mentor_id_foreign` (`mentor_id`),
  KEY `mentorship_requests_user_id_foreign` (`user_id`),
  KEY `mentorship_requests_admin_id_foreign` (`admin_id`),
  KEY `mentorship_requests_cycle_id_foreign` (`cycle_id`),
  KEY `mentorship_requests_organization_id_foreign` (`organization_id`),
  CONSTRAINT `mentorship_requests_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_requests_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_requests_mentor_id_foreign` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_requests_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentorship_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentorship_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cycle_id` bigint(20) unsigned DEFAULT NULL,
  `value` double(8,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(255) NOT NULL DEFAULT '0',
  `is_active` int(11) NOT NULL DEFAULT 0,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentorship_schedules_cycle_id_foreign` (`cycle_id`),
  CONSTRAINT `mentorship_schedules_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentorship_task_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentorship_task_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentorship_task_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `comment` mediumtext NOT NULL,
  `attached` longtext DEFAULT NULL,
  `edited` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentorship_task_comments_mentorship_task_id_foreign` (`mentorship_task_id`),
  KEY `mentorship_task_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `mentorship_task_comments_mentorship_task_id_foreign` FOREIGN KEY (`mentorship_task_id`) REFERENCES `mentorship_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_task_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentorship_task_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentorship_task_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentorship_request_id` bigint(20) unsigned DEFAULT NULL,
  `mentorship_task_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `file_name` text DEFAULT NULL,
  `file` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `mentorship_task_files_mentorship_request_id_foreign` (`mentorship_request_id`),
  KEY `mentorship_task_files_mentorship_task_id_foreign` (`mentorship_task_id`),
  KEY `mentorship_task_files_user_id_foreign` (`user_id`),
  CONSTRAINT `mentorship_task_files_mentorship_request_id_foreign` FOREIGN KEY (`mentorship_request_id`) REFERENCES `mentorship_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mentorship_task_files_mentorship_task_id_foreign` FOREIGN KEY (`mentorship_task_id`) REFERENCES `mentorship_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mentorship_task_files_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentorship_task_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentorship_task_labels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `label_id` bigint(20) unsigned NOT NULL,
  `mentorship_tasks_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `label_taks_label_id_foreign` (`label_id`),
  KEY `label_taks_mentorship_tasks_id_foreign` (`mentorship_tasks_id`),
  CONSTRAINT `label_taks_label_id_foreign` FOREIGN KEY (`label_id`) REFERENCES `labels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `label_taks_mentorship_tasks_id_foreign` FOREIGN KEY (`mentorship_tasks_id`) REFERENCES `mentorship_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentorship_task_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentorship_task_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mentorship_task_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` enum('create','update','delete') DEFAULT NULL,
  `column` varchar(255) DEFAULT NULL,
  `was` text DEFAULT NULL,
  `new` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `mentorship_task_logs_mentorship_task_id_foreign` (`mentorship_task_id`),
  KEY `mentorship_task_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `mentorship_task_logs_mentorship_task_id_foreign` FOREIGN KEY (`mentorship_task_id`) REFERENCES `mentorship_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mentorship_task_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentorship_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentorship_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `mentorship_request_id` bigint(20) unsigned DEFAULT NULL,
  `mentor_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `done_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `row_no` int(11) DEFAULT 0,
  `priority` enum('low','medium','high') DEFAULT NULL,
  `label_id` varchar(255) DEFAULT NULL,
  `attached` varchar(255) DEFAULT NULL,
  `reply` mediumtext DEFAULT NULL,
  `reported_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentorship_tasks_parent_id_foreign` (`parent_id`),
  KEY `mentorship_tasks_mentorship_request_id_foreign` (`mentorship_request_id`),
  KEY `mentorship_tasks_mentor_id_foreign` (`mentor_id`),
  KEY `mentorship_tasks_company_id_foreign` (`company_id`),
  KEY `mentorship_tasks_user_id_foreign` (`user_id`),
  KEY `mentorship_tasks_reported_by_foreign` (`reported_by`),
  CONSTRAINT `mentorship_tasks_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_tasks_mentor_id_foreign` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_tasks_mentorship_request_id_foreign` FOREIGN KEY (`mentorship_request_id`) REFERENCES `mentorship_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_tasks_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `mentorship_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_tasks_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mentorship_tasks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_type` int(11) NOT NULL,
  `parent_id` bigint(20) unsigned NOT NULL,
  `title` varchar(191) NOT NULL,
  `ar_title` varchar(191) NOT NULL,
  `url` varchar(191) NOT NULL,
  `target` varchar(250) NOT NULL DEFAULT '_self',
  `order` int(11) NOT NULL,
  `user_added` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menus_parent_id_foreign` (`parent_id`),
  KEY `menus_user_added_foreign` (`user_added`),
  CONSTRAINT `menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menus_user_added_foreign` FOREIGN KEY (`user_added`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `message_drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_drafts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender` bigint(20) unsigned NOT NULL,
  `receiver` bigint(20) unsigned NOT NULL,
  `cycle_id` bigint(20) unsigned DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `drafts` int(11) NOT NULL DEFAULT 0,
  `type` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_drafts_sender_foreign` (`sender`),
  KEY `message_drafts_receiver_foreign` (`receiver`),
  KEY `message_drafts_cycle_id_foreign` (`cycle_id`),
  CONSTRAINT `message_drafts_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `message_drafts_receiver_foreign` FOREIGN KEY (`receiver`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_drafts_sender_foreign` FOREIGN KEY (`sender`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `message_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_replies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `messenger_id` bigint(20) unsigned NOT NULL,
  `reply_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_replies_messenger_id_foreign` (`messenger_id`),
  KEY `message_replies_reply_id_foreign` (`reply_id`),
  CONSTRAINT `message_replies_messenger_id_foreign` FOREIGN KEY (`messenger_id`) REFERENCES `messenger` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_replies_reply_id_foreign` FOREIGN KEY (`reply_id`) REFERENCES `messenger` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) DEFAULT NULL,
  `internal_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(250) DEFAULT NULL,
  `subject` varchar(250) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `read` tinyint(1) DEFAULT 0,
  `userip` varchar(250) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `replyBy` int(11) DEFAULT NULL,
  `reSubject` varchar(250) DEFAULT NULL,
  `reMessage` varchar(1024) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_internal_id_foreign` (`internal_id`),
  KEY `messages_user_id_foreign` (`user_id`),
  CONSTRAINT `messages_internal_id_foreign` FOREIGN KEY (`internal_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `messenger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reply_id` bigint(20) unsigned DEFAULT NULL,
  `sender` bigint(20) unsigned NOT NULL,
  `receiver` bigint(20) unsigned NOT NULL,
  `messgeId` bigint(20) unsigned DEFAULT NULL,
  `subject` varchar(191) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `read` tinyint(1) DEFAULT NULL,
  `reported` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `starred` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `messenger_sender_foreign` (`sender`),
  KEY `messenger_receiver_foreign` (`receiver`),
  KEY `messenger_messgeid_foreign` (`messgeId`),
  KEY `messenger_reply_id_foreign` (`reply_id`),
  CONSTRAINT `messenger_messgeid_foreign` FOREIGN KEY (`messgeId`) REFERENCES `messenger` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messenger_receiver_foreign` FOREIGN KEY (`receiver`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messenger_reply_id_foreign` FOREIGN KEY (`reply_id`) REFERENCES `messenger` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messenger_sender_foreign` FOREIGN KEY (`sender`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
DROP TABLE IF EXISTS `milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `milestones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` tinytext DEFAULT NULL,
  `description` tinytext DEFAULT NULL,
  `completion_percentage` int(11) NOT NULL DEFAULT 0,
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `Company_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `milestones_company_id_foreign` (`Company_id`),
  CONSTRAINT `milestones_company_id_foreign` FOREIGN KEY (`Company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned DEFAULT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  KEY `model_has_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `model_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `model_type` varchar(255) NOT NULL,
  KEY `model_has_roles_role_id_foreign` (`role_id`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notification_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `channel` enum('email','whatsapp','pusher') NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `sender` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `version` int(11) NOT NULL DEFAULT 1,
  `parent_template_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_templates_created_by_foreign` (`created_by`),
  KEY `notification_templates_channel_is_active_index` (`channel`,`is_active`),
  KEY `notification_templates_parent_template_id_index` (`parent_template_id`),
  CONSTRAINT `notification_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notification_templates_parent_template_id_foreign` FOREIGN KEY (`parent_template_id`) REFERENCES `notification_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notification_triggers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_triggers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event` varchar(255) NOT NULL,
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conditions`)),
  `template_id` bigint(20) unsigned NOT NULL,
  `recipient_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`recipient_rules`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `priority` int(11) NOT NULL DEFAULT 0,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_triggers_name_unique` (`name`),
  KEY `notification_triggers_template_id_foreign` (`template_id`),
  KEY `notification_triggers_created_by_foreign` (`created_by`),
  KEY `notification_triggers_event_is_active_index` (`event`,`is_active`),
  KEY `notification_triggers_is_active_priority_index` (`is_active`,`priority`),
  CONSTRAINT `notification_triggers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notification_triggers_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `notification_templates` (`id`) ON DELETE CASCADE
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
DROP TABLE IF EXISTS `organization_question_bank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `organization_question_bank` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organization_question_bank_organization_id_foreign` (`organization_id`),
  KEY `organization_question_bank_question_id_foreign` (`question_id`),
  CONSTRAINT `organization_question_bank_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organization_question_bank_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions_bank` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organization_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `organization_service` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `service_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organization_service_organization_id_foreign` (`organization_id`),
  KEY `organization_service_service_id_foreign` (`service_id`),
  CONSTRAINT `organization_service_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organization_service_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `short_code` varchar(10) DEFAULT NULL,
  `name_ar` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `api_token` varchar(255) DEFAULT NULL,
  `color` varchar(255) NOT NULL DEFAULT '',
  `img_url` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organizations_short_code_unique` (`short_code`),
  KEY `organizations_user_id_foreign` (`user_id`),
  CONSTRAINT `organizations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`gltest`@`%`*/ /*!50003 TRIGGER before_insert_organizations
				BEFORE INSERT ON organizations
				FOR EACH ROW
				BEGIN
					DECLARE slug VARCHAR(10);
					DECLARE tries INT DEFAULT 0;

					label_retry: LOOP
						SET slug = LOWER(CONCAT(
							SUBSTRING('abcdefghijklmnopqrstuvwxyz0123456789', FLOOR(1 + RAND() * 36), 1),
							SUBSTRING('abcdefghijklmnopqrstuvwxyz0123456789', FLOOR(1 + RAND() * 36), 1),
							SUBSTRING('abcdefghijklmnopqrstuvwxyz0123456789', FLOOR(1 + RAND() * 36), 1),
							SUBSTRING('abcdefghijklmnopqrstuvwxyz0123456789', FLOOR(1 + RAND() * 36), 1),
							SUBSTRING('abcdefghijklmnopqrstuvwxyz0123456789', FLOOR(1 + RAND() * 36), 1)
						));

						IF NOT EXISTS (
							SELECT 1 FROM organizations WHERE short_code = slug COLLATE utf8mb4_unicode_ci
						) THEN
							SET NEW.short_code = slug;
							LEAVE label_retry;
						END IF;

						SET tries = tries + 1;
						IF tries > 10 THEN
							SIGNAL SQLSTATE '45000'
							SET MESSAGE_TEXT = 'Unable to generate unique short_code after 10 attempts';
						END IF;
					END LOOP;
				END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `organizations_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizations_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` int(10) unsigned DEFAULT NULL,
  `smtp_encryption` varchar(255) DEFAULT NULL,
  `smtp_username` varchar(255) DEFAULT NULL,
  `smtp_password` varchar(255) DEFAULT NULL,
  `smtp_from_address` varchar(255) DEFAULT NULL,
  `smtp_from_name` varchar(255) DEFAULT NULL,
  `accept_email` tinyint(1) NOT NULL DEFAULT 1,
  `accept_whatsapp` tinyint(1) NOT NULL DEFAULT 0,
  `accept_pusher` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organizations_settings_organization_id_foreign` (`organization_id`),
  CONSTRAINT `organizations_settings_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `otps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `phone` varchar(255) NOT NULL,
  `otp_code` varchar(255) NOT NULL,
  `bad` int(11) NOT NULL DEFAULT 0,
  `otp_sent` tinyint(1) NOT NULL DEFAULT 0,
  `wrong_attempts` int(11) NOT NULL DEFAULT 0,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `otps_user_id_foreign` (`user_id`),
  CONSTRAINT `otps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `page_durations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `page_durations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `cycle_id` bigint(20) unsigned DEFAULT NULL,
  `step_id` bigint(20) unsigned DEFAULT NULL,
  `training_sessions_id` bigint(20) unsigned DEFAULT NULL,
  `page_url` varchar(255) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `page_durations_company_id_foreign` (`company_id`),
  KEY `page_durations_cycle_id_foreign` (`cycle_id`),
  KEY `page_durations_step_id_foreign` (`step_id`),
  KEY `page_durations_training_sessions_id_foreign` (`training_sessions_id`),
  KEY `page_durations_user_id_foreign` (`user_id`),
  CONSTRAINT `page_durations_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `page_durations_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `page_durations_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `page_durations_training_sessions_id_foreign` FOREIGN KEY (`training_sessions_id`) REFERENCES `training_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `page_durations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(191) NOT NULL,
  `image` varchar(191) DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `ar_title` varchar(191) NOT NULL,
  `body` text NOT NULL,
  `ar_body` text NOT NULL,
  `published` varchar(5) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_url_unique` (`url`),
  KEY `pages_user_id_foreign` (`user_id`),
  CONSTRAINT `pages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `partners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `partners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ar_title` varchar(250) NOT NULL,
  `en_title` varchar(255) NOT NULL,
  `img_url` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `user_added` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partners_user_added_foreign` (`user_added`),
  CONSTRAINT `partners_user_added_foreign` FOREIGN KEY (`user_added`) REFERENCES `users` (`id`) ON DELETE SET NULL
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
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permission_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(75) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_groups_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `guard_name` varchar(255) NOT NULL DEFAULT 'web',
  `is_core` tinyint(1) DEFAULT 0 COMMENT 'core permission will be allowed to any role',
  `permission_group_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permissions_permission_group_id_foreign` (`permission_group_id`),
  CONSTRAINT `permissions_permission_group_id_foreign` FOREIGN KEY (`permission_group_id`) REFERENCES `permission_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(191) DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `ar_title` varchar(191) NOT NULL,
  `body` text NOT NULL,
  `ar_body` text NOT NULL,
  `published` varchar(2) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `posts_user_id_foreign` (`user_id`),
  CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `press`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `press` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ar_title` varchar(250) NOT NULL,
  `en_title` varchar(255) NOT NULL,
  `img_url` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `order` int(11) DEFAULT NULL,
  `user_added` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `press_user_added_foreign` (`user_added`),
  CONSTRAINT `press_user_added_foreign` FOREIGN KEY (`user_added`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_likes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_likes_team_id_foreign` (`team_id`),
  KEY `project_likes_user_id_foreign` (`user_id`),
  CONSTRAINT `project_likes_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `team_formations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_team_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_team_member` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `team_member_id` bigint(20) unsigned DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_team_member_project_id_foreign` (`project_id`),
  KEY `project_team_member_team_member_id_foreign` (`team_member_id`),
  CONSTRAINT `project_team_member_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_team_member_team_member_id_foreign` FOREIGN KEY (`team_member_id`) REFERENCES `team_members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `question_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `question_categories_organization_id_foreign` (`organization_id`),
  CONSTRAINT `question_categories_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `subcategory_id` bigint(20) unsigned DEFAULT NULL,
  `text_ar` text DEFAULT NULL COMMENT 'السؤال عربى',
  `text_en` text DEFAULT NULL COMMENT 'السؤال انجليزي',
  `info_ar` text DEFAULT NULL COMMENT 'معلومات عن السؤال باللغة العربية ',
  `info_en` text DEFAULT NULL COMMENT 'معلومات عن السؤال باللغة الانجليزية ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `questions_category_id_foreign` (`category_id`),
  KEY `questions_subcategory_id_foreign` (`subcategory_id`),
  CONSTRAINT `questions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questions_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `sub_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `questions_bank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `questions_bank` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `group` enum('quiz','survey','assessment') NOT NULL,
  `question` varchar(750) DEFAULT NULL,
  `type` enum('mcq','tfq') DEFAULT 'mcq',
  `survey_type` enum('stars','radio','answers') DEFAULT NULL,
  `correct` tinyint(1) DEFAULT 1,
  `answers` longtext DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `explanation_QA_ar` longtext DEFAULT NULL COMMENT 'شرح السؤال باللغة العربية',
  `explanation_QA_en` longtext DEFAULT NULL COMMENT 'شرح السؤال باللغة الانجليزية',
  `explanation_AA_ar` longtext DEFAULT NULL COMMENT 'شرح الجواب باللغة العربية',
  `explanation_AA_en` longtext DEFAULT NULL COMMENT 'شرح الجواب باللغة الانجليزية',
  `difficulty` enum('easy','medium','hard') DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `ascategory_id` bigint(20) unsigned DEFAULT NULL,
  `subcategory_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `questions_bank_organization_id_foreign` (`organization_id`),
  KEY `questions_bank_category_id_foreign` (`category_id`),
  CONSTRAINT `questions_bank_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `question_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `questions_bank_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) unsigned DEFAULT NULL,
  `question_id` bigint(20) unsigned DEFAULT NULL,
  `row_no` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `quiz_questions_quiz_id_foreign` (`quiz_id`),
  KEY `quiz_questions_question_id_foreign` (`question_id`),
  CONSTRAINT `quiz_questions_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions_bank` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `quiz_questions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `result_id` bigint(20) unsigned DEFAULT NULL,
  `quiz_id` bigint(20) unsigned DEFAULT NULL,
  `question_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `response` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `quiz_responses_result_id_foreign` (`result_id`),
  KEY `quiz_responses_quiz_id_foreign` (`quiz_id`),
  KEY `quiz_responses_question_id_foreign` (`question_id`),
  KEY `quiz_responses_user_id_foreign` (`user_id`),
  CONSTRAINT `quiz_responses_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions_bank` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `quiz_responses_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `quiz_responses_result_id_foreign` FOREIGN KEY (`result_id`) REFERENCES `quiz_results` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `quiz_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `score` double DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `quiz_results_quiz_id_foreign` (`quiz_id`),
  KEY `quiz_results_user_id_foreign` (`user_id`),
  CONSTRAINT `quiz_results_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `quiz_results_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quizzes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quizzes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `training_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `time_limit` double DEFAULT NULL,
  `attempts` int(11) DEFAULT NULL,
  `retry_after` varchar(25) DEFAULT NULL,
  `passing_criteria` double DEFAULT NULL,
  `shuffle` tinyint(1) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `quizzes_organization_id_foreign` (`organization_id`),
  KEY `quizzes_training_id_foreign` (`training_id`),
  CONSTRAINT `quizzes_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `quizzes_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reminder_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reminder_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `cycle_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL COMMENT 'user,mentor',
  `channel` varchar(255) DEFAULT NULL COMMENT ' [email,in-app,whatsapp]',
  `status` varchar(255) DEFAULT NULL COMMENT '[sent,failed,skipped]',
  `delivery_id` varchar(255) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reminder_logs_user_id_foreign` (`user_id`),
  KEY `reminder_logs_cycle_id_foreign` (`cycle_id`),
  CONSTRAINT `reminder_logs_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reminder_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned DEFAULT NULL,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  KEY `role_has_permissions_permission_id_foreign` (`permission_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `default_for` varchar(25) DEFAULT NULL,
  `guard_name` varchar(250) NOT NULL DEFAULT 'web',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `room_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_bookings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `fromDate` datetime DEFAULT NULL,
  `toDate` datetime DEFAULT NULL,
  `approved` varchar(10) NOT NULL,
  `user_added` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_bookings_room_id_foreign` (`room_id`),
  KEY `room_bookings_user_id_foreign` (`user_id`),
  KEY `room_bookings_user_added_foreign` (`user_added`),
  CONSTRAINT `room_bookings_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_bookings_user_added_foreign` FOREIGN KEY (`user_added`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `room_bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_en` varchar(255) NOT NULL,
  `name_ar` varchar(255) NOT NULL,
  `type_trans` varchar(255) NOT NULL,
  `price` double NOT NULL,
  `discount` double NOT NULL,
  `desc_en` text NOT NULL,
  `desc_ar` text NOT NULL,
  `published` varchar(255) DEFAULT NULL,
  `video_link` varchar(255) NOT NULL,
  `user_added` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rooms_user_added_foreign` (`user_added`),
  CONSTRAINT `rooms_user_added_foreign` FOREIGN KEY (`user_added`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rooms_imgs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms_imgs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint(20) unsigned NOT NULL,
  `img_url` varchar(255) NOT NULL,
  `user_added` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rooms_imgs_room_id_foreign` (`room_id`),
  KEY `rooms_imgs_user_added_foreign` (`user_added`),
  CONSTRAINT `rooms_imgs_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rooms_imgs_user_added_foreign` FOREIGN KEY (`user_added`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rooms_inclusives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms_inclusives` (
  `room_id` bigint(20) unsigned DEFAULT NULL,
  `inclusive_id` bigint(20) unsigned DEFAULT NULL,
  `user_added` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  KEY `rooms_inclusives_room_id_foreign` (`room_id`),
  KEY `rooms_inclusives_inclusive_id_foreign` (`inclusive_id`),
  KEY `rooms_inclusives_user_added_foreign` (`user_added`),
  CONSTRAINT `rooms_inclusives_inclusive_id_foreign` FOREIGN KEY (`inclusive_id`) REFERENCES `inclusives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rooms_inclusives_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rooms_inclusives_user_added_foreign` FOREIGN KEY (`user_added`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(20) NOT NULL,
  `days` varchar(250) DEFAULT NULL,
  `dfrom` date DEFAULT NULL,
  `dto` date DEFAULT NULL,
  `tfrom` time DEFAULT NULL,
  `tto` time DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `active` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedules_user_id_foreign` (`user_id`),
  CONSTRAINT `schedules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `screening_evaluation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `screening_evaluation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `step_id` bigint(20) unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = Draft, 1 = Published',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `screening_evaluation_step_id_foreign` (`step_id`),
  CONSTRAINT `screening_evaluation_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `screening_evaluation_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `screening_evaluation_criteria` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `weight` double(5,2) DEFAULT NULL,
  `result` varchar(75) DEFAULT NULL,
  `evaluation_id` bigint(20) unsigned DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `screening_evaluation_criteria_evaluation_id_foreign` (`evaluation_id`),
  KEY `screening_evaluation_criteria_parent_id_foreign` (`parent_id`),
  CONSTRAINT `screening_evaluation_criteria_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `screening_evaluation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `screening_evaluation_criteria_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `screening_evaluation_criteria` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `screening_evaluation_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `screening_evaluation_custom_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `evaluation_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `screening_evaluation_custom_fields_evaluation_id_foreign` (`evaluation_id`),
  CONSTRAINT `screening_evaluation_custom_fields_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `screening_evaluation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `screening_evaluation_fields_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `screening_evaluation_fields_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `custom_field_id` bigint(20) unsigned DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `screening_evaluation_fields_values_custom_field_id_foreign` (`custom_field_id`),
  KEY `screening_evaluation_fields_values_user_id_foreign` (`user_id`),
  KEY `screening_evaluation_fields_values_company_id_foreign` (`company_id`),
  CONSTRAINT `screening_evaluation_fields_values_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `screening_evaluation_fields_values_custom_field_id_foreign` FOREIGN KEY (`custom_field_id`) REFERENCES `screening_evaluation_custom_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `screening_evaluation_fields_values_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `screening_evaluation_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `screening_evaluation_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `evaluation_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `criteria_id` bigint(20) unsigned DEFAULT NULL,
  `score` varchar(10) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = Draft, 1 = Published',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `screening_evaluation_results_user_id_foreign` (`user_id`),
  KEY `screening_evaluation_results_criteria_id_foreign` (`criteria_id`),
  KEY `screening_evaluation_results_evaluation_id_foreign` (`evaluation_id`),
  KEY `screening_evaluation_results_company_id_foreign` (`company_id`),
  CONSTRAINT `screening_evaluation_results_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `screening_evaluation_results_criteria_id_foreign` FOREIGN KEY (`criteria_id`) REFERENCES `screening_evaluation_criteria` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `screening_evaluation_results_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `screening_evaluation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `screening_evaluation_results_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `screenings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `screenings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `step_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `panel` int(11) NOT NULL DEFAULT 1,
  `users` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `screenings_step_id_foreign` (`step_id`),
  KEY `screenings_user_id_foreign` (`user_id`),
  CONSTRAINT `screenings_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `screenings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sectors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_en` varchar(500) DEFAULT NULL,
  `name_ar` varchar(500) DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sectors_parent_id_foreign` (`parent_id`),
  CONSTRAINT `sectors_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `sectors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `service_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `name_en` varchar(255) NOT NULL,
  `name_ar` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_categories_organization_id_foreign` (`organization_id`),
  CONSTRAINT `service_categories_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `service_category_id` bigint(20) unsigned NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `name_ar` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description_en` text DEFAULT NULL,
  `description_ar` text DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `vendor_email` varchar(255) DEFAULT NULL,
  `vendor_phone` varchar(255) DEFAULT NULL,
  `hide_price` tinyint(1) NOT NULL DEFAULT 0,
  `hide_vendor_info` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `services_service_category_id_foreign` (`service_category_id`),
  KEY `services_organization_id_foreign` (`organization_id`),
  CONSTRAINT `services_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `services_service_category_id_foreign` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `services_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `services_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  `approved` varchar(3) DEFAULT NULL,
  `rate` int(11) DEFAULT NULL,
  `rate_comment` varchar(250) DEFAULT NULL,
  `admin_comment` varchar(255) DEFAULT NULL,
  `admin` bigint(20) unsigned DEFAULT NULL,
  `done` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_requests_service_id_foreign` (`service_id`),
  KEY `services_requests_user_id_foreign` (`user_id`),
  KEY `services_requests_admin_foreign` (`admin`),
  CONSTRAINT `services_requests_admin_foreign` FOREIGN KEY (`admin`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_requests_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `services_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `session_attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `session_attendances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `attended` tinyint(1) NOT NULL DEFAULT 0,
  `joined_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `session_attendances_session_id_foreign` (`session_id`),
  KEY `session_attendances_user_id_foreign` (`user_id`),
  CONSTRAINT `session_attendances_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `training_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `session_attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `session_recordings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `session_recordings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` bigint(20) unsigned DEFAULT NULL,
  `mentorship_request_id` bigint(20) unsigned DEFAULT NULL,
  `recording_type` varchar(255) NOT NULL,
  `recording_id` varchar(255) NOT NULL,
  `recording_view_url` longtext DEFAULT NULL,
  `recording_download_url` longtext DEFAULT NULL,
  `recording_time` timestamp NULL DEFAULT NULL,
  `passcode` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_recordings_recording_id_unique` (`recording_id`),
  KEY `session_recordings_mentorship_request_id_foreign` (`mentorship_request_id`),
  KEY `session_recordings_session_id_foreign` (`session_id`),
  CONSTRAINT `session_recordings_mentorship_request_id_foreign` FOREIGN KEY (`mentorship_request_id`) REFERENCES `mentorship_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `session_recordings_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `training_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `session_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `session_reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` bigint(20) unsigned DEFAULT NULL,
  `mentorship_request_id` bigint(20) unsigned DEFAULT NULL,
  `participant_user_id` varchar(255) DEFAULT NULL,
  `participant_email` varchar(255) DEFAULT NULL,
  `participant_name` varchar(255) DEFAULT NULL,
  `participant_join_date` timestamp NULL DEFAULT NULL,
  `participant_end_date` timestamp NULL DEFAULT NULL,
  `attendance_duration` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `session_reports_mentorship_request_id_foreign` (`mentorship_request_id`),
  KEY `session_reports_session_id_foreign` (`session_id`),
  CONSTRAINT `session_reports_mentorship_request_id_foreign` FOREIGN KEY (`mentorship_request_id`) REFERENCES `mentorship_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `session_reports_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `training_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `title` varchar(250) NOT NULL,
  `ar_title` varchar(250) NOT NULL,
  `dsc` text DEFAULT NULL,
  `ar_dsc` text DEFAULT NULL,
  `set1` text DEFAULT NULL,
  `ar_set1` text DEFAULT NULL,
  `set2` text DEFAULT NULL,
  `ar_set2` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `set_order` int(11) DEFAULT NULL,
  `user_added` bigint(20) unsigned DEFAULT NULL,
  `HomePage` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `Platform_Name` varchar(255) DEFAULT NULL,
  `Platform_support_email` varchar(255) DEFAULT NULL,
  `Country` varchar(255) DEFAULT NULL,
  `State` varchar(255) DEFAULT NULL,
  `Default_Language` varchar(255) DEFAULT NULL,
  `timezone` text DEFAULT NULL,
  `Currency` text DEFAULT NULL,
  `Logo` text DEFAULT NULL,
  `header` text DEFAULT NULL,
  `footer` text DEFAULT NULL,
  `google` text DEFAULT NULL,
  `host` text DEFAULT NULL,
  `Mail` text DEFAULT NULL,
  `Mailpassword` text DEFAULT NULL,
  `Port` int(11) DEFAULT NULL,
  `encryption` varchar(255) DEFAULT NULL,
  `BroadCast` text DEFAULT NULL,
  `PUSHER_APP_KEY` text DEFAULT NULL,
  `PUSHER_APP_ID` text DEFAULT NULL,
  `PUSHER_APP_SECRET` text DEFAULT NULL,
  `PUSHER_APP_CLUSTER` text DEFAULT NULL,
  `MIX_PUSHER_APP_KEY` text DEFAULT NULL,
  `MIX_PUSHER_APP_CLUSTER` text DEFAULT NULL,
  `Meeting` text DEFAULT NULL,
  `GOOGLE_CLIENT_ID` text DEFAULT NULL,
  `GOOGLE_CLIENT_SECRET` text DEFAULT NULL,
  `GOOGLE_REDIRECT` text DEFAULT NULL,
  `GOOGLE_LOGIN` enum('enabled','disabled') NOT NULL DEFAULT 'disabled',
  `LINKEDIN_CLIENT_ID` varchar(255) DEFAULT NULL,
  `LINKEDIN_CLIENT_SECRET` varchar(255) DEFAULT NULL,
  `LINKEDIN_REDIRECT_URI` varchar(255) DEFAULT NULL,
  `LINKEDIN_LOGIN` enum('enabled','disabled') NOT NULL DEFAULT 'disabled',
  `MICROSOFT_CLIENT_ID` text DEFAULT NULL,
  `MICROSOFT_CLIENT_SECRET_VALUE` text DEFAULT NULL,
  `MICROSOFT_TENANT_ID` text DEFAULT NULL,
  `MICROSOFT_CLIENT_SECRET_ID` text DEFAULT NULL,
  `MICROSOFT_APP_EMAIL` text DEFAULT NULL,
  `MICROSOFT_REDIRECT_URI` text DEFAULT NULL,
  `ZOOM_CLIENT_ID` text DEFAULT NULL,
  `ZOOM_CLIENT_SECRET` text DEFAULT NULL,
  `ZOOM_ACCOUNT_ID` text DEFAULT NULL,
  `VFEmail` int(11) NOT NULL DEFAULT 0 COMMENT 'تشغيل تأكيد الايميل او لا',
  `mentor_list` tinyint(1) NOT NULL DEFAULT 0,
  `assessment` tinyint(4) NOT NULL DEFAULT 0,
  `whatsApp_URL_callback` varchar(255) DEFAULT NULL,
  `whatsapp_api_key` varchar(255) DEFAULT NULL,
  `whatsApp_ACCESS_TOKEN` varchar(255) DEFAULT NULL,
  `terms_url` varchar(255) DEFAULT NULL,
  `privacy_url` varchar(255) DEFAULT NULL,
  `GOOGLE_CALENDAR_CLIENT_ID` text DEFAULT NULL,
  `GOOGLE_CALENDAR_CLIENT_SECRET` text DEFAULT NULL,
  `GOOGLE_CALENDAR_REDIRECT` text DEFAULT NULL,
  `whatsApp_SESSION_ID` varchar(255) DEFAULT NULL,
  `whatsApp_Status_Session` tinyint(4) NOT NULL DEFAULT 0,
  `notifications_email_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `notifications_whatsapp_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `notifications_pusher_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `enable_email` tinyint(1) NOT NULL DEFAULT 1,
  `enable_whatsapp` tinyint(1) NOT NULL DEFAULT 1,
  `enable_pusher` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `settings_user_added_foreign` (`user_added`),
  CONSTRAINT `settings_user_added_foreign` FOREIGN KEY (`user_added`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shared_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shared_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_formation_id` bigint(20) unsigned NOT NULL,
  `team_member_id` bigint(20) unsigned NOT NULL,
  `file` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `cycle_id` bigint(20) unsigned NOT NULL,
  `step_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shared_files_team_formation_id_foreign` (`team_formation_id`),
  KEY `shared_files_team_member_id_foreign` (`team_member_id`),
  KEY `shared_files_user_id_foreign` (`user_id`),
  KEY `shared_files_cycle_id_foreign` (`cycle_id`),
  KEY `shared_files_step_id_foreign` (`step_id`),
  CONSTRAINT `shared_files_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shared_files_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shared_files_team_formation_id_foreign` FOREIGN KEY (`team_formation_id`) REFERENCES `team_formations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shared_files_team_member_id_foreign` FOREIGN KEY (`team_member_id`) REFERENCES `team_members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shared_files_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sliders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sliders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `img_path` varchar(255) NOT NULL,
  `title` varchar(250) NOT NULL,
  `ar_title` varchar(250) NOT NULL,
  `img_desc` varchar(255) DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `url_title` varchar(255) DEFAULT NULL,
  `ar_url_title` varchar(255) DEFAULT NULL,
  `ar_img_desc` varchar(255) DEFAULT NULL,
  `slideorder` int(11) NOT NULL,
  `user_added` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sliders_user_added_foreign` (`user_added`),
  CONSTRAINT `sliders_user_added_foreign` FOREIGN KEY (`user_added`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `description_ar` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `states` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` bigint(20) unsigned NOT NULL,
  `name_ar` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `states_country_id_foreign` (`country_id`),
  CONSTRAINT `states_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `static_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `static_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) NOT NULL,
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `steps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cycle_id` bigint(20) unsigned NOT NULL,
  `title` varchar(250) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stype` int(11) NOT NULL,
  `data` varchar(250) DEFAULT NULL,
  `list` longtext DEFAULT NULL,
  `arr` int(11) NOT NULL,
  `from` date DEFAULT NULL,
  `to` date DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `public_slug` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `count_team_number` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of team members in the team for hackathon step',
  PRIMARY KEY (`id`),
  KEY `steps_cycle_id_foreign` (`cycle_id`),
  CONSTRAINT `steps_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sub_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sub_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `Type` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sub_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `sub_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` bigint(20) unsigned DEFAULT NULL,
  `question_id` bigint(20) unsigned DEFAULT NULL,
  `row_no` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `survey_questions_survey_id_foreign` (`survey_id`),
  KEY `survey_questions_question_id_foreign` (`question_id`),
  CONSTRAINT `survey_questions_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions_bank` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `survey_questions_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `survey_id` bigint(20) unsigned DEFAULT NULL,
  `cycle_id` bigint(20) unsigned DEFAULT NULL,
  `scope_id` bigint(20) unsigned DEFAULT NULL,
  `triggered_on` varchar(75) DEFAULT NULL COMMENT 'scope: mentorship, training, quiz, etc',
  `triggered_at` varchar(75) DEFAULT NULL COMMENT 'id of the scope',
  `triggered_by` varchar(75) DEFAULT NULL COMMENT 'condition: session, due, quiz , etc',
  `responses` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `survey_responses_user_id_foreign` (`user_id`),
  KEY `survey_responses_survey_id_foreign` (`survey_id`),
  KEY `survey_responses_cycle_id_foreign` (`cycle_id`),
  KEY `survey_responses_scope_id_foreign` (`scope_id`),
  CONSTRAINT `survey_responses_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `survey_responses_scope_id_foreign` FOREIGN KEY (`scope_id`) REFERENCES `survey_scopes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `survey_responses_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `survey_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_scopes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_scopes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` bigint(20) unsigned DEFAULT NULL,
  `cycle_id` bigint(20) unsigned DEFAULT NULL,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `scope` varchar(25) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `trigger_by_session` tinyint(1) DEFAULT 1,
  `trigger_by_due` tinyint(1) DEFAULT 1,
  `trigger_by_quiz` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `survey_scopes_survey_id_foreign` (`survey_id`),
  KEY `survey_scopes_cycle_id_foreign` (`cycle_id`),
  KEY `survey_scopes_organization_id_foreign` (`organization_id`),
  CONSTRAINT `survey_scopes_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `survey_scopes_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `survey_scopes_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `surveys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_required` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `surveys_organization_id_foreign` (`organization_id`),
  CONSTRAINT `surveys_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sustainable_icon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sustainable_icon` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` tinytext DEFAULT NULL,
  `icon_ar` tinytext DEFAULT NULL,
  `icon_en` tinytext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `swot_analyses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `swot_analyses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `strength` text DEFAULT NULL,
  `weakness` text DEFAULT NULL,
  `opportunity` text DEFAULT NULL,
  `threat` text DEFAULT NULL,
  `srecommendation` text DEFAULT NULL,
  `wrecommendation` text DEFAULT NULL,
  `orecommendation` text DEFAULT NULL,
  `trecommendation` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `swot_analyses_company_id_foreign` (`company_id`),
  CONSTRAINT `swot_analyses_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tag_training`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tag_training` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` bigint(20) unsigned NOT NULL,
  `training_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tag_training_tag_id_foreign` (`tag_id`),
  KEY `tag_training_training_id_foreign` (`training_id`),
  CONSTRAINT `tag_training_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tag_training_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `team_formations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_formations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_id_chat` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Name of the team formation',
  `description` text DEFAULT NULL COMMENT 'Description of the team formation',
  `img_team` varchar(255) DEFAULT NULL,
  `cycle_id` bigint(20) unsigned NOT NULL COMMENT 'ID of the cycle this team formation belongs to',
  `step_id` bigint(20) unsigned NOT NULL COMMENT 'ID of the step this team formation belongs to',
  `max_teams_members` int(11) NOT NULL DEFAULT 0 COMMENT 'Maximum number of teams allowed in this formation',
  `winner_status` varchar(75) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `project_name_ar` varchar(255) DEFAULT NULL,
  `project_name_en` varchar(255) DEFAULT NULL,
  `project_description_ar` text DEFAULT NULL,
  `project_description_en` text DEFAULT NULL,
  `target_track` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `project_status` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `team_formations_cycle_id_foreign` (`cycle_id`),
  KEY `team_formations_step_id_foreign` (`step_id`),
  KEY `team_formations_user_id_foreign` (`user_id`),
  CONSTRAINT `team_formations_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_formations_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_formations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `gender` varchar(255) DEFAULT NULL,
  `employment_type` varchar(255) DEFAULT NULL,
  `hiring_date` date DEFAULT NULL,
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `TeamMember` varchar(255) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `LinkedIn` varchar(255) DEFAULT NULL,
  `RoleShereholder` varchar(255) DEFAULT NULL,
  `PresentagShereholder` double(14,2) DEFAULT NULL,
  `investorType` varchar(255) DEFAULT NULL,
  `Company_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `acceptInvition` varchar(255) DEFAULT NULL,
  `isTeamLeader` tinyint(4) NOT NULL DEFAULT 0,
  `team_formation_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `expire_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_members_company_id_foreign` (`Company_id`),
  KEY `team_members_user_id_foreign` (`user_id`),
  KEY `team_members_team_formation_id_foreign` (`team_formation_id`),
  CONSTRAINT `team_members_company_id_foreign` FOREIGN KEY (`Company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_members_team_formation_id_foreign` FOREIGN KEY (`team_formation_id`) REFERENCES `team_formations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `team_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `technologies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `technologies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `time_sheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `time_sheets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` text DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `hours_worked` double(8,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `program_id` bigint(20) unsigned DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0 COMMENT 'if 0=Draft and 1=Submitted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `todo_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `todo_lists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL COMMENT 'To Do, In Progress, Done',
  `assignee` varchar(255) DEFAULT NULL,
  `due_date` varchar(20) DEFAULT NULL,
  `cycle_id` bigint(20) unsigned DEFAULT NULL,
  `step_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `team_formation_id` bigint(20) unsigned DEFAULT NULL,
  `team_member_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `training_completed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_completed` (
  `training_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  KEY `training_completed_training_id_foreign` (`training_id`),
  KEY `training_completed_user_id_foreign` (`user_id`),
  KEY `training_completed_course_id_foreign` (`course_id`),
  CONSTRAINT `training_completed_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `training_completed_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `training_contents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `training_completed_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `training_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_contents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `training_id` bigint(20) unsigned NOT NULL,
  `title` varchar(191) NOT NULL,
  `arr` int(11) NOT NULL DEFAULT 0,
  `content` text NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `training_contents_training_id_foreign` (`training_id`),
  KEY `training_contents_user_id_foreign` (`user_id`),
  CONSTRAINT `training_contents_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `training_contents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `training_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `step_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(10) NOT NULL,
  `title` varchar(191) NOT NULL,
  `dsc` varchar(2048) DEFAULT NULL,
  `datetime` varchar(20) DEFAULT NULL,
  `enddatetime` varchar(20) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `zoom_id` varchar(250) DEFAULT NULL,
  `zoom_passcode` varchar(25) DEFAULT NULL,
  `start_url` varchar(1024) DEFAULT NULL,
  `join_url` varchar(1024) DEFAULT NULL,
  `trainer_name` varchar(250) DEFAULT NULL,
  `trainer_email` varchar(255) DEFAULT NULL,
  `training_Address` varchar(255) DEFAULT NULL,
  `training_location` text DEFAULT NULL COMMENT 'training location Google Map',
  `QrCode` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `training_sessions_step_id_foreign` (`step_id`),
  KEY `training_sessions_user_id_foreign` (`user_id`),
  CONSTRAINT `training_sessions_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `training_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trainings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `trainings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL,
  `dsc` varchar(250) DEFAULT NULL,
  `image` varchar(250) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `CoursLevel` varchar(255) DEFAULT NULL,
  `Quizs` int(11) DEFAULT NULL,
  `Instractor` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `Status` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `trainings_user_id_foreign` (`user_id`),
  CONSTRAINT `trainings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_oauth_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_oauth_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `provider` varchar(255) NOT NULL DEFAULT 'google',
  `refresh_token` text DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_oauth_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `user_oauth_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_scores` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `subcategory_id` bigint(20) unsigned NOT NULL,
  `stage_id` bigint(20) unsigned NOT NULL,
  `score` decimal(8,2) NOT NULL DEFAULT 0.00,
  `weight` decimal(8,2) NOT NULL DEFAULT 0.00,
  `weighted_score` decimal(8,2) NOT NULL DEFAULT 0.00,
  `assessment_id` bigint(20) unsigned NOT NULL,
  `QuestionBank` text DEFAULT NULL,
  `explanation_QA_ar` longtext DEFAULT NULL,
  `explanation_QA_en` longtext DEFAULT NULL,
  `Answers` longtext DEFAULT NULL,
  `Result_assessment` tinytext DEFAULT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_scores_user_id_foreign` (`user_id`),
  KEY `user_scores_category_id_foreign` (`category_id`),
  KEY `user_scores_subcategory_id_foreign` (`subcategory_id`),
  KEY `user_scores_assessment_id_foreign` (`assessment_id`),
  KEY `user_scores_company_id_foreign` (`company_id`),
  KEY `user_scores_stage_id_foreign` (`stage_id`),
  CONSTRAINT `user_scores_assessment_id_foreign` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_scores_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_scores_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_scores_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `stages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_scores_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_scores_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `first_name_en` varchar(250) NOT NULL,
  `last_name_en` varchar(250) NOT NULL,
  `first_name_ar` varchar(250) DEFAULT NULL,
  `last_name_ar` varchar(255) DEFAULT NULL,
  `guardian` varchar(250) DEFAULT NULL,
  `gender` varchar(7) NOT NULL DEFAULT 'Male',
  `age` varchar(15) DEFAULT NULL,
  `country_code` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `country_code_w_a` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `whatsapp_verified` int(11) NOT NULL DEFAULT 0,
  `email` varchar(255) NOT NULL,
  `new_email` varchar(255) DEFAULT NULL COMMENT 'البريد الإلكتروني الجديد',
  `zip_code` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `title_ar` varchar(250) DEFAULT NULL,
  `title_en` varchar(250) DEFAULT NULL,
  `HaveCompany` int(11) DEFAULT 0,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `country_id` bigint(20) unsigned DEFAULT NULL,
  `state_id` bigint(20) unsigned DEFAULT NULL,
  `city_id` bigint(20) unsigned DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `academic_stat` varchar(255) DEFAULT NULL,
  `university` varchar(255) DEFAULT NULL,
  `faculty` varchar(255) DEFAULT NULL,
  `employer` varchar(255) DEFAULT NULL,
  `expertise` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `provider_id` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `linkedin_id` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `api_token` varchar(60) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `PageId` int(11) DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  `currency` tinytext DEFAULT NULL,
  `timezone` tinytext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `houre_rate` double(8,2) NOT NULL DEFAULT 0.00 COMMENT 'add houre rate',
  `department_id` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT 'department number user',
  `google2fa_secret` text DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `accept_email` tinyint(1) NOT NULL DEFAULT 1,
  `accept_whatsapp` tinyint(1) NOT NULL DEFAULT 0,
  `accept_pusher` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_country_id_foreign` (`country_id`),
  KEY `users_state_id_foreign` (`state_id`),
  KEY `users_city_id_foreign` (`city_id`),
  KEY `users_company_id_foreign` (`company_id`),
  KEY `users_organization_id_foreign` (`organization_id`),
  CONSTRAINT `users_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users_bio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_bio` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `industries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`industries`)),
  `user_id` bigint(20) unsigned NOT NULL,
  `bio_ar` mediumtext DEFAULT NULL,
  `bio_en` mediumtext DEFAULT NULL,
  `specialization_ar` varchar(250) DEFAULT NULL,
  `specialization_en` varchar(250) DEFAULT NULL,
  `linkedin` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `entrepreneurial_experience` tinyint(1) NOT NULL DEFAULT 0,
  `entrepreneurial_experience_brief` mediumtext NOT NULL DEFAULT '0',
  `profile_privacy` tinyint(1) NOT NULL DEFAULT 1,
  `mentorship_approach` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_bio_user_id_foreign` (`user_id`),
  CONSTRAINT `users_bio_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `weights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `weights` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stage_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `subcategory_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned DEFAULT NULL,
  `weight` double(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `weights_stage_id_foreign` (`stage_id`),
  KEY `weights_category_id_foreign` (`category_id`),
  KEY `weights_subcategory_id_foreign` (`subcategory_id`),
  KEY `weights_question_id_foreign` (`question_id`),
  CONSTRAINT `weights_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `weights_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions_bank` (`id`) ON DELETE CASCADE,
  CONSTRAINT `weights_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `stages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `weights_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wire_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wire_actions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actionable_id` bigint(20) unsigned NOT NULL,
  `actionable_type` varchar(255) NOT NULL,
  `actor_id` bigint(20) unsigned NOT NULL,
  `actor_type` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `data` varchar(255) DEFAULT NULL COMMENT 'Some additional information about the action',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wire_actions_actionable_id_actionable_type_index` (`actionable_id`,`actionable_type`),
  KEY `wire_actions_actor_id_actor_type_index` (`actor_id`,`actor_type`),
  KEY `wire_actions_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wire_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wire_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attachable_type` varchar(255) NOT NULL,
  `attachable_id` bigint(20) unsigned NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wire_attachments_attachable_type_attachable_id_index` (`attachable_type`,`attachable_id`),
  KEY `wire_attachments_attachable_id_attachable_type_index` (`attachable_id`,`attachable_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wire_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wire_conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL COMMENT 'Private is 1-1 , group or channel',
  `disappearing_started_at` timestamp NULL DEFAULT NULL,
  `disappearing_duration` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wire_conversations_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wire_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wire_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` char(36) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'private',
  `allow_members_to_send_messages` tinyint(1) NOT NULL DEFAULT 1,
  `allow_members_to_add_others` tinyint(1) NOT NULL DEFAULT 1,
  `allow_members_to_edit_group_info` tinyint(1) NOT NULL DEFAULT 0,
  `admins_must_approve_new_members` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'when turned on, admins must approve anyone who wants to join group',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wire_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wire_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `sendable_id` bigint(20) unsigned NOT NULL,
  `sendable_type` varchar(255) NOT NULL,
  `reply_id` bigint(20) unsigned DEFAULT NULL,
  `body` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `kept_at` timestamp NULL DEFAULT NULL COMMENT 'filled when a message is kept from disappearing',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wire_messages_reply_id_foreign` (`reply_id`),
  KEY `wire_messages_sendable_id_sendable_type_index` (`sendable_id`,`sendable_type`),
  KEY `wire_messages_conversation_id_foreign` (`conversation_id`),
  CONSTRAINT `wire_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `wire_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wire_messages_reply_id_foreign` FOREIGN KEY (`reply_id`) REFERENCES `wire_messages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wire_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wire_participants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` char(36) NOT NULL,
  `role` varchar(255) NOT NULL,
  `participantable_id` bigint(20) unsigned NOT NULL,
  `participantable_type` varchar(255) NOT NULL,
  `exited_at` timestamp NULL DEFAULT NULL,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `conversation_cleared_at` timestamp NULL DEFAULT NULL,
  `conversation_deleted_at` timestamp NULL DEFAULT NULL,
  `conversation_read_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conv_part_id_type_unique` (`conversation_id`,`participantable_id`,`participantable_type`),
  KEY `wire_participants_role_index` (`role`),
  KEY `wire_participants_exited_at_index` (`exited_at`),
  KEY `wire_participants_conversation_cleared_at_index` (`conversation_cleared_at`),
  KEY `wire_participants_conversation_deleted_at_index` (`conversation_deleted_at`),
  KEY `wire_participants_conversation_read_at_index` (`conversation_read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

/*M!999999\- enable the sandbox mode */ 
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_10_12_100000_create_password_reset_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2020_03_18_215145_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2024_04_18_000000_create_countries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2024_04_18_000001_create_states_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2024_04_18_000003_create_cities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2024_04_18_000004_create_sectors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2024_04_18_000005_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2024_04_18_000006_create_audit_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2024_04_18_000007_create_users_bio_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2024_04_18_000008_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2024_04_18_000009_create_incubators_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2024_04_18_000009_create_menus_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2024_04_18_000010_create_pages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2024_04_18_000011_create_partners_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2024_04_18_000012_create_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2024_04_18_000013_create_sliders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2024_04_18_000014_create_press_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2024_04_18_000015_create_posts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2024_04_18_000016_create_organizations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2024_04_18_000017_create_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2024_04_18_000018_create_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2024_04_18_000019_create_role_has_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2024_04_18_000020_create_model_has_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2024_04_18_000021_create_model_has_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2024_04_18_000022_create_faqs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2024_04_18_000023_create_inclusives_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2024_04_18_000024_create_schedules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2024_04_18_000025_create_service_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2024_04_18_000026_create_services_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2024_04_18_000027_create_services_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2024_04_18_000028_create_messenger_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2024_04_18_000029_create_messages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2024_04_18_000030_create_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2024_04_18_000031_create_events_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2024_04_18_000032_create_documentations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2024_04_18_000033_create_builder_forms_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2024_04_18_000034_create_fields_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2024_04_18_000035_create_builder_fields_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2024_04_18_000036_create_calendars_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2024_04_18_000037_create_cycles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2024_04_18_000038_create_steps_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2024_04_18_000039_create_cycles_mentors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2024_04_18_000040_create_form_fields_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2024_04_18_000041_create_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2024_04_18_000042_create_final_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2024_04_18_000043_create_finalscreenings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2024_04_18_000044_create_form_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2024_04_18_000045_create_mentorship_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2024_04_18_000046_create_rooms_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2024_04_18_000047_create_room_bookings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2024_04_18_000048_create_rooms_imgs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2024_04_18_000049_create_rooms_inclusives_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2024_04_18_000050_create_trainings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2024_04_18_000051_create_training_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2024_04_18_000052_create_training_contents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2024_04_18_000053_create_screenings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2024_04_18_134136_create_training_completed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2024_04_21_153258_add_cols_company_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2024_04_23_102343_add_col_organization_id_to_mentorship_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2024_06_09_075407_create_expertises_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2024_06_11_140618_add_cols_entrepreneurial_to_users_bio_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2024_07_02_210129_create_mentorship_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2024_07_19_163827_create_mentorship_task_comments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2024_07_21_205612_add_col_business_type_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2024_07_22_214338_add_cols_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2024_07_23_133649_create_funding_rounds_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2024_07_24_205427_add_col_trainer_email_to_training_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2024_07_28_115919_add_col_id_to_finalscreenings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2024_07_29_163901_create_assessments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2024_07_29_164132_create_assessment_aspects_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2024_07_30_133703_add_cols_pitch_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2024_07_31_142045_add_cols_about_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2024_08_07_194057_create_user_oauth_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2024_08_08_024538_create_calender_syncs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2024_08_09_232214_create_questions_bank_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2024_08_10_192337_create_question_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2024_08_10_192926_add_category_id_to_questions_bank_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2024_08_11_164237_create_quizzes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2024_08_11_164237_create_surveys_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2024_08_11_164256_create_quiz_questions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2024_08_11_164256_create_survey_questions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2024_08_11_164518_create_quiz_results_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2024_08_11_164533_create_quiz_responses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2024_08_28_204514_add_group_to_questions_bank_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2024_08_31_114717_add_new_add_in_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2024_09_04_185116_add_columns_to_settings_and_changes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2024_09_18_101207_create_tags_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2024_09_18_101345_create_tag_training_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2024_09_18_113319_add_columns_to_trainings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2024_09_18_124113_create_categories_training_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2024_09_19_144722_add_language_currency_timezone_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2024_09_25_121456_add_user_id_in_tags__table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2024_09_26_225545_add_survey_type_to_questions_bank_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2024_09_28_095452_add_houre_rate_and_department_in_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2024_09_28_100647_create_timesheet_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2024_09_28_100727_create_catigories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2024_09_28_100835_create_finance_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2024_10_05_205207_add__home__page_in__settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2024_10_06_175259_add__sustainable_development_on_table_companies',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2024_10_06_185151_create_table_sustainable_icon',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2024_10_11_201932_create_team_members_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2024_10_11_202321_create__milestones_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2024_10_11_210730_add_columns_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2024_10_12_205444_create_currencies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2024_10_19_120835_add_new_column_in_companies',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2024_10_20_133347_add_country_code_in_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2024_11_06_152335_add__fields_in__compaaies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2024_11_08_184902_create_labels_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2024_11_08_185153_create_label_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2024_11_08_185942_add_new_column_in_mentortask_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2024_11_12_173201_add__attached_on_mentortaskcomment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2024_11_16_134634_add_other_business_model_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2024_11_16_151402_create_survey_scopes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2024_11_16_180132_add__funding_stage_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2024_11_17_111552_create_survey_responses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2024_11_19_102153_add_vfemail_to_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2024_11_27_211311_add_weight_and_type_to_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2024_11_28_104447_create_sub_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2024_11_28_112737_add_description_to_sub_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2024_11_30_132435_create_questions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2024_11_30_163637_create_answer_options_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2024_12_01_201938_add_zip_code_and_address_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2024_12_01_205428_add_as_category_id_and_subcategory_id_to_questions_bank_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2024_12_01_212635_add_group_to_questions_bank',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2024_12_03_195843_create_stages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2024_12_03_200115_create_weights_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2024_12_04_153104_change_team_num_companies_table=companies',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2024_12_04_210221_add_phone_code_to_countries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2024_12_05_090800_add_years_of_operations_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2024_12_05_094516_add_product_or_service_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2024_12_05_102625_add_business_categories_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2024_12_05_135214_Add_user_id_to_assessments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2024_12_05_210427_create_user_scores_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2024_12_08_143519_make_question_id_nullable_in_weights_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2024_12_10_161748_add_company_to_labels_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2024_12_10_163202_change_label_taks_table_to_mentorship_task_labels',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2024_12_10_164718_add_priority_and_reported_by_to_mentorship_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2024_12_10_165438_create_mentorship_task_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2024_12_11_074627_add_new_email_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2024_12_14_083824_add_linkedin_id_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2024_12_15_042450_add_row_no_column_to_mentorship_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2024_12_21_023433_add_redirect_linkedin_in_settigs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2024_12_24_012354_create_mentorship_task_files_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2024_12_26_020107_add_age_and_api_token_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2024_12_26_024201_add_sharks_columns_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2025_01_01_121524_add_type_to_survey_scopes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2025_01_01_225419_update_survey_type_in__question_bank',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2025_01_02_093832_create_session_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2025_01_02_110614_add_join_stock_company_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2025_01_02_161220_create_session_recordings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2025_01_08_180927_add_default_for_column_to_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2025_01_12_201907_add_new_coulmn__country_code_w_a_in_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2025_01_13_092859_add_passcode_to_session_recordings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2025_01_13_135231_update_cycles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2025_01_22_152316_add_whatsapp_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2025_01_22_163032_create_otps_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2025_01_24_172627_add_mentor_list_setting_to_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2025_01_26_120558_add_instagram_and_tiktok_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2025_01_29_100934_create_swot_analyses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2025_01_29_105633_add_wawp_settings_to_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2025_01_30_104212_change_columns_to_longtext_in_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2025_02_04_161132_create_organization_question_bank_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2025_02_05_231351_allow_nullable__country__register_id_at_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2025_02_10_110417_add_organization_details_to_organizations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2025_02_11_080041_add_microsoft_to_organizations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2025_02_12_091532_create_invitations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2025_02_16_095359_add_status_to_invitations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2025_02_18_221659_modify_email_phone_nullable_in_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2025_02_19_092748_update_business_phase_length_in_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2025_02_25_080923_create_projects_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2025_02_25_104512_create_cycle_project_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2025_02_25_125831_create_project_team_member_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2025_02_26_210410_add_organization_id_to_services_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2025_02_27_062425_add_assessment_to_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2025_03_04_110326_add_colsto_services_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2025_03_05_140611_add_qr_code_to_training_sessions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2025_03_06_121248_create_attendance_training_offline_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2025_03_08_105119_add_google_2fa_columns_in_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2025_03_09_140124_update_location_training_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2025_03_17_104502_create_organization_service_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2025_03_23_144429_update_user_id_in_messages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2025_03_26_102517_add_terms_and_privacy_to_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2025_03_30_022447_change__presentag_shereholder_to_accept_decimal',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2025_04_08_213840_create_message_drafts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2025_04_09_062206_add_starred_to_messenger_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2025_04_09_221754_add_is_core_column_to_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2025_04_10_053022_create_message_replies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2025_04_10_125545_add_reply_id_to_messenger_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2025_04_14_135013_add_cycle_id_to_message_drafts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2025_04_17_110042_create_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2025_04_20_164713_create_permission_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2025_04_28_064953_add_auto_approve_mentorship_requests_to_cycles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2025_04_28_193632_create_screening_evaluation_module',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2025_05_16_011125_add_evaluation_id_and_status_to_screening_evaluation_results_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (198,'2025_05_26_120012_update_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (199,'2025_05_27_150656_change_datetime_columns_type_in_traning_sessions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2025_06_02_144234_add_session_columns_to_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2025_06_03_091130_rename_whatsapp_instance_id_to_api_key_in_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (202,'2025_06_16_072005_update_reports',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (203,'2025_06_16_080044_add_zoom_id_to_mentorship_request_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2025_06_16_161321_add_session_whatsapp_in_organization_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2025_06_18_092954_update_session_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2025_06_18_093038_update_session_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2025_06_20_205539_add_company_id_to_screening_evaluation_results_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2025_06_24_193958_add_company_id_to_screening_evaluation_fields_values_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2025_06_26_135505_add_img_url_to_cycles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2025_07_06_064053_add_title_ar_to_expertises_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (211,'2025_07_09_093907_add_status_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (212,'2025_07_13_085035_create_team_formation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (213,'2025_07_13_086547_add_user_id_and_team_id_in_team_members_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (214,'2025_07_14_071520_add_type_to_cycles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (215,'2025_07_14_090907_add_count_team_number_to_steps_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (216,'2025_07_14_221920_add_builder_forms_id_to_form_fields_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (217,'2025_07_17_112040_add_user_id_to_team_formations_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (218,'2025_07_17_112720_add_accept_invition_and_is_team_leader_to_team_members_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (219,'2025_07_20_120123_create_activities_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (220,'2025_07_22_074852_add_code_and_expire_at_to_team_members_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (221,'2025_07_23_154325_add_project_fields_to_team_formations_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (222,'2025_07_28_085856_add_img_team_to_team_formation_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (223,'2025_07_25_212514_add_public_fields_to_steps_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (224,'2025_07_25_221814_add_short_code_column_to_organizations_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (225,'2025_07_27_235435_add_public_fields_to_cycles_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (226,'2025_07_28_015310_create_cycle_custom_pages_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (227,'2025_07_29_093621_add_override_reason_to_mentorship_requests_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (228,'2025_08_04_090655_create_sharedfile_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (229,'2025_08_05_095221_add_encryption_to_settings_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (230,'2025_08_05_073603_create_todo_lists_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (231,'2025_08_08_105630_add_region_to_team_members_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (232,'2025_08_08_110248_add_region_to_team__formation_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (233,'2025_08_08_195457_add_zoom_passcode_to_tables',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (234,'2025_08_08_210935_change_columns_format_on_jobs_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (235,'2024_11_01_000001_create_wirechat_conversations_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (236,'2024_11_01_000002_create_wirechat_attachments_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (237,'2024_11_01_000003_create_wirechat_messages_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (238,'2024_11_01_000004_create_wirechat_participants_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (239,'2024_11_01_000006_create_wirechat_actions_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (240,'2024_11_01_000007_create_wirechat_groups_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (241,'2025_08_10_135556_add_winner_status_to_team_formations_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (242,'2025_08_10_173008_add_winners_announcement_to_cycles_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (243,'2024_08_17_131900_create_notification_templates_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (244,'2024_08_17_140200_create_notification_triggers_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (245,'2025_08_20_020651_fix_wire_conversations_id_field',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (246,'2025_08_18_173320_add_user_notification_setting',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (247,'2025_08_26_131330_add_group_id_chat_to_team_formations_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (248,'2025_08_28_201542_add_winners_visibility_to_cycles_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (249,'2025_08_28_215724_create_project_likes_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (250,'2025_09_13_104127_add_hours_and_status_to_cycles_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (251,'2025_09_13_135455_create_reminder_logs_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (252,'2025_09_14_121609_create_mentorship_schedules_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (253,'2025_09_26_154244_add_cycle_id_to_mentorship_schedules_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (254,'2025_09_30_104417_create_static_categories_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (255,'2025_09_30_120929_create_technologies_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (256,'2025_10_01_093613_create_industry_sector_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (257,'2025_10_02_084907_create_company_relations_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (258,'2025_10_10_143156_add_completion_and_documents_to_milestones_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (259,'2025_10_10_143624_remove_plan_field_from_milestones_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (260,'2025_10_07_081239_add_step_type_to_cycles_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (261,'2025_10_12_141516_update_session_recordings_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (262,'2025_10_19_090136_add_linkedin_login_column_to_settings_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (263,'2025_10_19_100337_add_google_login_column_to_settings_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (264,'2025_10_20_041227_create_chat_tables',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (265,'2025_10_20_041250_add_chat_widget_to_sidebar',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (266,'2025_10_20_094521_add_is_required_to_surveys_table',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (267,'2025_10_20_130850_create_session_attendances_table',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (268,'2025_10_21_092120_alter_session_recordings_columns_to_text',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (269,'2025_10_23_181101_create_page_durations_table',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (270,'2025_10_26_150123_update_static_categories_table_add_name_ar_and_rename_name_column',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (271,'2025_09_14_171013_organizations_settings',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (272,'2025_09_14_173739_organizations_move_data',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (273,'2025_09_14_173748_organizations_delete_coulum',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (274,'2025_09_30_115625_create_industries_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (275,'2024_11_11_000000_add_notification_channels_to_settings_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (276,'2024_11_11_000001_add_notification_channels_to_settings_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (277,'2025_10_31_122009_change_field_type_column_in_fields_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (278,'2025_11_09_141942_add_cohort_to_cycles_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (279,'2025_11_10_164201_add_industries_to_users_bio_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (280,'2025_11_14_173739_organizations_move_data',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (281,'2025_11_14_171013_organizations_settings',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (282,'2025_11_14_173738_organizations_move_data',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (283,'2025_11_16_040500_add_uuid_to_failed_jobs_table',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (284,'2025_11_13_113056_add_fields_to_team_members_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (285,'2024_11_17_000001_create_employees_table',35);
