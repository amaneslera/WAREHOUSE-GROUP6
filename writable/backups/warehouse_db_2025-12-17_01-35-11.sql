-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: warehouse_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `warehouse_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `warehouse_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `warehouse_db`;

--
-- Table structure for table `accounts_payable`
--

DROP TABLE IF EXISTS `accounts_payable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts_payable` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(100) NOT NULL,
  `po_reference` varchar(100) DEFAULT NULL COMMENT 'Purchase Order reference number',
  `delivery_receipt` varchar(100) DEFAULT NULL COMMENT 'Delivery receipt number',
  `vendor_id` int(11) unsigned NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `invoice_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(15,2) NOT NULL COMMENT 'Remaining amount to pay',
  `status` enum('pending','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'e.g., Check, Bank Transfer, Cash',
  `payment_reference` varchar(100) DEFAULT NULL,
  `warehouse_id` int(11) unsigned DEFAULT NULL COMMENT 'Receiving warehouse (if material purchase)',
  `stock_movement_ids` text DEFAULT NULL COMMENT 'Comma-separated stock movement IDs for matching',
  `matching_status` enum('unmatched','matched','discrepancy') DEFAULT 'unmatched' COMMENT 'Status of invoice matching with documents',
  `discrepancy_notes` text DEFAULT NULL COMMENT 'Notes about discrepancies found during matching',
  `matched_by` int(11) unsigned DEFAULT NULL COMMENT 'User ID who performed the matching',
  `matched_at` datetime DEFAULT NULL COMMENT 'Timestamp when matching was completed',
  `created_by` int(11) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `accounts_payable_warehouse_id_foreign` (`warehouse_id`),
  KEY `accounts_payable_created_by_foreign` (`created_by`),
  KEY `vendor_id` (`vendor_id`),
  KEY `status` (`status`),
  KEY `due_date` (`due_date`),
  CONSTRAINT `accounts_payable_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `accounts_payable_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `accounts_payable_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts_payable`
--

LOCK TABLES `accounts_payable` WRITE;
/*!40000 ALTER TABLE `accounts_payable` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_payable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts_receivable`
--

DROP TABLE IF EXISTS `accounts_receivable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts_receivable` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(100) NOT NULL,
  `client_id` int(11) unsigned NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `invoice_amount` decimal(15,2) NOT NULL,
  `received_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(15,2) NOT NULL COMMENT 'Remaining amount to collect',
  `status` enum('pending','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'e.g., Check, Bank Transfer, Cash',
  `payment_reference` varchar(100) DEFAULT NULL,
  `warehouse_id` int(11) unsigned DEFAULT NULL COMMENT 'Source warehouse (if material sale)',
  `created_by` int(11) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `accounts_receivable_warehouse_id_foreign` (`warehouse_id`),
  KEY `accounts_receivable_created_by_foreign` (`created_by`),
  KEY `client_id` (`client_id`),
  KEY `status` (`status`),
  KEY `due_date` (`due_date`),
  CONSTRAINT `accounts_receivable_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `accounts_receivable_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `accounts_receivable_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts_receivable`
--

LOCK TABLES `accounts_receivable` WRITE;
/*!40000 ALTER TABLE `accounts_receivable` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_receivable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_payment_transactions`
--

DROP TABLE IF EXISTS `ap_payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_payment_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ap_id` bigint(20) unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `processed_by` int(11) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ap_payment_transactions_processed_by_foreign` (`processed_by`),
  KEY `ap_id` (`ap_id`),
  CONSTRAINT `ap_payment_transactions_ap_id_foreign` FOREIGN KEY (`ap_id`) REFERENCES `accounts_payable` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ap_payment_transactions_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_payment_transactions`
--

LOCK TABLES `ap_payment_transactions` WRITE;
/*!40000 ALTER TABLE `ap_payment_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ap_payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ar_payment_transactions`
--

DROP TABLE IF EXISTS `ar_payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_payment_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ar_id` bigint(20) unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `processed_by` int(11) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ar_payment_transactions_processed_by_foreign` (`processed_by`),
  KEY `ar_id` (`ar_id`),
  CONSTRAINT `ar_payment_transactions_ar_id_foreign` FOREIGN KEY (`ar_id`) REFERENCES `accounts_receivable` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ar_payment_transactions_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ar_payment_transactions`
--

LOCK TABLES `ar_payment_transactions` WRITE;
/*!40000 ALTER TABLE `ar_payment_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ar_payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` int(11) unsigned DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `module` (`module`),
  KEY `record_id` (`record_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,7,'user_create','user_management',12,NULL,'{\"email\":\"draine123@gmail.com\",\"role\":\"warehouse_manager\",\"is_active\":1}','Created user #12','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2025-12-16 21:15:27'),(2,7,'user_status_change','user_management',12,'{\"is_active\":1}','{\"is_active\":0}','Deactivated user #12','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2025-12-16 21:15:37'),(3,7,'user_status_change','user_management',11,'{\"is_active\":1}','{\"is_active\":0}','Deactivated user #11','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2025-12-16 21:15:41'),(4,7,'backup_auto','backups',NULL,NULL,'{\"file\":\"warehouse_db_2025-12-16_21-37-51.sql\"}','Automatic daily backup created','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2025-12-16 21:37:52'),(5,7,'backup_download','backups',NULL,NULL,'{\"file\":\"warehouse_db_2025-12-16_21-37-51.sql\"}','Downloaded backup','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2025-12-16 22:01:12'),(6,7,'backup_run','backups',NULL,NULL,'{\"file\":\"warehouse_db_2025-12-16_22-01-17.sql\"}','Manual backup created','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2025-12-16 22:01:17'),(7,4,'pr_create','procurement',1,NULL,'{\"pr_number\":\"PR-2025-001\"}','Created PR PR-2025-001','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2025-12-16 22:43:29'),(8,4,'pr_submit','procurement',1,'{\"status\":\"draft\"}','{\"status\":\"submitted\"}','Submitted PR PR-2025-001','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2025-12-16 22:43:38'),(9,8,'pr_approve','top_management',1,'{\"status\":\"submitted\"}','{\"status\":\"approved\"}','Approved PR PR-2025-001','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2025-12-16 22:57:22');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Construction materials','Materials used for construction projects','2025-12-16 03:18:21','2025-12-16 03:18:21'),(2,'Tools','Hand tools and power tools','2025-12-16 03:18:21','2025-12-16 03:18:21'),(3,'Safety','Safety equipment and gear','2025-12-16 03:18:21','2025-12-16 03:18:21'),(4,'Electrical','Electrical components and supplies','2025-12-16 03:18:21','2025-12-16 03:18:21'),(5,'Equipment','Heavy equipment and machinery','2025-12-16 03:18:21','2025-12-16 03:18:21');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client_code` varchar(50) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(100) DEFAULT NULL COMMENT 'TIN or Tax Identification Number',
  `credit_limit` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Maximum credit allowed',
  `payment_terms` varchar(100) DEFAULT NULL COMMENT 'e.g., Net 30, Net 60, COD',
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_code` (`client_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` varchar(50) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) unsigned NOT NULL,
  `warehouse_id` int(11) unsigned NOT NULL,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_of_measure` varchar(50) NOT NULL DEFAULT 'units',
  `status` enum('active','inactive','discontinued') NOT NULL DEFAULT 'active',
  `supplier_info` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id` (`item_id`),
  KEY `inventory_items_category_id_foreign` (`category_id`),
  KEY `inventory_items_warehouse_id_foreign` (`warehouse_id`),
  CONSTRAINT `inventory_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventory_items_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
INSERT INTO `inventory_items` VALUES (1,'INV001','Steel','High-quality construction steel for building projects. Meets industry standards and specifications.',1,1,4140,50,450.00,'units','active',NULL,'2025-12-16 03:18:21','2025-12-16 08:45:48'),(2,'INV002','Cement','Portland cement for construction work.',1,2,100,75,25.50,'units','active',NULL,'2025-12-16 03:18:21','2025-12-16 05:45:03'),(3,'INV003','Power Drill','Cordless power drill with multiple speed settings.',2,1,10,20,125.00,'units','active',NULL,'2025-12-16 03:18:21','2025-12-16 20:15:33'),(4,'INV004','Safety Helmet','OSHA compliant safety helmets for construction workers.',3,3,45,30,35.75,'units','active',NULL,'2025-12-16 03:18:21','2025-12-16 03:18:21'),(5,'INV005','Electrical Wire','12 AWG electrical wire for building wiring.',4,2,0,100,2.75,'units','active',NULL,'2025-12-16 03:18:21','2025-12-16 03:18:21'),(6,'INV006','Forklift','Electric forklift for warehouse operations.',5,1,3,2,25000.00,'units','active',NULL,'2025-12-16 03:18:21','2025-12-16 03:18:21'),(8,'INV002-3','Cement','Portland cement for construction work.',1,3,100,75,25.50,'units','active',NULL,'2025-12-16 05:44:06','2025-12-16 05:44:06'),(9,'INV003-3','Power Drill','Cordless power drill with multiple speed settings.',2,3,5,20,125.00,'units','active',NULL,'2025-12-16 20:15:34','2025-12-16 20:15:34');
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (12,'2025-08-31-132948','App\\Database\\Migrations\\CreateUsersTable','default','App',1761911205,1),(13,'2025-09-03-100000','App\\Database\\Migrations\\CreateWarehousesTable','default','App',1761911205,1),(14,'2025-09-03-100100','App\\Database\\Migrations\\CreateCategoriesTable','default','App',1761911205,1),(15,'2025-09-03-100200','App\\Database\\Migrations\\CreateInventoryItemsTable','default','App',1761911205,1),(16,'2025-09-03-100300','App\\Database\\Migrations\\CreateStockMovementsTable','default','App',1761911205,1),(17,'2025-10-31-100000','App\\Database\\Migrations\\CreateVendorsTable','default','App',1761911205,1),(18,'2025-10-31-100100','App\\Database\\Migrations\\CreateClientsTable','default','App',1761911205,1),(19,'2025-10-31-100200','App\\Database\\Migrations\\CreateAccountsPayableTable','default','App',1761911205,1),(20,'2025-10-31-100300','App\\Database\\Migrations\\CreateAccountsReceivableTable','default','App',1761911205,1),(21,'2025-10-31-100400','App\\Database\\Migrations\\CreateApPaymentTransactionsTable','default','App',1761911205,1),(22,'2025-10-31-100500','App\\Database\\Migrations\\CreateArPaymentTransactionsTable','default','App',1761911205,1),(23,'2025-12-16-100000','App\\Database\\Migrations\\AddApprovalColumnsToStockMovements','default','App',1765854818,2),(24,'2025-12-16-172135','App\\Database\\Migrations\\AddMatchingFieldsToAccountsPayableTable','default','App',1765918336,3),(25,'2025-12-16-172426','App\\Database\\Migrations\\CreateAuditLogTable','default','App',1765918336,3),(26,'2025-12-17-043500','App\\Database\\Migrations\\AddUserStatusAndLoginColumnsToUsers','default','App',1765918336,3),(27,'2025-12-17-060000','App\\Database\\Migrations\\CreatePurchaseRequestsTable','default','App',1765922564,4),(28,'2025-12-17-060100','App\\Database\\Migrations\\CreatePurchaseRequestItemsTable','default','App',1765922564,4),(29,'2025-12-17-060200','App\\Database\\Migrations\\CreatePurchaseOrdersTable','default','App',1765922565,4),(30,'2025-12-17-060300','App\\Database\\Migrations\\CreatePurchaseOrderItemsTable','default','App',1765922565,4),(31,'2025-12-17-062500','App\\Database\\Migrations\\AddApprovalFieldsToPurchaseOrdersTable','default','App',1765924630,5),(32,'2025-12-17-063000','App\\Database\\Migrations\\CreateWarehouseTasksTable','default','App',1765924713,6);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int(11) unsigned NOT NULL,
  `inventory_item_id` int(11) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_id` (`purchase_order_id`),
  KEY `inventory_item_id` (`inventory_item_id`),
  CONSTRAINT `purchase_order_items_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `po_number` varchar(50) NOT NULL,
  `purchase_request_id` int(11) unsigned DEFAULT NULL,
  `vendor_id` int(11) unsigned NOT NULL,
  `warehouse_id` int(11) unsigned NOT NULL,
  `status` enum('pending','partial','complete','cancelled') NOT NULL DEFAULT 'pending',
  `po_approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `po_approved_by` int(11) unsigned DEFAULT NULL,
  `po_approved_at` datetime DEFAULT NULL,
  `po_approval_notes` text DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number` (`po_number`),
  UNIQUE KEY `purchase_request_id` (`purchase_request_id`),
  KEY `status` (`status`),
  KEY `vendor_id` (`vendor_id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `purchase_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_orders_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `purchase_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_orders_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_request_items`
--

DROP TABLE IF EXISTS `purchase_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_request_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_request_id` int(11) unsigned NOT NULL,
  `inventory_item_id` int(11) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_request_id` (`purchase_request_id`),
  KEY `inventory_item_id` (`inventory_item_id`),
  CONSTRAINT `purchase_request_items_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_request_items_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_request_items`
--

LOCK TABLES `purchase_request_items` WRITE;
/*!40000 ALTER TABLE `purchase_request_items` DISABLE KEYS */;
INSERT INTO `purchase_request_items` VALUES (1,1,5,2000,'','2025-12-16 22:43:29','2025-12-16 22:43:29');
/*!40000 ALTER TABLE `purchase_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_requests`
--

DROP TABLE IF EXISTS `purchase_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_requests` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pr_number` varchar(50) NOT NULL,
  `requested_by` int(11) unsigned NOT NULL,
  `warehouse_id` int(11) unsigned DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  `approved_by` int(11) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pr_number` (`pr_number`),
  KEY `purchase_requests_approved_by_foreign` (`approved_by`),
  KEY `status` (`status`),
  KEY `requested_by` (`requested_by`),
  KEY `warehouse_id` (`warehouse_id`),
  CONSTRAINT `purchase_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `purchase_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_requests_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_requests`
--

LOCK TABLES `purchase_requests` WRITE;
/*!40000 ALTER TABLE `purchase_requests` DISABLE KEYS */;
INSERT INTO `purchase_requests` VALUES (1,'PR-2025-001',4,1,'approved',8,'2025-12-16 22:57:22','','2025-12-16 22:43:29','2025-12-16 22:57:22',NULL);
/*!40000 ALTER TABLE `purchase_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_item_id` int(11) unsigned NOT NULL,
  `movement_type` enum('in','out','transfer','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `from_warehouse_id` int(11) unsigned DEFAULT NULL,
  `to_warehouse_id` int(11) unsigned DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `performed_by` int(11) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) unsigned DEFAULT NULL,
  `rejected_by` int(11) unsigned DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_inventory_item_id_foreign` (`inventory_item_id`),
  KEY `stock_movements_from_warehouse_id_foreign` (`from_warehouse_id`),
  KEY `stock_movements_to_warehouse_id_foreign` (`to_warehouse_id`),
  KEY `stock_movements_performed_by_foreign` (`performed_by`),
  CONSTRAINT `stock_movements_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `stock_movements_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stock_movements_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stock_movements_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,1,'in',50,NULL,1,'PO-2025-001','New stock received from supplier',3,'2025-12-16 01:18:39','2025-12-16 05:09:46','approved',1,NULL,'',NULL),(2,2,'in',75,NULL,2,'PO-2025-002','Cement delivery from vendor',4,'2025-12-16 02:18:39','2025-12-16 03:26:38','approved',1,NULL,'awda',NULL),(3,5,'in',150,NULL,2,'PO-2025-003','Emergency stock of electrical wire',5,'2025-12-16 02:48:39','2025-12-16 03:23:13','approved',1,NULL,'',NULL),(4,3,'out',5,1,NULL,'DO-2025-001','Drills sent to Site A for project work',3,'2025-12-15 03:18:39','2025-12-15 03:18:39','approved',1,NULL,'Approved - requested by project manager',NULL),(5,4,'out',20,3,NULL,'DO-2025-002','Safety helmets for new worker onboarding',6,'2025-12-14 03:18:39','2025-12-14 03:18:39','approved',1,NULL,'Approved - routine distribution',NULL),(6,1,'transfer',25,1,2,'TR-2025-001','Transfer to balance stock levels',4,'2025-12-13 03:18:39','2025-12-13 03:18:39','approved',2,NULL,'Approved - stock balancing',NULL),(7,2,'out',100,2,NULL,'DO-2025-003','Large cement order for Site B',5,'2025-12-12 03:18:39','2025-12-12 03:18:39','rejected',NULL,1,NULL,'Stock insufficient - only 200 available but requesting 100. Insufficient safety stock.'),(8,1,'out',10,1,NULL,'STAFF-1765863516747','',2,'2025-12-16 05:38:36','2025-12-16 05:39:38','approved',1,NULL,'',NULL),(10,2,'transfer',100,2,3,'STAFF-1765863845823','',2,'2025-12-16 05:44:06','2025-12-16 05:45:03','approved',1,NULL,'',NULL),(11,1,'in',2000,NULL,2,'STAFF-1765874738313','',2,'2025-12-16 08:45:38','2025-12-16 08:45:48','approved',1,NULL,'',NULL),(12,3,'transfer',5,1,3,'STAFF-1765916133786','',2,'2025-12-16 20:15:33','2025-12-16 20:15:33','pending',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'warehouse_staff',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Manager','Warehouse','','manager@example.com','$2y$10$NSydn.9YCEfkZVEKCQ9cpOakmZjjhED6YoEeSJWLWf0mE0T34DuOm','warehouse_manager','2025-10-31 11:47:01','2025-10-31 11:47:01',1,'2025-12-17 01:33:59'),(2,'Staff','Warehouse','','staff@example.com','$2y$10$RzBqLc2HY.wi7d11EuMBn.1jcPLt2MFBRzYyveE6ik5XC8yYd8/Fe','warehouse_staff','2025-10-31 11:47:01','2025-10-31 11:47:01',1,'2025-12-17 01:34:25'),(3,'Auditor','Inventory','','auditor@example.com','$2y$10$eBXPhshAzpU9rz43tbvd/eyo0UIVIit8dNRxL3lcFhv1igCVj7mgW','inventory_auditor','2025-10-31 11:47:01','2025-10-31 11:47:01',1,NULL),(4,'Officer','Procurement','','procurement@example.com','$2y$10$x/T87n0KPnA12DG9nUDapegZyKMK31gGDosUZHt0TT9Q5qiqnTl4y','procurement_officer','2025-10-31 11:47:01','2025-10-31 11:47:01',1,'2025-12-16 22:42:26'),(5,'Clerk','Accounts Payable','','apclerk@example.com','$2y$10$35ijkfn8KU/rAF/bqQ6o6Or5rIWxDQapiHZiAbd3rhKINR1t0XHMe','accounts_payable_clerk','2025-10-31 11:47:01','2025-10-31 11:47:01',1,'2025-12-17 01:28:26'),(6,'Clerk','Accounts Receivable','','arclerk@example.com','$2y$10$t1Y5OP2wlsD2KnfaEIh6EerbcgjZiWmolCFT39FYRPt1ZqMOgnpSK','accounts_receivable_clerk','2025-10-31 11:47:01','2025-10-31 11:47:01',1,'2025-12-17 01:28:56'),(7,'Administrator','IT','','itadmin@example.com','$2y$10$HuoB.QOyioeJHECx9W/87OsJtYWkZnt3C87M.gRT/hOHi4wkKH91q','it_administrator','2025-10-31 11:47:01','2025-10-31 11:47:01',1,'2025-12-17 01:34:58'),(8,'Management','Top','','topmanagement@example.com','$2y$10$jA58NrQ2GFcQWuKVB8/ijevBuSAHOuNITeNOvBhCxUK76yTc7dpvy','top_management','2025-10-31 11:47:01','2025-10-31 11:47:01',1,'2025-12-16 23:10:40'),(11,'Draine','Gray','Ray','draine@gmail.com','$2y$10$IzmcKNTX/3IMdkRED7AN.uuJixFoAtxgB4DW.iVnAsuTcWrgwFfa.','warehouse_manager','2025-12-16 21:01:34','2025-12-16 21:15:41',0,NULL),(12,'Draine','Gray','Ray','draine123@gmail.com','$2y$10$pj7ebg9DD9ND7VEq/tTEje.gj7rU2wBJwD7gdkR9VYaXtRxGdhAVy','warehouse_manager','2025-12-16 21:15:27','2025-12-16 21:15:37',0,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_code` varchar(50) NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(100) DEFAULT NULL COMMENT 'TIN or Tax Identification Number',
  `payment_terms` varchar(100) DEFAULT NULL COMMENT 'e.g., Net 30, Net 60, COD',
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_code` (`vendor_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_tasks`
--

DROP TABLE IF EXISTS `warehouse_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_tasks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `po_id` int(11) unsigned NOT NULL,
  `assigned_staff_id` int(11) unsigned NOT NULL,
  `warehouse_id` int(11) unsigned NOT NULL,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `scheduled_at` datetime DEFAULT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_id` (`po_id`),
  KEY `warehouse_tasks_created_by_foreign` (`created_by`),
  KEY `assigned_staff_id` (`assigned_staff_id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `status` (`status`),
  CONSTRAINT `warehouse_tasks_assigned_staff_id_foreign` FOREIGN KEY (`assigned_staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `warehouse_tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `warehouse_tasks_po_id_foreign` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `warehouse_tasks_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_tasks`
--

LOCK TABLES `warehouse_tasks` WRITE;
/*!40000 ALTER TABLE `warehouse_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `warehouse_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouses`
--

DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_name` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouses`
--

LOCK TABLES `warehouses` WRITE;
/*!40000 ALTER TABLE `warehouses` DISABLE KEYS */;
INSERT INTO `warehouses` VALUES (1,'Building A','North Wing',1000,'active','2025-12-16 03:18:21','2025-12-16 03:18:21'),(2,'Building B','South Wing',800,'active','2025-12-16 03:18:21','2025-12-16 03:18:21'),(3,'Building C','East Wing',600,'active','2025-12-16 03:18:21','2025-12-16 03:18:21');
/*!40000 ALTER TABLE `warehouses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'warehouse_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-17  9:35:12
