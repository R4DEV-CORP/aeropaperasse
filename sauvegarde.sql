-- MySQL dump 10.13  Distrib 9.0.1, for macos15.1 (arm64)
--
-- Host: localhost    Database: api-badges
-- ------------------------------------------------------
-- Server version	9.0.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_comments`
--

DROP TABLE IF EXISTS `activity_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `activity_request_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_comments_user_id_foreign` (`user_id`),
  KEY `activity_comments_activity_request_id_foreign` (`activity_request_id`),
  CONSTRAINT `activity_comments_activity_request_id_foreign` FOREIGN KEY (`activity_request_id`) REFERENCES `activity_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_comments`
--

LOCK TABLES `activity_comments` WRITE;
/*!40000 ALTER TABLE `activity_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_requests`
--

DROP TABLE IF EXISTS `activity_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `renouvellement` tinyint(1) NOT NULL DEFAULT '0',
  `autorisation_anterieur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raison_sociale` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_commercial` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `siret` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsable_nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsable_prenom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsable_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsable_telephone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsable_fonction` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activite_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_personnes` int NOT NULL,
  `nombre_vehicules` int NOT NULL,
  `clients_denomination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extrait_kbis_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attestations_clients_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `formulaire_surete_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agrement_prefectoral_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contrat_iata_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_requests`
--

LOCK TABLES `activity_requests` WRITE;
/*!40000 ALTER TABLE `activity_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `message_id` bigint unsigned NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attachments_message_id_foreign` (`message_id`),
  CONSTRAINT `attachments_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
INSERT INTO `attachments` VALUES (1,9,'Capture d’écran 2024-11-28 111258.png','attachments/oO46QEw0Of7i0VhpRoWP9XVrQVFVfj3m46frjhs8.png','image/png',13830,'2024-11-29 15:56:30','2024-11-29 15:56:30',NULL),(2,10,'chute20-11-2024.png','attachments/6XZnyXji0hmSpStbMPZ3wuOFFAAWwOUa1V2EYMFK.png','image/png',762324,'2024-11-30 07:52:03','2024-11-30 07:52:03',NULL),(3,13,'Capture d’écran 2024-11-28 111258.png','attachments/EAyOSbpbMhilAilko81pYttVdOgjXYyaNmAvCInL.png','image/png',13830,'2024-12-01 09:57:13','2024-12-01 09:57:13',NULL),(4,14,'Capture d’écran 2024-11-28 111258.png','attachments/xrXDTML3Gom1rU2muDfFTIO3B0600Rs61JyR1Oe7.png','image/png',13830,'2024-12-01 09:57:48','2024-12-01 09:57:48',NULL);
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `badge_requests`
--

DROP TABLE IF EXISTS `badge_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `badge_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `airport` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending_rem','rejected_rem','pending_adp','approved_adp','rejected_adp','pending_fabrication','ready_for_delivery') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_rem',
  `photoIdentite` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `autorisationActivite` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificatFormation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pending_rem_at` timestamp NULL DEFAULT NULL,
  `rejected_rem_at` timestamp NULL DEFAULT NULL,
  `pending_adp_at` timestamp NULL DEFAULT NULL,
  `approved_adp_at` timestamp NULL DEFAULT NULL,
  `rejected_adp_at` timestamp NULL DEFAULT NULL,
  `pending_fabrication_at` timestamp NULL DEFAULT NULL,
  `ready_for_delivery_at` timestamp NULL DEFAULT NULL,
  `est_habilitation` tinyint(1) NOT NULL DEFAULT '0',
  `documentFor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `badge_requests_user_id_foreign` (`user_id`),
  CONSTRAINT `badge_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badge_requests`
--

LOCK TABLES `badge_requests` WRITE;
/*!40000 ALTER TABLE `badge_requests` DISABLE KEYS */;
INSERT INTO `badge_requests` VALUES (1,1,NULL,'dupin','nicolas','dupin.nicolas@yahoo.fr','0619180765','pending_fabrication','photos/xzx6wtTVWvOSOzxuVgA5pQFbveHKe5G0S1wFg3au.jpg','autorisations/RxMhcUOXhmIOE83VfY1XuU14pfD6OjPiRsE0jKWs.png','certificats/Ngvt4jNjiYk5Wx7Z8xC0T3cOmv3k0LW6srOrV0wW.png','2024-11-26 17:05:07','2025-02-21 08:20:58',NULL,NULL,'2025-02-21 08:20:55','2025-02-21 08:20:57',NULL,'2025-02-21 08:20:58',NULL,0,NULL,NULL),(2,1,NULL,'nicolas','dupin','pinpin34android@gmail.com','0619180765','pending_fabrication','photos/4ueJmm1od1x1KakXSyzDvOlz93Gtf2kQOgu1u9La.jpg','autorisations/aWHtlwuCqD63qtPHHpeQhpoytasUlc18S3TJMGRP.png','certificats/ZeaW8HYE2H2bBVygcfNSHS34GOEvNS30lNuCn3np.png','2024-11-26 17:05:53','2025-02-21 07:32:53',NULL,NULL,'2025-02-21 07:32:42','2025-02-21 07:32:48',NULL,'2025-02-21 07:32:53',NULL,0,NULL,NULL),(3,1,NULL,'DUPIN','Nicolas','nicolas1.dupin@orange.com','0647545931','pending_rem','photos/LCVhIqIGCVvIdstFP9nl0w8gVHqhBMMJb1k0q1kK.jpg','autorisations/9XdrJsoTDj5gBJBuBN13mcMuCjtoD8B3vWG6RhFo.png','certificats/rpM4CIa2KQYInU8nwJdm61BDb7tzHatxjtz3FOkw.png','2024-11-26 17:22:43','2024-11-26 17:22:43',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(4,1,NULL,'dupin','nicolas','dupin.nicolas@yahoo.fr','0619180765','rejected_rem','photos/EM5W1Q26EzHTJre9Yg3vCYWpKYSMK7eR3AAZwshp.jpg','autorisations/bR4jQ4SVHuxyrDWL7DJ6oVXhgu48GVwh4g2HiB60.png','certificats/utgOLukb79V82wS9KANNsgg7etiIxHsejJ4hVyGa.png','2024-11-26 20:03:58','2025-02-21 07:30:00',NULL,'2025-02-21 07:30:00',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(5,1,NULL,'DUPIN','Nicolas','nicolas1.dupin@orange.com','0647545931','ready_for_delivery','photos/FNhLFS6nLieDXzDJYFxMMmhKzCrRH8ttIN1KvGZw.jpg','autorisations/6SjwElN461r6YYZFB5T7Z1KPVj79x71mROiyj4JR.png','certificats/xxkyjFkNdb6RPjjoqF07MHO8RIUecB6ME5WFyZR5.png','2024-11-27 08:55:13','2025-02-21 07:29:30',NULL,NULL,'2025-02-21 07:29:13','2025-02-21 07:29:21',NULL,'2025-02-21 07:29:26','2025-02-21 07:29:30',0,NULL,NULL),(6,7,NULL,'dupin','nicolas','dupin.nicolas@yahoo.fr','0619180765','pending_rem','photos/sMsQNB2li4AlwYSipNU4LQ7DcqpTY1WwS4XVwYL0.jpg','autorisations/mtRwylw0uRzzcY1I1rlm19hIuDQR0CSxFOGyw6Sy.png','certificats/PFMwg8KQcR7FRIyR5yLuopAnD4rHtG8HCvp6f5n0.png','2024-11-27 09:58:21','2025-02-20 14:54:16',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(7,6,NULL,'kreiger.mariah@example.net','kreiger.mariah@example.net','kreiger.mariah@example.net','0647545931','ready_for_delivery','photos/1H03UNmVPh4hZd0iMdiuXttttFeMpevwuEBmkG1X.jpg','autorisations/QmslOrdEE0KDEyLFAnadKa9YbqHOb7qfDr64DZYV.png','certificats/kgqqKuCdPSqRLzBUrGp7VPrZRLQocz1WUa09ltcL.png','2024-11-27 10:16:21','2025-02-21 07:24:55',NULL,NULL,'2025-02-21 07:24:52','2025-02-21 07:24:52',NULL,'2025-02-21 07:24:54','2025-02-21 07:24:55',0,NULL,NULL),(8,6,NULL,'dupin','nicolas','dupin.nicolas@yahoo.fr','0619180765','rejected_adp','photos/L82MAU62kvmmmDaYeaHouwGPTB8iCHssKcNYj1AA.jpg','autorisations/6OapjPL73VjxdbPjW4inAZJddioR9fdFOMbty6VA.png','certificats/rhokfgavEdWuFLY93T3yPcbqWwqIMycZ0nB5Tjbn.png','2024-11-27 11:18:01','2025-02-21 07:24:45',NULL,NULL,'2025-02-21 07:24:40',NULL,'2025-02-21 07:24:45',NULL,NULL,0,NULL,NULL),(9,6,NULL,'nicolasxcvc','dupinxvvx c','pinpin34android@gmail.com','0619180765','approved_adp','photos/TOQUAk156tY5YXeBP0zgugbZYAn0C31Sz7yyXvPi.jpg','autorisations/CGpMoedlec8G1chvBcGRulvhRQtvKEQLWtTaCSi7.png','certificats/CJPIuEuOxbOx1y3pORlCx9V8jWogac0O6CN2vVa2.png','2024-11-27 13:19:37','2025-02-21 07:24:36',NULL,'2025-02-20 14:54:13','2025-02-21 07:24:33','2025-02-21 07:24:36',NULL,NULL,NULL,0,NULL,NULL),(10,19,NULL,'dupin','nicolas','dupin.nicolas@yahoo.fr','0619180765','pending_rem','photos/0UBUEj12LS7iNagGDIkhL6aB3hFzhjWLL8xUSuoV.jpg','autorisations/eNbQ8KcSlu0RScvwnrFzf1Gl3WNAuLXsApL0pTTj.png','certificats/xqgIh93XeLCkmAXd8qxfTKUnbq2VpQJuYaOxGoxs.png','2024-11-27 14:06:33','2025-02-20 15:04:25',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(12,1,NULL,'dupin','nicolas','dupin.nicolas@yahoo.fr','0619180765','pending_fabrication','photos/2iU0bbSTLJ0Sj6dnSi5OzpSe4y4NSZxkTyY578rf.jpg','autorisations/zAzanIbHYX2hegl2x0sETzBhAR7xPHfnQ8svHIm3.png','certificats/qKe7P2QWx3WM8Ca2qhAGAkqgHAgGJImyj206QfDa.png','2024-11-28 11:46:47','2025-02-20 15:30:02',NULL,NULL,'2025-02-20 15:29:59','2025-02-20 15:29:59',NULL,'2025-02-20 15:30:02',NULL,0,NULL,NULL),(13,6,NULL,'dupin','nicolas','dupin.nicolas@yahoo.fr','0619180765','rejected_rem','photos/ekHsfrHGaOErqTRqKCxaR5rV3iAaS23t3hnKvCKl.jpg','autorisations/WSdo8ObfINgQEywS5y9Alp55ITU9LAAIRR630cpu.png','certificats/68oxTMXOL35QVR03Uhgh6ci2lReypcROe7CQSnva.png','2024-11-28 16:50:38','2025-02-20 15:29:57',NULL,'2025-02-20 15:29:57',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(14,1,NULL,'dupin','nicolas','dupin.nicolas@yahoo.fr','0619180765','pending_fabrication','photos/8qLsPQ9978uswIw3Z6P2LapvbWJFd7vEhC9oiYry.jpg','autorisations/QDYwlo13FPYq0KCKsGAL6YvDA8MwgdxlsIgtwGDp.png','certificats/IeF8lo5kCeQMApymWsw2iM5bINUpdSVP5eiPpi7j.png','2024-12-01 09:45:29','2025-02-20 15:32:47',NULL,NULL,'2025-02-20 15:29:54','2025-02-20 15:29:55',NULL,'2025-02-20 15:32:47',NULL,0,NULL,NULL),(15,1,NULL,'dupin','nicolas','dupin.nicolas@yahoo.fr','0619180765','pending_adp','photos/rSQPrZfMYN9iQ7hmCZGwTjcW5WoDkboODnd71es0.jpg','autorisations/vtmqk4KC3j193jfzKYIIGAXfNy5arMLTDEsg3fs2.png','certificats/k9MKJQNKQmzkfnVVinhLg8lFy4wr6RuZvIF2KtDK.png','2024-12-01 16:11:58','2025-02-20 15:29:52',NULL,'2025-02-20 14:54:00','2025-02-20 15:29:52',NULL,NULL,NULL,NULL,0,NULL,NULL),(16,1,NULL,'hih','ihih','dupin.nicolas@yahoo.fr','0619180765','ready_for_delivery','photos/piGaMbpZ5Mt6lvKpAo814QYmqj7aWWfaRoN0lpgb.jpg','autorisations/OQqasx4dvRi4fndBcjt5i40HYYmrz25aCjvghONk.png','certificats/MlKKqJSy4rM1LpfqZ3jViFqc3rj0wRjTsAxN3MDS.png','2024-12-16 12:24:39','2025-02-20 15:29:42',NULL,NULL,'2025-02-20 15:29:29','2025-02-20 15:29:31',NULL,'2025-02-20 15:29:40','2025-02-20 15:29:42',0,NULL,NULL),(17,1,NULL,'Test','Benjamin','benjamin@test.com','0612345678','pending_adp','photos/3CE3sOYAGLovw4fUKpRNfgPRC0bwkRJgX1RJEoeO.jpg','autorisations/tp8XMu95NFinPjoTpxgM7rHQq2w20fjOUPtFkrxo.pdf','certificats/kqoWZmv7FT2Zn9tDJoeCHOAmLqnpgUikAYHN3DH9.pdf','2025-02-25 15:34:40','2025-02-26 08:10:34','2025-02-25 15:34:40',NULL,'2025-02-26 08:10:34',NULL,NULL,NULL,NULL,0,NULL,'factures/LYdm3si64fXYlygMwVldc2d9K8A7hasbBcIvK4g7.pdf'),(18,1,NULL,'Test','Jean-Michel','jeanmi@exemple.com','0612345678','rejected_rem','photos/3OXn9H0JbZOWIig5zfJDCiEzsoyNKPckAPwWdfQS.jpg','autorisations/89kCZgsrqgsuXwIEguIwbUqyfh2uSIae12C6zIAh.pdf',NULL,'2025-02-25 15:38:20','2025-02-26 10:25:37','2025-02-25 15:38:20','2025-02-26 10:25:37',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(19,1,'aeroportOrly','Testeur','Ben','benjamin.diard@r4web.fr','0612345678','pending_rem','photos/b9L24GpsjK6LSLAeZmynhz7IrLy69FLFH72EnbEQ.jpg','autorisations/rIy0funv1rRnjw8V2CFVW0zmFww4H42nC883Py2r.pdf',NULL,'2025-02-26 09:36:41','2025-02-26 09:36:41','2025-02-26 09:36:41',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(20,1,'aeroportCDG','Testttt','Benjamin','benjamin.test@test.com','0612345678','approved_adp','photos/Ep3kDtzQJkHO4r54EctOJzmrJ4Npfpp06THXXCMZ.jpg','autorisations/XEgqQBmmbdOfVgeEC0HEADAG22WeWZncep9a4VXX.pdf','certificats/2W68SCqLcxR66tg3ZCZqIHqPrZo7aqf0nVC810nu.pdf','2025-02-26 10:20:56','2025-02-26 10:21:51','2025-02-26 10:20:56',NULL,'2025-02-26 10:21:50','2025-02-26 10:21:51',NULL,NULL,NULL,0,'documents_for/lOBQW3XZTErVUOvn07DTtsSwtHZEdNOmkjc6FpSN.pdf','factures/tZZHoBpBLPWw7M25dbjUuKAaK6RSeA87qmkVeApb.pdf'),(21,1,'aeroportBourget','Habilitation','Benjamin','benjaminhabilitation@test.com','0615654654','rejected_rem','photos/VVdJmMlfOfcX9PpZmeajbeumiXYq4VUzoIBK3uaM.jpg','autorisations/YS3nDcR6YyOwbMA5OJv3rbWli4lZ2FcjzBPVTy6w.pdf',NULL,'2025-02-26 10:23:10','2025-02-26 10:25:33','2025-02-26 10:23:10','2025-02-26 10:25:33',NULL,NULL,NULL,NULL,NULL,0,'documents_for/5yLzt1GSPwzcLvTZbmlFX2OW5O0aAatYGcL10jzL.pdf',NULL),(22,1,'aeroportBourget','Teeeest','Benjamin','test.test@test.com','0612345678','approved_adp','photos/KW246FfzMbKNWi8cWyL7GFI2qw10M1YlHcQe0ilT.jpg','autorisations/J7XaaCoMJB90dNYf2kLzpHzk7GXj9bmXnHozcTtv.pdf',NULL,'2025-02-26 12:08:26','2025-02-26 12:08:40','2025-02-26 12:08:26',NULL,'2025-02-26 12:08:39','2025-02-26 12:08:40',NULL,NULL,NULL,0,'documents_for/2nZuKKzU3liNxeqdLtm3nYGu8Pbce6k2DQpXE8fI.pdf',NULL),(23,1,'aeroportOrly','Habi','Benjamin','benjamin.habi@test.com','0611111111','pending_fabrication','photos/0qkdTQaZG9aOXGYWCdYBuJhRoVjrIP24uw1tLEvg.jpg','autorisations/e0vhvhtWhZhR8wDhry9IbUDPRUHdEQos8d2jIbST.pdf',NULL,'2025-02-26 12:09:37','2025-02-26 12:13:12','2025-02-26 12:09:37',NULL,'2025-02-26 12:13:10','2025-02-26 12:13:11',NULL,'2025-02-26 12:13:12',NULL,0,'documents_for/mIqgXzd6OXZwEwEP3Zls9jOpcUGzgLIyinivRUWk.pdf',NULL),(24,1,'aeroportCDG','Testencore','John','john.test@example.com','0612345678','ready_for_delivery','photos/Hx9MkjG2W4KK2l3UMWqwqc7dLybvedhTcBcgbVYe.jpg','autorisations/P8XEQR5KbqlKqE4zNVo9M10q6TWqfrKwsQEbKRhl.pdf',NULL,'2025-02-26 12:12:56','2025-02-26 12:13:09','2025-02-26 12:12:56',NULL,'2025-02-26 12:13:02','2025-02-26 12:13:02',NULL,'2025-02-26 12:13:07','2025-02-26 12:13:09',1,'documents_for/tBY7VwWzhtMw3sAMskPWmKXEYJdjG3PE0a2VDsbD.pdf',NULL);
/*!40000 ALTER TABLE `badge_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `badges`
--

DROP TABLE IF EXISTS `badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `badges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `badge_request_id` bigint unsigned NOT NULL,
  `badge_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','expired','returned','not_returned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `expiry_date` date NOT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `return_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `badges_badge_number_unique` (`badge_number`),
  KEY `badges_badge_request_id_foreign` (`badge_request_id`),
  CONSTRAINT `badges_badge_request_id_foreign` FOREIGN KEY (`badge_request_id`) REFERENCES `badge_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badges`
--

LOCK TABLES `badges` WRITE;
/*!40000 ALTER TABLE `badges` DISABLE KEYS */;
/*!40000 ALTER TABLE `badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_training_access`
--

DROP TABLE IF EXISTS `client_training_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_training_access` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL,
  `training_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_training_access_client_id_training_id_unique` (`client_id`,`training_id`),
  KEY `client_training_access_training_id_foreign` (`training_id`),
  CONSTRAINT `client_training_access_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_training_access_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_training_access`
--

LOCK TABLES `client_training_access` WRITE;
/*!40000 ALTER TABLE `client_training_access` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_training_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referent_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referent_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `safety_referent_name_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `safety_referent_email_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `safety_referent_phone_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `safety_referent_document_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `safety_referent_name_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `safety_referent_email_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `safety_referent_phone_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `safety_referent_document_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `safety_referent_name_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `safety_referent_email_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `safety_referent_phone_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `safety_referent_document_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_name_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_email_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_phone_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_document_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_name_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_email_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_phone_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_document_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_name_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_email_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_phone_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_correspondent_document_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kbis_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hr_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hr_contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hr_contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'Le Client','Marie Dupont','marie.dupont@corporate.com',NULL,'2025-01-28 09:19:04',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'test client','Jean Martin','jean.martin@entreprise.fr','2024-11-27 13:32:35','2025-01-28 09:19:04',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,'tarte','Sophie Leroy','sophie.leroy@management.fr','2024-11-27 14:04:03','2025-01-28 09:19:04',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,'R4web','Pierre Dubois','pierre.dubois@direction.com','2024-11-28 18:58:10','2025-01-28 09:19:04',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `badge_request_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_user_id_foreign` (`user_id`),
  KEY `comments_badge_request_id_foreign` (`badge_request_id`),
  CONSTRAINT `comments_badge_request_id_foreign` FOREIGN KEY (`badge_request_id`) REFERENCES `badge_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,'fff',1,4,'2024-11-27 08:44:01','2024-11-27 08:47:17','2024-11-27 08:47:17'),(2,'test de commentairessss',1,4,'2024-11-27 08:47:34','2024-11-27 08:50:09','2024-11-27 08:50:09'),(3,'ici je test un commentaire',1,4,'2024-11-27 08:50:34','2024-11-27 08:50:34',NULL),(4,'commentaire',1,5,'2024-11-27 08:55:42','2024-11-27 08:55:42',NULL),(5,'cvbxcvcxv',1,6,'2024-11-27 09:58:30','2024-11-27 09:58:41','2024-11-27 09:58:41'),(6,'xwc vxxxx',1,4,'2024-11-27 10:12:46','2024-11-27 10:12:58','2024-11-27 10:12:58'),(7,'test de commentaire',6,7,'2024-11-27 10:17:02','2024-11-27 10:17:02',NULL),(8,'ezdsedfqsdf',6,8,'2024-11-27 11:18:11','2024-11-27 11:18:11',NULL),(9,'qzsfdb',20,10,'2024-11-27 14:08:42','2024-11-27 14:08:42',NULL),(10,'sdfvbbvsd',1,12,'2024-11-28 11:47:06','2024-11-28 11:47:14','2024-11-28 11:47:14'),(11,'sdgrdbxbc',6,9,'2024-11-28 16:15:22','2024-11-28 16:15:33','2024-11-28 16:15:33'),(12,'daqsdxcvb',6,13,'2024-11-28 16:51:43','2024-11-28 16:51:43',NULL),(13,'reponse today',1,13,'2024-11-29 13:00:33','2024-11-29 13:00:33',NULL),(14,'xwcvccc',1,12,'2024-12-01 09:45:38','2024-12-01 09:45:45',NULL),(15,'cfbcb',1,14,'2024-12-01 09:59:17','2024-12-01 09:59:17',NULL),(16,'cvbc',1,14,'2024-12-01 09:59:20','2024-12-01 09:59:20',NULL),(17,'sdfg',1,15,'2024-12-01 16:12:13','2024-12-01 16:12:19','2024-12-01 16:12:19'),(18,'szdfgbhnsdfgvb',1,15,'2024-12-01 16:12:22','2024-12-01 16:12:25',NULL),(19,'te_',1,16,'2024-12-16 12:24:48','2024-12-16 12:24:48',NULL);
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `object` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversations_created_by_foreign` (`created_by`),
  CONSTRAINT `conversations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
INSERT INTO `conversations` VALUES (1,'Test conversation','pending',1,'2024-11-29 15:13:22','2024-11-29 15:13:22',NULL),(2,'Test conversation','pending',1,'2024-11-29 15:12:55','2024-11-29 15:12:55',NULL),(3,NULL,'open',1,'2024-11-29 15:41:49','2024-11-29 15:41:49',NULL),(4,NULL,'open',1,'2024-11-29 15:48:29','2024-11-29 15:48:29',NULL),(5,NULL,'open',1,'2024-11-29 15:49:23','2024-11-29 15:49:23',NULL),(6,NULL,'open',1,'2024-11-29 15:50:26','2024-11-29 15:50:26',NULL),(7,NULL,'open',1,'2024-11-29 15:55:16','2024-11-29 15:55:16',NULL),(8,NULL,'open',1,'2024-11-29 15:55:37','2024-11-29 15:55:37',NULL),(9,NULL,'open',1,'2024-11-29 15:56:30','2024-11-29 15:56:30',NULL),(10,NULL,'open',1,'2024-11-30 07:52:03','2024-11-30 07:52:03',NULL),(11,NULL,'open',1,'2024-12-01 09:57:13','2024-12-01 09:57:13',NULL),(12,NULL,'open',1,'2024-12-01 09:57:48','2024-12-01 09:57:48',NULL);
/*!40000 ALTER TABLE `conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discussion_files`
--

DROP TABLE IF EXISTS `discussion_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discussion_files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discussion_id` bigint unsigned DEFAULT NULL,
  `message_comment_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discussion_files_discussion_id_foreign` (`discussion_id`),
  KEY `discussion_files_message_comment_id_foreign` (`message_comment_id`),
  CONSTRAINT `discussion_files_discussion_id_foreign` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `discussion_files_message_comment_id_foreign` FOREIGN KEY (`message_comment_id`) REFERENCES `message_comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discussion_files`
--

LOCK TABLES `discussion_files` WRITE;
/*!40000 ALTER TABLE `discussion_files` DISABLE KEYS */;
INSERT INTO `discussion_files` VALUES (1,'Capture d’écran 2024-11-28 111258.png','discussion-files/v9h77Qo7KDIMRTeBfMTWVYFgDUAnbPp7xIYtagY2.png',1,NULL,'2024-12-01 10:44:59','2024-12-01 10:44:59'),(2,'chute20-11-2024.png','comment-files/giaWeNSNEvrqvb2qHRagljP8KU3o1dK0wOGbPNvR.png',NULL,4,'2024-12-01 10:54:07','2024-12-01 10:54:07'),(3,'Capture d’écran 2024-11-29 105105.png','comment-files/tn7mJD2Brh7IndSFBiKMZgBuBqLydIiA6Vl1y1S5.png',NULL,5,'2024-12-01 10:55:20','2024-12-01 10:55:20'),(4,'chute20-11-2024.png','comment-files/slXhUPb7BxiPklL1Q0M5sWP2tK229IxJW3As4D0A.png',NULL,9,'2024-12-01 11:06:24','2024-12-01 11:06:24'),(5,'chute20-11-2024.png','discussion-files/ET1JDDklm0pLdzSF3rsDKmepHcpDaXZ8qB9DPQiF.png',2,NULL,'2024-12-01 11:13:26','2024-12-01 11:13:26'),(6,'Capture d’écran 2024-11-28 111258.png','discussion-files/Rya7jrTgRybSatDl5qr1AtUsouNNWYSIuBhemSH6.png',3,NULL,'2024-12-01 11:14:00','2024-12-01 11:14:00'),(7,'Capture d’écran 2024-11-29 105105.png','discussion-files/PU9VcUZBeWA2D30GJ24Oug1mxJecXaCBvgwrmMDs.png',3,NULL,'2024-12-01 11:14:00','2024-12-01 11:14:00'),(8,'chute20-11-2024.png','discussion-files/ENnE8NfuZELpSV6pDa6G83r5P0Q42QpkDLzROYzS.png',3,NULL,'2024-12-01 11:14:00','2024-12-01 11:14:00'),(9,'chute20-11-2024.png','comment-files/QVJGVwgcyAF4w4QBkZPXz327TfiNr32W2qxYXvUZ.png',NULL,11,'2024-12-01 11:14:20','2024-12-01 11:14:20'),(10,'Capture d’écran 2024-11-28 111258.png','comment-files/1xfbN8m7e1KoGdnRSDizLxZN9BzIayM0pxbAZ8oI.png',NULL,12,'2024-12-01 11:28:42','2024-12-01 11:28:42'),(11,'Capture d’écran 2024-11-28 111258.png','comment-files/7h3KEBAbxpGR15EAmNpL8GwZpyhfKIX5ZCuEv7Zd.png',NULL,22,'2024-12-01 14:13:47','2024-12-01 14:13:47'),(12,'Capture d’écran 2024-11-28 111258.png','comment-files/XCxHvXxAhYBmHorKgFYECE4SLtza8Dm8JfNpLTt8.png',NULL,25,'2024-12-01 14:30:50','2024-12-01 14:30:50'),(13,'chute20-11-2024.png','discussion-files/ThbD2cESnzKDrV1wvj9MoOT2OdTtL0IsvWYCLYo4.png',6,NULL,'2024-12-01 16:08:40','2024-12-01 16:08:40'),(14,'Capture d’écran 2024-12-01 181731.png','comment-files/UemGGFweB7WXPftXl2UvOQabdoRgHqgRi34nhkRo.png',NULL,41,'2024-12-03 15:22:11','2024-12-03 15:22:11'),(15,'chute20-11-2024.png','discussion-files/2PSrt1XAJhfpCbs2CrhDtV2hr9VOE9heKj2J9l8J.png',7,NULL,'2024-12-16 12:26:32','2024-12-16 12:26:32'),(16,'chute20-11-2024.png','comment-files/Ei7AoMz6NC0CE34mzy5bj4yWOWjr8Y68MuoLUZ72.png',NULL,43,'2024-12-16 12:26:56','2024-12-16 12:26:56');
/*!40000 ALTER TABLE `discussion_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discussions`
--

DROP TABLE IF EXISTS `discussions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discussions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discussions_user_id_foreign` (`user_id`),
  CONSTRAINT `discussions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discussions`
--

LOCK TABLES `discussions` WRITE;
/*!40000 ALTER TABLE `discussions` DISABLE KEYS */;
INSERT INTO `discussions` VALUES (1,1,'sfcx','dgxdg','open','2024-12-01 10:44:59','2024-12-01 11:18:38'),(2,1,'Time attitude','C\'est la merde','closed','2024-12-01 11:13:26','2024-12-01 14:14:11'),(3,1,'test','test','closed','2024-12-01 11:14:00','2024-12-01 14:00:01'),(4,1,'r4web','r4web','open','2024-12-01 13:55:47','2024-12-01 14:31:11'),(5,1,'w<xcvb','sxdcfvgbnh,;','open','2024-12-01 14:32:20','2024-12-01 16:00:56'),(6,1,'cxcvxc','vcxvc','open','2024-12-01 16:08:40','2024-12-03 15:22:46'),(7,1,'ggg','gggg','open','2024-12-16 12:26:32','2024-12-16 12:26:32');
/*!40000 ALTER TABLE `discussions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_comments`
--

DROP TABLE IF EXISTS `message_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `discussion_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_comments_user_id_foreign` (`user_id`),
  KEY `message_comments_discussion_id_foreign` (`discussion_id`),
  KEY `message_comments_parent_id_foreign` (`parent_id`),
  CONSTRAINT `message_comments_discussion_id_foreign` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `message_comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_comments`
--

LOCK TABLES `message_comments` WRITE;
/*!40000 ALTER TABLE `message_comments` DISABLE KEYS */;
INSERT INTO `message_comments` VALUES (1,1,1,NULL,'cswvdwc','2024-12-01 10:51:52','2024-12-01 10:51:52'),(2,1,1,NULL,'cswvdwc','2024-12-01 10:52:38','2024-12-01 10:52:38'),(3,1,1,NULL,'cswvdwc','2024-12-01 10:53:55','2024-12-01 10:53:55'),(4,1,1,NULL,'test','2024-12-01 10:54:07','2024-12-01 10:54:07'),(5,1,1,NULL,'test de mise a jour','2024-12-01 10:55:20','2024-12-01 10:55:20'),(6,1,1,NULL,'wxvxv','2024-12-01 11:05:07','2024-12-01 11:05:07'),(7,1,1,NULL,'sesdffdf','2024-12-01 11:06:00','2024-12-01 11:06:00'),(8,1,1,NULL,'1111111111','2024-12-01 11:06:06','2024-12-01 11:06:06'),(9,1,1,NULL,'222222','2024-12-01 11:06:24','2024-12-01 11:06:24'),(10,1,1,NULL,'xbcvbv','2024-12-01 11:12:08','2024-12-01 11:12:08'),(11,1,3,NULL,'test','2024-12-01 11:14:20','2024-12-01 11:14:20'),(12,6,3,NULL,'sxdqscqfcvb','2024-12-01 11:28:42','2024-12-01 11:28:42'),(13,1,3,NULL,'<xscvb','2024-12-01 12:04:17','2024-12-01 12:04:17'),(14,1,3,NULL,'cv','2024-12-01 13:47:48','2024-12-01 13:47:48'),(15,1,3,NULL,'cvb','2024-12-01 13:48:09','2024-12-01 13:48:09'),(16,1,3,NULL,'dddd','2024-12-01 13:48:45','2024-12-01 13:48:45'),(17,1,2,NULL,'time','2024-12-01 13:49:02','2024-12-01 13:49:02'),(18,1,2,NULL,'time','2024-12-01 13:49:51','2024-12-01 13:49:51'),(19,1,2,NULL,'xdcx  time','2024-12-01 13:55:15','2024-12-01 13:55:15'),(20,1,4,NULL,'r4webr4web','2024-12-01 13:55:56','2024-12-01 13:55:56'),(21,1,4,NULL,'lol','2024-12-01 14:10:49','2024-12-01 14:10:49'),(22,1,4,NULL,'test','2024-12-01 14:13:47','2024-12-01 14:13:47'),(23,1,4,NULL,'dd','2024-12-01 14:21:13','2024-12-01 14:21:13'),(24,1,4,NULL,'zob','2024-12-01 14:30:38','2024-12-01 14:30:38'),(25,1,4,NULL,'aqsdfvgbhnj,;:','2024-12-01 14:30:50','2024-12-01 14:30:50'),(26,1,5,NULL,'sxdcfvg,','2024-12-01 14:32:34','2024-12-01 14:32:34'),(27,1,5,NULL,'xcvb','2024-12-01 14:33:15','2024-12-01 14:33:15'),(28,1,5,NULL,'ssqd','2024-12-01 15:06:45','2024-12-01 15:06:45'),(29,1,5,NULL,'ffffff','2024-12-01 15:06:50','2024-12-01 15:06:50'),(30,1,5,NULL,'xxxxxxxxxx','2024-12-01 15:07:08','2024-12-01 15:07:08'),(31,1,5,NULL,'wwwwwwwwwwwww','2024-12-01 15:07:25','2024-12-01 15:07:25'),(32,1,5,NULL,'fffffffff','2024-12-01 15:07:47','2024-12-01 15:07:47'),(33,1,5,NULL,'ffff','2024-12-01 15:48:37','2024-12-01 15:48:37'),(34,1,5,NULL,'sdfghnj,','2024-12-01 15:49:33','2024-12-01 15:49:33'),(35,1,5,NULL,'ff','2024-12-01 15:49:58','2024-12-01 15:49:58'),(36,1,5,NULL,'test','2024-12-01 15:51:56','2024-12-01 15:51:56'),(37,1,5,NULL,'eeeeeeeeeeeeeeeeeeeeeeeeeeee','2024-12-01 15:53:43','2024-12-01 15:53:43'),(38,6,5,NULL,'cxvxcbv','2024-12-01 16:06:16','2024-12-01 16:06:16'),(39,1,6,NULL,'xcvcxbcb','2024-12-01 16:08:49','2024-12-01 16:08:49'),(40,1,6,NULL,'w','2024-12-01 16:09:43','2024-12-01 16:09:43'),(41,1,6,NULL,'ta mere','2024-12-03 15:22:11','2024-12-03 15:22:11'),(42,1,7,NULL,'gug_','2024-12-16 12:26:41','2024-12-16 12:26:41'),(43,1,7,NULL,'gyu','2024-12-16 12:26:56','2024-12-16 12:26:56');
/*!40000 ALTER TABLE `message_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_conversation_id_foreign` (`conversation_id`),
  KEY `messages_user_id_foreign` (`user_id`),
  CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_chk_1` CHECK (json_valid(`attachments`))
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (2,1,1,'Premier message de test',0,NULL,'2024-11-29 15:13:22','2024-11-29 15:13:22',NULL),(3,3,1,'sdf',0,NULL,'2024-11-29 15:41:49','2024-11-29 15:41:49',NULL),(4,4,1,'dxcv',0,NULL,'2024-11-29 15:48:29','2024-11-29 15:48:29',NULL),(5,5,1,'sdqfqsdf',0,NULL,'2024-11-29 15:49:23','2024-11-29 15:49:23',NULL),(6,6,1,'vdv',0,NULL,'2024-11-29 15:50:26','2024-11-29 15:50:26',NULL),(7,7,1,'cxvb',0,NULL,'2024-11-29 15:55:16','2024-11-29 15:55:16',NULL),(8,8,1,'dxw',0,NULL,'2024-11-29 15:55:37','2024-11-29 15:55:37',NULL),(9,9,1,'sdwxc',0,NULL,'2024-11-29 15:56:30','2024-11-29 15:56:30',NULL),(10,10,1,'vcxvbxvc',0,NULL,'2024-11-30 07:52:03','2024-11-30 07:52:03',NULL),(11,1,1,'sdgdf',0,NULL,'2024-12-01 09:56:15','2024-12-01 09:56:15',NULL),(12,1,1,'ccccv',0,NULL,'2024-12-01 09:56:52','2024-12-01 09:56:52',NULL),(13,11,1,'xcvcxv',0,NULL,'2024-12-01 09:57:13','2024-12-01 09:57:13',NULL),(14,12,1,'bbbbb',0,NULL,'2024-12-01 09:57:48','2024-12-01 09:57:48',NULL),(15,12,1,'cxxb',0,NULL,'2024-12-01 09:58:51','2024-12-01 09:58:51',NULL);
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2024_11_26_144317_add_role_to_users_table',2),(6,'2024_11_26_174624_create_badge_requests_table',3),(7,'2024_11_27_084548_create_comments_table',4),(8,'2024_11_27_084549_create_replies_table',4),(9,'2024_11_27_104056_add_user_id_to_badge_requests_table',5),(10,'2024_11_27_104436_add_foreign_key_to_badge_requests_table_step2',6),(11,'2024_11_27_132714_create_clients_table',7),(14,'2024_11_29_083417_create_messages_table',8),(15,'2024_11_29_091536_create_message_attachments_table',8),(16,'2024_11_29_094712_add_parent_message_id_to_message_table',9),(17,'2024_11_29_141257_create_conversations_table',10),(18,'2024_12_01_112925_create_discussions_table',11),(19,'2024_12_01_112951_create_message_comments_table',11),(20,'2024_12_01_113017_create_discussion_files_table',11),(21,'2025_01_13_151750_create_trainings_table',12),(22,'2025_01_13_153502_create_client_training_access_table',12),(23,'2025_01_13_154052_create_user_trainings_table',12),(25,'2025_01_15_105930_add_dates_to_user_trainings',13),(26,'2025_01_15_143258_add_missing_dates_to_user_trainings',13),(27,'2025_01_28_093041_add_function_to_users_table',14),(28,'2025_01_28_101036_add_referent_columns_to_clients_table',15),(29,'2025_01_27_104828_create_activity_requests_table',16),(30,'2025_01_27_125027_create_activity_comments',16),(31,'2025_01_27_130702_create_reply_activity_table',16),(32,'2025_02_17_105236_update_training_tables_structure',17),(33,'2025_02_17_141452_create_training_catalogs_table',18),(34,'2025_02_17_154520_update_trainings_table_structure',19),(35,'2025_02_17_142106_create_badges_table',20),(36,'2025_02_18_123003_create_two_factor_codes_table',20),(37,'2025_02_18_123034_add_two_factor_enabled_to_users_table',20),(38,'2025_02_20_135817_update_badge_requests_table_with_detailed_status',21),(39,'2025_02_20_162349_remove_approved_rem_from_badge_requests_table',22),(40,'2025_02_25_154611_add_new_fields_to_badge_requests_table',23),(41,'2025_02_25_163611_allow_null_certificat_formation',24),(42,'2025_02_26_101757_add_airport_to_badge_requests_table',25),(43,'2025_02_26_111115_rename_document_for_column',26),(44,'2025_02_21_102943_update_users_two_factor_default',27),(45,'2025_03_03_133658_add_additional_fields_to_clients_table',27);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',1,'auth_token','589b24d50cdeff3b7e91d6a6b9ff5affb82d28452c0ce738ab79c55e276e5a5e','[\"*\"]',NULL,NULL,'2024-11-26 13:53:08','2024-11-26 13:53:08'),(2,'App\\Models\\User',1,'auth_token','4e3fa453b7ad4f45de9186e825dd3a1651a26292922c0d35417de7bc7113f41d','[\"*\"]',NULL,NULL,'2024-11-26 14:00:07','2024-11-26 14:00:07'),(3,'App\\Models\\User',1,'auth_token','720f588132dbdfb71ceaa4a713d94b3d9b1f9c0b9e69d861e509b436443bb09f','[\"*\"]',NULL,NULL,'2024-11-26 14:04:46','2024-11-26 14:04:46'),(4,'App\\Models\\User',1,'auth_token','681fe155d5cf0a30956e796e4f2c26e518788178bbd6abbccd94a093b69ba504','[\"*\"]',NULL,NULL,'2024-11-26 14:23:15','2024-11-26 14:23:15'),(5,'App\\Models\\User',1,'auth_token','a3f4671e2188c1624229d51fed7529725583ece1777e03259a5c47a92570e649','[\"*\"]',NULL,NULL,'2024-11-26 14:23:42','2024-11-26 14:23:42'),(6,'App\\Models\\User',1,'auth_token','d23026b2736c1d7be10f1d3b9cc30a0c18c3d2a980630242825fb9bc371a1012','[\"*\"]',NULL,NULL,'2024-11-26 14:24:37','2024-11-26 14:24:37'),(7,'App\\Models\\User',1,'auth_token','cacf2c6408bd39612c01b91864a1643739b0529ad6787d3014ed466b6b786951','[\"*\"]','2024-11-26 14:57:34',NULL,'2024-11-26 14:37:15','2024-11-26 14:57:34'),(9,'App\\Models\\User',1,'auth_token','5cdc30ca4af564a24b01f5a8fc21aeba786a435f0720a40dcdd7bd45923b8247','[\"*\"]','2024-11-26 15:08:44',NULL,'2024-11-26 15:04:21','2024-11-26 15:08:44'),(10,'App\\Models\\User',1,'auth_token','5916379e7878236391eeddb0c9964125116eb987dd7f9f657cd95fd378f6d6a5','[\"*\"]','2024-11-26 15:41:44',NULL,'2024-11-26 15:09:07','2024-11-26 15:41:44'),(11,'App\\Models\\User',1,'auth_token','af939554521f56bc4863aea8c7f2638229e2e83d6109c635cf9ee4b1d903a281','[\"*\"]','2024-11-26 17:41:35',NULL,'2024-11-26 15:43:13','2024-11-26 17:41:35'),(12,'App\\Models\\User',13,'auth_token','14b1989ebe157a1ea93b767905b182fa08573d12fdaf7b0bbdcca65472a50915','[\"*\"]','2024-11-26 17:41:49',NULL,'2024-11-26 17:41:42','2024-11-26 17:41:49'),(13,'App\\Models\\User',1,'auth_token','86db0331c0c7aaa5007afcb1b541ad453c21ec9ee9dd2dd151bb0119365cab32','[\"*\"]','2024-11-27 07:29:02',NULL,'2024-11-26 17:42:10','2024-11-27 07:29:02'),(14,'App\\Models\\User',1,'auth_token','02fcae746da6cd924126296701e34e1cc782dc4cadc4fac259bc1222c0bcd123','[\"*\"]',NULL,NULL,'2024-11-27 07:29:25','2024-11-27 07:29:25'),(15,'App\\Models\\User',1,'auth_token','e5d40c1c198ac6d87525f66e6349274aa661ae9c2a4515879e533a413085f938','[\"*\"]','2024-11-27 08:08:50',NULL,'2024-11-27 07:29:45','2024-11-27 08:08:50'),(16,'App\\Models\\User',1,'auth_token','55a9456df9932740e3cbf6ef5b95b9ee9e80e72f1fdecaebed93cdc739ba0574','[\"*\"]',NULL,NULL,'2024-11-27 08:04:32','2024-11-27 08:04:32'),(17,'App\\Models\\User',1,'auth_token','16f8a939332e55e147ef0e4eb2c0f1ce71adbea569054384627bb32afb5fab68','[\"*\"]','2024-11-27 08:49:10',NULL,'2024-11-27 08:09:29','2024-11-27 08:49:10'),(18,'App\\Models\\User',1,'auth_token','c602897f4c04e4f7db12b84e6d791518cbbf998ff2c004f29387c3b6889061ba','[\"*\"]','2024-11-27 09:32:33',NULL,'2024-11-27 08:54:30','2024-11-27 09:32:33'),(19,'App\\Models\\User',1,'auth_token','d41bc8f9c1448a266fa4b0ba1139126c8f8e9ba1fa3df434d00a48e45199568a','[\"*\"]','2024-11-27 10:15:00',NULL,'2024-11-27 09:47:13','2024-11-27 10:15:00'),(20,'App\\Models\\User',1,'auth_token','a583cd5e96acde27959b599359658d3ddb1cbb3f07f0fcab055a9b30b63b8070','[\"*\"]','2024-11-27 10:15:42',NULL,'2024-11-27 10:15:25','2024-11-27 10:15:42'),(21,'App\\Models\\User',6,'auth_token','862a78d8483ba12fb9b925bc49c8e4f2a1d9e26d6833c73f06647ad2c0338342','[\"*\"]','2024-11-27 10:21:26',NULL,'2024-11-27 10:15:54','2024-11-27 10:21:26'),(22,'App\\Models\\User',1,'auth_token','328afeb95f8c32fe87fa37e2f65715d0c489b7aaeb2a130f5fd842eb8fcfd260','[\"*\"]','2024-11-27 10:22:56',NULL,'2024-11-27 10:22:38','2024-11-27 10:22:56'),(23,'App\\Models\\User',6,'auth_token','b3e3bc962dc257d8cc57967431467b055e0b11c3359ae1e3f4a92e0ffff31a5d','[\"*\"]','2024-11-27 10:26:52',NULL,'2024-11-27 10:23:03','2024-11-27 10:26:52'),(24,'App\\Models\\User',6,'auth_token','4bb82010cc669e71071742a30bff9f0e9884f358e6f9a5be68d038b4c0ed2768','[\"*\"]','2024-11-27 10:27:33',NULL,'2024-11-27 10:27:06','2024-11-27 10:27:33'),(25,'App\\Models\\User',6,'auth_token','6a9d2ef4fd70c90f2878ab37a1d515f532fed5db57bf52d82b47f6549445e0e0','[\"*\"]','2024-11-27 10:29:29',NULL,'2024-11-27 10:28:02','2024-11-27 10:29:29'),(26,'App\\Models\\User',6,'auth_token','09581a270efedac997c5218b7f64862c4e416e81412bce0ff7cf89e4cd92d487','[\"*\"]','2024-11-27 10:31:56',NULL,'2024-11-27 10:31:54','2024-11-27 10:31:56'),(27,'App\\Models\\User',6,'auth_token','b293f1f03d52500be8fe205a0c92f19924c463e791ae84c6672eda0fe01aff38','[\"*\"]','2024-11-27 10:36:59',NULL,'2024-11-27 10:36:58','2024-11-27 10:36:59'),(28,'App\\Models\\User',6,'auth_token','c9dfcc2af4a526e3756e4b879275722be9ea82c9b5d9e95b37e0b4dbe23f1fff','[\"*\"]','2024-11-27 10:42:15',NULL,'2024-11-27 10:42:13','2024-11-27 10:42:15'),(29,'App\\Models\\User',1,'auth_token','573e83e4da9dc6cb78ba6bf0629212e9c7726348337f83c46fdc967c7ac8bfa0','[\"*\"]','2024-11-27 10:42:29',NULL,'2024-11-27 10:42:28','2024-11-27 10:42:29'),(30,'App\\Models\\User',6,'auth_token','420f8c0cfc872f006d063a97185adbc27dda262776e7316598dea8f08fea09b1','[\"*\"]','2024-11-27 10:45:37',NULL,'2024-11-27 10:42:39','2024-11-27 10:45:37'),(31,'App\\Models\\User',1,'auth_token','237f4d9580f3e3598a62b9fb3557e8ea508c0c35e0386985426ca078483c1de8','[\"*\"]','2024-11-27 10:45:58',NULL,'2024-11-27 10:45:57','2024-11-27 10:45:58'),(32,'App\\Models\\User',6,'auth_token','1146f4bd26124e8fef49cee75cbd141196aa95bccce2dc3edf8e01f59ab779c0','[\"*\"]','2024-11-27 10:47:21',NULL,'2024-11-27 10:46:23','2024-11-27 10:47:21'),(33,'App\\Models\\User',6,'auth_token','f90b06f08f32dab8af1a2f6d24a3a6cb2f247ec29064df79d93f8c07c963ec7f','[\"*\"]','2024-11-27 11:17:36',NULL,'2024-11-27 11:17:32','2024-11-27 11:17:36'),(34,'App\\Models\\User',1,'auth_token','f66f22d76f314a47aa73dc192f7969f56711550a84b2137ff2db076ab09bd403','[\"*\"]','2024-11-27 11:18:39',NULL,'2024-11-27 11:18:23','2024-11-27 11:18:39'),(35,'App\\Models\\User',6,'auth_token','da4f151a24d62415192033cc441adf87d76b0eba3164b6150658ea4b2bc35ec3','[\"*\"]','2024-11-27 11:19:14',NULL,'2024-11-27 11:19:11','2024-11-27 11:19:14'),(36,'App\\Models\\User',1,'auth_token','40f72588c90d51943027b789a276cb4599932043b864854114c858b7c528faaf','[\"*\"]','2024-11-27 12:02:19',NULL,'2024-11-27 12:02:17','2024-11-27 12:02:19'),(37,'App\\Models\\User',6,'auth_token','e4946658d132c61535376ec1297bf3424746cc7960e222316d526aba0736e004','[\"*\"]','2024-11-27 12:05:09',NULL,'2024-11-27 12:05:05','2024-11-27 12:05:09'),(38,'App\\Models\\User',1,'auth_token','d1b0726063706baef6df1d604dd6007bee89c8e49e47f6fadb8573fd29f0aedc','[\"*\"]','2024-11-27 12:09:49',NULL,'2024-11-27 12:08:11','2024-11-27 12:09:49'),(39,'App\\Models\\User',1,'auth_token','9377a2f86f97919fda7c89cd115e4db1c2a37025c644810f2e49f896409bdd2c','[\"*\"]',NULL,NULL,'2024-11-27 12:39:46','2024-11-27 12:39:46'),(40,'App\\Models\\User',6,'auth_token','97ed091ad0d45d6af42e0c1860db9fa810bc4d5c56ca6b555b6301b9cfc079b9','[\"*\"]',NULL,NULL,'2024-11-27 12:40:16','2024-11-27 12:40:16'),(41,'App\\Models\\User',6,'auth_token','bbbf6cb6e7e8f36ac60f96c5a8c680f082a710afa9521dcdb7413d86ce2c28e2','[\"*\"]',NULL,NULL,'2024-11-27 12:41:03','2024-11-27 12:41:03'),(42,'App\\Models\\User',6,'auth_token','2c90a0b8997f926d9147f17601ec6799565f72a0d9a542846c4a1e252dcdbdf4','[\"*\"]','2024-11-27 12:42:18',NULL,'2024-11-27 12:42:16','2024-11-27 12:42:18'),(43,'App\\Models\\User',1,'auth_token','8843806212bd589a3a4e79fc70d425e03d5d7c74c28e34f6461c2bf01d8bd5d2','[\"*\"]','2024-11-27 13:16:42',NULL,'2024-11-27 12:43:55','2024-11-27 13:16:42'),(44,'App\\Models\\User',6,'auth_token','0af8ecff84bea00d96d8f266047a360303e31b245b54e6ae46cce96fa9795470','[\"*\"]','2024-11-27 13:18:00',NULL,'2024-11-27 13:16:53','2024-11-27 13:18:00'),(45,'App\\Models\\User',1,'auth_token','ac169399c5827759b8cf3cf14696cf041b851b237d53b47a6cd4b6c3b4251797','[\"*\"]','2024-11-27 13:50:00',NULL,'2024-11-27 13:20:41','2024-11-27 13:50:00'),(46,'App\\Models\\User',1,'auth_token','bbfbac2de7d6ecb13b40bfbf8ee2b5dcb106a6d8d27ab1cee709e5cd16937f9b','[\"*\"]','2024-11-27 13:53:06',NULL,'2024-11-27 13:53:01','2024-11-27 13:53:06'),(47,'App\\Models\\User',6,'auth_token','6a5671cfd1359bca307fdfa937716290a8154e49f40b3e27433e1406a894e638','[\"*\"]','2024-11-27 14:01:44',NULL,'2024-11-27 13:53:14','2024-11-27 14:01:44'),(48,'App\\Models\\User',1,'auth_token','1b784fc09c2f17ab8e7d63c11f30e39d9ed9a6d11828774fba891982897660df','[\"*\"]','2024-11-27 14:05:11',NULL,'2024-11-27 14:03:36','2024-11-27 14:05:11'),(49,'App\\Models\\User',19,'auth_token','60e5a5334117f43024bbafbbf6fa8239bfd2450e28b3b0a055e284ddbfa10809','[\"*\"]','2024-11-27 14:06:45',NULL,'2024-11-27 14:05:20','2024-11-27 14:06:45'),(50,'App\\Models\\User',20,'auth_token','7050f03dbba855dbf7fa1b7c97a42a8177e25f2f53ef07cf75a389b1f9fa5a7f','[\"*\"]','2024-11-27 14:08:33',NULL,'2024-11-27 14:07:25','2024-11-27 14:08:33'),(51,'App\\Models\\User',1,'auth_token','d6d533586114cc395a558de2ed6df1c7ab5642cdd2308378a4dc940b12b1743f','[\"*\"]','2024-11-28 06:51:54',NULL,'2024-11-27 14:14:10','2024-11-28 06:51:54'),(52,'App\\Models\\User',6,'auth_token','f46b8843247244305eea4ec501dc71b43721f35c394b3771834dcbcd0ded41db','[\"*\"]',NULL,NULL,'2024-11-27 15:03:26','2024-11-27 15:03:26'),(53,'App\\Models\\User',1,'auth_token','abf50ab13e39b47bab6ce127a5bac3ce4fbdcdc04322a7cdd8f65f6866acf6d8','[\"*\"]','2024-11-28 08:30:15',NULL,'2024-11-28 06:51:58','2024-11-28 08:30:15'),(54,'App\\Models\\User',1,'auth_token','ec5942beffbe4cf4045ec1e715fa1d467c35afb0a80426deaf36dd64505c6a5b','[\"*\"]','2024-11-28 08:30:31',NULL,'2024-11-28 08:30:19','2024-11-28 08:30:31'),(55,'App\\Models\\User',1,'auth_token','fdecc9a5ec4ab08eb69f97471d39066b5d384d4afb4cdf77856a91d07c5c4839','[\"*\"]','2024-11-28 08:31:06',NULL,'2024-11-28 08:30:50','2024-11-28 08:31:06'),(56,'App\\Models\\User',6,'auth_token','185314fe98cd378c9c995a6611251b43fe0e77876dbdbebfb376b84474fd7f09','[\"*\"]',NULL,NULL,'2024-11-28 08:32:00','2024-11-28 08:32:00'),(57,'App\\Models\\User',1,'auth_token','58bc132987e729de1ab82c1542ba5a2583240a0726f510e5d3c2142a3210a856','[\"*\"]','2024-11-28 08:32:40',NULL,'2024-11-28 08:32:24','2024-11-28 08:32:40'),(58,'App\\Models\\User',1,'auth_token','79c3f53449b2f1dd9bf6c6fd862e1cd598b2750e5e8792a1e5017f0c8e4af75f','[\"*\"]','2024-11-28 08:39:30',NULL,'2024-11-28 08:39:12','2024-11-28 08:39:30'),(59,'App\\Models\\User',1,'auth_token','300b833f8071437b87b157091ee9a3ce879b8c07b96e14d8d949830d3909869c','[\"*\"]','2024-11-28 08:41:13',NULL,'2024-11-28 08:40:54','2024-11-28 08:41:13'),(60,'App\\Models\\User',1,'auth_token','a2d92100f8cfad4e5da68c54bb535d53f71828dc3be8b83594286643535b9476','[\"*\"]','2024-11-28 08:43:32',NULL,'2024-11-28 08:42:55','2024-11-28 08:43:32'),(61,'App\\Models\\User',1,'auth_token','5684e8674b627db6925a012d9fc8a98a571208c3ae752c6cfc35c41cf60eb7a7','[\"*\"]','2024-11-28 08:57:03',NULL,'2024-11-28 08:54:09','2024-11-28 08:57:03'),(62,'App\\Models\\User',1,'auth_token','d1918b9b32766736d3de86e74db6f8b0372d64f4428d070949fc5191962bf6de','[\"*\"]','2024-11-28 08:58:47',NULL,'2024-11-28 08:58:23','2024-11-28 08:58:47'),(63,'App\\Models\\User',1,'auth_token','7b72579fdb4894c749d656a7c3095782baa5c83dab2720cf1716ea0304baf9ce','[\"*\"]','2024-11-28 09:11:07',NULL,'2024-11-28 09:10:49','2024-11-28 09:11:07'),(64,'App\\Models\\User',1,'auth_token','aea6623df9738604375bc71712b368610e602accc408fe18124d9852368416bc','[\"*\"]','2024-11-28 09:22:44',NULL,'2024-11-28 09:22:18','2024-11-28 09:22:44'),(65,'App\\Models\\User',1,'auth_token','8d6a9c293b95d4d21844c5d65ebb43286a3101f17190d800f0519446a8c2e513','[\"*\"]','2024-11-28 09:31:22',NULL,'2024-11-28 09:31:03','2024-11-28 09:31:22'),(66,'App\\Models\\User',1,'auth_token','79499163c82ff14500aa0820e73fa1f0bbd06234b96c5518705843a348bb2cfa','[\"*\"]','2024-11-28 09:34:39',NULL,'2024-11-28 09:34:05','2024-11-28 09:34:39'),(67,'App\\Models\\User',1,'auth_token','34f531ad674a727cbc721fa5189318bd298f2847dda3f6883fba5d73460e6173','[\"*\"]','2024-11-28 09:36:35',NULL,'2024-11-28 09:35:59','2024-11-28 09:36:35'),(68,'App\\Models\\User',1,'auth_token','18e9c074ef968eb421e9f1dfc8a8da245c6c333552e8ccbf1d9a9876b993d323','[\"*\"]','2024-11-28 09:39:17',NULL,'2024-11-28 09:38:45','2024-11-28 09:39:17'),(69,'App\\Models\\User',1,'auth_token','9bda9aeed6edc0ac2ef6f377934cc23c904b0465224a7f254daecedccd6fafff','[\"*\"]','2024-11-28 09:40:23',NULL,'2024-11-28 09:39:58','2024-11-28 09:40:23'),(70,'App\\Models\\User',1,'auth_token','178181f6aa8386a33409153eb78d37135201bcf2e0fcff03c86b8048f27f02c0','[\"*\"]','2024-11-28 09:41:53',NULL,'2024-11-28 09:41:30','2024-11-28 09:41:53'),(71,'App\\Models\\User',1,'auth_token','7e0668704d649d11d0a814d4b79e3b8fe95b6218abeabb02ceb87bc8c83d8152','[\"*\"]','2024-11-28 11:33:52',NULL,'2024-11-28 11:33:29','2024-11-28 11:33:52'),(72,'App\\Models\\User',1,'auth_token','7e4be8191a383bfc891fc277228a28508ef6c9a7aefa4c4cd2b75269ccdfe035','[\"*\"]','2024-11-28 11:37:07',NULL,'2024-11-28 11:36:35','2024-11-28 11:37:07'),(73,'App\\Models\\User',1,'auth_token','cb5c1546c09c197a140e9284867fc415470541a709e3f578ed6ea64d89041498','[\"*\"]','2024-11-28 11:40:10',NULL,'2024-11-28 11:37:45','2024-11-28 11:40:10'),(74,'App\\Models\\User',1,'auth_token','67075548adb49be4de098606a6b7d9d3e2337f99d097417d36ca2c656380365c','[\"*\"]','2024-11-28 11:44:11',NULL,'2024-11-28 11:42:25','2024-11-28 11:44:11'),(75,'App\\Models\\User',1,'auth_token','14d0bbd88d5e097057c4396087475009fa167a0d2fb37cdd6dd73ee93a6ebf5d','[\"*\"]','2024-11-28 12:08:47',NULL,'2024-11-28 11:46:22','2024-11-28 12:08:47'),(76,'App\\Models\\User',1,'auth_token','3dc4f2868356973062c83cc4d486215fc2e1638ae968b69889d5f36c0307f41d','[\"*\"]','2024-11-28 12:14:49',NULL,'2024-11-28 12:08:50','2024-11-28 12:14:49'),(77,'App\\Models\\User',1,'auth_token','d811edf09b8da3f5b130e9cfb0c4e80e90ec9235eaa5a033d294dc3572b0789d','[\"*\"]','2024-11-28 14:06:01',NULL,'2024-11-28 12:20:51','2024-11-28 14:06:01'),(78,'App\\Models\\User',1,'auth_token','bf201d199ca2feb4d7b95954a6a41041a65c959295985be425b0722b1319f94a','[\"*\"]',NULL,NULL,'2024-11-28 14:07:51','2024-11-28 14:07:51'),(79,'App\\Models\\User',1,'auth_token','2e84f7a92026d36ca524e696e7b3396be7917a1fab82935fabf032d9b2c1cea2','[\"*\"]',NULL,NULL,'2024-11-28 14:14:16','2024-11-28 14:14:16'),(80,'App\\Models\\User',1,'auth_token','4f217ec2e1b6dc6867f72da583068a8c3cf16e0bb79078dc85d570152cba5902','[\"*\"]',NULL,NULL,'2024-11-28 14:15:25','2024-11-28 14:15:25'),(81,'App\\Models\\User',1,'auth_token','4e05f5855f7a9a48c6750630a7b4a89f715eaca52e2c6518a8101841d90da76e','[\"*\"]',NULL,NULL,'2024-11-28 14:20:07','2024-11-28 14:20:07'),(82,'App\\Models\\User',1,'auth_token','f2a82e37f784ba92145df859011032c6d630acf8e2caa1a8234b2810973ea076','[\"*\"]',NULL,NULL,'2024-11-28 14:22:41','2024-11-28 14:22:41'),(83,'App\\Models\\User',1,'auth_token','1809c1dfefe3a8828b8cf946d65d6689b372a876a61dc707b2fb17b3ff6906bc','[\"*\"]',NULL,NULL,'2024-11-28 14:26:58','2024-11-28 14:26:58'),(84,'App\\Models\\User',1,'auth_token','9a5ff2179f091ce06d20b2c0deb4b39beb324c3983a3cb68b80df93577a01d60','[\"*\"]',NULL,NULL,'2024-11-28 14:27:21','2024-11-28 14:27:21'),(85,'App\\Models\\User',1,'auth_token','9a98ad9322e6db7fde4fffab0c4d4326ac4a668af5b7cce6276db4df6a21af07','[\"*\"]',NULL,NULL,'2024-11-28 14:29:55','2024-11-28 14:29:55'),(86,'App\\Models\\User',1,'auth_token','87f73b253f6f6e1e0eddf676298f720c90d7d44ccca28896f80be03281e91a6c','[\"*\"]',NULL,NULL,'2024-11-28 14:30:08','2024-11-28 14:30:08'),(87,'App\\Models\\User',1,'auth_token','9fc24bcecc7bff3fd294076aa3a8b102384e7a89c8bac6ebf88a95778cacec4f','[\"*\"]',NULL,NULL,'2024-11-28 14:31:13','2024-11-28 14:31:13'),(88,'App\\Models\\User',1,'auth_token','230cf9e039e19a34d89410aeff4db385b55e69ccaabccf5ffe734f0bea91c022','[\"*\"]','2024-11-28 14:33:12',NULL,'2024-11-28 14:33:05','2024-11-28 14:33:12'),(89,'App\\Models\\User',1,'auth_token','41ba6c2c95b9ca003cc1a7949d727e0f7a69faf1a0207d3b0bf3e4973a46f742','[\"*\"]',NULL,NULL,'2024-11-28 14:34:12','2024-11-28 14:34:12'),(90,'App\\Models\\User',1,'auth_token','b47c26f6d684da4d83cb2ef0ca2c88f02658657b78fe51f9b3468525225f2a5c','[\"*\"]',NULL,NULL,'2024-11-28 14:34:59','2024-11-28 14:34:59'),(91,'App\\Models\\User',1,'auth_token','ad7971d2d0b33257231ec6c123088921a9fe62d0491ba6692750626548feb269','[\"*\"]',NULL,NULL,'2024-11-28 14:35:58','2024-11-28 14:35:58'),(92,'App\\Models\\User',1,'auth_token','2659c4d095bc5004fc134fe9029a99fb533d43e82282e4a10ab97079c6f7fa13','[\"*\"]',NULL,NULL,'2024-11-28 14:36:31','2024-11-28 14:36:31'),(93,'App\\Models\\User',1,'auth_token','8cf4028f2587beca4aeec129db116ca50199f17bc798a2f5f936c6ecdc95bc67','[\"*\"]',NULL,NULL,'2024-11-28 14:41:17','2024-11-28 14:41:17'),(94,'App\\Models\\User',1,'auth_token','709991113ccfaae313f9d21e3423985934a4b242e773cf77f7473181a0661395','[\"*\"]',NULL,NULL,'2024-11-28 14:43:20','2024-11-28 14:43:20'),(95,'App\\Models\\User',6,'auth_token','95e8312225b5e1fe4f9c8f0077dc179656d0f7f067fe6a15de98ddf7d13cd25f','[\"*\"]',NULL,NULL,'2024-11-28 14:44:01','2024-11-28 14:44:01'),(96,'App\\Models\\User',1,'auth_token','4eab30a383afef873a7ffd60a35995bbe7295177fc230ca2134286bb2e8eba4b','[\"*\"]','2024-11-28 14:47:16',NULL,'2024-11-28 14:47:07','2024-11-28 14:47:16'),(97,'App\\Models\\User',1,'auth_token','1ed564d259160f86bda30ef1a2070b69183d40c5497c5958797e2a305678f662','[\"*\"]',NULL,NULL,'2024-11-28 14:49:53','2024-11-28 14:49:53'),(98,'App\\Models\\User',1,'auth_token','f475bbe031b216ef3e70be53eb76e9407aa663cfd12d4743cbbd8f885625cf79','[\"*\"]',NULL,NULL,'2024-11-28 14:53:17','2024-11-28 14:53:17'),(99,'App\\Models\\User',1,'auth_token','bb4b9d74f72ccdd6694e9842a1aafeb3e26a999040d38696a47249955dd50d42','[\"*\"]',NULL,NULL,'2024-11-28 14:55:32','2024-11-28 14:55:32'),(100,'App\\Models\\User',1,'auth_token','7bbc769f905b80a654aff6644acddab9814333fb2ca0a3fd97f58a94db973e59','[\"*\"]',NULL,NULL,'2024-11-28 14:56:00','2024-11-28 14:56:00'),(101,'App\\Models\\User',1,'auth_token','1d922e8e87e6d42191bb95ca0c70722e559683975e5f76ce11cf321c194b87c5','[\"*\"]',NULL,NULL,'2024-11-28 14:56:34','2024-11-28 14:56:34'),(102,'App\\Models\\User',1,'auth_token','5b304ac515a6870bcd25c61cb057e31857f67b4726965434f2b177ede8447451','[\"*\"]',NULL,NULL,'2024-11-28 14:58:07','2024-11-28 14:58:07'),(103,'App\\Models\\User',1,'auth_token','ea16a7773dc42b65bed859745babc3882b1e944eb762cb1a1138bdffc82e3537','[\"*\"]',NULL,NULL,'2024-11-28 15:01:37','2024-11-28 15:01:37'),(104,'App\\Models\\User',1,'auth_token','7713b02f06332fc572ab2a97c8856d19b2367de636b53262c1610fd0b73fe7a4','[\"*\"]',NULL,NULL,'2024-11-28 15:02:31','2024-11-28 15:02:31'),(105,'App\\Models\\User',6,'auth_token','565db24cd4c61a469b56f017c270b521ce804ebf2461756c23c7f43452e540c1','[\"*\"]',NULL,NULL,'2024-11-28 15:05:47','2024-11-28 15:05:47'),(106,'App\\Models\\User',1,'auth_token','b2a5106488a1319d9a40c43655673f5bbff412da2568e45f96dcf7c4edcb93de','[\"*\"]',NULL,NULL,'2024-11-28 15:07:48','2024-11-28 15:07:48'),(107,'App\\Models\\User',1,'auth_token','a70a32ed2df8a8742b126d478fcec179cf6bb78f9d43b5ef67b3852e2aedf791','[\"*\"]',NULL,NULL,'2024-11-28 15:08:38','2024-11-28 15:08:38'),(108,'App\\Models\\User',1,'auth_token','5e04be39ac991ea3c350afa81f6cf0ed94c67c2275a44d183d8bd51f5fab7a43','[\"*\"]',NULL,NULL,'2024-11-28 15:10:57','2024-11-28 15:10:57'),(109,'App\\Models\\User',1,'auth_token','d1f9acf83f00a183dc3bb7e8315c6721d5e67a38f66081f1637f004ad17fdc98','[\"*\"]',NULL,NULL,'2024-11-28 15:18:50','2024-11-28 15:18:50'),(110,'App\\Models\\User',1,'auth_token','c642d95edb939df89fd3bfa9a50aa8aba9e95592b9364d05872e7eb1d632d72e','[\"*\"]',NULL,NULL,'2024-11-28 15:19:12','2024-11-28 15:19:12'),(111,'App\\Models\\User',1,'auth_token','8427c1486fd0487c3ab102e4ec8ab12068786d6897c385357ee79ab387aa6e6b','[\"*\"]',NULL,NULL,'2024-11-28 15:21:32','2024-11-28 15:21:32'),(112,'App\\Models\\User',1,'auth_token','c7fe52d4911b08c63ae071ef0fd34923c4772ffb6b8db9e47045bb8f78997b87','[\"*\"]',NULL,NULL,'2024-11-28 15:29:30','2024-11-28 15:29:30'),(113,'App\\Models\\User',1,'auth_token','cb468ba8ef1963ebe85d0d5fa950f2a007cec86f499a856fd3b9c53b8dbf7472','[\"*\"]','2024-11-28 15:32:25',NULL,'2024-11-28 15:31:45','2024-11-28 15:32:25'),(114,'App\\Models\\User',1,'auth_token','169bb658a3c75a33bcc86df9ea2fa2e82715be3c773a317456e46304859bacb0','[\"*\"]','2024-11-28 15:32:49',NULL,'2024-11-28 15:32:39','2024-11-28 15:32:49'),(115,'App\\Models\\User',1,'auth_token','324b7a26cd73beb4569e581c30f50cb95a4ea6c11f00fee27dd72b0f4231c977','[\"*\"]','2024-11-28 15:33:42',NULL,'2024-11-28 15:33:34','2024-11-28 15:33:42'),(116,'App\\Models\\User',1,'auth_token','74cd415589d406703061f6aecd8d184c1e3aadd380b8a1bcc40ce17c77efc24d','[\"*\"]',NULL,NULL,'2024-11-28 15:35:47','2024-11-28 15:35:47'),(117,'App\\Models\\User',1,'auth_token','bd851bd4a4887bff5aaccde0c6b44a7f757c59054ce594f75fec47331285d685','[\"*\"]',NULL,NULL,'2024-11-28 15:36:23','2024-11-28 15:36:23'),(118,'App\\Models\\User',1,'auth_token','79e2e008121c4f003d9d7d7340c23be96f58f0e07dcd9a83af1148c8b2229db5','[\"*\"]',NULL,NULL,'2024-11-28 15:37:18','2024-11-28 15:37:18'),(119,'App\\Models\\User',1,'auth_token','da8fef9a59504d27c3461f37ab1b1101b28e7dd9b9c4f79675279736f67b1c94','[\"*\"]',NULL,NULL,'2024-11-28 15:37:44','2024-11-28 15:37:44'),(120,'App\\Models\\User',1,'auth_token','77f8653c572f7bef83a04c574020e85b670ae482a6342403d4294112f9f06530','[\"*\"]',NULL,NULL,'2024-11-28 15:38:35','2024-11-28 15:38:35'),(121,'App\\Models\\User',1,'auth_token','4fb5fde8de8939b734c8fb14eda4e4e7c0ff7465b937aea542a2d7cf96bd52f5','[\"*\"]',NULL,NULL,'2024-11-28 15:39:32','2024-11-28 15:39:32'),(122,'App\\Models\\User',1,'auth_token','1001c9b0a3ef97ce5996f3cbaeeb768cab3e19cece11375b5a76d3d42817830a','[\"*\"]',NULL,NULL,'2024-11-28 15:41:08','2024-11-28 15:41:08'),(123,'App\\Models\\User',1,'auth_token','dcafa9cbb904c51106be4fb255348722e7c1005ce5f1237f1c89f4830f93563c','[\"*\"]',NULL,NULL,'2024-11-28 15:42:47','2024-11-28 15:42:47'),(124,'App\\Models\\User',1,'auth_token','6e5ec461d50b191d40e824ea1e210b59444fea3c5c093d78651d139a6b84d48a','[\"*\"]','2024-11-28 15:43:03',NULL,'2024-11-28 15:42:58','2024-11-28 15:43:03'),(125,'App\\Models\\User',6,'auth_token','e7e34488988c04a215555ae394ba32d4d09786c34397d6e212d148fb9ec0c484','[\"*\"]','2024-11-28 16:14:20',NULL,'2024-11-28 15:43:17','2024-11-28 16:14:20'),(126,'App\\Models\\User',19,'auth_token','c15e414a12bf7f3799e8c8d167768f4aa489902d2fb9af2f4cd970d9e428c40c','[\"*\"]','2024-11-28 16:16:18',NULL,'2024-11-28 16:15:53','2024-11-28 16:16:18'),(127,'App\\Models\\User',1,'auth_token','9847413e63a752f8a685df5d16d1265b03efd5f460da7cebfed935b226dcaea2','[\"*\"]',NULL,NULL,'2024-11-28 16:16:31','2024-11-28 16:16:31'),(128,'App\\Models\\User',6,'auth_token','fd0fc64483f5cf8e502c0e29a235afaacd328f5a3acffbf45d39e22d383764ae','[\"*\"]','2024-11-28 16:29:54',NULL,'2024-11-28 16:16:44','2024-11-28 16:29:54'),(129,'App\\Models\\User',6,'auth_token','ae62c8c9c5a0a0843a4db4bf2ec9ac1b22a2ded97bd2097d5dadc30eeb2c42c1','[\"*\"]','2024-11-28 16:30:34',NULL,'2024-11-28 16:30:01','2024-11-28 16:30:34'),(130,'App\\Models\\User',6,'auth_token','cc6d6cf499d78d7c8df37f715176d39ecc57212ed2d70c336fbd55aab80263b3','[\"*\"]','2024-11-28 16:35:15',NULL,'2024-11-28 16:30:46','2024-11-28 16:35:15'),(131,'App\\Models\\User',6,'auth_token','9302db63558c9e065ba697a8d232192506af34e5e18dd6c9278b5458a0fe07b3','[\"*\"]','2024-11-28 16:37:07',NULL,'2024-11-28 16:35:20','2024-11-28 16:37:07'),(132,'App\\Models\\User',6,'auth_token','69a0202d59764efecdf7b2d10632a475124ab085602ef1555ab441487cbdcfb0','[\"*\"]',NULL,NULL,'2024-11-28 16:37:28','2024-11-28 16:37:28'),(133,'App\\Models\\User',6,'auth_token','c5dd06e168168791b079874fcf7a3801dbfe010da7585a06fc1bc2eabf002ff0','[\"*\"]','2024-11-28 16:41:17',NULL,'2024-11-28 16:38:45','2024-11-28 16:41:17'),(134,'App\\Models\\User',6,'auth_token','608a424c2a5be206da8ee2a237ae976d07b2949bf3af43bfb740889506f93e17','[\"*\"]','2024-11-28 16:42:02',NULL,'2024-11-28 16:41:25','2024-11-28 16:42:02'),(135,'App\\Models\\User',6,'auth_token','c23e715d35d0756bbc576496efc75bbf50682a6c751ed0f8d2b6d6a9eeb6c0bc','[\"*\"]','2024-11-28 16:42:11',NULL,'2024-11-28 16:42:08','2024-11-28 16:42:11'),(136,'App\\Models\\User',6,'auth_token','e4da5be724a6d1b2c11bfe6a86a6cdb4d5ca4a0fb2da6e55052d3639855bf5e0','[\"*\"]','2024-11-28 16:42:27',NULL,'2024-11-28 16:42:24','2024-11-28 16:42:27'),(137,'App\\Models\\User',6,'auth_token','7bf1e8fb21f9525548cae6ee1a940fe06654b0596b990f51da192584cb26c4c0','[\"*\"]','2024-11-28 16:42:36',NULL,'2024-11-28 16:42:33','2024-11-28 16:42:36'),(138,'App\\Models\\User',1,'auth_token','018b754f55e04b2406106cab7ae118f98db44bd3e5342f63945a80073c0aee07','[\"*\"]','2024-11-28 16:42:46',NULL,'2024-11-28 16:42:42','2024-11-28 16:42:46'),(139,'App\\Models\\User',19,'auth_token','ce9f563753a3d97a1a7763bc8caf9c620aa35ec9cb1a20e7486452067516d424','[\"*\"]','2024-11-28 16:43:01',NULL,'2024-11-28 16:42:58','2024-11-28 16:43:01'),(140,'App\\Models\\User',1,'auth_token','664bb324639321d478db7812f29e3a8a29cc3f45734589d0665471a5a6bac4b0','[\"*\"]','2024-11-28 16:46:33',NULL,'2024-11-28 16:46:07','2024-11-28 16:46:33'),(141,'App\\Models\\User',1,'auth_token','02b38eebbc6b919e14d3dbda9862841f9ed76e9c38be8aebdb713d1f0462f183','[\"*\"]','2024-11-28 16:46:47',NULL,'2024-11-28 16:46:43','2024-11-28 16:46:47'),(142,'App\\Models\\User',19,'auth_token','1cec9c335a655d7e69f271c592411ff6cf4c6f1ca155cda8e8956da884807fd9','[\"*\"]','2024-11-28 16:46:56',NULL,'2024-11-28 16:46:54','2024-11-28 16:46:56'),(143,'App\\Models\\User',6,'auth_token','b901b61e2b857d9e082920f10ae82eb75586a30229d0596a3feb9e30db5f3a6f','[\"*\"]','2024-11-28 16:47:08',NULL,'2024-11-28 16:47:06','2024-11-28 16:47:08'),(144,'App\\Models\\User',6,'auth_token','108dbcfada526f1c9b92ddb6d46ad3868ff8bac3836f298dc1c56379d095493c','[\"*\"]','2024-11-28 18:54:18',NULL,'2024-11-28 18:54:15','2024-11-28 18:54:18'),(145,'App\\Models\\User',1,'auth_token','10dd68bdcaf216689501b7d20aaba7db1e848ef1a4f8b64e2dd342be9b85cda4','[\"*\"]',NULL,NULL,'2024-11-28 18:56:27','2024-11-28 18:56:27'),(146,'App\\Models\\User',1,'auth_token','c98655d8a73ebff75703e38ad7eea8c902e067c62e6bf643f5e4814d6165dbd5','[\"*\"]','2024-11-29 08:40:34',NULL,'2024-11-29 07:46:59','2024-11-29 08:40:34'),(147,'App\\Models\\User',6,'auth_token','bb4722243aa9d96f9047cdc3d4b5aa4cac21175d254ab0af288d3b1737832763','[\"*\"]',NULL,NULL,'2024-11-29 07:55:28','2024-11-29 07:55:28'),(148,'App\\Models\\User',6,'auth_token','39fcdd17ec921e39010f395dbf300534100821694f47f89e932969d7b8cd0543','[\"*\"]','2024-11-29 08:11:41',NULL,'2024-11-29 08:11:39','2024-11-29 08:11:41'),(149,'App\\Models\\User',1,'auth_token','8f45ace3f8de894c9fbe4f0c91f7d65bf92a2012c6394c84fc093213e55a8eb3','[\"*\"]','2024-11-29 12:12:44',NULL,'2024-11-29 08:49:45','2024-11-29 12:12:44'),(150,'App\\Models\\User',6,'auth_token','e4b7fbb57bcbdbbccf3c80e3c8a8b6a7aa4d247c5f1bc6a1bb3aefb0aea8d190','[\"*\"]',NULL,NULL,'2024-11-29 11:57:48','2024-11-29 11:57:48'),(151,'App\\Models\\User',6,'auth_token','cc9436097f39b8b6a3db50a5bd5ca7075b8d94739869e08324b0b6d25407d663','[\"*\"]','2024-11-29 11:59:37',NULL,'2024-11-29 11:59:07','2024-11-29 11:59:37'),(152,'App\\Models\\User',1,'auth_token','48a683bb7fde92a48aa215fc8c597270a75d22eb0e884669bd3d282d7c9a220c','[\"*\"]','2024-11-29 13:11:05',NULL,'2024-11-29 12:54:23','2024-11-29 13:11:05'),(153,'App\\Models\\User',1,'auth_token','19896b3e719fa79f0350f9bd8a4e7423aac6e52609d466cf697428431e1599ac','[\"*\"]','2024-11-29 14:29:15',NULL,'2024-11-29 13:48:24','2024-11-29 14:29:15'),(154,'App\\Models\\User',6,'auth_token','770d3df54ed7de8d3329d8fbd839911063b6c9f5e7a8c0ccf2a68c44c5beeb96','[\"*\"]','2024-11-29 14:29:55',NULL,'2024-11-29 14:16:40','2024-11-29 14:29:55'),(155,'App\\Models\\User',1,'auth_token','e51a3d6143fd05624c0f39dc346fd4a812fd2d51934537b720777de294ae4b9d','[\"*\"]','2024-11-29 15:24:08',NULL,'2024-11-29 14:30:38','2024-11-29 15:24:08'),(156,'App\\Models\\User',1,'auth_token','f8ce6057cd4af068a5d89c384461377978597983a4c9cee819e3ebf19f8127a2','[\"*\"]','2024-11-29 15:18:30',NULL,'2024-11-29 14:30:43','2024-11-29 15:18:30'),(157,'App\\Models\\User',1,'auth_token','f95e78c0d1e87dff024b1e5ebeee93c00a9f3a377d1c5c23d7fb097b54a7c202','[\"*\"]','2024-12-01 09:18:16',NULL,'2024-11-29 15:24:14','2024-12-01 09:18:16'),(158,'App\\Models\\User',1,'auth_token','6465ef2a0c60c395f83b2b47741091d8a30e28e75eecd87609d8c62d36372ee9','[\"*\"]','2024-12-01 09:45:04',NULL,'2024-12-01 09:18:21','2024-12-01 09:45:04'),(159,'App\\Models\\User',1,'auth_token','9ece5bb6004c4afd68321b240186347b058a9d97e1c1e39055d1945da9e1d077','[\"*\"]','2024-12-01 10:04:14',NULL,'2024-12-01 09:45:10','2024-12-01 10:04:14'),(160,'App\\Models\\User',1,'auth_token','73732347ccfc0a859c57ee2462d46091c66709d461e5fb27ef5423354018c067','[\"*\"]',NULL,NULL,'2024-12-01 10:36:40','2024-12-01 10:36:40'),(161,'App\\Models\\User',1,'auth_token','43f64e7ecc727483470ecf8b15ad49ab21a2c1432f40381a3c42e16787896c3d','[\"*\"]','2024-12-01 11:04:48',NULL,'2024-12-01 10:37:21','2024-12-01 11:04:48'),(162,'App\\Models\\User',1,'auth_token','5d7daf4f689a6b126433b9fd0076c823e7ee1fbb9cba92f07bf4a69389bb6cb1','[\"*\"]','2024-12-01 11:27:57',NULL,'2024-12-01 11:11:07','2024-12-01 11:27:57'),(163,'App\\Models\\User',6,'auth_token','33c97ab56bc348f9070d48338c372d9ddfbeef5fd0e1056210a8703c31b95dcd','[\"*\"]','2024-12-01 11:28:24',NULL,'2024-12-01 11:28:21','2024-12-01 11:28:24'),(164,'App\\Models\\User',1,'auth_token','65c616143146b6a11d3f442a13f54b781d586750ff2065907ab87d07630c0142','[\"*\"]','2024-12-01 16:05:46',NULL,'2024-12-01 11:28:56','2024-12-01 16:05:46'),(165,'App\\Models\\User',6,'auth_token','09fcd28f2d89ab7f6f493f58048b1a419d5c7ee3b1868e0f3a66c2623008e6bf','[\"*\"]','2024-12-01 16:06:03',NULL,'2024-12-01 16:06:01','2024-12-01 16:06:03'),(166,'App\\Models\\User',1,'auth_token','4942017fd846ed4c03a88306a5f2e920a04fa8aefad6eaeeebe41d7c7cac7f77','[\"*\"]','2024-12-03 15:22:47',NULL,'2024-12-01 16:06:27','2024-12-03 15:22:47'),(167,'App\\Models\\User',1,'auth_token','5b87c373fdd60a6e06da813c1c45dad2166ea6dda2877fc8b16b5504c51338cf','[\"*\"]','2024-12-03 16:43:44',NULL,'2024-12-03 15:24:26','2024-12-03 16:43:44'),(168,'App\\Models\\User',1,'auth_token','a7297a360dfdc4201d637793761000a6b10c269f64e11aaaa3233baaf512e5e5','[\"*\"]','2024-12-03 16:47:12',NULL,'2024-12-03 16:43:53','2024-12-03 16:47:12'),(169,'App\\Models\\User',1,'auth_token','79e4f3a23e59864c465e8097b4eb85a74d6f4577e89238a48c787df8378b69ab','[\"*\"]',NULL,NULL,'2024-12-03 17:49:44','2024-12-03 17:49:44'),(170,'App\\Models\\User',1,'auth_token','5c470330ea364ea2ea4231cffd393e268127d5ea0e864cc31cdf3c869e1351a4','[\"*\"]','2024-12-16 09:17:10',NULL,'2024-12-06 12:38:57','2024-12-16 09:17:10'),(171,'App\\Models\\User',1,'auth_token','c295e9bc6679b006b07ba138a6623ca11b86bc1179b6c19d3a995c9e93142ae7','[\"*\"]','2024-12-16 12:28:05',NULL,'2024-12-16 12:24:05','2024-12-16 12:28:05'),(172,'App\\Models\\User',22,'auth_token','259bd6f0a2c729c56ceb6e8a8246a2255349c9c987a57cfe20af4d27ec64eb99','[\"*\"]','2025-01-28 10:05:29',NULL,'2024-12-30 13:12:16','2025-01-28 10:05:29'),(174,'App\\Models\\User',1,'auth_token','d72800a409fc13d37a4c0c87632ea07884bc904031bd9257b6a747dd5580d59c','[\"*\"]','2025-02-17 10:18:33',NULL,'2025-02-10 15:10:14','2025-02-17 10:18:33'),(178,'App\\Models\\User',1,'auth_token','e8707192f5f7ac6d3c808cad56f1ec9d4b4cb3438ada994ac2d6613807785f20','[\"*\"]','2025-03-03 13:45:17',NULL,'2025-02-26 15:09:43','2025-03-03 13:45:17');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `replies`
--

DROP TABLE IF EXISTS `replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `replies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `replies_comment_id_foreign` (`comment_id`),
  KEY `replies_user_id_foreign` (`user_id`),
  CONSTRAINT `replies_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `replies`
--

LOCK TABLES `replies` WRITE;
/*!40000 ALTER TABLE `replies` DISABLE KEYS */;
INSERT INTO `replies` VALUES (1,'xcbcxb',1,1,'2024-11-27 08:46:36','2024-11-27 08:46:36',NULL),(2,'svddgfb',2,1,'2024-11-27 08:47:41','2024-11-27 08:47:41',NULL),(3,'et sa reponse',3,1,'2024-11-27 08:50:41','2024-11-27 08:50:41',NULL),(4,'reponse',4,1,'2024-11-27 08:55:50','2024-11-27 08:55:50',NULL),(5,'xcv',5,1,'2024-11-27 09:58:35','2024-11-27 09:58:35',NULL),(6,'wcxwccx',6,1,'2024-11-27 10:12:51','2024-11-27 10:12:51',NULL),(7,'test de reponse',7,1,'2024-11-27 10:46:13','2024-11-27 10:46:13',NULL),(8,'QSDFGBNAZSEDRFGHN',8,1,'2024-11-27 11:18:53','2024-11-27 11:18:53',NULL),(9,'xcvb',9,20,'2024-11-27 14:08:48','2024-11-27 14:08:48',NULL),(10,'sdfdf',10,1,'2024-11-28 11:47:09','2024-11-28 11:47:09',NULL),(11,'xdvxbf',11,6,'2024-11-28 16:15:27','2024-11-28 16:15:27',NULL),(12,'qswdxcv',12,6,'2024-11-28 16:51:52','2024-11-28 16:51:52',NULL),(13,'test reponse today',13,1,'2024-11-29 13:01:27','2024-11-29 13:01:27',NULL),(14,'wxv',14,1,'2024-12-01 09:45:42','2024-12-01 09:45:42',NULL),(15,'fvgdfgv',15,1,'2024-12-01 09:59:26','2024-12-01 09:59:26',NULL),(16,'dxfcvb',17,1,'2024-12-01 16:12:18','2024-12-01 16:12:18',NULL),(17,'merci',19,1,'2024-12-16 12:25:01','2024-12-16 12:25:01',NULL);
/*!40000 ALTER TABLE `replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reply_activities`
--

DROP TABLE IF EXISTS `reply_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reply_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity_comment_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reply_activities_activity_comment_id_foreign` (`activity_comment_id`),
  KEY `reply_activities_user_id_foreign` (`user_id`),
  CONSTRAINT `reply_activities_activity_comment_id_foreign` FOREIGN KEY (`activity_comment_id`) REFERENCES `activity_comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reply_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reply_activities`
--

LOCK TABLES `reply_activities` WRITE;
/*!40000 ALTER TABLE `reply_activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `reply_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_catalogs`
--

DROP TABLE IF EXISTS `training_catalogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_catalogs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dendreo_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validity_duration` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `training_catalogs_dendreo_id_unique` (`dendreo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_catalogs`
--

LOCK TABLES `training_catalogs` WRITE;
/*!40000 ALTER TABLE `training_catalogs` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_catalogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trainings`
--

DROP TABLE IF EXISTS `trainings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trainings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dendreo_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validity_duration` int DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=265 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trainings`
--

LOCK TABLES `trainings` WRITE;
/*!40000 ALTER TABLE `trainings` DISABLE KEYS */;
INSERT INTO `trainings` VALUES (1,'','Fondamentaux de la Sûreté Aéroportuaire',NULL,NULL,NULL,NULL,'2025-01-27 09:59:14','2025-01-27 09:59:14'),(2,'','Contrôle d\'Accès en Zone Réservée',NULL,NULL,NULL,NULL,'2025-01-27 10:02:26','2025-01-27 10:02:26'),(3,'','Formation Active - Sécurité Avancée',NULL,NULL,NULL,NULL,'2025-01-27 14:08:44','2025-01-27 14:08:44'),(4,'','Formation Proche Expiration - Gestion de Crise',NULL,NULL,NULL,NULL,'2025-01-27 14:09:54','2025-01-27 14:09:54'),(5,'','Formation Expirée - Procédures Initiales',NULL,NULL,NULL,NULL,'2025-01-27 14:13:49','2025-01-27 14:13:49'),(6,'19','11.2.7 du Regl n°EU 2015/1998: Formation des personnes nécessitant une sensibilisation à la sûreté générale','§11.2.7 Initiale Regl. EU2015/1998 REM',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(7,'21','11.2.4.du Regl. EU2015/1998 : Formation spécifique des personnes supervisant directement des personnes effectuant les contrôles de sûreté 11.2.3.1 à 11.2.3.5. \"SUPERVISEUR -AGENT DE SURETE\"','11.2.4 ADS',0,'Superviseur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(8,'23','Formation Responsable Sûreté','Responsable sûreté',0,'Encadrement','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(9,'24','CQP Agent de Sureté Aéroportuaires Typologie 10','',0,'CQP','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(10,'25','Formation Périodique Imagerie RX et EDS simple vue multi-vues (11.4.1 du règlement EU2015/1998) – FPI Typologie 1','FPI Typologie 1',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(11,'26','Agent de sûreté typologie 1 Analyse d\'images sur simulateur Initiale','Typo 1 Analyse images Init.',0,'Formation initiale','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(12,'27','CQP Agent de Sûreté Aéroportuaires Typologie 2','',0,'CQP','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(13,'28','Agent de sûreté typologie 2 Analyse d\'images sur simulateur Initiale','Agent de sûreté T2 (analyse images) - Initiale',0,'Formation initiale','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(14,'29','CQP Agent de Sûreté Aéroportuaires Typologie 3','',0,'CQP','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(15,'30','CQP Agent de Sûreté Aéroportuaires Typologie 4','',0,'CQP','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(16,'32','Agent de sûreté typologie 4 Analyse d\'images sur simulateur initiale','TYPO 4 Analyse images Init.',0,'Formation initiale','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(17,'33','CQP Agent de Sûreté Aéroportuaires Typologie 5','',0,'CQP','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(18,'34','Agent de sûreté typologie 6','',0,'Formation initiale','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(19,'35','CQP Agent de Sûreté Aéroportuaires Typologie 7','CQP ASA TYPO 7',0,'CQP','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(20,'36','Agent de sûreté typologie 7 Analyse d\'images sur simulateur Initiale','Typo 7 Analyse images Init.',0,'Formation initiale','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(21,'37','IATA DGR 10','',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(22,'38','IATA DGR 12 Marchandises Dangereuses IATA - Filtrage des passagers, bagages cabine et soute, fret et courrier','IATA MD Cat. 12',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(23,'39','IATA DGR 8 a','',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(24,'40','IATA DGR 8 b','',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(25,'41','IATA DGR 8 c','IATA DGR 8 c',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(26,'42','IATA DGR 9','',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(27,'43','Marchandises Dangereuses - Cat.1-3-6 – Base OACI-IATA « Agent de Fret aérien » Acheminement, préparation, stockage, acceptation des marchandises dangereuses- Base - Excluant la classe 7 - Radioactifs','IATA DGR 1-3-6 hors RRY - Initiale',24,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(28,'45','PRAP – Gestes et postures','',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(29,'46','Sauveteur Secouriste du Travail – SST initiale','SST - Initiale',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(30,'47','Sensibilisation à la Radioprotection','',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(31,'48','Palettisation et arrimage - Initiale','',0,'MÉTIERS_CARGO',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(32,'49','Palettisation et arrimage - Recyclage','',0,'MÉTIERS_CARGO',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(33,'50','ADR 1.3 – Sensibilisation Transport Routier MD','Sensi ADR 1.3',0,'Adr 1.3','MARCHANDISES DANGEREUSES','2025-02-18 08:12:06','2025-02-18 08:12:06'),(34,'51','DCS Altea passage','',0,'MÉTIERS_CARGO',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(35,'56','11.2.3.8. + Compl. 11.2.6.2 du Regl. EU2015/1998  : Formation des personnes qui mettent en oeuvre la vérification de concordance entre passagers et bagages + partie TCA','11.2.3.8 + 11.2.6.2 (TCA)',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(36,'58','11.2.6.2 du Regl. EU2015/1998: Formation des personnes accédant la ZSAR sans escorte E-LEARNING','§11.2.6.2 Regl. 2015/1998 E-learning',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(37,'59','11.2.3.9 CCo Regl. EU2015/1998: Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF - Variante C','11.2.3.9 Chargeur Connu',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(38,'60','SensibilisatIon 1.3 ADR – Expédition par route de marchandises dangereuses','Sensibilisation ADR 1.3 Initiale',24,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(39,'61','TSA – Programme de sûreté type compagnie aérienne– Module Formation Passage','TSA - Module Passage',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(40,'62','§ Formation \"Clean and Search\" US - Module Compagnies aériennes NORWEGIAN - Validation regl. TSA','Clean&Search TSA',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(41,'64','§ 11.2.6.2 du Regl européen n°n°EU 2015/1998: Complément TCA aux modules de formation 11.2.3.8, 11.2.3.9 et 11.2.3.10','Compl. TCA 11.2.6.2',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(42,'65','Sauveteur Secouriste du Travail – SST Recyclage','SST - Recyclage',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(43,'66','ADR_1.3 – Expédition par route de Marchandises Dangereuses','ADR 1.3 Expédition par route de Marchandises Dangereuses',0,'Adr 1.3','MARCHANDISES DANGEREUSES','2025-02-18 08:12:06','2025-02-18 08:12:06'),(44,'69','CQP Agent de Sureté Aéroportuaires Typologie 10','',0,'CQP','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(45,'70','Formation Périodique Imagerie FPI T10 -RX et EDS simple vue multi-vues (11.4.1 du règlement EU2015/1998)','FPI Typologie 10',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(46,'71','Formation Sûreté Périodique Typologie 1 (T1) du Règlement EU2015/1998 §11.2.3.2: Formation Périodique Hors Imagerie des personnes qui effectuent l’inspection/ le filtrage du fret et du courrier et/ou vérification physique du fret et du courrier','FPHI Typologie 1 (hors imagerie)',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(47,'72','Formation Sûreté Périodique Typologie 4 (T4) du Règlement EU2015/1998 §11.2.3.1 IFBS: Formation Périodique Hors Imagerie des personnes qui effectuent l’inspection/ le filtrage des bagages de soute','FPHI Typologie 4',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(48,'74','Formation Sûreté Périodique Typologie 10 (T10) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.2 §11.2.3.3 §11.2.3.4 §11.2.3.5 : Formation Périodique Hors Imagerie :','FPHI Typologie 10 (hors imagerie)',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(49,'75','Formation Sûreté Périodique Typologie 2 (T2) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.2 §11.2.3.3 §11.2.3.4 §11.2.3.5 : Formation Périodique Hors Imagerie :','FPHI Typologie 2 (hors imagerie)',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(50,'76','§ 11.2.2 du Reg. EU2015/1998 Formation de base des personnes qui exécutent les tâches énumérées au point 11.2.4 du règl. EU2015/1998: personnes qui supervisent directement les personnes qui effectuent les contrôles de sûreté 11.2.3.6. à 11.2.3.10 (\"Superv','11.2.2/11.2.4 Contributeur Amarante',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(51,'77','Formation Facteurs Humains','Facteurs humains',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(52,'78','Marchandises Dangereuses - Cat.1-3-6 – Base OACI-IATA « Agent de Fret aérien » Acheminement, préparation, stockage, acceptation des marchandises dangereuses- Base - Incluant la classe 7 - Radioactifs','IATA DGR 1-3-6 Incluant RRY - Initiale',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(53,'79','IATA DGR Cat. 5 Base & Recyclage - Hors RRY','IATA DGR 5 - Excluant RRY',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(54,'80','11.2.5 MANAGEMENT DE LA SURETE: Formation spécifique destinée aux personnes assumant une responsabilité générale au niveau national ou local en respect de toutes les dispositions légales applicables dans d\'un programme de sûreté et d\'Assurance Qualité','11.2.5 Management de la sûreté',0,'Encadrement','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(55,'81','Marchandises Dangereuses - Cat.1-3-6 – Recyclage OACI-IATA « Agent de Fret aérien » Acheminement, préparation, stockage, acceptation des marchandises dangereuses- Recyclage - Excluant la classe 7 - Radioactifs','IATA DGR 1-3-6 hors RRY - Recyclage',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(56,'82','Marchandises Dangereuses - Cat.1-3-6 – Recyclage OACI-IATA « Agent de Fret aérien » Acheminement, préparation, stockage, acceptation des marchandises dangereuses- recyclage - INcluant la classe 7 - Radioactifs','IATA DGR 1-3-6 Incluant RRY - Recyclage',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(57,'84','Formation de Formateur Qualifié en Sûreté de l\'Aviation Civile','Formation de formateur qualifié sûreté',0,'Encadrement','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(58,'85','Formation de Formateur Qualifié en Sûreté de l\'Aviation Civile (Excl. §11.2.2) - Qualification pour les modules §11.2.6.2 et §11.2.3.9','Formation de formateur qualifié Sûreté (Excl. 11.2.2.) Module 11.2.6.2/11.2.3.9',0,'Encadrement','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(59,'86','§ Formation \"Clean and Search\" US - Module Compagnies aériennes CORSAIR INTERNATIONAL - Validation regl. TSA','Clean&Search TSA Corsair',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(60,'88','11.2.3.7 : Formation des personnes en charge de la protection des aéronefs','11.2.3.7 E LEARNING',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(61,'89','11.2.3.3. CV/FM Initiale du Regl. EU2015/1998  : inspection/filtrage du courrier et du matériel des transporteurs aériens, des approvisionnements de bord et des fournitures d\'aéroport, limitée au Contrôle Visuel et à la Fouille Manuelle','11.2.3.3. CV FM - Initiale si 11.2.3.10 valide',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(62,'91','Sécurité en Piste (piéton) et Système de gestion du risque','Sécurité Piste/Piéton - Initiale',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(63,'92','Formation Marchandises Dangereuses Réglementées Catégorie IATA 1 - UN1845 - Recyclage','IATA 1 - UN1845 - Recyclage',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(64,'93','EPI – Équipier de Première Intervention et Manipulation des extincteurs','EPI - Manipulation extincteurs  - Recyclage',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(65,'95','Formation Sûreté Périodique Typologie 7 (T7) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.2 §11.2.3.3 §11.2.3.4 §11.2.3.5 : Formation Périodique Hors Imagerie :','FPHI Typologie 7 HORS IMAGERIE',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(66,'96','Formation Sûreté Initiale Typologie 7 (T7) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.3 §11.2.3.4 §11.2.3.5','Formation Typologie 7 - Initiale',0,'Formation initiale','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(67,'97','Formation de Formateur Qualifié en Sûreté de l\'Aviation Civile (Excl. §11.2.2) - Qualification pour les module §11.2.3.8','Formation de formateur qualifié Sûreté (Excl. 11.2.2.) Module 11.2.3.8',0,'Encadrement','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(68,'98','Permis Trafic CDG - Pratique','',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(69,'99','2020 Recycling Training Course Sûreté PNT Compagnie Aérienne - Fouille aéronef  §11.2.3.6 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs','2020 RTC PNT CORSAIR INTERNATIONAL §11.2.3.6',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(70,'100','11.2.3.9 E LEARNING  Formation des personnes qui effectuent sur le fret et les envois postaux des contrôles de sûreté, autres que l\'inspection filtrage','11.2.3.9 E learning',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(71,'101','EPI – 7H Équipier de Première Intervention et Manipulation des extincteurs','EPI - Manipulation extincteurs - Initiale',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(72,'102','Permis Trafic CHARLES DE GAULLE','Permis T CDG',24,'PERMIS TRAFIC',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(73,'103','Permis Trafic ORLY (EA)','Permis T ORLY',0,'PERMIS TRAFIC',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(74,'104','Permis Trafic ORLY - Partie Pratique (EA)','Permis T Pratique ORLY',0,'PERMIS TRAFIC',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(75,'105','Permis Trafic ROISSY CDG- Partie Pratique','Permis T Pratique CDG',0,'PERMIS TRAFIC',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(76,'106','Formation Sûreté sur le Tas prévue au 11.2.1.2. du Regl. EU2015/1998 : Module §11.2.3.3. CV/FM','Formation sur le Tas 11.2.3.3 CV/FM',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(77,'107','Recycling Training Course Sûreté PN Compagnie Aérienne - Gestion du risque terroriste et maitrise PAXI','RTC PNT CORSAIR INTERNATIONAL -Risque Terroriste - Maitrise PAXI',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(78,'108','11.2.3.9+11.2.3.10 Regl.EU2015/1998: Formation des personnes qui effectuent sur le fret, les envois postaux,le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté,hors IF','11.2.3.9 + 11.2.3.10',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(79,'109','11.2.3.8. Formation des personnes qui mettent en oeuvre la vérification de concordance entre passagers et bagages','11.2.3.8 Concordance Bagages',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(80,'110','Recycling Training Course Sûreté PNC Compagnie Aérienne - Fouille aéronef  §11.2.3.6 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs','RTC PNC CORSAIR INTERNATIONAL §11.2.3.6',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(81,'111','MISE A NIVEAU REM CONSEIL','Rc',0,'QUALITÉ DE VIE AU TRAVAIL',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(82,'112','SENSIBILISATION A LA RADICALISATION - 7h00','',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(83,'114','11.2.3.10 Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage','11.2.3.10-INITIALE',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(84,'116','Formation Transport Routier Matières Dangereuses: ADR-RID-IMDG-ADN','ADR 1.3 RID-IMDG-ADN',0,'Adr 1.3','MARCHANDISES DANGEREUSES','2025-02-18 08:12:06','2025-02-18 08:12:06'),(85,'119','Formation initiale Typologie 9 - §11.2.3.5.Regl. EU2015/1998 : contrôles d’accès à un aéroport et opérations de surveillance et de patrouille.','Formation Typologie 9 - Initiale',0,'Formation initiale','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(86,'120','Marchandises Dangereuses - Cat.1 Piles & Batteries Lithium Base OACI-IATA \"Expéditeurs\" Acheminement des marchandises dangereuses','DGR 1 Piles & Batteries Lithium',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(87,'121','11.2.7 du Regl n°EU 2015/1998: Formation des personnes nécessitant une sensibilisation à la sûreté générale - Variante transporteurs de fret - courrier','11.2.7 Variant AHA/CCo - Initiale',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(88,'122','11.2.7 du Regl n°EU 2015/1998: Formation des personnes nécessitant une sensibilisation à la sûreté générale - Variante fournitures d\'aéroports','11.2.7 Variante fournitures d\'aéroports - Initiale',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(89,'123','11.2.7 du Regl n°EU 2015/1998: Formation des personnes nécessitant une sensibilisation à la sûreté générale - Variante approvisionnements de bord','11.2.7 Variante approvisionnements de bord - Initiale',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(90,'124','SENSIBILISATION A LA RADICALISATION ET COMPREHENSION DE LA MENACE TERRORISTE - 3H00','Sensi Radicalisation - 3H00',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(91,'125','Mise à Jour Connaissances Formateur en Sûreté de l\'Aviation Civile','Formation Mise à jour des connaissances - Formateur Sûreté REM',0,'Encadrement','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(92,'126','Formation Secure Supply Chain - ACC3 -RA3','AAC3 - RA3',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(93,'127','FORMATION CARISTE R489 – CHARIOT 3 CATEGORIE INITIAL','CACES 1-3-5 - Initiale',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(94,'128','Formation continue sur l’inspection Filtrage des personnes et sur la palpation de sécurité (Hors Aéroportuaire)','IF personnels/Palpation (hors aéro) Continue',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(95,'129','Formation continue sur l’interprétation de l’imagerie radioscopique (Hors secteur aéroportuaire)','Formation Imagerie (hors milieu aéro) Recyclage',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(96,'130','Formation initiale sur l’interprétation de l’imagerie radioscopique (Hors secteur aéroportuaire)','Formation Imagerie Hors milieu Aéro - Initiale',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(97,'131','Formation initiale sur l’inspection Filtrage des personnes et sur la palpation de sécurité (Hors Aéroportuaire)','IF personnels/Palpation (hors aéro) Initiale',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(98,'133','Sensibilisation O.E.A (Opérateur Economique Agréé)','Sensibilisation O.E.A',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(99,'134','Référent/Correspondant Sûreté - CDG – Orly – LBG.','Corsur ADP - CDG/ORLY/LBG',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(100,'135','MISE A DISPO PERSONNEL ET LOCAUX','DISPO',0,NULL,NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(101,'136','SADE 1 Course Sûreté PN Compagnie Aérienne - Gestion du risque terroriste et maitrise PAXI','SADE 1 PNT CORSAIR INTERNATIONAL -Risque Terroriste - Maitrise PAXI',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(102,'137','SADE 1- Cours Sûreté PNC Compagnie Aérienne - Fouille aéronef  §11.2.3.6 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs','SADE- PNC CORSAIR INTERNATIONAL §11.2.3.6',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(103,'138','SADE 1- Cours Sûreté PNC Compagnie Aérienne - 11.2.3.7.Regl. EU2015/1998  : Formation des personnes en charge de la protection des aéronefs','SADE- PNC CORSAIR INTERNATIONAL -§11.2.3.7 RegL EU2015/1998',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(104,'139','ADR 1.3 Transport Routier Matières Dangereuses','ADR 1.3 TRMD (colis)',0,'Adr 1.3','MARCHANDISES DANGEREUSES','2025-02-18 08:12:06','2025-02-18 08:12:06'),(105,'140','LOCAL ARCHIVE','ARCHIVE',0,NULL,NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(106,'141','11.2.6.2 du Regl. EU2015/1998: Formation des personnes accédant la ZSAR sans escorte E-LEARNING','B11.2.6.2 Regl. 2015/1998 E-learning',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(107,'142','Formation initiale/Continue de formateur: interprétation de l’imagerie radioscopique (Hors secteur aéroportuaire)','Formation Imagerie (hors aéro)',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(108,'143','SensibilisatIon 1.3 ADR – Expédition par route de marchandises dangereuses','Sensi ADR1.3 -STERNE',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(109,'144','Sensibilisation à la Radicalisation, Compréhension des menaces terroristes et Culture Sûreté 1H30','Sensibilisation radicalisation/culture sûreté',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(110,'145','Sensibilisation à la culture de sûreté, Menace interne et Radicalisation','Culture de sûreté et Radicalisation',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(111,'146','2025 -Recycling Training Course Sûreté PNC Compagnie Aérienne -Protection aéronef  11.2.3.7. et §11.2.3.11 du Regl. EU2015/1998 : Formation des personnes en charge de la protection des aéronefs et sureté en vol','RTC PNC CORSAIR INTERNATIONAL §11.2.3.7 -§11.2.3.11',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(112,'148','11.2.3.9 E LEARNING  Formation des personnes qui effectuent sur le fret et les envois postaux des contrôles de sûreté, autres que l\'inspection filtrage','11.2.3.9 E learning',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(113,'149','11.2.3.10 Variante FC Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage','11.2.3.10-E FORMATION Variante FC',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(114,'150','11.2.3.10 Variante AP Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage','11.2.3.10-E FORMATION Variante AP',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(115,'151','Marchandises Dangereuses - Cat.1 ID 8000Base OACI-IATA «Expéditeurs» Acheminement des marchandises dangereuses','DGR1 ID8000',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(116,'152','SensibilisatIon 1.3 ADR – Groupe Sterne','STERNE ADR1.3 -E-Learning',24,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(117,'153','Facteurs humains et gestion du risque','Facteurs humains et gestion du risque',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(118,'154','Formation Paragraphe 1.3 ADR Recyclage – Expédition par route de marchandises dangereuses','ADR 1.3 Exp. Route MD - Recyclage',0,'Adr 1.3','MARCHANDISES DANGEREUSES','2025-02-18 08:12:06','2025-02-18 08:12:06'),(119,'155','Sensibilisation à la Responsabilité Sociétale des Entreprises','Sensibilisation à la Responsabilité Sociétale (RSE)',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(120,'156','11.2.8 Sensibilisation à la cyber sécurité','11.2.8 Cyber sécurité',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(121,'157','Sensibilisation à la Responsabilité Sociétale des Entreprises','La RSE GROUPE STERNE',0,'QUALITÉ DE VIE AU TRAVAIL',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(122,'158','Sensibilisation RGPD','Sensibilisation RGPD',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(123,'159','SensibilisatIon 1.3 ADR – Groupe Sterne','STERNE ADR1.3 -E-Learning',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(124,'160','Parcours sécurité des données (RGPD-CYBERSECURITE)','E DONNEES GROUPE STERNE',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(125,'161','Sensibilisation à la cyber sécurité _Groupe Sterne -','Cyber sécurité',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(126,'162','LOCAL ARCHIVE','ARCHIVE',0,NULL,NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(127,'163','X RAY','',0,NULL,NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(128,'164','X RAY','X RAY',0,NULL,NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(129,'165','SUITE ECHEC PREMIERE SESSION -11.2.3.10 Variante AP Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspec','11.2.3.10-E FORMATION Variante AP',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(130,'166','Sensibilisation à la Responsabilité Sociétale des Entreprises','La RSE GROUPE STERNE',0,'QUALITÉ DE VIE AU TRAVAIL',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(131,'167','PARCOURS GROUPE STERNE','RSE, RGPD, CYBER',0,'QUALITÉ DE VIE AU TRAVAIL',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(132,'168','LOCATION SALLE DE FORMATION REM ALPHA','LOC. SALLE DE FORMATION',0,NULL,NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(133,'169','Formation Pratique 11.2.3.6.du Regl. EU2015/1998  : Formation Pratique des personnes en charge de la fouille de sûreté des aéronefs','11.2.3.6 - Partie Pratique',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(134,'170','11.2.6.2  E-LEARNING- 2de PASSAGE','2de PASSAGE EVALUATION 11.2.6.2 Regl. 2015/1998 E-learning',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(135,'171','11.2.6.2  E-LEARNING- 2de PASSAGE','2de PASSAGE EVALUATION 11.2.6.2 Regl. 2015/1998 E-learning',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(136,'172','Marchandises Dangereuses - Cat. 1 Piles & batteries Lithium ionique & gaz comprimé nsa BASE OACI-IATA \"Expéditeurs\" acheminement des marchandises dangereuses','DGR Cat. 1 Piles & batteries Lithium',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(137,'173','Marchandises Dangereuses - Cat.1– Base OACI-IATA « Expéditeurs de Fret aérien » Acheminement, préparation, stockage des marchandises dangereuses- Base - Excluant la classe 7 - Radioactifs','IATA DGR 1 - Expéditeurs hors RRY (Initiale)',24,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(138,'174','Marchandises Dangereuses - Cat.1– Recyclage OACI-IATA « Expéditeurs de Fret aérien »- Acheminement, préparation, stockage  des marchandises dangereuses- Recyclage - Excluant la classe 7 - Radioactifs','IATA DGR 1 - Expéditeurs hors RRY (Recyclage)',24,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(139,'176','SUITE ECHEC 11.2.3.9 E LEARNING  Formation des personnes qui effectuent sur le fret et les envois postaux des contrôles de sûreté, autres que l\'inspection filtrage','11.2.3.9 E learning',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(140,'177','Sensibilisation à la loi Sapin 2','Sensibilisation loi Sapin 2',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(141,'178','LOCATION SALLE DE FORMATION REM RDC BRAVO','LOC. SALLE DE FORMATION',0,NULL,NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(142,'179','2022 Recycling Training Course Sûreté PNT Compagnie Aérienne - Fouille aéronef  §11.2.3.6 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs','2022 RTC PNT CORSAIR INTERNATIONAL §11.2.3.6',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(143,'180','11.2.3.9 E LEARNING  Formation des personnes qui effectuent sur le fret et les envois postaux des contrôles de sûreté, autres que l\'inspection filtrage','11.2.3.9 E learning',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(144,'181','Recycling Training Course Sûreté PNT Compagnie Aérienne -Protection aéronef  11.2.3.7.du Regl. EU2015/1998 : Formation des personnes en charge de la protection des aéronefs','RTC PNT CORSAIR INTERNATIONAL §11.2.3.7 & §11.2.8 Sensi à la Cyber-menace',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(145,'182','13-SensibilisatIon 1.3 ADR – Groupe Sterne','STERNE ADR1.3 -E-Learning',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(146,'183','FORFAIT AFI 2022','',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(147,'186','2023 Recycling Training Course Sûreté PNC Compagnie Aérienne - Fouille aéronef  11.2.3.6 /11.2.3.11 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs, des mesures de sureté en vol & 11.2.8 Sensi à la Cyber-menace','RTC PNC CORSAIR INTERNATIONAL §11.2.3.6/11.2.3.11 & §11.2.8 Cyber-menace',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(148,'187','SADE 2023- Formation Sûreté PNT CORSAIR -§11.2.3.11/§11.2.3.6/11.2.3.7 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces','SADE - PNT CORSAIR INTERNATIONAL 11.2.3.11/11.2.3.6/11.2.3.7/11.2.8',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(149,'188','S01I-E-11.2.6.2 Formation INITIALE pour l\'obtention du TCA dit badge aéroportuaire - SUITE ECHEC-','01IE-Formation  badge aéroportuaire',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(150,'190','11.2.3.6.du Regl. EU2015/1998  : Formation des personnes en charge de la fouille de sûreté des aéronefs','11.2.3.6',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(151,'191','11.2.3.7.du Regl. EU2015/1998  : Formation des personnes en charge de la protection des aéronefs -v2023','11.2.3.7 Protection aéronef',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(152,'192','11.2.3.8. du Regl. EU2015/1998 : Formation des personnes qui mettent en oeuvre la vérification de concordance entre passagers et bagages -v2023','11.2.3.8 Concordance Bagages',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(153,'195','11.2.4. du Regl. EU2015/1998: Formation spécifique des personnes supervisant directement des personnes effectuant les contrôles de sûreté 11.2.3.6. à 11.2.3.10. -v2023','11.2.4 Contributeur superviseur',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(154,'196','11.2.6.2 du Regl. EU2015/1998: Formation Initiale des personnes accédant la ZSAR sans escorte','Obtention du badge (TCA)',36,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(155,'197','11.2.7 du Regl n°EU 2015/1998: Formation des personnes nécessitant une sensibilisation à la sûreté générale -v2023','11.2.7 - Initiale',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(156,'198','11.2.3.3. CV/FM Initiale du Regl. EU2015/1998  : inspection/filtrage du courrier et du matériel des transporteurs aériens, des approvisionnements de bord et des fournitures d\'aéroport, limitée au Contrôle Visuel et à la Fouille Manuelle -v2023','11.2.3.3 CV FM',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(157,'199','11.2.4.du Regl. EU2015/1998 : Formation spécifique des personnes supervisant directement des personnes effectuant les contrôles de sûreté 11.2.3.1 à 11.2.3.5. \"SUPERVISEUR -AGENT DE SURETE\" -v2023','11.2.4 Superviseur agent de sûreté (ADS)',0,'Superviseur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(158,'200','11.2.5. du Regl. EU2015/1998: Formation spécifique destinée aux personnes assumant une responsabilité générale au niveau national ou local en respect de toutes les dispositions légales applicables dans d\'un programme de sûreté et d\'Assurance Qualité','11.2.5',60,'Encadrement','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(159,'204','11.2.6.2 du Regl. EU2015/1998: Formation Périodique des personnes accédant la ZSAR sans escorte','11.2.6.2 Périodique Sensi Badge -V2023',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(160,'205','11.2.6.2 du Regl. EU2015/1998: Formation Initiale des personnes accédant la ZSAR sans escorte et Formation Référent/Correspondant Sûreté Roissy CDG – Orly – LBG.','11.2.6.2 + CORSUR',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(161,'206','2de -Sensibilisation à la cyber sécurité','2D Passage-Cyber sécurité',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(162,'207','Formation facteurs humains et à la gestion du risque','2nd passage facteurs humains et à la gestion du risque',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(163,'209','11.2.3.9  Formation INITIALE des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF','NE PLUS UTILISER',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(164,'210','2023 Recycling Training Course Sûreté PNT Compagnie Aérienne - Fouille aéronef  11.2.3.6 /11.2.3.11 du Regl. EU2015/1998 -Formation des personnes en charge de la fouille de sûreté des aéronefs, des mesures de sureté en vol & 11.2.8 Sensi à la Cyber-menace','2023 RTC PNT CORSAIR INTERNATIONAL §11.2.3.6/11.2.3.11 & §11.2.8 Cyber-menace',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(165,'211','S03P-11.2.3.10 PERIODIQUE .Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage','NE PLUS UTILISER',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(166,'212','11.2.3.9  Formation PERIODIQUE des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF','NE PLUS UTILSER',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(167,'213','Frais de fabrication des Titres de circulation aéroportuaire ADP (63,90€ + 8%) Tarif en vigueur au 01/04/2022','Demande de TCA REM',0,NULL,NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(168,'214','CARISTE R489 - CHARIOT 1-3-5','CACES 1-3-5 - Périodique',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(169,'215','S03I-11.2.3.10 INITIALE Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage','NE PLUS UTILISER',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(170,'216','SADE 2023- Formation Sûreté PNT CMA CGM AIRCARGO -§11.2.3.11/§11.2.3.6/11.2.3.7 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces','SADE- PNT CMA CGM AIRCARGO §11.2.3.11/§11.2.3.6/11.2.3.7',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(171,'217','11.2.3.7 : Formation  initiale des personnes en charge de la protection des aéronefs','11.2.3.7 E LEARNING INITIALE',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(172,'218','11.2.3.7 : Formation périodique des personnes en charge de la protection des aéronefs','11.2.3.7 E LEARNING PERIODIQUE',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(173,'219','SENSIBILISATION SUR LA CHAÎNE D’APPROVISIONNEMENT ET PRATIQUES ACTUELLES DANS LE DOMAINE DE LA SÛRETE DU FRET ET DU COURRIER AÉRIENS','Sensibilisation sûreté chaine du fret aérien',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(174,'220','SADE 2023- Formation Sûreté PNC CORSAIR -§11.2.3.11/§11.2.3.6 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef','SADE- PNC CORSAIR INTERNATIONAL §11.2.3.11/§11.2.3.6',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(175,'221','SADE 2023- Formation Sûreté PNC CORSAIR -11.2.3.7/11.2.8 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces','SADE- PNC CORSAIR INTERNATIONAL 11.2.3.7/§11.2.8',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(176,'222','SADE 2023 §11.2.3.11 Mesures de Sûreté en vol - PN Compagnie Aérienne - Gestion du risque terroriste et maitrise PAXI','Sade 2023 PN - Risque Terroriste - Maitrise Paxi',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(177,'223','FORMATION AIR CHINA','F AIR CHINA',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(178,'224','FORMATION AIR CHINA  2022','2022',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(179,'225','2023- Formation Sûreté PN compagnie aérienne -§11.2.3.11 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef','SADE- PN Compagnie Aérienne §11.2.3.11',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(180,'226','Marchandises Dangereuses - Cat. 1 UN 1072 / 3356','DGR Cat. 1 UN 1072 / 3356',0,'MARCHANDISES DANGEREUSES',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(181,'227','SADE 2023- Formation Sûreté PNC COMPAGNIE AERIENNE -§11.2.3.11/§11.2.3.6 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef + COMPL. TCA','SADE- PNC COMPAGNIE AERIENNE §11.2.3.11/§11.2.3.6+TCA',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(182,'228','SADE 2024- Formation Sûreté PN COMPAGNIE AERIENNE -11.2.3.7/11.2.3.11 du Regl. EU2015/1998: Mesures de sûreté en vol - Protection aéronef / Mesures de sûreté en vol','SADE- PNC COMPAGNIE AERIENNE 11.2.3.7/§11.2.3.11',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(183,'229','CONCEPTION DE FORMATION','MDC',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(184,'230','Formation : Formation de formateurs et maitrise pédagogique','Formation pédagogique Formateur',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(185,'232','11.2.6.2 Formation INITIALE pour l\'obtention du TCA dit badge aéroportuaire','11.2.6.2 INITIALE',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(186,'234','CIME -11.2.6.2-Formation pour l\'obtention carte CIME','11.2.6.2 - Carte CIME',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(187,'236','11.2.3.9 + Compl. 11.2.6.2 Formation des personnes ayant un accès non surveillé au fret et courrier aérien identifiables et/ou qui effectuent sur le fret et les envois postaux des contrôles de sûreté autre que l\'IF + Partie TCA','11.2.3.9 + 11.2.6.2',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(188,'237','2025 Stage Sûreté PNT / CDB CORSAIR - 11.2.3.11 du Regl. EU2015/1998 -Formation des CDB CORSAIR en charge des mesures de sureté en vol','2025 Stage CDB PNT International 11.2.3.11 & 11.2.8',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(189,'242','Marchandises Dangereuses IATA 7.1  \"Expéditeurs\" UN1956/3480/3481','DGR 7.1',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(190,'243','11.2.6.2 PERIODIQUE pour l\'obtention du TCA dit badge aéroportuaire','11.2.6.2 PERIODIQUE 2023',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(191,'244','Formation en Ligne Gestes et Postures en Manutention','Gestes et postures en manutention',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(192,'245','2023- Formation Sûreté PN compagnie aérienne -§11.2.3.11 du Regl. EU2015/1998: Mesures de sûreté en vol -Compl. TCA-Carte CIME §11.2.6.2','SADE- PN Compagnie Aérienne §11.2.3.11+ Compl. TCA',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(193,'246','11.2.3.7 : Formation initiale des personnes en charge de la protection des aéronefs','11.2.3.7 Protection aéronef - Initiale',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(194,'247','11.2.3.7 : Formation périodique des personnes en charge de la protection des aéronefs','11.2.3.7 Protection aéronef - Périodique',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(195,'248','11.2.6.2 Formation INITIALE pour l\'obtention du TCA dit badge aéroportuaire','11.2.6.2 Obtention du badge aéroportuaire - Initiale',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(196,'249','11.2.6.2 PERIODIQUE pour l\'obtention du TCA dit badge aéroportuaire','11.2.6.2 Obtention du badge aéroportuaire - Périodique',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(197,'250','Formation Sûreté Périodique Typologie 6 (T6) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.2 §11.2.3.3 §11.2.3.4 §11.2.3.5 : Formation Périodique Hors Imagerie :','FPHI Typologie 6 (hors imagerie)',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(198,'251','Périodique Sécurité en Piste (piéton) et Système de gestion du risque','Sécurité en Piste/Piéton - Périodique',0,'E-LEARNING',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(199,'252','11.2.8 Sensibilisation à la cyber sécurité','11.2.8 Cyber sécurité',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(200,'253','Second passage Formation en Ligne Gestes et Postures en Manutention','SECOND PASSAGE GESTES ET POSTURES MANUTENTION',0,'QUALITÉ DE VIE AU TRAVAIL',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(201,'254','SensibilisatIon 1.3 ADR – Expédition par route de marchandises dangereuses','Sensi ADR1.3  Second passage',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(202,'255','11.2.6.2 Formation INITIALE pour l\'obtention du TCA dit badge aéroportuaire','Second passage 11.2.6.2 INITIALE',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(203,'256','2SD PASSAGE 11.2.6.2 Formation INITIALE pour l\'obtention du TCA dit badge aéroportuaire','11.2.6.2 INITIALE SECOND PASSAGE',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(204,'257','Formation -§11.2.6.2 du Regl. EU2015/1998: Formation Initiale des personnes accédant la ZSAR sans escorte -Obtention carte CIME','11.2.6.2 - CIME',0,'Badge','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(205,'258','IATA 1.6 : Instructions adéquates pour l’expédition de batteries au lithium de section II','',0,'SÛRETÉ',NULL,'2025-02-18 08:12:06','2025-02-18 08:12:06'),(206,'259','M14 - Formation de Formateur Qualifié en Sûreté de l\'Aviation Civile pour la dispense des Modules M01 et M04 à M10','Formation de formateur qualifié Sûreté M14',0,'Encadrement','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(207,'260','SADE OCC 2025- Formation Sûreté PNT CORSAIR -§11.2.3.11/§11.2.3.6/11.2.8. du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces','SADE OCC 2025 - PNT CORSAIR INTERNATIONAL 11.2.3.11/§11.2.3.6/11.2.8',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:06','2025-02-18 08:12:06'),(208,'261','SADE OCC 2025- Formation Sûreté PNC CORSAIR -§11.2.3.11/§11.2.3.6 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef','SADE OCC 2025 - PNC CORSAIR INTERNATIONAL §11.2.3.11/§11.2.3.6',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(209,'262','2024 - Recycling Training Course Sûreté PNC CORSAIR - -§11.2.3.11Regl. EU2015/1998 - Gestion du risque terroriste et maitrise PAXI','RTC 2024 - PNC CORSAIR INTERNATIONAL -Risque Terroriste - Maitrise PAXI',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(210,'263','2024 - Recycling Training Course Sûreté PNT CORSAIR -§11.2.3.11REGL. EU2015/1998 Gestion du risque terroriste et maitrise PAXI','RTC 2024 - PNT CORSAIR INTERNATIONAL -Risque Terroriste - Maitrise PAXI',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(211,'264','Formation Sûreté Périodique Typologie 2 (T2) du Règlement EU2015/1998 §11.2.3.1 §11.2.3.2 §11.2.3.3 §11.2.3.4 §11.2.3.5 : Formation Périodique Hors Imagerie :','FPHI Typologie 2 (hors imagerie)',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(212,'265','Formation Sûreté §11.2.3.4 c) du Regl. EU2015/1998 : Formation complémentaire relative au contrôle visuel du compartiment moteur-  « formation complétée par les connaissances supplémentaires prévues au point 11.2.3.4 c) de l’annexe du règlement (UE) n° 20','11.2.3.4 c)',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(213,'267','Permis Trafic CHARLES DE GAULLE','Permis T E LEARNING  CDG',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(214,'268','Gestes et Postures en Manutention atelier pratique','Gestes et Postures en Manutention (pratique)',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(215,'269','Gestes et Postures en Manutention Théorie','Gestes et Postures en Manutention (théorie)',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(216,'270','Gestes et Postures  théorie et pratique','Gestes et Postures (théorie et pratique)',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(217,'271','Permis Trafic CHARLES DE GAULLE','Permis T CDG',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(218,'272','Sécurité en Piste et Système de gestion du risque','Sécurité en Piste et Système de gestion du risque',0,'SÉCURITÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(219,'273','Second passage Sécurité en Piste (piéton) et Système de gestion du risque','Sécurité Piste E-Learning',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(220,'274','OCC 2024- Formation Sûreté PN CELESTE -§11.2.3.6/§11.2.3.11/11.2.8. du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces','OCC 2024 - PN  CELESTE §11.2.3.6/§11.2.3.11/§11.2.8',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(221,'275','OCC 2024- Formation Sûreté PN CELESTE -11.2.3.7/11.2.3.11 du Regl. EU2015/1998: Mesures de sûreté en vol - Protection aéronef / Mesures de sûreté en vol','OCC- PN CELESTE 11.2.3.7/§11.2.3.11',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(222,'276','Sensibilisation PHMR','Sensibilisation PHMR',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(223,'278','Essai Logyx - AIR AND PORT SERVICES','Essai Logyx - AIR AND PORT SERVICES',0,'SÛRETÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(224,'281','Salle à disposition annuelle LOGYX','Salle à disposition LOGYX',0,'SÛRETÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(225,'282','IMDG 1.3 - Spécial véhicule sur la base d’un programme IMDG ou ADR 1.3 \"classique\"','IMDG 1.3 - Spécial véhicule',0,'SÛRETÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(226,'283','SADE-OCC 2025- Formation Sûreté PNC CORSAIR -11.2.3.7/11.2.3.11/11.2.8 du Regl. EU2015/1998: Mesures de sûreté en vol - Fouille aéronef /Protection aéronef / Cyber-menaces','SADE OCC- PNC CORSAIR INTERNATIONAL 11.2.3.7/11.2.3.11/§11.2.8',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(227,'284','SADE-OCC 2025 PNC §11.2.3.11 Mesures de Sûreté en vol - PN Compagnie Aérienne - Gestion du risque terroriste et maitrise PAXI','SADE OCC 2025 PNC -Risque Terroriste - Maitrise PAXI',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(228,'285','2024 - SADE - OCC Sûreté PNC CORSAIR  -§11.2.3.11Regl. EU2015/1998 - Gestion du risque terroriste et maitrise PAXI','SADE OCC 2024 - PNC CORSAIR INTERNATIONAL -Risque Terroriste - Maitrise PAXI',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(229,'286','11.2.3.9 + Compl. 11.2.6.2 Regl. EU2015/1998: Formation des personnes ayant un accès non surveillé au fret et courrier aérien identifiables et/ou qui effectuent sur le fret et les envois postaux des contrôles de sûreté autre que l\'IF + Partie TCA','11.2.3.9 + Compl. 11.2.6.2',60,'Contributeur','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(230,'287','11.2.3.9 Regl. EU2015/1998: Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF','11.2.3.9',60,'Contributeur','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(231,'288','11.2.3.10. + Compl. 11.2.6.2 du Regl. EU2015/1998 :Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l\'IF + TC','11.2.3.10 + 11.2.6.2 (TCA)',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(232,'289','11.2.3.10.du Regl. EU2015/1998 Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage -2023','11.2.3.10',60,'Contributeur','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(233,'290','11.2.2. Formation de base des personnes qui exécutent les tâches énumérées aux points 11.2.3.1, 11.2.3.4 et 11.2.3.5 et aux points 11.2.4, 11.2.5 et 11.5 -V2023','11.2.2 Formation de base à la sûreté',0,'Encadrement','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(234,'291','11.2.3.9  Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF','11.2.3.9 - Initiale',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(235,'292','11.2.3.9  Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF','11.2.3.9 - Périodique',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(236,'293','11.2.3.10 Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage','11.2.3.10 - Initiale',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(237,'294','Formation UM accueil des enfants mineurs voyageant seuls','Accueil des UM (mineur non accompagné)',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(238,'295','Agent welcome','Agent Welcome',0,'COMPAGNIE',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(239,'296','Agent welcome','Agent Welcome',0,'COMPAGNIE',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(240,'297','Formation sûreté Agent Welcome','Agent Welcome',0,'SÛRETÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(241,'298','11.2.3.10 Formation des personnes qui effectuent sur le courrier et le matériel des transporteurs aériens, les approvisionnements de bord et les fournitures d’aéroport, des contrôles de sûreté, autres que l’inspection filtrage','11.2.3.10 - Périodique',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(242,'299','Permis Trafic CHARLES DE GAULLE-2-','Permis T CDG',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(243,'300','Formation Périodique Imagerie FPI T2 -RX et EDS simple vue multi-vues (11.4.1 du règlement EU2015/1998)','FPI Typologie 2',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(244,'301','Formation Périodique Imagerie FPI T6 -RX et EDS simple vue multi-vues (11.4.1 du règlement EU2015/1998)','FPI Typologie 6',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(245,'302','Formation Périodique Imagerie FPI T7 -RX et EDS simple vue multi-vues (11.4.1 du règlement EU2015/1998)','FPI Typologie 7',0,'Formation périodique','AGENT DE SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(246,'303','Formation pédagogique - Gestion du temps','Gestion du temps',0,'SÛRETÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(247,'305','11.2.3.9  Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF','11.2.3.9 Second passage',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(248,'307','Parcours sécurité des données','',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(249,'308','Mise à disposition d\'un référent','Mise à disposition d\'un référent',0,'SÛRETÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(250,'310','Sensibilisation 1.3 ADR – Expédition par route de marchandises dangereuses (Recyclage)','Sensibilisation ADR 1.3 Recyclage',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(251,'311','2ND passage - 11.2.6.2 PERIODIQUE pour l\'obtention du TCA dit badge aéroportuaire','11.2.6.2 Obtention du badge aéroportuaire - Périodique',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(252,'312','11.2.3.9  Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF','11.2.3.9 - Initiale',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(253,'313','ADR et spécification déchets (ou autres)','ADR et spécification déchets (ou autres)',0,'Adr 1.3','MARCHANDISES DANGEREUSES','2025-02-18 08:12:07','2025-02-18 08:12:07'),(254,'314','Second passage 11.2.3.9 - La formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables qui ont fait l’objet de contrôles de sûreté et des personnes effectuant sur du fret aérien et du courrier aérien des contr','Second passage - 11.2.3.9 Périodique',0,'SÛRETÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(255,'315','11.2.3.9 + Compl. 11.2.6.2 Regl. EU2015/1998: Formation des personnes ayant un accès non surveillé au fret et courrier aérien identifiables et/ou qui effectuent sur le fret et les envois postaux des contrôles de sûreté autre que l\'IF + Partie TCA','11.2.3.9 + Compl. 11.2.6.2',0,'Contributeur','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(256,'316','11.2.3.9 + Compl. 11.2.6.2 Formation des personnes ayant un accès non surveillé au fret et courrier aérien identifiables et/ou qui effectuent sur le fret et les envois postaux des contrôles de sûreté autre que l\'IF + Partie TCA','11.2.3.9 + 11.2.6.2 (2nd passage)',0,'E-LEARNING',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(257,'317','11.2.3.9 Regl. EU2015/1998: Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF','11.2.3.9',60,'Contributeur','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(258,'320','CORSUR + 11.2.8','CORSUR + 11.2.8',0,'SÛRETÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(259,'321','11.2.3.9 Formation des personnes ayant un accès non surveillé au fret aérien et au courrier aérien identifiables et des personnes effectuant sur du fret aérien et du courrier aérien des contrôle de sûreté autre que l\'IF','11.2.3.9 (visio)',0,'SÛRETÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(260,'324','11.2.2. Formation de base des personnes qui exécutent les tâches énumérées aux points 11.2.3.1, 11.2.3.4 et 11.2.3.5 et aux points 11.2.4, 11.2.5 et 11.5 -V2023','11.2.2 Formation de base à la sûreté',0,'Encadrement','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(261,'325','11.2.5. du Regl. EU2015/1998: Formation spécifique destinée aux personnes assumant une responsabilité générale au niveau national ou local en respect de toutes les dispositions légales applicables dans d\'un programme de sûreté et d\'Assurance Qualité','11.2.5',60,'Encadrement','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(262,'326','2025 - Recycling Training Course Sûreté PNT Corsair Aérienne -Protection aéronef  11.2.3.7. et §11.2.3.1.11 du Regl. EU2015/1998 : Formation des personnes en charge de la protection des aéronefs et sureté en vol','2025 RTC PNT CORSAIR INTERNATIONAL §11.2.3.7 -§11.2.3.11',0,'Compagnie','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07'),(263,'327','Formation sur mesure de base et management de la sûreté','Formation sur mesure',0,'SÛRETÉ',NULL,'2025-02-18 08:12:07','2025-02-18 08:12:07'),(264,'328','11.2.5: Formation spécifique destinée aux personnes assumant une responsabilité générale au niveau national ou local en respect de toutes les dispositions légales applicables dans d\'un programme de sûreté et d\'Assurance Qualité','11.2.5 Management de la sûreté',60,'Encadrement','SÛRETÉ','2025-02-18 08:12:07','2025-02-18 08:12:07');
/*!40000 ALTER TABLE `trainings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `two_factor_codes`
--

DROP TABLE IF EXISTS `two_factor_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `two_factor_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `two_factor_codes_user_id_foreign` (`user_id`),
  CONSTRAINT `two_factor_codes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `two_factor_codes`
--

LOCK TABLES `two_factor_codes` WRITE;
/*!40000 ALTER TABLE `two_factor_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `two_factor_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_trainings`
--

DROP TABLE IF EXISTS `user_trainings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_trainings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `training_id` bigint unsigned NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `certificate_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_trainings_user_id_training_id_unique` (`user_id`,`training_id`),
  KEY `user_trainings_training_id_foreign` (`training_id`),
  CONSTRAINT `user_trainings_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_trainings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_trainings`
--

LOCK TABLES `user_trainings` WRITE;
/*!40000 ALTER TABLE `user_trainings` DISABLE KEYS */;
INSERT INTO `user_trainings` VALUES (1,6,1,'2024-12-27 10:06:08','2025-12-27 10:06:13','certificates/vDpFvmghQSgI2GCqBFpe43t7vez2D3cV4B6r9Qxz.pdf','2025-01-27 10:07:07','2025-02-25 12:00:11'),(2,7,1,'2024-11-27 10:07:35','2025-11-27 10:07:43',NULL,'2025-01-27 10:07:48','2025-01-27 10:07:48'),(3,7,2,'2024-02-27 10:12:16','2025-02-11 10:12:26','certificates/rt42Vvg3KjuDbTE5BuKd2m5BRf9RyJgKLgTa90Qn.pdf','2025-01-27 10:08:38','2025-02-25 08:49:13'),(5,6,3,'2025-01-27 14:09:05','2025-04-27 13:09:05',NULL,'2025-01-27 14:09:05','2025-01-27 14:09:05'),(6,6,4,'2024-11-27 14:09:54','2025-02-11 14:09:54',NULL,'2025-01-27 14:09:54','2025-01-27 14:09:54'),(8,6,5,'2024-07-27 13:13:50','2024-12-28 14:13:50',NULL,'2025-01-27 14:13:50','2025-01-27 14:13:50'),(11,6,120,'2025-02-19 23:00:00','2025-02-19 23:00:00',NULL,'2025-02-18 08:12:35','2025-02-18 08:12:35'),(12,6,125,'2025-02-20 23:00:00','2025-02-20 23:00:00',NULL,'2025-02-18 15:12:57','2025-02-18 15:12:57');
/*!40000 ALTER TABLE `user_trainings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `function` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_id` bigint unsigned DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin User','admin@example.com',NULL,'$2y$12$FhrPwQ3Pu2LgSzOX.PT5gO7PPLGNJZLXYadfvGBfxXeHZpnF7Bu8y',NULL,'2024-11-26 13:52:42','2025-02-10 12:25:42','admin',NULL,NULL,0),(2,'Ulises Lubowitz IIIcc','juanita32@example.org','2024-11-26 15:00:52','$2y$12$Qam6vI3B.XQP.1ZnayeKXefHxHHccLytmj3XxLfa//8FH9JGrP6dm','q4i962H4JH','2024-11-26 15:00:53','2024-12-01 09:46:13','client',NULL,NULL,0),(4,'Manuel Graham','nestor.price@example.net','2024-11-26 15:00:53','$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy','LFfwzZX2Ot','2024-11-26 15:00:53','2024-11-26 16:09:46','formation',NULL,NULL,0),(5,'Prof. Estelle Kirlin I','sherwood.bergnaum@example.com','2024-11-26 15:00:53','$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy','ZcMXVzsma1','2024-11-26 15:00:53','2024-11-26 15:32:59','formation',NULL,NULL,0),(6,'Ardella Reilly IV','kreiger.mariah@example.net','2024-11-26 15:00:53','$2y$12$YyotAt3SVFEjnPH6uX6iJeoqTxlQFDOquNPlfu2u6G6h/r2aw2vsa','8HeeApkMZB','2024-11-26 15:00:53','2024-11-27 10:15:41','client',NULL,1,0),(7,'Eddie Beahannnnn','fletcher62@example.com','2024-11-26 15:00:53','$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy','0aTzn5Uz4m','2024-11-26 15:00:53','2024-11-27 14:00:23','client',NULL,1,0),(8,'Mr. Eleazar Block Sr.','brandi62@example.com','2024-11-26 15:00:53','$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy','q2GsUcpSpo','2024-11-26 15:00:53','2024-11-26 15:00:53','client',NULL,NULL,0),(9,'Sophia Lakin Sr.','zbeer@example.org','2024-11-26 15:00:53','$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy','0i0I4Xqs0p','2024-11-26 15:00:53','2024-11-26 15:00:53','client',NULL,NULL,0),(11,'Shany Wyman','manley.volkman@example.org','2024-11-26 15:00:53','$2y$12$EzPYP1/rfgNqYTBw6z26HO4nHzEAF3b8PvwLVTUtPKHOSDbKpYlNy','tnHeatje9Q','2024-11-26 15:00:53','2024-11-26 15:00:53','client',NULL,NULL,0),(12,'fdsgfdhsf','admddddin@example.com',NULL,'$2y$12$92JpTtMD8kqDQ1PtPTATkuVBFAC8zFuPB8PcNCb.i9PBuNqHRuIFy',NULL,'2024-11-26 15:34:07','2024-11-26 15:34:07','formation',NULL,NULL,0),(16,'sdvdsv','admfffin@esdfcsvd.fffff',NULL,'$2y$12$w7r7P0rJpO2h1xSoL/xTeehkGkLVNJ3E2OVTVtDOCj5I3ylMOhjt2',NULL,'2024-11-27 13:07:39','2024-11-27 13:07:39','client',NULL,NULL,0),(17,'fffffff','adffvfffmin@example.com',NULL,'$2y$12$tTw.nEb1NLT4ApIR4XpsTub7AmnFad25o1dw3ZGzSl8x4X0Vqk9EO',NULL,'2024-11-27 13:11:49','2024-11-27 13:11:49','client',NULL,1,0),(18,'fdgfdgfd','admdfdfdfdin@example.com',NULL,'$2y$12$Cap57glUya76I2exAznziO0e9RY3qI./rYUYpQlAtczW3rcTky4Qa',NULL,'2024-11-27 13:43:53','2024-11-27 13:43:53','client',NULL,2,0),(19,'wxcvbn','adxcvbmin@example.com',NULL,'$2y$12$VzkMjB9j2BhfLaMvFH0u6eQuBsaWKLFkdUu2RRz.w1dqG4G0Vj8j.',NULL,'2024-11-27 14:04:48','2024-11-27 14:05:10','client',NULL,3,0),(20,'fdfddfdfdfdfd','fdfdfdfddffdfdfd@example.com',NULL,'$2y$12$2hBQc6ozjFotrO8Qt.OxAeJAL0gWkuvuNe3gruOar768W0I10Bd8q',NULL,'2024-11-27 14:05:52','2024-11-27 14:07:10','client',NULL,3,0),(22,'John Doe','john@example.com',NULL,'$2y$12$X/13j6rSgn2.XxdFI2gzaeuwAOfk69mlOCyEj09l85J6WzemIH74m',NULL,'2024-12-30 13:08:55','2024-12-30 13:08:55','admin',NULL,1,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-03 17:14:26
