-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: sys_rez_us
-- ------------------------------------------------------
-- Server version	26.7.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

-- SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'f4cc2eec-a6ca-11f1-b770-74563cc75024:1-3555';

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_availabilities`
--

DROP TABLE IF EXISTS `employee_availabilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_availabilities` (
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `day_of_week` tinyint unsigned DEFAULT NULL,
  `specific_date` date DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  KEY `employee_availabilities_employee_id_foreign` (`employee_id`),
  CONSTRAINT `employee_availabilities_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`uuid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_availabilities`
--

LOCK TABLES `employee_availabilities` WRITE;
/*!40000 ALTER TABLE `employee_availabilities` DISABLE KEYS */;
INSERT INTO `employee_availabilities` VALUES ('7188dc69-b9e4-11f1-af4c-74563cc75024','fbcb622a-b76d-11f1-82e3-74563cc75024',1,NULL,'07:00:00','15:00:00',NULL,NULL),('75c50ec5-b9e4-11f1-af4c-74563cc75024','fbcb622a-b76d-11f1-82e3-74563cc75024',2,NULL,'09:00:00','15:00:00',NULL,NULL),('830e3ce5-b76e-11f1-82e3-74563cc75024','fbcb622a-b76d-11f1-82e3-74563cc75024',3,NULL,'07:00:00','15:00:00',NULL,NULL),('860dcd97-b2d6-11f1-899d-74563cc75024','6f6a9757-f1b3-3a1c-bf6a-41a7438db096',6,NULL,'08:00:00','16:00:00',NULL,NULL),('c505621d-b2d6-11f1-899d-74563cc75024','c9448f84-c390-3d40-aad6-0bcb5266af54',5,NULL,'09:00:00','17:00:00',NULL,NULL),('ca62b561-b9e6-11f1-af4c-74563cc75024','6f6a9757-f1b3-3a1c-bf6a-41a7438db096',2,NULL,'08:00:00','16:00:00',NULL,NULL),('cc99df61-b9e6-11f1-af4c-74563cc75024','6f6a9757-f1b3-3a1c-bf6a-41a7438db096',1,NULL,'08:00:00','16:00:00',NULL,NULL),('d33354a2-b9e6-11f1-af4c-74563cc75024','6f6a9757-f1b3-3a1c-bf6a-41a7438db096',3,NULL,'10:00:00','16:00:00',NULL,NULL),('d7484304-b9e6-11f1-af4c-74563cc75024','6f6a9757-f1b3-3a1c-bf6a-41a7438db096',4,NULL,'08:00:00','16:00:00',NULL,NULL),('d90fb6d5-b9e6-11f1-af4c-74563cc75024','6f6a9757-f1b3-3a1c-bf6a-41a7438db096',5,NULL,'08:00:00','16:00:00',NULL,NULL),('f2ca3a3a-b9e6-11f1-af4c-74563cc75024','c9448f84-c390-3d40-aad6-0bcb5266af54',1,NULL,'09:00:00','17:00:00',NULL,NULL),('f49062df-b9e6-11f1-af4c-74563cc75024','c9448f84-c390-3d40-aad6-0bcb5266af54',2,NULL,'09:00:00','17:00:00',NULL,NULL),('f5f46ed7-b9e6-11f1-af4c-74563cc75024','c9448f84-c390-3d40-aad6-0bcb5266af54',3,NULL,'09:00:00','17:00:00',NULL,NULL),('f775bf65-b9e6-11f1-af4c-74563cc75024','c9448f84-c390-3d40-aad6-0bcb5266af54',4,NULL,'09:00:00','17:00:00',NULL,NULL);
INSERT INTO `employee_availabilities` (`uuid`,`employee_id`,`day_of_week`,`specific_date`,`start_time`,`end_time`,`created_at`,`updated_at`) VALUES ('6015f129-1779-490e-84fc-0dddb5d5e7f7','b61c714c-0eb3-4761-a67c-f652f3b1605e',1,NULL,'09:00:00','17:00:00','2026-10-15 15:16:56','2026-10-15 15:16:56'),('31fde0a2-e84a-4e23-91fc-0f199b56e6a9','b61c714c-0eb3-4761-a67c-f652f3b1605e',2,NULL,'09:00:00','17:00:00','2026-10-15 15:16:56','2026-10-15 15:16:56'),('359d721b-7b8a-48e0-adfc-097197df0af4','b61c714c-0eb3-4761-a67c-f652f3b1605e',3,NULL,'09:00:00','17:00:00','2026-10-15 15:16:56','2026-10-15 15:16:56'),('2a425d4e-952b-4c9e-ab65-6a99df51f963','b61c714c-0eb3-4761-a67c-f652f3b1605e',4,NULL,'09:00:00','17:00:00','2026-10-15 15:16:56','2026-10-15 15:16:56'),('c10eaae8-ca37-4737-85e8-e35bb5eae914','b61c714c-0eb3-4761-a67c-f652f3b1605e',5,NULL,'09:00:00','15:00:00','2026-10-15 15:16:56','2026-10-15 15:16:56'),('e7611b6b-1673-4a5d-817e-496ba78733cc','27945871-c3fe-4a23-9bb3-66d6764eab96',2,NULL,'10:00:00','18:00:00','2026-10-15 15:16:56','2026-10-15 15:16:56'),('855a8575-b0b3-494b-b280-533918dcaab8','27945871-c3fe-4a23-9bb3-66d6764eab96',3,NULL,'10:00:00','18:00:00','2026-10-15 15:16:56','2026-10-15 15:16:56'),('1fb480d9-9622-40a9-9614-d3b78616be58','27945871-c3fe-4a23-9bb3-66d6764eab96',4,NULL,'10:00:00','18:00:00','2026-10-15 15:16:56','2026-10-15 15:16:56'),('fd9ef315-2426-4b88-a1af-5121c6501835','27945871-c3fe-4a23-9bb3-66d6764eab96',5,NULL,'10:00:00','18:00:00','2026-10-15 15:16:56','2026-10-15 15:16:56'),('818ab1dc-6c77-48ba-936a-e4541756da32','27945871-c3fe-4a23-9bb3-66d6764eab96',6,NULL,'09:00:00','14:00:00','2026-10-15 15:16:57','2026-10-15 15:16:57'),('54a5be76-0bb7-4e1b-a0fe-e5e6e3c5312d','ec8c6720-d596-438c-801d-dc63d72b4d02',1,NULL,'08:00:00','16:00:00','2026-10-15 15:16:57','2026-10-15 15:16:57'),('a8d3a537-de37-4526-968b-8b1ef11234e2','ec8c6720-d596-438c-801d-dc63d72b4d02',3,NULL,'08:00:00','16:00:00','2026-10-15 15:16:57','2026-10-15 15:16:57'),('df0a91b0-77a5-4323-b0fa-771ae786d532','ec8c6720-d596-438c-801d-dc63d72b4d02',5,NULL,'08:00:00','16:00:00','2026-10-15 15:16:57','2026-10-15 15:16:57'),('76586ab1-ffc2-4045-9363-9e9bf90930be','ec8c6720-d596-438c-801d-dc63d72b4d02',6,NULL,'08:00:00','13:00:00','2026-10-15 15:16:57','2026-10-15 15:16:57'),('f74f7994-e73f-44f0-bc15-6c562c4c8355','7de073c3-d0e2-40f2-8956-19d78630ce8b',1,NULL,'12:00:00','20:00:00','2026-10-15 15:16:57','2026-10-15 15:16:57'),('15aed9ed-e5da-4a8c-adde-47624931fab8','7de073c3-d0e2-40f2-8956-19d78630ce8b',2,NULL,'12:00:00','20:00:00','2026-10-15 15:16:57','2026-10-15 15:16:57'),('8317a6e7-453d-4da0-97ab-a84a7f6545aa','7de073c3-d0e2-40f2-8956-19d78630ce8b',4,NULL,'12:00:00','20:00:00','2026-10-15 15:16:57','2026-10-15 15:16:57');
/*!40000 ALTER TABLE `employee_availabilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_services`
--

DROP TABLE IF EXISTS `employee_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_services` (
  `uuid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  KEY `employee_services_employee_id_foreign` (`employee_id`),
  KEY `employee_services_service_id_foreign` (`service_id`),
  CONSTRAINT `employee_services_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`uuid`) ON DELETE CASCADE,
  CONSTRAINT `employee_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`uuid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_services`
--

LOCK TABLES `employee_services` WRITE;
/*!40000 ALTER TABLE `employee_services` DISABLE KEYS */;
INSERT INTO `employee_services` VALUES ('1ef8bd17-b76e-11f1-82e3-74563cc75024','fbcb622a-b76d-11f1-82e3-74563cc75024','1895be2e-b0de-11f1-becb-74563cc75024',NULL,NULL),('defe6ea5-2be4-4dfc-8a3e-72a9bc7f1a72','6f6a9757-f1b3-3a1c-bf6a-41a7438db096','1895be2e-b0de-11f1-becb-74563cc75024',NULL,NULL),('fe3d204a-2eb6-46c4-add9-f7f0ec654aa0','c9448f84-c390-3d40-aad6-0bcb5266af54','1895be2e-b0de-11f1-becb-74563cc75024',NULL,NULL);
INSERT INTO `employee_services` (`uuid`,`employee_id`,`service_id`,`created_at`,`updated_at`) VALUES ('9d9fc1b2-56f7-4fa2-b196-16b445d9b5dc','b61c714c-0eb3-4761-a67c-f652f3b1605e','1186eea1-dd24-4ea2-b915-1a954b0435d9','2026-10-15 15:16:56','2026-10-15 15:16:56'),('f51f1a7d-3360-470d-aaa2-f5103fab19ea','b61c714c-0eb3-4761-a67c-f652f3b1605e','1efddaf8-a059-43c3-9e68-e62d4f4a0af2','2026-10-15 15:16:56','2026-10-15 15:16:56'),('c37772b6-604d-47b8-a385-26aa1caaa266','b61c714c-0eb3-4761-a67c-f652f3b1605e','bc960fd8-1c5f-4228-92db-6749ec9ff6a8','2026-10-15 15:16:56','2026-10-15 15:16:56'),('6d05f8b4-00dc-48ed-8d8e-f91b1370ddce','b61c714c-0eb3-4761-a67c-f652f3b1605e','1693ce55-c602-4f4f-a281-720ae3d54b7e','2026-10-15 15:16:56','2026-10-15 15:16:56'),('6f9fd683-0b08-4d32-969e-3fa3416f72b1','b61c714c-0eb3-4761-a67c-f652f3b1605e','97c19a44-13e6-4771-9af9-a929b3e9c2a6','2026-10-15 15:16:56','2026-10-15 15:16:56'),('725f39c3-623c-485e-bf19-11b329cab95c','27945871-c3fe-4a23-9bb3-66d6764eab96','1895be2e-b0de-11f1-becb-74563cc75024','2026-10-15 15:16:56','2026-10-15 15:16:56'),('4f1b498f-9a47-43c5-95c1-0d8dd95e88da','27945871-c3fe-4a23-9bb3-66d6764eab96','4e99e81b-79d4-4513-b48a-08e1e5ab1e31','2026-10-15 15:16:56','2026-10-15 15:16:56'),('d15c2c6b-12e6-4f0c-b66d-6a770f7f90ae','27945871-c3fe-4a23-9bb3-66d6764eab96','97c19a44-13e6-4771-9af9-a929b3e9c2a6','2026-10-15 15:16:56','2026-10-15 15:16:56'),('d657e644-dbca-43bc-a2da-cb90a0e85660','27945871-c3fe-4a23-9bb3-66d6764eab96','2a73f3b8-8fac-4729-a2e0-2e25ec3071d7','2026-10-15 15:16:56','2026-10-15 15:16:56'),('7d7ef277-43b4-47aa-96c4-3b9b9a6d33bc','27945871-c3fe-4a23-9bb3-66d6764eab96','a20abf87-67ae-4c04-ac46-dca60878b60e','2026-10-15 15:16:56','2026-10-15 15:16:56'),('30656e25-7f62-4b57-bf53-255a9c3ef034','ec8c6720-d596-438c-801d-dc63d72b4d02','fb77265a-b0dd-11f1-becb-74563cc75024','2026-10-15 15:16:56','2026-10-15 15:16:56'),('5439027f-51f9-4251-b79b-be014d792e8b','ec8c6720-d596-438c-801d-dc63d72b4d02','2c7ad07a-b0de-11f1-becb-74563cc75024','2026-10-15 15:16:56','2026-10-15 15:16:56'),('e4acefb4-314b-4604-aa68-f3ebba65677e','ec8c6720-d596-438c-801d-dc63d72b4d02','4e99e81b-79d4-4513-b48a-08e1e5ab1e31','2026-10-15 15:16:56','2026-10-15 15:16:56'),('f94f0d8e-b290-46f6-865a-71dcbf577b1b','7de073c3-d0e2-40f2-8956-19d78630ce8b','570d5905-597e-452d-8445-792070f82f30','2026-10-15 15:16:56','2026-10-15 15:16:56'),('79b12c65-5b90-4395-8a14-f6638a22d2e4','7de073c3-d0e2-40f2-8956-19d78630ce8b','d95e9df8-6f32-43c2-a205-be6b663fd127','2026-10-15 15:16:56','2026-10-15 15:16:56'),('619ab9fe-473d-4651-869d-6766e46e9c86','7de073c3-d0e2-40f2-8956-19d78630ce8b','da0f5e0a-9780-4b67-8964-a970cc9ace41','2026-10-15 15:16:56','2026-10-15 15:16:56'),('71defe81-6b32-4e61-ac5b-774a383eb333','7de073c3-d0e2-40f2-8956-19d78630ce8b','a20abf87-67ae-4c04-ac46-dca60878b60e','2026-10-15 15:16:56','2026-10-15 15:16:56'),('b46ee45f-bb15-4993-8a17-de26222b6167','7de073c3-d0e2-40f2-8956-19d78630ce8b','1693ce55-c602-4f4f-a281-720ae3d54b7e','2026-10-15 15:16:56','2026-10-15 15:16:56');
/*!40000 ALTER TABLE `employee_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `employees_user_id_unique` (`user_id`),
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`uuid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES ('6f6a9757-f1b3-3a1c-bf6a-41a7438db096','d17f65f1-80dc-3534-a4e1-7d9d9830ee0a','Mollitia sunt debitis ipsa molestias perspiciatis. Soluta eos unde nulla qui.',1,'2026-09-14 16:35:21','2026-09-14 16:35:21'),('c9448f84-c390-3d40-aad6-0bcb5266af54','16b32c2e-78b8-326a-b577-33ee5ca82c5e','Est perferendis quo quia dolorem ullam voluptatem quo. Magni ipsa vero nihil dolores. Tempora laudantium ducimus est voluptas omnis.',1,'2026-09-14 16:35:19','2026-09-14 16:35:19'),('fbcb622a-b76d-11f1-82e3-74563cc75024','40ce1312-2a98-3b7c-827c-9f750776d080','testowy opis',1,NULL,NULL);
INSERT INTO `employees` (`uuid`,`user_id`,`description`,`active`,`created_at`,`updated_at`) VALUES ('b61c714c-0eb3-4761-a67c-f652f3b1605e','505e699f-1f23-3393-a0a2-a5e2f94bba3e','Kolorystka z 10-letnim doświadczeniem, specjalizuje się w balayage i refleksach.',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('27945871-c3fe-4a23-9bb3-66d6764eab96','32d5a3da-22ab-3835-98a4-9cfb0f06d5ab','Stylistka fryzur damskich, specjalistka od precyzyjnych cięć i upięć okolicznościowych.',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('ec8c6720-d596-438c-801d-dc63d72b4d02','64e06cc1-71f7-3ba7-9cfb-7f1ba2ec4a40','Barber i fryzjer męski, strzyże także dzieci.',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('7de073c3-d0e2-40f2-8956-19d78630ce8b','0dd84ca8-3594-3f97-86ba-88fd70aafd5e','Trycholożka, prowadzi zabiegi regenerujące i keratynowe prostowanie.',1,'2026-10-15 15:16:56','2026-10-15 15:16:56');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
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
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_05_151108_create_service_categories_table',1),(5,'2026_09_05_154144_create_services_table',1),(6,'2026_09_05_194158_create_employees_table',1),(7,'2026_09_05_194919_create_employee_services_table',1),(8,'2026_09_05_201844_create_employee_availabilities_table',1),(9,'2026_09_07_071605_create_reservations_table',1);
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
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  KEY `reservations_user_id_foreign` (`user_id`),
  KEY `reservations_employee_id_foreign` (`employee_id`),
  KEY `reservations_service_id_foreign` (`service_id`),
  CONSTRAINT `reservations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`uuid`),
  CONSTRAINT `reservations_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`uuid`),
  CONSTRAINT `reservations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES ('7c5f2123-c1bd-11f1-bb05-74563cc75024','01a0a13a-fa1b-71fc-8eab-b172b58be54d','fbcb622a-b76d-11f1-82e3-74563cc75024','1895be2e-b0de-11f1-becb-74563cc75024','2026-10-12','12:00:00','13:00:00','confirmed',NULL,NULL,NULL);
INSERT INTO `reservations` (`uuid`,`user_id`,`employee_id`,`service_id`,`reservation_date`,`start_time`,`end_time`,`status`,`comment`,`created_at`,`updated_at`) VALUES ('4082db58-e1d5-447b-81cc-65cf41dd3100','beca6074-399a-3685-af7a-39151724b6c2','ec8c6720-d596-438c-801d-dc63d72b4d02','fb77265a-b0dd-11f1-becb-74563cc75024','2026-09-25','09:00:00','10:00:00','completed',NULL,'2026-10-15 15:16:57','2026-10-15 15:16:57'),('6edec662-a384-4cd3-b53e-428b9571568b','a6b23273-3fc8-3216-b11a-06bee889bd2e','b61c714c-0eb3-4761-a67c-f652f3b1605e','1efddaf8-a059-43c3-9e68-e62d4f4a0af2','2026-10-01','10:00:00','13:00:00','completed','Proszę o chłodny odcień blondu.','2026-10-15 15:16:57','2026-10-15 15:16:57'),('1e570f89-2db5-4a43-aaa2-a1b3b61a44a2','dc972765-0803-36b1-8fba-9bf126bad45a','7de073c3-d0e2-40f2-8956-19d78630ce8b','570d5905-597e-452d-8445-792070f82f30','2026-10-05','13:00:00','14:30:00','completed',NULL,'2026-10-15 15:16:57','2026-10-15 15:16:57'),('14dc961a-d51a-463a-9c55-be5eff6bf180','9302e1de-8c43-3572-b9f6-896fb7131736','27945871-c3fe-4a23-9bb3-66d6764eab96','1895be2e-b0de-11f1-becb-74563cc75024','2026-10-08','11:00:00','12:40:00','cancelled','Odwołane przez klientkę.','2026-10-15 15:16:57','2026-10-15 15:16:57'),('e12bdd55-f3cb-4ed9-8a56-812bf8f8c290','0e68e6e9-3e7b-38b1-8fc2-606800aeadb9','ec8c6720-d596-438c-801d-dc63d72b4d02','2c7ad07a-b0de-11f1-becb-74563cc75024','2026-10-12','10:00:00','10:45:00','completed','Syn, 7 lat.','2026-10-15 15:16:57','2026-10-15 15:16:57'),('641a3702-ed39-4a3e-a93a-faa01adfa527','a6b23273-3fc8-3216-b11a-06bee889bd2e','27945871-c3fe-4a23-9bb3-66d6764eab96','2a73f3b8-8fac-4729-a2e0-2e25ec3071d7','2026-10-17','10:00:00','11:00:00','confirmed','Fryzura na wesele.','2026-10-15 15:16:57','2026-10-15 15:16:57'),('9d60f22d-8325-481a-abe5-d7eda3fd40e3','beca6074-399a-3685-af7a-39151724b6c2','ec8c6720-d596-438c-801d-dc63d72b4d02','fb77265a-b0dd-11f1-becb-74563cc75024','2026-10-19','08:30:00','09:30:00','confirmed',NULL,'2026-10-15 15:16:57','2026-10-15 15:16:57'),('1dad5cea-3a0b-4d9a-a2ec-1289bed100fe','901f9dc1-b2fb-3c85-8e5b-7adc4c0be0da','ec8c6720-d596-438c-801d-dc63d72b4d02','fb77265a-b0dd-11f1-becb-74563cc75024','2026-10-19','12:00:00','13:00:00','pending',NULL,'2026-10-15 15:16:57','2026-10-15 15:16:57'),('0512ec45-d101-462f-ac6a-e6327e5bf65e','dc972765-0803-36b1-8fba-9bf126bad45a','b61c714c-0eb3-4761-a67c-f652f3b1605e','bc960fd8-1c5f-4228-92db-6749ec9ff6a8','2026-10-20','09:00:00','11:00:00','pending',NULL,'2026-10-15 15:16:57','2026-10-15 15:16:57'),('5c4c1590-0f10-4e15-b85f-d20c60aa02ac','9302e1de-8c43-3572-b9f6-896fb7131736','7de073c3-d0e2-40f2-8956-19d78630ce8b','d95e9df8-6f32-43c2-a205-be6b663fd127','2026-10-22','14:00:00','16:30:00','pending','Pierwszy raz, proszę o konsultację.','2026-10-15 15:16:57','2026-10-15 15:16:57'),('2d2837a1-bde4-4389-8965-2d49376b1a9d','0e68e6e9-3e7b-38b1-8fc2-606800aeadb9','27945871-c3fe-4a23-9bb3-66d6764eab96','97c19a44-13e6-4771-9af9-a929b3e9c2a6','2026-10-24','11:00:00','11:45:00','pending',NULL,'2026-10-15 15:16:57','2026-10-15 15:16:57');
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_categories`
--

DROP TABLE IF EXISTS `service_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_categories` (
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `service_categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_categories`
--

LOCK TABLES `service_categories` WRITE;
/*!40000 ALTER TABLE `service_categories` DISABLE KEYS */;
INSERT INTO `service_categories` VALUES ('67a1a395-6956-36e0-ab80-95a5c57887c9','Ciecie','Strzyżenie damskie, męskie i dziecięce dopasowane do kształtu twarzy.','fa-solid fa-scissors','2026-09-14 16:35:45','2026-09-14 16:35:45'),('97fb4404-879e-3b57-9a38-dee8a8bfa7df','Koloryzacja','Farbowanie, balayage, refleksy i inne techniki zmiany koloru włosów.','fa-solid fa-palette','2026-09-14 16:35:45','2026-09-14 16:35:45'),('fb26fea7-ec4f-3564-8835-0ff9d0bb06b8','Pielegnacja','Stylizacja, maski i produkty do codziennej pielęgnacji włosów.','fa-solid fa-pump-medical','2026-09-14 16:35:45','2026-09-14 16:35:45'),('fc951e79-49a3-340c-89bc-91e265c2fdd2','Zabiegi','Regeneracja, botoks do włosów i inne zabiegi specjalistyczne.','fa-solid fa-spa','2026-09-14 16:35:45','2026-09-14 16:35:45');
/*!40000 ALTER TABLE `service_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` smallint NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `slug` (`slug`),
  KEY `services_category_id_foreign` (`category_id`),
  CONSTRAINT `services_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`uuid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES ('1895be2e-b0de-11f1-becb-74563cc75024','67a1a395-6956-36e0-ab80-95a5c57887c9','Strzyzenie Damskie','strzyzenie-damskie','Strzyżenie z konsultacją fryzjerską, dopasowane do struktury włosów i preferencji klientki. Obejmuje mycie, cięcie i modelowanie.',100,200.00,1,NULL,NULL),('2c7ad07a-b0de-11f1-becb-74563cc75024','67a1a395-6956-36e0-ab80-95a5c57887c9','Strzyzenie Dzieciece','strzyzenie-dzieciece','Szybkie i bezstresowe strzyżenie dla najmłodszych klientów, w przyjaznej atmosferze. Idealne dla dzieci do 12. roku życia.',45,60.00,1,NULL,NULL),('fb77265a-b0dd-11f1-becb-74563cc75024','67a1a395-6956-36e0-ab80-95a5c57887c9','Strzyzenie Meskie','strzyzenie-meskie','Klasyczne lub nowoczesne cięcie dopasowane do kształtu twarzy i stylu klienta. W cenie mycie, strzyżenie i stylizacja.',60,90.00,1,NULL,NULL);
INSERT INTO `services` (`uuid`,`category_id`,`name`,`slug`,`description`,`duration`,`price`,`active`,`created_at`,`updated_at`) VALUES ('1186eea1-dd24-4ea2-b915-1a954b0435d9','97fb4404-879e-3b57-9a38-dee8a8bfa7df','Farbowanie jednolite','farbowanie-jednolite','Farbowanie całych włosów na jeden kolor, z doborem odcienia do karnacji.',90,'180.00',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('1efddaf8-a059-43c3-9e68-e62d4f4a0af2','97fb4404-879e-3b57-9a38-dee8a8bfa7df','Balayage','balayage','Ręcznie malowane, naturalne rozjaśnienia z płynnym przejściem koloru.',180,'450.00',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('bc960fd8-1c5f-4228-92db-6749ec9ff6a8','97fb4404-879e-3b57-9a38-dee8a8bfa7df','Refleksy','refleksy','Delikatne pasemka rozświetlające fryzurę.',120,'280.00',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('1693ce55-c602-4f4f-a281-720ae3d54b7e','97fb4404-879e-3b57-9a38-dee8a8bfa7df','Tonowanie','tonowanie','Neutralizacja niechcianych odcieni i odświeżenie koloru.',45,'120.00',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('4e99e81b-79d4-4513-b48a-08e1e5ab1e31','67a1a395-6956-36e0-ab80-95a5c57887c9','Podcięcie grzywki','podciecie-grzywki','Wyrównanie i wycieniowanie grzywki.',15,'30.00',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('570d5905-597e-452d-8445-792070f82f30','fc951e79-49a3-340c-89bc-91e265c2fdd2','Botoks na włosy','botoks-na-wlosy','Zabieg wypełniający strukturę włosa, wygładza i nadaje połysk.',90,'250.00',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('d95e9df8-6f32-43c2-a205-be6b663fd127','fc951e79-49a3-340c-89bc-91e265c2fdd2','Keratynowe prostowanie','keratynowe-prostowanie','Długotrwałe wygładzenie i prostowanie włosów keratyną.',150,'400.00',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('da0f5e0a-9780-4b67-8964-a970cc9ace41','fc951e79-49a3-340c-89bc-91e265c2fdd2','Regeneracja Olaplex','regeneracja-olaplex','Odbudowa wiązań we włosach zniszczonych farbowaniem.',60,'160.00',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('97c19a44-13e6-4771-9af9-a929b3e9c2a6','fb26fea7-ec4f-3564-8835-0ff9d0bb06b8','Mycie i modelowanie','mycie-i-modelowanie','Mycie, odżywka i modelowanie szczotką.',45,'80.00',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('a20abf87-67ae-4c04-ac46-dca60878b60e','fb26fea7-ec4f-3564-8835-0ff9d0bb06b8','Maska nawilżająca','maska-nawilzajaca','Intensywnie nawilżająca maska z masażem skóry głowy.',30,'70.00',1,'2026-10-15 15:16:56','2026-10-15 15:16:56'),('2a73f3b8-8fac-4729-a2e0-2e25ec3071d7','fb26fea7-ec4f-3564-8835-0ff9d0bb06b8','Upięcie okolicznościowe','upiecie-okolicznosciowe','Fryzura na wesele, studniówkę lub inną okazję.',60,'150.00',1,'2026-10-15 15:16:56','2026-10-15 15:16:56');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('gTRwu8clkvxC60LRpMR8oAxes9zgiLhhZdm0bOfR','01a0a13a-fa1b-71fc-8eab-b172b58be54d','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSXBDYXBDNWJQTWtDNm1XQ0tGekl0eVc1anlONUpNSmRMU21QMWRhdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9kYXNoYm9hcmQvcHJvZmlsZSI7czo1OiJyb3V0ZSI7czoxNzoiZGFzaGJvYXJkLnByb2ZpbGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7czozNjoiMDFhMGExM2EtZmExYi03MWZjLThlYWItYjE3MmI1OGJlNTRkIjt9',1791388958);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('client','employee','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `active` tinyint NOT NULL DEFAULT '1',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('01a0a13a-fa1b-71fc-8eab-b172b58be54d','Piotr','Michalak','mp@z.pl','123456789','admin',1,'$2y$12$V5.Ixl1x77zpHapJmDwgn.Uku4ecR1FKAAcZnWk6DfjSfAqrKHTmO',NULL,NULL,'2026-09-14 16:43:12','2026-09-14 16:43:12'),('02dc9f56-c384-3119-a5b2-9cca0713780d','Prof. Lulu Price','Larkin','johnny.fadel@example.com','+1-463-990-9480','client',0,'$2y$12$s5jZe0whlxaaAHWpxULZy.THiPBllseGi.xHNaFdT7rP01yKA7BeO','2026-09-14 16:34:16','a56OZEAADN','2026-09-14 16:34:16','2026-09-14 16:34:16'),('16b32c2e-78b8-326a-b577-33ee5ca82c5e','Ms. Daniella Luettgen DDS','Ledner','hettie31@example.com','1-831-286-4246','employee',1,'$2y$12$s5jZe0whlxaaAHWpxULZy.THiPBllseGi.xHNaFdT7rP01yKA7BeO','2026-09-14 16:34:16','uYhYM3s10P','2026-09-14 16:34:16','2026-09-14 16:34:16'),('40ce1312-2a98-3b7c-827c-9f750776d080','Tate Sauer','Turcotte','donna15@example.com','+12698054931','employee',1,'$2y$12$7M3Li073MFtDWFtet6Qat.nOiFEWmAkVeo7s1HhktJxhz6DBVqwQ.','2026-09-14 16:34:16','Dlb8laNMqBPPFeTYMXC2nFo9txpFAREDQ13njsDagaotTcddZ6ysSDEOMfi5','2026-09-14 16:34:16','2026-09-14 16:34:16'),('6b06f175-ed39-35a1-83e0-0b8d19ddd686','Marques Koss','Hermiston','ramiro.howe@example.net','(469) 943-1190','client',1,'$2y$12$s5jZe0whlxaaAHWpxULZy.THiPBllseGi.xHNaFdT7rP01yKA7BeO','2026-09-14 16:34:16','4szzxSUOfS','2026-09-14 16:34:16','2026-09-14 16:34:16'),('7afca2c1-af9c-34c5-b47e-1aa59abddc95','Joannie Hartmann','Mertz','aliya42@example.org','281-848-2784','client',1,'$2y$12$s5jZe0whlxaaAHWpxULZy.THiPBllseGi.xHNaFdT7rP01yKA7BeO','2026-09-14 16:34:16','yEtBWv48kK','2026-09-14 16:34:16','2026-09-14 16:34:16'),('80e5b58a-9368-3b68-b6e9-e0205726d31d','Thomas Bernhard DDS','Erdman','madisyn.predovic@example.net','+1 (640) 680-4066','admin',0,'$2y$12$s5jZe0whlxaaAHWpxULZy.THiPBllseGi.xHNaFdT7rP01yKA7BeO','2026-09-14 16:34:16','byRHypSgXh','2026-09-14 16:34:16','2026-09-14 16:34:16'),('a234cdc7-88ec-3dd9-91b3-4282fdad5d3c','Blanche Beatty MD','Hodkiewicz','selina.sawayn@example.org','1-351-822-2557','admin',1,'$2y$12$s5jZe0whlxaaAHWpxULZy.THiPBllseGi.xHNaFdT7rP01yKA7BeO','2026-09-14 16:34:16','FmDySybLaT','2026-09-14 16:34:16','2026-09-14 16:34:16'),('c0d59dea-f565-360d-b1b7-ce013d3d7a84','Fabian Sipes','Herman','yparker@example.com','820-985-1041','admin',1,'$2y$12$s5jZe0whlxaaAHWpxULZy.THiPBllseGi.xHNaFdT7rP01yKA7BeO','2026-09-14 16:34:16','F22niLUQVU','2026-09-14 16:34:16','2026-09-14 16:34:16'),('d17f65f1-80dc-3534-a4e1-7d9d9830ee0a','Nelson Brekke','McCullough','lucas.rowe@example.com','1-321-956-2151','employee',1,'$2y$12$s5jZe0whlxaaAHWpxULZy.THiPBllseGi.xHNaFdT7rP01yKA7BeO','2026-09-14 16:34:16','acYqjKJ1i2','2026-09-14 16:34:16','2026-09-14 16:34:16'),('f934b06b-7da6-35df-8d77-cfed820d338d','Dr. Bernita Little PhD','Bode','bbauch@example.org','+1.206.470.3073','admin',1,'$2y$12$s5jZe0whlxaaAHWpxULZy.THiPBllseGi.xHNaFdT7rP01yKA7BeO','2026-09-14 16:34:16','Mry8lhV8am','2026-09-14 16:34:16','2026-09-14 16:34:16');
INSERT INTO `users` (`uuid`,`name`,`surname`,`email`,`phone`,`role`,`active`,`password`,`email_verified_at`,`remember_token`,`created_at`,`updated_at`) VALUES ('0304525a-68e4-3ed5-bed9-762ed885701f','Admin','Systemowy','admin@salon.test','500100100','admin',1,'$2y$12$w644b6I8FPNj5XgQoGq0k.fnmjNbC3r5Vknghc1kkM6jNLFwtHudm','2026-10-15 15:16:56','qbrnMdXmDH','2026-10-15 15:16:56','2026-10-15 15:16:56'),('505e699f-1f23-3393-a0a2-a5e2f94bba3e','Anna','Kowalska','anna.kowalska@salon.test','500200201','employee',1,'$2y$12$w644b6I8FPNj5XgQoGq0k.fnmjNbC3r5Vknghc1kkM6jNLFwtHudm','2026-10-15 15:16:56','TdzAL9y9yf','2026-10-15 15:16:56','2026-10-15 15:16:56'),('32d5a3da-22ab-3835-98a4-9cfb0f06d5ab','Marta','Nowak','marta.nowak@salon.test','500200202','employee',1,'$2y$12$w644b6I8FPNj5XgQoGq0k.fnmjNbC3r5Vknghc1kkM6jNLFwtHudm','2026-10-15 15:16:56','5aZauqP06h','2026-10-15 15:16:56','2026-10-15 15:16:56'),('64e06cc1-71f7-3ba7-9cfb-7f1ba2ec4a40','Piotr','Wiśniewski','piotr.wisniewski@salon.test','500200203','employee',1,'$2y$12$w644b6I8FPNj5XgQoGq0k.fnmjNbC3r5Vknghc1kkM6jNLFwtHudm','2026-10-15 15:16:56','eZX4K8SEfJ','2026-10-15 15:16:56','2026-10-15 15:16:56'),('0dd84ca8-3594-3f97-86ba-88fd70aafd5e','Katarzyna','Zielińska','katarzyna.zielinska@salon.test','500200204','employee',1,'$2y$12$w644b6I8FPNj5XgQoGq0k.fnmjNbC3r5Vknghc1kkM6jNLFwtHudm','2026-10-15 15:16:56','Nn4RqKgYpu','2026-10-15 15:16:56','2026-10-15 15:16:56'),('beca6074-399a-3685-af7a-39151724b6c2','Jan','Kamiński','jan.kaminski@example.com','600300301','client',1,'$2y$12$w644b6I8FPNj5XgQoGq0k.fnmjNbC3r5Vknghc1kkM6jNLFwtHudm','2026-10-15 15:16:56','yymldLNrgJ','2026-10-15 15:16:56','2026-10-15 15:16:56'),('a6b23273-3fc8-3216-b11a-06bee889bd2e','Ewa','Lewandowska','ewa.lewandowska@example.com','600300302','client',1,'$2y$12$w644b6I8FPNj5XgQoGq0k.fnmjNbC3r5Vknghc1kkM6jNLFwtHudm','2026-10-15 15:16:56','S5nG2ATRko','2026-10-15 15:16:56','2026-10-15 15:16:56'),('0e68e6e9-3e7b-38b1-8fc2-606800aeadb9','Tomasz','Wójcik','tomasz.wojcik@example.com','600300303','client',1,'$2y$12$w644b6I8FPNj5XgQoGq0k.fnmjNbC3r5Vknghc1kkM6jNLFwtHudm','2026-10-15 15:16:56','PcNuP1wkQU','2026-10-15 15:16:56','2026-10-15 15:16:56'),('dc972765-0803-36b1-8fba-9bf126bad45a','Magdalena','Kozłowska','magdalena.kozlowska@example.com','600300304','client',1,'$2y$12$w644b6I8FPNj5XgQoGq0k.fnmjNbC3r5Vknghc1kkM6jNLFwtHudm','2026-10-15 15:16:56','TTdwcjnRgl','2026-10-15 15:16:56','2026-10-15 15:16:56'),('901f9dc1-b2fb-3c85-8e5b-7adc4c0be0da','Michał','Jankowski','michal.jankowski@example.com','600300305','client',1,'$2y$12$w644b6I8FPNj5XgQoGq0k.fnmjNbC3r5Vknghc1kkM6jNLFwtHudm','2026-10-15 15:16:56','BepJSu8Sis','2026-10-15 15:16:56','2026-10-15 15:16:56'),('9302e1de-8c43-3572-b9f6-896fb7131736','Agnieszka','Mazur','agnieszka.mazur@example.com','600300306','client',1,'$2y$12$w644b6I8FPNj5XgQoGq0k.fnmjNbC3r5Vknghc1kkM6jNLFwtHudm','2026-10-15 15:16:56','dP0i3euyZ9','2026-10-15 15:16:56','2026-10-15 15:16:56');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-07 22:33:43
