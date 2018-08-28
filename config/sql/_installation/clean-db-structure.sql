
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `fcs_action_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_action_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL DEFAULT '',
  `customer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `object_type` varchar(255) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_address` (
  `id_address` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL DEFAULT '0',
  `id_manufacturer` int(10) unsigned NOT NULL DEFAULT '0',
  `lastname` varchar(32) NOT NULL DEFAULT '',
  `firstname` varchar(32) NOT NULL DEFAULT '',
  `address1` varchar(128) NOT NULL DEFAULT '',
  `address2` varchar(128) DEFAULT NULL,
  `postcode` varchar(12) DEFAULT NULL,
  `city` varchar(64) NOT NULL DEFAULT '',
  `comment` text,
  `phone` varchar(32) DEFAULT NULL,
  `phone_mobile` varchar(32) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_upd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_address`),
  KEY `address_customer` (`id_customer`),
  KEY `id_manufacturer` (`id_manufacturer`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_attribute` (
  `id_attribute` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `can_be_used_as_unit` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id_attribute`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_blog_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_blog_posts` (
  `id_blog_post` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `short_description` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `id_customer` int(11) unsigned NOT NULL DEFAULT '0',
  `id_manufacturer` int(11) unsigned NOT NULL DEFAULT '0',
  `is_private` int(11) unsigned NOT NULL DEFAULT '0',
  `active` int(11) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  `is_featured` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_blog_post`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_cart_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_cart_products` (
  `id_cart_product` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cart` int(10) unsigned NOT NULL DEFAULT '0',
  `id_product` int(10) unsigned NOT NULL DEFAULT '0',
  `id_product_attribute` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id_cart_product`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_carts` (
  `id_cart` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id_cart`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_category` (
  `id_category` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_parent` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `nleft` int(10) NOT NULL DEFAULT '0',
  `nright` int(10) NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id_category`),
  KEY `category_parent` (`id_parent`),
  KEY `nleftrightactive` (`nleft`,`nright`,`active`),
  KEY `nright` (`nright`),
  KEY `activenleft` (`active`,`nleft`),
  KEY `activenright` (`active`,`nright`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_category_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_category_product` (
  `id_category` int(10) unsigned NOT NULL DEFAULT '0',
  `id_product` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_category`,`id_product`),
  KEY `id_product` (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_configuration` (
  `id_configuration` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(254) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `value` text,
  `type` varchar(20) NOT NULL DEFAULT '',
  `position` int(8) unsigned NOT NULL DEFAULT '0',
  `locale` varchar(5) DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_upd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_configuration`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_customer` (
  `id_customer` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_default_group` int(10) unsigned NOT NULL DEFAULT '1',
  `firstname` varchar(32) NOT NULL DEFAULT '',
  `lastname` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `passwd` varchar(32) NOT NULL DEFAULT '',
  `change_password_code` varchar(12) DEFAULT NULL,
  `email_order_reminder` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `terms_of_use_accepted_date` date NOT NULL DEFAULT '1000-01-01',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_upd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `timebased_currency_enabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_customer`),
  KEY `customer_email` (`email`),
  KEY `customer_login` (`email`,`passwd`),
  KEY `id_customer_passwd` (`id_customer`,`passwd`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_deposits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_deposits` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL DEFAULT '0',
  `id_product_attribute` int(10) unsigned NOT NULL DEFAULT '0',
  `deposit` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_address` text,
  `to_address` text,
  `cc_address` text,
  `bcc_address` text,
  `subject` text,
  `headers` text,
  `message` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_images` (
  `id_image` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_image`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_manufacturer` int(10) unsigned NOT NULL DEFAULT '0',
  `invoice_number` int(10) unsigned NOT NULL DEFAULT '0',
  `send_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_manufacturer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_manufacturer` (
  `id_manufacturer` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `description` text,
  `short_description` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `holiday_from` date DEFAULT NULL,
  `holiday_to` date DEFAULT NULL,
  `is_private` int(11) unsigned NOT NULL DEFAULT '0',
  `uid_number` varchar(30) NOT NULL DEFAULT '',
  `additional_text_for_invoice` text NOT NULL,
  `iban` varchar(20) NOT NULL DEFAULT '',
  `bic` varchar(11) NOT NULL DEFAULT '',
  `bank_name` varchar(255) NOT NULL DEFAULT '',
  `firmenbuchnummer` varchar(20) NOT NULL DEFAULT '',
  `firmengericht` varchar(150) NOT NULL DEFAULT '',
  `aufsichtsbehoerde` varchar(150) NOT NULL DEFAULT '',
  `kammer` varchar(150) NOT NULL DEFAULT '',
  `homepage` varchar(255) NOT NULL DEFAULT '',
  `id_customer` int(10) unsigned DEFAULT NULL,
  `variable_member_fee` int(8) unsigned DEFAULT NULL,
  `send_invoice` tinyint(4) unsigned DEFAULT NULL,
  `send_order_list` tinyint(4) unsigned DEFAULT NULL,
  `default_tax_id` int(8) unsigned DEFAULT NULL,
  `send_order_list_cc` varchar(512) DEFAULT NULL,
  `bulk_orders_allowed` tinyint(4) unsigned DEFAULT NULL,
  `send_instant_order_notification` tinyint(4) unsigned DEFAULT NULL,
  `send_ordered_product_deleted_notification` int(10) unsigned DEFAULT NULL,
  `send_ordered_product_price_changed_notification` int(10) unsigned DEFAULT NULL,
  `send_ordered_product_amount_changed_notification` int(10) unsigned DEFAULT NULL,
  `enabled_sync_domains` varchar(50) DEFAULT NULL,
  `timebased_currency_enabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `timebased_currency_max_percentage` tinyint(3) unsigned NOT NULL DEFAULT '30',
  `timebased_currency_max_credit_balance` int(7) unsigned DEFAULT '360000',
  `stock_management_enabled` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `send_product_sold_out_limit_reached_for_manufacturer` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `send_product_sold_out_limit_reached_for_contact_person` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_manufacturer`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_order_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_order_detail` (
  `id_order_detail` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_attribute_id` int(10) unsigned DEFAULT NULL,
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `product_amount` int(10) unsigned NOT NULL DEFAULT '0',
  `total_price_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_price_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `id_tax` int(11) unsigned DEFAULT '0',
  `deposit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `id_customer` int(10) unsigned NOT NULL,
  `id_cart_product` int(10) unsigned NOT NULL,
  `order_state` tinyint(4) unsigned NOT NULL,
  `pickup_day` date NOT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_order_detail_tax`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_order_detail_tax` (
  `id_order_detail` int(11) NOT NULL DEFAULT '0',
  `unit_amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
  `total_amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
  KEY `id_order_detail` (`id_order_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_order_detail_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_order_detail_units` (
  `id_order_detail` int(11) NOT NULL DEFAULT '0',
  `product_quantity_in_units` decimal(10,3) unsigned DEFAULT NULL,
  `price_incl_per_unit` decimal(10,2) unsigned DEFAULT NULL,
  `quantity_in_units` decimal(10,3) unsigned DEFAULT NULL,
  `unit_name` varchar(50) NOT NULL DEFAULT '',
  `unit_amount` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `id_order_detail` (`id_order_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_pages` (
  `id_page` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `content` text NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `menu_type` varchar(255) NOT NULL DEFAULT 'header',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `extern_url` varchar(255) NOT NULL DEFAULT '',
  `id_customer` int(10) unsigned NOT NULL DEFAULT '0',
  `is_private` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `full_width` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id_parent` int(10) unsigned DEFAULT NULL,
  `lft` int(10) NOT NULL DEFAULT '0',
  `rght` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_page`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_payments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL DEFAULT '0',
  `id_manufacturer` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL DEFAULT 'product',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `text` varchar(255) NOT NULL DEFAULT '',
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_changed` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `approval` tinyint(4) NOT NULL DEFAULT '0',
  `approval_comment` text NOT NULL,
  `changed_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_pickup_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_pickup_days` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `pickup_day` date NOT NULL,
  `comment` text,
  `products_picked_up` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `pickup_day` (`pickup_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_product` (
  `id_product` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_manufacturer` int(10) unsigned DEFAULT NULL,
  `id_tax` int(11) unsigned NOT NULL DEFAULT '0',
  `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `name` text NOT NULL,
  `description` text,
  `description_short` text,
  `unity` varchar(255) DEFAULT NULL,
  `is_declaration_ok` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `is_stock_product` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `delivery_rhythm_type` varchar(10) NOT NULL DEFAULT 'week',
  `delivery_rhythm_amount` tinyint(10) NOT NULL DEFAULT '1',
  `delivery_rhythm_delivery_day` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id_product`),
  KEY `product_manufacturer` (`id_manufacturer`,`id_product`),
  KEY `id_manufacturer` (`id_manufacturer`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_product_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_product_attribute` (
  `id_product_attribute` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL DEFAULT '0',
  `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `quantity` int(10) NOT NULL DEFAULT '0',
  `default_on` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_product_attribute`),
  KEY `product_attribute_product` (`id_product`),
  KEY `id_product_id_product_attribute` (`id_product_attribute`,`id_product`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_product_attribute_combination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_product_attribute_combination` (
  `id_attribute` int(10) unsigned NOT NULL DEFAULT '0',
  `id_product_attribute` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_attribute`,`id_product_attribute`),
  KEY `id_product_attribute` (`id_product_attribute`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_sliders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_sliders` (
  `id_slider` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_slider`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_stock_available`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_stock_available` (
  `id_stock_available` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(11) unsigned NOT NULL DEFAULT '0',
  `id_product_attribute` int(11) unsigned NOT NULL DEFAULT '0',
  `quantity` int(10) NOT NULL DEFAULT '0',
  `quantity_limit` int(10) NOT NULL DEFAULT '0',
  `sold_out_limit` int(10) DEFAULT NULL,
  PRIMARY KEY (`id_stock_available`),
  UNIQUE KEY `product_sqlstock` (`id_product`,`id_product_attribute`),
  KEY `id_product` (`id_product`),
  KEY `id_product_attribute` (`id_product_attribute`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_sync_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_sync_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(128) NOT NULL DEFAULT '',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_sync_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_sync_products` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sync_domain_id` int(10) unsigned NOT NULL DEFAULT '0',
  `local_product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `remote_product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `local_product_attribute_id` int(10) unsigned NOT NULL DEFAULT '0',
  `remote_product_attribute_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_tax`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_tax` (
  `id_tax` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rate` decimal(10,3) NOT NULL DEFAULT '0.000',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_tax`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_timebased_currency_order_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_timebased_currency_order_detail` (
  `id_order_detail` int(11) NOT NULL DEFAULT '0',
  `money_excl` decimal(10,6) unsigned DEFAULT NULL,
  `money_incl` decimal(10,6) unsigned DEFAULT NULL,
  `seconds` int(7) unsigned DEFAULT NULL,
  `max_percentage` int(11) unsigned DEFAULT NULL,
  `exchange_rate` decimal(6,2) unsigned DEFAULT NULL,
  UNIQUE KEY `id_order_detail` (`id_order_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_timebased_currency_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_timebased_currency_payments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned DEFAULT NULL,
  `id_manufacturer` int(11) unsigned DEFAULT NULL,
  `seconds` int(7) NOT NULL DEFAULT '0',
  `text` varchar(255) NOT NULL DEFAULT '',
  `working_day` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `approval` tinyint(4) NOT NULL DEFAULT '0',
  `approval_comment` text NOT NULL,
  `modified_by` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcs_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcs_units` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned DEFAULT NULL,
  `id_product_attribute` int(11) unsigned DEFAULT NULL,
  `price_incl_per_unit` decimal(10,2) unsigned DEFAULT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `amount` int(10) unsigned DEFAULT NULL,
  `price_per_unit_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `quantity_in_units` decimal(10,3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_product` (`id_product`,`id_product_attribute`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `phinxlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

