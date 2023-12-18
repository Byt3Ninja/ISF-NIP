-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 04, 2022 at 12:09 PM
-- Server version: 10.2.41-MariaDB
-- PHP Version: 7.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `master_vidb`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `subject_id` bigint(20) DEFAULT NULL,
  `subject_type` varchar(250) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `properties` text DEFAULT NULL,
  `host` varchar(46) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `description`, `subject_id`, `subject_type`, `user_id`, `properties`, `host`, `updated_at`, `created_at`) VALUES
(1, 'audit:created', 0, 'App\\Screening#0', 1, '{\"step_id\":\"100\",\"user_id\":\"31\",\"panel\":0,\"updated_at\":\"2022-01-02 10:09:36\",\"created_at\":\"2022-01-02 10:09:36\",\"id\":0}', '108.162.229.145', '2022-01-02 09:09:36', '2022-01-02 09:09:36'),
(2, 'audit:updated', 1, 'App\\User#1', 1, '{\"HaveCompany\":\"1\",\"updated_at\":\"2022-01-02 10:11:38\",\"id\":1}', '141.101.98.87', '2022-01-02 09:11:38', '2022-01-02 09:11:38'),
(3, 'audit:updated', 1, 'App\\User#1', 1, '{\"HaveCompany\":null,\"updated_at\":\"2022-01-02 10:11:54\",\"id\":1}', '141.101.98.87', '2022-01-02 09:11:54', '2022-01-02 09:11:54'),
(4, 'audit:updated', 1, 'App\\User#1', 1, '{\"HaveCompany\":\"1\",\"updated_at\":\"2022-01-02 10:11:59\",\"id\":1}', '141.101.98.87', '2022-01-02 09:11:59', '2022-01-02 09:11:59'),
(5, 'audit:updated', 1, 'App\\User#1', 1, '{\"img_url\":\"users\\/9hmHCBq0nLdktuAthZQdBO361qAPnrSuiqzAabdh.png\",\"updated_at\":\"2022-01-02 10:13:06\",\"id\":1}', '172.70.85.19', '2022-01-02 09:13:06', '2022-01-02 09:13:06'),
(6, 'audit:updated', 1, 'App\\User#1', 1, '{\"img_url\":\"users\\/9Z1P8Z2OGaSsgoxvgCUGtWYxUikEzl98m4qFqbak.png\",\"updated_at\":\"2022-01-02 13:30:13\",\"id\":1}', '172.70.162.221', '2022-01-02 12:30:13', '2022-01-02 12:30:13'),
(7, 'audit:deleted', 22, 'App\\Cycle#22', 1, '{\"id\":22,\"incubator_id\":1,\"title\":\"\\u062f\\u0648\\u0631\\u0629 \\u062a\\u062c\\u0631\\u064a\\u0628\\u064a\\u0629\",\"description\":null,\"start\":\"2021-12-27\",\"end\":\"2021-12-31\",\"private\":\"1031439410b2f4c55ad8ffe0711b20adc47b4246\",\"created_at\":\"2021-12-27 13:02:31\",\"updated_at\":\"2021-12-27 13:02:31\"}', '172.70.162.221', '2022-01-02 12:31:03', '2022-01-02 12:31:03'),
(8, 'audit:created', 1000189, 'App\\Calendar#1000189', 100, '{\"title\":\"Private\",\"start\":\"2022-01-17\",\"end\":\"2022-01-19\",\"allDay\":true,\"user_id\":100,\"isPrivate\":true,\"updated_at\":\"2022-01-04 11:05:11\",\"created_at\":\"2022-01-04 11:05:11\",\"id\":1000189}', '141.101.68.245', '2022-01-04 10:05:11', '2022-01-04 10:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `builder_fields`
--

CREATE TABLE `builder_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `builder_form_id` int(11) NOT NULL,
  `field_id` int(11) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `builder_fields`
--

INSERT INTO `builder_fields` (`id`, `builder_form_id`, `field_id`, `options`, `updated_at`, `created_at`) VALUES
(686, 43, 3, '{\"label\": \"Fav list\", \"values\": \"Football,\\nGames,\\nDrawing\", \"validation\": {\"required\": 1}}', '2021-11-05 23:33:44', '2021-11-05 23:33:44'),
(687, 43, 5, '{\"type\": \"file\", \"label\": \"Upload your CV\", \"validation\": {\"required\": 0}}', '2021-11-05 23:33:44', '2021-11-05 23:33:44'),
(685, 43, 2, '{\"rows\": 3, \"label\": \"Details\", \"validation\": {\"required\": 0}}', '2021-11-05 23:33:44', '2021-11-05 23:33:44'),
(684, 43, 1, '{\"type\": \"number\", \"label\": \"Phone\", \"validation\": {\"required\": 0}}', '2021-11-05 23:33:44', '2021-11-05 23:33:44'),
(683, 43, 1, '{\"type\": \"date\", \"label\": \"Join Date\", \"validation\": {\"required\": 1}}', '2021-11-05 23:33:44', '2021-11-05 23:33:44'),
(708, 46, 3, '{\"label\":\"\\u0642\\u0627\\u0626\\u0645\\u0629 \\u0627\\u0644\\u0645\\u062d\\u0627\\u0631\\u0628\\u064a\\u0646\",\"validation\":{\"required\":0},\"values\":\"\\u0645\\u062d\\u0627\\u0631\\u0628 1 ,\\n\\u0645\\u062d\\u0627\\u0631\\u0628  2 ,\\n\\u0645\\u062d\\u0627\\u0631\\u0628 3 ,\\n\\u0645\\u062d\\u0627\\u0631\\u0628 4\"}', '2021-12-25 13:06:55', '2021-12-25 13:06:55'),
(707, 46, 1, '{\"label\":\"\\u062a\\u0627\\u0631\\u064a\\u062e \\u0627\\u0644\\u0627\\u063a\\u0644\\u0627\\u0642\",\"validation\":{\"required\":0},\"type\":\"date\"}', '2021-12-25 13:06:55', '2021-12-25 13:06:55'),
(682, 43, 1, '{\"type\": \"email\", \"label\": \"Company Email\", \"validation\": {\"required\": 1}}', '2021-11-05 23:33:44', '2021-11-05 23:33:44'),
(712, 45, 1, '{\"label\":\"\\u062d\\u0642\\u0644 \\u0627\\u062e\\u062a\\u064a\\u0627\\u0631 \\u062a\\u0627\\u0631\\u064a\\u062e\",\"validation\":{\"required\":0},\"type\":\"date\"}', '2022-01-02 10:10:55', '2022-01-02 10:10:55'),
(711, 45, 1, '{\"label\":\"\\u062d\\u0642\\u0644 \\u0625\\u062f\\u062e\\u0627\\u0644\",\"validation\":{\"required\":0},\"type\":\"text\"}', '2022-01-02 10:10:55', '2022-01-02 10:10:55'),
(710, 45, 1, '{\"label\":\"Activity title\",\"validation\":{\"required\":1},\"type\":\"text\"}', '2022-01-02 10:10:55', '2022-01-02 10:10:55'),
(688, 49, 1, '{\"label\":\"Screening date\",\"validation\":{\"required\":0},\"type\":\"date\"}', '2021-12-05 17:13:21', '2021-12-05 17:13:21'),
(689, 49, 3, '{\"label\":\"Trainining\",\"validation\":{\"required\":1},\"values\":\"Well trained,Need to train\"}', '2021-12-05 17:13:21', '2021-12-05 17:13:21'),
(690, 49, 1, '{\"label\":\"Descripe in one word\",\"validation\":{\"required\":0},\"type\":\"text\"}', '2021-12-05 17:13:21', '2021-12-05 17:13:21'),
(691, 49, 2, '{\"label\":\"Your Comments\",\"validation\":{\"required\":0},\"rows\":3}', '2021-12-05 17:13:21', '2021-12-05 17:13:21'),
(692, 50, 1, '{\"label\":\"Name\",\"validation\":{\"required\":0},\"type\":\"text\"}', '2021-12-19 10:15:36', '2021-12-19 10:15:36'),
(693, 50, 1, '{\"label\":\"Email \",\"validation\":{\"required\":1},\"type\":\"email\"}', '2021-12-19 10:15:36', '2021-12-19 10:15:36'),
(694, 50, 3, '{\"label\":\"Company\",\"validation\":{\"required\":1},\"values\":\"company, not company\"}', '2021-12-19 10:15:36', '2021-12-19 10:15:36'),
(695, 50, 5, '{\"label\":\"Upload file\",\"validation\":{\"required\":0},\"type\":\"file\"}', '2021-12-19 10:15:36', '2021-12-19 10:15:36'),
(709, 46, 5, '{\"label\":\"\\u0627\\u0644\\u0635\\u0648\\u0631\\u0629 \\u0627\\u0644\\u0643\\u0628\\u064a\\u0631\\u0629\",\"validation\":{\"required\":1},\"type\":\"file\"}', '2021-12-25 13:06:55', '2021-12-25 13:06:55'),
(706, 46, 1, '{\"label\":\"\\u0627\\u0644\\u0627\\u0633\\u0645 \\u0631\\u0628\\u0627\\u0639\\u064a\",\"validation\":{\"required\":0},\"type\":\"text\"}', '2021-12-25 13:06:55', '2021-12-25 13:06:55'),
(704, 46, 1, '{\"label\":\"\\u062a\\u0627\\u0631\\u064a\\u062e \\u0627\\u0644\\u0625\\u0646\\u0634\\u0627\\u0621\",\"validation\":{\"required\":1},\"type\":\"date\"}', '2021-12-25 13:06:55', '2021-12-25 13:06:55'),
(705, 46, 3, '{\"label\":\"\\u0645\\u062c\\u0627\\u0644 \\u0627\\u0644\\u0639\\u0645\\u0644\",\"validation\":{\"required\":0},\"values\":\"\\u0627\\u0644\\u0628\\u0631\\u0645\\u062c\\u064a\\u0627\\u062a,\\n\\u0627\\u0644\\u0635\\u064a\\u0627\\u0646\\u0629,\\n\\u0627\\u0644\\u062a\\u0648\\u0631\\u064a\\u062f\\u0627\\u062a\"}', '2021-12-25 13:06:55', '2021-12-25 13:06:55'),
(703, 46, 1, '{\"label\":\"\\u0639\\u0646\\u0648\\u0627\\u0646 \\u0627\\u0644\\u0634\\u0631\\u0643\\u0629\",\"validation\":{\"required\":0},\"type\":\"text\"}', '2021-12-25 13:06:55', '2021-12-25 13:06:55'),
(713, 45, 2, '{\"label\":\"Details\",\"validation\":{\"required\":0},\"rows\":3}', '2022-01-02 10:10:55', '2022-01-02 10:10:55'),
(714, 45, 3, '{\"label\":\"\\u062d\\u0642\\u0644 \\u0642\\u0627\\u0626\\u0645\\u0629 \\u0645\\u0646\\u0633\\u062f\\u0644\\u0629\",\"validation\":{\"required\":0},\"values\":\"\\u0648\\u0627\\u062d\\u062f, \\u0627\\u062a\\u0646\\u064a\\u0646\"}', '2022-01-02 10:10:55', '2022-01-02 10:10:55'),
(715, 45, 5, '{\"label\":\"\\u062a\\u062d\\u0645\\u064a\\u0644 \\u0645\\u0644\\u0641\",\"validation\":{\"required\":0},\"type\":\"file\"}', '2022-01-02 10:10:55', '2022-01-02 10:10:55'),
(716, 45, 5, '{\"label\":\"Upload file\",\"validation\":{\"required\":1},\"type\":\"file\"}', '2022-01-02 10:10:55', '2022-01-02 10:10:55');

-- --------------------------------------------------------

--
-- Table structure for table `builder_forms`
--

CREATE TABLE `builder_forms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `form_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `builder_forms`
--

INSERT INTO `builder_forms` (`id`, `form_name`, `created_at`, `updated_at`) VALUES
(45, 'Complete registeration - Cycle 2', '2021-10-10 22:18:01', '2022-01-02 09:11:14'),
(43, 'Complete registeration - Cycle 1', '2021-10-10 21:40:37', '2021-10-11 21:33:48'),
(46, 'استكمال المتطلبات', '2021-10-10 22:23:13', '2021-10-10 22:23:13'),
(49, 'Final Screening', '2021-12-05 15:10:20', '2021-12-05 15:10:20'),
(50, 'islam test cycle form', '2021-12-19 09:13:25', '2021-12-19 09:13:25'),
(51, 'New form', '2021-12-20 07:11:02', '2021-12-20 07:11:02'),
(52, 'Custmized For FinTech', '2021-12-20 08:50:04', '2021-12-20 08:50:04');

-- --------------------------------------------------------

--
-- Table structure for table `calendars`
--

CREATE TABLE `calendars` (
  `id` int(11) NOT NULL,
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
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `calendars`
--

INSERT INTO `calendars` (`id`, `calendarId`, `title`, `body`, `start`, `end`, `allDay`, `category`, `location`, `isReadOnly`, `isPrivate`, `color`, `user_id`, `created_at`, `updated_at`) VALUES
(1000102, 1, 'Demo 2nd calendar', 'Demo 2nd calendar Demo 2nd calendar Demo 2nd calendar', '2021-10-19 04:00:00', '2021-10-19 07:30:00', 1, NULL, NULL, 0, NULL, '#4caf50', 1, '2021-09-29 21:57:46', '2021-10-29 18:10:11'),
(1000103, 1, 'Demo 3rd calendar', 'Demo 3rd calendar Demo 3 calendar Demo 3 calendar', '2021-10-08 00:00:00', '2021-10-08 00:00:00', 1, NULL, NULL, 0, 1, '#7300ff', 1, '2021-09-29 21:57:46', '2021-10-29 16:19:29'),
(1000105, 1, '6 October Holiday', 'Holiday', '2021-10-07 02:00:00', '2021-10-07 02:00:00', NULL, NULL, NULL, 0, NULL, '#ff2929', 1, '2021-10-01 20:34:45', '2021-11-01 20:13:32'),
(1000156, 1, 'Medo', 'demo', '2021-09-30 04:00:00', '2021-09-30 05:00:00', 1, NULL, NULL, 0, NULL, '#238d1c', 1, '2021-10-29 14:31:48', '2021-10-29 18:10:11'),
(1000155, 1, 'October 2', 'demo', '2021-10-02 00:00:00', '2021-10-03 00:00:00', 1, NULL, NULL, 0, 1, '#21b06d', 1, '2021-10-29 11:37:18', '2021-10-29 16:19:01'),
(1000158, 1, 'Ibrahim Private event', 'medo', '2021-10-01 00:00:00', '2021-10-06 00:00:00', 1, NULL, NULL, 0, 1, '#5b3aa6', 1, '2021-10-29 15:07:11', '2021-10-29 16:15:28'),
(1000149, 1, 'Try 11 November', 'Nov details', '2021-10-11 06:00:00', '2021-10-11 10:00:00', NULL, NULL, NULL, 0, 1, '#0f61e6', 1, '2021-10-07 17:38:06', '2021-10-29 16:15:53'),
(1000159, 1, 'demo for all', 'demo', '2021-09-28 00:00:00', '2021-10-01 00:00:00', 1, NULL, NULL, 0, NULL, '#000000', 1, '2021-10-29 15:12:47', '2021-10-29 18:08:51'),
(1000162, 1, 'Demo 26-27 test Private by mido', 'demo', '2021-09-26 00:00:00', '2021-09-28 00:00:00', 1, NULL, NULL, 0, 1, '#1b7973', 86, '2021-10-29 16:07:14', '2021-10-29 16:33:02'),
(1000163, 1, 'Private 2 Oct', NULL, '2021-10-01 00:00:00', '2021-10-02 00:00:00', 1, NULL, NULL, NULL, 1, '#3a87ad', 86, '2021-10-29 16:26:26', '2021-10-29 18:28:32'),
(1000164, 1, '11-13 Private', 'private', '2021-10-13 00:00:00', '2021-10-16 00:00:00', 1, NULL, NULL, NULL, 1, '#3a87ad', 86, '2021-10-29 16:29:07', '2021-11-11 20:05:46'),
(1000171, 1, 'Free from 16 to 18', 'free', '2021-11-16 09:00:00', '2021-11-19 17:00:00', NULL, NULL, NULL, NULL, 1, '#a439ac', 87, '2021-11-07 21:17:05', '2021-11-07 21:17:45'),
(1000172, 1, 'demo', 'demo', '2021-11-16 00:00:00', '2021-11-19 00:00:00', 1, NULL, NULL, NULL, 1, '#344f03', 89, '2021-11-08 11:01:53', '2021-11-08 11:08:24'),
(1000173, 1, 'demo', 'save', '2021-11-16 00:00:00', '2021-11-19 00:00:00', 1, NULL, NULL, NULL, NULL, '#1512ba', 1, '2021-11-08 11:06:41', '2021-11-10 10:03:08'),
(1000174, 1, 'today', 'today', '2021-11-25 00:00:00', '2021-11-27 00:00:00', 1, NULL, NULL, NULL, 1, '#1b9d1d', 1, '2021-11-11 08:41:58', '2021-11-25 00:34:55'),
(1000175, 1, 'ok', 'ok', '2021-11-01 00:00:00', '2021-11-06 00:00:00', 1, NULL, NULL, NULL, 1, '#ac3939', 1, '2021-11-25 20:41:11', '2021-11-25 20:41:43'),
(1000176, 1, 'Attend online meeting', NULL, '2021-12-06 00:00:00', '2021-12-08 00:00:00', 1, NULL, NULL, NULL, 1, '#3a87ad', 92, '2021-12-04 07:28:52', '2021-12-04 07:28:52'),
(1000177, 1, 'Online meeting with all startups', 'Important for everyone', '2021-12-13 00:00:00', '2021-12-17 00:00:00', 1, NULL, NULL, NULL, NULL, '#6a25a2', 1, '2021-12-04 07:44:39', '2021-12-21 05:08:03'),
(1000178, 1, 'Attend the party', NULL, '2021-12-29 00:00:00', '2022-01-01 00:00:00', 1, NULL, NULL, NULL, 1, '#3a87ad', 92, '2021-12-04 08:07:11', '2021-12-04 08:07:11'),
(1000179, 1, 'My private party', 'it\'s private', '2021-12-17 00:00:00', '2021-12-18 00:00:00', 1, NULL, NULL, NULL, 1, '#3a87ad', 92, '2021-12-04 08:07:31', '2021-12-04 08:09:13'),
(1000180, 1, '! 1 January', 'nothing', '2022-01-01 00:00:00', '2022-01-01 00:00:00', 1, NULL, NULL, NULL, 1, '#3a87ad', 92, '2021-12-04 08:08:03', '2021-12-04 08:08:45'),
(1000181, 1, 'My personal 8 days', 'Personal', '2021-12-22 00:00:00', '2021-12-30 00:00:00', 1, NULL, NULL, NULL, 1, '#a21ea4', 2, '2021-12-04 09:28:50', '2021-12-21 09:58:55'),
(1000182, 1, '1 Hour meeting', '1', '2021-12-08 09:00:00', '2021-12-09 10:00:00', NULL, NULL, NULL, NULL, 1, '#356b1e', 2, '2021-12-05 15:25:43', '2021-12-21 16:42:06'),
(1000183, 1, 'Meeting', 'new meeting', '2021-11-29 00:00:00', '2021-12-03 00:00:00', 1, NULL, NULL, NULL, NULL, '#3a87ad', 1, '2021-12-19 09:45:02', '2021-12-19 09:45:49'),
(1000184, 1, 'demo', NULL, '2021-12-07 00:00:00', '2021-12-09 00:00:00', 1, NULL, NULL, NULL, 1, '#3a87ad', 1, '2021-12-20 10:28:35', '2021-12-21 16:41:31'),
(1000185, 1, 'My Personal Meeting', NULL, '2021-12-27 00:00:00', '2021-12-29 00:00:00', 1, NULL, NULL, NULL, 1, '#3a87ad', 99, '2021-12-22 10:25:47', '2021-12-22 10:25:47'),
(1000186, 1, 'مؤتمر رواد الأعمال', 'كافة تفاصيل المؤتمر هنا', '2021-12-28 00:00:00', '2021-12-31 00:00:00', 1, NULL, NULL, NULL, 1, '#3a87ad', 101, '2021-12-22 10:32:34', '2021-12-24 17:22:45'),
(1000188, 1, 'مناسبة تجريبية', 'تفاصيل المناسبة التجريبية', '2021-12-27 00:00:00', '2022-01-04 00:00:00', 1, NULL, NULL, NULL, 1, '#3a87ad', 104, '2021-12-27 06:34:11', '2021-12-27 07:12:50'),
(1000189, 1, 'Private', NULL, '2022-01-17 00:00:00', '2022-01-19 00:00:00', 1, NULL, NULL, NULL, 1, '#3a87ad', 100, '2022-01-04 10:05:11', '2022-01-04 10:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `state_id` bigint(20) UNSIGNED NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `state_id`, `name_en`, `name_ar`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cairo', 'القاهره', NULL, NULL),
(2, 2, 'Giza', 'الجيزة', NULL, NULL),
(3, 2, 'Sixth of October', 'السادس من أكتوبر', NULL, NULL),
(4, 2, 'Sheikh Zayed', 'الشيخ زايد', NULL, NULL),
(5, 2, 'Hawamdiyah', 'الحوامدية', NULL, NULL),
(6, 2, 'Al Badrasheen', 'البدرشين', NULL, NULL),
(7, 2, 'Saf', 'الصف', NULL, NULL),
(8, 2, 'Atfih', 'أطفيح', NULL, NULL),
(9, 2, 'Al Ayat', 'العياط', NULL, NULL),
(10, 2, 'Al-Bawaiti', 'الباويطي', NULL, NULL),
(11, 2, 'ManshiyetAl Qanater', 'منشأة القناطر', NULL, NULL),
(12, 2, 'Oaseem', 'أوسيم', NULL, NULL),
(13, 2, 'Kerdasa', 'كرداسة', NULL, NULL),
(14, 2, 'Abu Nomros', 'أبو النمرس', NULL, NULL),
(15, 2, 'Kafr Ghati', 'كفر غطاطي', NULL, NULL),
(16, 2, 'Manshiyet Al Bakari', 'منشأة البكاري', NULL, NULL),
(17, 3, 'Alexandria', 'الأسكندرية', NULL, NULL),
(18, 3, 'Burj Al Arab', 'برج العرب', NULL, NULL),
(19, 3, 'New Burj Al Arab', 'برج العرب الجديدة', NULL, NULL),
(20, 12, 'Banha', 'بنها', NULL, NULL),
(21, 12, 'Qalyub', 'قليوب', NULL, NULL),
(22, 12, 'Shubra Al Khaimah', 'شبرا الخيمة', NULL, NULL),
(23, 12, 'Al Qanater Charity', 'القناطر الخيرية', NULL, NULL),
(24, 12, 'Khanka', 'الخانكة', NULL, NULL),
(25, 12, 'Kafr Shukr', 'كفر شكر', NULL, NULL),
(26, 12, 'Tukh', 'طوخ', NULL, NULL),
(27, 12, 'Qaha', 'قها', NULL, NULL),
(28, 12, 'Obour', 'العبور', NULL, NULL),
(29, 12, 'Khosous', 'الخصوص', NULL, NULL),
(30, 12, 'Shibin Al Qanater', 'شبين القناطر', NULL, NULL),
(31, 6, 'Damanhour', 'دمنهور', NULL, NULL),
(32, 6, 'Kafr El Dawar', 'كفر الدوار', NULL, NULL),
(33, 6, 'Rashid', 'رشيد', NULL, NULL),
(34, 6, 'Edco', 'إدكو', NULL, NULL),
(35, 6, 'Abu al-Matamir', 'أبو المطامير', NULL, NULL),
(36, 6, 'Abu Homs', 'أبو حمص', NULL, NULL),
(37, 6, 'Delengat', 'الدلنجات', NULL, NULL),
(38, 6, 'Mahmoudiyah', 'المحمودية', NULL, NULL),
(39, 6, 'Rahmaniyah', 'الرحمانية', NULL, NULL),
(40, 6, 'Itai Baroud', 'إيتاي البارود', NULL, NULL),
(41, 6, 'Housh Eissa', 'حوش عيسى', NULL, NULL),
(42, 6, 'Shubrakhit', 'شبراخيت', NULL, NULL),
(43, 6, 'Kom Hamada', 'كوم حمادة', NULL, NULL),
(44, 6, 'Badr', 'بدر', NULL, NULL),
(45, 6, 'Wadi Natrun', 'وادي النطرون', NULL, NULL),
(46, 6, 'New Nubaria', 'النوبارية الجديدة', NULL, NULL),
(47, 23, 'Marsa Matrouh', 'مرسى مطروح', NULL, NULL),
(48, 23, 'El Hamam', 'الحمام', NULL, NULL),
(49, 23, 'Alamein', 'العلمين', NULL, NULL),
(50, 23, 'Dabaa', 'الضبعة', NULL, NULL),
(51, 23, 'Al-Nagila', 'النجيلة', NULL, NULL),
(52, 23, 'Sidi Brani', 'سيدي براني', NULL, NULL),
(53, 23, 'Salloum', 'السلوم', NULL, NULL),
(54, 23, 'Siwa', 'سيوة', NULL, NULL),
(55, 19, 'Damietta', 'دمياط', NULL, NULL),
(56, 19, 'New Damietta', 'دمياط الجديدة', NULL, NULL),
(57, 19, 'Ras El Bar', 'رأس البر', NULL, NULL),
(58, 19, 'Faraskour', 'فارسكور', NULL, NULL),
(59, 19, 'Zarqa', 'الزرقا', NULL, NULL),
(60, 19, 'alsaru', 'السرو', NULL, NULL),
(61, 19, 'alruwda', 'الروضة', NULL, NULL),
(62, 19, 'Kafr El-Batikh', 'كفر البطيخ', NULL, NULL),
(63, 19, 'Azbet Al Burg', 'عزبة البرج', NULL, NULL),
(64, 19, 'Meet Abou Ghalib', 'ميت أبو غالب', NULL, NULL),
(65, 19, 'Kafr Saad', 'كفر سعد', NULL, NULL),
(66, 4, 'Mansoura', 'المنصورة', NULL, NULL),
(67, 4, 'Talkha', 'طلخا', NULL, NULL),
(68, 4, 'Mitt Ghamr', 'ميت غمر', NULL, NULL),
(69, 4, 'Dekernes', 'دكرنس', NULL, NULL),
(70, 4, 'Aga', 'أجا', NULL, NULL),
(71, 4, 'Menia El Nasr', 'منية النصر', NULL, NULL),
(72, 4, 'Sinbillawin', 'السنبلاوين', NULL, NULL),
(73, 4, 'El Kurdi', 'الكردي', NULL, NULL),
(74, 4, 'Bani Ubaid', 'بني عبيد', NULL, NULL),
(75, 4, 'Al Manzala', 'المنزلة', NULL, NULL),
(76, 4, 'tami al\'amdid', 'تمي الأمديد', NULL, NULL),
(77, 4, 'aljamalia', 'الجمالية', NULL, NULL),
(78, 4, 'Sherbin', 'شربين', NULL, NULL),
(79, 4, 'Mataria', 'المطرية', NULL, NULL),
(80, 4, 'Belqas', 'بلقاس', NULL, NULL),
(81, 4, 'Meet Salsil', 'ميت سلسيل', NULL, NULL),
(82, 4, 'Gamasa', 'جمصة', NULL, NULL),
(83, 4, 'Mahalat Damana', 'محلة دمنة', NULL, NULL),
(84, 4, 'Nabroh', 'نبروه', NULL, NULL),
(85, 22, 'Kafr El Sheikh', 'كفر الشيخ', NULL, NULL),
(86, 22, 'Desouq', 'دسوق', NULL, NULL),
(87, 22, 'Fooh', 'فوه', NULL, NULL),
(88, 22, 'Metobas', 'مطوبس', NULL, NULL),
(89, 22, 'Burg Al Burullus', 'برج البرلس', NULL, NULL),
(90, 22, 'Baltim', 'بلطيم', NULL, NULL),
(91, 22, 'Masief Baltim', 'مصيف بلطيم', NULL, NULL),
(92, 22, 'Hamol', 'الحامول', NULL, NULL),
(93, 22, 'Bella', 'بيلا', NULL, NULL),
(94, 22, 'Riyadh', 'الرياض', NULL, NULL),
(95, 22, 'Sidi Salm', 'سيدي سالم', NULL, NULL),
(96, 22, 'Qellen', 'قلين', NULL, NULL),
(97, 22, 'Sidi Ghazi', 'سيدي غازي', NULL, NULL),
(98, 8, 'Tanta', 'طنطا', NULL, NULL),
(99, 8, 'Al Mahalla Al Kobra', 'المحلة الكبرى', NULL, NULL),
(100, 8, 'Kafr El Zayat', 'كفر الزيات', NULL, NULL),
(101, 8, 'Zefta', 'زفتى', NULL, NULL),
(102, 8, 'El Santa', 'السنطة', NULL, NULL),
(103, 8, 'Qutour', 'قطور', NULL, NULL),
(104, 8, 'Basion', 'بسيون', NULL, NULL),
(105, 8, 'Samannoud', 'سمنود', NULL, NULL),
(106, 10, 'Shbeen El Koom', 'شبين الكوم', NULL, NULL),
(107, 10, 'Sadat City', 'مدينة السادات', NULL, NULL),
(108, 10, 'Menouf', 'منوف', NULL, NULL),
(109, 10, 'Sars El-Layan', 'سرس الليان', NULL, NULL),
(110, 10, 'Ashmon', 'أشمون', NULL, NULL),
(111, 10, 'Al Bagor', 'الباجور', NULL, NULL),
(112, 10, 'Quesna', 'قويسنا', NULL, NULL),
(113, 10, 'Berkat El Saba', 'بركة السبع', NULL, NULL),
(114, 10, 'Tala', 'تلا', NULL, NULL),
(115, 10, 'Al Shohada', 'الشهداء', NULL, NULL),
(116, 20, 'Zagazig', 'الزقازيق', NULL, NULL),
(117, 20, 'Al Ashr Men Ramadan', 'العاشر من رمضان', NULL, NULL),
(118, 20, 'Minya Al Qamh', 'منيا القمح', NULL, NULL),
(119, 20, 'Belbeis', 'بلبيس', NULL, NULL),
(120, 20, 'Mashtoul El Souq', 'مشتول السوق', NULL, NULL),
(121, 20, 'Qenaiat', 'القنايات', NULL, NULL),
(122, 20, 'Abu Hammad', 'أبو حماد', NULL, NULL),
(123, 20, 'El Qurain', 'القرين', NULL, NULL),
(124, 20, 'Hehia', 'ههيا', NULL, NULL),
(125, 20, 'Abu Kabir', 'أبو كبير', NULL, NULL),
(126, 20, 'Faccus', 'فاقوس', NULL, NULL),
(127, 20, 'El Salihia El Gedida', 'الصالحية الجديدة', NULL, NULL),
(128, 20, 'Al Ibrahimiyah', 'الإبراهيمية', NULL, NULL),
(129, 20, 'Deirb Negm', 'ديرب نجم', NULL, NULL),
(130, 20, 'Kafr Saqr', 'كفر صقر', NULL, NULL),
(131, 20, 'Awlad Saqr', 'أولاد صقر', NULL, NULL),
(132, 20, 'Husseiniya', 'الحسينية', NULL, NULL),
(133, 20, 'san alhajar alqablia', 'صان الحجر القبلية', NULL, NULL),
(134, 20, 'Manshayat Abu Omar', 'منشأة أبو عمر', NULL, NULL),
(135, 18, 'PorSaid', 'بورسعيد', NULL, NULL),
(136, 18, 'PorFouad', 'بورفؤاد', NULL, NULL),
(137, 9, 'Ismailia', 'الإسماعيلية', NULL, NULL),
(138, 9, 'Fayed', 'فايد', NULL, NULL),
(139, 9, 'Qantara Sharq', 'القنطرة شرق', NULL, NULL),
(140, 9, 'Qantara Gharb', 'القنطرة غرب', NULL, NULL),
(141, 9, 'El Tal El Kabier', 'التل الكبير', NULL, NULL),
(142, 9, 'Abu Sawir', 'أبو صوير', NULL, NULL),
(143, 9, 'Kasasien El Gedida', 'القصاصين الجديدة', NULL, NULL),
(144, 14, 'Suez', 'السويس', NULL, NULL),
(145, 26, 'Arish', 'العريش', NULL, NULL),
(146, 26, 'Sheikh Zowaid', 'الشيخ زويد', NULL, NULL),
(147, 26, 'Nakhl', 'نخل', NULL, NULL),
(148, 26, 'Rafah', 'رفح', NULL, NULL),
(149, 26, 'Bir al-Abed', 'بئر العبد', NULL, NULL),
(150, 26, 'Al Hasana', 'الحسنة', NULL, NULL),
(151, 21, 'Al Toor', 'الطور', NULL, NULL),
(152, 21, 'Sharm El-Shaikh', 'شرم الشيخ', NULL, NULL),
(153, 21, 'Dahab', 'دهب', NULL, NULL),
(154, 21, 'Nuweiba', 'نويبع', NULL, NULL),
(155, 21, 'Taba', 'طابا', NULL, NULL),
(156, 21, 'Saint Catherine', 'سانت كاترين', NULL, NULL),
(157, 21, 'Abu Redis', 'أبو رديس', NULL, NULL),
(158, 21, 'Abu Zenaima', 'أبو زنيمة', NULL, NULL),
(159, 21, 'Ras Sidr', 'رأس سدر', NULL, NULL),
(160, 17, 'Bani Sweif', 'بني سويف', NULL, NULL),
(161, 17, 'Beni Suef El Gedida', 'بني سويف الجديدة', NULL, NULL),
(162, 17, 'Al Wasta', 'الواسطى', NULL, NULL),
(163, 17, 'Naser', 'ناصر', NULL, NULL),
(164, 17, 'Ehnasia', 'إهناسيا', NULL, NULL),
(165, 17, 'beba', 'ببا', NULL, NULL),
(166, 17, 'Fashn', 'الفشن', NULL, NULL),
(167, 17, 'Somasta', 'سمسطا', NULL, NULL),
(168, 7, 'Fayoum', 'الفيوم', NULL, NULL),
(169, 7, 'Fayoum El Gedida', 'الفيوم الجديدة', NULL, NULL),
(170, 7, 'Tamiya', 'طامية', NULL, NULL),
(171, 7, 'Snores', 'سنورس', NULL, NULL),
(172, 7, 'Etsa', 'إطسا', NULL, NULL),
(173, 7, 'Epschway', 'إبشواي', NULL, NULL),
(174, 7, 'Yusuf El Sediaq', 'يوسف الصديق', NULL, NULL),
(175, 11, 'Minya', 'المنيا', NULL, NULL),
(176, 11, 'Minya El Gedida', 'المنيا الجديدة', NULL, NULL),
(177, 11, 'El Adwa', 'العدوة', NULL, NULL),
(178, 11, 'Magagha', 'مغاغة', NULL, NULL),
(179, 11, 'Bani Mazar', 'بني مزار', NULL, NULL),
(180, 11, 'Mattay', 'مطاي', NULL, NULL),
(181, 11, 'Samalut', 'سمالوط', NULL, NULL),
(182, 11, 'Madinat El Fekria', 'المدينة الفكرية', NULL, NULL),
(183, 11, 'Meloy', 'ملوي', NULL, NULL),
(184, 11, 'Deir Mawas', 'دير مواس', NULL, NULL),
(185, 16, 'Assiut', 'أسيوط', NULL, NULL),
(186, 16, 'Assiut El Gedida', 'أسيوط الجديدة', NULL, NULL),
(187, 16, 'Dayrout', 'ديروط', NULL, NULL),
(188, 16, 'Manfalut', 'منفلوط', NULL, NULL),
(189, 16, 'Qusiya', 'القوصية', NULL, NULL),
(190, 16, 'Abnoub', 'أبنوب', NULL, NULL),
(191, 16, 'Abu Tig', 'أبو تيج', NULL, NULL),
(192, 16, 'El Ghanaim', 'الغنايم', NULL, NULL),
(193, 16, 'Sahel Selim', 'ساحل سليم', NULL, NULL),
(194, 16, 'El Badari', 'البداري', NULL, NULL),
(195, 16, 'Sidfa', 'صدفا', NULL, NULL),
(196, 13, 'El Kharga', 'الخارجة', NULL, NULL),
(197, 13, 'Paris', 'باريس', NULL, NULL),
(198, 13, 'Mout', 'موط', NULL, NULL),
(199, 13, 'Farafra', 'الفرافرة', NULL, NULL),
(200, 13, 'Balat', 'بلاط', NULL, NULL),
(201, 5, 'Hurghada', 'الغردقة', NULL, NULL),
(202, 5, 'Ras Ghareb', 'رأس غارب', NULL, NULL),
(203, 5, 'Safaga', 'سفاجا', NULL, NULL),
(204, 5, 'El Qusiar', 'القصير', NULL, NULL),
(205, 5, 'Marsa Alam', 'مرسى علم', NULL, NULL),
(206, 5, 'Shalatin', 'الشلاتين', NULL, NULL),
(207, 5, 'Halaib', 'حلايب', NULL, NULL),
(208, 27, 'Sohag', 'سوهاج', NULL, NULL),
(209, 27, 'Sohag El Gedida', 'سوهاج الجديدة', NULL, NULL),
(210, 27, 'Akhmeem', 'أخميم', NULL, NULL),
(211, 27, 'Akhmim El Gedida', 'أخميم الجديدة', NULL, NULL),
(212, 27, 'Albalina', 'البلينا', NULL, NULL),
(213, 27, 'El Maragha', 'المراغة', NULL, NULL),
(214, 27, 'almunsha\'a', 'المنشأة', NULL, NULL),
(215, 27, 'Dar AISalaam', 'دار السلام', NULL, NULL),
(216, 27, 'Gerga', 'جرجا', NULL, NULL),
(217, 27, 'Jahina Al Gharbia', 'جهينة الغربية', NULL, NULL),
(218, 27, 'Saqilatuh', 'ساقلته', NULL, NULL),
(219, 27, 'Tama', 'طما', NULL, NULL),
(220, 27, 'Tahta', 'طهطا', NULL, NULL),
(221, 25, 'Qena', 'قنا', NULL, NULL),
(222, 25, 'New Qena', 'قنا الجديدة', NULL, NULL),
(223, 25, 'Abu Tesht', 'أبو تشت', NULL, NULL),
(224, 25, 'Nag Hammadi', 'نجع حمادي', NULL, NULL),
(225, 25, 'Deshna', 'دشنا', NULL, NULL),
(226, 25, 'Alwaqf', 'الوقف', NULL, NULL),
(227, 25, 'Qaft', 'قفط', NULL, NULL),
(228, 25, 'Naqada', 'نقادة', NULL, NULL),
(229, 25, 'Farshout', 'فرشوط', NULL, NULL),
(230, 25, 'Quos', 'قوص', NULL, NULL),
(231, 24, 'Luxor', 'الأقصر', NULL, NULL),
(232, 24, 'New Luxor', 'الأقصر الجديدة', NULL, NULL),
(233, 24, 'Esna', 'إسنا', NULL, NULL),
(234, 24, 'New Tiba', 'طيبة الجديدة', NULL, NULL),
(235, 24, 'Al ziynia', 'الزينية', NULL, NULL),
(236, 24, 'Al Bayadieh', 'البياضية', NULL, NULL),
(237, 24, 'Al Qarna', 'القرنة', NULL, NULL),
(238, 24, 'Armant', 'أرمنت', NULL, NULL),
(239, 24, 'Al Tud', 'الطود', NULL, NULL),
(240, 15, 'Aswan', 'أسوان', NULL, NULL),
(241, 15, 'Aswan El Gedida', 'أسوان الجديدة', NULL, NULL),
(242, 15, 'Drau', 'دراو', NULL, NULL),
(243, 15, 'Kom Ombo', 'كوم أمبو', NULL, NULL),
(244, 15, 'Nasr Al Nuba', 'نصر النوبة', NULL, NULL),
(245, 15, 'Kalabsha', 'كلابشة', NULL, NULL),
(246, 15, 'Edfu', 'إدفو', NULL, NULL),
(247, 15, 'Al-Radisiyah', 'الرديسية', NULL, NULL),
(248, 15, 'Al Basilia', 'البصيلية', NULL, NULL),
(249, 15, 'Al Sibaeia', 'السباعية', NULL, NULL),
(250, 15, 'Abo Simbl Al Siyahia', 'ابوسمبل السياحية', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `city_id` bigint(20) UNSIGNED NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sector` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `est_date` date DEFAULT NULL,
  `idea` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `problem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `solution` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `team_num` int(11) DEFAULT NULL,
  `stage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raised_fund` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `investors` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `founder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employees` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fax` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `website` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_map` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cycle` int(11) DEFAULT NULL,
  `step` int(11) NOT NULL DEFAULT 0,
  `approved` int(1) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `state_id`, `city_id`, `name_en`, `name_ar`, `sector`, `address_en`, `address_ar`, `est_date`, `idea`, `problem`, `solution`, `team_num`, `stage`, `raised_fund`, `investors`, `founder`, `employees`, `phone`, `fax`, `email`, `website`, `facebook`, `twitter`, `linkedin`, `youtube`, `google_map`, `logo`, `cycle`, `step`, `approved`, `approved_by`, `created_at`, `updated_at`) VALUES
(3, 1, 1, 'GoOne', 'جو وان', 'Industrial', 'Cairo', 'القاهرة', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0101010011', NULL, 'ad@adadad.com', NULL, 'https://www.facebook.com/', 'https://www.twitter.com/', 'https://www.linkedin.com/', 'https://www.youtube.com/', '                                        <iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d23233.091420866243!2d31.22638447970126!3d30.047766426502825!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14583fa60b21beeb%3A0x79dfb296e8423bba!2sCairo%2C%20Cairo%20Governorate!5e0!3m2!1sen!2seg!4v1582132855858!5m2!1sen!2seg\" style=\'width: 100%;height: 100%;\' frameborder=\"0\" style=\"border:0;\" allowfullscreen=\"\"></iframe>', NULL, NULL, 0, NULL, NULL, '2020-02-16 01:44:08', '2021-12-21 12:49:39'),
(4, 1, 1, 'SEED', 'سييد', 'Industrial', 'Test Address', 'عنوان تجريبى', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '+201234567890', NULL, 'trader.egypt@gmail.com', 'www.seeeeeeeed.com', 'https://www.facebook.com/', 'https://www.twitter.com/', 'https://www.linkedin.com/', 'https://www.youtube.com/', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d23233.091420866243!2d31.22638447970126!3d30.047766426502825!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14583fa60b21beeb%3A0x79dfb296e8423bba!2sCairo%2C%20Cairo%20Governorate!5e0!3m2!1sen!2seg!4v1582132855858!5m2!1sen!2seg\" style=\'width: 100%;height: 100%;\' frameborder=\"0\" style=\"border:0;\" allowfullscreen=\"\"></iframe>', 'users/CugfAqHg0KkwROuq8xfcRJextQl6loVz5hWZenE6.png', NULL, 0, NULL, NULL, '2020-02-16 01:45:50', '2021-12-21 12:49:39'),
(6, 1, 1, 'IbrCo', 'ابراهيم', 'Industrial', 'sss', 'sss', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '6456156556', NULL, 'ibra@ibra.com', NULL, 'https://www.facebook.com/', 'https://www.twitter.com/', 'https://www.linkedin.com/', 'https://www.youtube.com/', '                                        <iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d23233.091420866243!2d31.22638447970126!3d30.047766426502825!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14583fa60b21beeb%3A0x79dfb296e8423bba!2sCairo%2C%20Cairo%20Governorate!5e0!3m2!1sen!2seg!4v1582132855858!5m2!1sen!2seg\" style=\'width: 100%;height: 100%;\' frameborder=\"0\" style=\"border:0;\" allowfullscreen=\"\"></iframe>', NULL, NULL, 0, NULL, NULL, '2020-02-16 02:44:35', '2021-12-21 12:49:39'),
(8, 2, 3, 'Golden', 'جولدن', 'Industrial', 'Elhay Elsades', 'الحي السادس', '2015-01-03', 'My idea will be here', 'the problem is no problem ... and this text is demo, also the problem is no problem ... and this text is demo, and the problem is no problem ... and this text is demo, finally the problem is no problem ... and this text is demo', 'Help us to find the solution', 3, 'Just started', '5000', 'No investors', 'Medo', NULL, '1548456', '00202020202', 'info@golden.com', 'http://golden.eg', 'https://www.facebook.com/', 'https://www.twitter.com/', 'https://www.linkedin.com/', 'https://www.youtube.com/', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d23233.091420866243!2d31.22638447970126!3d30.047766426502825!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14583fa60b21beeb%3A0x79dfb296e8423bba!2sCairo%2C%20Cairo%20Governorate!5e0!3m2!1sen!2seg!4v1582132855858!5m2!1sen!2seg\" style=\'width: 100%;height: 100%;\' frameborder=\"0\" style=\"border:0;\" allowfullscreen=\"\"></iframe>', 'users/xxyW4d8AVe4SLxI1To6GtVfFSeZoThUUji8PTiMl.jpg', 18, 99, 1, 1, '2020-02-18 00:02:19', '2021-12-25 07:25:06'),
(10, 2, 15, 'Elwadi', 'الوادي', 'Industrial', 'Giza', 'Giza', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '23232323', NULL, 'info@goldednsa.com', NULL, 'https://www.facebook.com/', 'https://www.twitter.com/', 'https://www.linkedin.com/', 'https://www.youtube.com/', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d23233.091420866243!2d31.22638447970126!3d30.047766426502825!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14583fa60b21beeb%3A0x79dfb296e8423bba!2sCairo%2C%20Cairo%20Governorate!5e0!3m2!1sen!2seg!4v1582132855858!5m2!1sen!2seg\" style=\'width: 100%;height: 100%;\' frameborder=\"0\" style=\"border:0;\" allowfullscreen=\"\"></iframe>', NULL, NULL, 0, NULL, NULL, '2020-02-18 00:37:46', '2021-12-21 12:49:39'),
(11, 1, 1, 'Mohamed', 'محمد', 'Industrial', 'صثصضثصض', 'صضثصضثصض', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '12312332121', NULL, 'mtawfik007@gmail.com', NULL, 'https://www.facebook.com/', 'https://www.twitter.com/', 'https://www.linkedin.com/', 'https://www.youtube.com/', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d23233.091420866243!2d31.22638447970126!3d30.047766426502825!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14583fa60b21beeb%3A0x79dfb296e8423bba!2sCairo%2C%20Cairo%20Governorate!5e0!3m2!1sen!2seg!4v1582132855858!5m2!1sen!2seg\" style=\'width: 100%;height: 100%;\' frameborder=\"0\" style=\"border:0;\" allowfullscreen=\"\"></iframe>', NULL, NULL, 0, NULL, NULL, '2020-02-18 15:01:39', '2021-12-21 12:49:39'),
(12, 20, 133, 'شركة تيستو', 'Testo Company', 'Industrial', 'الشرقية، الزقازيق', 'Sharqia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '23424124123', NULL, 'info@sherka.com', NULL, 'http://face.com', 'http//twiiiiiit.com', NULL, NULL, NULL, 'users/logo.jpg', NULL, 0, NULL, NULL, '2020-03-06 22:18:36', '2021-12-21 12:49:39'),
(14, 13, 196, 'Matrix', 'ماتريكس', 'Industrial', 'يثةخ', 'demo234334', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '4352', NULL, 'INFO@MATRIX.NET', NULL, NULL, NULL, NULL, NULL, NULL, 'users/logo.jpg', NULL, 0, NULL, NULL, '2021-11-07 14:48:11', '2021-12-21 12:49:39'),
(16, 21, 153, 'theCircle', 'سيركل', 'Industrial', '321 Hod elders', '321 Hod elders', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '13123213', NULL, 'nonz2@tree.com', NULL, NULL, NULL, NULL, NULL, NULL, 'users/logo.jpg', NULL, 0, 1, 1, '2021-11-07 15:40:00', '2021-12-21 12:49:39'),
(17, 6, 34, 'demo', 'demo', 'Industrial', 'demo', 'demo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '123123', NULL, 'demo@demo.com', NULL, NULL, NULL, NULL, NULL, NULL, 'users/logo.jpg', NULL, 0, 1, 1, '2021-11-08 10:44:27', '2021-12-21 12:49:39'),
(18, 4, 69, 'demo', 'demo', 'Industrial', 'demo', 'demo', '2001-11-23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'demo', 'de,p', 'lkmlml@demo.com', NULL, NULL, NULL, NULL, NULL, NULL, 'users/logo.jpg', NULL, 0, NULL, NULL, '2021-11-11 07:58:33', '2021-12-21 12:49:39'),
(19, 3, 19, 'Th3Contract', 'العقد', 'Industrial', 'Borg El3arab', 'برج العرب', '2019-06-10', 'no idea', 'no thing', 'the solution', 10, 'just try to start', '5000000', 'no one', 'Lamar fondation', NULL, '01235456545', '2345456879', 'info@th3contract.com', 'the3contract.com', 'fb.com', 'tw.com', 'lin.com', 'yout.com', NULL, 'users/ZdVyCZ7Z84LEPmY8mrIie15yrama2IkPt4WjqsM9.jpg', NULL, 0, NULL, NULL, '2021-12-04 06:23:56', '2021-12-21 12:49:39'),
(20, 2, 3, 'Trader', 'تريد', 'Industrial', 'تلباب', 'ىرىر', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '545646', '654654', 'trad@no.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 18, 99, 1, 1, '2021-12-20 07:31:46', '2021-12-25 08:34:49'),
(21, 1, 1, 'Macro', 'ماكرو', 'Technology', 'New Cairo', 'القاهرة الجديدة', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '654654656', NULL, 'new@macro-121.com', 'macro-121.com', 'facebook.com', NULL, NULL, 'youtube.com', NULL, 'users/2L5g1cfchin2RlJe8DVXcSqqraH7byg2Mhj0Qc3Y.jpg', 18, 86, 1, 1, '2021-12-22 10:49:09', '2022-01-01 18:35:40'),
(22, 2, 3, 'Elmarkeb', 'المركب', 'Agriculture', '6 October', '6 أكتوبر', '2021-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '564654654654', NULL, 'a@elmarkeb.com', NULL, NULL, NULL, NULL, NULL, NULL, 'users/6CnyKT6JLD6sDKWlOSnJ1dXMfPTNCaeDrl0ytQ4E.jpg', 18, 86, 1, 1, '2021-12-27 07:58:52', '2021-12-27 10:27:53');

-- --------------------------------------------------------

--
-- Table structure for table `cycles`
--

CREATE TABLE `cycles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `incubator_id` int(11) NOT NULL DEFAULT 1,
  `title` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `private` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cycles`
--

INSERT INTO `cycles` (`id`, `incubator_id`, `title`, `description`, `start`, `end`, `private`, `created_at`, `updated_at`) VALUES
(18, 1, 'December Cycle', NULL, '2021-12-01', '2021-12-31', NULL, '2021-12-08 12:07:04', '2021-12-08 12:07:04'),
(19, 1, 'نسخة عربية للتجربة', 'وصف باللغة العربية', '2022-01-01', '2022-01-31', NULL, '2021-12-10 08:07:18', '2021-12-11 15:33:07'),
(20, 1, 'Islam Test', 'test cycle', '2022-01-01', '2022-04-30', 'dd26c11ec478bd20377f51cfbceb48e86b0f2baf', '2021-12-19 10:12:34', '2021-12-20 19:20:06'),
(21, 1, 'دورة تجريبية', 'وصف مختصر', '2021-12-20', '2021-12-31', 'ab2607d98d1bf3bad61fca1ddb52e8de6d3af3df', '2021-12-20 11:42:58', '2021-12-20 19:30:31'),
(23, 1, 'ا. حامد', 'اى حاجدة', '2022-01-01', '2022-04-28', NULL, '2021-12-28 13:27:33', '2021-12-28 13:27:33');

-- --------------------------------------------------------

--
-- Table structure for table `cycles_mentors`
--

CREATE TABLE `cycles_mentors` (
  `id` int(11) NOT NULL,
  `cycle_id` bigint(20) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cycles_mentors`
--

INSERT INTO `cycles_mentors` (`id`, `cycle_id`, `user_id`, `created_at`, `updated_at`) VALUES
(17, 16, 13, '2021-12-03 13:51:07', '2021-12-03 13:51:07'),
(16, 16, 46, '2021-12-03 13:51:07', '2021-12-03 13:51:07'),
(15, 16, 45, '2021-12-03 13:51:07', '2021-12-03 13:51:07'),
(18, 16, 44, '2021-12-03 13:51:07', '2021-12-03 13:51:07'),
(28, 1, 44, '2021-12-04 16:21:58', '2021-12-04 16:21:58'),
(27, 1, 13, '2021-12-04 16:21:58', '2021-12-04 16:21:58'),
(26, 1, 46, '2021-12-04 16:21:58', '2021-12-04 16:21:58'),
(39, 18, 44, '2021-12-22 12:31:28', '2021-12-22 12:31:28'),
(38, 18, 99, '2021-12-22 12:31:28', '2021-12-22 12:31:28'),
(36, 20, 46, '2021-12-20 12:17:59', '2021-12-20 12:17:59'),
(37, 20, 44, '2021-12-20 12:17:59', '2021-12-20 12:17:59'),
(40, 23, 99, '2021-12-28 14:15:55', '2021-12-28 14:15:55'),
(41, 23, 46, '2021-12-28 14:15:55', '2021-12-28 14:15:55');

-- --------------------------------------------------------

--
-- Table structure for table `documentations`
--

CREATE TABLE `documentations` (
  `id` int(10) UNSIGNED NOT NULL,
  `menu_ar` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_en` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `arr` int(5) NOT NULL DEFAULT 0,
  `content_ar` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_en` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documentations`
--

INSERT INTO `documentations` (`id`, `menu_ar`, `menu_en`, `arr`, `content_ar`, `content_en`, `role`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'عن البرنامج', 'About program', 1, '                        <p class=\"card-text\">شكرا لاستخدامك تطبيق الويب - الحاضنة الإفتراضية، نسعد بتلقي جميع استفسارتكم من هنا: <a href=\"/admin/sendmessage\"> أرسل رسالتك</a>.</p>\r\n                        <p><strong>صدر بتاريخ:</strong>1 يناير 2022</p>\r\n                        <p><strong>الشركة المطورة:</strong> ماسترإنكود</strong></p>\r\n                        <p>\r\n                            <strong>بتمويل من: </strong> شركة فيمبي بدعم من الوكالة الأمريكية USAID\r\n                        </p>\r\n                        <p>\r\n                           ستجد هنا شرح تفصيلي لكل مكونات البرنامج، اختر العنصر من القائمة المقابلة لعرض المحتوى الخاص به، أو إضفط على الشعار أعلى القائمة للعودة إلى البرنامج، وللعودة إلى المساعدة مرة أخرى إضغط على علامة الإستفهام بالأعلى  <i class=\"bx bx-help-circle\"></i> ، ستجدها بكافة صفحات التطبيق.\r\n\r\n                        </p>\r\n                        <hr>\r\n                        <h5>مكونات شريط الانتقال العلوي</h5>\r\n                        <hr>\r\n                        <ul class=\"list-unstyled\">\r\n                            <li class=\"m-75\">\r\n                                <i class=\"bx bx-arrow-back\"></i>\r\n                                 مغادرة المساعدة، والعودة إلى الشاشة الرئيسية للبرنامج\r\n                            </li>\r\n                            <li class=\"m-75\">\r\n                                <i class=\"bx bx-help-circle\"></i>\r\n                                  للوصول إلى المساعدة، وتظهر بدلا من الأيقونة السابقة\r\n                            </li>\r\n                            <li class=\"m-75\">\r\n                                <i class=\"bx bx-moon\"></i>\r\n                                التبديل بين ثلاثة أوضاع للعرض: الوضع الفاتح والوضع شبه الداكن والوضع الداكن، إضغط عدة مرات لاختيار الوضع الأفضل لك\r\n                            </li>\r\n                            <li class=\"m-75\">\r\n                                <i class=\"bx bx-search\"></i>\r\n                                البحث عن عنصر داخل القائمة، غير متاح لكافة المستخدمين\r\n                            </li>\r\n                            <li class=\"m-75\">\r\n                                <i class=\"flag-icon flag-icon-us\"></i>\r\n                                التبديل بين اللغة العربية والإنجليزية\r\n                            </li>\r\n                            <li class=\"m-75\">\r\n                                <i class=\"bx bx-home\"></i>\r\n                               عرض واجهة الموقع الرئيسية\r\n                            </li>\r\n                            <li class=\"m-75\">\r\n                                <i class=\"bx bx-fullscreen\"></i>\r\n                                عرض ملء الشاشة، إضغط مرة ثانية للعودة\r\n                            </li>\r\n                            <li class=\"m-75\">\r\n                                <i class=\"ficon bx bx-bell bx-tada bx-flip-horizontal\"></i>\r\n                                عرض الاشعارات، مثل الرسائل الجديدة، ... إضغط على الاشعار لعرض محتواه\r\n                            </li>\r\n                            <li class=\"m-75\">\r\n                                <img class=\"round\" src=\"/191014/users/avatar.jpg\" alt=\"avatar\" height=\"20\" width=\"20\">\r\n                                قائمتك للوصول إلى ملفك الشخصي وبيانات شركتك أو تسجيل الخروج\r\n                            </li>\r\n                        </ul>\r\n<hr>\r\n                        <h5>متطلبات التشغيل</h5>\r\n                        <hr>\r\n<p>\r\nيعمل هذا الموقع بشكل جيد على متصفحات (ميكروسوفت Edge، جوجل كرووم، فايرفوكس) الإصدار 90 أو أعلى مع شاشة عرض بدقة (1920x1080) أو أعلى، كما يدعم التشغيل على الأجهزة المحمولة أندرويد 10+ أو IOS الإصدار 14+\r\n</p>\r\n', 'We will update it soon', 'all', 1, '2022-01-01 11:37:12', '2022-01-02 08:49:12'),
(4, 'التقويم الشخصي', 'Calendar', 5, '<p>يمكنك إدارة اجتماعاتك الخاصة عن طريق خيار (<strong><a href=\"../calendar\">التقويم</a></strong>) من القائمة، يعرض التقويم كافة الأحداث التي تشاركها الإدارة كما يمكنك إضافة اجتماعاتك أو مواعيدك عن طريق (إضافة جديد) أو الضغط مباشرة على التاريخ الذي تريده أو الضغط مع السحب لإختيار أكثر من يوم: يظهر التقويم في حسابك بالشكل التالي</p>\r\n<p><img style=\"border-style: solid;\" src=\"../../training/photos/L7fpk3bwyY2eEoibsYQfwEncXOmEfYpJ2LUoEBqN.jpg\" width=\"1328\" height=\"774\" /></p>\r\n<h3>يحتوي التقويم على القوائم التالية:</h3>\r\n<p>1- تحديد نوع عرض التقويم (يوم/ أسبوع/ شهر).</p>\r\n<p>2- (إضافة جديد) لإضافة موعد خاص جديد.</p>\r\n<p>3- (اليوم) الإنتقال إلى تاريخ اليوم عن تغيير الشهر الحالي.</p>\r\n<h3>تحرير موعد خاص:</h3>\r\n<p>يمكنك تعديل أي موعد خاص بالضغط عليه ستظهر النافذة التالية:</p>\r\n<p><img style=\"border-style: solid;\" src=\"../../training/photos/jcpDmKYo0hhSVNFumPYI8lfpwlcrZRCv94BJg9Xw.jpg\" width=\"531\" height=\"229\" /></p>\r\n<p>إضغط على (تعديل) ستظهر الشاشة التالية:<br /><img style=\"border-style: solid;\" title=\"calendar-edit.jpg\" src=\"../../training/photos/zQzPtDg65YoiuzGare5Sfzv0NUxK6ZVxtRqGG8XV.jpg\" alt=\"\" width=\"1068\" height=\"396\" /></p>\r\n<p>يمكنك تعديل كافة البيانات، بالإضافة إلى تغيير لون عرض الحدث في التقويم وجعله يظهر طوال اليوم دون النظر إلى توقيت البداية والنهاية، وأخيرا يمكنك حذف الحدث، ولا يمكن التراجع عن هذا الحذف</p>', '<p>Coming soon</p>', 'all', 1, '2022-01-01 15:54:12', '2022-01-01 19:08:14'),
(2, 'تفعيل الحساب', 'Activate account', 2, '<p>بعد تسجيل عضوية جديدة ستظهر لك الشاشة التالية</p>\r\n<p><img style=\"border-style: solid;\" src=\"/admin/training/photos/uyICzEg6dw4iZ8BHKzMGiFz6Jxhfl5G2LD5ts6gj.jpg\" width=\"1378\" height=\"340\" /></p>\r\n<p>&nbsp;</p>\r\n<p>لا يمكنك استخدام اي من القوائم بدون تفعيل حسابك، قم بالضغط على الرابط الذي تم إرساله على بريدك الإلكتروني، تأكد من الرسائل الغير مرغوبة، أو إضغط (إرسال رسالة جديدة من هنا) لتلقي رسالة جديدة.</p>', '<p>Coming soon</p>', 'all', 1, '2022-01-01 13:52:52', '2022-01-01 17:12:41'),
(3, 'استكمال بيانات الحساب', 'Complete Profile', 3, '<p>بعد تفعيل الحساب لابد من استكمال كافة بياناتك بالضغط على (<strong><a href=\"/admin/myProfile\">الحساب الشخصي</a></strong>) في القائمة، ستظهر لك البيانات التالية</p>\r\n<p><img style=\"border-style: solid;\" src=\"/admin/training/photos/BVJwBqVaE67WeB58kEMj0KraGSVvUHie5DFifxJQ.jpg\" width=\"1370\" height=\"689\" /></p>\r\n<p>في نهاية النموذج ستجد خيار (هل لديك شركة أو تعمل ضمن شركة) ، عن الإجابة بنعم سيظهر لك خيار جديد في القائمة (بيانات الشركة) لإضافة كافة بيانات شركتك:</p>\r\n<p><img style=\"border-style: solid;\" src=\"/admin/training/photos/BNtzkI4hQxyulTOVpoF9uHThfc2onEZlGiuVTl9M.jpg\" width=\"1369\" height=\"735\" /></p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>', '<p>Coming soon</p>', 'all', 1, '2022-01-01 15:07:52', '2022-01-01 17:12:06'),
(5, 'التحكم بالرسائل', 'Control messages', 6, '<p>يمكنك التواصل مع إدارة الموقع مباشرة عن طريق إختيار (<strong><a href=\"/admin/sendmessage\">رسالة جديدة</a></strong>) من قائمة رسائلي، وعند تسجيلك بأحد الدورات ستتمكن من التواصل مباشرة مع مرشدي الدورة والشركات المشتركة معك في نفس الدورة، تظهر شاشة إرسال رسالة جديدة بالشكل التالي:</p>\r\n<p><img style=\"border-style: solid;\" src=\"/admin/training/photos/89wNd91E0di7lB0V2GDRZzJev20WjRMfPIlYLAry.jpg\" width=\"906\" height=\"587\" /></p>\r\n<p>بعد تحديد مستلمي الرسالة (المديرين/ الشركات/ المرشدين) أكتب عنوان رسالتك ومحتواها ثم إضغط إرسال</p>\r\n<p>في قائمة رسائلي ستجد (الوارد) و (المرسل) للإطلاع على الرسائل التي تصلك ورسائلك المرسلة</p>', '<p>Coming soon</p>', 'all', 1, '2022-01-01 16:31:06', '2022-01-01 19:08:30'),
(6, 'الشاشة الرئيسية', 'Main dashboard', 4, '<p>بعد تفعيل حسابك ستظهر لك الشاشة الرئيسية التالية:</p>\r\n<p><img style=\"border-style: solid;\" src=\"/admin/training/photos/TCVhfeYsA66UZX4FXySfFKjmLCN96oF14HnEBRez.jpg\" width=\"842\" height=\"598\" /></p>\r\n<p>من خلال هذه الشاشة ستتمكن من الوصول إلى كافة الخدمات والخيارات الخاصة بك:</p>\r\n<ol>\r\n<li>الخدمات: تعرض عدد الخدمات التي يوفرها الموقع لك وبالضغط عليها تستعرض قائمة بتلك الخدمات.</li>\r\n<li>طلباتي: إجمالي الطلبات التي تقدمت بها للحصول على خدمات وبالضفط عليها تظهر لك طلباتك.</li>\r\n<li>المرشدين: عدد المرشدين المتاحين لمساعدتك، إضغط لعرض طلباتك.</li>\r\n<li>الرسائل الجديدة: عدد الرسائل الجديدة التي تم تطلع عليها، اصغط لعرض رسائلك الجديدة.</li>\r\n<li>آخر الخدمات: قائمة بآخر الخدمات التي تم إضافتها للموقع، يمكنك عرض الخدمة بالضغط عليها أو استعراض محتوى القسم بالضغط عليه.</li>\r\n<li>آخر الأحداث: قائمة بآخر الأحداث القادمة في التقويم الخاص بك.</li>\r\n<li>قائمة الدورات الجديدة: عرض للدورات الجارية وبياناتها للإنضمام إليها (لا يمكنك الإنضمام لأكثر من دورة).</li>\r\n</ol>\r\n<p>عند الإنضمام لدورة تتغير الشاشة الرئيسية، حيث يظهر لك مخطط لتقدمك بالدورة وإرشادات لكل مرحلة، بالإضافة إلى جدول بالمرشدين لتتمكن من طلب جلسة إرشاد، وكذلك جدول بتدريباتك المتاحة خلال الدورة</p>', '<p>comming sson</p>', 'user', 1, '2022-01-01 16:50:19', '2022-01-01 17:11:09'),
(7, 'خدمات الموقع', 'Portal services', 7, '<p>عن الضغط على (<a href=\"/admin/directory\">الخدمات</a>) في القائمة تظهر لك الشاشة التالية:</p>\r\n<p><img style=\"border-style: solid;\" src=\"/admin/training/photos/0XaN7pCrJpnwBZysr5KMgi96nIfkh6oCIe7zeE4l.jpg\" width=\"1008\" height=\"597\" /></p>\r\n<p>يمكنك هنا من البحث عن خدمة أو استعراض كافة الخدمات بقسم معين عن طريق الضغط على القسم ثم (بحث).</p>\r\n<p>بجوار كل خدمة وصف مختصر وسعر الخدمة وتقييم الخدمة من قبل كافة الشركات.</p>\r\n<h3>طلب الحصول على خدمة:</h3>\r\n<p>إضغط على مسمى الخدمة ستظهر لك النافذة التالية:</p>\r\n<p><img style=\"border-style: solid;\" src=\"/admin/training/photos/MJ5G3JW2UFco41uUi0DXspWnDoLYLNQBntd3xDfL.jpg\" width=\"409\" height=\"405\" /></p>\r\n<p>سيتم عرض كافة تفاصيل الخدمة، ولتقديم طلب خدمة حدد تاريخ ووقت طلبك للحصول على الخدمة، كما يمكنك إضافة تعليق للإدارة للاطلاع عليه مع الطلب، ثم إضغط (إرسال الطلب) أو (إغلاق) للإلغاء.</p>\r\n<p>عند الموافقة أو رفض الطلب سيصلك بريد إلكتروني يخبرك بحالة الطلب ويمكنك مراجعة كافة طلباتك من قائمة (طلباتي - الخدمات).</p>\r\n<p>بعد حصولك على الخدمة ستظهر لك نافذة في الشاشة الرئيسية لتقييم الخدمة وتعليقك عليها.</p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>', '<p>Comming soon</p>', 'user', 1, '2022-01-01 17:07:50', '2022-01-01 17:10:08'),
(8, 'الشاشة الرئيسية', 'Dashboard', 4, '<p>بعد تفعيل حسابك ستظهر لك الشاشة الرئيسية التالية:</p>\r\n<p><img style=\"border-style: solid;\" src=\"/admin/training/photos/GYOJAMOBO7U5Pvfp9kLk3IkghVaRq9q3LyOsx2VC.jpg\" width=\"1068\" height=\"725\" /></p>\r\n<p>من خلال هذه الشاشة ستتمكن من تحديد مواعيدك المناسبة لعقد جلسات الإرشاد مع الشركات حيث تحتوي:</p>\r\n<ol>\r\n<li>الخدمات: تعرض إجمالي عدد الخدمات المتاحة على الموقع.</li>\r\n<li>الرسائل الجديدة: عدد الرسائل الجديدة التي لم تطلع عليها، اصغط لعرض رسائلك الجديدة.</li>\r\n<li>الجلسات القادمة: عدد جلسات الإرشاد القادمة والتي تظهر تفصيلاً أسفل الشاشة.</li>\r\n<li>الدورات: عدد الدورات الخاصة بك.</li>\r\n<li>آخر الخدمات: آخر الخدمات المضافة للنظام.</li>\r\n<li>آخر الأحداث: مواعيدك القادمة المسجلة بالتقويم الخاص بك.</li>\r\n<li>جدول مواعيدي: عرض لمواعيدك الحالية، يمكنك إضافة جديد بالضغط على (هنا) أو علامة (+) أعلى الجدول، ستظهر لك النافذة التالية:</li>\r\n</ol>\r\n<p><img style=\"border-style: solid; display: block; margin-left: auto; margin-right: auto;\" src=\"/admin/training/photos/LhTdVBWcGGvUndvyYTEJDU66fqfjlfjLvp6eVJie.jpg\" width=\"532\" height=\"479\" /></p>\r\n<p>تتيح لك إضافة يوم واحد/ جدولة بعض أيام الأسبوع/ جدولة كافة أيام العمل/ فترة زمنية معينة</p>', '<p>Soon</p>', 'mentor', 1, '2022-01-04 09:49:08', '2022-01-04 10:30:32'),
(9, 'الشاشة الرئيسية', 'Dashboard', 4, '<p>بعد تفعيل حسابك ستظهر لك الشاشة الرئيسية التالية:</p>\r\n<p><img style=\"border-style: solid;\" src=\"/admin/training/photos/aT4IjtsebjKqyM7WP0Lw3jAFqPJTfLIIDE7UrWUQ.jpg\" width=\"1045\" height=\"881\" /></p>\r\n<p>من خلال هذه الشاشة ستتمكن من تحديد مواعيدك المناسبة لعقد جلسات الإرشاد مع الشركات حيث تحتوي:</p>\r\n<ol>\r\n<li>الخدمات: تعرض إجمالي عدد الخدمات المتاحة على الموقع.</li>\r\n<li>الرسائل الجديدة: عدد الرسائل الجديدة التي لم تطلع عليها، اصغط لعرض رسائلك الجديدة.</li>\r\n<li>التقييمات المبدأية/ النهائية: عدد التقييمات المتاحة لك حاليا وتظهر تفاصيلها أسفل هذه الشاشة.</li>\r\n<li>آخر الخدمات: آخر الخدمات المضافة للنظام.</li>\r\n<li>آخر الأحداث: مواعيدك القادمة المسجلة بالتقويم الخاص بك.</li>\r\n</ol>', '<p>Soon</p>', 'judge', 1, '2022-01-04 10:10:00', '2022-01-04 10:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(10) UNSIGNED NOT NULL,
  `title_en` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_ar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `showtimer` int(1) DEFAULT NULL,
  `timerdate` datetime DEFAULT NULL,
  `googlemap` text DEFAULT NULL,
  `published` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title_en`, `title_ar`, `image`, `body_ar`, `body_en`, `showtimer`, `timerdate`, `googlemap`, `published`, `user_id`, `created_at`, `updated_at`) VALUES
(500001, 'The first International Conference for entrepreneurs', 'المؤتمر الدولي الأول لرواد الأعمال', 'TbhZZK1MO5nktyBRKrbi2021-09-24event.jpg', '<p>تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية.</p>\r\n\r\n<p>تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول لرواد الأعمال باللغة العربية،تفاصيل&nbsp;المؤتمر الدولي الأول باللغة العربية.</p>', '<p>The first International Conference for entrepreneurs details in English,The first International Conference for entrepreneurs details in English,&nbsp;The first International Conference for entrepreneurs details in English,&nbsp;The first International Conference for entrepreneurs details in English,&nbsp;The first International Conference for entrepreneurs details in English.</p>\r\n\r\n<p>The first International Conference for entrepreneurs details in English,The first International Conference for entrepreneurs details in English,&nbsp;The first International Conference for entrepreneurs details in English,&nbsp;The first International Conference for entrepreneurs details in English,&nbsp;The first International Conference for entrepreneurs details in English.</p>\r\n\r\n<p>&nbsp;</p>', 1, '2021-12-05 09:30:00', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3453.84580648717!2d31.21963271992172!3d30.04128122576464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1458412a48ef4ea3%3A0x5b287b005f7efbcd!2z2YHZitmG2KjZig!5e0!3m2!1sar!2seg!4v1632497826291!5m2!1sar!2seg', '1', 1, '2020-02-07 22:00:00', '2021-11-29 18:44:35'),
(500004, 'Young Entrepreneur Summit', 'مؤتمر قمة رواد الأعمال الشباب', 'kMoD02021-10-05....jpg', '<p>تنطلق فعاليات مؤتمر قمة رواد الأعمال الشباب (ستارت اب لاند ) للسنة الثالثة على التوالي، بهدف نشر ثقافة وفكر ريادة الأعمال بين الشباب في مراحلهم المختلفة، ويساهم في جذب الاستثمار للشركات الناشئة في مصر و الشرق الاوسط، حيث يقام في الحرم اليوناني بالتحرير (جريك كامبوس)، يوم ١١ سبتمبر المقبل.</p>\r\n\r\n<p>حيث يترقب الكثير من المتابعين هذا المؤتمر خاصة بعد النجاح الكبير الذي حقق في النسخة الرقمية السابقة و التي كانت تقام عن بعد بسبب الظروف التي مر بها العالم نتيجة لانتشار جائحة كورونا.</p>\r\n\r\n<p>ويستضيف مؤتمر {ستارت اب لاند 2022} في نسخته الثالثة، مجموعة من الخبراء في كافة المجالات المتعلقة بريادة الاعمال، يصل عددهم الى 35 خبير، ومتحدث رسمي، كما يتم تنظيم اكتر من 30 ورش عمل ومحادثات ولقاءات للتحدث ومناقشة ابرز واهم الموضوعات في هذا المجال وعلى رأسهم التجارة الإلكترونية، الاستثمار، إدارة المشاريع الناشئة النمو، التسويق الألكتروني، تأسيس الشركات قانونيا.</p>', '<p>The activities of the Young Entrepreneurs Summit (Start Up Land) will be launched for the third year in a row, with the aim of spreading the culture and thought of entrepreneurship among young people in their different stages, and contribute to attracting investment for emerging companies in Egypt and the Middle East, as it is held in the Greek Campus in Tahrir (Greek Campus). ), on the 11th of September.</p>\r\n\r\n<p>Where many observers are waiting for this conference, especially after the great success that was achieved in the previous digital version, which was held remotely due to the circumstances that the world went through as a result of the spread of the Corona pandemic.</p>\r\n\r\n<p>The Startup Land 2022 conference, in its third edition, will host a group of 35 experts in all fields related to entrepreneurship, and an official speaker. Their head is e-commerce, investing, managing emerging growth projects, e-marketing, and legal establishment of companies.</p>', 1, '2021-09-01 09:00:00', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d13815.004328821437!2d31.2388414!3d30.0439983!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x27392189a2c25351!2z2KfZhNit2LHZhSDYp9mE2YrZiNmG2KfZhtmK!5e0!3m2!1sar!2seg!4v1633463160124!5m2!1sar!2seg', '1', 1, '2021-10-05 17:46:13', '2021-10-29 13:28:27'),
(500006, 'demo', 'demo', NULL, '<p>demo</p>', '<p>demo</p>', 1, '2021-12-21 09:00:00', NULL, '1', 1, '2021-12-20 10:32:13', '2021-12-20 10:32:13');

-- --------------------------------------------------------

--
-- Table structure for table `events_users`
--

CREATE TABLE `events_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employer` varchar(250) NOT NULL,
  `jobtitle` varchar(250) NOT NULL,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `events_users`
--

INSERT INTO `events_users` (`id`, `name`, `email`, `phone`, `employer`, `jobtitle`, `event_id`, `created_at`, `updated_at`) VALUES
(3, 'Ibrahim Elsayed', 'ibrahim.suez@gmail.com', '01000415330', 'Manager', 'TVET Egypt', 1, '2020-03-08 21:22:42', '2020-03-08 21:22:42'),
(4, 'Morad Kareem', 'Kareem@gmail.com', '8765467890', 'Prof', 'Professor', 1, '2020-03-08 21:59:12', '2020-03-08 21:59:12'),
(8, 'Ibrahim Elsayed', 'ibrahim.suez@gmail.com', '01000415330', 'demo', 'demo', 500001, '2021-11-29 18:44:49', '2021-11-29 18:44:49');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `failed_jobs`
--

INSERT INTO `failed_jobs` (`id`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(9, 'database', 'default', '{\"displayName\":\"App\\\\Notifications\\\\TemplateEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"delay\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":11:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:8:\\\"App\\\\User\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";N;}s:12:\\\"notification\\\";O:31:\\\"App\\\\Notifications\\\\TemplateEmail\\\":9:{s:7:\\\"Message\\\";a:3:{i:0;s:31:\\\"\\u0631\\u062f \\u0639\\u0644\\u0649 \\u0631\\u0627\\u0626\\u0641- VI portal\\\";i:1;s:21:\\\"Dear Raef Elsherbiny,\\\";i:2;s:64:\\\"\\u0627\\u0644\\u0631\\u062f \\u0628\\u0627\\u0644\\u062a\\u0641\\u0635\\u064a\\u0644 ......... \\u0627\\u0644\\u062a\\u0641\\u0635\\u064a\\u0644 \\u0641\\u064a \\u0627\\u0644\\u0631\\u062f\\\";}s:2:\\\"id\\\";s:36:\\\"16e5f2d2-b270-46ab-b339-a37a7f1c7223\\\";s:6:\\\"locale\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 'ErrorException: Undefined offset: 3 in C:\\wamp64\\www\\vi\\app\\Notifications\\TemplateEmail.php:50\nStack trace:\n#0 C:\\wamp64\\www\\vi\\app\\Notifications\\TemplateEmail.php(50): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(8, \'Undefined offse...\', \'C:\\\\wamp64\\\\www\\\\v...\', 50, Array)\n#1 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\MailChannel.php(51): App\\Notifications\\TemplateEmail->toMail(Object(App\\User))\n#2 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(144): Illuminate\\Notifications\\Channels\\MailChannel->send(Object(App\\User), Object(App\\Notifications\\TemplateEmail))\n#3 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(103): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\User), \'11a10bde-3c9b-4...\', Object(App\\Notifications\\TemplateEmail), \'mail\')\n#4 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#5 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(105): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#6 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\TemplateEmail), Array)\n#7 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(73): Illuminate\\Notifications\\ChannelManager->sendNow(Object(App\\User), Object(App\\Notifications\\TemplateEmail), Array)\n#8 [internal function]: Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#9 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(32): call_user_func_array(Array, Array)\n#10 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(90): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#11 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(34): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#12 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(576): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#13 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(94): Illuminate\\Container\\Container->call(Array)\n#14 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(128): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#15 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(104): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#16 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(98): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(49): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#18 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(88): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#19 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(337): Illuminate\\Queue\\Jobs\\Job->fire()\n#20 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(283): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(118): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(102): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(86): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#24 [internal function]: Illuminate\\Queue\\Console\\WorkCommand->handle()\n#25 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(32): call_user_func_array(Array, Array)\n#26 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(90): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#27 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(34): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#28 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(576): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#29 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(183): Illuminate\\Container\\Container->call(Array)\n#30 C:\\wamp64\\www\\vi\\vendor\\symfony\\console\\Command\\Command.php(255): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(170): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#32 C:\\wamp64\\www\\vi\\vendor\\symfony\\console\\Application.php(1009): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\wamp64\\www\\vi\\vendor\\symfony\\console\\Application.php(273): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\wamp64\\www\\vi\\vendor\\symfony\\console\\Application.php(149): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Application.php(90): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\wamp64\\www\\vi\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(133): Illuminate\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 C:\\wamp64\\www\\vi\\artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#38 {main}', '2021-12-21 14:58:52'),
(10, 'database', 'default', '{\"displayName\":\"App\\\\Notifications\\\\TemplateEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"delay\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":11:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:8:\\\"App\\\\User\\\";s:2:\\\"id\\\";i:97;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";N;}s:12:\\\"notification\\\";O:31:\\\"App\\\\Notifications\\\\TemplateEmail\\\":9:{s:7:\\\"Message\\\";a:4:{i:0;s:24:\\\"Step changed - VI portal\\\";i:1;s:15:\\\"Dear Trader Eg,\\\";i:2;s:53:\\\"Your step into Cycle changed, check your account now.\\\";i:3;s:6:\\\"\\/admin\\\";}s:2:\\\"id\\\";s:36:\\\"24f086f7-900c-4789-b203-090d5542f24d\\\";s:6:\\\"locale\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\User]. in /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:454\nStack trace:\n#0 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Queue/SerializesAndRestoresModelIdentifiers.php(102): Illuminate\\Database\\Eloquent\\Builder->firstOrFail()\n#1 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Queue/SerializesAndRestoresModelIdentifiers.php(57): Illuminate\\Notifications\\SendQueuedNotifications->restoreModel(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#2 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Queue/SerializesModels.php(45): Illuminate\\Notifications\\SendQueuedNotifications->getRestoredPropertyValue(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#3 [internal function]: Illuminate\\Notifications\\SendQueuedNotifications->__wakeup()\n#4 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(42): unserialize(\'O:48:\"Illuminat...\')\n#5 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Queue/Jobs/Job.php(88): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#6 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(337): Illuminate\\Queue\\Jobs\\Job->fire()\n#7 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(283): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#8 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(118): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#9 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(102): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#10 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(86): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#11 [internal function]: Illuminate\\Queue\\Console\\WorkCommand->handle()\n#12 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(32): call_user_func_array(Array, Array)\n#13 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(90): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(34): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#15 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Container/Container.php(576): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#16 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call(Array)\n#17 /home/master/vi.masterencode.com/vendor/symfony/console/Command/Command.php(255): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#18 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Console/Command.php(170): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#19 /home/master/vi.masterencode.com/vendor/symfony/console/Application.php(1009): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#20 /home/master/vi.masterencode.com/vendor/symfony/console/Application.php(273): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#21 /home/master/vi.masterencode.com/vendor/symfony/console/Application.php(149): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#22 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Console/Application.php(90): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#23 /home/master/vi.masterencode.com/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(133): Illuminate\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#24 /home/master/vi.masterencode.com/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#25 {main}', '2021-12-26 10:32:13');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(10) UNSIGNED NOT NULL,
  `title_en` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_ar` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `published` int(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `title_en`, `title_ar`, `answer_ar`, `answer_en`, `published`, `created_at`, `updated_at`) VALUES
(10, 'What is the meaning of Business accelerator?', 'ما المقصود بمسرع الأعمال؟', 'برنامج تسريع الأعمال هو برنامج يمنح الشركات النامية إمكانية الوصول إلى الإرشاد والمستثمرين وغير ذلك من أشكال الدعم التي تساعدهم على أن يصبحوا شركات مستقرة ومكتفية ذاتيًا.', 'A business accelerator is a program that gives developing companies access to mentorship, investors and other support that help them become stable, self-sufficient businesses.', 1, '2021-10-01 17:03:02', '2021-10-06 15:51:27'),
(6, 'What is the meaning of incubator?', 'ما المقصود بحاضنة الأعمال؟', 'هي مؤسسة أو مركز يساعد في تطوير المشاريع التجارية الجديدة الخاصة من خلال توفير مساحة تجارية منخفضة التكلفة أو مساعدة إدارية أو خدمات مشتركة.', 'An organization or place that aids the development of new business ventures especially by providing low-cost commercial space, management assistance, or shared services.', 1, '2021-10-01 15:52:16', '2021-10-01 16:49:06');

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `field_type` enum('input','textarea','select','radio','checkbox','file') COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `field_type`) VALUES
(1, 'input'),
(2, 'textarea'),
(3, 'select'),
(4, 'radio'),
(5, 'file');

-- --------------------------------------------------------

--
-- Table structure for table `finalscreenings`
--

CREATE TABLE `finalscreenings` (
  `company_id` int(20) NOT NULL,
  `step_id` int(20) NOT NULL,
  `judges` varchar(500) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `duration` int(5) DEFAULT NULL,
  `zoom_id` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_url` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `join_url` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `finalscreenings`
--

INSERT INTO `finalscreenings` (`company_id`, `step_id`, `judges`, `datetime`, `duration`, `zoom_id`, `start_url`, `join_url`, `created_at`, `updated_at`) VALUES
(14, 87, '[\"31\",\"13\"]', NULL, NULL, NULL, NULL, NULL, '2021-12-20 12:36:22', NULL),
(8, 87, '[\"8\"]', NULL, NULL, NULL, NULL, NULL, '2021-12-20 12:34:48', NULL),
(20, 87, '[\"10\"]', NULL, NULL, NULL, NULL, NULL, '2021-12-20 12:34:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `final_data`
--

CREATE TABLE `final_data` (
  `field_id` int(11) NOT NULL,
  `judge_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `question` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `final_data`
--

INSERT INTO `final_data` (`field_id`, `judge_id`, `company_id`, `step_id`, `question`, `answer`, `updated_at`, `created_at`) VALUES
(175, 13, 8, 87, 'Screening date', '2021-12-10', '2021-12-10 20:06:50', '2021-12-10 20:06:50'),
(176, 13, 8, 87, 'Trainining', 'Well trained', '2021-12-10 20:06:50', '2021-12-10 20:06:50'),
(177, 13, 8, 87, 'Descripe in one word', 'Trader word', '2021-12-10 20:06:50', '2021-12-10 20:06:50'),
(178, 13, 8, 87, 'Your Comments', 'comments from trader', '2021-12-10 20:06:50', '2021-12-10 20:06:50'),
(175, 3, 8, 87, 'Screening date', '2021-12-25', '2021-12-10 22:09:22', '2021-12-10 22:09:22'),
(176, 3, 8, 87, 'Trainining', 'Need to train', '2021-12-10 22:09:22', '2021-12-10 22:09:22'),
(177, 3, 8, 87, 'Descripe in one word', 'word from Othman', '2021-12-10 22:09:22', '2021-12-10 22:09:22'),
(178, 3, 8, 87, 'Your Comments', 'Othman comments', '2021-12-10 22:09:22', '2021-12-10 22:09:22');

-- --------------------------------------------------------

--
-- Table structure for table `form_data`
--

CREATE TABLE `form_data` (
  `field_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `question` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `screening` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `comments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `form_data`
--

INSERT INTO `form_data` (`field_id`, `company_id`, `user_id`, `form_id`, `step_id`, `question`, `answer`, `screening`, `comments`, `updated_at`, `created_at`) VALUES
(172, 14, 86, 83, 99, 'Details', 'all details about my company', '{}', '{}', '2021-12-27 10:21:19', '2021-12-08 13:44:27'),
(170, 14, 86, 83, 99, 'Join Date', '2021-12-23', '{}', '{}', '2021-12-27 10:21:19', '2021-12-08 13:44:27'),
(169, 14, 86, 83, 99, 'Company Email', 'elsherbinyraef@gmail.com', '{}', '{}', '2021-12-27 10:21:19', '2021-12-08 13:44:27'),
(174, 14, 86, 83, 99, 'Upload your CV', 'http://vi.vi/admin/uploads/files/86/Wn.jpg', '{}', '{}', '2021-12-27 10:21:19', '2021-12-08 13:44:27'),
(173, 14, 86, 83, 99, 'Fav list', 'Games', '{}', '{}', '2021-12-27 10:21:19', '2021-12-08 13:44:27'),
(171, 14, 86, 83, 99, 'Phone', '01208888093', '{}', '{}', '2021-12-27 10:21:19', '2021-12-08 13:44:27'),
(169, 8, 2, 83, 99, 'Company Email', 'ctqs.egypt@gmail.com', '{\"100\": \"2\"}', '{\"100\": null}', '2021-12-28 07:26:22', '2021-12-20 12:09:09'),
(170, 8, 2, 83, 99, 'Join Date', '2021-11-02', '{\"100\": \"2\"}', '{\"100\": null}', '2021-12-28 07:26:22', '2021-12-20 12:09:09'),
(172, 8, 2, 83, 99, 'Details', 'some details', '{\"100\": \"8\"}', '{\"100\": null}', '2021-12-28 07:26:22', '2021-12-20 12:09:09'),
(173, 8, 2, 83, 99, 'Fav list', 'Football', '{\"100\": \"10\"}', '{\"100\": null}', '2021-12-28 07:26:22', '2021-12-20 12:09:09'),
(171, 8, 2, 83, 99, 'Phone', '234525323', '{\"100\": \"4\"}', '{\"100\": null}', '2021-12-28 07:26:22', '2021-12-20 12:09:09'),
(174, 8, 2, 83, 99, 'Upload your CV', 'http://vi.vi/admin/uploads/files/2/الاطباق والانفتاح.pptx', '{\"100\": \"10\"}', '{\"100\": null}', '2021-12-28 07:26:22', '2021-12-20 12:09:09'),
(174, 20, 97, 83, 99, 'Upload your CV', 'https://vi.masterencode.com/admin/uploads/files/97/0475-014-001-011x.pdf', '{}', '{}', '2021-12-27 10:21:19', '2021-12-20 08:34:38'),
(173, 20, 97, 83, 99, 'Fav list', 'Football', '{}', '{}', '2021-12-27 10:21:19', '2021-12-20 08:34:38'),
(171, 20, 97, 83, 99, 'Phone', '01026744447', '{}', '{}', '2021-12-27 10:21:19', '2021-12-20 08:34:38'),
(172, 20, 97, 83, 99, 'Details', NULL, '{}', '{}', '2021-12-27 10:21:19', '2021-12-20 08:34:38'),
(170, 20, 97, 83, 99, 'Join Date', '2021-12-20', '{}', '{}', '2021-12-27 10:21:19', '2021-12-20 08:34:38'),
(169, 20, 97, 83, 99, 'Company Email', 'trader.egypt@gmail.com', '{}', '{}', '2021-12-27 10:21:19', '2021-12-20 08:34:38'),
(174, 21, 101, 83, 99, 'Upload your CV', 'http://vi.vi/admin/uploads/files/101/Macr.jpg', '{\"100\": \"5\", \"3\": \"8\"}', '{\"100\": null, \"3\": \"good work\"}', '2021-12-27 11:18:54', '2021-12-24 19:23:38'),
(173, 21, 101, 83, 99, 'Fav list', 'Football', '{\"100\": \"5\", \"3\": \"8\"}', '{\"100\": null, \"3\": null}', '2021-12-27 11:18:54', '2021-12-24 19:23:38'),
(171, 21, 101, 83, 99, 'Phone', '64564645', '{\"100\": \"5\", \"3\": \"8\"}', '{\"100\": null, \"3\": null}', '2021-12-27 11:18:54', '2021-12-24 19:23:38'),
(172, 21, 101, 83, 99, 'Details', 'More details and text', '{\"100\": \"5\", \"3\": \"8\"}', '{\"100\": null, \"3\": null}', '2021-12-27 11:18:54', '2021-12-24 19:23:38'),
(170, 21, 101, 83, 99, 'Join Date', '2021-12-22', '{\"100\": \"5\", \"3\": \"8\"}', '{\"100\": null, \"3\": null}', '2021-12-27 11:18:54', '2021-12-24 19:23:38'),
(169, 21, 101, 83, 99, 'Company Email', 'ctqs.egypt@gmail.com', '{\"100\": \"5\", \"3\": \"8\"}', '{\"100\": \"not working\", \"3\": null}', '2021-12-27 11:18:54', '2021-12-24 19:23:38'),
(174, 22, 104, 83, 99, 'Upload your CV', 'https://vi.masterencode.com/admin/uploads/files/104/pngtree-vector-boat-icon-png-image_314452.jpg', '{\"3\": \"8\", \"100\": \"5\"}', '{\"3\": null, \"100\": \"CV not uploaded\"}', '2021-12-27 10:25:20', '2021-12-27 11:13:00'),
(173, 22, 104, 83, 99, 'Fav list', 'Games', '{\"3\": \"8\", \"100\": \"5\"}', '{\"3\": null, \"100\": null}', '2021-12-27 10:25:20', '2021-12-27 11:13:00'),
(171, 22, 104, 83, 99, 'Phone', '0020100415330', '{\"3\": \"8\", \"100\": \"5\"}', '{\"3\": null, \"100\": null}', '2021-12-27 10:25:20', '2021-12-27 11:13:00'),
(172, 22, 104, 83, 99, 'Details', 'Some details will be here', '{\"3\": \"8\", \"100\": \"5\"}', '{\"3\": null, \"100\": null}', '2021-12-27 10:25:20', '2021-12-27 11:13:00'),
(170, 22, 104, 83, 99, 'Join Date', '2021-12-26', '{\"3\": \"8\", \"100\": \"5\"}', '{\"3\": null, \"100\": null}', '2021-12-27 10:25:20', '2021-12-27 11:13:00'),
(169, 22, 104, 83, 99, 'Company Email', 'ctqs.egypt@gmail.com', '{\"3\": \"8\", \"100\": \"5\"}', '{\"3\": \"should be business email\", \"100\": null}', '2021-12-27 10:25:20', '2021-12-27 11:13:00');

-- --------------------------------------------------------

--
-- Table structure for table `form_fields`
--

CREATE TABLE `form_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `step_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `form_fields`
--

INSERT INTO `form_fields` (`id`, `step_id`, `field_id`, `options`, `updated_at`, `created_at`) VALUES
(178, 87, 2, '{\"label\":\"Your Comments\",\"validation\":{\"required\":0},\"rows\":3}', '2021-12-08 13:11:44', '2021-12-08 13:11:44'),
(176, 87, 3, '{\"label\":\"Trainining\",\"validation\":{\"required\":1},\"values\":\"Well trained,Need to train\"}', '2021-12-08 13:11:44', '2021-12-08 13:11:44'),
(177, 87, 1, '{\"label\":\"Descripe in one word\",\"validation\":{\"required\":0},\"type\":\"text\"}', '2021-12-08 13:11:44', '2021-12-08 13:11:44'),
(174, 83, 5, '{\"type\":\"file\",\"label\":\"Upload your CV\",\"validation\":{\"required\":0}}', '2021-12-08 12:07:48', '2021-12-08 12:07:48'),
(175, 87, 1, '{\"label\":\"Screening date\",\"validation\":{\"required\":0},\"type\":\"date\"}', '2021-12-08 13:11:44', '2021-12-08 13:11:44'),
(173, 83, 3, '{\"label\":\"Fav list\",\"values\":\"Football,\\nGames,\\nDrawing\",\"validation\":{\"required\":1}}', '2021-12-08 12:07:48', '2021-12-08 12:07:48'),
(171, 83, 1, '{\"type\":\"number\",\"label\":\"Phone\",\"validation\":{\"required\":0}}', '2021-12-08 12:07:48', '2021-12-08 12:07:48'),
(172, 83, 2, '{\"rows\":3,\"label\":\"Details\",\"validation\":{\"required\":0}}', '2021-12-08 12:07:48', '2021-12-08 12:07:48'),
(170, 83, 1, '{\"type\":\"date\",\"label\":\"Join Date\",\"validation\":{\"required\":1}}', '2021-12-08 12:07:48', '2021-12-08 12:07:48'),
(169, 83, 1, '{\"type\":\"email\",\"label\":\"Company Email\",\"validation\":{\"required\":1}}', '2021-12-08 12:07:48', '2021-12-08 12:07:48'),
(168, 79, 5, '{\"type\":\"file\",\"label\":\"Upload your CV\",\"validation\":{\"required\":0}}', '2021-12-08 11:32:15', '2021-12-08 11:32:15'),
(167, 79, 3, '{\"label\":\"Fav list\",\"values\":\"Football,\\nGames,\\nDrawing\",\"validation\":{\"required\":1}}', '2021-12-08 11:32:15', '2021-12-08 11:32:15'),
(166, 79, 2, '{\"rows\":3,\"label\":\"Details\",\"validation\":{\"required\":0}}', '2021-12-08 11:32:15', '2021-12-08 11:32:15'),
(165, 79, 1, '{\"type\":\"number\",\"label\":\"Phone\",\"validation\":{\"required\":0}}', '2021-12-08 11:32:15', '2021-12-08 11:32:15'),
(164, 79, 1, '{\"type\":\"date\",\"label\":\"Join Date\",\"validation\":{\"required\":1}}', '2021-12-08 11:32:15', '2021-12-08 11:32:15'),
(163, 79, 1, '{\"type\":\"email\",\"label\":\"Company Email\",\"validation\":{\"required\":1}}', '2021-12-08 11:32:15', '2021-12-08 11:32:15'),
(179, 89, 1, '{\"label\":\"Screening date\",\"validation\":{\"required\":0},\"type\":\"date\"}', '2021-12-10 08:08:27', '2021-12-10 08:08:27'),
(180, 89, 3, '{\"label\":\"Trainining\",\"validation\":{\"required\":1},\"values\":\"Well trained,Need to train\"}', '2021-12-10 08:08:27', '2021-12-10 08:08:27'),
(181, 89, 1, '{\"label\":\"Descripe in one word\",\"validation\":{\"required\":0},\"type\":\"text\"}', '2021-12-10 08:08:27', '2021-12-10 08:08:27'),
(182, 89, 2, '{\"label\":\"Your Comments\",\"validation\":{\"required\":0},\"rows\":3}', '2021-12-10 08:08:27', '2021-12-10 08:08:27'),
(183, 90, 1, '{\"label\":\"Name\",\"validation\":{\"required\":0},\"type\":\"text\"}', '2021-12-19 10:16:22', '2021-12-19 10:16:22'),
(184, 90, 1, '{\"label\":\"Email \",\"validation\":{\"required\":1},\"type\":\"email\"}', '2021-12-19 10:16:22', '2021-12-19 10:16:22'),
(185, 90, 3, '{\"label\":\"Company\",\"validation\":{\"required\":1},\"values\":\"company, not company\"}', '2021-12-19 10:16:22', '2021-12-19 10:16:22'),
(186, 90, 5, '{\"label\":\"Upload file\",\"validation\":{\"required\":0},\"type\":\"file\"}', '2021-12-19 10:16:22', '2021-12-19 10:16:22'),
(187, 92, 1, '{\"type\":\"text\",\"label\":\"\\u0639\\u0646\\u0648\\u0627\\u0646 \\u0627\\u0644\\u0634\\u0631\\u0643\\u0629\",\"validation\":{\"required\":0}}', '2021-12-20 11:44:03', '2021-12-20 11:44:03'),
(188, 92, 1, '{\"type\":\"date\",\"label\":\"\\u062a\\u0627\\u0631\\u064a\\u062e \\u0627\\u0644\\u0625\\u0646\\u0634\\u0627\\u0621\",\"validation\":{\"required\":1}}', '2021-12-20 11:44:03', '2021-12-20 11:44:03'),
(189, 92, 3, '{\"label\":\"\\u0645\\u062c\\u0627\\u0644 \\u0627\\u0644\\u0639\\u0645\\u0644\",\"values\":\"\\u0627\\u0644\\u0628\\u0631\\u0645\\u062c\\u064a\\u0627\\u062a,\\n\\u0627\\u0644\\u0635\\u064a\\u0627\\u0646\\u0629,\\n\\u0627\\u0644\\u062a\\u0648\\u0631\\u064a\\u062f\\u0627\\u062a\",\"validation\":{\"required\":0}}', '2021-12-20 11:44:03', '2021-12-20 11:44:03');

-- --------------------------------------------------------

--
-- Table structure for table `inclusives`
--

CREATE TABLE `inclusives` (
  `id` int(11) NOT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `en_title` varchar(255) NOT NULL,
  `ar_title` varchar(255) NOT NULL,
  `user_added` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `inclusives`
--

INSERT INTO `inclusives` (`id`, `img_url`, `en_title`, `ar_title`, `user_added`, `created_at`, `updated_at`) VALUES
(1, 'rooms/WIFI_icon.svg.png', 'Wi-Fi', 'إنترنت', 1, '2021-10-26 03:09:57', '2021-10-26 03:09:57'),
(2, 'rooms/imgbin-television-channel-mobile-television-stock-illustration-tv-icons-pV5Qz0HVpp0AV2ybMe3aYf7gS.jpg', 'TV', 'تلفزيون', 1, '2021-10-26 03:10:13', '2021-10-26 03:10:13'),
(3, 'rooms/1530481.png', 'Air Condition', 'مكيف هواء', 11, '2021-10-26 03:10:53', '2021-10-26 03:10:53'),
(4, 'rooms/683974.png', 'soft Drinks', 'مشروبات خفيفة', 1, '2021-10-26 03:11:27', '2021-10-26 03:11:27'),
(5, 'rooms/kisspng-soft-drink-scalable-vector-graphics-icon-drink-5a9c44346026c3.0503121615201905163938.jpg', 'soft meals', 'وجبات خفيفة', 1, '2021-10-26 03:11:27', '2021-10-26 03:11:27'),
(6, 'rooms/icons-private-bathroom.png', 'Private Bathroom', 'مرحاض خاص', 1, '2021-10-26 03:11:27', '2021-10-26 03:11:27'),
(7, 'rooms/173077-200.png', 'Waite board ', 'لوحة شرح \"سبورة\"', 1, '2021-10-26 03:11:27', '2021-10-26 03:11:27'),
(8, 'rooms/projector-131964753363397901.png', 'Projector', 'بروجيكتور', 1, '2021-10-26 03:11:27', '2021-10-26 03:11:27');

-- --------------------------------------------------------

--
-- Table structure for table `incubators`
--

CREATE TABLE `incubators` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `establishmentdate` date DEFAULT NULL,
  `portfolio` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `partners` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offering` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkeded` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `incubators`
--

INSERT INTO `incubators` (`id`, `title`, `description`, `establishmentdate`, `portfolio`, `partners`, `offering`, `logo`, `facebook`, `twitter`, `linkeded`, `youtube`, `website`, `created_at`, `updated_at`) VALUES
(16, 'Main incubator', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-11-30 11:51:29', '2021-11-30 11:51:29');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mentorship_requests`
--

CREATE TABLE `mentorship_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mentor_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_id` int(20) DEFAULT NULL,
  `fileurl` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `join_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zoom_date` datetime DEFAULT NULL,
  `period` int(11) NOT NULL DEFAULT 0,
  `rating` int(2) DEFAULT NULL,
  `rate_comment` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `done` int(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mentorship_requests`
--

INSERT INTO `mentorship_requests` (`id`, `mentor_id`, `user_id`, `comment`, `approved`, `admin_comment`, `admin_id`, `fileurl`, `start_url`, `join_url`, `zoom_date`, `period`, `rating`, `rate_comment`, `done`, `created_at`, `updated_at`) VALUES
(13, 13, 2, NULL, 'Yes', NULL, 1, NULL, 'https://zoom.us/s/96432306733?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJhdWQiOiJjbGllbnRzbSIsInVpZCI6IkY1dllrTjdsU01HQ0c0YjR6QTYwUEEiLCJpc3MiOiJ3ZWIiLCJzdHkiOjEwMCwid2NkIjoiYXcxIiwiY2x0IjowLCJtbnVtIjoiOTY0MzIzMDY3MzMiLCJzdGsiOiJVOVRtZzZGOTk3QTF1eEY1XzFZSk1VQXhNcmhOamtJTUU3NXpQcDBySlVnLkJnWWdUUzhyWlZwS04xcENhakZQU0V3NWMwUkdkbGcwTTFkdlNWRlhObE5IVjB4QU9EY3lNemxpTW1GaFkyWTVPR05qTkRWaE1USTFPR0UyTkRCbFl6YzFZbUV3TTJVek5URmtaak13Tm1Ka05qSXhNRFJrTVRObU5qZzRaR00wWTJWbE1nQWdka012UlZKR2IyeHNORzh6TVU1cVZESlplWEI0VEd4blYyNDFWVlkzTlU4QUEyRjNNUUFBQVgxbVAyU1NBQkoxQUFBQSIsImV4cCI6MTYzODEwNTE3MCwiaWF0IjoxNjM4MDk3OTcwLCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.oRhJP9P34q_y-n7HBsnTJLOjd6ruPNu4BHAKArRg3gI', 'https://zoom.us/j/96432306733?pwd=M1RWSUJUcnMraGlYNTQwTlNKRTlMZz09', '2021-11-28 17:00:00', 120, 1, 'Very bad', NULL, '2021-11-27 19:52:45', '2021-12-04 13:10:53'),
(14, 44, 2, NULL, 'Yes', 'Final', 1, NULL, 'https://zoom.us/s/92847518083?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJhdWQiOiJjbGllbnRzbSIsInVpZCI6IkY1dllrTjdsU01HQ0c0YjR6QTYwUEEiLCJpc3MiOiJ3ZWIiLCJzdHkiOjEwMCwid2NkIjoiYXcxIiwiY2x0IjowLCJtbnVtIjoiOTI4NDc1MTgwODMiLCJzdGsiOiI1QnlHNUxJbnFsRjBoeGprcGt6Rk53T3FhckIzQTMxUmVVR2VnQ2I4NjlJLkJnWWdUUzhyWlZwS04xcENhakZQU0V3NWMwUkdkbGcwTTFkdlNWRlhObE5IVjB4QU9EY3lNemxpTW1GaFkyWTVPR05qTkRWaE1USTFPR0UyTkRCbFl6YzFZbUV3TTJVek5URmtaak13Tm1Ka05qSXhNRFJrTVRObU5qZzRaR00wWTJWbE1nQWdka012UlZKR2IyeHNORzh6TVU1cVZESlplWEI0VEd4blYyNDFWVlkzTlU4QUEyRjNNUUFBQVgxbU5fNVhBQkoxQUFBQSIsImV4cCI6MTYzODEwNDY4NSwiaWF0IjoxNjM4MDk3NDg1LCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.VRQLv1gDromLDEFvLvNizSSzabG2MqWQEcNw1C4dtKU', 'https://zoom.us/j/92847518083?pwd=OTFXdHNqT0xoV2R0R0JzZmhFTDhRZz09', '2021-12-02 10:00:00', 30, 2, 'not good', NULL, '2021-11-27 19:56:30', '2021-12-04 12:32:17'),
(15, 44, 89, NULL, 'Yes', 'modified after calling both users', 1, NULL, 'https://zoom.us/s/97356038851?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJhdWQiOiJjbGllbnRzbSIsInVpZCI6IkY1dllrTjdsU01HQ0c0YjR6QTYwUEEiLCJpc3MiOiJ3ZWIiLCJzdHkiOjEwMCwid2NkIjoiYXcxIiwiY2x0IjowLCJtbnVtIjoiOTczNTYwMzg4NTEiLCJzdGsiOiJMVEplY1lWX1NDUFBRTUMwUkVyZWtkeGtxd3hDaGdVbTBRSzNfZHRtbERVLkJnWWdUUzhyWlZwS04xcENhakZQU0V3NWMwUkdkbGcwTTFkdlNWRlhObE5IVjB4QU9EY3lNemxpTW1GaFkyWTVPR05qTkRWaE1USTFPR0UyTkRCbFl6YzFZbUV3TTJVek5URmtaak13Tm1Ka05qSXhNRFJrTVRObU5qZzRaR00wWTJWbE1nQWdka012UlZKR2IyeHNORzh6TVU1cVZESlplWEI0VEd4blYyNDFWVlkzTlU4QUEyRjNNUUFBQVgxbWg5SmFBQkoxQUFBQSIsImV4cCI6MTYzODEwOTkxNywiaWF0IjoxNjM4MTAyNzE3LCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.NfHjZHTtld6_su3m96wcAzLSHvGYXsHKxundA_oWlrU', 'https://zoom.us/j/97356038851?pwd=RE5LTDJSTUhPQmxSSUZrclZkNjk2QT09', '2021-12-05 05:00:00', 300, NULL, '', NULL, '2021-11-27 20:20:22', '2021-11-28 11:31:57'),
(16, 13, 86, NULL, 'Yes', NULL, 1, NULL, 'https://zoom.us/s/98138899243?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJhdWQiOiJjbGllbnRzbSIsInVpZCI6IkY1dllrTjdsU01HQ0c0YjR6QTYwUEEiLCJpc3MiOiJ3ZWIiLCJzdHkiOjEwMCwid2NkIjoiYXcxIiwiY2x0IjowLCJtbnVtIjoiOTgxMzg4OTkyNDMiLCJzdGsiOiJ2dVZXMWROdkhYeXk3Z0xhMjkxM2FaR2t4SGtSRW15RENQOXJlcGZpWWpRLkJnWWdUUzhyWlZwS04xcENhakZQU0V3NWMwUkdkbGcwTTFkdlNWRlhObE5IVjB4QU9EY3lNemxpTW1GaFkyWTVPR05qTkRWaE1USTFPR0UyTkRCbFl6YzFZbUV3TTJVek5URmtaak13Tm1Ka05qSXhNRFJrTVRObU5qZzRaR00wWTJWbE1nQWdka012UlZKR2IyeHNORzh6TVU1cVZESlplWEI0VEd4blYyNDFWVlkzTlU4QUEyRjNNUUFBQVgxbUVXajRBQkoxQUFBQSIsImV4cCI6MTYzODEwMjE1NiwiaWF0IjoxNjM4MDk0OTU2LCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.efDQ0Z7bLZkTCk6orno8v2MnREz48bts88jw280bbKs', 'https://zoom.us/j/98138899243?pwd=UjBPNWVnaUQwbjhPcEVmNzdUT0V2UT09', '2021-12-01 09:00:00', 30, 2, 'bad', NULL, '2021-11-28 10:22:36', '2021-12-07 10:44:08'),
(17, 13, 2, NULL, 'Yes', NULL, NULL, NULL, 'https://zoom.us/j/98138899243?pwd=UjBPNWVnaUQwbjhPcEVmNzdUT0V2UT09', 'https://zoom.us/j/98138899243?pwd=UjBPNWVnaUQwbjhPcEVmNzdUT0V2UT09', '2021-11-29 15:00:00', 60, 3, NULL, NULL, '2021-11-29 17:05:19', '2021-12-04 12:33:09'),
(18, 45, 2, NULL, 'No', NULL, NULL, NULL, NULL, NULL, '2021-11-29 22:00:00', 60, NULL, '', NULL, '2021-11-29 17:08:03', '2021-11-29 15:38:07'),
(19, 13, 2, 'This will important for our company please approve', 'No', NULL, NULL, NULL, NULL, NULL, '2021-12-04 19:14:00', 60, NULL, '', NULL, '2021-11-29 17:14:48', '2021-11-29 15:37:34'),
(20, 13, 2, '1 Hour only', 'No', NULL, NULL, NULL, NULL, NULL, '2021-12-04 20:00:00', 60, NULL, '', NULL, '2021-11-29 17:59:05', '2021-11-30 07:26:57'),
(21, 13, 2, 'Just one hour', 'Yes', 'Changed to the next day', NULL, NULL, 'https://zoom.us/s/93728009368?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJhdWQiOiJjbGllbnRzbSIsInVpZCI6IkY1dllrTjdsU01HQ0c0YjR6QTYwUEEiLCJpc3MiOiJ3ZWIiLCJzdHkiOjEwMCwid2NkIjoiYXcxIiwiY2x0IjowLCJtbnVtIjoiOTM3MjgwMDkzNjgiLCJzdGsiOiI4N0JGMEJpa21obWlLREJvdGFQQ21fMEVsdm9vUzlXR19hd1pFZnAyRGdNLkJnWWdUUzhyWlZwS04xcENhakZQU0V3NWMwUkdkbGcwTTFkdlNWRlhObE5IVjB4QU9EY3lNemxpTW1GaFkyWTVPR05qTkRWaE1USTFPR0UyTkRCbFl6YzFZbUV3TTJVek5URmtaak13Tm1Ka05qSXhNRFJrTVRObU5qZzRaR00wWTJWbE1nQWdka012UlZKR2IyeHNORzh6TVU1cVZESlplWEI0VEd4blYyNDFWVlkzTlU4QUEyRjNNUUFBQVgyek1lV2FBQkoxQUFBQSIsImV4cCI6MTYzOTM5NjEzMSwiaWF0IjoxNjM5Mzg4OTMxLCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.oSTPH8qY_aye-pGnkD_cYjxDWH9JREbUCmFzyrtWwFc', 'https://zoom.us/j/93728009368?pwd=N0lnNzk2bktQTGhPWlFQc2ViUGF2dz09', '2021-12-30 10:00:00', 60, NULL, '', NULL, '2021-12-03 08:40:16', '2021-12-13 08:48:51'),
(22, 44, 2, NULL, 'Yes', NULL, NULL, NULL, 'https://zoom.us/s/95397018170?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJhdWQiOiJjbGllbnRzbSIsInVpZCI6IkY1dllrTjdsU01HQ0c0YjR6QTYwUEEiLCJpc3MiOiJ3ZWIiLCJzdHkiOjEwMCwid2NkIjoiYXcxIiwiY2x0IjowLCJtbnVtIjoiOTUzOTcwMTgxNzAiLCJzdGsiOiI3Q2I2eGZESDI3Sk9tZXJteDYtOGlyZm11R2FJSDBXTExpRTJoS3FDVFgwLkJnWWdUUzhyWlZwS04xcENhakZQU0V3NWMwUkdkbGcwTTFkdlNWRlhObE5IVjB4QU9EY3lNemxpTW1GaFkyWTVPR05qTkRWaE1USTFPR0UyTkRCbFl6YzFZbUV3TTJVek5URmtaak13Tm1Ka05qSXhNRFJrTVRObU5qZzRaR00wWTJWbE1nQWdka012UlZKR2IyeHNORzh6TVU1cVZESlplWEI0VEd4blYyNDFWVlkzTlU4QUEyRjNNUUFBQVgyelpfSVdBQkoxQUFBQSIsImV4cCI6MTYzOTM5OTY3MywiaWF0IjoxNjM5MzkyNDczLCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.ZnIjuOnyiUCuxSQBZihEyExt8I_kIZjrSRmUnCBlRy0', 'https://zoom.us/j/95397018170?pwd=Vzl0QnVyVFpDeFJDdlNqOEJzMmluUT09', '2021-12-04 09:00:00', 60, 4, 'تمام', NULL, '2021-12-03 16:35:59', '2021-12-19 09:50:32'),
(23, 13, 2, NULL, 'Yes', NULL, NULL, '2/E40kd9lCecwpOdoNPDuZvh7Hgi4g0HC21NDoS7yI.jpg', 'https://zoom.us/s/95717664023?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJ3ZWIiLCJjbHQiOjAsIm1udW0iOiI5NTcxNzY2NDAyMyIsInN0ayI6IkFOOHNONnV0UlJhX2d6V0VLYUNxU05oemJ1ZDhOVU9HT3NUQ1RCdlVSRE0uQmdZZ1RTOHJaVnBLTjFwQ2FqRlBTRXc1YzBSR2RsZzBNMWR2U1ZGWE5sTkhWMHhBT0RjeU16bGlNbUZoWTJZNU9HTmpORFZoTVRJMU9HRTJOREJsWXpjMVltRXdNMlV6TlRGa1pqTXdObUprTmpJeE1EUmtNVE5tTmpnNFpHTTBZMlZsTWdBZ2RrTXZSVkpHYjJ4c05HOHpNVTVxVkRKWmVYQjRUR3huVjI0MVZWWTNOVThBQTJGM01RQUFBWDNYSW5OSEFCSjFBQUFBIiwiYXVkIjoiY2xpZW50c20iLCJ1aWQiOiJGNXZZa043bFNNR0NHNGI0ekE2MFBBIiwic2siOiIyOTEwNTE0MjkwMjAwMDU2NDgzIiwic3R5IjoxMDAsIndjZCI6ImF3MSIsImV4cCI6MTYzOTk5OTA5OCwiaWF0IjoxNjM5OTkxODk4LCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.tPK-w2M0wOAnY2G4aqlmqgUiUPuLQwCO5oOvskEnNKM', 'https://zoom.us/j/95717664023?pwd=RTYzR3pBd1R0Vy93dDB2dHh0RlFyZz09', '2021-12-10 15:00:00', 60, 2, 'thanks', NULL, '2021-12-03 17:32:42', '2021-12-20 11:07:45'),
(24, 44, 2, NULL, 'Yes', NULL, NULL, '', 'https://zoom.us/s/97487958864?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJ3ZWIiLCJjbHQiOjAsIm1udW0iOiI5NzQ4Nzk1ODg2NCIsInN0ayI6InlGTEduZFFvREQ4Vjd3bm1nSUdtSkRkQ0lodHZUZUp3NjNUVXN3UFlBQlEuQmdZZ1RTOHJaVnBLTjFwQ2FqRlBTRXc1YzBSR2RsZzBNMWR2U1ZGWE5sTkhWMHhBT0RjeU16bGlNbUZoWTJZNU9HTmpORFZoTVRJMU9HRTJOREJsWXpjMVltRXdNMlV6TlRGa1pqTXdObUprTmpJeE1EUmtNVE5tTmpnNFpHTTBZMlZsTWdBZ2RrTXZSVkpHYjJ4c05HOHpNVTVxVkRKWmVYQjRUR3huVjI0MVZWWTNOVThBQTJGM01RQUFBWDNZRW9ySEFCSjFBQUFBIiwiYXVkIjoiY2xpZW50c20iLCJ1aWQiOiJGNXZZa043bFNNR0NHNGI0ekE2MFBBIiwic2siOiIyOTEwNTE0MjkwMjAwMDU2NDgzIiwic3R5IjoxMDAsIndjZCI6ImF3MSIsImV4cCI6MTY0MDAxNDgzMywiaWF0IjoxNjQwMDA3NjMzLCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.zoDnoUya5d7ZIT-_vQYw2yd-zReXQoIjIAL4YMJv438', 'https://zoom.us/j/97487958864?pwd=cEo3Mi9DN3BuOTFaTGR0b3ZHNXBxZz09', '2021-12-07 12:09:00', 120, 3, 'OK', NULL, '2021-12-06 10:09:30', '2021-12-21 10:01:01'),
(25, 44, 2, NULL, 'Yes', NULL, NULL, '', 'https://zoom.us/s/99561770964?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJ3ZWIiLCJjbHQiOjAsIm1udW0iOiI5OTU2MTc3MDk2NCIsInN0ayI6Iks4WGptQU85Xzk3S1RoNGllMWdiQ2xEWG1UYkZQY1dOYnB5d0xfblVNQmsuQmdZZ1RTOHJaVnBLTjFwQ2FqRlBTRXc1YzBSR2RsZzBNMWR2U1ZGWE5sTkhWMHhBT0RjeU16bGlNbUZoWTJZNU9HTmpORFZoTVRJMU9HRTJOREJsWXpjMVltRXdNMlV6TlRGa1pqTXdObUprTmpJeE1EUmtNVE5tTmpnNFpHTTBZMlZsTWdBZ2RrTXZSVkpHYjJ4c05HOHpNVTVxVkRKWmVYQjRUR3huVjI0MVZWWTNOVThBQTJGM01RQUFBWDM4RnF3eEFCSjFBQUFBIiwiYXVkIjoiY2xpZW50c20iLCJ1aWQiOiJGNXZZa043bFNNR0NHNGI0ekE2MFBBIiwic2siOiIyOTEwNTE0MjkwMjAwMDU2NDgzIiwic3R5IjoxMDAsIndjZCI6ImF3MSIsImV4cCI6MTY0MDYxOTA4NCwiaWF0IjoxNjQwNjExODg0LCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.ogAgO74exCiL5UzsqzufkxldSwu_alEMFjaqFDGaqNQ', 'https://zoom.us/j/99561770964?pwd=SXhaK0l0bGZtUmVGcmpUSTVxREhSZz09', '2021-12-19 12:52:00', 60, NULL, NULL, NULL, '2021-12-19 10:52:38', '2021-12-27 12:31:24'),
(26, 44, 97, NULL, 'Yes', NULL, NULL, '', 'https://zoom.us/s/93782966116?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJ3ZWIiLCJjbHQiOjAsIm1udW0iOiI5Mzc4Mjk2NjExNiIsInN0ayI6InR6QkhBUFpGUWs5ZGlDXzJUWFAycHhHY0N5V2gxajl2aDBsazNwUm1IOGsuQmdZZ1RTOHJaVnBLTjFwQ2FqRlBTRXc1YzBSR2RsZzBNMWR2U1ZGWE5sTkhWMHhBT0RjeU16bGlNbUZoWTJZNU9HTmpORFZoTVRJMU9HRTJOREJsWXpjMVltRXdNMlV6TlRGa1pqTXdObUprTmpJeE1EUmtNVE5tTmpnNFpHTTBZMlZsTWdBZ2RrTXZSVkpHYjJ4c05HOHpNVTVxVkRKWmVYQjRUR3huVjI0MVZWWTNOVThBQTJGM01RQUFBWDNYQUoxdEFCSjFBQUFBIiwiYXVkIjoiY2xpZW50c20iLCJ1aWQiOiJGNXZZa043bFNNR0NHNGI0ekE2MFBBIiwic2siOiIyOTEwNTE0MjkwMjAwMDU2NDgzIiwic3R5IjoxMDAsIndjZCI6ImF3MSIsImV4cCI6MTYzOTk5Njg4MSwiaWF0IjoxNjM5OTg5NjgxLCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.hTawIsk1Lxbow4b0cCogYfQXdfHtBL-nkxQQ37fnhmw', 'https://zoom.us/j/93782966116?pwd=ZytPdWh1MU5GaWplZDdFc1owT21zQT09', '2021-12-20 13:00:00', 60, NULL, NULL, NULL, '2021-12-20 08:38:13', '2021-12-20 07:41:21'),
(27, 44, 2, NULL, 'Yes', NULL, NULL, '', 'https://zoom.us/s/92238443776?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJ3ZWIiLCJjbHQiOjAsIm1udW0iOiI5MjIzODQ0Mzc3NiIsInN0ayI6IklLYXBWcnJOWXdxejJkSGVjVTdoejFMZXFBc0h1em9JRFlTZ2xmZEFwZUEuQmdZZ1RTOHJaVnBLTjFwQ2FqRlBTRXc1YzBSR2RsZzBNMWR2U1ZGWE5sTkhWMHhBT0RjeU16bGlNbUZoWTJZNU9HTmpORFZoTVRJMU9HRTJOREJsWXpjMVltRXdNMlV6TlRGa1pqTXdObUprTmpJeE1EUmtNVE5tTmpnNFpHTTBZMlZsTWdBZ2RrTXZSVkpHYjJ4c05HOHpNVTVxVkRKWmVYQjRUR3huVjI0MVZWWTNOVThBQTJGM01RQUFBWDNYeVgxUkFCSjFBQUFBIiwiYXVkIjoiY2xpZW50c20iLCJ1aWQiOiJGNXZZa043bFNNR0NHNGI0ekE2MFBBIiwic2siOiIyOTEwNTE0MjkwMjAwMDU2NDgzIiwic3R5IjoxMDAsIndjZCI6ImF3MSIsImV4cCI6MTY0MDAxMDA0NiwiaWF0IjoxNjQwMDAyODQ2LCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.OCAKXHIARajxiAZjDFHa9DxwbbNeO8Z-m5xBPNbTsoU', 'https://zoom.us/j/92238443776?pwd=MWZGM05DcXlvcVRRUGNMdE14R05qQT09', '2021-12-20 17:00:00', 60, 2, 'OK', NULL, '2021-12-20 12:19:30', '2021-12-21 10:01:07'),
(28, 99, 101, NULL, 'Yes', NULL, NULL, '', 'https://zoom.us/s/96326597663?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJ3ZWIiLCJjbHQiOjAsIm1udW0iOiI5NjMyNjU5NzY2MyIsInN0ayI6Im90NEtiV1RZTW1MVjFMS21US01aUTVSRkFjTkxGYnI0Tm9Qb29oY2NUTFEuQmdZZ1RTOHJaVnBLTjFwQ2FqRlBTRXc1YzBSR2RsZzBNMWR2U1ZGWE5sTkhWMHhBT0RjeU16bGlNbUZoWTJZNU9HTmpORFZoTVRJMU9HRTJOREJsWXpjMVltRXdNMlV6TlRGa1pqTXdObUprTmpJeE1EUmtNVE5tTmpnNFpHTTBZMlZsTWdBZ2RrTXZSVkpHYjJ4c05HOHpNVTVxVkRKWmVYQjRUR3huVjI0MVZWWTNOVThBQTJGM01RQUFBWDM4TG9QLUFCSjFBQUFBIiwiYXVkIjoiY2xpZW50c20iLCJ1aWQiOiJGNXZZa043bFNNR0NHNGI0ekE2MFBBIiwic2siOiIyOTEwNTE0MjkwMjAwMDU2NDgzIiwic3R5IjoxMDAsIndjZCI6ImF3MSIsImV4cCI6MTY0MDYyMDY0NiwiaWF0IjoxNjQwNjEzNDQ2LCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.btYmMpgdBi6cHK9bN9keXcQZYZGXHhSx1qv1LKrlRqw', 'https://zoom.us/j/96326597663?pwd=Y3FadWg4RUkwcFp3YTZpeFZCZUxJQT09', '2021-12-28 10:00:00', 60, 4, 'لا يوجد', 1, '2021-12-27 13:53:20', '2021-12-28 06:59:18'),
(29, 44, 101, NULL, 'Yes', NULL, NULL, '', 'https://zoom.us/s/95590820822?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJ3ZWIiLCJjbHQiOjAsIm1udW0iOiI5NTU5MDgyMDgyMiIsInN0ayI6InE3SWhzQTRxaWpublFhLTROTlZJOVZJa1hHZXRrelhaUmJyYnJXQkdrY3cuQmdZZ1RTOHJaVnBLTjFwQ2FqRlBTRXc1YzBSR2RsZzBNMWR2U1ZGWE5sTkhWMHhBT0RjeU16bGlNbUZoWTJZNU9HTmpORFZoTVRJMU9HRTJOREJsWXpjMVltRXdNMlV6TlRGa1pqTXdObUprTmpJeE1EUmtNVE5tTmpnNFpHTTBZMlZsTWdBZ2RrTXZSVkpHYjJ4c05HOHpNVTVxVkRKWmVYQjRUR3huVjI0MVZWWTNOVThBQTJGM01RQUFBWDM4TW1OR0FCSjFBQUFBIiwiYXVkIjoiY2xpZW50c20iLCJ1aWQiOiJGNXZZa043bFNNR0NHNGI0ekE2MFBBIiwic2siOiIyOTEwNTE0MjkwMjAwMDU2NDgzIiwic3R5IjoxMDAsIndjZCI6ImF3MSIsImV4cCI6MTY0MDYyMDkwMCwiaWF0IjoxNjQwNjEzNzAwLCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.AWxB58xtyInYeEcEgjzDUNu3VagxEprtXkKkawy-vAQ', 'https://zoom.us/j/95590820822?pwd=WTdBVzlHWjZMby8vakdodWdzTWZaUT09', '2021-12-27 15:00:00', 60, 0, 'اتلغت', 1, '2021-12-27 14:00:58', '2021-12-28 06:59:36'),
(30, 44, 101, NULL, 'No', NULL, NULL, '', NULL, NULL, '2021-12-30 14:25:00', 60, NULL, NULL, NULL, '2021-12-28 12:25:12', '2021-12-28 10:29:32'),
(31, 99, 101, NULL, 'No', NULL, NULL, '', NULL, NULL, '2021-12-31 14:29:00', 60, NULL, NULL, NULL, '2021-12-28 12:29:20', '2021-12-28 10:29:34'),
(32, 44, 101, NULL, 'No', NULL, NULL, '', NULL, NULL, '2021-12-30 14:46:00', 60, NULL, NULL, NULL, '2021-12-28 12:46:34', '2021-12-28 10:47:14');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(10) UNSIGNED NOT NULL,
  `menu_type` int(1) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ar_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_self',
  `order` int(11) NOT NULL,
  `user_added` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `menu_type`, `parent_id`, `title`, `ar_title`, `url`, `target`, `order`, `user_added`, `created_at`, `updated_at`) VALUES
(45, 2, 0, 'Privacy Policy', 'سياسة الخصوصية', '/page/privacy', '_self', 2, 1, NULL, NULL),
(46, 2, 0, 'Terms & Condition', 'شروط الاستخدام', '/page/terms', '_self', 3, 1, NULL, NULL),
(9, 1, 0, 'Google', 'جوجل', 'https://google.com', '_blank', 2, 1, NULL, NULL),
(8, 1, 0, 'Events', 'الفعاليات', '/events', '_self', 1, 1, NULL, NULL),
(2, 0, 0, 'About', 'عن الحاضنة', '/about', '_self', 2, 1, NULL, NULL),
(57, 0, 0, 'FAQs', 'الأسئلة المتكررة', '/faqs', '_self', 5, 1, NULL, NULL),
(5, 0, 1, 'About FEI', 'عن اتحاد الصناعات', '/about', '_self', 2, 1, NULL, NULL),
(6, 2, 0, 'Contact us', 'اتصل بنا', '/contact', '_self', 4, 1, NULL, NULL),
(44, 2, 0, 'Sitemap', 'خريطة الموقع', '/page/sitemap', '_self', 1, 1, NULL, NULL),
(10, 0, 0, 'News', 'أخبارنا', '/blog', '_self', 3, 1, NULL, NULL),
(49, 0, 0, 'Home', 'الرئيسية', '/', '_self', 1, 1, NULL, NULL),
(51, 0, 1, 'FEI Website', 'الموقع الرسمي للاتحاد', 'http://www.fei.org.eg/', '_self', 1, 1, NULL, NULL),
(52, 0, 0, 'Events', 'الفعاليات', '/events', '_self', 4, 1, NULL, NULL),
(56, 0, 0, 'Contact Us', 'اتصل بنا', '/contact', '_self', 6, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(13) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internal_id` int(11) DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read` bit(1) DEFAULT b'0',
  `userip` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `replyBy` int(11) DEFAULT NULL,
  `reSubject` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reMessage` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `internal_id`, `email`, `phone`, `subject`, `message`, `read`, `userip`, `user_id`, `replyBy`, `reSubject`, `reMessage`, `created_at`, `updated_at`, `deleted_at`) VALUES
(161, 'Raef Elsherbiny', NULL, 'elsherbinyraef@gmail.com', '01208888093', 'dfsdf wq', 'Trw wjkf nwdf nkjdwfn jkdnf wdf  wn jkfdwnl njldn wdlkjf;k l lknalsvnkajsdkvjasbdvklsd vbsadkvjbsadkjvl sd vsjkldb vkjasb dkjb sdklbv asjkdbv ksdv k jsabdvbsdkjvbskjdvbkj kjs dvjkasbd kjvb sd', b'1', '127.0.0.1', 1, 1, 'اهلا رائف', 'شكرا على رسالتك', '2021-09-24 19:49:31', '2021-12-21 16:26:48', NULL),
(159, 'CTQS Egypt', NULL, 'ctqs.egypt@gmail.com', '+201140770127', 'demo', 'demo', b'0', '127.0.0.1', 1, NULL, NULL, NULL, '2021-09-24 17:00:03', '2021-10-11 18:51:28', '2021-10-11 18:51:28'),
(162, 'Mohammed Ali', NULL, 'moz1212@gmail.com', '2423423423', 'Just text', 'Juest description', b'0', '::1', 1, NULL, NULL, NULL, '2021-09-24 20:02:34', '2021-10-11 18:52:20', '2021-10-11 18:52:20'),
(165, 'CTQS Egypt', NULL, 'ctqs.egypt@gmail.com', '+201140770127', 'demo', 'demo', b'1', '127.0.0.1', 0, NULL, NULL, NULL, '2021-09-24 17:00:03', '2021-10-11 18:49:28', '2021-10-11 18:49:28'),
(166, 'سامح خليفة - #(13)', NULL, 'trader.egypt@gmail.com', '01026744447', 'Good work', 'Just Good work', b'1', '::1', 1, NULL, NULL, NULL, '2021-10-11 16:32:04', '2021-12-21 16:34:12', '2021-12-21 16:34:12'),
(167, 'سامح خليفة - #(13)', NULL, 'trader.egypt@gmail.com', '01026744447', 'sure', 'sure', b'1', '162.158.159.51', 1, NULL, NULL, NULL, '2021-12-19 14:04:45', '2021-12-21 14:40:05', '2021-12-21 14:40:05'),
(168, 'Ibrahim Elsayed', NULL, 'ctqs.egypt@gmail.com', '+201140770127', 'demo', 'demo', b'1', '172.70.85.19', 1, NULL, NULL, NULL, '2021-12-19 16:50:32', '2021-12-20 12:40:21', '2021-12-20 12:40:21'),
(169, 'تريد مصر - #(97)', NULL, 'trader.egypt@gmail.com', '123456987', 'demo', 'demo', b'1', '162.158.50.24', 1, 1, 'رد على تريد', 'تم الرد عن طريق البريد الإلكتروني', '2021-12-20 07:49:12', '2021-12-21 16:34:09', '2021-12-21 16:34:09'),
(170, 'Ibrahim Elsayed', NULL, 'ctqs.egypt@gmail.com', '+201140770127', 'demo', 'demo', b'0', '172.70.162.221', 1, NULL, NULL, NULL, '2021-12-20 10:34:06', '2021-12-21 08:13:09', '2021-12-21 08:13:09'),
(171, 'Ibrahim Elsayed', NULL, 'ibrahim.suez@gmail.com', '01000415330', NULL, 'New', b'0', '::1', 1, NULL, NULL, NULL, '2021-12-21 07:48:49', '2021-12-21 07:51:49', '2021-12-21 07:51:49'),
(172, 'Ibrahim Elsayed -(1)', NULL, 'ibrahim.suez@gmail.com', '01000415330', 'Got SQL Error when installing the template', 'Got SQL Error when installing the template , Got SQL Error when installing the template', b'0', '::1', 1, NULL, NULL, NULL, '2021-12-21 07:53:26', '2021-12-21 08:13:13', '2021-12-21 08:13:13'),
(173, 'Ibrahim Elsayed - #(1)', NULL, 'ibrahim.suez@gmail.com', '01000415330', 'test', 'test', b'1', '::1', 1, NULL, NULL, NULL, '2021-12-21 09:23:16', '2021-12-21 14:31:15', '2021-12-21 14:31:15'),
(174, 'Hanna Saad - #(2)', NULL, 'hany@masterencode.com', '324564321', 'Got SQL Error when installing the template', 'check this error', b'0', '::1', 1, NULL, NULL, NULL, '2021-12-21 09:53:51', '2021-12-21 10:04:32', '2021-12-21 10:04:32'),
(175, 'Hanna Saad - #(2)', NULL, 'hany@masterencode.com', '324564321', 'Got SQL Error when installing the template', 'just test', b'0', '::1', 1, NULL, NULL, NULL, '2021-12-21 10:03:51', '2021-12-21 10:04:30', '2021-12-21 10:04:30'),
(176, 'Hanna Saad - #(2)', NULL, 'hany@masterencode.com', '324564321', 'd', 'dd', b'1', '::1', 1, NULL, NULL, NULL, '2021-12-21 10:04:22', '2021-12-21 10:04:36', '2021-12-21 10:04:36'),
(177, 'Ibrahim Elsayed', 1, 'ibrahim.suez@gmail.com', '01000415330', 'demo', 'demo from contact us', b'1', '::1', 1, 1, 'شكرا', 'سيتم المتابعة معك', '2021-12-21 15:14:50', '2021-12-21 15:46:28', NULL),
(178, 'Ibrahim Elsayed', NULL, 'ctqs.egypt@gmail.com', '+201140770127', 'demo', 'demo', b'1', '::1', 1, NULL, NULL, NULL, '2021-12-21 15:16:11', '2021-12-22 06:04:30', NULL),
(179, 'ابراهيم السيد', 1, 'ibrahim.suez@gmail.com', '01000415330', 'رسالة بالعربي', 'نص الرسالة', b'1', '::1', 1, 1, 'جميل', 'ان شاء الله', '2021-12-21 15:43:19', '2021-12-22 07:21:39', NULL),
(180, 'Ibrahim Elsayed', 1, 'ibrahim.suez@gmail.com', '01000415330', 'Thanks for registration', 'Thanks message', b'1', '::1', 1, NULL, NULL, NULL, '2021-12-21 16:27:18', '2021-12-21 16:34:04', '2021-12-21 16:34:04'),
(181, 'Ibrahim Elsayed', 1, 'ibrahim.suez@gmail.com', '01000415330', 'demo', 'demo', b'1', '::1', 1, NULL, NULL, NULL, '2021-12-21 16:27:57', '2021-12-21 16:34:07', '2021-12-21 16:34:07'),
(182, 'Ibrahim Elsayed', 1, 'ibrahim.suez@gmail.com', '01000415330', 'Thanks for registration', 'Thanks', b'1', '::1', 1, NULL, NULL, NULL, '2021-12-21 16:33:22', '2021-12-21 16:34:02', '2021-12-21 16:34:02');

-- --------------------------------------------------------

--
-- Table structure for table `messenger`
--

CREATE TABLE `messenger` (
  `id` int(15) UNSIGNED NOT NULL,
  `sender` int(10) NOT NULL,
  `receiver` int(10) NOT NULL,
  `messgeId` int(15) DEFAULT NULL,
  `subject` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read` tinyint(1) DEFAULT NULL,
  `reported` int(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `messenger`
--

INSERT INTO `messenger` (`id`, `sender`, `receiver`, `messgeId`, `subject`, `message`, `read`, `reported`, `created_at`, `updated_at`, `deleted_at`) VALUES
(69, 1, 15, NULL, 'Thanks for registration', 'Complete registration form to enable your account', 0, NULL, '2021-10-02 21:33:45', '2021-10-11 17:45:57', '2021-10-11 17:45:57'),
(66, 1, 1, NULL, 'Erro', 'Got SQL Error when installing the template', 0, NULL, '2021-09-24 20:39:32', '2021-10-11 02:26:59', '2021-09-24 20:45:17'),
(67, 1, 1, 66, 'Reply to: Erro', 'Thanks', 0, NULL, '2021-09-24 20:44:12', '2021-10-11 02:26:59', '2021-09-24 20:44:52'),
(68, 1, 1, 66, 'Reply to: Erro', 'thanks', 0, NULL, '2021-09-24 20:45:11', '2021-10-11 02:26:59', '2021-09-24 20:50:07'),
(70, 15, 1, 69, 'Reply to: Thanks for registration', 'Ok', 1, NULL, '2021-10-02 21:36:12', '2021-10-11 00:32:45', NULL),
(71, 1, 15, 70, 'Reply to:  Thanks for registration', 'I got it', 0, NULL, '2021-10-02 22:42:09', '2021-10-11 02:26:59', '2021-10-04 17:43:19'),
(73, 1, 15, 70, 'Reply to:  Thanks for registration', 'ok', NULL, NULL, '2021-10-11 00:34:32', '2021-10-11 00:34:32', NULL),
(72, 15, 1, 71, 'Reply to:   Thanks for registration', 'وماله', 1, NULL, '2021-10-02 22:42:42', '2021-10-11 00:27:10', NULL),
(74, 1, 15, 70, 'Reply to:  Thanks for registration', 'Thanks Kadry', NULL, NULL, '2021-10-11 16:02:09', '2021-10-11 16:02:09', NULL),
(75, 1, 13, NULL, 'Welcome to VI', 'Please complete your profile to activate your account', 1, NULL, '2021-10-11 16:04:05', '2021-10-11 17:41:35', '2021-10-11 17:41:35'),
(76, 13, 1, 75, 'Reply to: Welcome to VI', 'I did, Admin', 1, NULL, '2021-10-11 16:10:51', '2021-10-11 16:28:27', NULL),
(77, 1, 13, 76, 'Reply to:  Welcome to VI', 'Good Luck', 1, NULL, '2021-10-11 16:28:47', '2021-10-11 16:29:28', NULL),
(78, 13, 1, 77, 'Reply to:   Welcome to VI', 'More \"Try it Yourself\" examples below.\r\nDefinition and Usage\r\nThe charCodeAt() method returns the Unicode of the character at the specified index in a string.\r\nThe index of the first character is 0, the second character 1, and so on.\r\nTip: You can use the charCodeAt() method together with the length property to return the Unicode of the last character in a string. The index of the last character is -1, the second last character is -2, and so on (See Example below).\r\nTip: For more information about Unicode Character Sets, visit our HTML Character Sets reference.\r\nBrowser Support\r\ncharCodeAt() is fully supported in all browsers:\r\nChrome	IE	Edge	Firefox	Safari	Opera\r\nYes	Yes	Yes	Yes	Yes	Yes\r\nSyntax\r\nstring.charCodeAt(index)\r\nParameter Values\r\nParameter	Description\r\nindex	Optional. A number representing the index of the character you want to return. If not specified, default value is 0\r\nADVERTISEMENT\r\n\r\nTechnical Details\r\nReturn Value:	A Number, representing the unicode of the character at the specified index.\r\n\r\nNote: This method returns \"NaN\" if there is no character at the specified index, or if the index is less than \"0\".\r\nJavaScript Version:	ECMAScript 1\r\nMore Examples\r\nExample\r\nReturn the Unicode of the last character in a string (the Unicode value for \"D\"):\r\n\r\nlet str = \"HELLO WORLD\";\r\nstr.charCodeAt(str.length-1)   // Returns 68\r\nRelated Pages\r\nString Tutorial\r\nString Methods\r\nString Search', 1, NULL, '2021-10-11 16:35:07', '2021-10-11 19:38:09', NULL),
(79, 13, 1, 75, 'Reply to: Welcome to VI', 'More \"Try it Yourself\" examples below. Definition and Usage The charCodeAt() method returns the Unicode of the character at the specified index in a string. The index of the first character is 0, the second character 1, and so on. Tip: You can use the charCodeAt() method together with the length property to return the Unicode of the last character in a string. The index of the last character is -1, the second last character is -2, and so on (See Example below). Tip: For more information about Unicode Character Sets, visit our HTML Character Sets reference. Browser Support charCodeAt() is fully supported in all browsers: Chrome IE Edge Firefox Safari Opera Yes Yes Yes Yes Yes Yes Syntax string.charCodeAt(index) Parameter Values Parameter Description index Optional. A number representing the index of the character you want to return. If not specified, default value is 0 ADVERTISEMENT Technical Details Return Value: A Number, representing the unicode of the character at the specified index. Note: This method returns \"NaN\" if there is no character at the specified index, or if the index is less than \"0\". JavaScript Version: ECMAScript 1 More Examples Example Return the Unicode of the last character in a string (the Unicode value for \"D\"): let str = \"HELLO WORLD\"; str.charCodeAt(str.length-1) // Returns 68 Related Pages String Tutorial String Methods String Search\r\nMore \"Try it Yourself\" examples below. Definition and Usage The charCodeAt() method returns the Unicode of the character at the specified index in a string. The index of the first character is 0, the second character 1, and so on. Tip: You can use the charCodeAt() method together with the length property to return the Unicode of the last character in a string. The index of the last character is -1, the second last character is -2, and so on (See Example below). Tip: For more information about Unicode Character Sets, visit our HTML Character Sets reference. Browser Support charCodeAt() is fully supported in all browsers: Chrome IE Edge Firefox Safari Opera Yes Yes Yes Yes Yes Yes Syntax string.charCodeAt(index) Parameter Values Parameter Description index Optional. A number representing the index of the character you want to return. If not specified, default value is 0 ADVERTISEMENT Technical Details Return Value: A Number, representing the unicode of the character at the specified index. Note: This method returns \"NaN\" if there is no character at the specified index, or if the index is less than \"0\". JavaScript Version: ECMAScript 1 More Examples Example Return the Unicode of the last character in a string (the Unicode value for \"D\"): let str = \"HELLO WORLD\"; str.charCodeAt(str.length-1) // Returns 68 Related Pages String Tutorial String Methods String Search\r\nMore \"Try it Yourself\" examples below. Definition and Usage The charCodeAt() method returns the Unicode of the character at the specified index in a string. The index of the first character is 0, the second character 1, and so on. Tip: You can use the charCodeAt() method together with the length property to return the Unicode of the last character in a string. The index of the last character is -1, the second last character is -2, and so on (See Example below). Tip: For more information about Unicode Character Sets, visit our HTML Character Sets reference. Browser Support charCodeAt() is fully supported in all browsers: Chrome IE Edge Firefox Safari Opera Yes Yes Yes Yes Yes Yes Syntax string.charCodeAt(index) Parameter Values Parameter Description index Optional. A number representing the index of the character you want to return. If not specified, default value is 0 ADVERTISEMENT Technical Details Return Value: A Number, representing the unicode of the character at the specified index. Note: This method returns \"NaN\" if there is no character at the specified index, or if the index is less than \"0\". JavaScript Version: ECMAScript 1 More Examples Example Return the Unicode of the last character in a string (the Unicode value for \"D\"): let str = \"HELLO WORLD\"; str.charCodeAt(str.length-1) // Returns 68 Related Pages String Tutorial String Methods String Search\r\nMore \"Try it Yourself\" examples below. Definition and Usage The charCodeAt() method returns the Unicode of the character at the specified index in a string. The index of the first character is 0, the second character 1, and so on. Tip: You can use the charCodeAt() method together with the length property to return the Unicode of the last character in a string. The index of the last character is -1, the second last character is -2, and so on (See Example below). Tip: For more information about Unicode Character Sets, visit our HTML Character Sets reference. Browser Support charCodeAt() is fully supported in all browsers: Chrome IE Edge Firefox Safari Opera Yes Yes Yes Yes Yes Yes Syntax string.charCodeAt(index) Parameter Values Parameter Description index Optional. A number representing the index of the character you want to return. If not specified, default value is 0 ADVERTISEMENT Technical Details Return Value: A Number, representing the unicode of the character at the specified index. Note: This method returns \"NaN\" if there is no character at the specified index, or if the index is less than \"0\". JavaScript Version: ECMAScript 1 More Examples Example Return the Unicode of the last character in a string (the Unicode value for \"D\"): let str = \"HELLO WORLD\"; str.charCodeAt(str.length-1) // Returns 68 Related Pages String Tutorial String Methods String Search\r\nMore \"Try it Yourself\" examples below. Definition and Usage The charCodeAt() method returns the Unicode of the character at the specified index in a string. The index of the first character is 0, the second character 1, and so on. Tip: You can use the charCodeAt() method together with the length property to return the Unicode of the last character in a string. The index of the last character is -1, the second last character is -2, and so on (See Example below). Tip: For more information about Unicode Character Sets, visit our HTML Character Sets reference. Browser Support charCodeAt() is fully supported in all browsers: Chrome IE Edge Firefox Safari Opera Yes Yes Yes Yes Yes Yes Syntax string.charCodeAt(index) Parameter Values Parameter Description index Optional. A number representing the index of the character you want to return. If not specified, default value is 0 ADVERTISEMENT Technical Details Return Value: A Number, representing the unicode of the character at the specified index. Note: This method returns \"NaN\" if there is no character at the specified index, or if the index is less than \"0\". JavaScript Version: ECMAScript 1 More Examples Example Return the Unicode of the last character in a string (the Unicode value for \"D\"): let str = \"HELLO WORLD\"; str.charCodeAt(str.length-1) // Returns 68 Related Pages String Tutorial String Methods String Search', 1, NULL, '2021-10-11 16:36:44', '2021-10-11 16:36:51', NULL),
(80, 1, 13, 79, 'Reply to:  Welcome to VI', 'Eaaaaaaaaaaah kol dah', 1, NULL, '2021-10-11 16:37:10', '2021-10-11 17:40:37', '2021-10-11 17:40:37'),
(81, 1, 86, NULL, 'Thanks for registration', 'you need to add your company details', 1, NULL, '2021-10-21 22:20:24', '2021-10-21 22:21:36', NULL),
(82, 86, 1, 81, 'Reply to: Thanks for registration', 'ok', 1, NULL, '2021-10-22 00:25:19', '2021-10-24 15:52:32', NULL),
(83, 1, 1, NULL, 'Your request - تسويق إلكترونى', 'هنرد عليك الاسبوع الجاي', 1, NULL, '2021-11-25 08:36:59', '2021-11-25 08:37:21', NULL),
(84, 1, 1, 83, 'Reply to: Your request - تسويق إلكترونى', 'Thanks', 1, NULL, '2021-11-25 08:37:44', '2021-11-25 08:37:49', NULL),
(85, 1, 13, NULL, 'Your request - تحليل مالى', 'this service not available today, I\'ll check it tomorow', 1, NULL, '2021-11-25 09:36:50', '2021-11-25 09:37:03', NULL),
(86, 13, 86, NULL, 'Demo', 'Demo message', 1, NULL, '2021-11-29 08:22:00', '2021-11-29 08:33:11', NULL),
(87, 86, 13, 86, 'Reply to: Demo', 'Thanks', 1, NULL, '2021-11-29 08:33:23', '2021-11-29 08:34:37', NULL),
(88, 1, 2, NULL, 'Thanks for registration', 'good luck', 1, NULL, '2021-12-04 10:22:06', '2021-12-04 10:22:17', NULL),
(89, 1, 13, 79, 'Reply to:  Welcome to VI', 'thanks', 1, NULL, '2021-12-19 09:47:28', '2021-12-19 13:45:12', NULL),
(90, 13, 1, 89, 'Reply to:   Welcome to VI', 'Thanks again', 1, NULL, '2021-12-19 13:45:18', '2021-12-19 13:56:02', NULL),
(91, 13, 1, 89, 'Reply to:   Welcome to VI', 'OK', 1, NULL, '2021-12-19 13:48:42', '2021-12-19 13:56:01', NULL),
(92, 13, 1, 89, 'Reply to:   Welcome to VI', 'Ok', 1, NULL, '2021-12-19 13:52:12', '2021-12-19 13:55:59', NULL),
(93, 13, 1, 89, 'Reply to:   Welcome to VI', 'OK', 1, NULL, '2021-12-19 13:55:14', '2021-12-19 13:55:55', NULL),
(94, 13, 1, 89, 'Reply to:   Welcome to VI', 'DONE', 1, NULL, '2021-12-19 14:02:44', '2021-12-20 10:14:10', NULL),
(95, 13, 1, 89, 'Reply to:   Welcome to VI', 'DONE', 1, NULL, '2021-12-19 14:02:57', '2021-12-20 10:14:05', NULL),
(96, 1, 13, NULL, 'Hi sameh and Kadry', 'Thanks for joining', NULL, NULL, '2021-12-21 09:33:15', '2021-12-21 09:33:15', NULL),
(97, 1, 15, NULL, 'Hi sameh and Kadry', 'Thanks for joining', NULL, NULL, '2021-12-21 09:33:15', '2021-12-21 09:33:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2020_02_08_103159_create_service_categories_table', 2),
(4, '2020_02_08_103300_create_services_table', 2),
(5, '2020_02_08_103741_create_types_table', 2),
(6, '2020_02_08_103820_create_states_table', 2),
(7, '2020_02_08_103821_create_companies_table', 2),
(8, '2020_02_08_113812_create_permission_tables', 3),
(9, '2020_02_08_103819_create_cities_table', 4),
(10, '2020_02_13_163708_create_company_service_table', 5),
(11, '2020_03_11_070257_create_failed_jobs_table', 6),
(12, '2020_03_18_215145_create_notifications_table', 7);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`model_id`, `role_id`, `model_type`) VALUES
(19, 3, 'App\\User'),
(25, 3, 'App\\User'),
(26, 3, 'App\\User'),
(30, 3, 'App\\User'),
(33, 3, 'App\\User'),
(34, 3, 'App\\User'),
(35, 3, 'App\\User'),
(37, 3, 'App\\User'),
(39, 3, 'App\\User'),
(42, 3, 'App\\User'),
(43, 3, 'App\\User'),
(47, 3, 'App\\User'),
(51, 3, 'App\\User'),
(49, 3, 'App\\User'),
(50, 3, 'App\\User'),
(27, 3, 'App\\User'),
(52, 3, 'App\\User'),
(55, 3, 'App\\User'),
(58, 3, 'App\\User'),
(67, 3, 'App\\User'),
(69, 3, 'App\\User'),
(84, 3, 'App\\User'),
(38, 3, 'App\\User'),
(1, 1, 'App\\User'),
(86, 8, 'App\\User'),
(87, 8, 'App\\User'),
(89, 8, 'App\\User'),
(90, 8, 'App\\User'),
(15, 7, 'App\\User'),
(3, 7, 'App\\User'),
(8, 7, 'App\\User'),
(31, 7, 'App\\User'),
(44, 6, 'App\\User'),
(46, 6, 'App\\User'),
(2, 8, 'App\\User'),
(92, 8, 'App\\User'),
(95, 8, 'App\\User'),
(96, 8, 'App\\User'),
(13, 7, 'App\\User'),
(98, 8, 'App\\User'),
(53, 5, 'App\\User'),
(99, 6, 'App\\User'),
(100, 7, 'App\\User'),
(101, 8, 'App\\User'),
(97, 8, 'App\\User'),
(45, 6, 'App\\User'),
(102, 5, 'App\\User'),
(10, 7, 'App\\User'),
(103, 1, 'App\\User'),
(104, 8, 'App\\User');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `url` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ar_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ar_body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `published` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `url`, `image`, `title`, `ar_title`, `body`, `ar_body`, `published`, `user_id`, `created_at`, `updated_at`) VALUES
(2, 'privacy', 'uDrW74VzdviISg17Ls8S2021-09-25page.jpg', 'Privacy', 'سياسة الخصوصية', '<h3>Terms and Conditions (&quot;Privacy Policy&quot;)<br />\r\n&nbsp;</h3>\r\n\r\n<p><strong>1. Data Confidentiality:</strong> We are committed to maintaining the privacy of all site users. This privacy policy regulates all the pages of the site, and does not apply to the pages it hosts or links that appear on some pages to other companies, each of them has privacy policies related to them and applies to their sites, and your use of the site means that you accept the provisions contained in this privacy policy. We may change the provisions of this policy from time to time, and if any changes are made, the site may display notifications for the user to be aware of them. The user&rsquo;s continued use of the Authority&rsquo;s websites is based on the conditions stated in this Privacy Policy as it is modified from time to time.</p>\r\n\r\n<p><strong>2. Types of information that the site collects:</strong> We do not keep any personal information for our visitors, we may sometimes reserve some general data, and this information is used to develop aggregate statistics, such as the times when the site is accessed, the pages that refer visitors to it, and the type of electronic browsing tools that Used by visitors, and viewed pages. These statistics will help us understand the nature of the use of the site and improve our services.</p>\r\n\r\n<p><strong>3. Use of cookies:</strong> A cookie is a text-only information that a website transfers to a user&rsquo;s computer&rsquo;s hard disk so that the website can recognize its identity. The cookie usually includes the name of the internet link from which the file was received and its &quot;age&quot; in addition to a quantitative value, usually a unique number that is randomly assigned. They are used on the site and are known as &ldquo;staging cookies&rdquo;, which are temporary files that are stored in your device&rsquo;s memory and deleted as soon as your web browser is closed or after a period of inactivity. These cookies can control the behavior of the windows that appear automatically to ensure that they do not appear every time the user visits the page in question. Alternatively, a cookie may be placed on the user&#39;s computer once he visits a page that contains an automatic window, to ensure that the window does not appear the next time the page is visited.</p>\r\n\r\n<p><strong>4. Legal Responsibility:</strong> The user acknowledges that he is solely responsible for the nature of the use determined by the site, and we release our party, to the fullest extent permitted by law, from full liability for any direct or indirect losses, damages, expenses, or expenses that the user incurs or is exposed to. Or any other party as a result of entering the site, as well as from any contact with other websites from the site, or relying on any information contained therein, whether by negligence, error, breach of contract or otherwise, as it arises. Under no circumstances do we bear any liability for private or consequential damages.</p>\r\n\r\n<p><strong>5. Interruptions and omissions in the site: </strong>The team displays a reasonable amount of care to maintain the continuity of the site well, but the Internet is not a stable means in all cases, and errors and omissions, interruptions to the service and delay thereof may occur at any time. We have no ongoing responsibility or responsibility to operate the site.</p>\r\n\r\n<p><strong>6. Applicable law:</strong> These terms of use are subject to Egyptian law, and Egyptian courts alone have exclusive jurisdiction over any disputes arising out of the use of the site. In the event that any part of the conditions of use is invalid or cannot be implemented, this does not affect the rest of the terms of use and does not invalidate or nullify them.</p>', '<h3>الأحكام والشروط&nbsp;(&ldquo;سياسة الخصوصية&rdquo;)<br />\r\n&nbsp;</h3>\r\n\r\n<p><strong>1. سرية البيانات: </strong>نلتزم&nbsp; بالحفاظ على خصوصيات جميع مستخدمي الموقع. وتنظم سياسة الخصوصية هذه جميع صفحات الموقع، ولا تنطبق على الصفحات التي تستضيفها أو الروابط التي تظهر في بعض&nbsp;الصفحات لشركات أخرى، فلكل منها سياسات خصوصية متعلقة بها وتنطبق على مواقعها، ويعني استخدامك للموقع&nbsp;أنك تقبل الأحكام الواردة في سياسة الخصوصية هذه. ويجوز لنا تغيير أحكام هذه السياسة من حين لآخر، وفي حالة إجراء أية تغييرات، قد يعرض&nbsp;الموقع إخطارات ليكون المُستخدِم على علم بها. ويكون استمرار المُستخدِم في استعمال المواقع الإلكترونية للهيئة على أساس الشروط الواردة في سياسة الخصوصية هذه حسبما يجري تعديلها من وقت لآخر.</p>\r\n\r\n<p><strong>2. أنواع المعلومات التي يجمعها الموقع:</strong> لا نحتفظ بأي معلومات شخصية لزوارنا، ربما نحتفظ أحيانا ببعض البيانات العامة،&nbsp;ويُستعان بهذه المعلومات لوضع إحصائيات إجمالية، من قبيل الأوقات التي يتم فيها الدخول إلى الموقع، والصفحات التي تُحيل الزوار إليه، ونوع أدوات التصفح الإلكتروني التي يستعملها الزوار، والصفحات التي تُطالع. ومن شأن هذه الإحصائيات أن تساعدنا&nbsp;على فهم طبيعة استخدام الموقع&nbsp;وتحسين خدماتنا.&nbsp;</p>\r\n\r\n<p><strong>3. استخدام ملفات تعريف الارتباط cookies: </strong>ملف تعريف الارتباط هو عبارة عن معلومة نصية فقط ينقلها الموقع الإلكتروني إلى القرص الصلب في جهاز الحاسب الخاص بالمُستخدِم حتى يستطيع الموقع الإلكتروني أن يتعرف على هويته. وعادةً ما يتضمن ملف تعريف الارتباط اسم رابط الإنترنت الذي ورد منه الملف و&rdquo;عمره&rdquo; بالإضافة إلى قيمة كمية، عادةً ما تكون رقماً مميزاً يحدَّد بصورة عشوائية. وتُستخدم في الموقع وتُعرَف باسم &ldquo;ملفات تعريف الارتباط المرحلية&rdquo;، وهي ملفات مؤقتة تُخزن في ذاكرة جهازكوتُحذف بمجرد إغلاق المتصفح الإلكتروني الخاص بك&nbsp;أو بعد فترة من انعدام النشاط. ويمكن لملفات تعريف الارتباط هذه التحكم بسلوك النوافذ التي تظهر تلقائياً للتأكد من عدم ظهورها في كل مرة يزور فيها المُستخدِم الصفحة المعنية. وعوضاً عن ذلك، قد يُوضع في جهاز الحاسوب الخاص بالمُستخدِم أحد ملفات تعريف الارتباط بمجرد أن يزور صفحة تحتوي على نافذة تلقائية الظهور، وذلك للتأكد من عدم ظهور النافذة عند زيارة الصفحة في المرة القادمة.</p>\r\n\r\n<p><strong>4. المسؤولية القانونية: </strong>يقر المُستخدِم بأنه المسئول الوحيد عن طبيعة الاستخدام الذي يحدده للموقع، ونخلي طرفنا، إلى أقصى مدى يجيزه القانون، من كامل المسؤولية عن أية خسائر أو أضرار أو نفقات أو مصاريف، مباشرة أو غير مباشرة، يتكبدها المُستخدِم أو يتعرض لها هو أو أي طرف آخر من جراء الدخول إلى الموقع،&nbsp;وكذلك من جراء أي اتصال بمواقع إلكترونية أخرى انطلاقاً من الموقع، أو الاعتماد على أية معلومات ترد فيها، سواء عن طريق الإهمال أو الخطأ أو الإخلال بعقد أو خلافه كيفما نشأ. ولا نتحمل تحت أي ظرف من الظروف أية مسؤولية قانونية عن الأضرار الخاصة أو الناجمة عما سبق.</p>\r\n\r\n<p><strong>5. حالات الانقطاع والسهو في الموقع:</strong>&nbsp;يبدي فريق العمل&nbsp;قدراً معقولاً من الحرص للحفاظ على استمرار الموقع بشكل جيد، إلا إن شبكة الإنترنت ليست وسيلة مستقرة في كل الأحوال، وقد تقع في أي وقت أخطاء وحالات سهو وانقطاع للخدمة وتأخير لها. ولا يقع على عاتقنا أي التزام أو مسؤولية بصفة مستمرة لتشغيل الموقع.</p>\r\n\r\n<p><strong>6. القانون المعمول به:</strong> تخضع شروط الاستخدام هذه للقانون المصري، وتتمتع المحاكم المصرية وحدها دون غيرها بالولاية القضائية فيما يتعلق بأي نزاعات ناجمة عن استخدام الموقع. وفي حالة بطلان أي جزء من شروط الاستخدام أو عدم إمكان تنفيذه، فإن ذلك لا يؤثر على بقية شروط الاستخدام ولا يعطلها ولا يبطلها.</p>', 'on', 1, NULL, '2021-10-04 13:40:47'),
(1, 'sitemap', 'H8GsuKyLnHYkFElZwnAI2021-09-25page.jpg', 'Site Map', 'خريطة الموقع', '<blockquote>\r\n<ol>\r\n	<li><strong>Home page</strong></li>\r\n	<li><strong>About VI</strong></li>\r\n	<li><strong>Our services</strong></li>\r\n	<li><strong>Events</strong></li>\r\n	<li><strong>News</strong></li>\r\n	<li><strong>FAQs</strong></li>\r\n	<li><strong>Contact us</strong></li>\r\n</ol>\r\n</blockquote>', '<ol>\r\n	<li><strong>الرئيسية</strong></li>\r\n	<li><strong>عن الحاضنة</strong></li>\r\n	<li><strong>خدماتنا</strong></li>\r\n	<li><strong>أخبارنا</strong></li>\r\n	<li><strong>الفعاليات</strong></li>\r\n	<li><strong>الأسئلة المتكررة</strong></li>\r\n	<li><strong>اتصل بنا</strong></li>\r\n</ol>', 'on', 1, NULL, '2021-10-11 19:55:56'),
(8, 'terms', 'B9X7k2020-02-17....jpg', 'Terms and Conditions', 'شروط الاستخدام', '<p>Terms &amp; Conditions</p>', '<p>شروط الاستخدام</p>', 'on', 1, '2020-02-17 14:05:09', '2021-10-04 13:44:57'),
(9, 'tree', 'aVr8i2021-10-04....jpg', 'How to start a technology business?', 'كيف تبدأ مشروعك التكنولوجي الجديد؟', '<p>demo</p>', '<p>demo page</p>', NULL, 1, '2021-10-04 13:48:58', '2021-10-05 17:12:49');

-- --------------------------------------------------------

--
-- Table structure for table `partners`
--

CREATE TABLE `partners` (
  `id` int(11) NOT NULL,
  `ar_title` varchar(250) NOT NULL,
  `en_title` varchar(255) NOT NULL,
  `img_url` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `user_added` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `partners`
--

INSERT INTO `partners` (`id`, `ar_title`, `en_title`, `img_url`, `url`, `order`, `user_added`, `created_at`, `updated_at`) VALUES
(3, 'USAID', 'USAID', 'partners/Tulw2OQ7pvWZwabtRiGhWJpMsR7uBdFc14vyVare.png', 'https://www.usaid.gov/', 2, 1, '2020-02-16 17:29:50', '2021-10-01 10:23:47'),
(4, 'وزارة التجارة والصناعة', 'MTI', 'partners/YQFfUC4sR1Fx8KU5E5L9JkEY434ePyiffewN28Yg.jpeg', 'http://mti.gov.eg', 1, 1, '2020-02-16 17:44:54', '2020-02-17 19:37:43'),
(5, 'أورانج مصر', 'Orange Egypt', 'partners/jGKN8iI7j4rcj3rThraQXeO4NK5SQ9gVIqkbQkfy.jpg', 'http://orange.eg', 3, 1, '2021-10-01 10:20:27', '2021-10-01 10:20:27'),
(6, 'توشيبا العربي', 'Toshiba Elaraby', 'partners/NoRiaOQW6zsLLadj8OtrdEN21MBj8EBS7g1yhpDt.png', 'https://www.elarabygroup.com/', 4, 1, '2021-10-01 10:21:29', '2021-10-01 10:21:29'),
(7, 'المصرية للإتصالات', 'We', 'partners/RyF1BIEKc8B0v27vj73czJwwGrLoQF9SefmOPsV0.jpg', 'http://we.eg', 5, 1, '2021-10-01 10:22:27', '2021-10-01 10:22:27'),
(8, 'وزارة التخطيط والتنمية الاقتصادية', 'MPED', 'partners/Vf0BBlAVmMUW92hZyXvum6sasX5bGcvWmqfzPDq1.jpg', 'https://mped.gov.eg/', 6, 1, '2021-10-01 10:23:26', '2021-10-01 10:23:26');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('ibrahim@nctu.edu.eg', '$2y$10$v4rC8kyLP/dFuLve9HesP.8JMXWO.rjQX0ZD8YMSjjjEL/M7pNWkO', '2021-10-22 17:10:15'),
('ibrahim.suez@gmail.com', '$2y$10$q/6CuB7kp/b3NsjRQ/r3F.QEnMn1UCfhDnW8JX8/Eg7c.cZ/.vkF6', '2021-11-08 09:35:53');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'add_service_category', 'web', '2020-02-07 22:00:00', '2021-09-30 20:00:42'),
(2, 'edit_service_category', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(3, 'delete_service_category', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(4, 'view_service_category', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(5, 'add_service', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(6, 'edit_service', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(7, 'delete_service', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(8, 'view_service', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(9, 'add_type', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(10, 'edit_type', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(11, 'delete_type', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(12, 'view_type', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(13, 'add_slide', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(14, 'edit_slide', 'web', '2020-02-08 05:00:00', '2021-10-08 10:05:35'),
(15, 'delete_slide', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(16, 'view_slide', 'web', '2020-02-08 05:00:00', '2021-10-08 10:04:57'),
(17, 'edit_settings', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(18, 'view_messages', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(19, 'delete_messages', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(20, 'view_partner', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(21, 'add_partner', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(22, 'edit_partner', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(23, 'delete_partner', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(24, 'view_press', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(25, 'add_press', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(26, 'edit_press', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(27, 'delete_press', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(28, 'add_user', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(29, 'edit_user', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(30, 'delete_user', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(31, 'view_user', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(32, 'view_report', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(33, 'manage_products_cat', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(34, 'manage_pages', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(35, 'manage_posts', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(36, 'manage_menus', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(37, 'manage_events', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(38, 'manage_page', 'web', '2020-02-08 05:00:00', '2020-02-08 05:00:00'),
(40, 'edit_permissions', 'web', '2021-09-30 20:22:51', '2021-09-30 20:24:58'),
(41, 'view_permissions', 'web', '2021-09-30 20:25:11', '2021-09-30 20:25:11'),
(42, 'view_log', 'web', NULL, '2021-09-30 21:02:15'),
(44, 'view_faqs', 'web', '2021-10-01 14:21:45', '2021-10-01 14:21:45'),
(45, 'edit_faqs', 'web', '2021-10-01 14:21:58', '2021-10-01 14:21:58'),
(46, 'view_calendar', 'web', '2021-10-02 00:04:24', '2021-10-02 00:04:24'),
(47, 'edit_calendar', 'web', '2021-10-02 00:04:31', '2021-10-02 00:04:31'),
(48, 'manage_log', 'web', '2021-10-05 12:48:41', '2021-10-05 12:48:41'),
(49, 'view_forms', 'web', '2021-10-09 12:18:15', '2021-10-09 12:18:15'),
(50, 'edit_forms', 'web', '2021-10-09 12:18:24', '2021-10-09 12:18:24'),
(51, 'view_cycles', 'web', '2021-10-26 17:13:05', '2021-10-26 17:13:05'),
(52, 'edit_cycles', 'web', '2021-10-26 17:13:18', '2021-10-26 17:13:18'),
(53, 'view_allfiles', 'web', '2021-11-02 16:56:05', '2021-11-02 16:56:05'),
(54, 'manage_myfiles', 'web', '2021-11-13 13:48:05', '2021-11-13 13:48:05'),
(55, 'manage_roles', 'web', '2021-12-31 15:45:05', '2021-12-31 15:45:05'),
(57, 'view_roles', 'web', '2021-12-31 15:48:40', '2021-12-31 15:48:40'),
(58, 'view_documentation', 'web', '2022-01-01 10:05:23', '2022-01-01 10:05:23'),
(59, 'manage_documentation', 'web', '2022-01-01 10:08:27', '2022-01-01 10:08:27');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ar_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ar_body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `published` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `image`, `title`, `ar_title`, `body`, `ar_body`, `published`, `user_id`, `created_at`, `updated_at`) VALUES
(2, 'lcHxB2021-09-24post.jpg', '3 winners of innovative entrepreneurs qualify for competition in the \"Orange OSVP\"', '3 فائزين من رواد الأعمال المبتكرين يتأهلون للمنافسة  في \"أورنج OSVP\"', '<p>Orange Egypt announced the winning teams for the first three places of the local version of its Orange Social Venture Prize (OSVP) competition in its eleventh session for the year 2021.</p>\r\n\r\n<p>It is one of the most important competitions in Egypt and the region to support entrepreneurs and emerging technology entrepreneurs that provide smart, technical and modern solutions to societal problems, and qualify them to compete in the final round of the Orange Social Venture Prize Africa and the Middle East, with a total prize of 50,000 euros.</p>\r\n\r\n<p>The local finals were held via advanced video conferencing technology, during which 3 innovative Egyptian entrepreneurs were selected and awarded with financial prizes, as well as qualified to compete in the final round of the international competition.</p>\r\n\r\n<p>The company announced the winning projects in the finals, namely Medfast, Zr3i.COM and Instadeel, where the Medfast project won first place, after presenting a medical application based on the Global Positioning System (GPS) for the purpose of providing home medical services through reputable and accredited institutions and destinations.</p>\r\n\r\n<p>The Zr3i.com project won second place after developing a collaborative platform based on SAAS technology to exploit digital transformation technologies that focus on using advanced information technology to improve the lives of farmers. And start-up companies to find resources and opportunities that support their businesses, which helps the development of society.</p>', '<p>أعلنت اورنچ مصر عن الفرق الفائزة بالمراكز الثلاثة الأولى من النسخة المحلية لمسابقتها Orange Social Venture Prize &nbsp;(OSVP) في دورتها الحادية عشرة للعام 2021،</p>\r\n\r\n<p>&nbsp;وهي من أهم المسابقات في مصر والمنطقة لدعم رواد الأعمال وأصحاب المشاريع التكنولوجية الناشئة التي تقدم حلولا ذكية وتقنية وعصرية للمشاكل المجتمعية، وتأهلهم للمنافسة في الدور النهائي من مسابقة Orange Social Venture Prize&nbsp;إفريقيا والشرق الأوسط والتي يبلغ مجموع جوائزها 50 ألف يورو.</p>\r\n\r\n<p>وأقيمت النهائيات المحلية عبر تقنية الفيديو كونفرانس المتطورة والتي تم خلالها اختيار 3 فائزين مصريين من رواد الأعمال المبتكرين وتم منحهم جوائز مالية، فضلا عن تأهلهم للمنافسة في الدور النهائي في المنافسة الدولية.</p>\r\n\r\n<p>وأعلنت الشركة عن المشاريع الفائزة في النهائيات وهم مشاريع Medfast و Zr3i.COM و Instadeel حيث حصد مشروع Medfast المركز الأول، بعد عرض تطبيق طبي يعتمد على نظام تحديد المواقع العالمي (GPS) بغرض تقديم الخدمات الطبية المنزلية من خلال مؤسسات وجهات مرموقة ومعتمدة.</p>\r\n\r\n<p>كما فاز مشروع Zr3i.com بالمركز الثاني بعد تطوير منصة تعاونية مبنية على تقنية SAAS لاستغلال تقنيات التحول الرقمي التي تركز على استخدام تكنولوجيا المعلومات المتطورة في تحسين حياة المزارعين، وأخيرا حصد مشروع Instadeel على المركز الثالث إثر توفير منصة تخدم المجتمع النسائي من أصحاب الشركات الصغيرة والمتوسطة والشركات الناشئة لإيجاد موارد وفرص تدعم أعمالهن مما يساعد على تنمية المجتمع.</p>', 'on', 1, '2021-09-09 05:00:00', '2021-09-24 13:52:11'),
(6, '3J4hQ2021-09-24post.jpg', '\"Nile Pioneers\" opens the application for 3rd session of the EI Incubator under the auspices of the HDB', '\"رواد النيل\" تفتح التقديم للدورة الثالثة لحاضنة الصناعات الهندسية برعاية بنك التعمير والإسكان', '<p>The Nile Pioneers Initiative, funded by the Central Bank of Egypt and implemented by the National Nile University, announced the opening of applications for the third cycle of the Business Incubator Program for Engineering Industries and Sustainable Development, sponsored by the Housing and Development Bank, with the aim of supporting start-up owners and innovative entrepreneurs who wish to establish their own projects.</p>\r\n\r\n<p>Dr. Mohamed Abbas, director of the Business Incubators Program at the Nile Pioneers Initiative, said in a statement issued today, Sunday, that the program aims to provide a set of financial and in-kind services such as guidance, training, consultancy and assistance in establishing companies and financial services they need at this stage of project establishment, through an incubation period. Ranging from 6 to 9 months.</p>\r\n\r\n<p>He pointed out that during the third incubation cycle, 10 startups will be selected in the field of supporting green industries, with a focus on industrial solutions that help shape a better future (the Fourth Industrial Revolution) using robots or smart manufacturing solutions.</p>\r\n\r\n<p>He added that the initiative will provide support of up to 200,000 pounds, in the form of financial and in-kind services, including assistance in legal company registration procedures and providing intensive training on business and technology through specialists and professionals.</p>\r\n\r\n<p>Abbas explained that the application is available at the Engineering Industries Incubator on the website of the Nile Pioneers Initiative ttps://np.eg/incubate/ or through its page on the social networking site https://www.facebook.com/NPIncubate</p>\r\n\r\n<p>He pointed out that the submission is available from now until September 22, after which companies and owners of ideas that meet the conditions are selected to enter the incubation period starting from the middle of next October.</p>\r\n\r\n<p>The Director of the Business Incubators Program at the Nile Pioneers Initiative stated that the incubation program at the Nile Pioneers Initiative, funded by the Central Bank of Egypt, incubated more than 136 companies from small and emerging companies and entrepreneurs by the end of last year 2020, which received more than a thousand services from the initiative, which helped them provide more than 563 A new job opportunity for young people, with sales exceeding 30 million pounds.</p>\r\n\r\n<p>The business incubator program on the initiative provides a set of services for entrepreneurs and emerging companies during or before the incubation period. its products and marketing.</p>\r\n\r\n<p>The Nile Pioneers Initiative&rsquo;s business incubator program helps network with clients and investors and use the capabilities available in other programs of the initiative, such as the mini-factory, mold center, design house, or other services provided by the Nile Pioneers Initiative. He pointed out that after the end of the course and the pre-incubation stage, the possibility of completing the provision of support will be discussed by enrolling them in other incubation courses in the initiative to transform these prototypes into final products.</p>', '<p>أعلنت&nbsp;مبادرة رواد النيل&nbsp;الممولة من&nbsp;البنك المركزي&nbsp;المصري وتنفذها&nbsp;جامعة النيل الأهلية&nbsp;عن فتح باب التقديم في الدورة الثالثة من برنامج حاضنة الأعمال للصناعات الهندسية والتنمية المستدامة برعاية&nbsp;بنك التعمير والإسكان&nbsp;بهدف دعم أصحاب الشركات الناشئة ورواد الأعمال المبتكرين والراغبين في تأسيس مشروعاتهم الخاصة.</p>\r\n\r\n<p>وقال الدكتور محمد عباس مدير برنامج حاضنات الأعمال بمبادرة رواد النيل في بيان صادر له اليوم الأحد إن البرنامج يهدف إلى تقديم مجموعة من الخدمات المالية والعينية مثل التوجيه والتدريب والاستشارات والمساعدة في تأسيس الشركات والخدمات المالية التي يحتاجونها في هذه المرحلة من تأسيس المشروع وذلك من خلال فترة احتضان تتراوح ما بين 6 إلى 9 أشهر.</p>\r\n\r\n<p>وأشار إلى أن خلال الدورة الثالثة للاحتضان ستختار 10 شركات ناشئة في مجال دعم الصناعات الخضراء مع التركيز على الحلول الصناعية التي تساعد على تشكيل مستقبل أفضل (الثورة الصناعية الرابعة) باستخدام الروبوتات أو حلول التصنيع الذكية.</p>\r\n\r\n<p>وأضاف أن المبادرة ستقدم دعما يصل إلى ٢٠٠ ألف جنيه، في شكل خدمات مالية وعينية منها المساعدة في إجراءات تسجيل الشركات بشكل قانوني وتوفير التدريب المكثف على الأعمال التجارية والتقنية من خلال متخصصين ومحترفين.</p>\r\n\r\n<p>وأوضح عباس أن التقديم متاح في حاضنة الصناعات الهندسية على الموقع الإلكتروني لمبادرة رواد النيل&nbsp;ttps://np.eg/incubate/&nbsp;أو من خلال صفحتها على موقع التواصل الاجتماعي&nbsp;https://www.facebook.com/NPIncubate</p>\r\n\r\n<p>وأشار إلى أن التقديم متاح من الآن وحتى 22 سبتمبر المقبل، بعدها يتم اختيار الشركات وأصحاب الأفكار التي تتوافر فيها الشروط، لدخولها فترة الاحتضان بداية من منتصف شهر أكتوبر المقبل.</p>\r\n\r\n<p>وأفاد مدير برنامج حاضنات الأعمال بمبادرة رواد النيل أن برنامج الاحتضان بمبادرة رواد النيل الممولة من&nbsp;البنك المركزي&nbsp;المصري احتضن أكثر من 136 شركة من الشركات الصغيرة والناشئة ورواد الأعمال بنهاية العام الماضي 2020، تلقت أكثر من ألف خدمة من خدمات المبادرة ما ساعدها على توفير أكثر من 563 فرصة عمل جديدة للشباب وحققت مبيعات تجاوزت 30 مليون جنيه.</p>\r\n\r\n<p>ويقدم برنامج حاضنات الأعمال بالمبادرة مجموعة من الخدمات لرواد الأعمال والشركات الناشئة خلال فترة الاحتضان أو قبلها حيث يقوم البرنامج بتنظيم معسكرات تدريبية فنية وإدارية طبقا لكل قطاع من خلال خبراء متخصصين، وأيضا التعريف بخدمات تأسيس الشركات وفتح الحسابات البنكية، بالإضافة للخدمات الاستشارية والتدريب لدعم الشركات في تطوير منتجاتها وتسويقها.</p>\r\n\r\n<p>ويساعد برنامج حاضنات الأعمال بمبادرة رواد النيل على عمليات التشبيك مع العملاء والمستثمرين واستخدام الإمكانيات المتاحة بالبرامج الأخرى بالمبادرة مثل المصنع المصغر أو مركز الإسطمبات أو بيت التصميم أو غيرها من الخدمات التي تقدمها مبادرة رواد النيل. ولفت إلى أنه سيتم بعد انتهاء الدورة ومرحلة ما قبل الاحتضان بحث إمكانية استكمال توفير الدعم من خلال إلحاقهم بدورات الاحتضان الأخرى بالمبادرة لتحويل هذه النماذج الأولية إلى منتجات نهائية.</p>', 'on', 1, '2021-09-11 05:30:49', '2021-09-24 14:01:38'),
(7, 'ykFx0d7S12UXV3wVtPWF2020-02-12post.jpg', 'The \"Pioneers 2030\" project continues to train young people on the entrepreneurship system', 'مشروع «رواد 2030» يواصل تدريب الشباب على منظومة ريادة الأعمال', '<p>The organizers of the 2030 Pioneers Project of the Ministry of Planning and Economic Development held a meeting with the youth camp at the Leadership Development Center in Benha Governorate, and another meeting with the local council in the oases for entrepreneurship training, as part of the One Million Entrepreneurs campaign launched by the Ministry of Planning with the aim of qualifying one million entrepreneurs by 2030 .</p>\r\n\r\n<p>Dr. Hala Al-Saeed, Minister of Planning and Economic Development, stressed that the focus on entrepreneurship has become one of the topics that cannot be ignored because it is closely related to the issue of economic growth, noting that the link between education and the economy is close, and that developed countries are keen to integrate entrepreneurship into national educational strategies and initiatives.</p>\r\n\r\n<p>The Minister of Planning: The &ldquo;One Million Entrepreneurs&rdquo; campaign targets all ages and groups<br />\r\nThe Minister of Planning added that the &ldquo;One Million Entrepreneurs&rdquo; campaign targets Egyptian youth of all ages and groups, and provides the full online training program, with the provision of trainers at the highest level, and entrepreneurship training programs in both Arabic and English, and the trainee receives a certificate of completion of the training course at the end of the program.</p>', '<p>عقد القائمون على مشروع رواد 2030 التابع لوزارة التخطيط والتنمية الاقتصادية لقاءً مع معسكر الشباب في مركز إعداد القادة بمحافظة بنها، ولقاء آخر بالمجلس المحلي بالواحات للتدريب على ريادة الأعمال، وذلك في إطار حملة المليون ريادي التي أطلقتها وزارة التخطيط بهدف تأهيل مليون رائد أعمال بحلول عام 2030.</p>\r\n\r\n<p>وأكدت الدكتورة هالة السعيد وزيرة التخطيط والتنمية الاقتصادية أن التركيز على ريادة الأعمال أصبح من الموضوعات التي لا يمكن تجاهلها لارتباطها الوثيق بمسألة النمو الاقتصادي، مشيرة إلى أن الصلة بين التعليم والاقتصاد وثيقة، وأن الدول المتقدمة تحرص على دمج ريادة الأعمال في الاستراتيجيات والمبادرات التعليمية الوطنية.</p>\r\n\r\n<p>وزيرة التخطيط: حملة &ldquo;المليون ريادي&rdquo; تستهدف جميع الأعمار والفئات<br />\r\nوأضافت وزيرة التخطيط أن حملة &ldquo;المليون ريادي&rdquo; تستهدف الشباب المصرى من جميع الأعمار والفئات، وتوفر البرنامج التدريبي الكامل عبر الإنترنت، مع توفير مدربين على أعلى مستوى، وبرامج تدريبية خاصة بريادة الأعمال باللغتين العربية والإنجليزية، ويحصل المتدرب على شهادة إتمام الدورة التدريبية بنهاية البرنامج.</p>', 'on', 1, '2021-08-11 09:01:24', '2021-11-26 06:02:21');

-- --------------------------------------------------------

--
-- Table structure for table `press`
--

CREATE TABLE `press` (
  `id` int(11) NOT NULL,
  `ar_title` varchar(250) NOT NULL,
  `en_title` varchar(255) NOT NULL,
  `img_url` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `order` int(11) DEFAULT NULL,
  `user_added` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `press`
--

INSERT INTO `press` (`id`, `ar_title`, `en_title`, `img_url`, `url`, `order`, `user_added`, `created_at`, `updated_at`) VALUES
(5, 'توقيع مذكرة تفاهم بين مركزي التطوير المهني وحاضنة أعمال مسار بجامعة سوهاج', 'Signing a memo between the PDC and the Massar Business Incubator at Sohag University', 'press/WgZk2iSExC8fi6LybEDHk0zwRaSbSk0xeLAmTIDQ.jpeg', 'https://www.youm7.com/story/2020/2/16/%D8%A7%D8%AA%D8%AD%D8%A7%D8%AF-%D8%A7%D9%84%D8%B5%D9%86%D8%A7%D8%B9%D8%A7%D8%AA-%D9%8A%D8%AA%D9%88%D9%82%D8%B9-%D9%86%D9%85%D9%88-%D8%A7%D9%84%D8%B7%D8%A8%D8%A7%D8%B9%D8%A9-%D8%A7%D9%84%D8%B1%D9%82%D9%85%D9%8A%D8%A9-%D9%88650-%D9%85%D8%B7%D8%A8%D8%B9%D8%A9-%D8%B1%D9%82%D9%85%D9%8A%D8%A9-%D9%81%D9%89/4633261', 1, 1, '2020-02-16 18:45:40', '2021-09-24 22:18:29'),
(10, '«دبي للمستقبل» تعلن انضمام «ميدلسكس» إلى برنامج حاضنات الجامعات', 'Dubai Future Foundation announces the joining of Middlesex to the University Incubator Program', 'press/dqViPbZCr3NjO4ULxdVtY3nRuBIdnOBronDBYAdG.jpeg', 'https://www.albayan.ae/uae/education/2021-09-21-1.4251906', 2, 1, '2021-09-24 22:21:04', '2021-09-24 22:21:04');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(2, 'Editor', 'web', '2020-02-07 22:00:00', '2020-02-07 22:00:00'),
(3, 'Coordinator', 'web', '2021-10-21 21:52:59', '2021-10-21 21:52:59'),
(4, 'Page Manager', 'web', '2020-02-07 22:00:00', '2021-10-21 21:57:32'),
(5, 'Manager', 'web', '2020-02-07 22:00:00', '2021-10-23 07:21:59'),
(6, 'Mentor', 'web', '2021-10-21 21:53:58', '2021-10-21 21:53:58'),
(7, 'Judger', 'web', '2021-10-21 21:56:28', '2021-10-21 21:56:28'),
(8, 'Registered', 'web', '2021-09-30 23:51:23', '2021-10-21 21:51:29'),
(9, 'Pages Editor', 'web', '2021-10-02 21:39:44', '2021-10-02 21:39:44');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(15, 1),
(13, 2),
(15, 2),
(16, 2),
(17, 1),
(18, 1),
(19, 1),
(14, 2),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(21, 1),
(32, 1),
(17, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(33, 1),
(34, 1),
(35, 1),
(34, 2),
(35, 2),
(36, 1),
(36, 2),
(37, 1),
(37, 2),
(38, 4),
(40, 1),
(41, 1),
(42, 1),
(44, 1),
(45, 1),
(44, 2),
(45, 2),
(46, 1),
(47, 1),
(34, 9),
(48, 1),
(14, 1),
(16, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(53, 7),
(54, 1),
(54, 2),
(54, 7),
(54, 6),
(54, 3),
(54, 4),
(54, 10),
(54, 9),
(53, 5),
(54, 5),
(54, 8),
(1, 5),
(2, 5),
(3, 5),
(4, 5),
(5, 5),
(6, 5),
(7, 5),
(8, 5),
(18, 5),
(19, 5),
(49, 5),
(50, 5),
(51, 5),
(52, 5),
(28, 5),
(29, 5),
(31, 5),
(55, 1),
(57, 1),
(58, 1),
(59, 1);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `name_ar` varchar(255) NOT NULL,
  `type_trans` varchar(255) NOT NULL,
  `price` double NOT NULL,
  `discount` double NOT NULL,
  `desc_en` text NOT NULL,
  `desc_ar` text NOT NULL,
  `published` varchar(255) DEFAULT NULL,
  `video_link` varchar(255) NOT NULL,
  `user_added` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name_en`, `name_ar`, `type_trans`, `price`, `discount`, `desc_en`, `desc_ar`, `published`, `video_link`, `user_added`, `created_at`, `updated_at`) VALUES
(1, 'The master room', 'الغرفة الرئيسية', 'أجتماع لشخصان فقط', 15, 0.5, '<p>The bed is the distinctive and essential piece of furniture in the bedroom, because sleep is a balance of physical and psychological comfort. The bed must be chosen carefully, and this depends on choosing the comfortable bed mattress in terms of its hardness, softness or size, and the design of the bed always prefers to be lined, if not Also, let the bed under the curtain be made of a patterned or striped fabric.</p>\r\n\r\n<p>And choosing a set of white cotton for the furnishings, the white color has its own charm. As for the bed linen, it should be carefully selected and preferably made of soft cotton, with calm colors and avoiding overlapping and geometric graphics and loud colors, and finally a lot of pillows stuffed with compressed fiber.</p>\r\n\r\n<p>In choosing the bedrooms, it is necessary to diversify the painting of the walls. It is possible to use wallpaper with calm drawings, and to stay away from geometric shapes to introduce a neutral and cheerful touch to the bedroom at the same time.</p>\r\n\r\n<p>In the case of choosing colors that are of light and bright degrees, and can be used with their lighter degree in one wall, for example, the dark degree of curtains and the lightness of the wall or vice versa.</p>', '<p>يعتبر السرير قطعة الأثاث المميزة والأساسية في غرفة النوم، لأن النوم هو ميزان للراحة الجسدية والنفسية، فيجب اختيار السرير بشكل دقيق، ويعتمد ذلك على اختيار مرتبة السرير المريحة من حيث صلابتها أو ليونتها أو حجمها، وتصميم السرير يفضل دائماً أن يكون مبطناً، وإن لم يكن كذلك فلتكن الستارة التحتية للسرير من قماش منقوش أو مخطط.</p>\r\n\r\n<p>واختيار طاقم من القطن الأبيض للمفروشات، فاللون الأبيض له سحره الخاص، أما أغطية السرير فيجب انتقاؤها بعناية ويفضل أن تكون مصنوعة من القطن الناعم، وذات ألوان هادئة والابتعاد عن الرسومات المتداخلة والهندسية والألوان الصاخبة، وأخيراً الإكثارمن الوسائد المحشوة بالفايبر المضغوط.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>في اختيارغرف النوم يجب التنويع في طلاء الجدران، فيمكن استخدم ورق حائط برسومات هادئة، والابتعاد عن الأشكال الهندسية لإدخال لمسة محايدة على غرفة النوم ومبهجة في نفس الوقت.</p>\r\n\r\n<p>وفي حالة اختيار الألوان التي تعتبر من الدرجات الفاتحة والزاهية، ويمكن استخدامها مع درجتها الأفتح في حائط واحد، فمثلا الدرجة القاتمة للستائر والفاتحة للحائط أو العكس.</p>', 'on', 'https://www.youtube.com/watch?v=CBFWIH9jbXo', 1, '2021-10-28 06:27:47', '2021-12-02 08:54:36'),
(2, 'Meeting Room #1', 'Meeting Room #1', 'admin.Meeting', 12, 0.5, '<p>dsdsd</p>', '<p>sdsdd</p>', NULL, 'https://www.youtube.com/watch?v=CBFWIH9jbXo', 1, '2021-10-28 12:37:19', '2021-10-28 12:37:19'),
(3, 'Meeting Room #2', 'غرفة إجتماعات رقم #2', 'admin.One to One', 200, 0.2, '<p>daddsdsf</p>', '<p>fdffddf</p>', 'on', 'https://www.youtube.com/watch?v=CBFWIH9jbXo', 1, '2021-11-01 19:50:29', '2021-11-01 19:50:29');

-- --------------------------------------------------------

--
-- Table structure for table `rooms_imgs`
--

CREATE TABLE `rooms_imgs` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `img_url` varchar(255) NOT NULL,
  `user_added` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `rooms_imgs`
--

INSERT INTO `rooms_imgs` (`id`, `room_id`, `img_url`, `user_added`, `created_at`, `updated_at`) VALUES
(4, 1, 'rooms/rJyn3ZONqrxl9pRjkbad9sgRBPAQ5y0xjrPqbNgI.png', 1, '2021-11-20 12:10:43', '2021-11-20 12:10:43'),
(3, 1, 'rooms/UMHMbeJYc87o0VcjO7tMGJTsS3bGeNte0DEzbckq.jpg', 1, '2021-11-20 12:10:43', '2021-11-20 12:10:43');

-- --------------------------------------------------------

--
-- Table structure for table `rooms_inclusives`
--

CREATE TABLE `rooms_inclusives` (
  `room_id` int(11) NOT NULL,
  `inclusive_id` int(11) NOT NULL,
  `user_added` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `rooms_inclusives`
--

INSERT INTO `rooms_inclusives` (`room_id`, `inclusive_id`, `user_added`, `created_at`, `updated_at`) VALUES
(2, 2, 1, '2021-10-28 12:37:19', '2021-10-28 12:37:19'),
(2, 3, 1, '2021-10-28 12:37:19', '2021-10-28 12:37:19'),
(3, 2, 1, '2021-11-01 19:50:29', '2021-11-01 19:50:29'),
(3, 1, 1, '2021-11-01 19:50:29', '2021-11-01 19:50:29'),
(1, 3, 1, '2021-12-02 08:54:36', '2021-12-02 08:54:36'),
(1, 2, 1, '2021-12-02 08:54:36', '2021-12-02 08:54:36'),
(1, 1, 1, '2021-12-02 08:54:36', '2021-12-02 08:54:36'),
(3, 3, 1, '2021-11-01 19:50:29', '2021-11-01 19:50:29'),
(1, 6, 1, '2021-12-02 08:54:36', '2021-12-02 08:54:36'),
(1, 4, 1, '2021-12-02 08:54:36', '2021-12-02 08:54:36'),
(1, 8, 1, '2021-12-02 08:54:36', '2021-12-02 08:54:36');

-- --------------------------------------------------------

--
-- Table structure for table `room_bookings`
--

CREATE TABLE `room_bookings` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fromDate` datetime DEFAULT NULL,
  `toDate` datetime DEFAULT NULL,
  `approved` varchar(10) NOT NULL,
  `user_added` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `room_bookings`
--

INSERT INTO `room_bookings` (`id`, `room_id`, `user_id`, `fromDate`, `toDate`, `approved`, `user_added`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2021-11-22 16:19:00', '2021-11-22 21:00:00', 'Approved', 1, '2021-11-22 12:24:17', '2021-11-24 19:28:21');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `days` varchar(250) DEFAULT NULL,
  `dfrom` date DEFAULT NULL,
  `dto` date DEFAULT NULL,
  `tfrom` time DEFAULT NULL,
  `tto` time DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `active` int(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `user_id`, `type`, `days`, `dfrom`, `dto`, `tfrom`, `tto`, `notes`, `active`, `created_at`, `updated_at`) VALUES
(18, 13, 'Weekly', '[\"Sat\",\"Mon\",\"Wed\"]', NULL, NULL, '09:00:00', '17:00:00', NULL, 1, '2021-11-26 13:10:21', '2021-12-03 05:07:38'),
(19, 13, 'OneDay', NULL, '2021-12-04', NULL, '02:00:00', '06:00:00', 'this is the monthly nothing', 1, '2021-11-26 20:47:50', '2021-11-28 12:21:30'),
(17, 13, 'WorkingDays', 'null', NULL, NULL, '09:00:00', '10:00:00', NULL, 1, '2021-11-26 13:09:39', '2021-11-28 12:21:29'),
(20, 44, 'OneDay', NULL, '2021-11-28', NULL, '10:00:00', '13:00:00', 'I\'ll add more days later', 1, '2021-11-27 16:59:27', '2021-11-27 16:59:27'),
(21, 44, 'Period', NULL, '2021-12-05', '2021-11-11', '10:00:00', '11:00:00', NULL, 1, '2021-11-27 17:00:18', '2021-11-27 17:00:18'),
(22, 44, 'Weekly', '[\"Mon\",\"Wed\"]', NULL, NULL, '12:00:00', '13:00:00', 'This is only from the next month', 1, '2021-11-27 17:01:13', '2021-11-27 17:01:13');

-- --------------------------------------------------------

--
-- Table structure for table `screenings`
--

CREATE TABLE `screenings` (
  `step_id` int(20) NOT NULL,
  `user_id` int(20) NOT NULL,
  `panel` int(20) NOT NULL DEFAULT 1,
  `users` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `screenings`
--

INSERT INTO `screenings` (`step_id`, `user_id`, `panel`, `users`, `created_at`, `updated_at`) VALUES
(5, 3, 3, '[\"16\"]', '2021-11-24 22:02:04', NULL),
(3, 10, 2, NULL, '2021-11-24 22:01:13', NULL),
(3, 3, 2, NULL, '2021-11-24 22:01:13', NULL),
(5, 13, 2, '[\"8\", \"14\"]', '2021-11-24 22:02:04', NULL),
(5, 3, 2, '[\"8\", \"14\"]', '2021-11-24 22:02:04', NULL),
(3, 15, 1, NULL, '2021-11-24 22:01:13', NULL),
(3, 3, 1, NULL, '2021-11-24 22:01:13', NULL),
(61, 15, 1, NULL, '2021-12-07 08:25:17', NULL),
(61, 10, 1, NULL, '2021-12-07 08:25:17', NULL),
(61, 10, 2, NULL, '2021-12-07 08:25:17', NULL),
(61, 31, 2, NULL, '2021-12-07 08:25:17', NULL),
(82, 15, 1, NULL, '2021-12-08 11:45:56', NULL),
(82, 3, 1, NULL, '2021-12-08 11:45:56', NULL),
(82, 8, 2, NULL, '2021-12-08 11:45:56', NULL),
(82, 31, 2, NULL, '2021-12-08 11:45:56', NULL),
(84, 15, 1, NULL, '2021-12-08 13:12:42', NULL),
(84, 3, 1, NULL, '2021-12-08 13:12:42', NULL),
(84, 31, 2, '[\"8\",\"14\"]', '2021-12-08 13:12:42', '2021-12-08 11:44:45'),
(84, 13, 2, '[\"8\",\"14\"]', '2021-12-08 13:12:42', '2021-12-08 11:44:45'),
(88, 3, 0, NULL, '2021-12-20 11:11:00', '2021-12-20 11:11:00'),
(97, 3, 0, NULL, '2021-12-20 15:09:16', '2021-12-20 15:09:16'),
(99, 100, 0, NULL, '2021-12-27 10:21:19', '2021-12-27 10:21:19'),
(99, 3, 0, NULL, '2021-12-27 10:21:19', '2021-12-27 10:21:19'),
(100, 31, 0, NULL, '2022-01-02 09:09:36', '2022-01-02 09:09:36');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_category_id` bigint(20) UNSIGNED NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description_en` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_ar` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_category_id`, `name_en`, `name_ar`, `price`, `description_en`, `description_ar`, `img_url`, `created_at`, `updated_at`) VALUES
(12, 20, 'Fianacial Analysis', 'تحليل مالى', '0.00', '<p>Free Financial Analysis services,</p>\r\n\r\n<p>Available only for entrepreneurs</p>\r\n\r\n<p>It&#39;s just for testing purposes&nbsp;</p>', '<p>تقديم خدمات التحليل المالي لكافة المؤسسات</p>', 'services/q76Rd2kVEWVqbsKnNeRIFrIseLGYgOzMURINcKTn.jpeg', '2020-02-16 02:18:06', '2021-11-24 10:49:32'),
(14, 22, 'Digital Marketing', 'تسويق إلكترونى', '0.00', '<p>Digital Marketing</p>', '<p>تسويق إلكترونى</p>', 'services/He0cKC6884LeBNubRIKESmzUUNQoXuaF6jmEDaRA.png', '2020-02-16 02:20:30', '2020-03-01 18:25:05'),
(15, 23, 'Restructure', 'إعادة هيكلة', '5000.00', '<p>Restructure&nbsp;</p>', '<p>إعادة هيكلة</p>', 'services/hXsCjYm5auBtABJdp1BJrhYrPVKvPo9hpGzN1Lia.jpg', '2020-02-16 02:21:34', '2021-11-24 14:34:04'),
(16, 22, 'Online ads', 'اعلانات اونلاين', '0.00', '<p>Ask about price within your&nbsp;request</p>', '<p>Ask about price within your&nbsp;request</p>', 'services/Q2Ru3wbml9txLRDhgCgzcpc6ScWxw0SydKF6k3mW.jpg', '2021-11-24 14:31:33', '2021-11-24 14:31:33'),
(17, 22, 'Rent bio lap', 'تأجير البيو لاب', '500.00', '<p>BIO lap des</p>', '<p>وصف مختصر للخدمة</p>', 'services/vyHMkmTMWCRpQ9vdxeTcgCXIBwrEXbGebbS4LGjC.jpg', '2021-12-04 07:24:36', '2021-12-04 07:24:36'),
(18, 20, 'test service', 'خدمة جديدة', '200.00', '<p>new service available from x to y</p>', '<p>خدمة جديدة متاحة للاستخدام&nbsp;</p>', 'services/bnlrV3gLyZIWDhu0nV4UKph911w8eP1O6dE8ZxlG.png', '2021-12-19 09:22:44', '2021-12-19 09:22:44');

-- --------------------------------------------------------

--
-- Table structure for table `services_requests`
--

CREATE TABLE `services_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  `approved` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rate` int(2) DEFAULT NULL,
  `rate_comment` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin` int(20) DEFAULT NULL,
  `done` int(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services_requests`
--

INSERT INTO `services_requests` (`id`, `service_id`, `user_id`, `comment`, `date_time`, `approved`, `rate`, `rate_comment`, `admin_comment`, `admin`, `done`, `created_at`, `updated_at`) VALUES
(1, 14, 1, 'I need it tomorow', NULL, 'No', NULL, NULL, NULL, 1, NULL, '2021-11-25 00:16:08', '2021-12-03 19:56:26'),
(2, 16, 1, NULL, NULL, 'No', NULL, NULL, NULL, 1, NULL, '2021-11-25 00:16:42', '2021-11-25 16:09:40'),
(10, 12, 1, 'Fast response please', NULL, 'Yes', NULL, NULL, 'Come tomorow', 1, NULL, '2021-11-25 00:31:52', '2021-11-25 17:42:54'),
(11, 12, 1, NULL, NULL, 'Yes', NULL, NULL, NULL, 1, NULL, '2021-11-25 00:33:22', '2021-12-03 21:08:29'),
(12, 12, 13, 'It\'s just a test', NULL, 'Yes', 5, 'It was a mazing', 'ok', 1, NULL, '2021-11-25 09:34:26', '2021-12-03 21:37:29'),
(13, 12, 2, NULL, NULL, 'No', NULL, NULL, NULL, 1, NULL, '2021-11-29 16:20:53', '2021-12-04 18:21:54'),
(14, 16, 2, 'Ads comment', NULL, 'No', NULL, NULL, NULL, 1, NULL, '2021-11-29 16:22:30', '2021-12-04 18:21:50'),
(18, 14, 2, NULL, '2021-12-03 19:00:00', 'Yes', 2, NULL, NULL, 1, NULL, '2021-12-03 18:57:57', '2021-12-04 14:15:51'),
(19, 14, 2, 'Hanna Saad', '2021-12-03 20:56:00', 'Yes', 4, 'Seem good', 'ok', 1, NULL, '2021-12-04 11:58:33', '2021-12-04 15:12:03'),
(20, 17, 2, NULL, '2021-12-04 12:00:00', 'Yes', 4, 'nice', NULL, 1, NULL, '2021-12-04 18:21:10', '2021-12-04 20:23:40'),
(21, 14, 2, NULL, '2021-12-07 12:15:00', 'Yes', 4, 'Good', NULL, 1, NULL, '2021-12-06 08:15:33', '2021-12-07 07:31:04'),
(22, 12, 1, NULL, NULL, 'Yes', NULL, NULL, NULL, 1, NULL, '2021-12-13 08:00:26', '2021-12-21 17:03:29'),
(23, 14, 1, NULL, '2021-12-13 11:00:00', 'Yes', NULL, NULL, NULL, 1, NULL, '2021-12-13 08:05:08', '2021-12-21 18:57:54'),
(24, 12, 2, 'trtrrt', '2021-12-31 20:00:00', 'Yes', NULL, NULL, 'accept', 1, NULL, '2021-12-19 12:45:56', '2021-12-19 12:46:51'),
(25, 16, 2, NULL, '2021-12-28 19:00:00', 'Yes', NULL, NULL, 'ok but no', 1, NULL, '2021-12-19 12:48:26', '2021-12-19 12:48:48'),
(26, 17, 2, NULL, '2021-12-21 20:00:00', 'Yes', NULL, NULL, NULL, 1, NULL, '2021-12-21 16:38:55', '2021-12-21 17:03:34'),
(27, 14, 2, NULL, '2021-12-21 20:47:00', 'Yes', NULL, NULL, NULL, 1, NULL, '2021-12-21 16:47:23', '2021-12-21 17:04:03'),
(28, 12, 2, NULL, '2021-12-20 20:00:00', 'Yes', 2, 'ok', NULL, 1, 1, '2021-12-21 16:47:46', '2021-12-21 19:47:16'),
(29, 14, 101, NULL, '2021-12-27 09:00:00', 'Yes', 3, 'Good', NULL, 1, 1, '2021-12-27 11:07:40', '2021-12-27 12:10:48'),
(30, 17, 101, NULL, '2021-12-28 15:50:00', 'Yes', 0, 'no', NULL, 1, 1, '2021-12-27 12:51:01', '2021-12-28 12:46:19'),
(31, 14, 101, NULL, '2021-12-27 10:00:00', 'Yes', 5, 'أفضل خدمة', NULL, 1, 1, '2021-12-27 13:19:26', '2021-12-28 08:58:50'),
(32, 14, 101, NULL, '2021-12-29 14:27:00', 'No', NULL, NULL, NULL, 1, NULL, '2021-12-28 10:27:27', '2021-12-28 10:46:07'),
(33, 17, 101, NULL, '2021-12-29 14:47:00', 'Yes', 2, 'ok', 'ok', 1, 1, '2021-12-28 10:47:33', '2021-12-31 20:18:17');

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `name_en`, `name_ar`, `created_at`, `updated_at`) VALUES
(20, 'Financial Services', 'خدمات مالية', '2020-02-08 17:14:10', '2020-02-16 02:12:32'),
(22, 'Technology Services', 'خدمات تكنولوجية', '2020-02-08 17:16:48', '2020-02-16 02:13:42'),
(23, 'Managerial  Services', 'خدمات إدارية', '2020-02-16 01:36:12', '2020-02-16 02:15:00'),
(24, 'Logistics services', 'خدمات لوجستية', '2021-11-24 11:29:42', '2021-11-24 11:29:42');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
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
  `user_added` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `type`, `title`, `ar_title`, `dsc`, `ar_dsc`, `set1`, `ar_set1`, `set2`, `ar_set2`, `url`, `img_url`, `set_order`, `user_added`, `created_at`, `updated_at`) VALUES
(1, '1', 'The Virtual Incubator', 'حاضنة الأعمال الإفتراضية', NULL, NULL, NULL, NULL, 'set_order = 1 to enable registration\nset url = UA-14400000?-? to add google analytics code\n', NULL, NULL, NULL, 1, 27, '2021-11-07 17:34:00', '2021-11-30 13:05:38'),
(2, '2', 'Facebook', 'فيسبوك', NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.facebook.com/pagelink', 'facebook', 1, 27, '2021-10-05 18:45:09', '2021-10-05 16:45:09'),
(3, '2', 'Twitter', 'تويتر', NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.twitter.com/pagelink', 'twitter', 2, 27, '2021-10-05 18:45:09', '2021-10-05 16:45:09'),
(4, '2', 'Youtube', 'يوتيوب', NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.youtube.com/pagelink', 'youtube', 5, 27, '2021-10-05 18:45:09', '2021-10-05 16:45:09'),
(5, '2', 'Instagram', 'انستجرام', NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.instagram.com/pagelink', 'instagram', 4, 27, '2021-10-05 18:45:09', '2021-10-05 16:45:09'),
(6, '2', 'Linkedin', 'لينكيدإن', NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.linkedin.com/pagelink', 'linkedin', 3, 27, '2021-10-05 18:45:09', '2021-10-05 16:45:09'),
(9, '3', 'Looking for a fund', 'تبحث عن تمويل لمشروعك', 'Join now to get all support', 'خطوات بسيطة لتلقي الدعم اللازم', 'Join us now', 'إنضم معنا الآن', 'icon-graphic', NULL, '/register', '3.jpg', 1, 27, '2021-09-25 11:11:41', '2021-09-25 09:11:41'),
(12, '6', 'Supported startups', 'شركة ناشئة تم دعمها', '3200+', NULL, NULL, NULL, NULL, NULL, '', 'office', 1, 27, '2021-10-05 18:45:09', '2021-10-05 16:45:09'),
(8, '3', 'Ask for Information', 'ترغب في الحصول على دعم فني', 'Get the required technical support now', 'احصل على الدعم الفني اللازم الان', 'Submit your application', 'سجل بياناتك الآن', 'icon-layers', NULL, '/register', '2.jpg', 1, 27, '2021-09-25 11:11:41', '2021-09-25 09:11:41'),
(7, '3', 'Join our parteners', 'إنضم لقائمة الرعاة', 'We appreciate your joining our partners, Contact us now.', 'يسعدنا انضمامكم كراعي، اتصل بنا الآن.', 'Register now', 'سجل بياناتك', 'icon-computer', NULL, '/contact', '1.jpg', 1, 27, '2021-09-25 02:14:07', '2021-09-25 00:14:07'),
(10, '4', 'About The Virtual Incubator', 'عن حاضنة الأعمال الإفتراضية', 'More About The Virtual Incubator, More About The Virtual Incubator, More About The Virtual Incubator', 'معلومات أكثر عن حاضنة الأعمال الإفتراضية، معلومات أكثر عن حاضنة الأعمال الإفتراضية، معلومات أكثر عن حاضنة الأعمال الإفتراضية', 'The Virtual Incubator Mission', 'رسالة حاضنة الأعمال الإفتراضية', 'The Virtual Incubator Vission', 'رؤية حاضنة الأعمال الإفتراضية', 'on', '', 1, 27, '2021-09-25 00:15:21', '2021-09-24 22:15:21'),
(14, '6', 'Fund provided', 'تمويل تم تقديمه', '300 M$', NULL, NULL, NULL, NULL, NULL, '', 'cup', 1, 27, '2021-10-01 20:47:06', '2021-10-01 18:47:06'),
(15, '6', 'Mentors', 'مدرب محترف', '38', NULL, NULL, NULL, NULL, NULL, '', 'professor', 1, 27, '2021-10-01 20:55:16', '2021-10-01 18:46:41'),
(11, '5', 'info@masterencode.com', '+20237495616', '8 Elsad El3aly st.<br>\r\nEldoki, Cairo<br>\r\nEgypt.', '8 ش السد العالي،<br>\r\nالدقي، القاهرة<br>\r\nمصر', '+20237495616', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3453.84580648717!2d31.21963271992172!3d30.04128122576464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1458412a48ef4ea3%3A0x5b287b005f7efbcd!2z2YHZitmG2KjZig!5e0!3m2!1sar!2seg!4v1632487999964!5m2!1sar!2seg', 'Sun - Thu: 09.00am to 5.00pm', 'الأحد - الخميس: 9ص إلى 5م', '', '', 1, 27, '2021-09-24 12:57:16', '2021-09-24 10:57:16'),
(13, '6', 'Entrepreneurs trained', 'رائد أعمال تم تدريبه', '5425+', NULL, NULL, NULL, NULL, NULL, '', 'man', 1, 27, '2021-10-05 18:45:09', '2021-10-05 16:45:09');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `img_path` varchar(255) NOT NULL,
  `title` varchar(250) NOT NULL,
  `ar_title` varchar(250) NOT NULL,
  `img_desc` varchar(255) DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `url_title` varchar(255) DEFAULT NULL,
  `ar_url_title` varchar(255) DEFAULT NULL,
  `ar_img_desc` varchar(255) DEFAULT NULL,
  `slideorder` int(11) NOT NULL,
  `user_added` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `img_path`, `title`, `ar_title`, `img_desc`, `img_url`, `url_title`, `ar_url_title`, `ar_img_desc`, `slideorder`, `user_added`, `created_at`, `updated_at`) VALUES
(26, 'sev3bE3Fqz2021-09-24slide.jpg', 'We prepare startups for accelerators', 'نعد الشركات الناشئة لمسرعات الأعمال', 'Rgister now & we will contact you soon', '/register', 'Register now', 'سجل الآن', 'سجل بياناتك وسنتواصل معك قريبا', 4, 1, '2021-09-25 01:41:56', '2021-09-24 23:41:56'),
(27, 'nTEGNthitG2021-09-24slide.jpg', 'How to start a technology business?', 'كيف تبدأ مشروعك التكنولوجي الجديد؟', '5 things you need to know to start your new business', '/blog', 'More', 'المزيد', '5 أشياء تحتاج لمعرفتها لتبدأ مشروعك الجديد', 1, 1, '2021-09-25 01:42:06', '2021-09-24 23:42:06'),
(28, '3XlTeI7CZH2021-09-24slide.jpg', 'Egyptian success stories', 'قصص نجاح مصرية', 'Stories from the Egyptian reality', '/blog', 'More', 'التفاصيل', 'قصص من الواقع المصري', 2, 1, '2021-09-25 01:42:20', '2021-09-24 23:42:20'),
(29, 'DXIftuHmPc2021-09-24slide.jpg', 'The 1st International Conference for Entrepreneurs', 'المؤتمر الدولي الأول لرواد الأعمال', 'Elmasa Gotel, New cairo, Cairo, Egypt - 1 Nov. 2021', '/event/1', 'More', 'التفاصيل', 'القاهرة الجديدة - 1 نوفمبر 2021', 3, 1, '2021-09-25 01:42:26', '2021-09-24 23:42:26');

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name_en`, `name_ar`, `created_at`, `updated_at`) VALUES
(1, 'Cairo', 'القاهرة', NULL, NULL),
(2, 'Giza', 'الجيزة', NULL, NULL),
(3, 'Alexandria', 'الأسكندرية', NULL, NULL),
(4, 'Dakahlia', 'الدقهلية', NULL, NULL),
(5, 'Red Sea', 'البحر الأحمر', NULL, NULL),
(6, 'Beheira', 'البحيرة', NULL, NULL),
(7, 'Fayoum', 'الفيوم', NULL, NULL),
(8, 'Gharbiya', 'الغربية', NULL, NULL),
(9, 'Ismailia', 'الإسماعلية', NULL, NULL),
(10, 'Monofia', 'المنوفية', NULL, NULL),
(11, 'Minya', 'المنيا', NULL, NULL),
(12, 'Qaliubiya', 'القليوبية', NULL, NULL),
(13, 'New Valley', 'الوادي الجديد', NULL, NULL),
(14, 'Suez', 'السويس', NULL, NULL),
(15, 'Aswan', 'اسوان', NULL, NULL),
(16, 'Assiut', 'اسيوط', NULL, NULL),
(17, 'Beni Suef', 'بني سويف', NULL, NULL),
(18, 'Port Said', 'بورسعيد', NULL, NULL),
(19, 'Damietta', 'دمياط', NULL, NULL),
(20, 'Sharkia', 'الشرقية', NULL, NULL),
(21, 'South Sinai', 'جنوب سيناء', NULL, NULL),
(22, 'Kafr Al sheikh', 'كفر الشيخ', NULL, NULL),
(23, 'Matrouh', 'مطروح', NULL, NULL),
(24, 'Luxor', 'الأقصر', NULL, NULL),
(25, 'Qena', 'قنا', NULL, NULL),
(26, 'North Sinai', 'شمال سيناء', NULL, NULL),
(27, 'Sohag', 'سوهاج', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `steps`
--

CREATE TABLE `steps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cycle_id` int(10) NOT NULL,
  `title` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stype` int(3) NOT NULL,
  `data` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `arr` int(5) NOT NULL,
  `from` date DEFAULT NULL,
  `to` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `steps`
--

INSERT INTO `steps` (`id`, `cycle_id`, `title`, `description`, `stype`, `data`, `list`, `arr`, `from`, `to`, `created_at`, `updated_at`) VALUES
(85, 18, 'Mentorship', NULL, 3, NULL, NULL, 3, '2021-12-01', '2022-01-31', '2021-12-08 13:09:40', '2021-12-27 10:14:55'),
(86, 18, 'TVET training', NULL, 4, NULL, NULL, 4, '2021-12-30', '2022-01-25', '2021-12-08 13:11:04', '2021-12-27 10:15:26'),
(87, 18, 'Final screening', NULL, 5, NULL, NULL, 5, '2022-01-25', '2022-01-31', '2021-12-08 13:11:44', '2021-12-27 10:15:44'),
(83, 18, 'Cycle registration form', NULL, 1, NULL, NULL, 1, '2021-12-01', '2021-12-30', '2021-12-08 12:07:48', '2021-12-25 06:56:47'),
(89, 19, 'التصفيات النهائية', NULL, 5, NULL, NULL, 2, '2022-01-10', '2022-01-15', '2021-12-10 08:08:27', '2021-12-28 17:06:58'),
(90, 20, 'registration form', NULL, 1, '50', NULL, 1, '2021-12-19', '2022-01-01', '2021-12-19 10:16:22', NULL),
(91, 20, 'تقييم وفرز المتقدمين', NULL, 2, 'onetoall', NULL, 2, '2021-12-23', '2021-12-24', '2021-12-20 09:48:30', '2021-12-20 17:07:30'),
(92, 21, 'نموذج البيانات', 'برجاء استيفاء البيانات التالية للإنضمام للدورة', 1, NULL, NULL, 1, '2021-12-20', '2021-12-25', '2021-12-20 11:44:03', '2021-12-23 11:23:02'),
(93, 21, 'اختيار الشركات', NULL, 2, 'onetoall', NULL, 2, '2021-12-25', '2021-12-30', '2021-12-20 11:44:57', '2021-12-23 11:23:02'),
(95, 21, 'تدريب اونلاين', NULL, 4, NULL, NULL, 3, '2021-12-28', '2021-12-29', '2021-12-20 11:47:32', '2021-12-23 11:23:02'),
(99, 18, 'الفرز والاختيار', 'سيتم الاختيار تبعا للمعلومات التي تم ادخالها في النموذج', 2, 'grouptoall', '[\"3\",\"100\"]', 2, '2021-12-27', '2022-01-01', '2021-12-22 11:55:33', '2021-12-27 10:21:19'),
(98, 19, 'tr', NULL, 4, NULL, NULL, 1, '2021-12-22', '2021-12-23', '2021-12-22 09:38:05', '2021-12-28 17:06:58'),
(100, 23, 'تحكيم اولي', NULL, 2, 'onetoall', '[\"31\"]', 2, '2021-12-20', '2021-12-29', '2021-12-28 13:30:11', '2022-01-02 09:09:36'),
(101, 23, 'ارشاد', 'اي حاجة', 3, NULL, NULL, 1, '2021-12-28', '2022-01-01', '2021-12-28 13:30:56', '2021-12-28 12:31:33'),
(102, 23, 'تدريب', 'ثثثث', 4, NULL, NULL, 3, '2022-01-01', '2022-03-31', '2021-12-28 13:31:24', '2021-12-28 12:31:33');

-- --------------------------------------------------------

--
-- Table structure for table `trainings`
--

CREATE TABLE `trainings` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dsc` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trainings`
--

INSERT INTO `trainings` (`id`, `title`, `dsc`, `image`, `user_id`, `created_at`, `updated_at`) VALUES
(23, 'التعليم الفني', 'TEVET Logo only', 'training/cbSNve9gocTCLFt8cT30AQSvwtTYsWW0aIRZQRpW.jpg', 1, '2021-12-01 15:21:49', '2021-12-22 15:47:46'),
(29, 'New demo', NULL, NULL, 1, '2021-12-04 17:05:18', '2021-12-04 17:05:18'),
(30, 'Januray round', NULL, NULL, 1, '2021-12-05 07:26:00', '2021-12-05 07:26:00'),
(31, 'test', 'test test', NULL, 1, '2021-12-19 09:25:09', '2021-12-19 09:25:09'),
(32, 'social media', 'social media social media social media social media social media social media social media social media social media', NULL, 1, '2021-12-19 12:51:25', '2021-12-19 12:51:25');

-- --------------------------------------------------------

--
-- Table structure for table `training_completed`
--

CREATE TABLE `training_completed` (
  `training_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `training_completed`
--

INSERT INTO `training_completed` (`training_id`, `user_id`, `course_id`, `created_at`, `updated_at`) VALUES
(48, 2, 23, '2021-12-24 10:10:25', '2021-12-24 10:10:25'),
(47, 2, 23, '2021-12-24 10:10:21', '2021-12-24 10:10:21'),
(44, 2, 23, '2021-12-24 10:10:16', '2021-12-24 10:10:16'),
(49, 2, 23, '2021-12-24 10:10:03', '2021-12-24 10:10:03'),
(30, 2, 23, '2021-12-24 10:10:01', '2021-12-24 10:10:01'),
(46, 2, 23, '2021-12-24 10:09:56', '2021-12-24 10:09:56'),
(46, 101, 23, '2021-12-24 18:32:18', '2021-12-24 18:32:18'),
(30, 101, 23, '2021-12-24 18:34:50', '2021-12-24 18:34:50'),
(49, 101, 23, '2021-12-24 18:38:17', '2021-12-24 18:38:17'),
(44, 101, 23, '2021-12-31 20:17:44', '2021-12-31 20:17:44'),
(47, 101, 23, '2021-12-31 20:17:50', '2021-12-31 20:17:50'),
(48, 101, 23, '2021-12-31 20:17:54', '2021-12-31 20:17:54');

-- --------------------------------------------------------

--
-- Table structure for table `training_contents`
--

CREATE TABLE `training_contents` (
  `id` int(10) UNSIGNED NOT NULL,
  `training_id` int(11) NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `arr` int(5) NOT NULL DEFAULT 0,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `training_contents`
--

INSERT INTO `training_contents` (`id`, `training_id`, `title`, `arr`, `content`, `user_id`, `created_at`, `updated_at`) VALUES
(30, 23, 'Meeting photo & New Da7e7 video on youtube', 3, '<figure class=\"image\" style=\"text-align: center;\"><img style=\"display: block; margin-left: auto; margin-right: auto;\" src=\"../photos/0r4GflZ7UJgB5Dufhq697JVWevNCNC9tDCiWN5Z1.jpg\" />\r\n<figcaption>This photo for laptop</figcaption>\r\n</figure>\r\n<hr />\r\n<p style=\"text-align: center;\"><iframe src=\"https://www.youtube.com/embed/o0D338ZgGzA\" width=\"560\" height=\"314\" allowfullscreen=\"allowfullscreen\"></iframe></p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n<p dir=\"rtl\" style=\"text-align: center;\"><strong><span style=\"font-size: 24pt;\">(البند الثالث)</span></strong></p>\r\n<p dir=\"rtl\" style=\"text-align: justify;\"><span style=\"font-size: 18pt;\">تسلم الطرف الثانى العين المؤجرة بمنقولاتها بتاريخ اليوم وذلك على سبيل الأمانة لاستعمالها فى الغرض الذي خصصت له العين ويلتزم بردها فى نهاية المدة المتفق عليها بحالتها المبينة بالبند الثانى وإلا كان مسئولا عن ذلك مدنيا فى حالة التلف أو سوء الاستعمال، وجنائيا فى حالة اختلاس شيء منها وفى هذه الحالة يكون للطرف الأول حبس التأمين كتعويض فضلاً عن رجوعه على الطرف الثانى بدعوى لتكملة التعويض بما ناله من ضرر ما لم يتم التصالح بينهما على تقدير ذلك.</span></p>\r\n<pre class=\"language-php\"><code> public function showfile($id,$filename) {\r\n        if (Auth::id() == $id || Auth::user()-&gt;can(\'view_allfiles\')) {\r\n            $storagePath = storage_path(\'app/uploads/files/\' . $id . \'/\' . $filename);\r\n            return response()-&gt;file($storagePath);\r\n        } else {abort(401);}\r\n    }</code></pre>', 1, '2021-12-01 15:27:39', '2021-12-06 08:12:54'),
(44, 23, 'Petrol video on AJ+', 4, '<p style=\"text-align: center;\"><iframe style=\"width: 1029px; height: 577px;\" src=\"https://www.youtube.com/embed/PgikctpMbYA\" width=\"829\" height=\"465\" allowfullscreen=\"allowfullscreen\"></iframe></p>', 1, '2021-12-01 16:38:40', '2021-12-06 08:12:54'),
(46, 23, 'Intro', 1, '<p style=\"text-align: center;\"><strong><span style=\"font-size: 18pt; color: #ba372a;\">Just intro text</span></strong></p>\r\n<table style=\"border-collapse: collapse; width: 48.7265%; height: 99.4378px; margin-left: auto; margin-right: auto;\" border=\"1\">\r\n<tbody>\r\n<tr style=\"height: 44.219px;\">\r\n<td style=\"width: 49.9665%; text-align: center; height: 44.219px;\">\r\n<h1><span style=\"color: #169179;\">1</span></h1>\r\n</td>\r\n<td style=\"width: 50.0335%; text-align: center; height: 44.219px;\">\r\n<h1><span style=\"color: #169179;\">2</span></h1>\r\n</td>\r\n</tr>\r\n<tr style=\"height: 55.2188px;\">\r\n<td style=\"width: 49.9665%; text-align: center; height: 55.2188px;\">\r\n<h1>User A</h1>\r\n</td>\r\n<td style=\"width: 50.0335%; text-align: center; height: 55.2188px;\">\r\n<h1>User B</h1>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n<p style=\"text-align: center;\"><iframe style=\"width: 912px; height: 511px;\" src=\"https://www.youtube.com/embed/YICOhIk-cpc\" width=\"912\" height=\"511\" allowfullscreen=\"allowfullscreen\"></iframe></p>\r\n<p><img style=\"display: block; margin-left: auto; margin-right: auto;\" src=\"../photos/oF2AbjtpdOT8ZR2fwNGX5F0MDNhTP72KtXPpLJPL.png\" width=\"578\" height=\"578\" /></p>', 1, '2021-12-02 08:58:45', '2021-12-06 08:12:54'),
(47, 23, 'the 4th part', 5, '<p style=\"text-align: center;\"><img src=\"../photos/3Vpan1howWwC4j1W6xGuUNKMx8mbTZRhZfOVU180.jpg\" /></p>', 1, '2021-12-04 15:17:24', '2021-12-06 08:12:54'),
(48, 23, 'Closing and final', 6, '<p style=\"text-align: center;\"><img src=\"../photos/BZXGpahENlRGmOiKnCt2xLwGNs034oiSfOAEKQXX.jpg\" /></p>', 1, '2021-12-04 15:18:04', '2021-12-06 08:12:54'),
(49, 23, 'Thanks', 7, '<h2 style=\"text-align: center;\"><span style=\"font-size: 36pt;\">THANKS</span></h2>\r\n<h2 style=\"text-align: center;\"><span style=\"font-size: 56pt;\">THANKS</span></h2>', 1, '2021-12-04 15:19:53', '2021-12-06 08:12:54'),
(51, 29, 'Course 1', 1, '<p>111</p>', 1, '2021-12-04 17:07:21', '2021-12-05 07:24:30'),
(52, 29, 'Course  2', 2, '<p>22</p>', 1, '2021-12-04 17:07:32', '2021-12-05 07:24:30'),
(53, 29, 'Course 3', 3, '<p>33</p>', 1, '2021-12-04 17:07:41', '2021-12-05 07:24:30'),
(54, 29, 'Course 4', 4, '<p>44</p>', 1, '2021-12-04 17:07:53', '2021-12-05 07:24:30'),
(55, 29, 'Course 5', 6, '<p>5</p>', 1, '2021-12-04 17:08:04', '2021-12-05 07:24:30'),
(58, 32, '1', 1, '<p><img style=\"display: block; margin-left: auto; margin-right: auto;\" src=\"https://vi.masterencode.com/admin/training/photos/ouvOHTJ3NAHFW84uvXL1XV20NPgMkms8X6O7IEqz.png\" /></p>', 1, '2021-12-20 07:43:02', '2021-12-20 07:43:02');

-- --------------------------------------------------------

--
-- Table structure for table `training_sessions`
--

CREATE TABLE `training_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `step_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dsc` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `enddatetime` datetime DEFAULT NULL,
  `duration` int(5) DEFAULT NULL,
  `zoom_id` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `join_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trainer_name` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `training_sessions`
--

INSERT INTO `training_sessions` (`id`, `step_id`, `type`, `title`, `dsc`, `datetime`, `enddatetime`, `duration`, `zoom_id`, `start_url`, `join_url`, `trainer_name`, `location`, `user_id`, `created_at`, `updated_at`) VALUES
(17, '86', 'sessions', 'demo', 'demo', '2021-12-20 15:40:00', NULL, 120, '95177069744', 'https://zoom.us/s/95177069744?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJ3ZWIiLCJjbHQiOjAsIm1udW0iOiI5NTE3NzA2OTc0NCIsInN0ayI6Ilk1QWl3S1ZzSnRCTk9maXBSLXBSV0RBNlNFTHYtVVdXRFBCblNCUjhIN28uQmdZZ1RTOHJaVnBLTjFwQ2FqRlBTRXc1YzBSR2RsZzBNMWR2U1ZGWE5sTkhWMHhBT0RjeU16bGlNbUZoWTJZNU9HTmpORFZoTVRJMU9HRTJOREJsWXpjMVltRXdNMlV6TlRGa1pqTXdObUprTmpJeE1EUmtNVE5tTmpnNFpHTTBZMlZsTWdBZ2RrTXZSVkpHYjJ4c05HOHpNVTVxVkRKWmVYQjRUR3huVjI0MVZWWTNOVThBQTJGM01RQUFBWDNZRXYwWUFCSjFBQUFBIiwiYXVkIjoiY2xpZW50c20iLCJ1aWQiOiJGNXZZa043bFNNR0NHNGI0ekE2MFBBIiwic2siOiIyOTEwNTE0MjkwMjAwMDU2NDgzIiwic3R5IjoxMDAsIndjZCI6ImF3MSIsImV4cCI6MTY0MDAxNDg2MiwiaWF0IjoxNjQwMDA3NjYyLCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.sp7CfmipFbof6h8UbZk8dYZVL3SogvConJcNFta3-Vs', 'https://zoom.us/j/95177069744?pwd=Ny8vWEo4RVUyNWFmR0pGMFJoV3k5UT09', 'Prof. J.A.', NULL, 1, '2021-12-20 13:41:02', '2021-12-23 13:23:32'),
(18, '86', 'online', 'كورس تدريبي شامل عن التعليم الفني', 'سيتم اتاحة هذا المحتوى مع كافة التدريبات الاخرى', '2021-12-25 09:00:00', NULL, NULL, NULL, NULL, NULL, NULL, '23', 1, '2021-12-23 09:03:02', '2021-12-23 13:23:45'),
(20, '86', 'offline', 'جلسة تعارف', 'الجلسة الأولى للتعارف', '2021-12-24 09:00:00', '2021-12-24 13:00:00', NULL, NULL, NULL, NULL, 'لواء/ سعد رواش', 'قاعة المؤتمرات', 1, '2021-12-23 09:12:16', '2021-12-23 13:23:48'),
(22, '86', 'sessions', 'demo', 'demo', '2021-12-28 15:37:00', NULL, 60, '93715146258', 'https://zoom.us/s/93715146258?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMSIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJ3ZWIiLCJjbHQiOjAsIm1udW0iOiI5MzcxNTE0NjI1OCIsInN0ayI6IjM3TjBtVUczNzFySzJWTElSUDZSaDhrSmNMU2pCSTFySHNKcDdQc3c5R2MuQmdZZ1RTOHJaVnBLTjFwQ2FqRlBTRXc1YzBSR2RsZzBNMWR2U1ZGWE5sTkhWMHhBT0RjeU16bGlNbUZoWTJZNU9HTmpORFZoTVRJMU9HRTJOREJsWXpjMVltRXdNMlV6TlRGa1pqTXdObUprTmpJeE1EUmtNVE5tTmpnNFpHTTBZMlZsTWdBZ2RrTXZSVkpHYjJ4c05HOHpNVTVxVkRKWmVYQjRUR3huVjI0MVZWWTNOVThBQTJGM01RQUFBWDM4SEYtOUFCSjFBQUFBIiwiYXVkIjoiY2xpZW50c20iLCJ1aWQiOiJGNXZZa043bFNNR0NHNGI0ekE2MFBBIiwic2siOiIyOTEwNTE0MjkwMjAwMDU2NDgzIiwic3R5IjoxMDAsIndjZCI6ImF3MSIsImV4cCI6MTY0MDYxOTQ1NywiaWF0IjoxNjQwNjEyMjU3LCJhaWQiOiJSWmh2ZlAzNlFiRzV1TTNFaXdnaDhnIiwiY2lkIjoiIn0.56P6KnDxwhlAxsvvHLOEoNgao6bjBrzn0e4Pp6I-TLM', 'https://zoom.us/j/93715146258?pwd=b3QvT0IrbFU0YXJST0c3cDg1Q25ZZz09', 'demo', NULL, 1, '2021-12-27 13:37:37', NULL),
(23, '86', 'online', 'معلومات عن السوشيال ميديا', NULL, '2021-12-31 15:43:00', NULL, NULL, NULL, NULL, NULL, NULL, '32', 1, '2021-12-27 13:43:23', NULL),
(24, '86', 'offline', 'الجلسة التدريبية الأولى للبرنامج', 'اليوم الأول', '2022-01-01 09:00:00', '2022-01-01 17:00:00', NULL, NULL, NULL, NULL, 'عصام عصام', 'القاعة الرئيسية', 1, '2021-12-27 13:44:22', '2021-12-27 12:44:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name_en` varchar(255) NOT NULL,
  `first_name_ar` varchar(250) DEFAULT NULL,
  `last_name_ar` varchar(255) DEFAULT NULL,
  `gender` varchar(7) NOT NULL DEFAULT 'Male',
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_ar` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_en` varchar(250) DEFAULT NULL,
  `HaveCompany` int(1) DEFAULT 1,
  `company_id` int(11) DEFAULT 0,
  `state_id` int(11) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
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
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `PageId` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name_en`, `last_name_en`, `first_name_ar`, `last_name_ar`, `gender`, `phone`, `email`, `title_ar`, `title_en`, `HaveCompany`, `company_id`, `state_id`, `city_id`, `dob`, `academic_stat`, `university`, `faculty`, `employer`, `expertise`, `email_verified_at`, `provider`, `provider_id`, `google_id`, `password`, `remember_token`, `img_url`, `PageId`, `created_at`, `updated_at`) VALUES
(1, 'Ibrahim', 'Elsayed', 'ابراهيم', 'السيد', 'Male', '01000415330', 'ibrahim.suez@gmail.com', 'مدير الموقع', 'Administrator', 1, 18, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2020-02-14 03:29:36', '', '', '', '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', 'D03PEtlKSE2MzTS23KWtekRamAYWxtzXItKqYHJKRD7sGs30uV1aAfiFaVQT', 'users/9Z1P8Z2OGaSsgoxvgCUGtWYxUikEzl98m4qFqbak.png', NULL, '2020-01-29 12:17:07', '2022-01-02 12:30:13'),
(2, 'Hanna', 'Saad', 'هناء', 'سعد', 'Male', '324564321', 'hany@masterencode.com', 'مدير فرع', 'Branch manager', 1, 8, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2020-02-17 07:00:00', '', '', '', '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'services/fqhkBqoBuOZzBUz5AxKdPgB9CBRWJphn5JO2cgPd.jpg', NULL, '2020-02-04 14:30:38', '2021-12-03 17:30:36'),
(3, 'Abdallah', 'Othman', 'عبد الله', 'عثمان', 'Male', '15464546', 'c@masterencode.com', 'مدير الموارد البشرية', 'HR Manager', 1, 10, 3, 18, NULL, NULL, NULL, NULL, NULL, NULL, '2020-02-17 07:00:00', '', '', '', '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'services/QEnZymF37ChLGOOUDOqvqGYoOCYXeg53pVentuVv.jpg', NULL, '2020-02-04 14:30:38', '2021-11-21 14:01:18'),
(8, 'Sayed', 'Ahmed', 'سيد', 'أحمد', 'Male', '123456789', 'info102@mail.com', NULL, NULL, NULL, 0, 7, 174, NULL, NULL, NULL, NULL, NULL, NULL, '2020-03-08 11:09:01', '', '', '', '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'services/ZzkMfZomDBG32AwuNJse0wHK5oIuGQQSL48zZKNZ.jpg', NULL, '2020-02-14 01:53:13', '2021-11-21 14:01:27'),
(10, 'Morad', 'Ali', 'مراد', 'علي', 'Male', '01545646546', 'ebrahimsuez@qmail.com', 'مدير', 'Manager', 1, 0, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, '2020-03-07 11:13:22', '', '', '', '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'services/K61gtcIzOYxMJsfj3UW3zMvuEtUb0QA0Jrz16mhH.jpg', NULL, '2020-02-14 03:31:42', '2021-12-25 08:41:41'),
(13, 'Sameh', 'Khalifa', 'سامح', 'خليفة', 'Male', '01026744447', 'tradeegypt@gmail.com', 'مدير الشركة', 'Manager', NULL, 4, 12, 22, NULL, NULL, NULL, NULL, NULL, NULL, '2021-11-13 13:33:28', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'users/s7kr9E1L6LMhIvSgVgvHPqQqjMsZBEwIfKSeJSuX.jpeg', NULL, '2020-02-16 01:37:33', '2021-12-20 07:27:04'),
(15, 'Kadry', 'Sayed', 'قدري', 'سيد', 'Male', '01224356565', 'pnsbusinesssolution@gmail.com', 'مساعد أول', 'Assistant', 1, 6, 9, 138, NULL, NULL, NULL, NULL, NULL, NULL, '2020-02-16 02:42:54', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'users/nQCXlsD4R5Qz9BzhOauYXcanpvvJfizQlJua8Fa9.jpg', NULL, '2020-02-16 02:42:20', '2021-11-20 14:08:17'),
(27, 'Dodi', 'Samir', 'دودي', 'سمير', 'Female', '54564654', 'mastersuez@yahoo.com', NULL, NULL, NULL, 0, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2020-02-16 20:04:43', 'facebook', '1488446967984213', NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-02-16 20:04:43', '2020-03-07 09:23:37'),
(31, 'Ibrahim', 'Abdo', 'ابراهيم', 'السيد', 'Male', '4646', 'ebrahim.suez@gmail.c', NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-02-16 21:00:35', NULL, NULL, '115470904256938814473', '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'services/hzoeWoKBu01mjSxcSx7p04qFxfzgBoUnPqes5oVa.jpg', NULL, '2020-02-16 21:00:35', '2021-11-21 14:01:48'),
(38, 'Ibrahim', 'Elsayed', 'ابراهيم', 'السيد', 'Male', '56465465', 'ibrahim@tvet2.org', 'مدير', 'Manager', 1, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2020-02-18 15:10:59', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-02-18 14:51:50', '2021-09-24 23:06:33'),
(39, 'Mohamed', 'Tawfik', 'محمد', 'العقدة', 'Male', '01223250218', 'mtawfik007@gmail.com', 'ICT', 'ict', 1, 11, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, '2020-02-18 14:55:57', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-02-18 14:55:20', '2021-11-20 14:08:17'),
(42, 'Fiona', 'Loan', 'فيونا', 'لوان', 'Female', NULL, 'iamfionafake@gmail.com', NULL, NULL, NULL, 0, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2020-02-19 17:26:41', 'facebook', '1001988168745', NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-02-19 17:26:41', '2020-02-19 17:26:41'),
(43, 'Ibrahim', 'Elsayed', 'ابراهيم', 'السيد', 'Male', '65434567', 'ioojsfaksjal@sdas.com', 'face', 'face', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2020-02-22 19:49:17', 'facebook', '2742082762494976', NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-02-22 19:49:17', '2020-03-10 03:40:43'),
(44, 'Yasmeen', 'Hosin', 'ياسمين', 'حسني', 'Female', '234567654', 'y@masterencode.com', 'خبيرة', 'Expert', NULL, 0, 2, 3, NULL, NULL, NULL, NULL, NULL, NULL, '2021-11-27 16:57:37', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'services/R5UtGV8DAB0HXsgiOhPVlR6OnYuqYsjEcMDSw5A1.jpg', NULL, '2020-02-29 11:24:36', '2021-11-27 16:57:37'),
(45, 'Magdy', 'Mero', 'مارو', 'مجدي', 'Male', '123123123', 'mtahsesden@tvet2.org', 'Mentor', 'Mentor', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-18 19:11:35', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'services/44HMXJDX37E0ohiHCITNWFBX2GFG37cDuhDqVKaw.jpg', NULL, '2020-02-29 11:25:36', '2021-12-22 11:30:42'),
(46, 'Mohammed', 'Metwally', 'محمد', 'متولي', 'Male', '76567890', 'mm@masterencode.com', 'مهندس', 'Eng', NULL, 0, 18, 135, NULL, NULL, NULL, NULL, NULL, NULL, '2021-11-26 17:28:53', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'services/Cygrs8afh1fNPOtVTWAjHb168KkEHQmB6NeMHLfc.jpg', NULL, '2020-02-29 11:50:18', '2021-11-26 17:29:39'),
(47, 'Matrix', 'Hero', NULL, NULL, 'Male', '123321123', 'sameh@masterencode.com', NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-02-29 11:54:15', '2020-02-29 11:54:15'),
(49, 'Bahaa', 'Hero', 'بهاء', 'هيرو', 'Male', '123321123', 'bahaa2@masterencode.com', NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-02-29 12:20:42', '2020-03-07 09:17:04'),
(50, 'Bahaa', 'Hero', 'بهاء', 'هيرو', 'Male', '123321123', 'b@masterencode.com', NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2020-02-28 22:00:00', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-02-29 12:25:09', '2020-03-07 09:17:42'),
(51, 'Marey', 'Marzook', 'مرعي', 'مرزوق', 'Male', '345231412521', 'm@masterencode.com', 'نقاش', 'Naqash', 1, 12, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2020-03-06 22:00:00', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'users/Leu3eOB5TiMtwX4eD97VnPtNBa46CrpV5mBFgN2U.jpeg', NULL, '2020-03-06 20:52:29', '2021-11-20 14:08:17'),
(52, 'Medo', 'Ahmed', 'ميدو', 'أحمد', 'Male', '123124125412124', 'z@masterencode.com', 'مدير العلاقات العامة', 'PR Manager', 1, 0, 10, 112, NULL, NULL, NULL, NULL, NULL, NULL, '2020-03-08 05:53:03', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-03-08 05:51:10', '2021-11-20 14:08:17'),
(53, 'Maro', 'Joun', 'مارو', 'جون', 'Male', '123123123', 'tree@masterencode.com', 'شجرة', 'Tree', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2020-03-08 05:53:03', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-03-08 06:33:59', '2021-12-18 19:11:11'),
(58, 'mado', 'medo', 'ميدو', 'ميدو', 'Male', '434543234', 'ebrahsuez@gmail.com', 'ميدو', 'Mido', NULL, 0, 17, 163, NULL, NULL, NULL, NULL, NULL, NULL, '2020-03-11 20:03:41', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-03-11 17:45:41', '2020-03-11 20:13:53'),
(65, 'tree', 'trerwfwq', NULL, NULL, 'Male', '54323', 'efse@esfwd.com', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-03-14 17:36:38', '2021-11-20 14:08:17'),
(66, 'Test', 'test', NULL, NULL, 'Male', '5678', 'rtew@wrgfw.com', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-03-15 15:48:54', '2021-11-20 14:08:17'),
(67, 'Demo', 'Demo', 'تيست', 'تيست', 'Male', '234234234', 'demo@demo.com', 'مدير', 'مدير', 1, 0, 14, 144, NULL, NULL, NULL, NULL, NULL, NULL, '2020-03-15 17:17:21', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-03-15 17:17:00', '2021-11-20 14:08:17'),
(69, 'Mahmoud', 'Abelsamad', 'محمود', 'عبد الصمد', 'Male', '34564633241', 'hodanew@hodacompany.com', 'خبير', 'Expert', 1, 0, 11, 176, NULL, NULL, NULL, NULL, NULL, NULL, '2020-03-17 09:59:37', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-03-17 09:43:58', '2021-11-20 14:08:17'),
(70, 'Marwa', 'Roshdy', NULL, NULL, 'Female', '3425341224', 'mero-roshdy@gmail.com', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-03-20 01:09:37', '2021-11-20 14:08:17'),
(71, 'Soso', 'Gamal', NULL, NULL, 'Male', '452342423', 'saso_gamal12@hotmail.com', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-03-20 01:11:47', '2021-11-20 14:08:17'),
(72, 'Reem', 'Mansour', 'ريم', 'منصور', 'Female', '1231235123', 'remo1212@yahoo.com', NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-03-20 01:14:26', '2021-12-20 16:35:48'),
(75, 'rfer', 'njk', NULL, NULL, 'Male', 'njkn', 'nkjn@wdsc.cp', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2020-03-23 18:05:36', '2021-11-20 14:08:17'),
(86, 'Medo', 'Hany', 'هاني', 'ميدو', 'Male', '0022453254225', 'ibrahim@nctu.edu.eg', 'المالك', 'Owner', 1, 14, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2021-10-21 19:29:28', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', '90HotNHXUhYSWuUh5ZVmpT6P8NFhvmg3eZfNMAsrEvgwbxALIKizM402uLmK', 'users/Nk53KLFYrHQvNTRplAFxqi14HwE62LHxmtTwwxQ8.jpg', NULL, '2021-10-21 18:57:02', '2021-12-12 13:42:30'),
(87, 'Mahmoud', 'Gamal', 'محمود', 'جمال', 'Male', '13412421', 'ebrahim.suez@gmail.com', 'مدير', 'Manager', 1, 16, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2021-11-07 15:37:04', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, 'users/WQwxTgQJ02Rlw46wAnGChTr2ED8kW4n9oQMRi8tm.jpg', NULL, '2021-11-07 15:34:48', '2021-11-20 14:08:17'),
(89, 'Mohammed', 'Ali', 'محمد', 'علي', 'Male', '033213213213', 'mmmdfd@gmail.com', 'demo', 'demo', 1, 17, 4, 68, NULL, NULL, NULL, NULL, NULL, NULL, '2021-11-08 12:36:30', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2021-11-08 10:34:19', '2021-11-20 14:08:17'),
(90, 'demo', 'demo', NULL, NULL, 'Male', '43235341', 'sdcsd@qef.com', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-11-08 14:11:45', NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, NULL, NULL, '2021-11-08 12:10:58', '2021-11-20 14:08:17'),
(91, 'demo', 'demo', 'demo', 'demo', 'Male', 'mwfwewf243255', 'dvs@wdsv.cp', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$d6t5UR.CM8Kf8bAjm.D7h.mxZRWybx5Y70uVG4XCnCAL/rCIk/Vf6', NULL, '', NULL, '2021-11-10 16:19:54', '2021-11-20 14:08:17'),
(92, 'Lamar', 'Ibrahim', 'لمار', 'ابراهيم', 'Female', '0123456789', 'lamaribrahim@gmail.com', 'مدير الشئون المالية', 'Finance Manager', 1, 19, 5, 201, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-04 06:18:08', NULL, NULL, NULL, '$2y$10$45OEDq0T9XZl6AJrRigVtO96qCNzKaw/kOYGFwuIwxVpkjsS34IIe', NULL, 'users/lR1D9HQV5nyhNxSPQHb2ptDF2k0oUQDPI9aBwp5O.jpg', NULL, '2021-12-04 06:16:27', '2021-12-04 06:23:56'),
(95, 'Tota', 'Yousef', 'أيات', 'يوسف', 'Female', '0100004000', 'tota.yousef@gmail.com', 'مدير', 'Manager', 1, 0, 21, 159, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-19 11:10:36', 'facebook', '2908564095892584', NULL, '$2y$10$l2K0HZhh0MBV6c9OQvajl.XQFoC52Q3PDE5cR3.jbjbKtSMZpztGm', NULL, NULL, NULL, '2021-12-19 11:10:36', '2021-12-19 12:16:10'),
(96, 'Mohammed', 'Ali', 'محمد', 'علي', 'Male', '01000415330', 'ibrahim@btu.edu.eg', 'مدير', 'Manager', 1, 0, 10, 107, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-19 16:17:38', NULL, NULL, '109284180896821319244', '$2y$10$AdhgS7wwEy1DHqDCjlKkt.6Ox3HWw8i5T0NkOrm7ENB9whuHgG45G', NULL, NULL, NULL, '2021-12-19 16:17:38', '2021-12-19 16:23:53'),
(97, 'Trader', 'Eg', 'تريد', 'مصر', 'Male', '123456987', 'trader.egypt@gmail.com', 'مدير', 'ةanager', 1, 20, 14, 144, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-20 07:28:49', NULL, NULL, NULL, '$2y$10$QMdZ58LQ/gLzVtAba/x6z.TtJV0iXNPb4UZHJD3s8WfHpWSuLOzYK', NULL, 'services/shu94MIB2L3ZOFZCFf1KIPlHiVjuqIBSTapvf4sr.jpg', NULL, '2021-12-20 07:27:53', '2021-12-22 11:28:36'),
(98, 'Hany', 'Mohammed', 'اسلام', 'محمد', 'Male', '45655661', 'islamm.orban@gmail.com', 'مدير', 'manager', 1, 0, 2, 14, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-20 10:58:05', NULL, NULL, NULL, '$2y$10$zF9TMPHntqVbAMLTIQhmWOYtQPS/F0ctuuMIX5ITPjNGimkSbGZMe', NULL, NULL, NULL, '2021-12-20 10:53:58', '2021-12-20 10:58:05'),
(99, 'Hassan', 'Saad', 'حسن', 'سعد', 'Male', '65845645654', 'mentor@masterencode.com', 'مرشد', 'Mentor', NULL, 0, 16, 186, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-22 10:15:55', NULL, NULL, NULL, '$2y$10$dyoYy5Ec69/CUiEGozoJfu77rS.wGKpeht85rFN8NBBD8gUl5JG5m', NULL, 'users/LbR6nAsbjcOIATtPMaZHWSgyrYXsgM03EwuQWhA7.jpg', NULL, '2021-12-22 10:12:06', '2021-12-22 10:17:04'),
(100, 'Reem', 'Kamal', 'ريم', 'كمال', 'Female', '15261415612', 'judger@masterencode.com', 'مهندسة', 'Engineer', NULL, 0, 3, 17, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-22 10:28:24', NULL, NULL, NULL, '$2y$10$8pQKlYFXofg/3XWjDV25cuYbBR4JheJNYXqzRXbyQHx0JMrSvjm.m', NULL, 'users/xa6sMLXFxVYOh2BImepZrV76FqSumIY7fzkJzBCA.jpg', NULL, '2021-12-22 10:27:13', '2021-12-22 10:29:16'),
(101, 'Amr', 'Mostafa', 'عمرو', 'مصطفى', 'Male', '5161651316513', 'user@masterencode.com', 'مدير المشروع', 'Project manager', 1, 21, 5, 201, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-22 10:31:12', NULL, NULL, NULL, '$2y$10$qo5K2tXQitdw4V/FSLu8GOyAGojVjZybCY1pyo.wE1MRHHCyJXq9a', NULL, 'services/i8ftflZaDPTQl5VDsPom2Sg2GWZfwMWjjXD9jgZw.jpg', NULL, '2021-12-22 10:30:50', '2021-12-22 10:49:09'),
(102, 'Hossam', 'Mohammed', 'حسام', 'محمد', 'Male', '65546546546', 'manager@masterencode.com', 'مدير نظام', 'System manager', NULL, 0, 9, 138, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-22 11:36:35', NULL, NULL, NULL, '$2y$10$KbdK2.8r/yXqWRdC03Po6ubdsqdGsrfPViJoggQA9KOZdyjlbyYAW', NULL, 'services/q7rQQFxjrcPMfTwmgE5A6lTpafaLE7MohCbJ1cie.jpg', NULL, '2021-12-22 11:36:19', '2021-12-22 11:37:47'),
(103, 'Lamia', 'Ali', 'لمياء', 'علي', 'Female', '012012012012', 'admin@masterencode.com', 'مديرة النظام', 'System admin', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-26 05:43:56', NULL, NULL, NULL, '$2y$10$oVssERr9Rh6zcWOR1XlxXeG9UikkjUs5UnnHp3qMrAtYBVWuDFCsa', NULL, 'services/orWYSNM76aAgfxJbSOROO50ZMHz00OlXLsRtl2Fm.jpg', NULL, '2021-12-26 05:43:37', '2021-12-26 05:46:07'),
(104, 'Hatem', 'Ali', 'حاتم', 'علي', 'Male', '1321231123', 'hatem.ali@gwmail.com', 'مهندس', 'Engineer', 1, 22, 4, 71, NULL, NULL, NULL, NULL, NULL, NULL, '2021-12-27 06:26:10', NULL, NULL, NULL, '$2y$10$BwZUzysN2ehgFgpnqy40qu9Ym5oBn5BFaH3mY5pIeNLeb67IIDXaK', NULL, 'users/JSEmFijXPfCJAKB5df3fbtf9lizyNlkbpFt31y4H.jpg', NULL, '2021-12-27 06:23:46', '2021-12-27 08:00:52');

-- --------------------------------------------------------

--
-- Table structure for table `users_bio`
--

CREATE TABLE `users_bio` (
  `user_id` int(11) NOT NULL,
  `bio_ar` varchar(255) DEFAULT NULL,
  `bio_en` varchar(255) DEFAULT NULL,
  `specialization_ar` varchar(250) DEFAULT NULL,
  `specialization_en` varchar(250) DEFAULT NULL,
  `linkedin` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users_bio`
--

INSERT INTO `users_bio` (`user_id`, `bio_ar`, `bio_en`, `specialization_ar`, `specialization_en`, `linkedin`, `created_at`, `updated_at`) VALUES
(13, 'ok', 'ok', 'الذكاء الصناعي', 'Artificial intelligence', 'https://www.linkedin.com/in/myprofile', '2021-11-25 22:34:52', '2021-11-30 10:50:53'),
(44, 'Here is my short bio in arabic', 'Here is my short bio in English', 'إلكترونيات', 'Electronics', 'https://www.linkedin.com/in/myprofile', '2021-11-27 18:58:17', '2021-12-03 08:32:23'),
(99, 'خبير نظم معلومات وحماية', 'IT & Secuirity Expert', 'خبير', 'Expert', 'https://linkedin.com', '2021-12-22 11:21:07', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `builder_fields`
--
ALTER TABLE `builder_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `builder_forms`
--
ALTER TABLE `builder_forms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `calendars`
--
ALTER TABLE `calendars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cities_state_id_foreign` (`state_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `companies_state_id_foreign` (`city_id`);

--
-- Indexes for table `cycles`
--
ALTER TABLE `cycles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cycles_mentors`
--
ALTER TABLE `cycles_mentors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documentations`
--
ALTER TABLE `documentations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events_users`
--
ALTER TABLE `events_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `finalscreenings`
--
ALTER TABLE `finalscreenings`
  ADD PRIMARY KEY (`step_id`,`company_id`);

--
-- Indexes for table `final_data`
--
ALTER TABLE `final_data`
  ADD PRIMARY KEY (`field_id`,`judge_id`,`company_id`) USING BTREE;

--
-- Indexes for table `form_data`
--
ALTER TABLE `form_data`
  ADD PRIMARY KEY (`field_id`,`company_id`) USING BTREE;

--
-- Indexes for table `form_fields`
--
ALTER TABLE `form_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inclusives`
--
ALTER TABLE `inclusives`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `incubators`
--
ALTER TABLE `incubators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mentorship_requests`
--
ALTER TABLE `mentorship_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messenger`
--
ALTER TABLE `messenger`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`(191),`notifiable_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Unique` (`url`);

--
-- Indexes for table `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `press`
--
ALTER TABLE `press`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms_imgs`
--
ALTER TABLE `rooms_imgs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_bookings`
--
ALTER TABLE `room_bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indexes for table `screenings`
--
ALTER TABLE `screenings`
  ADD PRIMARY KEY (`step_id`,`user_id`,`panel`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services_requests`
--
ALTER TABLE `services_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `steps`
--
ALTER TABLE `steps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trainings`
--
ALTER TABLE `trainings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `training_completed`
--
ALTER TABLE `training_completed`
  ADD PRIMARY KEY (`training_id`,`user_id`);

--
-- Indexes for table `training_contents`
--
ALTER TABLE `training_contents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`(191)) USING BTREE;

--
-- Indexes for table `users_bio`
--
ALTER TABLE `users_bio`
  ADD PRIMARY KEY (`user_id`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `builder_fields`
--
ALTER TABLE `builder_fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=717;

--
-- AUTO_INCREMENT for table `builder_forms`
--
ALTER TABLE `builder_forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `calendars`
--
ALTER TABLE `calendars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000190;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `cycles`
--
ALTER TABLE `cycles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `cycles_mentors`
--
ALTER TABLE `cycles_mentors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `documentations`
--
ALTER TABLE `documentations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=500007;

--
-- AUTO_INCREMENT for table `events_users`
--
ALTER TABLE `events_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `form_fields`
--
ALTER TABLE `form_fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `inclusives`
--
ALTER TABLE `inclusives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `incubators`
--
ALTER TABLE `incubators`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=300;

--
-- AUTO_INCREMENT for table `mentorship_requests`
--
ALTER TABLE `mentorship_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(13) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `messenger`
--
ALTER TABLE `messenger`
  MODIFY `id` int(15) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `press`
--
ALTER TABLE `press`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rooms_imgs`
--
ALTER TABLE `rooms_imgs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `room_bookings`
--
ALTER TABLE `room_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `services_requests`
--
ALTER TABLE `services_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `steps`
--
ALTER TABLE `steps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `trainings`
--
ALTER TABLE `trainings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `training_contents`
--
ALTER TABLE `training_contents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `training_sessions`
--
ALTER TABLE `training_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
