-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 16 déc. 2024 à 15:17
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `api_badges`
--

-- --------------------------------------------------------

--
-- Structure de la table `attachments`
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

--
-- Déchargement des données de la table `attachments`
--

INSERT INTO `attachments` (`id`, `message_id`, `original_name`, `file_path`, `mime_type`, `file_size`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 9, 'Capture d’écran 2024-11-28 111258.png', 'attachments/oO46QEw0Of7i0VhpRoWP9XVrQVFVfj3m46frjhs8.png', 'image/png', 13830, '2024-11-29 15:56:30', '2024-11-29 15:56:30', NULL),
(2, 10, 'chute20-11-2024.png', 'attachments/6XZnyXji0hmSpStbMPZ3wuOFFAAWwOUa1V2EYMFK.png', 'image/png', 762324, '2024-11-30 07:52:03', '2024-11-30 07:52:03', NULL),
(3, 13, 'Capture d’écran 2024-11-28 111258.png', 'attachments/EAyOSbpbMhilAilko81pYttVdOgjXYyaNmAvCInL.png', 'image/png', 13830, '2024-12-01 09:57:13', '2024-12-01 09:57:13', NULL),
(4, 14, 'Capture d’écran 2024-11-28 111258.png', 'attachments/xrXDTML3Gom1rU2muDfFTIO3B0600Rs61JyR1Oe7.png', 'image/png', 13830, '2024-12-01 09:57:48', '2024-12-01 09:57:48', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `badge_requests`
--

CREATE TABLE `badge_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `photoIdentite` varchar(255) NOT NULL,
  `autorisationActivite` varchar(255) NOT NULL,
  `certificatFormation` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `badge_requests`
--

INSERT INTO `badge_requests` (`id`, `user_id`, `nom`, `prenom`, `email`, `telephone`, `status`, `photoIdentite`, `autorisationActivite`, `certificatFormation`, `created_at`, `updated_at`) VALUES
(1, 1, 'dupin', 'nicolas', 'dupin.nicolas@yahoo.fr', '0619180765', 'pending', 'photos/xzx6wtTVWvOSOzxuVgA5pQFbveHKe5G0S1wFg3au.jpg', 'autorisations/RxMhcUOXhmIOE83VfY1XuU14pfD6OjPiRsE0jKWs.png', 'certificats/Ngvt4jNjiYk5Wx7Z8xC0T3cOmv3k0LW6srOrV0wW.png', '2024-11-26 17:05:07', '2024-11-26 17:05:07'),
(2, 1, 'nicolas', 'dupin', 'pinpin34android@gmail.com', '0619180765', 'approved', 'photos/4ueJmm1od1x1KakXSyzDvOlz93Gtf2kQOgu1u9La.jpg', 'autorisations/aWHtlwuCqD63qtPHHpeQhpoytasUlc18S3TJMGRP.png', 'certificats/ZeaW8HYE2H2bBVygcfNSHS34GOEvNS30lNuCn3np.png', '2024-11-26 17:05:53', '2024-11-26 17:14:39'),
(3, 1, 'DUPIN', 'Nicolas', 'nicolas1.dupin@orange.com', '0647545931', 'pending', 'photos/LCVhIqIGCVvIdstFP9nl0w8gVHqhBMMJb1k0q1kK.jpg', 'autorisations/9XdrJsoTDj5gBJBuBN13mcMuCjtoD8B3vWG6RhFo.png', 'certificats/rpM4CIa2KQYInU8nwJdm61BDb7tzHatxjtz3FOkw.png', '2024-11-26 17:22:43', '2024-11-26 17:22:43'),
(4, 1, 'dupin', 'nicolas', 'dupin.nicolas@yahoo.fr', '0619180765', 'approved', 'photos/EM5W1Q26EzHTJre9Yg3vCYWpKYSMK7eR3AAZwshp.jpg', 'autorisations/bR4jQ4SVHuxyrDWL7DJ6oVXhgu48GVwh4g2HiB60.png', 'certificats/utgOLukb79V82wS9KANNsgg7etiIxHsejJ4hVyGa.png', '2024-11-26 20:03:58', '2024-11-27 10:12:40'),
(5, 1, 'DUPIN', 'Nicolas', 'nicolas1.dupin@orange.com', '0647545931', 'approved', 'photos/FNhLFS6nLieDXzDJYFxMMmhKzCrRH8ttIN1KvGZw.jpg', 'autorisations/6SjwElN461r6YYZFB5T7Z1KPVj79x71mROiyj4JR.png', 'certificats/xxkyjFkNdb6RPjjoqF07MHO8RIUecB6ME5WFyZR5.png', '2024-11-27 08:55:13', '2024-11-27 08:56:00'),
(6, 7, 'dupin', 'nicolas', 'dupin.nicolas@yahoo.fr', '0619180765', 'rejected', 'photos/sMsQNB2li4AlwYSipNU4LQ7DcqpTY1WwS4XVwYL0.jpg', 'autorisations/mtRwylw0uRzzcY1I1rlm19hIuDQR0CSxFOGyw6Sy.png', 'certificats/PFMwg8KQcR7FRIyR5yLuopAnD4rHtG8HCvp6f5n0.png', '2024-11-27 09:58:21', '2024-11-27 10:12:36'),
(7, 6, 'kreiger.mariah@example.net', 'kreiger.mariah@example.net', 'kreiger.mariah@example.net', '0647545931', 'pending', 'photos/1H03UNmVPh4hZd0iMdiuXttttFeMpevwuEBmkG1X.jpg', 'autorisations/QmslOrdEE0KDEyLFAnadKa9YbqHOb7qfDr64DZYV.png', 'certificats/kgqqKuCdPSqRLzBUrGp7VPrZRLQocz1WUa09ltcL.png', '2024-11-27 10:16:21', '2024-11-27 10:16:21'),
(8, 6, 'dupin', 'nicolas', 'dupin.nicolas@yahoo.fr', '0619180765', 'approved', 'photos/L82MAU62kvmmmDaYeaHouwGPTB8iCHssKcNYj1AA.jpg', 'autorisations/6OapjPL73VjxdbPjW4inAZJddioR9fdFOMbty6VA.png', 'certificats/rhokfgavEdWuFLY93T3yPcbqWwqIMycZ0nB5Tjbn.png', '2024-11-27 11:18:01', '2024-11-27 11:18:56'),
(9, 6, 'nicolasxcvc', 'dupinxvvx c', 'pinpin34android@gmail.com', '0619180765', 'approved', 'photos/TOQUAk156tY5YXeBP0zgugbZYAn0C31Sz7yyXvPi.jpg', 'autorisations/CGpMoedlec8G1chvBcGRulvhRQtvKEQLWtTaCSi7.png', 'certificats/CJPIuEuOxbOx1y3pORlCx9V8jWogac0O6CN2vVa2.png', '2024-11-27 13:19:37', '2024-12-01 09:45:56'),
(10, 19, 'dupin', 'nicolas', 'dupin.nicolas@yahoo.fr', '0619180765', 'rejected', 'photos/0UBUEj12LS7iNagGDIkhL6aB3hFzhjWLL8xUSuoV.jpg', 'autorisations/eNbQ8KcSlu0RScvwnrFzf1Gl3WNAuLXsApL0pTTj.png', 'certificats/xqgIh93XeLCkmAXd8qxfTKUnbq2VpQJuYaOxGoxs.png', '2024-11-27 14:06:33', '2024-11-28 11:47:21'),
(12, 1, 'dupin', 'nicolas', 'dupin.nicolas@yahoo.fr', '0619180765', 'approved', 'photos/2iU0bbSTLJ0Sj6dnSi5OzpSe4y4NSZxkTyY578rf.jpg', 'autorisations/zAzanIbHYX2hegl2x0sETzBhAR7xPHfnQ8svHIm3.png', 'certificats/qKe7P2QWx3WM8Ca2qhAGAkqgHAgGJImyj206QfDa.png', '2024-11-28 11:46:47', '2024-11-28 11:47:17'),
(13, 6, 'dupin', 'nicolas', 'dupin.nicolas@yahoo.fr', '0619180765', 'rejected', 'photos/ekHsfrHGaOErqTRqKCxaR5rV3iAaS23t3hnKvCKl.jpg', 'autorisations/WSdo8ObfINgQEywS5y9Alp55ITU9LAAIRR630cpu.png', 'certificats/68oxTMXOL35QVR03Uhgh6ci2lReypcROe7CQSnva.png', '2024-11-28 16:50:38', '2024-12-01 09:45:59'),
(14, 1, 'dupin', 'nicolas', 'dupin.nicolas@yahoo.fr', '0619180765', 'pending', 'photos/8qLsPQ9978uswIw3Z6P2LapvbWJFd7vEhC9oiYry.jpg', 'autorisations/QDYwlo13FPYq0KCKsGAL6YvDA8MwgdxlsIgtwGDp.png', 'certificats/IeF8lo5kCeQMApymWsw2iM5bINUpdSVP5eiPpi7j.png', '2024-12-01 09:45:29', '2024-12-01 09:45:29'),
(15, 1, 'dupin', 'nicolas', 'dupin.nicolas@yahoo.fr', '0619180765', 'approved', 'photos/rSQPrZfMYN9iQ7hmCZGwTjcW5WoDkboODnd71es0.jpg', 'autorisations/vtmqk4KC3j193jfzKYIIGAXfNy5arMLTDEsg3fs2.png', 'certificats/k9MKJQNKQmzkfnVVinhLg8lFy4wr6RuZvIF2KtDK.png', '2024-12-01 16:11:58', '2024-12-16 12:25:16'),
(16, 1, 'hih', 'ihih', 'dupin.nicolas@yahoo.fr', '0619180765', 'approved', 'photos/piGaMbpZ5Mt6lvKpAo814QYmqj7aWWfaRoN0lpgb.jpg', 'autorisations/OQqasx4dvRi4fndBcjt5i40HYYmrz25aCjvghONk.png', 'certificats/MlKKqJSy4rM1LpfqZ3jViFqc3rj0wRjTsAxN3MDS.png', '2024-12-16 12:24:39', '2024-12-16 12:25:09');

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Le Client', NULL, '2024-12-01 09:46:36'),
(2, 'test client', '2024-11-27 13:32:35', '2024-11-27 13:32:35'),
(3, 'tarte', '2024-11-27 14:04:03', '2024-11-27 14:04:03'),
(4, 'R4web', '2024-11-28 18:58:10', '2024-11-28 18:58:10');

-- --------------------------------------------------------

--
-- Structure de la table `comments`
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
-- Déchargement des données de la table `comments`
--

INSERT INTO `comments` (`id`, `content`, `user_id`, `badge_request_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fff', 1, 4, '2024-11-27 08:44:01', '2024-11-27 08:47:17', '2024-11-27 08:47:17'),
(2, 'test de commentairessss', 1, 4, '2024-11-27 08:47:34', '2024-11-27 08:50:09', '2024-11-27 08:50:09'),
(3, 'ici je test un commentaire', 1, 4, '2024-11-27 08:50:34', '2024-11-27 08:50:34', NULL),
(4, 'commentaire', 1, 5, '2024-11-27 08:55:42', '2024-11-27 08:55:42', NULL),
(5, 'cvbxcvcxv', 1, 6, '2024-11-27 09:58:30', '2024-11-27 09:58:41', '2024-11-27 09:58:41'),
(6, 'xwc vxxxx', 1, 4, '2024-11-27 10:12:46', '2024-11-27 10:12:58', '2024-11-27 10:12:58'),
(7, 'test de commentaire', 6, 7, '2024-11-27 10:17:02', '2024-11-27 10:17:02', NULL),
(8, 'ezdsedfqsdf', 6, 8, '2024-11-27 11:18:11', '2024-11-27 11:18:11', NULL),
(9, 'qzsfdb', 20, 10, '2024-11-27 14:08:42', '2024-11-27 14:08:42', NULL),
(10, 'sdfvbbvsd', 1, 12, '2024-11-28 11:47:06', '2024-11-28 11:47:14', '2024-11-28 11:47:14'),
(11, 'sdgrdbxbc', 6, 9, '2024-11-28 16:15:22', '2024-11-28 16:15:33', '2024-11-28 16:15:33'),
(12, 'daqsdxcvb', 6, 13, '2024-11-28 16:51:43', '2024-11-28 16:51:43', NULL),
(13, 'reponse today', 1, 13, '2024-11-29 13:00:33', '2024-11-29 13:00:33', NULL),
(14, 'xwcvccc', 1, 12, '2024-12-01 09:45:38', '2024-12-01 09:45:45', NULL),
(15, 'cfbcb', 1, 14, '2024-12-01 09:59:17', '2024-12-01 09:59:17', NULL),
(16, 'cvbc', 1, 14, '2024-12-01 09:59:20', '2024-12-01 09:59:20', NULL),
(17, 'sdfg', 1, 15, '2024-12-01 16:12:13', '2024-12-01 16:12:19', '2024-12-01 16:12:19'),
(18, 'szdfgbhnsdfgvb', 1, 15, '2024-12-01 16:12:22', '2024-12-01 16:12:25', NULL),
(19, 'te_', 1, 16, '2024-12-16 12:24:48', '2024-12-16 12:24:48', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `conversations`
--

CREATE TABLE `conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `object` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `conversations`
--

INSERT INTO `conversations` (`id`, `object`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Test conversation', 'pending', 1, '2024-11-29 15:13:22', '2024-11-29 15:13:22', NULL),
(2, 'Test conversation', 'pending', 1, '2024-11-29 15:12:55', '2024-11-29 15:12:55', NULL),
(3, NULL, 'open', 1, '2024-11-29 15:41:49', '2024-11-29 15:41:49', NULL),
(4, NULL, 'open', 1, '2024-11-29 15:48:29', '2024-11-29 15:48:29', NULL),
(5, NULL, 'open', 1, '2024-11-29 15:49:23', '2024-11-29 15:49:23', NULL),
(6, NULL, 'open', 1, '2024-11-29 15:50:26', '2024-11-29 15:50:26', NULL),
(7, NULL, 'open', 1, '2024-11-29 15:55:16', '2024-11-29 15:55:16', NULL),
(8, NULL, 'open', 1, '2024-11-29 15:55:37', '2024-11-29 15:55:37', NULL),
(9, NULL, 'open', 1, '2024-11-29 15:56:30', '2024-11-29 15:56:30', NULL),
(10, NULL, 'open', 1, '2024-11-30 07:52:03', '2024-11-30 07:52:03', NULL),
(11, NULL, 'open', 1, '2024-12-01 09:57:13', '2024-12-01 09:57:13', NULL),
(12, NULL, 'open', 1, '2024-12-01 09:57:48', '2024-12-01 09:57:48', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `discussions`
--

CREATE TABLE `discussions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `discussions`
--

INSERT INTO `discussions` (`id`, `user_id`, `subject`, `content`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'sfcx', 'dgxdg', 'open', '2024-12-01 10:44:59', '2024-12-01 11:18:38'),
(2, 1, 'Time attitude', 'C\'est la merde', 'closed', '2024-12-01 11:13:26', '2024-12-01 14:14:11'),
(3, 1, 'test', 'test', 'closed', '2024-12-01 11:14:00', '2024-12-01 14:00:01'),
(4, 1, 'r4web', 'r4web', 'open', '2024-12-01 13:55:47', '2024-12-01 14:31:11'),
(5, 1, 'w<xcvb', 'sxdcfvgbnh,;', 'open', '2024-12-01 14:32:20', '2024-12-01 16:00:56'),
(6, 1, 'cxcvxc', 'vcxvc', 'open', '2024-12-01 16:08:40', '2024-12-03 15:22:46'),
(7, 1, 'ggg', 'gggg', 'open', '2024-12-16 12:26:32', '2024-12-16 12:26:32');

-- --------------------------------------------------------

--
-- Structure de la table `discussion_files`
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
-- Déchargement des données de la table `discussion_files`
--

INSERT INTO `discussion_files` (`id`, `name`, `path`, `discussion_id`, `message_comment_id`, `created_at`, `updated_at`) VALUES
(1, 'Capture d’écran 2024-11-28 111258.png', 'discussion-files/v9h77Qo7KDIMRTeBfMTWVYFgDUAnbPp7xIYtagY2.png', 1, NULL, '2024-12-01 10:44:59', '2024-12-01 10:44:59'),
(2, 'chute20-11-2024.png', 'comment-files/giaWeNSNEvrqvb2qHRagljP8KU3o1dK0wOGbPNvR.png', NULL, 4, '2024-12-01 10:54:07', '2024-12-01 10:54:07'),
(3, 'Capture d’écran 2024-11-29 105105.png', 'comment-files/tn7mJD2Brh7IndSFBiKMZgBuBqLydIiA6Vl1y1S5.png', NULL, 5, '2024-12-01 10:55:20', '2024-12-01 10:55:20'),
(4, 'chute20-11-2024.png', 'comment-files/slXhUPb7BxiPklL1Q0M5sWP2tK229IxJW3As4D0A.png', NULL, 9, '2024-12-01 11:06:24', '2024-12-01 11:06:24'),
(5, 'chute20-11-2024.png', 'discussion-files/ET1JDDklm0pLdzSF3rsDKmepHcpDaXZ8qB9DPQiF.png', 2, NULL, '2024-12-01 11:13:26', '2024-12-01 11:13:26'),
(6, 'Capture d’écran 2024-11-28 111258.png', 'discussion-files/Rya7jrTgRybSatDl5qr1AtUsouNNWYSIuBhemSH6.png', 3, NULL, '2024-12-01 11:14:00', '2024-12-01 11:14:00'),
(7, 'Capture d’écran 2024-11-29 105105.png', 'discussion-files/PU9VcUZBeWA2D30GJ24Oug1mxJecXaCBvgwrmMDs.png', 3, NULL, '2024-12-01 11:14:00', '2024-12-01 11:14:00'),
(8, 'chute20-11-2024.png', 'discussion-files/ENnE8NfuZELpSV6pDa6G83r5P0Q42QpkDLzROYzS.png', 3, NULL, '2024-12-01 11:14:00', '2024-12-01 11:14:00'),
(9, 'chute20-11-2024.png', 'comment-files/QVJGVwgcyAF4w4QBkZPXz327TfiNr32W2qxYXvUZ.png', NULL, 11, '2024-12-01 11:14:20', '2024-12-01 11:14:20'),
(10, 'Capture d’écran 2024-11-28 111258.png', 'comment-files/1xfbN8m7e1KoGdnRSDizLxZN9BzIayM0pxbAZ8oI.png', NULL, 12, '2024-12-01 11:28:42', '2024-12-01 11:28:42'),
(11, 'Capture d’écran 2024-11-28 111258.png', 'comment-files/7h3KEBAbxpGR15EAmNpL8GwZpyhfKIX5ZCuEv7Zd.png', NULL, 22, '2024-12-01 14:13:47', '2024-12-01 14:13:47'),
(12, 'Capture d’écran 2024-11-28 111258.png', 'comment-files/XCxHvXxAhYBmHorKgFYECE4SLtza8Dm8JfNpLTt8.png', NULL, 25, '2024-12-01 14:30:50', '2024-12-01 14:30:50'),
(13, 'chute20-11-2024.png', 'discussion-files/ThbD2cESnzKDrV1wvj9MoOT2OdTtL0IsvWYCLYo4.png', 6, NULL, '2024-12-01 16:08:40', '2024-12-01 16:08:40'),
(14, 'Capture d’écran 2024-12-01 181731.png', 'comment-files/UemGGFweB7WXPftXl2UvOQabdoRgHqgRi34nhkRo.png', NULL, 41, '2024-12-03 15:22:11', '2024-12-03 15:22:11'),
(15, 'chute20-11-2024.png', 'discussion-files/2PSrt1XAJhfpCbs2CrhDtV2hr9VOE9heKj2J9l8J.png', 7, NULL, '2024-12-16 12:26:32', '2024-12-16 12:26:32'),
(16, 'chute20-11-2024.png', 'comment-files/Ei7AoMz6NC0CE34mzy5bj4yWOWjr8Y68MuoLUZ72.png', NULL, 43, '2024-12-16 12:26:56', '2024-12-16 12:26:56');

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
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
-- Structure de la table `messages`
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

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `user_id`, `content`, `is_read`, `attachments`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 1, 1, 'Premier message de test', 0, NULL, '2024-11-29 15:13:22', '2024-11-29 15:13:22', NULL),
(3, 3, 1, 'sdf', 0, NULL, '2024-11-29 15:41:49', '2024-11-29 15:41:49', NULL),
(4, 4, 1, 'dxcv', 0, NULL, '2024-11-29 15:48:29', '2024-11-29 15:48:29', NULL),
(5, 5, 1, 'sdqfqsdf', 0, NULL, '2024-11-29 15:49:23', '2024-11-29 15:49:23', NULL),
(6, 6, 1, 'vdv', 0, NULL, '2024-11-29 15:50:26', '2024-11-29 15:50:26', NULL),
(7, 7, 1, 'cxvb', 0, NULL, '2024-11-29 15:55:16', '2024-11-29 15:55:16', NULL),
(8, 8, 1, 'dxw', 0, NULL, '2024-11-29 15:55:37', '2024-11-29 15:55:37', NULL),
(9, 9, 1, 'sdwxc', 0, NULL, '2024-11-29 15:56:30', '2024-11-29 15:56:30', NULL),
(10, 10, 1, 'vcxvbxvc', 0, NULL, '2024-11-30 07:52:03', '2024-11-30 07:52:03', NULL),
(11, 1, 1, 'sdgdf', 0, NULL, '2024-12-01 09:56:15', '2024-12-01 09:56:15', NULL),
(12, 1, 1, 'ccccv', 0, NULL, '2024-12-01 09:56:52', '2024-12-01 09:56:52', NULL),
(13, 11, 1, 'xcvcxv', 0, NULL, '2024-12-01 09:57:13', '2024-12-01 09:57:13', NULL),
(14, 12, 1, 'bbbbb', 0, NULL, '2024-12-01 09:57:48', '2024-12-01 09:57:48', NULL),
(15, 12, 1, 'cxxb', 0, NULL, '2024-12-01 09:58:51', '2024-12-01 09:58:51', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `message_comments`
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
-- Déchargement des données de la table `message_comments`
--

INSERT INTO `message_comments` (`id`, `user_id`, `discussion_id`, `parent_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'cswvdwc', '2024-12-01 10:51:52', '2024-12-01 10:51:52'),
(2, 1, 1, NULL, 'cswvdwc', '2024-12-01 10:52:38', '2024-12-01 10:52:38'),
(3, 1, 1, NULL, 'cswvdwc', '2024-12-01 10:53:55', '2024-12-01 10:53:55'),
(4, 1, 1, NULL, 'test', '2024-12-01 10:54:07', '2024-12-01 10:54:07'),
(5, 1, 1, NULL, 'test de mise a jour', '2024-12-01 10:55:20', '2024-12-01 10:55:20'),
(6, 1, 1, NULL, 'wxvxv', '2024-12-01 11:05:07', '2024-12-01 11:05:07'),
(7, 1, 1, NULL, 'sesdffdf', '2024-12-01 11:06:00', '2024-12-01 11:06:00'),
(8, 1, 1, NULL, '1111111111', '2024-12-01 11:06:06', '2024-12-01 11:06:06'),
(9, 1, 1, NULL, '222222', '2024-12-01 11:06:24', '2024-12-01 11:06:24'),
(10, 1, 1, NULL, 'xbcvbv', '2024-12-01 11:12:08', '2024-12-01 11:12:08'),
(11, 1, 3, NULL, 'test', '2024-12-01 11:14:20', '2024-12-01 11:14:20'),
(12, 6, 3, NULL, 'sxdqscqfcvb', '2024-12-01 11:28:42', '2024-12-01 11:28:42'),
(13, 1, 3, NULL, '<xscvb', '2024-12-01 12:04:17', '2024-12-01 12:04:17'),
(14, 1, 3, NULL, 'cv', '2024-12-01 13:47:48', '2024-12-01 13:47:48'),
(15, 1, 3, NULL, 'cvb', '2024-12-01 13:48:09', '2024-12-01 13:48:09'),
(16, 1, 3, NULL, 'dddd', '2024-12-01 13:48:45', '2024-12-01 13:48:45'),
(17, 1, 2, NULL, 'time', '2024-12-01 13:49:02', '2024-12-01 13:49:02'),
(18, 1, 2, NULL, 'time', '2024-12-01 13:49:51', '2024-12-01 13:49:51'),
(19, 1, 2, NULL, 'xdcx  time', '2024-12-01 13:55:15', '2024-12-01 13:55:15'),
(20, 1, 4, NULL, 'r4webr4web', '2024-12-01 13:55:56', '2024-12-01 13:55:56'),
(21, 1, 4, NULL, 'lol', '2024-12-01 14:10:49', '2024-12-01 14:10:49'),
(22, 1, 4, NULL, 'test', '2024-12-01 14:13:47', '2024-12-01 14:13:47'),
(23, 1, 4, NULL, 'dd', '2024-12-01 14:21:13', '2024-12-01 14:21:13'),
(24, 1, 4, NULL, 'zob', '2024-12-01 14:30:38', '2024-12-01 14:30:38'),
(25, 1, 4, NULL, 'aqsdfvgbhnj,;:', '2024-12-01 14:30:50', '2024-12-01 14:30:50'),
(26, 1, 5, NULL, 'sxdcfvg,', '2024-12-01 14:32:34', '2024-12-01 14:32:34'),
(27, 1, 5, NULL, 'xcvb', '2024-12-01 14:33:15', '2024-12-01 14:33:15'),
(28, 1, 5, NULL, 'ssqd', '2024-12-01 15:06:45', '2024-12-01 15:06:45'),
(29, 1, 5, NULL, 'ffffff', '2024-12-01 15:06:50', '2024-12-01 15:06:50'),
(30, 1, 5, NULL, 'xxxxxxxxxx', '2024-12-01 15:07:08', '2024-12-01 15:07:08'),
(31, 1, 5, NULL, 'wwwwwwwwwwwww', '2024-12-01 15:07:25', '2024-12-01 15:07:25'),
(32, 1, 5, NULL, 'fffffffff', '2024-12-01 15:07:47', '2024-12-01 15:07:47'),
(33, 1, 5, NULL, 'ffff', '2024-12-01 15:48:37', '2024-12-01 15:48:37'),
(34, 1, 5, NULL, 'sdfghnj,', '2024-12-01 15:49:33', '2024-12-01 15:49:33'),
(35, 1, 5, NULL, 'ff', '2024-12-01 15:49:58', '2024-12-01 15:49:58'),
(36, 1, 5, NULL, 'test', '2024-12-01 15:51:56', '2024-12-01 15:51:56'),
(37, 1, 5, NULL, 'eeeeeeeeeeeeeeeeeeeeeeeeeeee', '2024-12-01 15:53:43', '2024-12-01 15:53:43'),
(38, 6, 5, NULL, 'cxvxcbv', '2024-12-01 16:06:16', '2024-12-01 16:06:16'),
(39, 1, 6, NULL, 'xcvcxbcb', '2024-12-01 16:08:49', '2024-12-01 16:08:49'),
(40, 1, 6, NULL, 'w', '2024-12-01 16:09:43', '2024-12-01 16:09:43'),
(41, 1, 6, NULL, 'ta mere', '2024-12-03 15:22:11', '2024-12-03 15:22:11'),
(42, 1, 7, NULL, 'gug_', '2024-12-16 12:26:41', '2024-12-16 12:26:41'),
(43, 1, 7, NULL, 'gyu', '2024-12-16 12:26:56', '2024-12-16 12:26:56');

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2024_11_26_144317_add_role_to_users_table', 2),
(6, '2024_11_26_174624_create_badge_requests_table', 3),
(7, '2024_11_27_084548_create_comments_table', 4),
(8, '2024_11_27_084549_create_replies_table', 4),
(9, '2024_11_27_104056_add_user_id_to_badge_requests_table', 5),
(10, '2024_11_27_104436_add_foreign_key_to_badge_requests_table_step2', 6),
(11, '2024_11_27_132714_create_clients_table', 7),
(14, '2024_11_29_083417_create_messages_table', 8),
(15, '2024_11_29_091536_create_message_attachments_table', 8),
(16, '2024_11_29_094712_add_parent_message_id_to_message_table', 9),
(17, '2024_11_29_141257_create_conversations_table', 10),
(18, '2024_12_01_112925_create_discussions_table', 11),
(19, '2024_12_01_112951_create_message_comments_table', 11),
(20, '2024_12_01_113017_create_discussion_files_table', 11);

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personal_access_tokens`
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
-- Déchargement des données de la table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'auth_token', '589b24d50cdeff3b7e91d6a6b9ff5affb82d28452c0ce738ab79c55e276e5a5e', '[\"*\"]', NULL, NULL, '2024-11-26 13:53:08', '2024-11-26 13:53:08'),
(2, 'App\\Models\\User', 1, 'auth_token', '4e3fa453b7ad4f45de9186e825dd3a1651a26292922c0d35417de7bc7113f41d', '[\"*\"]', NULL, NULL, '2024-11-26 14:00:07', '2024-11-26 14:00:07'),
(3, 'App\\Models\\User', 1, 'auth_token', '720f588132dbdfb71ceaa4a713d94b3d9b1f9c0b9e69d861e509b436443bb09f', '[\"*\"]', NULL, NULL, '2024-11-26 14:04:46', '2024-11-26 14:04:46'),
(4, 'App\\Models\\User', 1, 'auth_token', '681fe155d5cf0a30956e796e4f2c26e518788178bbd6abbccd94a093b69ba504', '[\"*\"]', NULL, NULL, '2024-11-26 14:23:15', '2024-11-26 14:23:15'),
(5, 'App\\Models\\User', 1, 'auth_token', 'a3f4671e2188c1624229d51fed7529725583ece1777e03259a5c47a92570e649', '[\"*\"]', NULL, NULL, '2024-11-26 14:23:42', '2024-11-26 14:23:42'),
(6, 'App\\Models\\User', 1, 'auth_token', 'd23026b2736c1d7be10f1d3b9cc30a0c18c3d2a980630242825fb9bc371a1012', '[\"*\"]', NULL, NULL, '2024-11-26 14:24:37', '2024-11-26 14:24:37'),
(7, 'App\\Models\\User', 1, 'auth_token', 'cacf2c6408bd39612c01b91864a1643739b0529ad6787d3014ed466b6b786951', '[\"*\"]', '2024-11-26 14:57:34', NULL, '2024-11-26 14:37:15', '2024-11-26 14:57:34'),
(9, 'App\\Models\\User', 1, 'auth_token', '5cdc30ca4af564a24b01f5a8fc21aeba786a435f0720a40dcdd7bd45923b8247', '[\"*\"]', '2024-11-26 15:08:44', NULL, '2024-11-26 15:04:21', '2024-11-26 15:08:44'),
(10, 'App\\Models\\User', 1, 'auth_token', '5916379e7878236391eeddb0c9964125116eb987dd7f9f657cd95fd378f6d6a5', '[\"*\"]', '2024-11-26 15:41:44', NULL, '2024-11-26 15:09:07', '2024-11-26 15:41:44'),
(11, 'App\\Models\\User', 1, 'auth_token', 'af939554521f56bc4863aea8c7f2638229e2e83d6109c635cf9ee4b1d903a281', '[\"*\"]', '2024-11-26 17:41:35', NULL, '2024-11-26 15:43:13', '2024-11-26 17:41:35'),
(12, 'App\\Models\\User', 13, 'auth_token', '14b1989ebe157a1ea93b767905b182fa08573d12fdaf7b0bbdcca65472a50915', '[\"*\"]', '2024-11-26 17:41:49', NULL, '2024-11-26 17:41:42', '2024-11-26 17:41:49'),
(13, 'App\\Models\\User', 1, 'auth_token', '86db0331c0c7aaa5007afcb1b541ad453c21ec9ee9dd2dd151bb0119365cab32', '[\"*\"]', '2024-11-27 07:29:02', NULL, '2024-11-26 17:42:10', '2024-11-27 07:29:02'),
(14, 'App\\Models\\User', 1, 'auth_token', '02fcae746da6cd924126296701e34e1cc782dc4cadc4fac259bc1222c0bcd123', '[\"*\"]', NULL, NULL, '2024-11-27 07:29:25', '2024-11-27 07:29:25'),
(15, 'App\\Models\\User', 1, 'auth_token', 'e5d40c1c198ac6d87525f66e6349274aa661ae9c2a4515879e533a413085f938', '[\"*\"]', '2024-11-27 08:08:50', NULL, '2024-11-27 07:29:45', '2024-11-27 08:08:50'),
(16, 'App\\Models\\User', 1, 'auth_token', '55a9456df9932740e3cbf6ef5b95b9ee9e80e72f1fdecaebed93cdc739ba0574', '[\"*\"]', NULL, NULL, '2024-11-27 08:04:32', '2024-11-27 08:04:32'),
(17, 'App\\Models\\User', 1, 'auth_token', '16f8a939332e55e147ef0e4eb2c0f1ce71adbea569054384627bb32afb5fab68', '[\"*\"]', '2024-11-27 08:49:10', NULL, '2024-11-27 08:09:29', '2024-11-27 08:49:10'),
(18, 'App\\Models\\User', 1, 'auth_token', 'c602897f4c04e4f7db12b84e6d791518cbbf998ff2c004f29387c3b6889061ba', '[\"*\"]', '2024-11-27 09:32:33', NULL, '2024-11-27 08:54:30', '2024-11-27 09:32:33'),
(19, 'App\\Models\\User', 1, 'auth_token', 'd41bc8f9c1448a266fa4b0ba1139126c8f8e9ba1fa3df434d00a48e45199568a', '[\"*\"]', '2024-11-27 10:15:00', NULL, '2024-11-27 09:47:13', '2024-11-27 10:15:00'),
(20, 'App\\Models\\User', 1, 'auth_token', 'a583cd5e96acde27959b599359658d3ddb1cbb3f07f0fcab055a9b30b63b8070', '[\"*\"]', '2024-11-27 10:15:42', NULL, '2024-11-27 10:15:25', '2024-11-27 10:15:42'),
(21, 'App\\Models\\User', 6, 'auth_token', '862a78d8483ba12fb9b925bc49c8e4f2a1d9e26d6833c73f06647ad2c0338342', '[\"*\"]', '2024-11-27 10:21:26', NULL, '2024-11-27 10:15:54', '2024-11-27 10:21:26'),
(22, 'App\\Models\\User', 1, 'auth_token', '328afeb95f8c32fe87fa37e2f65715d0c489b7aaeb2a130f5fd842eb8fcfd260', '[\"*\"]', '2024-11-27 10:22:56', NULL, '2024-11-27 10:22:38', '2024-11-27 10:22:56'),
(23, 'App\\Models\\User', 6, 'auth_token', 'b3e3bc962dc257d8cc57967431467b055e0b11c3359ae1e3f4a92e0ffff31a5d', '[\"*\"]', '2024-11-27 10:26:52', NULL, '2024-11-27 10:23:03', '2024-11-27 10:26:52'),
(24, 'App\\Models\\User', 6, 'auth_token', '4bb82010cc669e71071742a30bff9f0e9884f358e6f9a5be68d038b4c0ed2768', '[\"*\"]', '2024-11-27 10:27:33', NULL, '2024-11-27 10:27:06', '2024-11-27 10:27:33'),
(25, 'App\\Models\\User', 6, 'auth_token', '6a9d2ef4fd70c90f2878ab37a1d515f532fed5db57bf52d82b47f6549445e0e0', '[\"*\"]', '2024-11-27 10:29:29', NULL, '2024-11-27 10:28:02', '2024-11-27 10:29:29'),
(26, 'App\\Models\\User', 6, 'auth_token', '09581a270efedac997c5218b7f64862c4e416e81412bce0ff7cf89e4cd92d487', '[\"*\"]', '2024-11-27 10:31:56', NULL, '2024-11-27 10:31:54', '2024-11-27 10:31:56'),
(27, 'App\\Models\\User', 6, 'auth_token', 'b293f1f03d52500be8fe205a0c92f19924c463e791ae84c6672eda0fe01aff38', '[\"*\"]', '2024-11-27 10:36:59', NULL, '2024-11-27 10:36:58', '2024-11-27 10:36:59'),
(28, 'App\\Models\\User', 6, 'auth_token', 'c9dfcc2af4a526e3756e4b879275722be9ea82c9b5d9e95b37e0b4dbe23f1fff', '[\"*\"]', '2024-11-27 10:42:15', NULL, '2024-11-27 10:42:13', '2024-11-27 10:42:15'),
(29, 'App\\Models\\User', 1, 'auth_token', '573e83e4da9dc6cb78ba6bf0629212e9c7726348337f83c46fdc967c7ac8bfa0', '[\"*\"]', '2024-11-27 10:42:29', NULL, '2024-11-27 10:42:28', '2024-11-27 10:42:29'),
(30, 'App\\Models\\User', 6, 'auth_token', '420f8c0cfc872f006d063a97185adbc27dda262776e7316598dea8f08fea09b1', '[\"*\"]', '2024-11-27 10:45:37', NULL, '2024-11-27 10:42:39', '2024-11-27 10:45:37'),
(31, 'App\\Models\\User', 1, 'auth_token', '237f4d9580f3e3598a62b9fb3557e8ea508c0c35e0386985426ca078483c1de8', '[\"*\"]', '2024-11-27 10:45:58', NULL, '2024-11-27 10:45:57', '2024-11-27 10:45:58'),
(32, 'App\\Models\\User', 6, 'auth_token', '1146f4bd26124e8fef49cee75cbd141196aa95bccce2dc3edf8e01f59ab779c0', '[\"*\"]', '2024-11-27 10:47:21', NULL, '2024-11-27 10:46:23', '2024-11-27 10:47:21'),
(33, 'App\\Models\\User', 6, 'auth_token', 'f90b06f08f32dab8af1a2f6d24a3a6cb2f247ec29064df79d93f8c07c963ec7f', '[\"*\"]', '2024-11-27 11:17:36', NULL, '2024-11-27 11:17:32', '2024-11-27 11:17:36'),
(34, 'App\\Models\\User', 1, 'auth_token', 'f66f22d76f314a47aa73dc192f7969f56711550a84b2137ff2db076ab09bd403', '[\"*\"]', '2024-11-27 11:18:39', NULL, '2024-11-27 11:18:23', '2024-11-27 11:18:39'),
(35, 'App\\Models\\User', 6, 'auth_token', 'da4f151a24d62415192033cc441adf87d76b0eba3164b6150658ea4b2bc35ec3', '[\"*\"]', '2024-11-27 11:19:14', NULL, '2024-11-27 11:19:11', '2024-11-27 11:19:14'),
(36, 'App\\Models\\User', 1, 'auth_token', '40f72588c90d51943027b789a276cb4599932043b864854114c858b7c528faaf', '[\"*\"]', '2024-11-27 12:02:19', NULL, '2024-11-27 12:02:17', '2024-11-27 12:02:19'),
(37, 'App\\Models\\User', 6, 'auth_token', 'e4946658d132c61535376ec1297bf3424746cc7960e222316d526aba0736e004', '[\"*\"]', '2024-11-27 12:05:09', NULL, '2024-11-27 12:05:05', '2024-11-27 12:05:09'),
(38, 'App\\Models\\User', 1, 'auth_token', 'd1b0726063706baef6df1d604dd6007bee89c8e49e47f6fadb8573fd29f0aedc', '[\"*\"]', '2024-11-27 12:09:49', NULL, '2024-11-27 12:08:11', '2024-11-27 12:09:49'),
(39, 'App\\Models\\User', 1, 'auth_token', '9377a2f86f97919fda7c89cd115e4db1c2a37025c644810f2e49f896409bdd2c', '[\"*\"]', NULL, NULL, '2024-11-27 12:39:46', '2024-11-27 12:39:46'),
(40, 'App\\Models\\User', 6, 'auth_token', '97ed091ad0d45d6af42e0c1860db9fa810bc4d5c56ca6b555b6301b9cfc079b9', '[\"*\"]', NULL, NULL, '2024-11-27 12:40:16', '2024-11-27 12:40:16'),
(41, 'App\\Models\\User', 6, 'auth_token', 'bbbf6cb6e7e8f36ac60f96c5a8c680f082a710afa9521dcdb7413d86ce2c28e2', '[\"*\"]', NULL, NULL, '2024-11-27 12:41:03', '2024-11-27 12:41:03'),
(42, 'App\\Models\\User', 6, 'auth_token', '2c90a0b8997f926d9147f17601ec6799565f72a0d9a542846c4a1e252dcdbdf4', '[\"*\"]', '2024-11-27 12:42:18', NULL, '2024-11-27 12:42:16', '2024-11-27 12:42:18'),
(43, 'App\\Models\\User', 1, 'auth_token', '8843806212bd589a3a4e79fc70d425e03d5d7c74c28e34f6461c2bf01d8bd5d2', '[\"*\"]', '2024-11-27 13:16:42', NULL, '2024-11-27 12:43:55', '2024-11-27 13:16:42'),
(44, 'App\\Models\\User', 6, 'auth_token', '0af8ecff84bea00d96d8f266047a360303e31b245b54e6ae46cce96fa9795470', '[\"*\"]', '2024-11-27 13:18:00', NULL, '2024-11-27 13:16:53', '2024-11-27 13:18:00'),
(45, 'App\\Models\\User', 1, 'auth_token', 'ac169399c5827759b8cf3cf14696cf041b851b237d53b47a6cd4b6c3b4251797', '[\"*\"]', '2024-11-27 13:50:00', NULL, '2024-11-27 13:20:41', '2024-11-27 13:50:00'),
(46, 'App\\Models\\User', 1, 'auth_token', 'bbfbac2de7d6ecb13b40bfbf8ee2b5dcb106a6d8d27ab1cee709e5cd16937f9b', '[\"*\"]', '2024-11-27 13:53:06', NULL, '2024-11-27 13:53:01', '2024-11-27 13:53:06'),
(47, 'App\\Models\\User', 6, 'auth_token', '6a5671cfd1359bca307fdfa937716290a8154e49f40b3e27433e1406a894e638', '[\"*\"]', '2024-11-27 14:01:44', NULL, '2024-11-27 13:53:14', '2024-11-27 14:01:44'),
(48, 'App\\Models\\User', 1, 'auth_token', '1b784fc09c2f17ab8e7d63c11f30e39d9ed9a6d11828774fba891982897660df', '[\"*\"]', '2024-11-27 14:05:11', NULL, '2024-11-27 14:03:36', '2024-11-27 14:05:11'),
(49, 'App\\Models\\User', 19, 'auth_token', '60e5a5334117f43024bbafbbf6fa8239bfd2450e28b3b0a055e284ddbfa10809', '[\"*\"]', '2024-11-27 14:06:45', NULL, '2024-11-27 14:05:20', '2024-11-27 14:06:45'),
(50, 'App\\Models\\User', 20, 'auth_token', '7050f03dbba855dbf7fa1b7c97a42a8177e25f2f53ef07cf75a389b1f9fa5a7f', '[\"*\"]', '2024-11-27 14:08:33', NULL, '2024-11-27 14:07:25', '2024-11-27 14:08:33'),
(51, 'App\\Models\\User', 1, 'auth_token', 'd6d533586114cc395a558de2ed6df1c7ab5642cdd2308378a4dc940b12b1743f', '[\"*\"]', '2024-11-28 06:51:54', NULL, '2024-11-27 14:14:10', '2024-11-28 06:51:54'),
(52, 'App\\Models\\User', 6, 'auth_token', 'f46b8843247244305eea4ec501dc71b43721f35c394b3771834dcbcd0ded41db', '[\"*\"]', NULL, NULL, '2024-11-27 15:03:26', '2024-11-27 15:03:26'),
(53, 'App\\Models\\User', 1, 'auth_token', 'abf50ab13e39b47bab6ce127a5bac3ce4fbdcdc04322a7cdd8f65f6866acf6d8', '[\"*\"]', '2024-11-28 08:30:15', NULL, '2024-11-28 06:51:58', '2024-11-28 08:30:15'),
(54, 'App\\Models\\User', 1, 'auth_token', 'ec5942beffbe4cf4045ec1e715fa1d467c35afb0a80426deaf36dd64505c6a5b', '[\"*\"]', '2024-11-28 08:30:31', NULL, '2024-11-28 08:30:19', '2024-11-28 08:30:31'),
(55, 'App\\Models\\User', 1, 'auth_token', 'fdecc9a5ec4ab08eb69f97471d39066b5d384d4afb4cdf77856a91d07c5c4839', '[\"*\"]', '2024-11-28 08:31:06', NULL, '2024-11-28 08:30:50', '2024-11-28 08:31:06'),
(56, 'App\\Models\\User', 6, 'auth_token', '185314fe98cd378c9c995a6611251b43fe0e77876dbdbebfb376b84474fd7f09', '[\"*\"]', NULL, NULL, '2024-11-28 08:32:00', '2024-11-28 08:32:00'),
(57, 'App\\Models\\User', 1, 'auth_token', '58bc132987e729de1ab82c1542ba5a2583240a0726f510e5d3c2142a3210a856', '[\"*\"]', '2024-11-28 08:32:40', NULL, '2024-11-28 08:32:24', '2024-11-28 08:32:40'),
(58, 'App\\Models\\User', 1, 'auth_token', '79c3f53449b2f1dd9bf6c6fd862e1cd598b2750e5e8792a1e5017f0c8e4af75f', '[\"*\"]', '2024-11-28 08:39:30', NULL, '2024-11-28 08:39:12', '2024-11-28 08:39:30'),
(59, 'App\\Models\\User', 1, 'auth_token', '300b833f8071437b87b157091ee9a3ce879b8c07b96e14d8d949830d3909869c', '[\"*\"]', '2024-11-28 08:41:13', NULL, '2024-11-28 08:40:54', '2024-11-28 08:41:13'),
(60, 'App\\Models\\User', 1, 'auth_token', 'a2d92100f8cfad4e5da68c54bb535d53f71828dc3be8b83594286643535b9476', '[\"*\"]', '2024-11-28 08:43:32', NULL, '2024-11-28 08:42:55', '2024-11-28 08:43:32'),
(61, 'App\\Models\\User', 1, 'auth_token', '5684e8674b627db6925a012d9fc8a98a571208c3ae752c6cfc35c41cf60eb7a7', '[\"*\"]', '2024-11-28 08:57:03', NULL, '2024-11-28 08:54:09', '2024-11-28 08:57:03'),
(62, 'App\\Models\\User', 1, 'auth_token', 'd1918b9b32766736d3de86e74db6f8b0372d64f4428d070949fc5191962bf6de', '[\"*\"]', '2024-11-28 08:58:47', NULL, '2024-11-28 08:58:23', '2024-11-28 08:58:47'),
(63, 'App\\Models\\User', 1, 'auth_token', '7b72579fdb4894c749d656a7c3095782baa5c83dab2720cf1716ea0304baf9ce', '[\"*\"]', '2024-11-28 09:11:07', NULL, '2024-11-28 09:10:49', '2024-11-28 09:11:07'),
(64, 'App\\Models\\User', 1, 'auth_token', 'aea6623df9738604375bc71712b368610e602accc408fe18124d9852368416bc', '[\"*\"]', '2024-11-28 09:22:44', NULL, '2024-11-28 09:22:18', '2024-11-28 09:22:44'),
(65, 'App\\Models\\User', 1, 'auth_token', '8d6a9c293b95d4d21844c5d65ebb43286a3101f17190d800f0519446a8c2e513', '[\"*\"]', '2024-11-28 09:31:22', NULL, '2024-11-28 09:31:03', '2024-11-28 09:31:22'),
(66, 'App\\Models\\User', 1, 'auth_token', '79499163c82ff14500aa0820e73fa1f0bbd06234b96c5518705843a348bb2cfa', '[\"*\"]', '2024-11-28 09:34:39', NULL, '2024-11-28 09:34:05', '2024-11-28 09:34:39'),
(67, 'App\\Models\\User', 1, 'auth_token', '34f531ad674a727cbc721fa5189318bd298f2847dda3f6883fba5d73460e6173', '[\"*\"]', '2024-11-28 09:36:35', NULL, '2024-11-28 09:35:59', '2024-11-28 09:36:35'),
(68, 'App\\Models\\User', 1, 'auth_token', '18e9c074ef968eb421e9f1dfc8a8da245c6c333552e8ccbf1d9a9876b993d323', '[\"*\"]', '2024-11-28 09:39:17', NULL, '2024-11-28 09:38:45', '2024-11-28 09:39:17'),
(69, 'App\\Models\\User', 1, 'auth_token', '9bda9aeed6edc0ac2ef6f377934cc23c904b0465224a7f254daecedccd6fafff', '[\"*\"]', '2024-11-28 09:40:23', NULL, '2024-11-28 09:39:58', '2024-11-28 09:40:23'),
(70, 'App\\Models\\User', 1, 'auth_token', '178181f6aa8386a33409153eb78d37135201bcf2e0fcff03c86b8048f27f02c0', '[\"*\"]', '2024-11-28 09:41:53', NULL, '2024-11-28 09:41:30', '2024-11-28 09:41:53'),
(71, 'App\\Models\\User', 1, 'auth_token', '7e0668704d649d11d0a814d4b79e3b8fe95b6218abeabb02ceb87bc8c83d8152', '[\"*\"]', '2024-11-28 11:33:52', NULL, '2024-11-28 11:33:29', '2024-11-28 11:33:52'),
(72, 'App\\Models\\User', 1, 'auth_token', '7e4be8191a383bfc891fc277228a28508ef6c9a7aefa4c4cd2b75269ccdfe035', '[\"*\"]', '2024-11-28 11:37:07', NULL, '2024-11-28 11:36:35', '2024-11-28 11:37:07'),
(73, 'App\\Models\\User', 1, 'auth_token', 'cb5c1546c09c197a140e9284867fc415470541a709e3f578ed6ea64d89041498', '[\"*\"]', '2024-11-28 11:40:10', NULL, '2024-11-28 11:37:45', '2024-11-28 11:40:10'),
(74, 'App\\Models\\User', 1, 'auth_token', '67075548adb49be4de098606a6b7d9d3e2337f99d097417d36ca2c656380365c', '[\"*\"]', '2024-11-28 11:44:11', NULL, '2024-11-28 11:42:25', '2024-11-28 11:44:11'),
(75, 'App\\Models\\User', 1, 'auth_token', '14d0bbd88d5e097057c4396087475009fa167a0d2fb37cdd6dd73ee93a6ebf5d', '[\"*\"]', '2024-11-28 12:08:47', NULL, '2024-11-28 11:46:22', '2024-11-28 12:08:47'),
(76, 'App\\Models\\User', 1, 'auth_token', '3dc4f2868356973062c83cc4d486215fc2e1638ae968b69889d5f36c0307f41d', '[\"*\"]', '2024-11-28 12:14:49', NULL, '2024-11-28 12:08:50', '2024-11-28 12:14:49'),
(77, 'App\\Models\\User', 1, 'auth_token', 'd811edf09b8da3f5b130e9cfb0c4e80e90ec9235eaa5a033d294dc3572b0789d', '[\"*\"]', '2024-11-28 14:06:01', NULL, '2024-11-28 12:20:51', '2024-11-28 14:06:01'),
(78, 'App\\Models\\User', 1, 'auth_token', 'bf201d199ca2feb4d7b95954a6a41041a65c959295985be425b0722b1319f94a', '[\"*\"]', NULL, NULL, '2024-11-28 14:07:51', '2024-11-28 14:07:51'),
(79, 'App\\Models\\User', 1, 'auth_token', '2e84f7a92026d36ca524e696e7b3396be7917a1fab82935fabf032d9b2c1cea2', '[\"*\"]', NULL, NULL, '2024-11-28 14:14:16', '2024-11-28 14:14:16'),
(80, 'App\\Models\\User', 1, 'auth_token', '4f217ec2e1b6dc6867f72da583068a8c3cf16e0bb79078dc85d570152cba5902', '[\"*\"]', NULL, NULL, '2024-11-28 14:15:25', '2024-11-28 14:15:25'),
(81, 'App\\Models\\User', 1, 'auth_token', '4e05f5855f7a9a48c6750630a7b4a89f715eaca52e2c6518a8101841d90da76e', '[\"*\"]', NULL, NULL, '2024-11-28 14:20:07', '2024-11-28 14:20:07'),
(82, 'App\\Models\\User', 1, 'auth_token', 'f2a82e37f784ba92145df859011032c6d630acf8e2caa1a8234b2810973ea076', '[\"*\"]', NULL, NULL, '2024-11-28 14:22:41', '2024-11-28 14:22:41'),
(83, 'App\\Models\\User', 1, 'auth_token', '1809c1dfefe3a8828b8cf946d65d6689b372a876a61dc707b2fb17b3ff6906bc', '[\"*\"]', NULL, NULL, '2024-11-28 14:26:58', '2024-11-28 14:26:58'),
(84, 'App\\Models\\User', 1, 'auth_token', '9a5ff2179f091ce06d20b2c0deb4b39beb324c3983a3cb68b80df93577a01d60', '[\"*\"]', NULL, NULL, '2024-11-28 14:27:21', '2024-11-28 14:27:21'),
(85, 'App\\Models\\User', 1, 'auth_token', '9a98ad9322e6db7fde4fffab0c4d4326ac4a668af5b7cce6276db4df6a21af07', '[\"*\"]', NULL, NULL, '2024-11-28 14:29:55', '2024-11-28 14:29:55'),
(86, 'App\\Models\\User', 1, 'auth_token', '87f73b253f6f6e1e0eddf676298f720c90d7d44ccca28896f80be03281e91a6c', '[\"*\"]', NULL, NULL, '2024-11-28 14:30:08', '2024-11-28 14:30:08'),
(87, 'App\\Models\\User', 1, 'auth_token', '9fc24bcecc7bff3fd294076aa3a8b102384e7a89c8bac6ebf88a95778cacec4f', '[\"*\"]', NULL, NULL, '2024-11-28 14:31:13', '2024-11-28 14:31:13'),
(88, 'App\\Models\\User', 1, 'auth_token', '230cf9e039e19a34d89410aeff4db385b55e69ccaabccf5ffe734f0bea91c022', '[\"*\"]', '2024-11-28 14:33:12', NULL, '2024-11-28 14:33:05', '2024-11-28 14:33:12'),
(89, 'App\\Models\\User', 1, 'auth_token', '41ba6c2c95b9ca003cc1a7949d727e0f7a69faf1a0207d3b0bf3e4973a46f742', '[\"*\"]', NULL, NULL, '2024-11-28 14:34:12', '2024-11-28 14:34:12'),
(90, 'App\\Models\\User', 1, 'auth_token', 'b47c26f6d684da4d83cb2ef0ca2c88f02658657b78fe51f9b3468525225f2a5c', '[\"*\"]', NULL, NULL, '2024-11-28 14:34:59', '2024-11-28 14:34:59'),
(91, 'App\\Models\\User', 1, 'auth_token', 'ad7971d2d0b33257231ec6c123088921a9fe62d0491ba6692750626548feb269', '[\"*\"]', NULL, NULL, '2024-11-28 14:35:58', '2024-11-28 14:35:58'),
(92, 'App\\Models\\User', 1, 'auth_token', '2659c4d095bc5004fc134fe9029a99fb533d43e82282e4a10ab97079c6f7fa13', '[\"*\"]', NULL, NULL, '2024-11-28 14:36:31', '2024-11-28 14:36:31'),
(93, 'App\\Models\\User', 1, 'auth_token', '8cf4028f2587beca4aeec129db116ca50199f17bc798a2f5f936c6ecdc95bc67', '[\"*\"]', NULL, NULL, '2024-11-28 14:41:17', '2024-11-28 14:41:17'),
(94, 'App\\Models\\User', 1, 'auth_token', '709991113ccfaae313f9d21e3423985934a4b242e773cf77f7473181a0661395', '[\"*\"]', NULL, NULL, '2024-11-28 14:43:20', '2024-11-28 14:43:20'),
(95, 'App\\Models\\User', 6, 'auth_token', '95e8312225b5e1fe4f9c8f0077dc179656d0f7f067fe6a15de98ddf7d13cd25f', '[\"*\"]', NULL, NULL, '2024-11-28 14:44:01', '2024-11-28 14:44:01'),
(96, 'App\\Models\\User', 1, 'auth_token', '4eab30a383afef873a7ffd60a35995bbe7295177fc230ca2134286bb2e8eba4b', '[\"*\"]', '2024-11-28 14:47:16', NULL, '2024-11-28 14:47:07', '2024-11-28 14:47:16'),
(97, 'App\\Models\\User', 1, 'auth_token', '1ed564d259160f86bda30ef1a2070b69183d40c5497c5958797e2a305678f662', '[\"*\"]', NULL, NULL, '2024-11-28 14:49:53', '2024-11-28 14:49:53'),
(98, 'App\\Models\\User', 1, 'auth_token', 'f475bbe031b216ef3e70be53eb76e9407aa663cfd12d4743cbbd8f885625cf79', '[\"*\"]', NULL, NULL, '2024-11-28 14:53:17', '2024-11-28 14:53:17'),
(99, 'App\\Models\\User', 1, 'auth_token', 'bb4b9d74f72ccdd6694e9842a1aafeb3e26a999040d38696a47249955dd50d42', '[\"*\"]', NULL, NULL, '2024-11-28 14:55:32', '2024-11-28 14:55:32'),
(100, 'App\\Models\\User', 1, 'auth_token', '7bbc769f905b80a654aff6644acddab9814333fb2ca0a3fd97f58a94db973e59', '[\"*\"]', NULL, NULL, '2024-11-28 14:56:00', '2024-11-28 14:56:00'),
(101, 'App\\Models\\User', 1, 'auth_token', '1d922e8e87e6d42191bb95ca0c70722e559683975e5f76ce11cf321c194b87c5', '[\"*\"]', NULL, NULL, '2024-11-28 14:56:34', '2024-11-28 14:56:34'),
(102, 'App\\Models\\User', 1, 'auth_token', '5b304ac515a6870bcd25c61cb057e31857f67b4726965434f2b177ede8447451', '[\"*\"]', NULL, NULL, '2024-11-28 14:58:07', '2024-11-28 14:58:07'),
(103, 'App\\Models\\User', 1, 'auth_token', 'ea16a7773dc42b65bed859745babc3882b1e944eb762cb1a1138bdffc82e3537', '[\"*\"]', NULL, NULL, '2024-11-28 15:01:37', '2024-11-28 15:01:37'),
(104, 'App\\Models\\User', 1, 'auth_token', '7713b02f06332fc572ab2a97c8856d19b2367de636b53262c1610fd0b73fe7a4', '[\"*\"]', NULL, NULL, '2024-11-28 15:02:31', '2024-11-28 15:02:31'),
(105, 'App\\Models\\User', 6, 'auth_token', '565db24cd4c61a469b56f017c270b521ce804ebf2461756c23c7f43452e540c1', '[\"*\"]', NULL, NULL, '2024-11-28 15:05:47', '2024-11-28 15:05:47'),
(106, 'App\\Models\\User', 1, 'auth_token', 'b2a5106488a1319d9a40c43655673f5bbff412da2568e45f96dcf7c4edcb93de', '[\"*\"]', NULL, NULL, '2024-11-28 15:07:48', '2024-11-28 15:07:48'),
(107, 'App\\Models\\User', 1, 'auth_token', 'a70a32ed2df8a8742b126d478fcec179cf6bb78f9d43b5ef67b3852e2aedf791', '[\"*\"]', NULL, NULL, '2024-11-28 15:08:38', '2024-11-28 15:08:38'),
(108, 'App\\Models\\User', 1, 'auth_token', '5e04be39ac991ea3c350afa81f6cf0ed94c67c2275a44d183d8bd51f5fab7a43', '[\"*\"]', NULL, NULL, '2024-11-28 15:10:57', '2024-11-28 15:10:57'),
(109, 'App\\Models\\User', 1, 'auth_token', 'd1f9acf83f00a183dc3bb7e8315c6721d5e67a38f66081f1637f004ad17fdc98', '[\"*\"]', NULL, NULL, '2024-11-28 15:18:50', '2024-11-28 15:18:50'),
(110, 'App\\Models\\User', 1, 'auth_token', 'c642d95edb939df89fd3bfa9a50aa8aba9e95592b9364d05872e7eb1d632d72e', '[\"*\"]', NULL, NULL, '2024-11-28 15:19:12', '2024-11-28 15:19:12'),
(111, 'App\\Models\\User', 1, 'auth_token', '8427c1486fd0487c3ab102e4ec8ab12068786d6897c385357ee79ab387aa6e6b', '[\"*\"]', NULL, NULL, '2024-11-28 15:21:32', '2024-11-28 15:21:32'),
(112, 'App\\Models\\User', 1, 'auth_token', 'c7fe52d4911b08c63ae071ef0fd34923c4772ffb6b8db9e47045bb8f78997b87', '[\"*\"]', NULL, NULL, '2024-11-28 15:29:30', '2024-11-28 15:29:30'),
(113, 'App\\Models\\User', 1, 'auth_token', 'cb468ba8ef1963ebe85d0d5fa950f2a007cec86f499a856fd3b9c53b8dbf7472', '[\"*\"]', '2024-11-28 15:32:25', NULL, '2024-11-28 15:31:45', '2024-11-28 15:32:25'),
(114, 'App\\Models\\User', 1, 'auth_token', '169bb658a3c75a33bcc86df9ea2fa2e82715be3c773a317456e46304859bacb0', '[\"*\"]', '2024-11-28 15:32:49', NULL, '2024-11-28 15:32:39', '2024-11-28 15:32:49'),
(115, 'App\\Models\\User', 1, 'auth_token', '324b7a26cd73beb4569e581c30f50cb95a4ea6c11f00fee27dd72b0f4231c977', '[\"*\"]', '2024-11-28 15:33:42', NULL, '2024-11-28 15:33:34', '2024-11-28 15:33:42'),
(116, 'App\\Models\\User', 1, 'auth_token', '74cd415589d406703061f6aecd8d184c1e3aadd380b8a1bcc40ce17c77efc24d', '[\"*\"]', NULL, NULL, '2024-11-28 15:35:47', '2024-11-28 15:35:47'),
(117, 'App\\Models\\User', 1, 'auth_token', 'bd851bd4a4887bff5aaccde0c6b44a7f757c59054ce594f75fec47331285d685', '[\"*\"]', NULL, NULL, '2024-11-28 15:36:23', '2024-11-28 15:36:23'),
(118, 'App\\Models\\User', 1, 'auth_token', '79e2e008121c4f003d9d7d7340c23be96f58f0e07dcd9a83af1148c8b2229db5', '[\"*\"]', NULL, NULL, '2024-11-28 15:37:18', '2024-11-28 15:37:18'),
(119, 'App\\Models\\User', 1, 'auth_token', 'da8fef9a59504d27c3461f37ab1b1101b28e7dd9b9c4f79675279736f67b1c94', '[\"*\"]', NULL, NULL, '2024-11-28 15:37:44', '2024-11-28 15:37:44'),
(120, 'App\\Models\\User', 1, 'auth_token', '77f8653c572f7bef83a04c574020e85b670ae482a6342403d4294112f9f06530', '[\"*\"]', NULL, NULL, '2024-11-28 15:38:35', '2024-11-28 15:38:35'),
(121, 'App\\Models\\User', 1, 'auth_token', '4fb5fde8de8939b734c8fb14eda4e4e7c0ff7465b937aea542a2d7cf96bd52f5', '[\"*\"]', NULL, NULL, '2024-11-28 15:39:32', '2024-11-28 15:39:32'),
(122, 'App\\Models\\User', 1, 'auth_token', '1001c9b0a3ef97ce5996f3cbaeeb768cab3e19cece11375b5a76d3d42817830a', '[\"*\"]', NULL, NULL, '2024-11-28 15:41:08', '2024-11-28 15:41:08'),
(123, 'App\\Models\\User', 1, 'auth_token', 'dcafa9cbb904c51106be4fb255348722e7c1005ce5f1237f1c89f4830f93563c', '[\"*\"]', NULL, NULL, '2024-11-28 15:42:47', '2024-11-28 15:42:47'),
(124, 'App\\Models\\User', 1, 'auth_token', '6e5ec461d50b191d40e824ea1e210b59444fea3c5c093d78651d139a6b84d48a', '[\"*\"]', '2024-11-28 15:43:03', NULL, '2024-11-28 15:42:58', '2024-11-28 15:43:03'),
(125, 'App\\Models\\User', 6, 'auth_token', 'e7e34488988c04a215555ae394ba32d4d09786c34397d6e212d148fb9ec0c484', '[\"*\"]', '2024-11-28 16:14:20', NULL, '2024-11-28 15:43:17', '2024-11-28 16:14:20'),
(126, 'App\\Models\\User', 19, 'auth_token', 'c15e414a12bf7f3799e8c8d167768f4aa489902d2fb9af2f4cd970d9e428c40c', '[\"*\"]', '2024-11-28 16:16:18', NULL, '2024-11-28 16:15:53', '2024-11-28 16:16:18'),
(127, 'App\\Models\\User', 1, 'auth_token', '9847413e63a752f8a685df5d16d1265b03efd5f460da7cebfed935b226dcaea2', '[\"*\"]', NULL, NULL, '2024-11-28 16:16:31', '2024-11-28 16:16:31'),
(128, 'App\\Models\\User', 6, 'auth_token', 'fd0fc64483f5cf8e502c0e29a235afaacd328f5a3acffbf45d39e22d383764ae', '[\"*\"]', '2024-11-28 16:29:54', NULL, '2024-11-28 16:16:44', '2024-11-28 16:29:54'),
(129, 'App\\Models\\User', 6, 'auth_token', 'ae62c8c9c5a0a0843a4db4bf2ec9ac1b22a2ded97bd2097d5dadc30eeb2c42c1', '[\"*\"]', '2024-11-28 16:30:34', NULL, '2024-11-28 16:30:01', '2024-11-28 16:30:34'),
(130, 'App\\Models\\User', 6, 'auth_token', 'cc6d6cf499d78d7c8df37f715176d39ecc57212ed2d70c336fbd55aab80263b3', '[\"*\"]', '2024-11-28 16:35:15', NULL, '2024-11-28 16:30:46', '2024-11-28 16:35:15'),
(131, 'App\\Models\\User', 6, 'auth_token', '9302db63558c9e065ba697a8d232192506af34e5e18dd6c9278b5458a0fe07b3', '[\"*\"]', '2024-11-28 16:37:07', NULL, '2024-11-28 16:35:20', '2024-11-28 16:37:07'),
(132, 'App\\Models\\User', 6, 'auth_token', '69a0202d59764efecdf7b2d10632a475124ab085602ef1555ab441487cbdcfb0', '[\"*\"]', NULL, NULL, '2024-11-28 16:37:28', '2024-11-28 16:37:28'),
(133, 'App\\Models\\User', 6, 'auth_token', 'c5dd06e168168791b079874fcf7a3801dbfe010da7585a06fc1bc2eabf002ff0', '[\"*\"]', '2024-11-28 16:41:17', NULL, '2024-11-28 16:38:45', '2024-11-28 16:41:17'),
(134, 'App\\Models\\User', 6, 'auth_token', '608a424c2a5be206da8ee2a237ae976d07b2949bf3af43bfb740889506f93e17', '[\"*\"]', '2024-11-28 16:42:02', NULL, '2024-11-28 16:41:25', '2024-11-28 16:42:02'),
(135, 'App\\Models\\User', 6, 'auth_token', 'c23e715d35d0756bbc576496efc75bbf50682a6c751ed0f8d2b6d6a9eeb6c0bc', '[\"*\"]', '2024-11-28 16:42:11', NULL, '2024-11-28 16:42:08', '2024-11-28 16:42:11'),
(136, 'App\\Models\\User', 6, 'auth_token', 'e4da5be724a6d1b2c11bfe6a86a6cdb4d5ca4a0fb2da6e55052d3639855bf5e0', '[\"*\"]', '2024-11-28 16:42:27', NULL, '2024-11-28 16:42:24', '2024-11-28 16:42:27'),
(137, 'App\\Models\\User', 6, 'auth_token', '7bf1e8fb21f9525548cae6ee1a940fe06654b0596b990f51da192584cb26c4c0', '[\"*\"]', '2024-11-28 16:42:36', NULL, '2024-11-28 16:42:33', '2024-11-28 16:42:36'),
(138, 'App\\Models\\User', 1, 'auth_token', '018b754f55e04b2406106cab7ae118f98db44bd3e5342f63945a80073c0aee07', '[\"*\"]', '2024-11-28 16:42:46', NULL, '2024-11-28 16:42:42', '2024-11-28 16:42:46'),
(139, 'App\\Models\\User', 19, 'auth_token', 'ce9f563753a3d97a1a7763bc8caf9c620aa35ec9cb1a20e7486452067516d424', '[\"*\"]', '2024-11-28 16:43:01', NULL, '2024-11-28 16:42:58', '2024-11-28 16:43:01'),
(140, 'App\\Models\\User', 1, 'auth_token', '664bb324639321d478db7812f29e3a8a29cc3f45734589d0665471a5a6bac4b0', '[\"*\"]', '2024-11-28 16:46:33', NULL, '2024-11-28 16:46:07', '2024-11-28 16:46:33'),
(141, 'App\\Models\\User', 1, 'auth_token', '02b38eebbc6b919e14d3dbda9862841f9ed76e9c38be8aebdb713d1f0462f183', '[\"*\"]', '2024-11-28 16:46:47', NULL, '2024-11-28 16:46:43', '2024-11-28 16:46:47'),
(142, 'App\\Models\\User', 19, 'auth_token', '1cec9c335a655d7e69f271c592411ff6cf4c6f1ca155cda8e8956da884807fd9', '[\"*\"]', '2024-11-28 16:46:56', NULL, '2024-11-28 16:46:54', '2024-11-28 16:46:56'),
(143, 'App\\Models\\User', 6, 'auth_token', 'b901b61e2b857d9e082920f10ae82eb75586a30229d0596a3feb9e30db5f3a6f', '[\"*\"]', '2024-11-28 16:47:08', NULL, '2024-11-28 16:47:06', '2024-11-28 16:47:08'),
(144, 'App\\Models\\User', 6, 'auth_token', '108dbcfada526f1c9b92ddb6d46ad3868ff8bac3836f298dc1c56379d095493c', '[\"*\"]', '2024-11-28 18:54:18', NULL, '2024-11-28 18:54:15', '2024-11-28 18:54:18'),
(145, 'App\\Models\\User', 1, 'auth_token', '10dd68bdcaf216689501b7d20aaba7db1e848ef1a4f8b64e2dd342be9b85cda4', '[\"*\"]', NULL, NULL, '2024-11-28 18:56:27', '2024-11-28 18:56:27'),
(146, 'App\\Models\\User', 1, 'auth_token', 'c98655d8a73ebff75703e38ad7eea8c902e067c62e6bf643f5e4814d6165dbd5', '[\"*\"]', '2024-11-29 08:40:34', NULL, '2024-11-29 07:46:59', '2024-11-29 08:40:34'),
(147, 'App\\Models\\User', 6, 'auth_token', 'bb4722243aa9d96f9047cdc3d4b5aa4cac21175d254ab0af288d3b1737832763', '[\"*\"]', NULL, NULL, '2024-11-29 07:55:28', '2024-11-29 07:55:28'),
(148, 'App\\Models\\User', 6, 'auth_token', '39fcdd17ec921e39010f395dbf300534100821694f47f89e932969d7b8cd0543', '[\"*\"]', '2024-11-29 08:11:41', NULL, '2024-11-29 08:11:39', '2024-11-29 08:11:41'),
(149, 'App\\Models\\User', 1, 'auth_token', '8f45ace3f8de894c9fbe4f0c91f7d65bf92a2012c6394c84fc093213e55a8eb3', '[\"*\"]', '2024-11-29 12:12:44', NULL, '2024-11-29 08:49:45', '2024-11-29 12:12:44'),
(150, 'App\\Models\\User', 6, 'auth_token', 'e4b7fbb57bcbdbbccf3c80e3c8a8b6a7aa4d247c5f1bc6a1bb3aefb0aea8d190', '[\"*\"]', NULL, NULL, '2024-11-29 11:57:48', '2024-11-29 11:57:48'),
(151, 'App\\Models\\User', 6, 'auth_token', 'cc9436097f39b8b6a3db50a5bd5ca7075b8d94739869e08324b0b6d25407d663', '[\"*\"]', '2024-11-29 11:59:37', NULL, '2024-11-29 11:59:07', '2024-11-29 11:59:37'),
(152, 'App\\Models\\User', 1, 'auth_token', '48a683bb7fde92a48aa215fc8c597270a75d22eb0e884669bd3d282d7c9a220c', '[\"*\"]', '2024-11-29 13:11:05', NULL, '2024-11-29 12:54:23', '2024-11-29 13:11:05'),
(153, 'App\\Models\\User', 1, 'auth_token', '19896b3e719fa79f0350f9bd8a4e7423aac6e52609d466cf697428431e1599ac', '[\"*\"]', '2024-11-29 14:29:15', NULL, '2024-11-29 13:48:24', '2024-11-29 14:29:15'),
(154, 'App\\Models\\User', 6, 'auth_token', '770d3df54ed7de8d3329d8fbd839911063b6c9f5e7a8c0ccf2a68c44c5beeb96', '[\"*\"]', '2024-11-29 14:29:55', NULL, '2024-11-29 14:16:40', '2024-11-29 14:29:55'),
(155, 'App\\Models\\User', 1, 'auth_token', 'e51a3d6143fd05624c0f39dc346fd4a812fd2d51934537b720777de294ae4b9d', '[\"*\"]', '2024-11-29 15:24:08', NULL, '2024-11-29 14:30:38', '2024-11-29 15:24:08'),
(156, 'App\\Models\\User', 1, 'auth_token', 'f8ce6057cd4af068a5d89c384461377978597983a4c9cee819e3ebf19f8127a2', '[\"*\"]', '2024-11-29 15:18:30', NULL, '2024-11-29 14:30:43', '2024-11-29 15:18:30'),
(157, 'App\\Models\\User', 1, 'auth_token', 'f95e78c0d1e87dff024b1e5ebeee93c00a9f3a377d1c5c23d7fb097b54a7c202', '[\"*\"]', '2024-12-01 09:18:16', NULL, '2024-11-29 15:24:14', '2024-12-01 09:18:16'),
(158, 'App\\Models\\User', 1, 'auth_token', '6465ef2a0c60c395f83b2b47741091d8a30e28e75eecd87609d8c62d36372ee9', '[\"*\"]', '2024-12-01 09:45:04', NULL, '2024-12-01 09:18:21', '2024-12-01 09:45:04'),
(159, 'App\\Models\\User', 1, 'auth_token', '9ece5bb6004c4afd68321b240186347b058a9d97e1c1e39055d1945da9e1d077', '[\"*\"]', '2024-12-01 10:04:14', NULL, '2024-12-01 09:45:10', '2024-12-01 10:04:14'),
(160, 'App\\Models\\User', 1, 'auth_token', '73732347ccfc0a859c57ee2462d46091c66709d461e5fb27ef5423354018c067', '[\"*\"]', NULL, NULL, '2024-12-01 10:36:40', '2024-12-01 10:36:40'),
(161, 'App\\Models\\User', 1, 'auth_token', '43f64e7ecc727483470ecf8b15ad49ab21a2c1432f40381a3c42e16787896c3d', '[\"*\"]', '2024-12-01 11:04:48', NULL, '2024-12-01 10:37:21', '2024-12-01 11:04:48'),
(162, 'App\\Models\\User', 1, 'auth_token', '5d7daf4f689a6b126433b9fd0076c823e7ee1fbb9cba92f07bf4a69389bb6cb1', '[\"*\"]', '2024-12-01 11:27:57', NULL, '2024-12-01 11:11:07', '2024-12-01 11:27:57'),
(163, 'App\\Models\\User', 6, 'auth_token', '33c97ab56bc348f9070d48338c372d9ddfbeef5fd0e1056210a8703c31b95dcd', '[\"*\"]', '2024-12-01 11:28:24', NULL, '2024-12-01 11:28:21', '2024-12-01 11:28:24'),
(164, 'App\\Models\\User', 1, 'auth_token', '65c616143146b6a11d3f442a13f54b781d586750ff2065907ab87d07630c0142', '[\"*\"]', '2024-12-01 16:05:46', NULL, '2024-12-01 11:28:56', '2024-12-01 16:05:46'),
(165, 'App\\Models\\User', 6, 'auth_token', '09fcd28f2d89ab7f6f493f58048b1a419d5c7ee3b1868e0f3a66c2623008e6bf', '[\"*\"]', '2024-12-01 16:06:03', NULL, '2024-12-01 16:06:01', '2024-12-01 16:06:03'),
(166, 'App\\Models\\User', 1, 'auth_token', '4942017fd846ed4c03a88306a5f2e920a04fa8aefad6eaeeebe41d7c7cac7f77', '[\"*\"]', '2024-12-03 15:22:47', NULL, '2024-12-01 16:06:27', '2024-12-03 15:22:47'),
(167, 'App\\Models\\User', 1, 'auth_token', '5b87c373fdd60a6e06da813c1c45dad2166ea6dda2877fc8b16b5504c51338cf', '[\"*\"]', '2024-12-03 16:43:44', NULL, '2024-12-03 15:24:26', '2024-12-03 16:43:44'),
(168, 'App\\Models\\User', 1, 'auth_token', 'a7297a360dfdc4201d637793761000a6b10c269f64e11aaaa3233baaf512e5e5', '[\"*\"]', '2024-12-03 16:47:12', NULL, '2024-12-03 16:43:53', '2024-12-03 16:47:12'),
(169, 'App\\Models\\User', 1, 'auth_token', '79e4f3a23e59864c465e8097b4eb85a74d6f4577e89238a48c787df8378b69ab', '[\"*\"]', NULL, NULL, '2024-12-03 17:49:44', '2024-12-03 17:49:44'),
(170, 'App\\Models\\User', 1, 'auth_token', '5c470330ea364ea2ea4231cffd393e268127d5ea0e864cc31cdf3c869e1351a4', '[\"*\"]', '2024-12-16 09:17:10', NULL, '2024-12-06 12:38:57', '2024-12-16 09:17:10'),
(171, 'App\\Models\\User', 1, 'auth_token', 'c295e9bc6679b006b07ba138a6623ca11b86bc1179b6c19d3a995c9e93142ae7', '[\"*\"]', '2024-12-16 12:28:05', NULL, '2024-12-16 12:24:05', '2024-12-16 12:28:05');

-- --------------------------------------------------------

--
-- Structure de la table `replies`
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

--
-- Déchargement des données de la table `replies`
--

INSERT INTO `replies` (`id`, `content`, `comment_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'xcbcxb', 1, 1, '2024-11-27 08:46:36', '2024-11-27 08:46:36', NULL),
(2, 'svddgfb', 2, 1, '2024-11-27 08:47:41', '2024-11-27 08:47:41', NULL),
(3, 'et sa reponse', 3, 1, '2024-11-27 08:50:41', '2024-11-27 08:50:41', NULL),
(4, 'reponse', 4, 1, '2024-11-27 08:55:50', '2024-11-27 08:55:50', NULL),
(5, 'xcv', 5, 1, '2024-11-27 09:58:35', '2024-11-27 09:58:35', NULL),
(6, 'wcxwccx', 6, 1, '2024-11-27 10:12:51', '2024-11-27 10:12:51', NULL),
(7, 'test de reponse', 7, 1, '2024-11-27 10:46:13', '2024-11-27 10:46:13', NULL),
(8, 'QSDFGBNAZSEDRFGHN', 8, 1, '2024-11-27 11:18:53', '2024-11-27 11:18:53', NULL),
(9, 'xcvb', 9, 20, '2024-11-27 14:08:48', '2024-11-27 14:08:48', NULL),
(10, 'sdfdf', 10, 1, '2024-11-28 11:47:09', '2024-11-28 11:47:09', NULL),
(11, 'xdvxbf', 11, 6, '2024-11-28 16:15:27', '2024-11-28 16:15:27', NULL),
(12, 'qswdxcv', 12, 6, '2024-11-28 16:51:52', '2024-11-28 16:51:52', NULL),
(13, 'test reponse today', 13, 1, '2024-11-29 13:01:27', '2024-11-29 13:01:27', NULL),
(14, 'wxv', 14, 1, '2024-12-01 09:45:42', '2024-12-01 09:45:42', NULL),
(15, 'fvgdfgv', 15, 1, '2024-12-01 09:59:26', '2024-12-01 09:59:26', NULL),
(16, 'dxfcvb', 17, 1, '2024-12-01 16:12:18', '2024-12-01 16:12:18', NULL),
(17, 'merci', 19, 1, '2024-12-16 12:25:01', '2024-12-16 12:25:01', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `client_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `client_id`) VALUES
(1, 'Admin User', 'admin@example.com', NULL, '$2y$12$ztcYHNmf48nBQK2YI7.4lOE9q3cAcbB3xhM36WY0aPFp0WNBXgGmi', NULL, '2024-11-26 13:52:42', '2024-11-26 13:52:42', 'admin', NULL),
(2, 'Ulises Lubowitz IIIcc', 'juanita32@example.org', '2024-11-26 15:00:52', '$2y$12$Qam6vI3B.XQP.1ZnayeKXefHxHHccLytmj3XxLfa//8FH9JGrP6dm', 'q4i962H4JH', '2024-11-26 15:00:53', '2024-12-01 09:46:13', 'client', NULL),
(4, 'Manuel Graham', 'nestor.price@example.net', '2024-11-26 15:00:53', '$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy', 'LFfwzZX2Ot', '2024-11-26 15:00:53', '2024-11-26 16:09:46', 'formation', NULL),
(5, 'Prof. Estelle Kirlin I', 'sherwood.bergnaum@example.com', '2024-11-26 15:00:53', '$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy', 'ZcMXVzsma1', '2024-11-26 15:00:53', '2024-11-26 15:32:59', 'formation', NULL),
(6, 'Ardella Reilly IV', 'kreiger.mariah@example.net', '2024-11-26 15:00:53', '$2y$12$YyotAt3SVFEjnPH6uX6iJeoqTxlQFDOquNPlfu2u6G6h/r2aw2vsa', '8HeeApkMZB', '2024-11-26 15:00:53', '2024-11-27 10:15:41', 'client', 1),
(7, 'Eddie Beahannnnn', 'fletcher62@example.com', '2024-11-26 15:00:53', '$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy', '0aTzn5Uz4m', '2024-11-26 15:00:53', '2024-11-27 14:00:23', 'client', 1),
(8, 'Mr. Eleazar Block Sr.', 'brandi62@example.com', '2024-11-26 15:00:53', '$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy', 'q2GsUcpSpo', '2024-11-26 15:00:53', '2024-11-26 15:00:53', 'client', NULL),
(9, 'Sophia Lakin Sr.', 'zbeer@example.org', '2024-11-26 15:00:53', '$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy', '0i0I4Xqs0p', '2024-11-26 15:00:53', '2024-11-26 15:00:53', 'client', NULL),
(11, 'Shany Wyman', 'manley.volkman@example.org', '2024-11-26 15:00:53', '$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy', 'tnHeatje9Q', '2024-11-26 15:00:53', '2024-11-26 15:00:53', 'client', NULL),
(12, 'fdsgfdhsf', 'admddddin@example.com', NULL, '$2y$12$92JpTtMD8kqDQ1PtPTATkuVBFAC8zFuPB8PcNCb.i9PBuNqHRuIFy', NULL, '2024-11-26 15:34:07', '2024-11-26 15:34:07', 'formation', NULL),
(16, 'sdvdsv', 'admfffin@esdfcsvd.fffff', NULL, '$2y$12$w7r7P0rJpO2h1xSoL/xTeehkGkLVNJ3E2OVTVtDOCj5I3ylMOhjt2', NULL, '2024-11-27 13:07:39', '2024-11-27 13:07:39', 'client', NULL),
(17, 'fffffff', 'adffvfffmin@example.com', NULL, '$2y$12$tTw.nEb1NLT4ApIR4XpsTub7AmnFad25o1dw3ZGzSl8x4X0Vqk9EO', NULL, '2024-11-27 13:11:49', '2024-11-27 13:11:49', 'client', 1),
(18, 'fdgfdgfd', 'admdfdfdfdin@example.com', NULL, '$2y$12$Cap57glUya76I2exAznziO0e9RY3qI./rYUYpQlAtczW3rcTky4Qa', NULL, '2024-11-27 13:43:53', '2024-11-27 13:43:53', 'client', 2),
(19, 'wxcvbn', 'adxcvbmin@example.com', NULL, '$2y$12$VzkMjB9j2BhfLaMvFH0u6eQuBsaWKLFkdUu2RRz.w1dqG4G0Vj8j.', NULL, '2024-11-27 14:04:48', '2024-11-27 14:05:10', 'client', 3),
(20, 'fdfddfdfdfdfd', 'fdfdfdfddffdfdfd@example.com', NULL, '$2y$12$2hBQc6ozjFotrO8Qt.OxAeJAL0gWkuvuNe3gruOar768W0I10Bd8q', NULL, '2024-11-27 14:05:52', '2024-11-27 14:07:10', 'client', 3);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attachments_message_id_foreign` (`message_id`);

--
-- Index pour la table `badge_requests`
--
ALTER TABLE `badge_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `badge_requests_user_id_foreign` (`user_id`);

--
-- Index pour la table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_user_id_foreign` (`user_id`),
  ADD KEY `comments_badge_request_id_foreign` (`badge_request_id`);

--
-- Index pour la table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversations_created_by_foreign` (`created_by`);

--
-- Index pour la table `discussions`
--
ALTER TABLE `discussions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `discussions_user_id_foreign` (`user_id`);

--
-- Index pour la table `discussion_files`
--
ALTER TABLE `discussion_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `discussion_files_discussion_id_foreign` (`discussion_id`),
  ADD KEY `discussion_files_message_comment_id_foreign` (`message_comment_id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_conversation_id_foreign` (`conversation_id`),
  ADD KEY `messages_user_id_foreign` (`user_id`);

--
-- Index pour la table `message_comments`
--
ALTER TABLE `message_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_comments_user_id_foreign` (`user_id`),
  ADD KEY `message_comments_discussion_id_foreign` (`discussion_id`),
  ADD KEY `message_comments_parent_id_foreign` (`parent_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Index pour la table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `replies_comment_id_foreign` (`comment_id`),
  ADD KEY `replies_user_id_foreign` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `badge_requests`
--
ALTER TABLE `badge_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `discussions`
--
ALTER TABLE `discussions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `discussion_files`
--
ALTER TABLE `discussion_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `message_comments`
--
ALTER TABLE `message_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT pour la table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `badge_requests`
--
ALTER TABLE `badge_requests`
  ADD CONSTRAINT `badge_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_badge_request_id_foreign` FOREIGN KEY (`badge_request_id`) REFERENCES `badge_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `discussions`
--
ALTER TABLE `discussions`
  ADD CONSTRAINT `discussions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `discussion_files`
--
ALTER TABLE `discussion_files`
  ADD CONSTRAINT `discussion_files_discussion_id_foreign` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_files_message_comment_id_foreign` FOREIGN KEY (`message_comment_id`) REFERENCES `message_comments` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `message_comments`
--
ALTER TABLE `message_comments`
  ADD CONSTRAINT `message_comments_discussion_id_foreign` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `message_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
