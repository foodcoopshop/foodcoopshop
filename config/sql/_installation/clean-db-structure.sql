
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
DROP TABLE IF EXISTS `fcs_action_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_action_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL DEFAULT '',
  `customer_id` int unsigned NOT NULL DEFAULT '0',
  `object_id` int unsigned NOT NULL DEFAULT '0',
  `object_type` varchar(255) NOT NULL DEFAULT '',
  `text` mediumtext NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_address` (
  `id_address` int unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int unsigned NOT NULL DEFAULT '0',
  `id_manufacturer` int unsigned NOT NULL DEFAULT '0',
  `lastname` varchar(50) NOT NULL DEFAULT '',
  `firstname` varchar(50) NOT NULL DEFAULT '',
  `address1` varchar(128) NOT NULL DEFAULT '',
  `address2` varchar(128) DEFAULT NULL,
  `postcode` varchar(12) DEFAULT NULL,
  `city` varchar(64) NOT NULL DEFAULT '',
  `comment` mediumtext,
  `phone` varchar(32) DEFAULT NULL,
  `phone_mobile` varchar(32) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_upd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_address`),
  KEY `address_customer` (`id_customer`),
  KEY `id_manufacturer` (`id_manufacturer`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_attribute` (
  `id_attribute` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `can_be_used_as_unit` tinyint unsigned NOT NULL DEFAULT '0',
  `active` int NOT NULL DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id_attribute`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_barcodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_barcodes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL DEFAULT '0',
  `product_attribute_id` int unsigned NOT NULL DEFAULT '0',
  `barcode` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`,`product_attribute_id`),
  KEY `barcode` (`barcode`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_blog_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_blog_posts` (
  `id_blog_post` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `short_description` varchar(100) NOT NULL,
  `content` longtext NOT NULL,
  `id_customer` int unsigned NOT NULL DEFAULT '0',
  `id_manufacturer` int unsigned DEFAULT NULL,
  `is_private` int unsigned NOT NULL DEFAULT '0',
  `active` int DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  `show_on_start_page_until` date DEFAULT NULL,
  PRIMARY KEY (`id_blog_post`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_cart_product_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_cart_product_units` (
  `id_cart_product` int unsigned NOT NULL,
  `ordered_quantity_in_units` decimal(10,3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_cart_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_cart_products` (
  `id_cart_product` int unsigned NOT NULL AUTO_INCREMENT,
  `id_cart` int unsigned NOT NULL DEFAULT '0',
  `id_product` int unsigned NOT NULL DEFAULT '0',
  `id_product_attribute` int unsigned NOT NULL DEFAULT '0',
  `amount` int unsigned NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id_cart_product`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_carts` (
  `id_cart` int unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int unsigned NOT NULL DEFAULT '0',
  `cart_type` int unsigned NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id_cart`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_category` (
  `id_category` int unsigned NOT NULL AUTO_INCREMENT,
  `id_parent` int unsigned DEFAULT '0',
  `name` varchar(128) NOT NULL,
  `description` mediumtext NOT NULL,
  `nleft` int NOT NULL DEFAULT '0',
  `nright` int NOT NULL DEFAULT '0',
  `active` tinyint unsigned NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id_category`),
  KEY `category_parent` (`id_parent`),
  KEY `nleftrightactive` (`nleft`,`nright`,`active`),
  KEY `nright` (`nright`),
  KEY `activenleft` (`active`,`nleft`),
  KEY `activenright` (`active`,`nright`),
  KEY `active` (`active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_category_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_category_product` (
  `id_category` int unsigned NOT NULL DEFAULT '0',
  `id_product` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_category`,`id_product`),
  KEY `id_product` (`id_product`),
  KEY `id_category` (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_configuration` (
  `id_configuration` int unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(254) NOT NULL DEFAULT '',
  `text` mediumtext NOT NULL,
  `value` mediumtext,
  `type` varchar(20) NOT NULL DEFAULT '',
  `position` int unsigned NOT NULL DEFAULT '0',
  `locale` varchar(5) DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_upd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_configuration`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_cronjob_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_cronjob_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cronjob_id` int unsigned NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `success` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_cronjobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_cronjobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `time_interval` varchar(50) NOT NULL,
  `day_of_month` tinyint unsigned DEFAULT NULL,
  `weekday` varchar(50) DEFAULT NULL,
  `not_before_time` time NOT NULL,
  `active` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_customer` (
  `id_customer` int unsigned NOT NULL AUTO_INCREMENT,
  `id_default_group` int unsigned NOT NULL DEFAULT '1',
  `is_company` tinyint NOT NULL DEFAULT '0',
  `firstname` varchar(50) NOT NULL DEFAULT '',
  `lastname` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `passwd` char(60) DEFAULT NULL,
  `tmp_new_passwd` char(60) DEFAULT NULL,
  `activate_new_password_code` varchar(12) DEFAULT NULL,
  `auto_login_hash` varchar(40) DEFAULT NULL,
  `email_order_reminder_enabled` tinyint unsigned NOT NULL DEFAULT '0',
  `terms_of_use_accepted_date` date NOT NULL DEFAULT '1000-01-01',
  `activate_email_code` varchar(12) DEFAULT NULL,
  `active` tinyint unsigned NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_upd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `timebased_currency_enabled` tinyint unsigned NOT NULL DEFAULT '0',
  `use_camera_for_barcode_scanning` tinyint unsigned DEFAULT '0',
  `user_id_registrierkasse` int unsigned DEFAULT '0',
  `shopping_price` varchar(2) DEFAULT 'SP',
  `check_credit_reminder_enabled` tinyint unsigned DEFAULT '1',
  `invoices_per_email_enabled` tinyint unsigned DEFAULT '1',
  `pickup_day_reminder_enabled` tinyint unsigned DEFAULT '1',
  `credit_upload_reminder_enabled` tinyint unsigned DEFAULT '1',
  `newsletter_enabled` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id_customer`),
  KEY `customer_email` (`email`),
  KEY `customer_login` (`email`,`passwd`),
  KEY `id_customer_passwd` (`id_customer`,`passwd`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_deposits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_deposits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_product` int unsigned NOT NULL DEFAULT '0',
  `id_product_attribute` int unsigned NOT NULL DEFAULT '0',
  `deposit` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_images` (
  `id_image` int unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_image`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_invoice_taxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_invoice_taxes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int unsigned NOT NULL DEFAULT '0',
  `tax_rate` double(20,6) NOT NULL DEFAULT '0.000000',
  `total_price_tax_excl` double(20,6) NOT NULL DEFAULT '0.000000',
  `total_price_tax` double(20,6) NOT NULL DEFAULT '0.000000',
  `total_price_tax_incl` double(20,6) NOT NULL DEFAULT '0.000000',
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_manufacturer` int unsigned NOT NULL DEFAULT '0',
  `invoice_number` varchar(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_customer` int unsigned NOT NULL DEFAULT '0',
  `paid_in_cash` tinyint unsigned DEFAULT '0',
  `filename` varchar(512) NOT NULL DEFAULT '',
  `email_status` varchar(30) DEFAULT NULL,
  `cancellation_invoice_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_manufacturer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_manufacturer` (
  `id_manufacturer` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `description` longtext,
  `short_description` mediumtext,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `is_private` int unsigned NOT NULL DEFAULT '0',
  `uid_number` varchar(30) DEFAULT NULL,
  `additional_text_for_invoice` mediumtext,
  `iban` varchar(22) DEFAULT NULL,
  `bic` varchar(11) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `firmenbuchnummer` varchar(20) DEFAULT NULL,
  `firmengericht` varchar(150) DEFAULT NULL,
  `aufsichtsbehoerde` varchar(150) DEFAULT NULL,
  `kammer` varchar(150) DEFAULT NULL,
  `homepage` varchar(255) DEFAULT NULL,
  `id_customer` int unsigned DEFAULT NULL,
  `variable_member_fee` int unsigned DEFAULT NULL,
  `send_invoice` tinyint unsigned DEFAULT NULL,
  `send_order_list` tinyint unsigned DEFAULT NULL,
  `default_tax_id` int unsigned DEFAULT NULL,
  `default_tax_id_purchase_price` int unsigned DEFAULT NULL,
  `send_order_list_cc` varchar(512) DEFAULT NULL,
  `send_instant_order_notification` tinyint unsigned DEFAULT NULL,
  `send_ordered_product_deleted_notification` int unsigned DEFAULT NULL,
  `send_ordered_product_price_changed_notification` int unsigned DEFAULT NULL,
  `send_ordered_product_amount_changed_notification` int unsigned DEFAULT NULL,
  `enabled_sync_domains` varchar(50) DEFAULT NULL,
  `timebased_currency_enabled` tinyint unsigned NOT NULL DEFAULT '0',
  `timebased_currency_max_percentage` tinyint unsigned NOT NULL DEFAULT '30',
  `timebased_currency_max_credit_balance` int unsigned DEFAULT '360000',
  `stock_management_enabled` tinyint unsigned NOT NULL DEFAULT '0',
  `send_product_sold_out_limit_reached_for_manufacturer` tinyint unsigned NOT NULL DEFAULT '0',
  `send_product_sold_out_limit_reached_for_contact_person` tinyint unsigned NOT NULL DEFAULT '0',
  `no_delivery_days` mediumtext,
  `include_stock_products_in_order_lists` tinyint unsigned NOT NULL DEFAULT '1',
  `send_delivery_notes` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_manufacturer`),
  KEY `stock_management_enabled` (`stock_management_enabled`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_order_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_order_detail` (
  `id_order_detail` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL DEFAULT '0',
  `product_attribute_id` int unsigned DEFAULT NULL,
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `product_amount` int unsigned NOT NULL DEFAULT '0',
  `total_price_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_price_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `tax_unit_amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
  `tax_total_amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
  `tax_rate` decimal(10,3) NOT NULL DEFAULT '0.000',
  `deposit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `id_customer` int unsigned NOT NULL,
  `id_invoice` int unsigned DEFAULT NULL,
  `id_cart_product` int unsigned NOT NULL,
  `order_state` tinyint unsigned NOT NULL,
  `pickup_day` date NOT NULL,
  `shopping_price` varchar(2) DEFAULT 'SP',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id_order_detail`),
  KEY `product_id` (`product_id`),
  KEY `product_attribute_id` (`product_attribute_id`),
  KEY `id_customer` (`id_customer`),
  KEY `pickup_day` (`pickup_day`),
  KEY `created` (`created`),
  KEY `order_state` (`order_state`),
  KEY `product_name` (`product_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_order_detail_feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_order_detail_feedbacks` (
  `id_order_detail` int unsigned NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `customer_id` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_order_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_order_detail_purchase_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_order_detail_purchase_prices` (
  `id_order_detail` int unsigned NOT NULL,
  `tax_rate` decimal(10,3) NOT NULL DEFAULT '0.000',
  `total_price_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_price_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `tax_unit_amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
  `tax_total_amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
  PRIMARY KEY (`id_order_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_order_detail_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_order_detail_units` (
  `id_order_detail` int NOT NULL DEFAULT '0',
  `product_quantity_in_units` decimal(10,3) unsigned DEFAULT NULL,
  `price_incl_per_unit` decimal(10,2) unsigned DEFAULT NULL,
  `purchase_price_incl_per_unit` decimal(10,2) unsigned DEFAULT NULL,
  `quantity_in_units` decimal(10,3) unsigned DEFAULT NULL,
  `unit_name` varchar(50) NOT NULL DEFAULT '',
  `unit_amount` int unsigned DEFAULT NULL,
  `mark_as_saved` tinyint unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `id_order_detail` (`id_order_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_pages` (
  `id_page` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `content` longtext NOT NULL,
  `position` int unsigned NOT NULL DEFAULT '0',
  `menu_type` varchar(255) NOT NULL DEFAULT 'header',
  `active` tinyint NOT NULL DEFAULT '0',
  `extern_url` varchar(255) NOT NULL DEFAULT '',
  `id_customer` int unsigned NOT NULL DEFAULT '0',
  `is_private` int unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `full_width` tinyint unsigned NOT NULL DEFAULT '0',
  `id_parent` int unsigned DEFAULT '0',
  `lft` int NOT NULL DEFAULT '0',
  `rght` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_page`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_customer` int unsigned NOT NULL DEFAULT '0',
  `id_manufacturer` int unsigned NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL DEFAULT 'product',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `text` varchar(255) NOT NULL DEFAULT '',
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_changed` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_transaction_add` datetime DEFAULT NULL,
  `transaction_text` mediumtext,
  `invoice_id` int unsigned DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `approval` tinyint NOT NULL DEFAULT '0',
  `approval_comment` mediumtext,
  `changed_by` int unsigned NOT NULL DEFAULT '0',
  `created_by` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_pickup_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_pickup_days` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned NOT NULL,
  `pickup_day` date NOT NULL,
  `comment` mediumtext,
  `products_picked_up` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `pickup_day` (`pickup_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_product` (
  `id_product` int unsigned NOT NULL AUTO_INCREMENT,
  `id_manufacturer` int unsigned DEFAULT NULL,
  `id_tax` int unsigned NOT NULL DEFAULT '0',
  `id_storage_location` tinyint unsigned NOT NULL DEFAULT '0',
  `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `name` mediumtext NOT NULL,
  `description` longtext,
  `description_short` mediumtext,
  `unity` varchar(255) DEFAULT NULL,
  `is_declaration_ok` tinyint unsigned NOT NULL DEFAULT '0',
  `is_stock_product` tinyint unsigned NOT NULL DEFAULT '0',
  `active` int NOT NULL DEFAULT '0',
  `delivery_rhythm_type` varchar(10) NOT NULL DEFAULT 'week',
  `delivery_rhythm_count` tinyint NOT NULL DEFAULT '1',
  `delivery_rhythm_first_delivery_day` date DEFAULT NULL,
  `delivery_rhythm_order_possible_until` date DEFAULT NULL,
  `delivery_rhythm_send_order_list_weekday` int unsigned DEFAULT NULL,
  `delivery_rhythm_send_order_list_day` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id_product`),
  KEY `product_manufacturer` (`id_manufacturer`,`id_product`),
  KEY `id_manufacturer` (`id_manufacturer`),
  KEY `is_stock_product` (`is_stock_product`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_product_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_product_attribute` (
  `id_product_attribute` int unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int unsigned NOT NULL DEFAULT '0',
  `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `default_on` tinyint unsigned DEFAULT NULL,
  PRIMARY KEY (`id_product_attribute`),
  KEY `product_attribute_product` (`id_product`),
  KEY `id_product_id_product_attribute` (`id_product_attribute`,`id_product`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_product_attribute_combination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_product_attribute_combination` (
  `id_attribute` int unsigned NOT NULL DEFAULT '0',
  `id_product_attribute` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_attribute`,`id_product_attribute`),
  KEY `id_product_attribute` (`id_product_attribute`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_purchase_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_purchase_prices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL DEFAULT '0',
  `product_attribute_id` int unsigned NOT NULL DEFAULT '0',
  `tax_id` int unsigned DEFAULT '0',
  `price` decimal(20,6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`,`product_attribute_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_sliders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_sliders` (
  `id_slider` int unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(999) DEFAULT NULL,
  `is_private` int unsigned NOT NULL DEFAULT '0',
  `position` int unsigned NOT NULL DEFAULT '0',
  `active` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_slider`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_stock_available`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_stock_available` (
  `id_stock_available` int unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int unsigned NOT NULL DEFAULT '0',
  `id_product_attribute` int unsigned NOT NULL DEFAULT '0',
  `quantity` int NOT NULL DEFAULT '0',
  `quantity_limit` int NOT NULL DEFAULT '0',
  `sold_out_limit` int DEFAULT NULL,
  `always_available` tinyint unsigned NOT NULL DEFAULT '1',
  `default_quantity_after_sending_order_lists` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id_stock_available`),
  UNIQUE KEY `product_sqlstock` (`id_product`,`id_product_attribute`),
  KEY `id_product` (`id_product`),
  KEY `id_product_attribute` (`id_product_attribute`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_storage_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_storage_locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rank` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_sync_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_sync_domains` (
  `id` int NOT NULL AUTO_INCREMENT,
  `domain` varchar(128) NOT NULL DEFAULT '',
  `active` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_sync_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_sync_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sync_domain_id` int unsigned NOT NULL DEFAULT '0',
  `local_product_id` int unsigned NOT NULL DEFAULT '0',
  `remote_product_id` int unsigned NOT NULL DEFAULT '0',
  `local_product_attribute_id` int unsigned NOT NULL DEFAULT '0',
  `remote_product_attribute_id` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_tax`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_tax` (
  `id_tax` int unsigned NOT NULL AUTO_INCREMENT,
  `rate` decimal(10,3) NOT NULL DEFAULT '0.000',
  `active` tinyint unsigned NOT NULL DEFAULT '1',
  `deleted` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_tax`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_timebased_currency_order_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_timebased_currency_order_detail` (
  `id_order_detail` int NOT NULL DEFAULT '0',
  `money_excl` decimal(10,6) unsigned DEFAULT NULL,
  `money_incl` decimal(10,6) unsigned DEFAULT NULL,
  `seconds` int unsigned DEFAULT NULL,
  `max_percentage` int unsigned DEFAULT NULL,
  `exchange_rate` decimal(6,2) unsigned DEFAULT NULL,
  UNIQUE KEY `id_order_detail` (`id_order_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_timebased_currency_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_timebased_currency_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_customer` int unsigned DEFAULT NULL,
  `id_manufacturer` int unsigned DEFAULT NULL,
  `seconds` int NOT NULL DEFAULT '0',
  `text` varchar(255) NOT NULL DEFAULT '',
  `working_day` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `approval` tinyint NOT NULL DEFAULT '0',
  `approval_comment` mediumtext,
  `modified_by` int unsigned DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcs_units` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_product` int unsigned DEFAULT NULL,
  `id_product_attribute` int unsigned DEFAULT NULL,
  `price_incl_per_unit` decimal(10,2) unsigned DEFAULT NULL,
  `purchase_price_incl_per_unit` decimal(10,2) unsigned DEFAULT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `amount` int unsigned DEFAULT NULL,
  `price_per_unit_enabled` tinyint NOT NULL DEFAULT '0',
  `quantity_in_units` decimal(10,3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_product` (`id_product`,`id_product_attribute`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `phinxlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phinxlog` (
  `version` bigint NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `queue_phinxlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue_phinxlog` (
  `version` bigint NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `queue_processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue_processes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pid` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `terminate` tinyint(1) NOT NULL DEFAULT '0',
  `server` varchar(90) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `workerkey` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workerkey` (`workerkey`),
  UNIQUE KEY `pid` (`pid`,`server`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `queued_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `queued_jobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `job_task` varchar(90) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `job_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL,
  `notbefore` datetime DEFAULT NULL,
  `fetched` datetime DEFAULT NULL,
  `completed` datetime DEFAULT NULL,
  `progress` float DEFAULT NULL,
  `failed` int NOT NULL DEFAULT '0',
  `failure_message` text,
  `workerkey` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` int NOT NULL DEFAULT '5',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

