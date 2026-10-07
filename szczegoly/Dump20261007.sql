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

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'f4cc2eec-a6ca-11f1-b770-74563cc75024:1-3555';

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
