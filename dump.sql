-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 23, 2025 at 12:57 PM
-- Server version: 11.4.5-MariaDB
-- PHP Version: 8.3.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ricl5954_preprod_old_back_aeropaperasse`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_comments`
--

CREATE TABLE `activity_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `activity_request_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_requests`
--

CREATE TABLE `activity_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `renouvellement` tinyint(1) NOT NULL DEFAULT 0,
  `autorisation_anterieur` varchar(255) DEFAULT NULL,
  `raison_sociale` varchar(255) DEFAULT NULL,
  `nom_commercial` varchar(255) DEFAULT NULL,
  `siret` varchar(255) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `responsable_nom` varchar(255) DEFAULT NULL,
  `responsable_prenom` varchar(255) DEFAULT NULL,
  `responsable_email` varchar(255) DEFAULT NULL,
  `responsable_telephone` varchar(255) DEFAULT NULL,
  `responsable_fonction` varchar(255) DEFAULT NULL,
  `activite_description` text DEFAULT NULL,
  `nombre_personnes` int(11) DEFAULT NULL,
  `nombre_vehicules` int(11) DEFAULT NULL,
  `clients_denomination` varchar(255) DEFAULT NULL,
  `extrait_kbis_path` varchar(255) DEFAULT NULL,
  `attestations_clients_path` varchar(255) DEFAULT NULL,
  `formulaire_surete_path` varchar(255) DEFAULT NULL,
  `agrement_prefectoral_path` varchar(255) DEFAULT NULL,
  `contrat_iata_path` varchar(255) DEFAULT NULL,
  `cta_path` varchar(255) DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected') NOT NULL DEFAULT 'draft',
  `previous_status` varchar(255) DEFAULT NULL,
  `draft_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `pending_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_requests`
--

INSERT INTO `activity_requests` (`id`, `user_id`, `renouvellement`, `autorisation_anterieur`, `raison_sociale`, `nom_commercial`, `siret`, `adresse`, `responsable_nom`, `responsable_prenom`, `responsable_email`, `responsable_telephone`, `responsable_fonction`, `activite_description`, `nombre_personnes`, `nombre_vehicules`, `clients_denomination`, `extrait_kbis_path`, `attestations_clients_path`, `formulaire_surete_path`, `agrement_prefectoral_path`, `contrat_iata_path`, `cta_path`, `status`, `previous_status`, `draft_at`, `created_at`, `updated_at`, `created_by`, `pending_at`, `approved_at`, `rejected_at`) VALUES
(10, NULL, 0, NULL, 'eee', 'eeeee', 'ee', 'zazadza', 'azdza', 'dzadza', 'pasutto.evan@gmail.com', 'zadza', 'dzazda', 'dzazda', 7, 11, 'SADRIN RAPIN', 'activity_requests/choY93KRZLvz3DiVJIlPSjgG9gi9AQuVZs9z5IAq.pdf', 'activity_requests/9NN2VCTTBsYD1zaiQmhEBPm5HbXGHnH9gmt6NNfO.pdf', 'activity_requests/HaVgrq0PvHL51RB1jm5bA8SeK1QbA92SQjiyV5i8.pdf', 'activity_requests/7bpOssrR9W0Vs4GxDtiVf7p2oiMRwMIfWc9xvX0K.pdf', 'activity_requests/sERo8j0r5d09lR6b4EaNtReus9tKiL7wOORTlW3A.pdf', 'activity_requests/NTPjCR4bZ7gXhbObjgzVUjTn2lGv45H2zsnhEsiI.pdf', 'rejected', NULL, NULL, '2025-03-11 12:41:31', '2025-03-11 12:41:46', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `message_id` bigint(20) UNSIGNED NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `badge_request_id` bigint(20) UNSIGNED NOT NULL,
  `badge_number` varchar(255) NOT NULL,
  `status` enum('active','expired','returned','not_returned') NOT NULL DEFAULT 'active',
  `previous_status` varchar(255) DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `return_document` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `badge_request_id`, `badge_number`, `status`, `previous_status`, `expiry_date`, `returned_at`, `return_document`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 19, '12355', 'active', NULL, '2026-03-11', NULL, NULL, '2025-03-11 12:22:05', '2025-03-11 12:22:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `badge_requests`
--

CREATE TABLE `badge_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `airport` varchar(255) DEFAULT NULL,
  `draft_at` timestamp NULL DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `status` enum('draft','pending_rem','rejected_rem','pending_adp','approved_adp','rejected_adp','pending_fabrication','ready_for_delivery') NOT NULL DEFAULT 'pending_rem',
  `previous_status` varchar(255) DEFAULT NULL,
  `reject_reason` text DEFAULT NULL,
  `photoIdentite` varchar(255) DEFAULT NULL,
  `pieceIdentite` varchar(255) DEFAULT NULL,
  `autorisationActivite` varchar(255) DEFAULT NULL,
  `certificatFormation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pending_rem_at` timestamp NULL DEFAULT NULL,
  `rejected_rem_at` timestamp NULL DEFAULT NULL,
  `pending_adp_at` timestamp NULL DEFAULT NULL,
  `approved_adp_at` timestamp NULL DEFAULT NULL,
  `rejected_adp_at` timestamp NULL DEFAULT NULL,
  `pending_fabrication_at` timestamp NULL DEFAULT NULL,
  `ready_for_delivery_at` timestamp NULL DEFAULT NULL,
  `est_habilitation` tinyint(1) NOT NULL DEFAULT 0,
  `documentFor` varchar(255) DEFAULT NULL,
  `facture` varchar(255) DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badge_requests`
--

INSERT INTO `badge_requests` (`id`, `user_id`, `airport`, `draft_at`, `nom`, `prenom`, `email`, `telephone`, `status`, `previous_status`, `reject_reason`, `photoIdentite`, `pieceIdentite`, `autorisationActivite`, `certificatFormation`, `created_at`, `updated_at`, `pending_rem_at`, `rejected_rem_at`, `pending_adp_at`, `approved_adp_at`, `rejected_adp_at`, `pending_fabrication_at`, `ready_for_delivery_at`, `est_habilitation`, `documentFor`, `facture`, `client_id`, `created_by`) VALUES
(19, 24, 'aeroportCDG', NULL, 'ffidfod', 'dqsd', 'pasutto.evan@gmail.com', 'daaezaezezae', 'ready_for_delivery', 'pending_fabrication', NULL, 'photos/f4cSrd314d6GZgpRuaQ9omePwCxNNmdACf54UnuU.jpg', NULL, 'autorisations/P1XU5InYen7opwXCZ3ItKMbwTKPrEvjLgNLKyU8I.pdf', 'certificats/iXVybugi0stU6spRlepnuau5rLw94aNY4szdTHCV.pdf', '2025-03-11 12:17:05', '2025-03-11 12:19:53', '2025-03-11 12:17:05', NULL, '2025-03-11 12:18:20', '2025-03-11 12:19:07', NULL, '2025-03-11 12:19:39', '2025-03-11 12:19:53', 0, 'documents_for/PJvSkRfdV5fUfVXFgYLODRdduwSzl0v7YhMxdQs5.xlsx', 'factures/2IsxuwIEUj3M80RQwthj9SPNL6hj8Cphmlyyzzd8.pdf', NULL, NULL),
(20, 24, 'aeroportCDG', NULL, 'Laigneau', 'David', 'jvi@sadrin-rapin.fr', '02.43.84.44.29', 'pending_adp', 'pending_rem', NULL, 'photos/DbkCUjFxvcIhFIDUINQRsd5blnc7ZHDNIYTspqCz.jpg', NULL, 'autorisations/TKrv4258mX0ObH6QidybaXj5D8lomGA1jiwaNgL6.pdf', 'certificats/Jh7ubA9gVNSqmhHOyIn8fU5hBANbuenUlbv5AMfB.pdf', '2025-03-12 15:40:25', '2025-03-12 15:50:37', '2025-03-12 15:40:25', NULL, '2025-03-12 15:50:37', NULL, NULL, NULL, NULL, 0, 'documents_for/1Ubdh26PPeDVTBXfvLWZEsCp4uywPYmElDjTjxUL.xlsx', 'factures/foTNyzUSlpyrWvMKXEgYWVW7WOy4kaHjMXE2tzWl.pdf', NULL, NULL),
(21, 25, 'aeroportCDG', '2025-03-15 06:59:54', 'eztezt', 'qtdtq', NULL, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-15 06:59:54', '2025-03-15 06:59:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(22, 25, 'aeroportCDG', '2025-03-17 15:29:35', NULL, NULL, NULL, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-17 15:29:35', '2025-03-17 15:29:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(23, 25, 'aeroportCDG', '2025-03-20 15:32:32', NULL, NULL, NULL, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-20 15:32:32', '2025-03-20 15:32:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, NULL),
(24, 1, 'aeroportOrly', NULL, 'dupin', 'test', 'n.dupin@r4web.fr', '0619180765', 'pending_rem', NULL, NULL, 'photos/rnvHmVU5uMUAire8vOJUnGagykxpeFlAKSirxluh.jpg', 'pieces_identite/WumN8snwZQtdxEaZcNvQMMgUyHiA5FQo80N7TlUM.png', 'autorisations/zruuVTuHS6JzfiK7wmo4Q1FBSM4NRznDXhyLt4CH.png', 'certificats/EE03dGLqAGBOy5AimXA0NiiLue9vKts2nqTC1wnD.png', '2025-03-24 15:17:06', '2025-03-24 15:17:06', '2025-03-24 15:17:06', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'documents_for/nMn6dzWhRa8V5ixDJ8wzqFybq1AcBb4RXujJbCwy.png', 'factures/j0BfaxJbo1e021zR3VG5a1rOmsQHUEygLdDPtgue.png', 6, 1),
(25, 1, 'aeroportOrly', NULL, 'dupin', 'nicolas', 'n.dupin@r4web.fr', '0619180765', 'pending_adp', 'pending_rem', NULL, 'photos/PbfTOlJZn7XybcnTir30fkr4jvPIFkGMW5G0bBQP.jpg', 'pieces_identite/zUldwsRxX9WPAQnOsI8Cc6LsjUVj0mO3UZbCLPrt.png', 'autorisations/kM10LPooB3xp6zPqCRXibA6ggBqhCKV90dgdXorA.png', 'certificats/rOvm0jbm5AdYuKoa1uUD4wJO8v5nfF6bkqVKcZQf.png', '2025-03-24 15:18:25', '2025-04-07 07:26:24', '2025-03-24 15:18:25', NULL, '2025-04-07 07:26:24', NULL, NULL, NULL, NULL, 0, 'documents_for/mXVqrUuvVdDpU5IaCtDvpI2HU0dn6dJQXSXZSl8g.png', 'factures/ztgSIqUkhXDRGivgeeYbVqRaHv5Ckd3gQwJ6jO7D.png', 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `referent_name` varchar(255) DEFAULT NULL,
  `referent_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `safety_referent_name_1` varchar(255) DEFAULT NULL,
  `safety_referent_email_1` varchar(255) DEFAULT NULL,
  `safety_referent_phone_1` varchar(255) DEFAULT NULL,
  `safety_referent_name_2` varchar(255) DEFAULT NULL,
  `safety_referent_email_2` varchar(255) DEFAULT NULL,
  `safety_referent_phone_2` varchar(255) DEFAULT NULL,
  `safety_referent_name_3` varchar(255) DEFAULT NULL,
  `safety_referent_email_3` varchar(255) DEFAULT NULL,
  `safety_referent_phone_3` varchar(255) DEFAULT NULL,
  `security_correspondent_name` varchar(255) DEFAULT NULL,
  `security_correspondent_email` varchar(255) DEFAULT NULL,
  `security_correspondent_phone` varchar(255) DEFAULT NULL,
  `kbis_document` varchar(255) DEFAULT NULL,
  `hr_contact_name` varchar(255) DEFAULT NULL,
  `hr_contact_email` varchar(255) DEFAULT NULL,
  `hr_contact_phone` varchar(255) DEFAULT NULL,
  `safety_document` varchar(255) DEFAULT NULL,
  `security_document` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `referent_name`, `referent_email`, `created_at`, `updated_at`, `safety_referent_name_1`, `safety_referent_email_1`, `safety_referent_phone_1`, `safety_referent_name_2`, `safety_referent_email_2`, `safety_referent_phone_2`, `safety_referent_name_3`, `safety_referent_email_3`, `safety_referent_phone_3`, `security_correspondent_name`, `security_correspondent_email`, `security_correspondent_phone`, `kbis_document`, `hr_contact_name`, `hr_contact_email`, `hr_contact_phone`, `safety_document`, `security_document`) VALUES
(1, 'admin', NULL, NULL, '2025-01-25 16:11:48', '2025-01-27 08:23:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'TEST', NULL, NULL, '2025-03-11 12:12:00', '2025-03-11 12:12:00', 'dzadzzda', 'dazdazazd@DZAZDA.com', 'dzazaddaz', NULL, NULL, NULL, NULL, NULL, NULL, 'dzadza', 'dzdza@daz.com', 'dzadza', 'client_documents/kbis/wWrfhcO2GrbGipf5KmrQ8OaorKiDEIxlLwZEMBfm.pdf', 'dzdaz', 'dazdz@dazazd.com', 'daadzadz', 'client_documents/safety_referents/8dZ1XHWTBOYQpcJHdLBtQUuplFnBufGdXdiyLwcw.pdf', 'client_documents/security_correspondents/mkVf1NtaSOmVdAjsfIG7LMgqMK9kEZexKtcHiVop.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `client_training_access`
--

CREATE TABLE `client_training_access` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `access_starts_at` timestamp NULL DEFAULT NULL,
  `access_expires_at` timestamp NULL DEFAULT NULL,
  `max_users` int(11) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `badge_request_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `content`, `user_id`, `badge_request_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 'Mince', 25, 21, '2025-03-17 15:27:19', '2025-03-17 15:27:19', NULL),
(8, 'hgfhgfghf', 25, 20, '2025-03-20 15:35:32', '2025-03-20 15:35:39', '2025-03-20 15:35:39');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `object` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discussions`
--

CREATE TABLE `discussions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `last_comment_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discussions`
--

INSERT INTO `discussions` (`id`, `user_id`, `last_comment_user_id`, `subject`, `content`, `status`, `created_at`, `updated_at`) VALUES
(8, 26, NULL, 'j\'ai une question', 'lhkljhj', 'open', '2025-03-11 12:24:11', '2025-03-11 12:30:14');

-- --------------------------------------------------------

--
-- Table structure for table `discussion_files`
--

CREATE TABLE `discussion_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `discussion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `message_comment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discussion_files`
--

INSERT INTO `discussion_files` (`id`, `name`, `path`, `discussion_id`, `message_comment_id`, `created_at`, `updated_at`) VALUES
(8, 'AAO 2025.pdf', 'discussion-files/kNymnUxP8gQklk8SK7cfdzhgFX0Pwxqmi8MFDaAv.pdf', 8, NULL, '2025-03-11 12:24:11', '2025-03-11 12:24:11');

-- --------------------------------------------------------

--
-- Table structure for table `discussion_read_status`
--

CREATE TABLE `discussion_read_status` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `discussion_id` bigint(20) UNSIGNED NOT NULL,
  `last_read_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discussion_read_status`
--

INSERT INTO `discussion_read_status` (`id`, `user_id`, `discussion_id`, `last_read_at`, `created_at`, `updated_at`) VALUES
(1, 25, 8, '2025-03-14 13:12:42', '2025-03-14 13:12:42', '2025-03-14 13:12:42');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_comments`
--

CREATE TABLE `message_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `discussion_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `message_comments`
--

INSERT INTO `message_comments` (`id`, `user_id`, `discussion_id`, `parent_id`, `content`, `created_at`, `updated_at`) VALUES
(8, 26, 8, NULL, 're', '2025-03-11 12:27:47', '2025-03-11 12:27:47'),
(9, 25, 8, NULL, 'nhfhgfghvg', '2025-03-11 12:28:41', '2025-03-11 12:28:41'),
(10, 24, 8, NULL, 'test', '2025-03-11 12:30:14', '2025-03-11 12:30:14');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2024_11_26_144317_add_role_to_users_table', 1),
(6, '2024_11_26_174624_create_badge_requests_table', 1),
(7, '2024_11_27_084548_create_comments_table', 1),
(8, '2024_11_27_084549_create_replies_table', 1),
(9, '2024_11_27_104056_add_user_id_to_badge_requests_table', 1),
(10, '2024_11_27_104436_add_foreign_key_to_badge_requests_table_step2', 1),
(11, '2024_11_27_132714_create_clients_table', 1),
(12, '2024_11_29_141257_create_conversations_table', 1),
(13, '2024_12_01_112925_create_discussions_table', 1),
(14, '2024_12_01_112951_create_message_comments_table', 1),
(15, '2024_12_01_113017_create_discussion_files_table', 1),
(16, '2025_01_27_104828_create_activity_requests_table', 2),
(17, '2025_01_27_125027_create_activity_comments', 2),
(18, '2025_01_27_130702_create_reply_activity_table', 2),
(19, '2025_01_28_140625_create_training_system_tables', 3),
(20, '2025_01_28_160842_remove_progress_from_user_trainings_table', 4),
(21, '2025_02_17_142106_create_badges_table', 4),
(22, '2025_02_18_123003_create_two_factor_codes_table', 5),
(23, '2025_02_18_123034_add_two_factor_enabled_to_users_table', 5),
(24, '2025_02_17_105236_update_training_tables_structure', 6),
(25, '2025_02_17_141452_create_training_catalogs_table', 6),
(26, '2025_02_17_154520_update_trainings_table_structure', 6),
(27, '2025_02_20_135817_update_badge_requests_table_with_detailed_status', 6),
(28, '2025_02_20_162349_remove_approved_rem_from_badge_requests_table', 6),
(29, '2025_02_21_102943_update_users_two_factor_default', 6),
(30, '2025_02_25_154611_add_new_fields_to_badge_requests_table', 7),
(31, '2025_02_25_163611_allow_null_certificat_formation', 7),
(32, '2025_02_26_101757_add_airport_to_badge_requests_table', 7),
(33, '2025_02_26_111115_rename_document_for_column', 7),
(34, '2025_03_03_133658_add_additional_fields_to_clients_table', 8),
(35, '2025_03_05_130853_add_departure_date_to_users_table', 8),
(36, '2025_03_05_141925_add_has_left_to_users_table', 8),
(37, '2025_03_06_143304_add_is_read_to_discussions_table', 8),
(38, '2025_03_07_104910_add_previous_status_to_badges_table', 9),
(39, '2025_03_07_124804_add_reject_reason_to_badge_requests', 10),
(40, '2025_03_07_155815_add_last_comment_user_id_to_discussions_table', 10),
(42, '2025_03_12_091520_update_clients_table_simplify_documents', 11),
(43, '2025_03_12_113145_create_discussion_read_status_table', 11),
(44, '2025_03_12_154455_add_draft_at_to_badge_requests_table', 12),
(45, '2025_03_14_112630_add_draft_status_to_badge_requests_table', 12),
(46, '2025_03_14_113128_allow_null_fields_for_drafts', 12),
(47, '2025_03_17_104846_add_client_id_and_created_by_to_badge_requests', 13),
(48, '2025_03_17_115035_add_identity_document_to_badge_requests', 13),
(49, '2025_03_17_124210_rename_identity_document_in_badge_requests', 13),
(50, '2025_03_19_132223_add_is_new_column_to_users_table', 14),
(51, '2025_03_17_154953_add_is_student_to_users_table', 15),
(52, '2025_03_24_115317_update_activity_requests_table', 15),
(53, '2025_03_24_153619_update_activity_requests_table_to_allow_null_fields', 15),
(54, '2025_03_25_092831_add_timestamp_fields_to_activity_requests', 15),
(55, '2025_03_25_111558_add_duration_hours_to_trainings_table', 15),
(56, '2025_03_25_112454_remove_start_date_and_expiry_date_from_trainings', 15),
(57, '2025_03_25_113548_add_duration_days_to_trainings_table', 15);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'auth_token', '064adcdfd7bc7081ee7dd557091f22049d552cc410c444a73790c2aa7c7ee343', '[\"*\"]', NULL, NULL, '2025-01-24 10:31:00', '2025-01-24 10:31:00'),
(2, 'App\\Models\\User', 1, 'auth_token', '1b0a95bab6efc075f459ced548c09ac0d209111f06f1d82564a050dcd283fa96', '[\"*\"]', '2025-01-24 13:35:55', NULL, '2025-01-24 11:36:59', '2025-01-24 13:35:55'),
(3, 'App\\Models\\User', 1, 'auth_token', '07162282e0a27668e6ddb8baf4d4b0f6710076993e89e986ab70ff49ca0069e3', '[\"*\"]', NULL, NULL, '2025-01-24 13:11:38', '2025-01-24 13:11:38'),
(4, 'App\\Models\\User', 1, 'auth_token', 'd1c75e405069dcea6e2e30a8ea63d5757889334de9a1f553408b8ba14d047295', '[\"*\"]', NULL, NULL, '2025-01-24 13:12:23', '2025-01-24 13:12:23'),
(5, 'App\\Models\\User', 1, 'auth_token', 'af1d99d693875831801fc7b2c677414e84683cb79c0cfbdcab3b2ada21a84442', '[\"*\"]', NULL, NULL, '2025-01-24 13:23:38', '2025-01-24 13:23:38'),
(6, 'App\\Models\\User', 1, 'auth_token', '9e752c9ca2422b4c951b50c781d542f6ffe91dede0b674556d5758b15a8bc224', '[\"*\"]', NULL, NULL, '2025-01-24 15:01:30', '2025-01-24 15:01:30'),
(7, 'App\\Models\\User', 1, 'auth_token', '8b9a25a0df8afa8ed2ac695442bc5446578e6ba738aba479a277b0498766fd7a', '[\"*\"]', NULL, NULL, '2025-01-24 15:02:05', '2025-01-24 15:02:05'),
(8, 'App\\Models\\User', 1, 'auth_token', '56a1cce41070e7ec955f27013c342035123e90e99500a09902e7e91994c196cd', '[\"*\"]', NULL, NULL, '2025-01-24 15:06:07', '2025-01-24 15:06:07'),
(9, 'App\\Models\\User', 1, 'auth_token', '61f627e8ed7d4a623ca36980503b37ce67a2aa9fc37a273980b849697c0c93ff', '[\"*\"]', '2025-01-24 16:54:20', NULL, '2025-01-24 16:54:12', '2025-01-24 16:54:20'),
(16, 'App\\Models\\User', 1, 'auth_token', 'fe0014c7bcc4734fa028522140b6c34407b8bfc5b4d9514e7e52748e36bedc65', '[\"*\"]', NULL, NULL, '2025-01-25 16:04:40', '2025-01-25 16:04:40'),
(17, 'App\\Models\\User', 1, 'auth_token', 'ed04f766b4323d639267d8aa69da2d3aa34f09eeef64d0ed12dbb5add9fee227', '[\"*\"]', NULL, NULL, '2025-01-25 16:04:48', '2025-01-25 16:04:48'),
(18, 'App\\Models\\User', 1, 'auth_token', '8c0f903b607e030235208fd7c572cb09ae2f44a0e3d393edf967bd43f2f70b39', '[\"*\"]', NULL, NULL, '2025-01-25 16:04:51', '2025-01-25 16:04:51'),
(19, 'App\\Models\\User', 1, 'auth_token', '74ad7cfaadb4da2575844bd2faa322d9f33deed01e48ac92536fa5e97547b60d', '[\"*\"]', NULL, NULL, '2025-01-25 16:04:55', '2025-01-25 16:04:55'),
(21, 'App\\Models\\User', 1, 'auth_token', 'af7199288750668e3881b9abaa4492f9d02f5c964f7b59ed07cf929f7051098c', '[\"*\"]', '2025-02-28 09:03:13', NULL, '2025-01-25 16:07:46', '2025-02-28 09:03:13'),
(22, 'App\\Models\\User', 1, 'auth_token', '0628459faa3a109fabc585f561a95e9c5533bf2f71d4cef8161c5b6b59d068b3', '[\"*\"]', '2025-01-25 16:12:37', NULL, '2025-01-25 16:09:32', '2025-01-25 16:12:37'),
(26, 'App\\Models\\User', 2, 'auth_token', 'c3d2ad12023c30969b913273dadcf701595780750aa124f71ae4835f35c95b28', '[\"*\"]', '2025-01-29 10:06:35', NULL, '2025-01-27 14:07:16', '2025-01-29 10:06:35'),
(30, 'App\\Models\\User', 1, 'auth_token', 'b6688d9f638ac0fe895447e616768c1fcb4ccb7d73e53a726fe8c99d3006763b', '[\"*\"]', '2025-02-20 10:34:50', NULL, '2025-01-29 09:56:59', '2025-02-20 10:34:50'),
(31, 'App\\Models\\User', 2, 'auth_token', '9ffb3b03afe790cb48a3e090341bfa4c5e7c674f53f29d4e0791e3d7c091b099', '[\"*\"]', '2025-02-04 10:17:28', NULL, '2025-01-29 10:07:09', '2025-02-04 10:17:28'),
(33, 'App\\Models\\User', 2, 'auth_token', 'd5b8082ee0fb4ba42596de1d70042223d5c5eaa208dc8034cd25249189c50da9', '[\"*\"]', '2025-02-10 12:54:06', NULL, '2025-02-10 12:43:35', '2025-02-10 12:54:06'),
(39, 'App\\Models\\User', 2, 'auth_token', 'eef252116e91b56ee03e76b9b44f054161295553a843d8670c4855af6fa896ec', '[\"*\"]', '2025-03-01 08:37:21', NULL, '2025-02-18 05:33:02', '2025-03-01 08:37:21'),
(43, 'App\\Models\\User', 2, 'auth_token', '7283e709e6a6a43c87ec85020e0f329c246683e0cb1ed401972f695a537df3d4', '[\"*\"]', '2025-02-19 13:07:56', NULL, '2025-02-19 11:45:44', '2025-02-19 13:07:56'),
(56, 'App\\Models\\User', 7, 'auth_token', '9738a1fe5064374ceb4df50d0272a22b8bf6786ba521bff1158a6d9eef9b0ae1', '[\"*\"]', '2025-02-21 09:01:54', NULL, '2025-02-21 09:01:53', '2025-02-21 09:01:54'),
(57, 'App\\Models\\User', 1, 'auth_token', 'a28e2b2987692f8db0703cb9487432f9bdb647331b3ab4dc9aaa3a636a261f68', '[\"*\"]', '2025-02-21 09:17:02', NULL, '2025-02-21 09:06:39', '2025-02-21 09:17:02'),
(58, 'App\\Models\\User', 1, 'auth_token', '999ee161b7cca317cc3a379c4c78c7d147762e5d00fb10c0deb29c0cfd247dc9', '[\"*\"]', '2025-02-21 09:17:09', NULL, '2025-02-21 09:17:05', '2025-02-21 09:17:09'),
(59, 'App\\Models\\User', 1, 'auth_token', 'a113e03dc739dcca1388ba7a08bea63bb4394d1a3ee8c062f9b48fe65bc8e0ce', '[\"*\"]', '2025-02-21 11:56:43', NULL, '2025-02-21 09:17:12', '2025-02-21 11:56:43'),
(64, 'App\\Models\\User', 1, 'auth_token', '3321d4ab4b5530d515eae90dc483311bcb1f029159486d166a232dbc2b5ecb90', '[\"*\"]', '2025-02-21 14:48:18', NULL, '2025-02-21 14:37:52', '2025-02-21 14:48:18'),
(65, 'App\\Models\\User', 1, 'auth_token', 'ccfa04cebc03330d0fd59dfb82c53ed4f231b95aa74d98e41015b4f957991ce3', '[\"*\"]', '2025-02-21 14:50:00', NULL, '2025-02-21 14:48:21', '2025-02-21 14:50:00'),
(66, 'App\\Models\\User', 2, 'auth_token', 'a905c5a9a3c020ac1d490a5b48a2c251672d932798d6f54d8a55bd59660230ae', '[\"*\"]', '2025-02-21 15:03:36', NULL, '2025-02-21 14:52:51', '2025-02-21 15:03:36'),
(69, 'App\\Models\\User', 2, 'auth_token', '1510d702364d4265ae5bf8cf21ba142d594f5159374b502c13d0ca06a65dd1f9', '[\"*\"]', '2025-02-28 09:02:37', NULL, '2025-02-28 08:59:35', '2025-02-28 09:02:37'),
(72, 'App\\Models\\User', 1, 'auth_token', 'b1e6c72cda707c8fa29cd1f23766ec3080d82796e5454d99925eda56be9a3d81', '[\"*\"]', '2025-02-28 09:09:37', NULL, '2025-02-28 09:08:11', '2025-02-28 09:09:37'),
(73, 'App\\Models\\User', 2, 'auth_token', 'bbe145478225e13ccf7bf067165cabfec4adeb23690d8c1e6945b6f547d65c79', '[\"*\"]', '2025-02-28 09:09:11', NULL, '2025-02-28 09:09:11', '2025-02-28 09:09:11'),
(74, 'App\\Models\\User', 1, 'auth_token', '26e160045cb95df0ec955ac6abd751f127f4621d7ec4a95db825e5bfc1830ff4', '[\"*\"]', '2025-02-28 09:16:09', NULL, '2025-02-28 09:15:33', '2025-02-28 09:16:09'),
(75, 'App\\Models\\User', 1, 'auth_token', '7028a64119c0b68eebbdc9fdefae3e4a72191ed58648f8dd4b28d00ae7db26af', '[\"*\"]', '2025-03-09 14:34:47', NULL, '2025-02-28 09:19:00', '2025-03-09 14:34:47'),
(85, 'App\\Models\\User', 2, 'auth_token', 'cc0b077a13d06079d98cf519a60a5c6325c2a8e26533131fdd1eb705ba561fd9', '[\"*\"]', '2025-03-04 09:59:16', NULL, '2025-03-03 09:03:08', '2025-03-04 09:59:16'),
(86, 'App\\Models\\User', 1, 'auth_token', '1634c1491a63715a228f4a4182b84e65fd43802591cf7a6a817e9322f7733b93', '[\"*\"]', '2025-03-10 19:35:02', NULL, '2025-03-04 09:54:53', '2025-03-10 19:35:02'),
(87, 'App\\Models\\User', 2, 'auth_token', 'd8f639aa3f8577643efc4d8d013a59c24f4e43864a0d07c7d6110af7db68f070', '[\"*\"]', '2025-03-06 15:07:13', NULL, '2025-03-04 09:59:18', '2025-03-06 15:07:13'),
(90, 'App\\Models\\User', 2, 'auth_token', '09bf016142766ca4425038ec1286986bcc01db76d4db8fbe4ceb18e2941c05d4', '[\"*\"]', '2025-03-06 15:20:44', NULL, '2025-03-06 15:07:19', '2025-03-06 15:20:44'),
(91, 'App\\Models\\User', 22, 'auth_token', '928d0754435c36e7bc9f452a29326a73010b1f81564f31c6c4c3f95413dd823e', '[\"*\"]', '2025-03-07 15:29:25', NULL, '2025-03-06 15:14:00', '2025-03-07 15:29:25'),
(93, 'App\\Models\\User', 23, 'auth_token', '84014cb9c5a16e944bbdd5d22ab7055ad34e999ff75c668ffd33ef792e9772b3', '[\"*\"]', '2025-03-07 12:54:29', NULL, '2025-03-06 16:13:10', '2025-03-07 12:54:29'),
(95, 'App\\Models\\User', 2, 'auth_token', '5c284717fd6109c0525df5ad079ed426cf7e4cfc16d82570982a105da53b85ce', '[\"*\"]', '2025-03-07 13:50:03', NULL, '2025-03-07 08:07:40', '2025-03-07 13:50:03'),
(99, 'App\\Models\\User', 2, 'auth_token', 'a951f519d59e8c03e1d416b36e6fee839a6acc4562e44bb2c25a585282b08e22', '[\"*\"]', '2025-03-07 14:49:02', NULL, '2025-03-07 13:50:08', '2025-03-07 14:49:02'),
(102, 'App\\Models\\User', 2, 'auth_token', 'c15344f4c25b71722045ca1923fb879b19ecdce8c9e7cba4f13fabf3d244cbab', '[\"*\"]', '2025-03-07 15:05:02', NULL, '2025-03-07 15:04:59', '2025-03-07 15:05:02'),
(103, 'App\\Models\\User', 2, 'auth_token', '3f54f7c8ccf0d6ffd1fcf21e427f3be1f0b4d1879ca3e252a4685bc860af8a44', '[\"*\"]', '2025-03-07 15:21:11', NULL, '2025-03-07 15:05:04', '2025-03-07 15:21:11'),
(104, 'App\\Models\\User', 2, 'auth_token', 'e01724735058147d141bcae12b79683854f82453ca3f6defc1dafb8efc0485e3', '[\"*\"]', '2025-03-07 16:54:37', NULL, '2025-03-07 15:21:13', '2025-03-07 16:54:37'),
(105, 'App\\Models\\User', 22, 'auth_token', '5586a7b221aa05c2738e77139590ba086cb59137377053ab818b0b9c37239f60', '[\"*\"]', '2025-03-07 15:34:17', NULL, '2025-03-07 15:29:57', '2025-03-07 15:34:17'),
(106, 'App\\Models\\User', 22, 'auth_token', '28baddada337a6373070fe2c99b2c3ff12eeea0f66a316c3484b859bac01d55b', '[\"*\"]', '2025-03-07 15:43:38', NULL, '2025-03-07 15:34:35', '2025-03-07 15:43:38'),
(108, 'App\\Models\\User', 5, 'auth_token', '757b05aa5400dfa6b2ed50cd0103d13482ac3cc49adb1ba533cbec8df09270e1', '[\"*\"]', '2025-03-07 16:08:49', NULL, '2025-03-07 15:45:02', '2025-03-07 16:08:49'),
(109, 'App\\Models\\User', 22, 'auth_token', '72e73e4df4011fbc5502f7f3848fc3fdf74717c4119c043bbd2ca054921f93f7', '[\"*\"]', '2025-03-07 16:23:18', NULL, '2025-03-07 15:51:56', '2025-03-07 16:23:18'),
(110, 'App\\Models\\User', 22, 'auth_token', '1c806f5b130e893ec4bf3100a10f0ab279738a6e4324133d65f98bc4fb2e83b0', '[\"*\"]', '2025-03-07 16:31:31', NULL, '2025-03-07 16:23:40', '2025-03-07 16:31:31'),
(113, 'App\\Models\\User', 23, 'auth_token', '5b6e4d549518abd2a93af65cc2a9ac75b17386370133a92ff1b470afe1fed0b7', '[\"*\"]', '2025-03-07 17:21:44', NULL, '2025-03-07 16:47:03', '2025-03-07 17:21:44'),
(115, 'App\\Models\\User', 22, 'auth_token', 'd3dfa00e9a8379054d89ed3e254505d8a2c235c756856266060da72e464ccfde', '[\"*\"]', '2025-03-07 16:50:19', NULL, '2025-03-07 16:49:35', '2025-03-07 16:50:19'),
(116, 'App\\Models\\User', 3, 'auth_token', '8544ea223804a109e00ec6bb453f420423d3b24fc9e97b30f9a850fe97fa90ba', '[\"*\"]', '2025-03-07 17:01:47', NULL, '2025-03-07 16:50:47', '2025-03-07 17:01:47'),
(117, 'App\\Models\\User', 22, 'auth_token', '9c8ba8b9ebd36aad985676f225837c164b28cc64e6b3dcba672f518c18be813e', '[\"*\"]', '2025-03-07 16:57:57', NULL, '2025-03-07 16:53:26', '2025-03-07 16:57:57'),
(119, 'App\\Models\\User', 22, 'auth_token', 'b344e9e6c4624667369badbd263bb7a7b9cf98790ad10660f3d5313896b77783', '[\"*\"]', '2025-03-07 17:01:10', NULL, '2025-03-07 16:58:42', '2025-03-07 17:01:10'),
(120, 'App\\Models\\User', 3, 'auth_token', '963aee2551a89487b30ab8132c578a58d21acc3f96860401083af88fb491e548', '[\"*\"]', '2025-03-07 18:09:08', NULL, '2025-03-07 17:02:13', '2025-03-07 18:09:08'),
(121, 'App\\Models\\User', 22, 'auth_token', 'a12e54bba8440aaac9b530dffba4bead217533623fe94b6ded63d33fccd84d00', '[\"*\"]', '2025-03-07 18:15:52', NULL, '2025-03-07 18:15:46', '2025-03-07 18:15:52'),
(122, 'App\\Models\\User', 22, 'auth_token', 'f018eb1a46f244b6410233879161723e8d487244eef04bc45add6a2663740908', '[\"*\"]', '2025-03-07 18:20:35', NULL, '2025-03-07 18:16:12', '2025-03-07 18:20:35'),
(123, 'App\\Models\\User', 2, 'auth_token', '731c8e93dbb656fe28fc1b2ceee3b693432342c0d01096b1f95898994beb9184', '[\"*\"]', '2025-03-07 18:20:39', NULL, '2025-03-07 18:20:39', '2025-03-07 18:20:39'),
(125, 'App\\Models\\User', 25, 'auth_token', '3002a3798fec8c7e866742450b6567423eef954a77b5b696d17bc40e8727e013', '[\"*\"]', '2025-03-10 12:13:13', NULL, '2025-03-07 18:26:17', '2025-03-10 12:13:13'),
(127, 'App\\Models\\User', 1, 'auth_token', 'c932e04a42ac405950605411ef20171e259c994115eb2fed5665378b78937b73', '[\"*\"]', '2025-03-24 07:59:16', NULL, '2025-03-10 12:36:44', '2025-03-24 07:59:16'),
(129, 'App\\Models\\User', 1, 'auth_token', 'dda2775fe071710f3e712c65e30cd740809347b5889d809614b336a4b4423721', '[\"*\"]', '2025-03-11 13:27:58', NULL, '2025-03-11 12:22:16', '2025-03-11 13:27:58'),
(134, 'App\\Models\\User', 24, 'auth_token', '02ae0f54d9661f1eceeaf698451fec07a803f6c94341a539ffc8b84b1d274e29', '[\"*\"]', '2025-03-18 11:45:46', NULL, '2025-03-12 15:29:22', '2025-03-18 11:45:46'),
(135, 'App\\Models\\User', 25, 'auth_token', 'f49492a27b3d86dc24c416721ad681400d057a4d2a3d191eb0a0949f90cededb', '[\"*\"]', '2025-03-15 06:58:13', NULL, '2025-03-13 08:53:04', '2025-03-15 06:58:13'),
(138, 'App\\Models\\User', 25, 'auth_token', 'fcff78bc3bd77db38c84d65678911b72b6a7f65bcc0c378ab9519697b58bace2', '[\"*\"]', '2025-03-17 15:37:50', NULL, '2025-03-17 15:27:00', '2025-03-17 15:37:50'),
(142, 'App\\Models\\User', 25, 'auth_token', '9135108a0fb2220885fd8df6dae88e91f03909648df126fb2ef99f5c77779cc7', '[\"*\"]', '2025-03-20 17:05:58', NULL, '2025-03-20 15:31:15', '2025-03-20 17:05:58'),
(145, 'App\\Models\\User', 1, 'auth_token', '6059a5d70ac1447ba7eca651c8ce3073afabd85fbb8afad1006c7a37694b699b', '[\"*\"]', '2025-03-26 10:46:02', NULL, '2025-03-24 15:01:57', '2025-03-26 10:46:02'),
(146, 'App\\Models\\User', 25, 'auth_token', '2f90f8d9978f4ab9cf003866ec760e2cd5729bf6ffddcf909fb1e6093e39a6c7', '[\"*\"]', '2025-03-26 09:34:15', NULL, '2025-03-24 15:18:01', '2025-03-26 09:34:15'),
(148, 'App\\Models\\User', 1, 'auth_token', 'c6a08d4e4134ea73bf1e0ded9259003a65f261ab8fccea36bd6aec410afcfe3f', '[\"*\"]', '2025-04-07 07:26:37', NULL, '2025-04-07 07:26:17', '2025-04-07 07:26:37');

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `comment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reply_activities`
--

CREATE TABLE `reply_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `activity_comment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trainings`
--

CREATE TABLE `trainings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dendreo_id` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_title` varchar(255) DEFAULT NULL,
  `duration_hours` decimal(5,2) DEFAULT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `validity_duration` int(11) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `parent_category` varchar(255) DEFAULT NULL,
  `visibility` enum('public','private') NOT NULL DEFAULT 'private',
  `duration` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trainings`
--

INSERT INTO `trainings` (`id`, `dendreo_id`, `title`, `short_title`, `duration_hours`, `duration_days`, `validity_duration`, `category`, `parent_category`, `visibility`, `duration`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '19', '11.2.7 du Regl n°EU 2015/1998: Formation des personnes nécessitant une sensibilisation à la sûreté générale', '§11.2.7 Initiale Regl. EU2015/1998 REM', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(2, '21', '11.2.4.du Regl. EU2015/1998 : Formation spécifique des personnes supervisant directement des personnes effectuant les contrôles de sûreté 11.2.3.1 à 11.2.3.5. \"SUPERVISEUR -AGENT DE SURETE\"', '11.2.4 ADS', NULL, NULL, 0, 'Superviseur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(3, '23', 'Formation Responsable Sûreté', 'Responsable sûreté', NULL, NULL, 0, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(4, '24', 'CQP Agent de Sureté Aéroportuaires Typologie 10', '', NULL, NULL, 0, 'CQP', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(5, '25', 'Formation Périodique Imagerie RX et EDS simple vue multi-vues (11.4.1 du règlement EU2015/1998) – FPI Typologie 1', 'FPI Typologie 1', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(6, '26', 'Agent de sûreté typologie 1 Analyse d\'images sur simulateur Initiale', 'Typo 1 Analyse images Init.', NULL, NULL, 0, 'Formation initiale', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(7, '27', 'CQP Agent de Sûreté Aéroportuaires Typologie 2', '', NULL, NULL, 0, 'CQP', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(8, '28', 'Agent de sûreté typologie 2 Analyse d\'images sur simulateur Initiale', 'Agent de sûreté T2 (analyse images) - Initiale', NULL, NULL, 0, 'Formation initiale', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(9, '29', 'CQP Agent de Sûreté Aéroportuaires Typologie 3', '', NULL, NULL, 0, 'CQP', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(10, '30', 'CQP Agent de Sûreté Aéroportuaires Typologie 4', '', NULL, NULL, 0, 'CQP', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(11, '32', 'Agent de sûreté typologie 4 Analyse d\'images sur simulateur initiale', 'TYPO 4 Analyse images Init.', NULL, NULL, 0, 'Formation initiale', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(12, '33', 'CQP Agent de Sûreté Aéroportuaires Typologie 5', '', NULL, NULL, 0, 'CQP', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(13, '34', 'Agent de sûreté typologie 6', '', NULL, NULL, 0, 'Formation initiale', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(14, '35', 'CQP Agent de Sûreté Aéroportuaires Typologie 7', 'CQP ASA TYPO 7', NULL, NULL, 0, 'CQP', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(15, '36', 'Agent de sûreté typologie 7 Analyse d\'images sur simulateur Initiale', 'Typo 7 Analyse images Init.', NULL, NULL, 0, 'Formation initiale', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(16, '37', 'IATA DGR 10', '', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(17, '38', 'IATA DGR 12 Marchandises Dangereuses IATA - Filtrage des passagers, bagages cabine et soute, fret et courrier', 'IATA MD Cat. 12', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(18, '39', 'IATA DGR 8 a', '', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(19, '40', 'IATA DGR 8 b', '', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(20, '41', 'IATA DGR 8 c', 'IATA DGR 8 c', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(21, '42', 'IATA DGR 9', '', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(22, '43', 'Marchandises Dangereuses - Cat.1-3-6 – Base OACI-IATA « Agent de Fret aérien » Acheminement, préparation, stockage, acceptation des marchandises dangereuses- Base - Excluant la classe 7 - Radioactifs', 'IATA DGR 1-3-6 hors RRY - Initiale', NULL, NULL, 24, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(23, '45', 'PRAP – Gestes et postures', '', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(24, '46', 'Sauveteur Secouriste du Travail – SST initiale', 'SST - Initiale', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(25, '47', 'Sensibilisation à la Radioprotection', '', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(26, '48', 'Palettisation et arrimage - Initiale', '', NULL, NULL, 0, 'MÉTIERS_CARGO', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(27, '49', 'Palettisation et arrimage - Recyclage', '', NULL, NULL, 0, 'MÉTIERS_CARGO', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(28, '50', 'ADR 1.3 – Sensibilisation Transport Routier MD', 'Sensi ADR 1.3', NULL, NULL, 0, 'Adr 1.3', 'MARCHANDISES DANGEREUSES', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(29, '51', 'DCS Altea passage', '', NULL, NULL, 0, 'MÉTIERS_CARGO', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(30, '56', '11.2.3.8. + Compl. 11.2.6.2 du Regl. EU2015/1998  : Formation des personnes qui mettent en oeuvre la vérification de concordance entre passagers et bagages + partie TCA', '11.2.3.8 + 11.2.6.2 (TCA)', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(31, '58', '11.2.6.2 du Regl. EU2015/1998: Formation des personnes accédant la ZSAR sans escorte E-LEARNING', '§11.2.6.2 Regl. 2015/1998 E-learning', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(32, '59', '11.2.3.9 CCo Regl. EU2015/1998: Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF - Variante C', '11.2.3.9 Chargeur Connu', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(33, '60', 'SensibilisatIon 1.3 ADR – Expédition par route de marchandises dangereuses', 'Sensibilisation ADR 1.3 Initiale', NULL, NULL, 24, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(34, '61', 'TSA – Programme de sûreté type compagnie aérienne– Module Formation Passage', 'TSA - Module Passage', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(35, '62', '§ Formation \"Clean and Search\" US - Module Compagnies aériennes NORWEGIAN - Validation regl. TSA', 'Clean&Search TSA', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(36, '64', '§ 11.2.6.2 du Regl européen n°n°EU 2015/1998: Complément TCA aux modules de formation 11.2.3.8, 11.2.3.9 et 11.2.3.10', 'Compl. TCA 11.2.6.2', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(37, '65', 'Sauveteur Secouriste du Travail – SST Recyclage', 'SST - Recyclage', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(38, '66', 'ADR_1.3 – Expédition par route de Marchandises Dangereuses', 'ADR 1.3 Expédition par route de Marchandises Dangereuses', NULL, NULL, 0, 'Adr 1.3', 'MARCHANDISES DANGEREUSES', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(39, '69', 'CQP Agent de Sureté Aéroportuaires Typologie 10', '', NULL, NULL, 0, 'CQP', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(40, '70', 'Formation Périodique Imagerie FPI T10 -RX et EDS simple vue multi-vues (11.4.1 du règlement EU2015/1998)', 'FPI Typologie 10', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(41, '71', 'Formation Sûreté Périodique Typologie 1 (T1) du Règlement EU2015/1998 §11.2.3.2: Formation Périodique Hors Imagerie des personnes qui effectuent l’inspection/ le filtrage du fret et du courrier et/ou vérification physique du fret et du courrier', 'FPHI Typologie 1 (hors imagerie)', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(42, '72', 'Formation Sûreté Périodique Typologie 4 (T4) du Règlement EU2015/1998 §11.2.3.1 IFBS: Formation Périodique Hors Imagerie des personnes qui effectuent l’inspection/ le filtrage des bagages de soute', 'FPHI Typologie 4', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(43, '74', 'Formation Sûreté Périodique Typologie 10 (T10) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.2 §11.2.3.3 §11.2.3.4 §11.2.3.5 : Formation Périodique Hors Imagerie :', 'FPHI Typologie 10 (hors imagerie)', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(44, '75', 'Formation Sûreté Périodique Typologie 2 (T2) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.2 §11.2.3.3 §11.2.3.4 §11.2.3.5 : Formation Périodique Hors Imagerie :', 'FPHI Typologie 2 (hors imagerie)', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(45, '76', '§ 11.2.2 du Reg. EU2015/1998 Formation de base des personnes qui exécutent les tâches énumérées au point 11.2.4 du règl. EU2015/1998: personnes qui supervisent directement les personnes qui effectuent les contrôles de sûreté 11.2.3.6. à 11.2.3.10 (\"Superv', '11.2.2/11.2.4 Contributeur Amarante', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(46, '77', 'Formation Facteurs Humains', 'Facteurs humains', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(47, '78', 'Marchandises Dangereuses - Cat.1-3-6 – Base OACI-IATA « Agent de Fret aérien » Acheminement, préparation, stockage, acceptation des marchandises dangereuses- Base - Incluant la classe 7 - Radioactifs', 'IATA DGR 1-3-6 Incluant RRY - Initiale', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(48, '79', 'IATA DGR Cat. 5 Base & Recyclage - Hors RRY', 'IATA DGR 5 - Excluant RRY', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(49, '80', '11.2.5 MANAGEMENT DE LA SURETE: Formation spécifique destinée aux personnes assumant une responsabilité générale au niveau national ou local en respect de toutes les dispositions légales applicables dans d\'un programme de sûreté et d\'Assurance Qualité', '11.2.5 Management de la sûreté', NULL, NULL, 0, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(50, '81', 'Marchandises Dangereuses - Cat.1-3-6 – Recyclage OACI-IATA « Agent de Fret aérien » Acheminement, préparation, stockage, acceptation des marchandises dangereuses- Recyclage - Excluant la classe 7 - Radioactifs', 'IATA DGR 1-3-6 hors RRY - Recyclage', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(51, '82', 'Marchandises Dangereuses - Cat.1-3-6 – Recyclage OACI-IATA « Agent de Fret aérien » Acheminement, préparation, stockage, acceptation des marchandises dangereuses- recyclage - INcluant la classe 7 - Radioactifs', 'IATA DGR 1-3-6 Incluant RRY - Recyclage', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(52, '84', 'Formation de Formateur Qualifié en Sûreté de l\'Aviation Civile', 'Formation de formateur qualifié sûreté', NULL, NULL, 0, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(53, '85', 'Formation de Formateur Qualifié en Sûreté de l\'Aviation Civile (Excl. §11.2.2) - Qualification pour les modules §11.2.6.2 et §11.2.3.9', 'Formation de formateur qualifié Sûreté (Excl. 11.2.2.) Module 11.2.6.2/11.2.3.9', NULL, NULL, 0, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(54, '86', '§ Formation \"Clean and Search\" US - Module Compagnies aériennes CORSAIR INTERNATIONAL - Validation regl. TSA', 'Clean&Search TSA Corsair', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(55, '88', '11.2.3.7 : Formation des personnes en charge de la protection des aéronefs', '11.2.3.7 E LEARNING', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(56, '89', '11.2.3.3. CV/FM Initiale du Regl. EU2015/1998  : inspection/filtrage du courrier et du matériel des transporteurs aériens, des approvisionnements de bord et des fournitures d\'aéroport, limitée au Contrôle Visuel et à la Fouille Manuelle', '11.2.3.3. CV FM - Initiale si 11.2.3.10 valide', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(57, '91', 'Sécurité en Piste (piéton) et Système de gestion du risque', 'Sécurité Piste/Piéton - Initiale', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(58, '92', 'Formation Marchandises Dangereuses Réglementées Catégorie IATA 1 - UN1845 - Recyclage', 'IATA 1 - UN1845 - Recyclage', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(59, '93', 'EPI – Équipier de Première Intervention et Manipulation des extincteurs', 'EPI - Manipulation extincteurs  - Recyclage', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(60, '95', 'Formation Sûreté Périodique Typologie 7 (T7) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.3 §11.2.3.4 §11.2.3.5 : Formation Périodique Hors Imagerie :', 'FPHI Typologie 7 HORS IMAGERIE', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(61, '96', 'Formation Sûreté Initiale Typologie 7 (T7) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.3 §11.2.3.4 §11.2.3.5', 'Formation Typologie 7 - Initiale', NULL, NULL, 0, 'Formation initiale', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(62, '97', 'Formation de Formateur Qualifié en Sûreté de l\'Aviation Civile (Excl. §11.2.2) - Qualification pour les module §11.2.3.8', 'Formation de formateur qualifié Sûreté (Excl. 11.2.2.) Module 11.2.3.8', NULL, NULL, 0, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(63, '98', 'Permis Trafic CDG - Pratique', '', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(64, '99', '2020 Recycling Training Course Sûreté PNT Compagnie Aérienne - Fouille aéronef  §11.2.3.6 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs', '2020 RTC PNT CORSAIR INTERNATIONAL §11.2.3.6', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(65, '100', '11.2.3.9 E LEARNING  Formation des personnes qui effectuent sur le fret et les envois postaux des contrôles de sûreté, autres que l\'inspection filtrage', '11.2.3.9 E learning', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(66, '101', 'EPI – 7H Équipier de Première Intervention et Manipulation des extincteurs', 'EPI - Manipulation extincteurs - Initiale', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(67, '102', 'Permis Trafic CHARLES DE GAULLE', 'Permis T CDG', NULL, NULL, 24, 'PERMIS TRAFIC', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(68, '103', 'Permis Trafic ORLY (EA)', 'Permis T ORLY', NULL, NULL, 0, 'PERMIS TRAFIC', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(69, '104', 'Permis Trafic ORLY - Partie Pratique (EA)', 'Permis T Pratique ORLY', NULL, NULL, 0, 'PERMIS TRAFIC', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(70, '105', 'Permis Trafic ROISSY CDG- Partie Pratique', 'Permis T Pratique CDG', NULL, NULL, 0, 'PERMIS TRAFIC', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(71, '106', 'Formation Sûreté sur le Tas prévue au 11.2.1.2. du Regl. EU2015/1998 : Module §11.2.3.3. CV/FM', 'Formation sur le Tas 11.2.3.3 CV/FM', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(72, '107', 'Recycling Training Course Sûreté PN Compagnie Aérienne - Gestion du risque terroriste et maitrise PAXI', 'RTC PNT CORSAIR INTERNATIONAL -Risque Terroriste - Maitrise PAXI', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(73, '108', '11.2.3.9+11.2.3.10 Regl.EU2015/1998: Formation des personnes qui effectuent sur le fret, les envois postaux,le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté,hors IF', '11.2.3.9 + 11.2.3.10', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(74, '109', '11.2.3.8. Formation des personnes qui mettent en oeuvre la vérification de concordance entre passagers et bagages', '11.2.3.8 Concordance Bagages', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(75, '110', 'Recycling Training Course Sûreté PNC Compagnie Aérienne - Fouille aéronef  §11.2.3.6 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs', 'RTC PNC CORSAIR INTERNATIONAL §11.2.3.6', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(76, '111', 'MISE A NIVEAU REM CONSEIL', 'Rc', NULL, NULL, 0, 'QUALITÉ DE VIE AU TRAVAIL', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(77, '112', 'SENSIBILISATION A LA RADICALISATION - 7h00', '', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(78, '114', '11.2.3.10 Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage', '11.2.3.10-INITIALE', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(79, '116', 'Formation Transport Routier Matières Dangereuses: ADR-RID-IMDG-ADN', 'ADR 1.3 RID-IMDG-ADN', NULL, NULL, 0, 'Adr 1.3', 'MARCHANDISES DANGEREUSES', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(80, '119', 'Formation initiale Typologie 9 - §11.2.3.5.Regl. EU2015/1998 : contrôles d’accès à un aéroport et opérations de surveillance et de patrouille.', 'Formation Typologie 9 - Initiale', NULL, NULL, 0, 'Formation initiale', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(81, '120', 'Marchandises Dangereuses - Cat.1 Piles & Batteries Lithium Base OACI-IATA \"Expéditeurs\" Acheminement des marchandises dangereuses', 'DGR 1 Piles & Batteries Lithium', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(82, '121', '11.2.7 du Regl n°EU 2015/1998: Formation des personnes nécessitant une sensibilisation à la sûreté générale - Variante transporteurs de fret - courrier', '11.2.7 Variant AHA/CCo - Initiale', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(83, '122', '11.2.7 du Regl n°EU 2015/1998: Formation des personnes nécessitant une sensibilisation à la sûreté générale - Variante fournitures d\'aéroports', '11.2.7 Variante fournitures d\'aéroports - Initiale', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(84, '123', '11.2.7 du Regl n°EU 2015/1998: Formation des personnes nécessitant une sensibilisation à la sûreté générale - Variante approvisionnements de bord', '11.2.7 Variante approvisionnements de bord - Initiale', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(85, '124', 'SENSIBILISATION A LA RADICALISATION ET COMPREHENSION DE LA MENACE TERRORISTE - 3H00', 'Sensi Radicalisation - 3H00', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(86, '125', 'Mise à Jour Connaissances Formateur en Sûreté de l\'Aviation Civile', 'Formation Mise à jour des connaissances - Formateur Sûreté REM', NULL, NULL, 0, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(87, '126', 'Formation Secure Supply Chain - ACC3 -RA3', 'AAC3 - RA3', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(88, '127', 'FORMATION CARISTE R489 – CHARIOT 3 CATEGORIE INITIAL', 'CACES 1-3-5 - Initiale', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(89, '128', 'Formation continue sur l’inspection Filtrage des personnes et sur la palpation de sécurité (Hors Aéroportuaire)', 'IF personnels/Palpation (hors aéro) Continue', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(90, '129', 'Formation continue sur l’interprétation de l’imagerie radioscopique (Hors secteur aéroportuaire)', 'Formation Imagerie (hors milieu aéro) Recyclage', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(91, '130', 'Formation initiale sur l’interprétation de l’imagerie radioscopique (Hors secteur aéroportuaire)', 'Formation Imagerie Hors milieu Aéro - Initiale', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(92, '131', 'Formation initiale sur l’inspection Filtrage des personnes et sur la palpation de sécurité (Hors Aéroportuaire)', 'IF personnels/Palpation (hors aéro) Initiale', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(93, '133', 'Sensibilisation O.E.A (Opérateur Economique Agréé)', 'Sensibilisation O.E.A', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(94, '134', 'Référent/Correspondant Sûreté - CDG – Orly – LBG.', 'Corsur ADP - CDG/ORLY/LBG', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(95, '135', 'MISE A DISPO PERSONNEL ET LOCAUX', 'DISPO', NULL, NULL, 0, NULL, NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(96, '136', 'SADE 1 Course Sûreté PN Compagnie Aérienne - Gestion du risque terroriste et maitrise PAXI', 'SADE 1 PNT CORSAIR INTERNATIONAL -Risque Terroriste - Maitrise PAXI', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(97, '137', 'SADE 1- Cours Sûreté PNC Compagnie Aérienne - Fouille aéronef  §11.2.3.6 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs', 'SADE- PNC CORSAIR INTERNATIONAL §11.2.3.6', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(98, '138', 'SADE 1- Cours Sûreté PNC Compagnie Aérienne - 11.2.3.7.Regl. EU2015/1998  : Formation des personnes en charge de la protection des aéronefs', 'SADE- PNC CORSAIR INTERNATIONAL -§11.2.3.7 RegL EU2015/1998', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(99, '139', 'ADR 1.3 Transport Routier Matières Dangereuses', 'ADR 1.3 TRMD (colis)', NULL, NULL, 0, 'Adr 1.3', 'MARCHANDISES DANGEREUSES', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(100, '140', 'LOCAL ARCHIVE', 'ARCHIVE', NULL, NULL, 0, NULL, NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(101, '141', '11.2.6.2 du Regl. EU2015/1998: Formation des personnes accédant la ZSAR sans escorte E-LEARNING', 'B11.2.6.2 Regl. 2015/1998 E-learning', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(102, '142', 'Formation initiale/Continue de formateur: interprétation de l’imagerie radioscopique (Hors secteur aéroportuaire)', 'Formation Imagerie (hors aéro)', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(103, '143', 'SensibilisatIon 1.3 ADR – Expédition par route de marchandises dangereuses', 'Sensi ADR1.3 -STERNE', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(104, '144', 'Sensibilisation à la Radicalisation, Compréhension des menaces terroristes et Culture Sûreté 1H30', 'Sensibilisation radicalisation/culture sûreté', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(105, '145', 'Sensibilisation à la culture de sûreté, Menace interne et Radicalisation', 'Culture de sûreté et Radicalisation', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(106, '146', '2025 -Recycling Training Course Sûreté PNC Compagnie Aérienne -Protection aéronef  11.2.3.7. et §11.2.3.11 du Regl. EU2015/1998 : Formation des personnes en charge de la protection des aéronefs et sureté en vol', 'RTC PNC CORSAIR INTERNATIONAL §11.2.3.7 -§11.2.3.11', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(107, '148', '11.2.3.9 E LEARNING  Formation des personnes qui effectuent sur le fret et les envois postaux des contrôles de sûreté, autres que l\'inspection filtrage', '11.2.3.9 E learning', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(108, '149', '11.2.3.10 Variante FC Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage', '11.2.3.10-E FORMATION Variante FC', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(109, '150', '11.2.3.10 Variante AP Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage', '11.2.3.10-E FORMATION Variante AP', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(110, '151', 'Marchandises Dangereuses - Cat.1 ID 8000Base OACI-IATA «Expéditeurs» Acheminement des marchandises dangereuses', 'DGR1 ID8000', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(111, '152', 'SensibilisatIon 1.3 ADR – Groupe Sterne', 'STERNE ADR1.3 -E-Learning', NULL, NULL, 24, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(112, '153', 'Facteurs humains et gestion du risque', 'Facteurs humains et gestion du risque', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(113, '154', 'Formation Paragraphe 1.3 ADR Recyclage – Expédition par route de marchandises dangereuses', 'ADR 1.3 Exp. Route MD - Recyclage', NULL, NULL, 0, 'Adr 1.3', 'MARCHANDISES DANGEREUSES', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(114, '155', 'Sensibilisation à la Responsabilité Sociétale des Entreprises', 'Sensibilisation à la Responsabilité Sociétale (RSE)', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(115, '156', '11.2.8 Sensibilisation à la cyber sécurité', '11.2.8 Cyber sécurité', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(116, '157', 'Sensibilisation à la Responsabilité Sociétale des Entreprises', 'La RSE GROUPE STERNE', NULL, NULL, 0, 'QUALITÉ DE VIE AU TRAVAIL', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(117, '158', 'Sensibilisation RGPD', 'Sensibilisation RGPD', NULL, NULL, 0, 'Soft Kill', 'E-LEARNING', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(118, '159', 'SensibilisatIon 1.3 ADR – Groupe Sterne', 'STERNE ADR1.3 -E-Learning', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(119, '160', 'Parcours sécurité des données (RGPD-CYBERSECURITE)', 'E DONNEES GROUPE STERNE', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(120, '161', 'Sensibilisation à la cyber sécurité _Groupe Sterne -', 'Cyber sécurité', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(121, '162', 'LOCAL ARCHIVE', 'ARCHIVE', NULL, NULL, 0, NULL, NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(122, '163', 'X RAY', '', NULL, NULL, 0, NULL, NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(123, '164', 'X RAY', 'X RAY', NULL, NULL, 0, NULL, NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(124, '165', 'SUITE ECHEC PREMIERE SESSION -11.2.3.10 Variante AP Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspec', '11.2.3.10-E FORMATION Variante AP', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(125, '166', 'Sensibilisation à la Responsabilité Sociétale des Entreprises', 'La RSE GROUPE STERNE', NULL, NULL, 0, 'QUALITÉ DE VIE AU TRAVAIL', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(126, '167', 'PARCOURS GROUPE STERNE', 'RSE, RGPD, CYBER', NULL, NULL, 0, 'QUALITÉ DE VIE AU TRAVAIL', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(127, '168', 'LOCATION SALLE DE FORMATION REM ALPHA', 'LOC. SALLE DE FORMATION', NULL, NULL, 0, NULL, NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(128, '169', 'Formation Pratique 11.2.3.6.du Regl. EU2015/1998  : Formation Pratique des personnes en charge de la fouille de sûreté des aéronefs', '11.2.3.6 - Partie Pratique', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(129, '170', '11.2.6.2  E-LEARNING- 2de PASSAGE', '2de PASSAGE EVALUATION 11.2.6.2 Regl. 2015/1998 E-learning', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(130, '171', '11.2.6.2  E-LEARNING- 2de PASSAGE', '2de PASSAGE EVALUATION 11.2.6.2 Regl. 2015/1998 E-learning', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(131, '172', 'Marchandises Dangereuses - Cat. 1 Piles & batteries Lithium ionique & gaz comprimé nsa BASE OACI-IATA \"Expéditeurs\" acheminement des marchandises dangereuses', 'DGR Cat. 1 Piles & batteries Lithium', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(132, '173', 'Marchandises Dangereuses - Cat.1– Base OACI-IATA « Expéditeurs de Fret aérien » Acheminement, préparation, stockage des marchandises dangereuses- Base - Excluant la classe 7 - Radioactifs', 'IATA DGR 1 - Expéditeurs hors RRY (Initiale)', NULL, NULL, 24, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(133, '174', 'Marchandises Dangereuses - Cat.1– Recyclage OACI-IATA « Expéditeurs de Fret aérien »- Acheminement, préparation, stockage  des marchandises dangereuses- Recyclage - Excluant la classe 7 - Radioactifs', 'IATA DGR 1 - Expéditeurs hors RRY (Recyclage)', NULL, NULL, 24, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(134, '176', 'SUITE ECHEC 11.2.3.9 E LEARNING  Formation des personnes qui effectuent sur le fret et les envois postaux des contrôles de sûreté, autres que l\'inspection filtrage', '11.2.3.9 E learning', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(135, '177', 'Sensibilisation à la loi Sapin 2', 'Sensibilisation loi Sapin 2', NULL, NULL, 0, 'Soft Kill', 'E-LEARNING', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(136, '178', 'LOCATION SALLE DE FORMATION REM RDC BRAVO', 'LOC. SALLE DE FORMATION', NULL, NULL, 0, NULL, NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(137, '179', '2022 Recycling Training Course Sûreté PNT Compagnie Aérienne - Fouille aéronef  §11.2.3.6 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs', '2022 RTC PNT CORSAIR INTERNATIONAL §11.2.3.6', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(138, '180', '11.2.3.9 E LEARNING  Formation des personnes qui effectuent sur le fret et les envois postaux des contrôles de sûreté, autres que l\'inspection filtrage', '11.2.3.9 E learning', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(139, '181', 'Recycling Training Course Sûreté PNT Compagnie Aérienne -Protection aéronef  11.2.3.7.du Regl. EU2015/1998 : Formation des personnes en charge de la protection des aéronefs', 'RTC PNT CORSAIR INTERNATIONAL §11.2.3.7 & §11.2.8 Sensi à la Cyber-menace', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(140, '182', '13-SensibilisatIon 1.3 ADR – Groupe Sterne', 'STERNE ADR1.3 -E-Learning', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(141, '183', 'FORFAIT AFI 2022', '', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(142, '186', '2023 Recycling Training Course Sûreté PNC Compagnie Aérienne - Fouille aéronef  11.2.3.6 /11.2.3.11 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs, des mesures de sureté en vol & 11.2.8 Sensi à la Cyber-menace', 'RTC PNC CORSAIR INTERNATIONAL §11.2.3.6/11.2.3.11 & §11.2.8 Cyber-menace', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(143, '187', 'SADE 2023- Formation Sûreté PNT CORSAIR -§11.2.3.11/§11.2.3.6/11.2.3.7 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces', 'SADE - PNT CORSAIR INTERNATIONAL 11.2.3.11/11.2.3.6/11.2.3.7/11.2.8', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(144, '188', 'S01I-E-11.2.6.2 Formation INITIALE pour l\'obtention du TCA dit badge aéroportuaire - SUITE ECHEC-', '01IE-Formation  badge aéroportuaire', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(145, '190', '11.2.3.6.du Regl. EU2015/1998  : Formation des personnes en charge de la fouille de sûreté des aéronefs', '11.2.3.6', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(146, '191', '11.2.3.7.du Regl. EU2015/1998  : Formation des personnes en charge de la protection des aéronefs -v2023', '11.2.3.7 Protection aéronef', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(147, '192', '11.2.3.8. du Regl. EU2015/1998 : Formation des personnes qui mettent en oeuvre la vérification de concordance entre passagers et bagages -v2023', '11.2.3.8 Concordance Bagages', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(148, '195', '11.2.4. du Regl. EU2015/1998: Formation spécifique des personnes supervisant directement des personnes effectuant les contrôles de sûreté 11.2.3.6. à 11.2.3.10. -v2023', '11.2.4 Contributeur superviseur', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(149, '196', '11.2.6.2 du Regl. EU2015/1998: Formation Initiale des personnes accédant la ZSAR sans escorte', 'Obtention du badge (TCA)', NULL, NULL, 36, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(150, '197', '11.2.7 du Regl n°EU 2015/1998: Formation des personnes nécessitant une sensibilisation à la sûreté générale -v2023', '11.2.7 - Initiale', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(151, '198', '11.2.3.3. CV/FM Initiale du Regl. EU2015/1998  : inspection/filtrage du courrier et du matériel des transporteurs aériens, des approvisionnements de bord et des fournitures d\'aéroport, limitée au Contrôle Visuel et à la Fouille Manuelle -v2023', '11.2.3.3 CV FM', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(152, '199', '11.2.4.du Regl. EU2015/1998 : Formation spécifique des personnes supervisant directement des personnes effectuant les contrôles de sûreté 11.2.3.1 à 11.2.3.5. \"SUPERVISEUR -AGENT DE SURETE\" -v2023', '11.2.4 Superviseur agent de sûreté (ADS)', NULL, NULL, 0, 'Superviseur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(153, '200', '11.2.5. du Regl. EU2015/1998: Formation spécifique destinée aux personnes assumant une responsabilité générale au niveau national ou local en respect de toutes les dispositions légales applicables dans d\'un programme de sûreté et d\'Assurance Qualité', '11.2.5', NULL, NULL, 60, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(154, '204', '11.2.6.2 du Regl. EU2015/1998: Formation Périodique des personnes accédant la ZSAR sans escorte', '11.2.6.2 Périodique Sensi Badge -V2023', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(155, '205', '11.2.6.2 du Regl. EU2015/1998: Formation Initiale des personnes accédant la ZSAR sans escorte et Formation Référent/Correspondant Sûreté Roissy CDG – Orly – LBG.', '11.2.6.2 + CORSUR', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(156, '206', '2de -Sensibilisation à la cyber sécurité', '2D Passage-Cyber sécurité', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(157, '207', 'Formation facteurs humains et à la gestion du risque', '2nd passage facteurs humains et à la gestion du risque', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(158, '209', '11.2.3.9  Formation INITIALE des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF', 'NE PLUS UTILISER', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(159, '210', '2023 Recycling Training Course Sûreté PNT Compagnie Aérienne - Fouille aéronef  11.2.3.6 /11.2.3.11 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs, des mesures de sureté en vol & 11.2.8 Sensi à la Cyber-menace', '2023 RTC PNT CORSAIR INTERNATIONAL §11.2.3.6/11.2.3.11 & §11.2.8 Cyber-menace', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(160, '211', 'S03P-11.2.3.10 PERIODIQUE .Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage', 'NE PLUS UTILISER', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(161, '212', '11.2.3.9  Formation PERIODIQUE des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF', 'NE PLUS UTILSER', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(162, '213', 'Frais de fabrication des Titres de circulation aéroportuaire ADP (63,90€ + 8%) Tarif en vigueur au 01/04/2022', 'Demande de TCA REM', NULL, NULL, 0, NULL, NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(163, '214', 'CARISTE R489 - CHARIOT 1-3-5', 'CACES 1-3-5 - Périodique', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(164, '215', 'S03I-11.2.3.10 INITIALE Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage', 'NE PLUS UTILISER', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(165, '216', 'SADE 2023- Formation Sûreté PNT CMA CGM AIRCARGO -§11.2.3.11/§11.2.3.6/11.2.3.7 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces', 'SADE- PNT CMA CGM AIRCARGO §11.2.3.11/§11.2.3.6/11.2.3.7', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(166, '217', '11.2.3.7 : Formation  initiale des personnes en charge de la protection des aéronefs', '11.2.3.7 E LEARNING INITIALE', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(167, '218', '11.2.3.7 : Formation périodique des personnes en charge de la protection des aéronefs', '11.2.3.7 E LEARNING PERIODIQUE', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(168, '219', 'SENSIBILISATION SUR LA CHAÎNE D’APPROVISIONNEMENT ET PRATIQUES ACTUELLES DANS LE DOMAINE DE LA SÛRETE DU FRET ET DU COURRIER AÉRIENS', 'Sensibilisation sûreté chaine du fret aérien', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(169, '220', 'SADE 2023- Formation Sûreté PNC CORSAIR -§11.2.3.11/§11.2.3.6 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef', 'SADE- PNC CORSAIR INTERNATIONAL §11.2.3.11/§11.2.3.6', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(170, '221', 'SADE 2023- Formation Sûreté PNC CORSAIR -11.2.3.7/11.2.8 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces', 'SADE- PNC CORSAIR INTERNATIONAL 11.2.3.7/§11.2.8', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(171, '222', 'SADE 2023 §11.2.3.11 Mesures de Sûreté en vol - PN Compagnie Aérienne - Gestion du risque terroriste et maitrise PAXI', 'Sade 2023 PN - Risque Terroriste - Maitrise Paxi', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(172, '223', 'FORMATION AIR CHINA', 'F AIR CHINA', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(173, '224', 'FORMATION AIR CHINA  2022', '2022', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(174, '225', '2023- Formation Sûreté PN compagnie aérienne -§11.2.3.11 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef', 'SADE- PN Compagnie Aérienne §11.2.3.11', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(175, '226', 'Marchandises Dangereuses - Cat. 1 UN 1072 / 3356', 'DGR Cat. 1 UN 1072 / 3356', NULL, NULL, 0, 'MARCHANDISES DANGEREUSES', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(176, '227', 'SADE 2023- Formation Sûreté PNC COMPAGNIE AERIENNE -§11.2.3.11/§11.2.3.6 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef + COMPL. TCA', 'SADE- PNC COMPAGNIE AERIENNE §11.2.3.11/§11.2.3.6+TCA', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(177, '228', 'SADE 2024- Formation Sûreté PN COMPAGNIE AERIENNE -11.2.3.7/11.2.3.11 du Regl. EU2015/1998: Mesures de sûreté en vol - Protection aéronef / Mesures de sûreté en vol', 'SADE- PNC COMPAGNIE AERIENNE 11.2.3.7/§11.2.3.11', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(178, '229', 'CONCEPTION DE FORMATION', 'MDC', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(179, '230', 'Formation : Formation de formateurs et maitrise pédagogique', 'Formation pédagogique Formateur', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(180, '232', '11.2.6.2 Formation INITIALE pour l\'obtention du TCA dit badge aéroportuaire', '11.2.6.2 INITIALE', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(181, '234', 'CIME -11.2.6.2-Formation pour l\'obtention carte CIME', '11.2.6.2 - Carte CIME', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(182, '236', '11.2.3.9 + Compl. 11.2.6.2 Formation des personnes ayant un accès non surveillé au fret et courrier aérien identifiables et/ou qui effectuent sur le fret et les envois postaux des contrôles de sûreté autre que l\'IF + Partie TCA', '11.2.3.9 + 11.2.6.2', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(183, '237', '2025 Stage Sûreté PNT / CDB CORSAIR - 11.2.3.11 du Regl. EU2015/1998 -Formation des CDB CORSAIR en charge des mesures de sureté en vol', '2025 Stage CDB PNT International 11.2.3.11 & 11.2.8', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(184, '242', 'Marchandises Dangereuses IATA 7.1  \"Expéditeurs\" UN1956/3480/3481', 'DGR 7.1', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(185, '243', '11.2.6.2 PERIODIQUE pour l\'obtention du TCA dit badge aéroportuaire', '11.2.6.2 PERIODIQUE 2023', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(186, '244', 'Formation en Ligne Gestes et Postures en Manutention', 'Gestes et postures en manutention', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(187, '245', '2023- Formation Sûreté PN compagnie aérienne -§11.2.3.11 du Regl. EU2015/1998: Mesures de sûreté en vol -Compl. TCA-Carte CIME §11.2.6.2', 'SADE- PN Compagnie Aérienne §11.2.3.11+ Compl. TCA', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(188, '246', '11.2.3.7 : Formation initiale des personnes en charge de la protection des aéronefs', '11.2.3.7 Protection aéronef - Initiale', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(189, '247', '11.2.3.7 : Formation périodique des personnes en charge de la protection des aéronefs', '11.2.3.7 Protection aéronef - Périodique', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(190, '248', '11.2.6.2 Formation INITIALE pour l\'obtention du TCA dit badge aéroportuaire', '11.2.6.2 Obtention du badge aéroportuaire - Initiale', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(191, '249', '11.2.6.2 PERIODIQUE pour l\'obtention du TCA dit badge aéroportuaire', '11.2.6.2 Obtention du badge aéroportuaire - Périodique', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(192, '250', 'Formation Sûreté Périodique Typologie 6 (T6) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.2 §11.2.3.3 §11.2.3.4 §11.2.3.5 : Formation Périodique Hors Imagerie :', 'FPHI Typologie 6 (hors imagerie)', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL);
INSERT INTO `trainings` (`id`, `dendreo_id`, `title`, `short_title`, `duration_hours`, `duration_days`, `validity_duration`, `category`, `parent_category`, `visibility`, `duration`, `created_at`, `updated_at`, `deleted_at`) VALUES
(193, '251', 'Périodique Sécurité en Piste (piéton) et Système de gestion du risque', 'Sécurité en Piste/Piéton - Périodique', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(194, '252', '11.2.8 Sensibilisation à la cyber sécurité', '11.2.8 Cyber sécurité', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(195, '253', 'Second passage Formation en Ligne Gestes et Postures en Manutention', 'SECOND PASSAGE GESTES ET POSTURES MANUTENTION', NULL, NULL, 0, 'QUALITÉ DE VIE AU TRAVAIL', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(196, '254', 'SensibilisatIon 1.3 ADR – Expédition par route de marchandises dangereuses', 'Sensi ADR1.3  Second passage', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(197, '255', '11.2.6.2 Formation INITIALE pour l\'obtention du TCA dit badge aéroportuaire', 'Second passage 11.2.6.2 INITIALE', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(198, '256', '2SD PASSAGE 11.2.6.2 Formation INITIALE pour l\'obtention du TCA dit badge aéroportuaire', '11.2.6.2 INITIALE SECOND PASSAGE', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(199, '257', 'Formation -§11.2.6.2 du Regl. EU2015/1998: Formation Initiale des personnes accédant la ZSAR sans escorte -Obtention carte CIME', '11.2.6.2 - CIME', NULL, NULL, 0, 'Badge', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(200, '258', 'IATA 1.6 : Instructions adéquates pour l’expédition de batteries au lithium de section II', '', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(201, '259', 'M14 - Formation de Formateur Qualifié en Sûreté de l\'Aviation Civile pour la dispense des Modules M01 et M04 à M10', 'Formation de formateur qualifié Sûreté M14', NULL, NULL, 0, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(202, '260', 'SADE OCC 2025- Formation Sûreté PNT CORSAIR -§11.2.3.11/§11.2.3.6/11.2.8. du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces', 'SADE OCC 2025 - PNT CORSAIR INTERNATIONAL 11.2.3.11/§11.2.3.6/11.2.8', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(203, '261', 'SADE OCC 2025- Formation Sûreté PNC CORSAIR -§11.2.3.11/§11.2.3.6 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef', 'SADE OCC 2025 - PNC CORSAIR INTERNATIONAL §11.2.3.11/§11.2.3.6', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(204, '262', '2024 - Recycling Training Course Sûreté PNC CORSAIR - -§11.2.3.11Regl. EU2015/1998 - Gestion du risque terroriste et maitrise PAXI', 'RTC 2024 - PNC CORSAIR INTERNATIONAL -Risque Terroriste - Maitrise PAXI', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(205, '263', '2024 - Recycling Training Course Sûreté PNT CORSAIR -§11.2.3.11REGL. EU2015/1998 Gestion du risque terroriste et maitrise PAXI', 'RTC 2024 - PNT CORSAIR INTERNATIONAL -Risque Terroriste - Maitrise PAXI', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(206, '264', 'Formation Sûreté Périodique Typologie 2 (T2) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.2 §11.2.3.3 §11.2.3.5 : Formation Périodique Hors Imagerie :', 'FPHI Typologie 2 (hors imagerie)', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(207, '265', 'Formation Sûreté §11.2.3.4 c) du Regl. EU2015/1998 : Formation complémentaire relative au contrôle visuel du compartiment moteur-  « formation complétée par les connaissances supplémentaires prévues au point 11.2.3.4 c) de l’annexe du règlement (UE) n° 20', '11.2.3.4 c)', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(208, '267', 'Permis Trafic CHARLES DE GAULLE', 'Permis T E LEARNING  CDG', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(209, '268', 'Gestes et Postures en Manutention atelier pratique', 'Gestes et Postures en Manutention (pratique)', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(210, '269', 'Gestes et Postures en Manutention Théorie', 'Gestes et Postures en Manutention (théorie)', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(211, '270', 'Gestes et Postures théorie et pratique', 'Gestes et Postures (théorie et pratique)', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(212, '271', 'Permis Trafic CHARLES DE GAULLE', 'Permis T CDG', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(213, '272', 'Sécurité en Piste et Système de gestion du risque', 'Sécurité en Piste et Système de gestion du risque', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(214, '273', 'Second passage Sécurité en Piste (piéton) et Système de gestion du risque', 'Sécurité Piste E-Learning', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(215, '274', 'OCC 2024- Formation Sûreté PN CELESTE -§11.2.3.6/§11.2.3.11/11.2.8. du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces', 'OCC 2024 - PN  CELESTE §11.2.3.6/§11.2.3.11/§11.2.8', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(216, '275', 'OCC 2024- Formation Sûreté PN CELESTE -11.2.3.7/11.2.3.11 du Regl. EU2015/1998: Mesures de sûreté en vol - Protection aéronef / Mesures de sûreté en vol', 'OCC- PN CELESTE 11.2.3.7/§11.2.3.11', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(217, '276', 'Sensibilisation PHMR', 'Sensibilisation PHMR', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(218, '278', 'Essai Logyx - AIR AND PORT SERVICES', 'Essai Logyx - AIR AND PORT SERVICES', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(219, '281', 'Salle à disposition annuelle LOGYX', 'Salle à disposition LOGYX', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(220, '282', 'IMDG 1.3 - Spécial véhicule sur la base d’un programme IMDG ou ADR 1.3 \"classique\"', 'IMDG 1.3 - Spécial véhicule', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(221, '283', 'SADE-OCC 2025- Formation Sûreté PNC CORSAIR -11.2.3.7/11.2.3.11/11.2.8 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces', 'SADE OCC- PNC CORSAIR INTERNATIONAL 11.2.3.7/11.2.3.11/§11.2.8', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(222, '284', 'SADE-OCC 2025 PNC §11.2.3.11 Mesures de Sûreté en vol - PN Compagnie Aérienne - Gestion du risque terroriste et maitrise PAXI', 'SADE OCC 2025 PNC -Risque Terroriste - Maitrise PAXI', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(223, '285', '2024 - SADE - OCC Sûreté PNC CORSAIR  -§11.2.3.11Regl. EU2015/1998 - Gestion du risque terroriste et maitrise PAXI', 'SADE OCC 2024 - PNC CORSAIR INTERNATIONAL -Risque Terroriste - Maitrise PAXI', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(224, '286', '11.2.3.9 + Compl. 11.2.6.2 Regl. EU2015/1998: Formation des personnes ayant un accès non surveillé au fret et courrier aérien identifiables et/ou qui effectuent sur le fret et les envois postaux des contrôles de sûreté autre que l\'IF + Partie TCA', '11.2.3.9 + Compl. 11.2.6.2', NULL, NULL, 60, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(225, '287', '11.2.3.9 Regl. EU2015/1998: Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF', '11.2.3.9', NULL, NULL, 60, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(226, '288', '11.2.3.10. + Compl. 11.2.6.2 du Regl. EU2015/1998 :Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l\'IF + TC', '11.2.3.10 + 11.2.6.2 (TCA)', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(227, '289', '11.2.3.10.du Regl. EU2015/1998 Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage -2023', '11.2.3.10', NULL, NULL, 60, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(228, '290', '11.2.2. Formation de base des personnes qui exécutent les tâches énumérées aux points 11.2.3.1, 11.2.3.4 et 11.2.3.5 et aux points 11.2.4, 11.2.5 et 11.5 -V2023', '11.2.2 Formation de base à la sûreté', NULL, NULL, 0, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(229, '291', '11.2.3.9  Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF', '11.2.3.9 - Initiale', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(230, '292', '11.2.3.9  Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF', '11.2.3.9 - Périodique', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(231, '293', '11.2.3.10 Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage', '11.2.3.10 - Initiale', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(232, '294', 'Formation UM accueil des enfants mineurs voyageant seuls', 'Accueil des UM (mineur non accompagné)', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(233, '295', 'Agent welcome', 'Agent Welcome', NULL, NULL, 0, 'COMPAGNIE', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(234, '296', 'Agent welcome', 'Agent Welcome', NULL, NULL, 0, 'COMPAGNIE', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(235, '297', 'Formation sûreté Agent Welcome', 'Agent Welcome', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(236, '298', '11.2.3.10 Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage', '11.2.3.10 - Périodique', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(237, '299', 'Permis Trafic CHARLES DE GAULLE-2-', 'Permis T CDG', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(238, '300', 'Formation Périodique Imagerie FPI T2 -RX et EDS simple vue multi-vues (11.4.1 du règlement EU2015/1998)', 'FPI Typologie 2', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(239, '301', 'Formation Périodique Imagerie FPI T6 -RX et EDS simple vue multi-vues (11.4.1 du règlement EU2015/1998)', 'FPI Typologie 6', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(240, '302', 'Formation Périodique Imagerie FPI T7 -RX et EDS simple vue multi-vues (11.4.1 du règlement EU2015/1998)', 'FPI Typologie 7', NULL, NULL, 0, 'Formation périodique', 'AGENT DE SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(241, '303', 'Formation pédagogique - Gestion du temps', 'Gestion du temps', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(242, '305', '11.2.3.9  Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF', '11.2.3.9 Second passage', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(243, '307', 'Parcours sécurité des données', '', NULL, NULL, 0, 'Soft Kill', 'E-LEARNING', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(244, '308', 'Mise à disposition d\'un référent', 'Mise à disposition d\'un référent', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(245, '310', 'Sensibilisation 1.3 ADR – Expédition par route de marchandises dangereuses (Recyclage)', 'Sensibilisation ADR 1.3 Recyclage', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(246, '311', '2ND passage - 11.2.6.2 PERIODIQUE pour l\'obtention du TCA dit badge aéroportuaire', '11.2.6.2 Obtention du badge aéroportuaire - Périodique', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(247, '312', '11.2.3.9  Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF', '11.2.3.9 - Initiale', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(248, '313', 'ADR et spécification déchets (ou autres)', 'ADR et spécification déchets (ou autres)', NULL, NULL, 0, 'Adr 1.3', 'MARCHANDISES DANGEREUSES', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(249, '314', 'Second passage 11.2.3.9 - La formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables qui ont fait l’objet de contrôles de sûreté et des personnes effectuant sur du fret aérien et du courrier aérien des contr', 'Second passage - 11.2.3.9 Périodique', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(250, '315', '11.2.3.9 + Compl. 11.2.6.2 Regl. EU2015/1998: Formation des personnes ayant un accès non surveillé au fret et courrier aérien identifiables et/ou qui effectuent sur le fret et les envois postaux des contrôles de sûreté autre que l\'IF + Partie TCA', '11.2.3.9 + Compl. 11.2.6.2', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(251, '316', '11.2.3.9 + Compl. 11.2.6.2 Formation des personnes ayant un accès non surveillé au fret et courrier aérien identifiables et/ou qui effectuent sur le fret et les envois postaux des contrôles de sûreté autre que l\'IF + Partie TCA', '11.2.3.9 + 11.2.6.2 (2nd passage)', NULL, NULL, 0, 'E-LEARNING', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(252, '317', '11.2.3.9 Regl. EU2015/1998: Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF', '11.2.3.9', NULL, NULL, 60, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(253, '320', 'CORSUR + 11.2.8', 'CORSUR + 11.2.8', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(254, '321', '11.2.3.9 Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF', '11.2.3.9 (visio)', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(255, '324', '11.2.2. Formation de base des personnes qui exécutent les tâches énumérées aux points 11.2.3.1, 11.2.3.4 et 11.2.3.5 et aux points 11.2.4, 11.2.5 et 11.5 -V2023', '11.2.2 Formation de base à la sûreté', NULL, NULL, 0, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(256, '325', '11.2.5. du Regl. EU2015/1998: Formation spécifique destinée aux personnes assumant une responsabilité générale au niveau national ou local en respect de toutes les dispositions légales applicables dans d\'un programme de sûreté et d\'Assurance Qualité', '11.2.5', NULL, NULL, 60, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(257, '326', '2025 - Recycling Training Course Sûreté PNT Corsair Aérienne -Protection aéronef  11.2.3.7. et §11.2.3.1.11 du Regl. EU2015/1998 : Formation des personnes en charge de la protection des aéronefs et sureté en vol', '2025 RTC PNT CORSAIR INTERNATIONAL §11.2.3.7 -§11.2.3.11', NULL, NULL, 0, 'Compagnie', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(258, '327', 'Formation sur mesure de base et management de la sûreté', 'Formation sur mesure', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(259, '328', '11.2.5: Formation spécifique destinée aux personnes assumant une responsabilité générale au niveau national ou local en respect de toutes les dispositions légales applicables dans d\'un programme de sûreté et d\'Assurance Qualité', '11.2.5 Management de la sûreté', NULL, NULL, 60, 'Encadrement', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(260, '329', '11.2.3.10 + Compl. 11.2.6.2 du Regl. EU2015/1998 :Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l\'IF + TCA', '11.2.3.10 + 11.2.6.2 (TCA)', NULL, NULL, 0, 'Contributeur', 'SÛRETÉ', 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(261, '330', 'Gérer les situations difficiles', 'Gérer les situations difficiles', NULL, NULL, 0, 'SOFT KILLS', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(262, '331', 'PTI (Premier Témoin Incendie) - 1H00', 'PTI (Premier Témoin Incendie) - 1H00', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(263, '332', 'PTI (Premier Témoin Incendie) - 1H30', 'PTI (Premier Témoin Incendie) - 1H30', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(264, '333', 'PTI (Premier Témoin Incendie) - 2H00', 'PTI (Premier Témoin Incendie) - 2H00', NULL, NULL, 0, 'SÉCURITÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(265, '334', 'Co-activité', 'Co-activité', NULL, NULL, 36, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL),
(266, '336', 'Co-activité + Sécurité en Piste et système de gestion du risque', 'Co-activité + Sécurité en Piste et système de gestion du risque', NULL, NULL, 0, 'SÛRETÉ', NULL, 'private', NULL, '2025-03-17 15:37:42', '2025-03-17 15:37:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `training_catalogs`
--

CREATE TABLE `training_catalogs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dendreo_id` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_title` varchar(255) DEFAULT NULL,
  `validity_duration` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `parent_category` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `two_factor_codes`
--

CREATE TABLE `two_factor_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(6) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `two_factor_codes`
--

INSERT INTO `two_factor_codes` (`id`, `user_id`, `code`, `expires_at`, `created_at`, `updated_at`) VALUES
(70, 69420, '852688', '2025-04-23 06:45:10', '2025-04-23 06:35:10', '2025-04-23 06:35:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_new` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `function` varchar(255) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `has_left` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `departure_date` date DEFAULT NULL,
  `is_student` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `is_new`, `remember_token`, `created_at`, `updated_at`, `role`, `function`, `client_id`, `has_left`, `two_factor_enabled`, `departure_date`, `is_student`) VALUES
(1, 'John Doe', 'n.dupin@r4web.fr', NULL, '$2y$12$cTRs6i32BTSeAcWWi/WAGuvSPArVy545hf2XcRUZwutYMyEJNVJ3i', 0, NULL, '2025-01-24 10:30:33', '2025-04-03 13:45:40', 'sadmin', NULL, 1, 0, 0, NULL, 1),
(24, 'Admin Rem', 'evan.pasutto@rem-distribution.com', NULL, '$2y$12$be8I7T4NWrhoqadbNgfLKeHs0ncNrfVXOwveEgPOhqtRiQ52FRrOi', 1, NULL, '2025-03-07 18:23:59', '2025-03-07 18:26:53', 'sadmin', NULL, 1, 0, 1, NULL, 1),
(25, 'Clement Richard', 'clement.richard@r4web.fr', NULL, '$2y$12$1XMklhntrTSOFUHQeD575.fW7TEjibgmg0meVL7Kre8YGK4zirXva', 1, NULL, '2025-03-07 18:25:31', '2025-03-07 18:25:31', 'sadmin', NULL, 1, 0, 1, NULL, 1),
(26, 'ADMIN DE LA SOCIETE TEST', 'pasutto.evan@gmail.com', NULL, '$2y$12$jeuR5BooRzoFKJMwPL/vFO6HqCSzOv6SU.N1RAytNvZm3t3P0XDOa', 1, NULL, '2025-03-11 12:13:28', '2025-03-14 13:12:15', 'sclient', NULL, 6, 1, 1, '2025-02-07', 0),
(27, 'JON JON', 'cricri77420@gmail.com', NULL, '$2y$12$LxHIUd4Qn3WF9v7JVV8E.uu3mX2YpJKhqs1kuZ4Mm0n7jg79b9DEC', 1, NULL, '2025-03-11 12:25:00', '2025-03-11 12:25:00', 'client', NULL, 6, 0, 1, NULL, 0),
(28, 'BEN D', 'benjamin.diard@r4web.fr', NULL, '$2y$12$th/bmMU5F6im3FYa907NMuc/huBZsZONW5TW.MI11W4P9/TEwBaSu', 1, NULL, '2025-03-17 15:33:59', '2025-03-17 15:33:59', 'admin', NULL, 6, 0, 1, NULL, 1),
(29, 'RICHARD CLEMENT TEST MAIL COMPTE', 'contact@r4web.fr', NULL, '$2y$12$SqoW3jF3R83qgPih0/8smOq90TgSPz/3EcGIXaIzorG31AhlW8oJy', 1, NULL, '2025-03-20 08:22:01', '2025-03-20 08:22:01', 'client', NULL, 6, 0, 1, NULL, 0),
(69420, 'trublion', 'truc.bidule@r4web.fr', NULL, '$2y$12$xPuDz5lKkfZRrqj0N.1xd.PcwvDgyO.tVv5PBMoP5s82XeSZUwuYG', 1, NULL, NULL, NULL, 'user', NULL, 0, 0, 1, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_trainings`
--

CREATE TABLE `user_trainings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `certificate_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_trainings`
--

INSERT INTO `user_trainings` (`id`, `user_id`, `training_id`, `started_at`, `expires_at`, `certificate_path`, `created_at`, `updated_at`) VALUES
(1, 25, 63, '2025-03-17 23:00:00', '2025-03-17 23:00:00', NULL, '2025-03-17 15:38:31', '2025-03-17 15:38:31'),
(2, 25, 2, '2025-03-26 23:00:00', '2025-03-26 23:00:00', NULL, '2025-03-17 15:38:52', '2025-03-17 15:38:52'),
(3, 29, 146, '2025-03-29 23:00:00', '2025-03-29 23:00:00', NULL, '2025-03-20 17:05:38', '2025-03-20 17:05:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_comments`
--
ALTER TABLE `activity_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_comments_user_id_foreign` (`user_id`),
  ADD KEY `activity_comments_activity_request_id_foreign` (`activity_request_id`);

--
-- Indexes for table `activity_requests`
--
ALTER TABLE `activity_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_requests_user_id_foreign` (`user_id`),
  ADD KEY `activity_requests_created_by_foreign` (`created_by`);

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attachments_message_id_foreign` (`message_id`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `badges_badge_number_unique` (`badge_number`),
  ADD KEY `badges_badge_request_id_foreign` (`badge_request_id`);

--
-- Indexes for table `badge_requests`
--
ALTER TABLE `badge_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `badge_requests_user_id_foreign` (`user_id`),
  ADD KEY `badge_requests_client_id_foreign` (`client_id`),
  ADD KEY `badge_requests_created_by_foreign` (`created_by`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_training_access`
--
ALTER TABLE `client_training_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_training_access_client_id_training_id_unique` (`client_id`,`training_id`),
  ADD KEY `client_training_access_training_id_foreign` (`training_id`),
  ADD KEY `client_training_access_status_index` (`status`),
  ADD KEY `client_training_access_access_expires_at_index` (`access_expires_at`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_user_id_foreign` (`user_id`),
  ADD KEY `comments_badge_request_id_foreign` (`badge_request_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversations_created_by_foreign` (`created_by`);

--
-- Indexes for table `discussions`
--
ALTER TABLE `discussions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `discussions_user_id_foreign` (`user_id`),
  ADD KEY `discussions_last_comment_user_id_foreign` (`last_comment_user_id`);

--
-- Indexes for table `discussion_files`
--
ALTER TABLE `discussion_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `discussion_files_discussion_id_foreign` (`discussion_id`),
  ADD KEY `discussion_files_message_comment_id_foreign` (`message_comment_id`);

--
-- Indexes for table `discussion_read_status`
--
ALTER TABLE `discussion_read_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `discussion_read_status_user_id_discussion_id_unique` (`user_id`,`discussion_id`),
  ADD KEY `discussion_read_status_discussion_id_foreign` (`discussion_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_conversation_id_foreign` (`conversation_id`),
  ADD KEY `messages_user_id_foreign` (`user_id`);

--
-- Indexes for table `message_comments`
--
ALTER TABLE `message_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_comments_user_id_foreign` (`user_id`),
  ADD KEY `message_comments_discussion_id_foreign` (`discussion_id`),
  ADD KEY `message_comments_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `replies_comment_id_foreign` (`comment_id`),
  ADD KEY `replies_user_id_foreign` (`user_id`);

--
-- Indexes for table `reply_activities`
--
ALTER TABLE `reply_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reply_activities_activity_comment_id_foreign` (`activity_comment_id`),
  ADD KEY `reply_activities_user_id_foreign` (`user_id`);

--
-- Indexes for table `trainings`
--
ALTER TABLE `trainings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `training_catalogs`
--
ALTER TABLE `training_catalogs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `training_catalogs_dendreo_id_unique` (`dendreo_id`);

--
-- Indexes for table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `two_factor_codes_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_trainings`
--
ALTER TABLE `user_trainings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_trainings_user_id_training_id_unique` (`user_id`,`training_id`),
  ADD KEY `user_trainings_training_id_foreign` (`training_id`),
  ADD KEY `user_trainings_expires_at_index` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_comments`
--
ALTER TABLE `activity_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `activity_requests`
--
ALTER TABLE `activity_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `badge_requests`
--
ALTER TABLE `badge_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `client_training_access`
--
ALTER TABLE `client_training_access`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discussions`
--
ALTER TABLE `discussions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `discussion_files`
--
ALTER TABLE `discussion_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `discussion_read_status`
--
ALTER TABLE `discussion_read_status`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_comments`
--
ALTER TABLE `message_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reply_activities`
--
ALTER TABLE `reply_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trainings`
--
ALTER TABLE `trainings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=267;

--
-- AUTO_INCREMENT for table `training_catalogs`
--
ALTER TABLE `training_catalogs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69421;

--
-- AUTO_INCREMENT for table `user_trainings`
--
ALTER TABLE `user_trainings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_comments`
--
ALTER TABLE `activity_comments`
  ADD CONSTRAINT `activity_comments_activity_request_id_foreign` FOREIGN KEY (`activity_request_id`) REFERENCES `activity_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_requests`
--
ALTER TABLE `activity_requests`
  ADD CONSTRAINT `activity_requests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `badges`
--
ALTER TABLE `badges`
  ADD CONSTRAINT `badges_badge_request_id_foreign` FOREIGN KEY (`badge_request_id`) REFERENCES `badge_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `badge_requests`
--
ALTER TABLE `badge_requests`
  ADD CONSTRAINT `badge_requests_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `badge_requests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `badge_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_training_access`
--
ALTER TABLE `client_training_access`
  ADD CONSTRAINT `client_training_access_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_training_access_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_badge_request_id_foreign` FOREIGN KEY (`badge_request_id`) REFERENCES `badge_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussions`
--
ALTER TABLE `discussions`
  ADD CONSTRAINT `discussions_last_comment_user_id_foreign` FOREIGN KEY (`last_comment_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `discussions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussion_files`
--
ALTER TABLE `discussion_files`
  ADD CONSTRAINT `discussion_files_discussion_id_foreign` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_files_message_comment_id_foreign` FOREIGN KEY (`message_comment_id`) REFERENCES `message_comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussion_read_status`
--
ALTER TABLE `discussion_read_status`
  ADD CONSTRAINT `discussion_read_status_discussion_id_foreign` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_read_status_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_comments`
--
ALTER TABLE `message_comments`
  ADD CONSTRAINT `message_comments_discussion_id_foreign` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `message_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reply_activities`
--
ALTER TABLE `reply_activities`
  ADD CONSTRAINT `reply_activities_activity_comment_id_foreign` FOREIGN KEY (`activity_comment_id`) REFERENCES `activity_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reply_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  ADD CONSTRAINT `two_factor_codes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_trainings`
--
ALTER TABLE `user_trainings`
  ADD CONSTRAINT `user_trainings_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_trainings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
