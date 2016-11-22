-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 21. Nov 2016 um 20:24
-- Server-Version: 10.1.13-MariaDB
-- PHP-Version: 7.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Datenbank: `foodcoopshop_test`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_address`
--

DROP TABLE IF EXISTS `fcs_address`;
CREATE TABLE `fcs_address` (
  `id_address` int(10) UNSIGNED NOT NULL,
  `id_country` int(10) UNSIGNED NOT NULL,
  `id_state` int(10) UNSIGNED DEFAULT NULL,
  `id_customer` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_manufacturer` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_supplier` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_warehouse` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `alias` varchar(32) NOT NULL,
  `company` varchar(64) DEFAULT NULL,
  `lastname` varchar(32) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `address1` varchar(128) NOT NULL,
  `address2` varchar(128) DEFAULT NULL,
  `postcode` varchar(12) DEFAULT NULL,
  `city` varchar(64) NOT NULL,
  `other` text,
  `phone` varchar(32) DEFAULT NULL,
  `phone_mobile` varchar(32) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `vat_number` varchar(32) DEFAULT NULL,
  `dni` varchar(16) DEFAULT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_address`
--

INSERT INTO `fcs_address` (`id_address`, `id_country`, `id_state`, `id_customer`, `id_manufacturer`, `id_supplier`, `id_warehouse`, `alias`, `company`, `lastname`, `firstname`, `address1`, `address2`, `postcode`, `city`, `other`, `phone`, `phone_mobile`, `email`, `vat_number`, `dni`, `date_add`, `date_upd`, `active`, `deleted`) VALUES
(153, 2, 0, 87, 0, 0, 0, 'Ihre Adresse', '', 'Mitglied', 'Demo', 'Demostraße 4', '', '4644', 'Scharnstein', '', '', '0664/000000000', 'foodcoopshop-demo-mitglied@mailinator.com', '', '', '2014-12-02 12:19:31', '2014-12-02 12:19:31', 1, 0),
(154, 2, 0, 88, 0, 0, 0, 'Ihre Adresse', '', 'Admin', 'Demo', 'Demostraße 4', '', '4644', 'Scharnstein', 'test', '', '0600/000000', 'fcs-demo-admin@mailinator.com', '', '', '2014-12-02 12:28:44', '2014-12-02 12:28:44', 1, 0),
(173, 2, 0, 0, 4, 0, 0, 'manufacturer', '', 'Fleisch-Hersteller', 'Demo', 'Demostraße 4', '', '4644', 'Scharnstein', '{"compensationPercentage":0,"sendInvoice":"1","sendOrderList":"1","defaultTaxId":2,"sendOrderListCc":"testfcs1@mailinator.com;testfcs2@mailinator.com","bulkOrdersAllowed":"0"}', '', '', 'fcs-demo-fleisch-hersteller@mailinator.com', '', '', '2014-05-27 22:20:18', '2015-04-07 16:18:28', 1, 0),
(177, 2, 0, 0, 15, 0, 0, 'manufacturer', '', 'Milch-Hersteller', 'Demo', 'Demostraße 4', '', '4644', 'Scharnstein', '{"compensationPercentage":0,"sendInvoice":"1","sendOrderList":"1","defaultTaxId":4,"sendOrderListCc":"test@test.at","bulkOrdersAllowed":"0"}', '', '', 'fcs-demo-milch-hersteller@mailinator.com', '', '', '2014-06-04 21:46:38', '2015-10-16 10:06:52', 1, 0),
(180, 2, 0, 0, 5, 0, 0, 'manufacturer', '', 'Gemüse-Hersteller', 'Demo', 'Demostraße 4', '', '4644', 'Scharnstein', '{"compensationPercentage":10,"sendInvoice":"1","sendOrderList":"1","defaultTaxId":1,"sendOrderListCc":"","bulkOrdersAllowed":"0"}', '', '', 'fcs-demo-gemuese-hersteller@mailinator.com', '', '', '2014-05-14 21:20:05', '2015-12-30 00:54:35', 1, 0),
(181, 2, 0, 0, 16, 0, 0, 'manufacturer', '', 'Hersteller ohne Customer-Eintrag', 'Demo', 'Demostraße 4', '', '4644', 'Scharnstein', '{"compensationPercentage":10,"sendInvoice":"1","sendOrderList":"1","defaultTaxId":1,"sendOrderListCc":"","bulkOrdersAllowed":"0"}', '', '', 'fcs-hersteller-ohne-customer-eintrag@mailinator.com', '', '', '2014-05-14 21:20:05', '2015-12-30 00:54:35', 1, 0),
(182, 2, NULL, 92, 0, 0, 0, '', NULL, 'Superadmin', 'Demo', 'Demostraße 4', '', '4644', 'Demostadt', NULL, '', '0600/000000', 'fcs-demo-superadmin@mailinator.com', NULL, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_attribute`
--

DROP TABLE IF EXISTS `fcs_attribute`;
CREATE TABLE `fcs_attribute` (
  `id_attribute` int(10) UNSIGNED NOT NULL,
  `id_attribute_group` int(10) UNSIGNED NOT NULL,
  `color` varchar(32) DEFAULT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_attribute`
--

INSERT INTO `fcs_attribute` (`id_attribute`, `id_attribute_group`, `color`, `position`, `active`, `created`, `modified`) VALUES
(27, 5, '', 2, 1, NULL, NULL),
(28, 5, '', 3, 1, NULL, NULL),
(29, 5, '', 4, 1, NULL, NULL),
(30, 5, '', 5, 1, NULL, NULL),
(31, 5, '', 6, 1, NULL, NULL),
(32, 5, '', 7, 1, NULL, NULL),
(33, 5, '', 8, 1, NULL, NULL),
(34, 5, '', 9, 1, NULL, NULL),
(35, 5, '', 1, 1, NULL, NULL),
(36, 5, '', 0, 1, NULL, NULL),
(38, 6, '', 0, 1, NULL, NULL),
(39, 6, '', 1, 1, NULL, NULL),
(42, 6, '', 2, 1, NULL, NULL),
(43, 6, '', 3, 1, NULL, NULL),
(44, 6, '', 4, 1, NULL, NULL),
(45, 6, '', 5, 1, NULL, NULL),
(46, 6, '', 0, 1, NULL, NULL),
(47, 6, '', 7, 1, NULL, NULL),
(48, 6, '', 8, 1, NULL, NULL),
(49, 6, '', 9, 1, NULL, NULL),
(50, 6, '', 10, 1, NULL, NULL),
(51, 6, '', 11, 1, NULL, NULL),
(52, 6, '', 12, 1, NULL, NULL),
(53, 6, '', 13, 1, NULL, NULL),
(54, 6, '', 14, 1, NULL, NULL),
(55, 5, '', 11, 1, NULL, NULL),
(56, 5, '', 12, 1, NULL, NULL),
(57, 6, '', 12, 1, NULL, NULL),
(58, 6, '', 2, 1, NULL, NULL),
(59, 5, '', 10, 1, NULL, NULL),
(60, 7, '', 0, 1, NULL, NULL),
(61, 7, '', 1, 1, NULL, NULL),
(62, 7, '', 2, 1, NULL, NULL),
(63, 7, '', 3, 1, NULL, NULL),
(64, 7, '', 4, 1, NULL, NULL),
(65, 7, '', 5, 1, NULL, NULL),
(66, 5, '', 13, 1, NULL, NULL),
(67, 5, '', 14, 1, NULL, NULL),
(68, 5, '', 15, 1, NULL, NULL),
(69, 8, '', 0, 1, NULL, NULL),
(70, 8, '', 1, 1, NULL, NULL),
(71, 8, '', 2, 1, NULL, NULL),
(72, 8, '', 3, 1, NULL, NULL),
(73, 5, '', 16, 1, NULL, NULL),
(74, 5, '', 17, 1, NULL, NULL),
(75, 5, '', 18, 1, NULL, NULL),
(76, 5, '', 19, 1, NULL, NULL),
(77, 5, '', 21, 1, NULL, NULL),
(78, 5, '', 20, 1, NULL, NULL),
(79, 8, '', 4, 1, NULL, NULL),
(80, 5, '', 22, 1, NULL, NULL),
(81, 5, '', 23, 1, NULL, NULL),
(82, 5, '', 24, 1, NULL, NULL),
(83, 5, '', 25, 1, NULL, NULL),
(84, 5, '', 26, 1, NULL, NULL),
(85, 5, '', 27, 1, NULL, NULL),
(86, 5, '', 28, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_attribute_lang`
--

DROP TABLE IF EXISTS `fcs_attribute_lang`;
CREATE TABLE `fcs_attribute_lang` (
  `id_attribute` int(10) UNSIGNED NOT NULL,
  `id_lang` int(10) UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_attribute_lang`
--

INSERT INTO `fcs_attribute_lang` (`id_attribute`, `id_lang`, `name`) VALUES
(31, 1, '0,10l'),
(75, 1, '0,25kg'),
(32, 1, '0,25l'),
(36, 1, '0,5 kg'),
(76, 1, '0,5kg'),
(33, 1, '0,5l'),
(78, 1, '0,75 kg'),
(34, 1, '0,75l'),
(35, 1, '1 kg'),
(29, 1, '1 L'),
(82, 1, '1 Stück'),
(59, 1, '1,0l'),
(77, 1, '1,5 kg'),
(55, 1, '10 kg'),
(74, 1, '100 g'),
(69, 1, '1L Flasche'),
(83, 1, '2 Stück'),
(73, 1, '200 g '),
(56, 1, '25 kg'),
(80, 1, '25 min'),
(70, 1, '2L Flasche '),
(27, 1, '3 kg'),
(84, 1, '3 Stück'),
(79, 1, '3L Bag-in-Box'),
(85, 1, '4 Stück'),
(28, 1, '5 kg'),
(30, 1, '5 L'),
(86, 1, '5 Stück'),
(81, 1, '50 min'),
(71, 1, '5L Bag-in-Box'),
(72, 1, '6x1L Flaschen in Kiste'),
(44, 1, 'Beutel 100g'),
(38, 1, 'Beutel 15g'),
(48, 1, 'Beutel 1kg'),
(45, 1, 'Beutel 250g'),
(46, 1, 'Beutel 25g'),
(39, 1, 'Beutel 30g'),
(58, 1, 'Beutel 40g'),
(42, 1, 'Beutel 45g'),
(47, 1, 'Beutel 500g'),
(43, 1, 'Beutel 50g'),
(49, 1, 'Beutel 5kg'),
(54, 1, 'Glas 100g'),
(50, 1, 'Glas 15g'),
(51, 1, 'Glas 25g'),
(52, 1, 'Glas 30g'),
(57, 1, 'Glas 45g'),
(53, 1, 'Glas 50g'),
(68, 1, 'groß'),
(66, 1, 'klein'),
(64, 1, 'L'),
(63, 1, 'M'),
(67, 1, 'mittel'),
(62, 1, 'S (groß)'),
(60, 1, 'S (klein)'),
(61, 1, 'S (mittel)'),
(65, 1, 'XL');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_cake_action_logs`
--

DROP TABLE IF EXISTS `fcs_cake_action_logs`;
CREATE TABLE `fcs_cake_action_logs` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `object_id` int(10) UNSIGNED NOT NULL,
  `object_type` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_cake_carts`
--

DROP TABLE IF EXISTS `fcs_cake_carts`;
CREATE TABLE `fcs_cake_carts` (
  `id_cart` int(10) UNSIGNED NOT NULL,
  `id_customer` int(10) UNSIGNED NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_cake_cart_products`
--

DROP TABLE IF EXISTS `fcs_cake_cart_products`;
CREATE TABLE `fcs_cake_cart_products` (
  `id_cart_product` int(10) UNSIGNED NOT NULL,
  `id_cart` int(10) UNSIGNED NOT NULL,
  `id_product` int(10) UNSIGNED NOT NULL,
  `id_product_attribute` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `amount` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_cake_cart_products`
--

INSERT INTO `fcs_cake_cart_products` (`id_cart_product`, `id_cart`, `id_product`, `id_product_attribute`, `amount`, `date_add`, `date_upd`) VALUES
(2, 1, 346, 0, 2, '2016-09-27 09:34:48', '2016-09-27 09:34:48'),
(3, 1, 60, 10, 3, '2016-09-27 09:34:48', '2016-09-27 09:34:48'),
(4, 1, 344, 0, 1, '2016-09-27 09:34:49', '2016-09-27 09:34:49'),
(6, 2, 60, 10, 1, '2016-09-27 09:34:57', '2016-09-27 09:34:57');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_cake_deposits`
--

DROP TABLE IF EXISTS `fcs_cake_deposits`;
CREATE TABLE `fcs_cake_deposits` (
  `id` int(10) NOT NULL,
  `id_product` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_product_attribute` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `deposit` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_cake_deposits`
--

INSERT INTO `fcs_cake_deposits` (`id`, `id_product`, `id_product_attribute`, `deposit`) VALUES
(1, 346, 0, 0.5),
(2, 0, 9, 0.5),
(3, 0, 10, 0.5),
(4, 0, 11, 0.5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_cake_invoices`
--

DROP TABLE IF EXISTS `fcs_cake_invoices`;
CREATE TABLE `fcs_cake_invoices` (
  `id` int(11) NOT NULL,
  `id_manufacturer` int(10) UNSIGNED NOT NULL,
  `invoice_number` int(10) UNSIGNED NOT NULL,
  `send_date` datetime NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_cake_payments`
--

DROP TABLE IF EXISTS `fcs_cake_payments`;
CREATE TABLE `fcs_cake_payments` (
  `id` int(10) NOT NULL,
  `id_customer` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_manufacturer` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL DEFAULT 'product',
  `amount` decimal(10,2) NOT NULL,
  `text` varchar(255) NOT NULL,
  `date_add` datetime NOT NULL,
  `date_changed` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_category`
--

DROP TABLE IF EXISTS `fcs_category`;
CREATE TABLE `fcs_category` (
  `id_category` int(10) UNSIGNED NOT NULL,
  `id_parent` int(10) UNSIGNED NOT NULL,
  `id_shop_default` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `level_depth` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `nleft` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `nright` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `is_root_category` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_category`
--

INSERT INTO `fcs_category` (`id_category`, `id_parent`, `id_shop_default`, `level_depth`, `nleft`, `nright`, `active`, `date_add`, `date_upd`, `position`, `is_root_category`) VALUES
(1, 0, 1, 0, 1, 32, 1, '2014-05-05 21:05:09', '2014-05-05 21:05:09', 0, 0),
(2, 1, 1, 1, 2, 31, 1, '2014-05-05 21:05:09', '2014-05-05 21:05:09', 0, 1),
(13, 2, 1, 2, 5, 6, 1, '2014-05-14 12:23:25', '2014-05-14 12:23:25', 0, 0),
(14, 2, 1, 2, 7, 8, 0, '2014-05-14 12:23:39', '2014-12-02 12:52:03', 3, 0),
(15, 2, 1, 2, 9, 10, 1, '2014-05-14 21:38:45', '2015-02-26 13:56:19', 4, 0),
(16, 2, 1, 2, 11, 12, 1, '2014-05-14 21:40:51', '2014-05-14 21:48:48', 4, 0),
(17, 2, 1, 2, 13, 14, 1, '2014-05-14 21:43:00', '2014-05-14 21:51:12', 5, 0),
(18, 2, 1, 2, 15, 16, 0, '2014-05-14 21:48:15', '2014-12-02 12:52:03', 7, 0),
(19, 2, 1, 2, 17, 18, 0, '2014-05-14 21:52:41', '2014-12-02 12:52:03', 8, 0),
(20, 2, 1, 2, 3, 4, 1, '2014-05-14 21:53:52', '2014-05-17 13:14:22', 1, 0),
(21, 2, 1, 2, 19, 20, 0, '2014-05-14 21:54:38', '2014-12-02 12:52:04', 9, 0),
(22, 2, 1, 2, 21, 22, 0, '2014-05-14 21:56:28', '2014-12-02 12:52:04', 10, 0),
(23, 2, 1, 2, 23, 24, 0, '2014-06-29 13:16:24', '2014-12-02 12:52:04', 11, 0),
(24, 2, 1, 2, 25, 26, 0, '2014-07-11 23:10:22', '2014-12-02 12:52:04', 12, 0),
(25, 2, 1, 2, 27, 28, 1, '2014-09-11 18:22:25', '2015-08-08 10:34:22', 13, 0),
(26, 2, 1, 2, 29, 30, 0, '2014-11-08 12:05:53', '2014-12-02 12:52:05', 14, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_category_lang`
--

DROP TABLE IF EXISTS `fcs_category_lang`;
CREATE TABLE `fcs_category_lang` (
  `id_category` int(10) UNSIGNED NOT NULL,
  `id_shop` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `id_lang` int(10) UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL,
  `description` text,
  `link_rewrite` varchar(128) NOT NULL,
  `meta_title` varchar(128) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_category_lang`
--

INSERT INTO `fcs_category_lang` (`id_category`, `id_shop`, `id_lang`, `name`, `description`, `link_rewrite`, `meta_title`, `meta_keywords`, `meta_description`) VALUES
(1, 1, 1, 'Root', '', 'root', '', '', ''),
(2, 1, 1, 'Produkte', '', 'home', '', '', ''),
(13, 1, 1, 'Obst und Gemüse', '', 'obst-und-gemuse', '', '', ''),
(14, 1, 1, 'Sonstiges', '', 'sonstiges', '', '', ''),
(15, 1, 1, 'Getreideprodukte und Hülsenfrüchte', '', 'getreideprodukte-und-hulsenfruchte', '', '', ''),
(16, 1, 1, 'Fleischprodukte', '', 'fleischprodukte', '', '', ''),
(17, 1, 1, 'Milchprodukte', '', 'milchprodukte', '', '', ''),
(18, 1, 1, 'Getränke', '', 'getranke', '', '', ''),
(19, 1, 1, 'Öle', '', 'ole', '', '', ''),
(20, 1, 1, 'Alle Produkte', '', 'alle-produkte', '', '', ''),
(21, 1, 1, 'Brot und Gebäck', '', 'brot-und-geback', '', '', ''),
(22, 1, 1, 'Gewürze und Saaten', '', 'gewurze-und-saaten', '', '', ''),
(23, 1, 1, 'Honigprodukte', '', 'honigprodukte', '', '', ''),
(24, 1, 1, 'Eier', '', 'eier', '', '', ''),
(25, 1, 1, 'Schnäpse', '', 'schnapse', '', '', ''),
(26, 1, 1, 'Hygieneartikel', '', 'hygieneartikel', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_category_product`
--

DROP TABLE IF EXISTS `fcs_category_product`;
CREATE TABLE `fcs_category_product` (
  `id_category` int(10) UNSIGNED NOT NULL,
  `id_product` int(10) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_category_product`
--

INSERT INTO `fcs_category_product` (`id_category`, `id_product`, `position`) VALUES
(2, 75, 0),
(2, 76, 1),
(2, 77, 2),
(2, 78, 3),
(2, 234, 4),
(2, 241, 5),
(2, 242, 6),
(2, 248, 7),
(2, 249, 8),
(2, 253, 9),
(2, 256, 10),
(2, 259, 11),
(2, 260, 12),
(2, 261, 13),
(2, 262, 14),
(2, 263, 15),
(2, 264, 16),
(2, 265, 17),
(2, 266, 18),
(2, 267, 19),
(2, 268, 20),
(2, 269, 21),
(2, 270, 22),
(2, 273, 23),
(2, 275, 24),
(2, 277, 25),
(2, 278, 26),
(2, 279, 27),
(2, 280, 28),
(2, 282, 29),
(2, 283, 30),
(2, 284, 31),
(2, 285, 32),
(2, 286, 33),
(2, 307, 34),
(2, 308, 35),
(2, 309, 36),
(2, 310, 37),
(2, 311, 38),
(2, 312, 39),
(2, 313, 40),
(2, 318, 41),
(2, 319, 42),
(2, 331, 43),
(2, 332, 44),
(2, 333, 45),
(2, 334, 46),
(2, 335, 47),
(2, 336, 48),
(2, 337, 49),
(2, 338, 50),
(2, 339, 59),
(2, 341, 51),
(2, 342, 52),
(2, 348, 53),
(13, 109, 0),
(13, 110, 1),
(13, 144, 2),
(13, 153, 3),
(13, 155, 4),
(13, 158, 5),
(13, 160, 6),
(13, 161, 7),
(13, 162, 8),
(13, 163, 9),
(13, 166, 10),
(13, 167, 11),
(13, 173, 12),
(13, 174, 13),
(13, 224, 16),
(13, 225, 15),
(13, 228, 14),
(13, 233, 17),
(13, 234, 18),
(13, 250, 19),
(13, 253, 20),
(13, 256, 21),
(13, 273, 22),
(13, 279, 23),
(13, 280, 24),
(13, 290, 25),
(13, 291, 26),
(13, 293, 27),
(13, 296, 28),
(13, 297, 29),
(13, 298, 30),
(13, 299, 31),
(13, 318, 32),
(13, 319, 33),
(13, 343, 0),
(13, 344, 0),
(13, 346, 0),
(14, 189, 0),
(14, 190, 1),
(14, 191, 2),
(14, 192, 3),
(14, 193, 4),
(14, 261, 5),
(14, 262, 6),
(14, 263, 7),
(14, 264, 8),
(14, 265, 9),
(14, 266, 10),
(14, 267, 11),
(14, 268, 12),
(14, 285, 13),
(14, 286, 14),
(14, 307, 15),
(14, 308, 16),
(14, 309, 17),
(14, 310, 18),
(14, 311, 20),
(14, 312, 19),
(15, 113, 0),
(15, 116, 1),
(15, 117, 2),
(15, 118, 3),
(15, 148, 4),
(15, 239, 5),
(15, 241, 6),
(15, 242, 7),
(15, 248, 8),
(15, 249, 9),
(15, 339, 10),
(16, 36, 0),
(16, 80, 1),
(16, 91, 2),
(16, 93, 3),
(16, 94, 4),
(16, 95, 5),
(16, 98, 6),
(16, 102, 0),
(16, 103, 0),
(16, 106, 9),
(16, 336, 10),
(16, 337, 11),
(16, 338, 12),
(16, 340, 0),
(16, 347, 0),
(16, 348, 13),
(16, 354, 0),
(16, 361, 0),
(17, 47, 0),
(17, 49, 1),
(17, 51, 2),
(17, 52, 3),
(17, 58, 4),
(17, 59, 5),
(17, 60, 6),
(17, 257, 7),
(18, 185, 0),
(18, 186, 1),
(18, 187, 2),
(18, 188, 3),
(18, 218, 4),
(18, 219, 5),
(18, 220, 6),
(18, 259, 7),
(18, 260, 8),
(18, 300, 9),
(18, 313, 10),
(19, 150, 0),
(20, 1, 0),
(20, 2, 12),
(20, 3, 13),
(20, 4, 14),
(20, 5, 15),
(20, 6, 16),
(20, 7, 17),
(20, 8, 18),
(20, 36, 19),
(20, 47, 20),
(20, 49, 21),
(20, 51, 22),
(20, 52, 23),
(20, 58, 24),
(20, 59, 25),
(20, 60, 26),
(20, 74, 27),
(20, 75, 49),
(20, 76, 50),
(20, 77, 51),
(20, 78, 28),
(20, 80, 29),
(20, 81, 30),
(20, 82, 31),
(20, 84, 32),
(20, 86, 33),
(20, 87, 34),
(20, 88, 35),
(20, 91, 36),
(20, 93, 37),
(20, 94, 38),
(20, 95, 39),
(20, 98, 40),
(20, 102, 1),
(20, 103, 2),
(20, 106, 41),
(20, 109, 42),
(20, 110, 43),
(20, 113, 44),
(20, 116, 45),
(20, 117, 46),
(20, 118, 47),
(20, 128, 48),
(20, 130, 52),
(20, 132, 53),
(20, 136, 54),
(20, 137, 55),
(20, 139, 56),
(20, 143, 57),
(20, 144, 58),
(20, 148, 59),
(20, 150, 60),
(20, 153, 61),
(20, 155, 62),
(20, 158, 63),
(20, 160, 64),
(20, 161, 65),
(20, 162, 66),
(20, 163, 67),
(20, 166, 68),
(20, 167, 69),
(20, 173, 70),
(20, 174, 71),
(20, 185, 72),
(20, 186, 73),
(20, 187, 74),
(20, 188, 75),
(20, 189, 76),
(20, 190, 77),
(20, 191, 78),
(20, 192, 79),
(20, 193, 80),
(20, 218, 81),
(20, 219, 82),
(20, 220, 83),
(20, 224, 86),
(20, 225, 85),
(20, 228, 84),
(20, 233, 87),
(20, 234, 88),
(20, 239, 89),
(20, 241, 90),
(20, 242, 91),
(20, 248, 92),
(20, 249, 93),
(20, 250, 94),
(20, 253, 95),
(20, 256, 96),
(20, 257, 97),
(20, 259, 98),
(20, 260, 99),
(20, 261, 100),
(20, 262, 101),
(20, 263, 102),
(20, 264, 103),
(20, 265, 104),
(20, 266, 105),
(20, 267, 106),
(20, 268, 107),
(20, 269, 108),
(20, 270, 109),
(20, 273, 110),
(20, 275, 111),
(20, 277, 112),
(20, 278, 113),
(20, 279, 114),
(20, 280, 115),
(20, 282, 116),
(20, 283, 117),
(20, 284, 118),
(20, 285, 119),
(20, 286, 120),
(20, 290, 121),
(20, 291, 122),
(20, 293, 123),
(20, 296, 124),
(20, 297, 125),
(20, 298, 126),
(20, 299, 127),
(20, 307, 128),
(20, 308, 129),
(20, 309, 130),
(20, 310, 131),
(20, 311, 133),
(20, 312, 132),
(20, 313, 134),
(20, 318, 135),
(20, 319, 136),
(20, 331, 137),
(20, 332, 138),
(20, 333, 139),
(20, 334, 140),
(20, 336, 141),
(20, 337, 142),
(20, 338, 143),
(20, 339, 147),
(20, 340, 3),
(20, 341, 144),
(20, 342, 145),
(20, 343, 0),
(20, 344, 5),
(20, 345, 6),
(20, 346, 7),
(20, 347, 8),
(20, 348, 146),
(20, 350, 9),
(20, 352, 10),
(20, 353, 11),
(20, 354, 0),
(20, 355, 0),
(20, 356, 0),
(20, 357, 0),
(20, 358, 0),
(20, 359, 0),
(20, 360, 0),
(20, 361, 0),
(20, 362, 0),
(21, 130, 0),
(21, 132, 1),
(21, 136, 2),
(21, 137, 3),
(21, 139, 4),
(21, 269, 5),
(21, 270, 6),
(21, 341, 7),
(21, 342, 8),
(22, 74, 0),
(22, 81, 1),
(22, 82, 2),
(22, 84, 3),
(22, 86, 4),
(22, 87, 5),
(22, 88, 6),
(22, 128, 7),
(23, 75, 0),
(23, 76, 1),
(23, 77, 3),
(23, 78, 2),
(23, 312, 4),
(24, 143, 0),
(25, 275, 0),
(25, 277, 1),
(25, 278, 2),
(26, 282, 3),
(26, 283, 4),
(26, 284, 5),
(26, 331, 0),
(26, 332, 1),
(26, 333, 2),
(26, 334, 6);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_cms`
--

DROP TABLE IF EXISTS `fcs_cms`;
CREATE TABLE `fcs_cms` (
  `id_cms` int(10) UNSIGNED NOT NULL,
  `id_cms_category` int(10) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `menu_type` varchar(255) NOT NULL DEFAULT 'header',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `url` varchar(255) NOT NULL,
  `indexation` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `id_customer` int(10) UNSIGNED NOT NULL,
  `modified` datetime NOT NULL,
  `created` datetime NOT NULL,
  `full_width` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `id_parent` int(10) UNSIGNED DEFAULT NULL,
  `lft` int(10) UNSIGNED NOT NULL,
  `rght` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_cms`
--

INSERT INTO `fcs_cms` (`id_cms`, `id_cms_category`, `position`, `menu_type`, `active`, `url`, `indexation`, `id_customer`, `modified`, `created`, `full_width`, `id_parent`, `lft`, `rght`) VALUES
(3, 1, 1, 'header', 1, '', 1, 0, '2016-08-29 13:36:43', '2016-08-29 13:36:43', 0, NULL, 0, 0),
(4, 1, 3, 'header', 1, '', 1, 0, '2016-08-29 13:36:43', '2016-08-29 13:36:43', 0, NULL, 0, 0),
(8, 1, 2, 'header', 1, '', 1, 0, '2016-08-29 13:36:43', '2016-08-29 13:36:43', 0, NULL, 0, 0),
(9, 1, 0, 'header', 1, '', 1, 0, '2016-08-29 13:36:43', '2016-08-29 13:36:43', 0, NULL, 0, 0),
(10, 1, 4, 'header', 1, '', 0, 0, '2016-08-29 13:36:43', '2016-08-29 13:36:43', 0, NULL, 0, 0),
(11, 1, 5, 'header', -1, '', 0, 3, '2016-09-12 14:59:53', '2016-08-29 13:36:43', 0, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_cms_lang`
--

DROP TABLE IF EXISTS `fcs_cms_lang`;
CREATE TABLE `fcs_cms_lang` (
  `id_cms` int(10) UNSIGNED NOT NULL,
  `id_lang` int(10) UNSIGNED NOT NULL,
  `meta_title` varchar(128) NOT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `content` longtext,
  `link_rewrite` varchar(128) NOT NULL,
  `id_shop` int(10) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_cms_lang`
--

INSERT INTO `fcs_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`, `id_shop`) VALUES
(3, 1, 'Statuten', 'Unsere Vereinsstatuten', '', '', 'statuten', 1),
(4, 1, 'Über uns', '', '', '', 'ueber-uns', 1),
(8, 1, 'Links', '', '', '<h4><strong>Links</strong></h4>\r\n<ul>\r\n<li><a href="http://www.foodcoopshop.com" target="_blank">foodcoopshop.com</a>&nbsp;- Die Software f&uuml;r eure Foodcoop</li>\r\n<li><a href="http://www.fairteiler-scharnstein.at" target="_blank">Fairteiler Scharnstein</a></li>\r\n</ul>', 'links', 1),
(9, 1, 'Impressum', '', '', '<h4>Impressum</h4>\r\n<p>Der Autor dieser Seite &uuml;bernehmen keinerlei Gew&auml;hr f&uuml;r die Aktualit&auml;t, Korrektheit, Vollst&auml;ndigkeit oder Qualit&auml;t der bereitgestellten Informationen. Haftungsanspr&uuml;che gegen die Autoren, welche sich auf Sch&auml;den materieller oder ideeller Art beziehen, die durch die Nutzung oder Nichtnutzung der dargebotenen Informationen bzw. durch die Nutzung fehlerhafter und unvollst&auml;ndiger Informationen verursacht wurden, sind grunds&auml;tzlich ausgeschlossen, sofern seitens des Autors kein nachweislich vors&auml;tzliches oder grob fahrl&auml;ssiges Verschulden vorliegt. Alle Angebote sind freibleibend und unverbindlich. Der Autor beh&auml;lt es sich ausdr&uuml;cklich vor, Teile der Seiten oder das gesamte Angebot ohne gesonderte Ank&uuml;ndigung zu ver&auml;ndern, zu erg&auml;nzen, zu l&ouml;schen oder die Ver&ouml;ffentlichung zeitweise oder endg&uuml;ltig einzustellen.</p>\r\n<p><strong>Urheber- und Kennzeichenrecht</strong></p>\r\n<p>Alle innerhalb des Internetangebotes genannten und gegebenenfalls durch Dritte gesch&uuml;tzten Marken- und Warenzeichen unterliegen uneingeschr&auml;nkt den Bestimmungen des jeweils g&uuml;ltigen Kennzeichenrechts und den Besitzrechten der jeweiligen eingetragenen Eigent&uuml;mer. Allein aufgrund der blo&szlig;en Nennung ist nicht der Schluss zu ziehen, dass Markenzeichen nicht durch Rechte Dritter gesch&uuml;tzt sind. Das Copyright f&uuml;r ver&ouml;ffentlichte, vom Autor selbst erstellte Objekte bleiben allein bei den Autoren der Seiten. Eine Vervielf&auml;ltigung oder Verwendung solcher Grafiken, Tondokumente, Videosequenzen und Texte in anderen elektronischen oder gedruckten Publikationen ist ohne ausdr&uuml;ckliche Zustimmung der Autoren nicht gestattet.</p>\r\n<p><strong>Verweise und Links</strong></p>\r\n<p>Bei direkten oder indirekten Verweisen auf fremde Webseiten ("Hyperlinks"), die au&szlig;erhalb des Verantwortungsbereiches des Autors liegen, w&uuml;rde eine Haftungsverpflichtung ausschlie&szlig;lich in dem Fall in Kraft treten, in dem der Autor von den Inhalten Kenntnis hat und es ihnen technisch m&ouml;glich und zumutbar w&auml;re, die Nutzung im Falle rechtswidriger Inhalte zu verhindern. Der Autor erkl&auml;rt hiermit ausdr&uuml;cklich, dass zum Zeitpunkt der Linksetzung keine illegalen Inhalte auf den zu verlinkenden Seiten erkennbar waren. Auf die aktuelle und zuk&uuml;nftige Gestaltung, die Inhalte oder die Urheberschaft der verlinkten/verkn&uuml;pften Seiten hat der Autor keinerlei Einfluss. Deshalb distanziert er sich hiermit ausdr&uuml;cklich von allen Inhalten aller verlinkten/verkn&uuml;pften Seiten, die nach der Linksetzung ver&auml;ndert wurden. Diese Feststellung gilt f&uuml;r alle innerhalb des eigenen Internetangebotes gesetzten Links und Verweise sowie f&uuml;r Fremdeintr&auml;ge in allen Formen von Datenbanken, auf deren Inhalt externe Schreibzugriffe m&ouml;glich sind. F&uuml;r illegale, fehlerhafte oder unvollst&auml;ndige Inhalte und insbesondere f&uuml;r Sch&auml;den, die aus der Nutzung oder Nichtnutzung solcherart dargebotener Informationen entstehen, haftet allein der Anbieter der Seite, auf welche verwiesen wurde, nicht derjenige, der &uuml;ber Links auf die jeweilige Ver&ouml;ffentlichung lediglich verweist.</p>\r\n<p><strong>Google Analytics</strong></p>\r\n<p>Diese Website benutzt Google Analytics, einen Webanalysedienst der Google Inc. (&bdquo;Google&ldquo;). Google Analytics verwendet sog. &bdquo;Cookies&ldquo;, Textdateien, die auf Ihrem Computer gespeichert werden und die eine Analyse der Benutzung der Website durch Sie erm&ouml;glichen. Die durch den Cookie erzeugten Informationen &uuml;ber Ihre Benutzung dieser Website (einschlie&szlig;lich Ihrer IP-Adresse) wird an einen Server von Google in den USA &uuml;bertragen und dort gespeichert. Google wird diese Informationen benutzen, um Ihre Nutzung der Website auszuwerten, um Reports &uuml;ber die Websiteaktivit&auml;ten f&uuml;r die Websitebetreiber zusammenzustellen und um weitere mit der Websitenutzung und der Internetnutzung verbundene Dienstleistungen zu erbringen. Auch wird Google diese Informationen gegebenenfalls an Dritte &uuml;bertragen, sofern dies gesetzlich vorgeschrieben oder soweit Dritte diese Daten im Auftrag von Google verarbeiten. Google wird in keinem Fall Ihre IP-Adresse mit anderen Daten von Google in Verbindung bringen. Sie k&ouml;nnen die Installation der Cookies durch eine entsprechende Einstellung Ihrer Browser Software verhindern; wir weisen Sie jedoch darauf hin, dass Sie in diesem Fall gegebenenfalls nicht s&auml;mtliche Funktionen dieser Website vollumf&auml;nglich nutzen k&ouml;nnen. Durch die Nutzung dieser Website erkl&auml;ren Sie sich mit der Bearbeitung der &uuml;ber Sie erhobenen Daten durch Google in der zuvor beschriebenen Art und Weise und zu dem zuvor benannten Zweck einverstanden.</p>', 'impressum', 1),
(10, 1, 'Mitmachen', '', '', '', 'mein-Konto', 1),
(11, 1, 'Newsletter', '', '', '', 'newsletter', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_configuration`
--

DROP TABLE IF EXISTS `fcs_configuration`;
CREATE TABLE `fcs_configuration` (
  `id_configuration` int(10) UNSIGNED NOT NULL,
  `id_shop_group` int(11) UNSIGNED DEFAULT NULL,
  `id_shop` int(11) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(254) NOT NULL,
  `text` text NOT NULL,
  `value` text,
  `type` varchar(20) NOT NULL,
  `position` int(8) UNSIGNED NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_configuration`
--

INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES
(11, NULL, NULL, 1, 'FCS_PRODUCT_AVAILABILITY_LOW', 'Geringe Verfügbarkeit<br /><div class="small">Ab welcher verfügbaren Produkt-Anzahl soll beim Bestellen der Hinweis "(x verfügbar") angezeigt werden?</div>', '10', 'number', 60, '0000-00-00 00:00:00', '2014-06-01 01:40:34'),
(31, NULL, NULL, 1, 'FCS_DAYS_SHOW_PRODUCT_AS_NEW', 'Wie viele Tage sollen Produkte "als neu markiert" bleiben?', '7', 'number', 70, '0000-00-00 00:00:00', '2014-05-14 21:15:45'),
(164, NULL, NULL, 1, 'FCS_CUSTOMER_GROUP', 'Welcher Gruppe sollen neu registrierte Mitglieder zugewiesen werden?', '3', 'dropdown', 40, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(456, NULL, NULL, 1, 'FCS_FOOTER_CMS_TEXT', 'Zusätzlicher Text für den Footer', NULL, 'textarea', 80, '2014-06-11 17:50:55', '2016-07-01 21:47:47'),
(508, NULL, NULL, 1, 'FCS_FACEBOOK_URL', 'Facebook-Url für die Einbindung im Footer', 'https://www.facebook.com/FoodCoopShop/', 'text', 90, '2015-07-08 13:23:54', '2015-07-08 13:23:54'),
(538, NULL, NULL, 1, 'FCS_REGISTRATION_EMAIL_TEXT', 'Zusätzlicher Text, der in der Bestätigungsmail nach einer Registrierung versendet wird.', '', 'textarea', 170, '2016-06-26 00:00:00', '2016-06-26 00:00:00'),
(543, NULL, NULL, 1, 'FCS_RIGHT_INFO_BOX_HTML', 'Inhalt der Box in der rechten Spalte unterhalb des Warenkorbes. <br /><div class="small">Um eine Zeile grün zu hinterlegen (Überschrift) bitte als "Überschrift 3" formatieren.<br />Die Variable {ABHOLTAG} zeigt automatisch das richtige Abholdatum an.</div>', '<h3>Abholzeiten</h3>\n\n<p>Wenn du deine Produkte jetzt bestellst, kannst du sie am <strong>{ABHOLTAG}</strong>&nbsp;zwischen 17 und 19 Uhr abholen.</p>\n\n<p>Du kannst jede Woche bis sp&auml;testens Dienstag Mitternacht bestellen und sie am darauffolgenden Freitag abholen.</p>\n', 'textarea', 150, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(544, NULL, NULL, 1, 'FCS_CART_ENABLED', 'Ist die Bestell-Funktion aktiviert?<br /><div class="small">Falls die Foodcoop mal Urlaub macht, kann das Bestellen hier deaktiviert werden.</div>', '1', 'boolean', 10, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(545, NULL, NULL, 1, 'FCS_ACCOUNTING_EMAIL', 'E-Mail-Adresse des Finanzverantwortlichen<br /><div class="small">Wer bekommt die Benachrichtigung über den erfolgten Rechnungsversand?</div>', '', 'text', 110, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(546, NULL, NULL, 1, 'FCS_AUTHENTICATION_INFO_TEXT', 'Info-Text beim Registrierungsformular<br /><div class="small">Beim Registrierungsformlar wird unterhalb der E-Mail-Adresse dieser Text angezeigt.</div>', 'Um bei uns zu bestellen musst du Vereinsmitglied sein.', 'textarea', 160, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(547, NULL, NULL, 1, 'FCS_SHOW_PRODUCTS_FOR_GUESTS', 'Produkte für nicht eingeloggte Mitglieder sichtbar?', '1', 'boolean', 20, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(548, NULL, NULL, 1, 'FCS_DEFAULT_NEW_MEMBER_ACTIVE', 'Neue Mitglieder automatisch aktivieren?', '1', 'boolean', 50, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(549, NULL, NULL, 1, 'FCS_MINIMAL_CREDIT_BALANCE', 'Höhe des Bestell-Limits, ab dem den Mitgliedern kein Bestellen mehr möglich ist.<br /><div class="small">Z.B.: "100" für 100 € im Minus. 0 bedeutet "kein Bestell-Limit".</div>', '100', 'number', 30, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(550, NULL, NULL, 1, 'FCS_BANK_ACCOUNT_DATA', 'Bankverbindung für die Guthaben-Einzahlungen".', 'Guthaben-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878', 'text', 130, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(551, NULL, NULL, 1, 'FCS_MEMBER_FEE_BANK_ACCOUNT_DATA', 'Bankverbindung für die Mitgliedsbeitrags-Einzahlungen".', 'MB-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878', 'text', 140, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(552, NULL, NULL, 1, 'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS', 'Zusätzliche Liefer-Informationen für die Hersteller<br /><div class="small">wird in den Bestell-Listen nach dem Lieferdatum angezeigt.</div>', ', 15:00 bis 17:00 Uhr', 'text', 120, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(553, NULL, NULL, 1, 'FCS_ORDER_CONFIRMATION_MAIL_BCC', 'E-Mail-Adresse, an die die Bestell-Bestätigungen als BCC geschickt werden.<br /><div class="small">Kann leer gelassen werden.</div>', '', 'text', 300, '2016-10-06 00:00:00', '2016-10-06 00:00:00');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_customer`
--

DROP TABLE IF EXISTS `fcs_customer`;
CREATE TABLE `fcs_customer` (
  `id_customer` int(10) UNSIGNED NOT NULL,
  `id_shop_group` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `id_shop` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `id_gender` int(10) UNSIGNED NOT NULL,
  `id_default_group` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `id_lang` int(10) UNSIGNED DEFAULT NULL,
  `id_risk` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `company` varchar(64) DEFAULT NULL,
  `siret` varchar(14) DEFAULT NULL,
  `ape` varchar(5) DEFAULT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `passwd` varchar(32) NOT NULL,
  `last_passwd_gen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `birthday` date DEFAULT NULL,
  `newsletter` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `ip_registration_newsletter` varchar(15) DEFAULT NULL,
  `newsletter_date_add` datetime DEFAULT NULL,
  `optin` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `website` varchar(128) DEFAULT NULL,
  `outstanding_allow_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `show_public_prices` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `max_payment_days` int(10) UNSIGNED NOT NULL DEFAULT '60',
  `secure_key` varchar(32) NOT NULL DEFAULT '-1',
  `note` text,
  `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `is_guest` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_customer`
--

INSERT INTO `fcs_customer` (`id_customer`, `id_shop_group`, `id_shop`, `id_gender`, `id_default_group`, `id_lang`, `id_risk`, `company`, `siret`, `ape`, `firstname`, `lastname`, `email`, `passwd`, `last_passwd_gen`, `birthday`, `newsletter`, `ip_registration_newsletter`, `newsletter_date_add`, `optin`, `website`, `outstanding_allow_amount`, `show_public_prices`, `max_payment_days`, `secure_key`, `note`, `active`, `is_guest`, `deleted`, `date_add`, `date_upd`) VALUES
(87, 1, 1, 1, 3, 1, 0, NULL, NULL, NULL, 'Demo', 'Mitglied', 'fcs-demo-mitglied@mailinator.com', '', '2014-12-02 04:19:31', '0000-00-00', 1, '88.117.53.105', '2014-12-02 12:19:31', 0, NULL, '0.000000', 0, 0, 'd39a3a43e5d9aedc566bc211895cd16c', NULL, 1, 0, 0, '2014-12-02 12:19:31', '2015-12-06 23:37:44'),
(88, 1, 1, 1, 4, 1, 0, NULL, NULL, NULL, 'Demo', 'Admin', 'fcs-demo-admin@mailinator.com', '', '2014-12-02 04:28:43', '0000-00-00', 1, '88.117.53.105', '2014-12-02 12:28:43', 0, NULL, '0.000000', 0, 0, 'c1064e463d615234b31d0a3d8095985c', NULL, 1, 0, 0, '2014-12-02 12:28:43', '2016-09-29 16:25:09'),
(89, 1, 1, 1, 4, 1, 0, NULL, NULL, NULL, 'Demo', 'Gemüse-Hersteller', 'fcs-demo-gemuese-hersteller@mailinator.com', '', '2014-12-02 04:37:26', '0000-00-00', 0, NULL, '0000-00-00 00:00:00', 0, NULL, '0.000000', 0, 0, '28f90fbd45ee72f09399c68195244a69', NULL, 1, 0, 0, '2014-12-02 12:37:26', '2015-03-11 18:12:10'),
(90, 1, 1, 1, 4, 1, 0, NULL, NULL, NULL, 'Demo', 'Milch-Hersteller', 'fcs-demo-milch-hersteller@mailinator.com', '', '2014-12-02 04:37:49', '0000-00-00', 0, NULL, '0000-00-00 00:00:00', 0, NULL, '0.000000', 0, 0, '82230af5ed33a8b80df6e2ad426d76f2', NULL, 1, 0, 0, '2014-12-02 12:37:49', '2015-03-11 18:11:54'),
(91, 1, 1, 1, 4, 1, 0, NULL, NULL, NULL, 'Demo', 'Fleisch-Hersteller', 'fcs-demo-fleisch-hersteller@mailinator.com', '', '2014-12-02 04:38:12', '0000-00-00', 0, NULL, '0000-00-00 00:00:00', 0, NULL, '0.000000', 0, 0, '50e86f1cf90b2b9b22dc84dd58e40efc', NULL, 1, 0, 0, '2014-12-02 12:38:12', '2015-03-11 18:11:47'),
(92, 1, 1, 0, 5, 1, 1, NULL, NULL, NULL, 'Demo', 'Superadmin', 'fcs-demo-superadmin@mailinator.com', '', '2016-09-29 14:26:12', NULL, 0, NULL, NULL, 0, NULL, '0.000000', 0, 0, '-1', NULL, 1, 0, 0, '2016-09-29 16:26:12', '2016-09-29 16:26:12');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_homeslider`
--

DROP TABLE IF EXISTS `fcs_homeslider`;
CREATE TABLE `fcs_homeslider` (
  `id_homeslider_slides` int(10) UNSIGNED NOT NULL,
  `id_shop` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_homeslider_slides`
--

DROP TABLE IF EXISTS `fcs_homeslider_slides`;
CREATE TABLE `fcs_homeslider_slides` (
  `id_homeslider_slides` int(10) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_homeslider_slides`
--

INSERT INTO `fcs_homeslider_slides` (`id_homeslider_slides`, `position`, `active`) VALUES
(6, 0, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_homeslider_slides_lang`
--

DROP TABLE IF EXISTS `fcs_homeslider_slides_lang`;
CREATE TABLE `fcs_homeslider_slides_lang` (
  `id_homeslider_slides` int(10) UNSIGNED NOT NULL,
  `id_lang` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `legend` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_homeslider_slides_lang`
--

INSERT INTO `fcs_homeslider_slides_lang` (`id_homeslider_slides`, `id_lang`, `title`, `description`, `legend`, `url`, `image`) VALUES
(6, 1, 'FoodCoopShop', '<h2>FoodCoopShop</h2>\r\n<p>Die Software f&uuml;r eure Foodcoop.</p>', 'Die Software für eure Foodcoop', '#', '2be64c60e6126c9085fd9d9717532a14e5a5bb4e_slide4.png');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_image`
--

DROP TABLE IF EXISTS `fcs_image`;
CREATE TABLE `fcs_image` (
  `id_image` int(10) UNSIGNED NOT NULL,
  `id_product` int(10) UNSIGNED NOT NULL,
  `position` smallint(2) UNSIGNED NOT NULL DEFAULT '0',
  `cover` tinyint(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_image`
--

INSERT INTO `fcs_image` (`id_image`, `id_product`, `position`, `cover`) VALUES
(154, 60, 2, 1),
(156, 340, 1, NULL),
(157, 338, 1, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_image_lang`
--

DROP TABLE IF EXISTS `fcs_image_lang`;
CREATE TABLE `fcs_image_lang` (
  `id_image` int(10) UNSIGNED NOT NULL,
  `id_lang` int(10) UNSIGNED NOT NULL,
  `legend` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_image_lang`
--

INSERT INTO `fcs_image_lang` (`id_image`, `id_lang`, `legend`) VALUES
(154, 1, 'Milch'),
(156, 1, 'Beuschl'),
(157, 1, 'Streichwurst');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_image_shop`
--

DROP TABLE IF EXISTS `fcs_image_shop`;
CREATE TABLE `fcs_image_shop` (
  `id_image` int(11) UNSIGNED NOT NULL,
  `id_shop` int(11) UNSIGNED NOT NULL,
  `cover` tinyint(1) UNSIGNED DEFAULT NULL,
  `id_product` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_image_shop`
--

INSERT INTO `fcs_image_shop` (`id_image`, `id_shop`, `cover`, `id_product`) VALUES
(154, 1, 1, 60),
(156, 1, 1, 340),
(157, 1, 1, 338);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_manufacturer`
--

DROP TABLE IF EXISTS `fcs_manufacturer`;
CREATE TABLE `fcs_manufacturer` (
  `id_manufacturer` int(10) UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `holiday` int(11) UNSIGNED NOT NULL,
  `is_private` int(11) UNSIGNED NOT NULL,
  `uid_number` varchar(30) NOT NULL,
  `additional_text_for_invoice` text NOT NULL,
  `iban` varchar(20) NOT NULL,
  `bic` varchar(8) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `firmenbuchnummer` varchar(20) NOT NULL,
  `firmengericht` varchar(150) NOT NULL,
  `aufsichtsbehoerde` varchar(150) NOT NULL,
  `kammer` varchar(150) NOT NULL,
  `homepage` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_manufacturer`
--

INSERT INTO `fcs_manufacturer` (`id_manufacturer`, `name`, `date_add`, `date_upd`, `active`, `holiday`, `is_private`, `uid_number`, `additional_text_for_invoice`, `iban`, `bic`, `bank_name`, `firmenbuchnummer`, `firmengericht`, `aufsichtsbehoerde`, `kammer`, `homepage`) VALUES
(4, 'Demo Fleisch-Hersteller', '2014-05-14 13:23:02', '2015-05-15 13:31:41', 1, 0, 0, '', '', '', '', '', '', '', '', '', ''),
(5, 'Demo Gemüse-Hersteller', '2014-05-14 13:36:44', '2016-09-27 09:34:51', 1, 0, 0, '', '', '', '', '', '', '', '', '', ''),
(15, 'Demo Milch-Hersteller', '2014-06-04 21:45:12', '2016-03-07 09:02:25', 1, 0, 0, '', '', '', '', '', '', '', '', '', ''),
(16, 'Hersteller ohne Customer-Eintrag', '2014-06-04 21:45:12', '2016-03-07 09:02:25', 1, 0, 0, '', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_manufacturer_lang`
--

DROP TABLE IF EXISTS `fcs_manufacturer_lang`;
CREATE TABLE `fcs_manufacturer_lang` (
  `id_manufacturer` int(10) UNSIGNED NOT NULL,
  `id_lang` int(10) UNSIGNED NOT NULL,
  `description` text,
  `short_description` text,
  `meta_title` varchar(128) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_manufacturer_lang`
--

INSERT INTO `fcs_manufacturer_lang` (`id_manufacturer`, `id_lang`, `description`, `short_description`, `meta_title`, `meta_keywords`, `meta_description`) VALUES
(4, 1, '<p>tests</p>\r\n', '', '', '', ''),
(5, 1, '<p>Gem&uuml;se-Hersteller Beschreibung&nbsp;lang</p>', '<div class="entry-content">\r\n<p>Gem&uuml;se-Hersteller Beschreibung kurz</p>\r\n</div>', '', '', ''),
(15, 1, '<p>Ja, ich bin der Milchhersteller!</p>', '', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_orders`
--

DROP TABLE IF EXISTS `fcs_orders`;
CREATE TABLE `fcs_orders` (
  `id_order` int(10) UNSIGNED NOT NULL,
  `reference` varchar(9) DEFAULT NULL,
  `id_shop_group` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `id_shop` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `id_carrier` int(10) UNSIGNED NOT NULL,
  `id_lang` int(10) UNSIGNED NOT NULL,
  `id_customer` int(10) UNSIGNED NOT NULL,
  `id_cart` int(10) UNSIGNED NOT NULL,
  `id_cake_cart` int(10) NOT NULL,
  `id_currency` int(10) UNSIGNED NOT NULL,
  `id_address_delivery` int(10) UNSIGNED NOT NULL,
  `id_address_invoice` int(10) UNSIGNED NOT NULL,
  `current_state` int(10) UNSIGNED NOT NULL,
  `secure_key` varchar(32) NOT NULL DEFAULT '-1',
  `payment` varchar(255) NOT NULL,
  `conversion_rate` decimal(13,6) NOT NULL DEFAULT '1.000000',
  `module` varchar(255) DEFAULT NULL,
  `recyclable` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `gift` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `gift_message` text,
  `mobile_theme` tinyint(1) NOT NULL DEFAULT '0',
  `shipping_number` varchar(64) DEFAULT NULL,
  `total_discounts` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_discounts_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_discounts_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_paid` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_paid_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_paid_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_paid_real` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_products` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_products_wt` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_shipping` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_shipping_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_shipping_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `carrier_tax_rate` decimal(10,3) NOT NULL DEFAULT '0.000',
  `total_wrapping` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_wrapping_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_wrapping_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `invoice_number` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `delivery_number` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `invoice_date` datetime NOT NULL,
  `delivery_date` datetime NOT NULL,
  `valid` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  `round_mode` tinyint(1) NOT NULL DEFAULT '2',
  `round_type` tinyint(1) NOT NULL DEFAULT '1',
  `total_deposit` decimal(10,2) NOT NULL,
  `general_terms_and_conditions_accepted` tinyint(4) UNSIGNED NOT NULL,
  `cancellation_terms_accepted` tinyint(4) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_order_detail`
--

DROP TABLE IF EXISTS `fcs_order_detail`;
CREATE TABLE `fcs_order_detail` (
  `id_order_detail` int(10) UNSIGNED NOT NULL,
  `id_order` int(10) UNSIGNED NOT NULL,
  `id_order_invoice` int(11) DEFAULT NULL,
  `id_warehouse` int(10) UNSIGNED DEFAULT '0',
  `id_shop` int(11) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_attribute_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_quantity` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `product_quantity_in_stock` int(10) NOT NULL DEFAULT '0',
  `product_quantity_refunded` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `product_quantity_return` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `product_quantity_reinjected` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `product_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `reduction_percent` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reduction_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `reduction_amount_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `reduction_amount_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `group_reduction` decimal(10,2) NOT NULL DEFAULT '0.00',
  `product_quantity_discount` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `product_ean13` varchar(13) DEFAULT NULL,
  `product_upc` varchar(12) DEFAULT NULL,
  `product_reference` varchar(32) DEFAULT NULL,
  `product_supplier_reference` varchar(32) DEFAULT NULL,
  `product_weight` decimal(20,6) NOT NULL,
  `tax_computation_method` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `tax_name` varchar(16) NOT NULL,
  `tax_rate` decimal(10,3) NOT NULL DEFAULT '0.000',
  `ecotax` decimal(21,6) NOT NULL DEFAULT '0.000000',
  `ecotax_tax_rate` decimal(5,3) NOT NULL DEFAULT '0.000',
  `discount_quantity_applied` tinyint(1) NOT NULL DEFAULT '0',
  `download_hash` varchar(255) DEFAULT NULL,
  `download_nb` int(10) UNSIGNED DEFAULT '0',
  `download_deadline` datetime DEFAULT NULL,
  `total_price_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_price_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `unit_price_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `unit_price_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_shipping_price_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_shipping_price_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `purchase_supplier_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `original_product_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `id_tax_rules_group` int(11) UNSIGNED DEFAULT '0',
  `id_tax` int(11) UNSIGNED DEFAULT '0',
  `original_wholesale_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `deposit` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_order_detail`
--

INSERT INTO `fcs_order_detail` (`id_order_detail`, `id_order`, `id_order_invoice`, `id_warehouse`, `id_shop`, `product_id`, `product_attribute_id`, `product_name`, `product_quantity`, `product_quantity_in_stock`, `product_quantity_refunded`, `product_quantity_return`, `product_quantity_reinjected`, `product_price`, `reduction_percent`, `reduction_amount`, `reduction_amount_tax_incl`, `reduction_amount_tax_excl`, `group_reduction`, `product_quantity_discount`, `product_ean13`, `product_upc`, `product_reference`, `product_supplier_reference`, `product_weight`, `tax_computation_method`, `tax_name`, `tax_rate`, `ecotax`, `ecotax_tax_rate`, `discount_quantity_applied`, `download_hash`, `download_nb`, `download_deadline`, `total_price_tax_incl`, `total_price_tax_excl`, `unit_price_tax_incl`, `unit_price_tax_excl`, `total_shipping_price_tax_incl`, `total_shipping_price_tax_excl`, `purchase_supplier_price`, `original_product_price`, `id_tax_rules_group`, `id_tax`, `original_wholesale_price`, `deposit`) VALUES
(1, 1, NULL, 0, 0, 346, 0, 'Artischocke : Stück', 2, 0, 0, 0, 0, '3.305786', '0.00', '0.000000', '0.000000', '0.000000', '0.00', '0.000000', NULL, NULL, NULL, NULL, '0.000000', 0, '', '0.000', '0.000000', '0.000', 0, NULL, 0, NULL, '3.640000', '3.305786', '0.000000', '0.000000', '0.000000', '0.000000', '0.000000', '0.000000', 2, 2, '0.000000', '1.00'),
(2, 1, NULL, 0, 0, 60, 10, 'Milch : 0,5l', 3, 0, 0, 0, 0, '1.636365', '0.00', '0.000000', '0.000000', '0.000000', '0.00', '0.000000', NULL, NULL, NULL, NULL, '0.000000', 0, '', '0.000', '0.000000', '0.000', 0, NULL, 0, NULL, '1.860000', '1.636365', '0.000000', '0.000000', '0.000000', '0.000000', '0.000000', '0.000000', 3, 3, '0.000000', '1.50');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_order_detail_tax`
--

DROP TABLE IF EXISTS `fcs_order_detail_tax`;
CREATE TABLE `fcs_order_detail_tax` (
  `id_order_detail` int(11) NOT NULL,
  `id_tax` int(11) UNSIGNED DEFAULT '0',
  `unit_amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
  `total_amount` decimal(16,6) NOT NULL DEFAULT '0.000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_order_detail_tax`
--

INSERT INTO `fcs_order_detail_tax` (`id_order_detail`, `id_tax`, `unit_amount`, `total_amount`) VALUES
(1, 2, '0.170000', '0.340000'),
(2, 3, '0.070000', '0.210000');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_product`
--

DROP TABLE IF EXISTS `fcs_product`;
CREATE TABLE `fcs_product` (
  `id_product` int(10) UNSIGNED NOT NULL,
  `id_supplier` int(10) UNSIGNED DEFAULT NULL,
  `id_manufacturer` int(10) UNSIGNED DEFAULT NULL,
  `id_category_default` int(10) UNSIGNED DEFAULT NULL,
  `id_shop_default` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `id_tax_rules_group` int(11) UNSIGNED NOT NULL,
  `id_tax` int(11) UNSIGNED NOT NULL,
  `on_sale` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `online_only` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `ean13` varchar(13) DEFAULT NULL,
  `upc` varchar(12) DEFAULT NULL,
  `ecotax` decimal(17,6) NOT NULL DEFAULT '0.000000',
  `quantity` int(10) NOT NULL DEFAULT '0',
  `minimal_quantity` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `wholesale_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `unity` varchar(255) DEFAULT NULL,
  `unit_price_ratio` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `additional_shipping_cost` decimal(20,2) NOT NULL DEFAULT '0.00',
  `reference` varchar(32) DEFAULT NULL,
  `supplier_reference` varchar(32) DEFAULT NULL,
  `location` varchar(64) DEFAULT NULL,
  `width` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `height` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `depth` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `weight` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `out_of_stock` int(10) UNSIGNED NOT NULL DEFAULT '2',
  `quantity_discount` tinyint(1) DEFAULT '0',
  `customizable` tinyint(2) NOT NULL DEFAULT '0',
  `uploadable_files` tinyint(4) NOT NULL DEFAULT '0',
  `text_fields` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `redirect_type` enum('','404','301','302') NOT NULL DEFAULT '',
  `id_product_redirected` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `available_for_order` tinyint(1) NOT NULL DEFAULT '1',
  `available_date` date NOT NULL DEFAULT '0000-00-00',
  `condition` enum('new','used','refurbished') NOT NULL DEFAULT 'new',
  `show_price` tinyint(1) NOT NULL DEFAULT '1',
  `indexed` tinyint(1) NOT NULL DEFAULT '0',
  `visibility` enum('both','catalog','search','none') NOT NULL DEFAULT 'both',
  `cache_is_pack` tinyint(1) NOT NULL DEFAULT '0',
  `cache_has_attachments` tinyint(1) NOT NULL DEFAULT '0',
  `is_virtual` tinyint(1) NOT NULL DEFAULT '0',
  `cache_default_attribute` int(10) UNSIGNED DEFAULT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  `advanced_stock_management` tinyint(1) NOT NULL DEFAULT '0',
  `pack_stock_type` int(11) UNSIGNED NOT NULL DEFAULT '3'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_product`
--

INSERT INTO `fcs_product` (`id_product`, `id_supplier`, `id_manufacturer`, `id_category_default`, `id_shop_default`, `id_tax_rules_group`, `id_tax`, `on_sale`, `online_only`, `ean13`, `upc`, `ecotax`, `quantity`, `minimal_quantity`, `price`, `wholesale_price`, `unity`, `unit_price_ratio`, `additional_shipping_cost`, `reference`, `supplier_reference`, `location`, `width`, `height`, `depth`, `weight`, `out_of_stock`, `quantity_discount`, `customizable`, `uploadable_files`, `text_fields`, `active`, `redirect_type`, `id_product_redirected`, `available_for_order`, `available_date`, `condition`, `show_price`, `indexed`, `visibility`, `cache_is_pack`, `cache_has_attachments`, `is_virtual`, `cache_default_attribute`, `date_add`, `date_upd`, `advanced_stock_management`, `pack_stock_type`) VALUES
(47, 0, 15, 17, 1, 3, 3, 0, 0, '', '', '0.000000', 0, 1, '1.727273', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, 0, 0, '2014-06-11 21:20:24', '2015-02-26 14:49:19', 0, 3),
(49, 0, 15, 17, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '1.090909', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 1, 1, 1, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, 0, 0, '2014-06-11 21:20:24', '2015-02-26 14:46:32', 0, 3),
(60, 0, 15, 17, 1, 3, 3, 0, 0, '', '', '0.000000', 0, 1, '0.909091', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, 0, 9, '2014-06-11 21:20:24', '2014-12-14 19:47:33', 0, 3),
(102, 0, 4, 20, 1, 2, 2, 0, 0, '', '', '0.000000', 0, 1, '3.181818', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-04-27 21:13:37', '2014-09-19 14:32:51', 0, 3),
(103, 0, 4, 16, 1, 2, 2, 0, 0, '', '', '0.000000', 0, 1, '3.181818', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-05-05 08:28:49', '2014-08-16 14:05:58', 0, 3),
(161, 0, 5, 20, 1, 2, 2, 0, 0, '', '', '0.000000', 0, 1, '0.909091', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, 0, 0, '2014-07-12 20:41:43', '2014-12-14 19:35:39', 0, 3),
(163, 0, 5, 20, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '1.363637', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2014-07-12 20:41:43', '0000-00-00 00:00:00', 0, 3),
(173, 0, 5, 20, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '1.545454', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, 0, 0, '2014-07-12 20:41:43', '2014-12-14 19:36:10', 0, 3),
(225, 0, 5, 13, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '1.545454', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2014-07-19 21:05:13', '2014-08-03 21:28:39', 0, 3),
(279, 0, 5, 13, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '2.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, 0, 0, '2014-09-16 21:42:14', '2014-12-14 19:35:50', 0, 3),
(338, 0, 4, 2, 1, 5, 0, 0, 0, '', '', '0.000000', 0, 1, '4.545455', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, 0, 0, '2014-11-10 20:02:35', '2014-12-14 19:35:58', 0, 3),
(339, 0, 5, 15, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, 0, 1, '2015-09-07 12:05:38', '2015-02-26 13:54:07', 0, 3),
(340, 0, 4, 20, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-05-05 08:28:45', '2015-06-23 14:52:53', 0, 3),
(343, 0, 5, 20, 1, 0, 0, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2015-07-06 09:46:19', '2015-07-06 09:46:19', 0, 3),
(344, 0, 5, 20, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2015-10-05 17:22:40', '2015-07-06 10:24:44', 0, 3),
(346, 0, 5, 20, 1, 2, 2, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2015-08-19 09:35:45', '2015-08-19 09:35:45', 0, 3),
(347, 0, 4, 20, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 17, '2015-08-24 20:36:58', '2015-08-24 20:36:58', 0, 3),
(350, 0, 15, 20, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2015-09-07 12:11:00', '2015-09-07 12:11:00', 0, 3),
(352, 0, 5, 20, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2015-10-05 17:21:00', '2015-10-05 17:21:00', 0, 3),
(353, 0, 5, 20, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2015-10-12 21:48:14', '2015-10-12 21:48:14', 0, 3),
(354, 0, 4, 20, 1, 2, 2, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-05-14 13:31:36', '2015-11-24 22:37:48', 0, 3),
(355, 0, 5, 20, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-02-10 08:44:47', '2016-02-10 08:44:47', 0, 3),
(356, 0, 4, 20, 1, 2, 2, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-03-23 12:10:17', '2016-03-23 12:10:17', 0, 3),
(357, 0, 5, 20, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-03-23 21:50:37', '2016-03-23 21:50:37', 0, 3),
(358, 0, 5, 20, 1, 4, 0, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-03-23 21:51:04', '2016-03-23 21:51:04', 0, 3),
(359, 0, 4, 20, 1, 2, 2, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-03-24 17:53:06', '2016-03-24 17:53:06', 0, 3),
(360, 0, 4, 20, 1, 2, 2, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-04-08 10:35:56', '2016-04-08 10:35:56', 0, 3),
(361, 0, 4, 20, 1, 2, 2, 0, 0, '', '', '0.000000', 0, 1, '9.090909', '2.000000', '2kg', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-05-03 17:08:33', '2016-05-03 17:25:29', 0, 3),
(362, 0, 4, 20, 1, 2, 2, 0, 0, '', '', '0.000000', 0, 1, '0.000000', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, 0, 0, '2016-05-25 13:15:31', '2016-05-25 13:15:31', 0, 3);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_product_attribute`
--

DROP TABLE IF EXISTS `fcs_product_attribute`;
CREATE TABLE `fcs_product_attribute` (
  `id_product_attribute` int(10) UNSIGNED NOT NULL,
  `id_product` int(10) UNSIGNED NOT NULL,
  `reference` varchar(32) DEFAULT NULL,
  `supplier_reference` varchar(32) DEFAULT NULL,
  `location` varchar(64) DEFAULT NULL,
  `ean13` varchar(13) DEFAULT NULL,
  `upc` varchar(12) DEFAULT NULL,
  `wholesale_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `ecotax` decimal(17,6) NOT NULL DEFAULT '0.000000',
  `quantity` int(10) NOT NULL DEFAULT '0',
  `weight` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `unit_price_impact` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `default_on` tinyint(1) UNSIGNED DEFAULT NULL,
  `minimal_quantity` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `available_date` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_product_attribute`
--

INSERT INTO `fcs_product_attribute` (`id_product_attribute`, `id_product`, `reference`, `supplier_reference`, `location`, `ean13`, `upc`, `wholesale_price`, `price`, `ecotax`, `quantity`, `weight`, `unit_price_impact`, `default_on`, `minimal_quantity`, `available_date`) VALUES
(1, 339, '', '', '', '', '', '0.000000', '1.363636', '0.000000', 1000, '0.000000', '0.000000', 1, 1, '0000-00-00'),
(2, 339, '', '', '', '', '', '0.000000', '5.454545', '0.000000', 1000, '0.000000', '0.000000', 0, 1, '0000-00-00'),
(3, 339, '', '', '', '', '', '0.000000', '10.909091', '0.000000', 1000, '0.000000', '0.000000', 0, 1, '0000-00-00'),
(9, 60, NULL, NULL, NULL, NULL, NULL, '0.000000', '0.000000', '0.000000', 0, '0.000000', '0.000000', 1, 1, '0000-00-00'),
(10, 60, NULL, NULL, NULL, NULL, NULL, '0.000000', '0.000000', '0.000000', 0, '0.000000', '0.000000', 0, 1, '0000-00-00'),
(11, 60, NULL, NULL, NULL, NULL, NULL, '0.000000', '0.000000', '0.000000', 0, '0.000000', '0.000000', 0, 1, '0000-00-00'),
(14, 102, NULL, NULL, NULL, NULL, NULL, '0.000000', '0.000000', '0.000000', 0, '0.000000', '0.000000', 1, 1, '0000-00-00'),
(15, 102, NULL, NULL, NULL, NULL, NULL, '0.000000', '0.000000', '0.000000', 0, '0.000000', '0.000000', 0, 1, '0000-00-00'),
(16, 102, NULL, NULL, NULL, NULL, NULL, '0.000000', '0.000000', '0.000000', 0, '0.000000', '0.000000', 0, 1, '0000-00-00'),
(17, 347, NULL, NULL, NULL, NULL, NULL, '0.000000', '0.000000', '0.000000', 0, '0.000000', '0.000000', 1, 1, '0000-00-00'),
(18, 361, NULL, NULL, NULL, NULL, NULL, '0.000000', '0.000000', '0.000000', 0, '0.000000', '0.000000', 1, 1, '0000-00-00');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_product_attribute_combination`
--

DROP TABLE IF EXISTS `fcs_product_attribute_combination`;
CREATE TABLE `fcs_product_attribute_combination` (
  `id_attribute` int(10) UNSIGNED NOT NULL,
  `id_product_attribute` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_product_attribute_combination`
--

INSERT INTO `fcs_product_attribute_combination` (`id_attribute`, `id_product_attribute`) VALUES
(28, 2),
(32, 9),
(33, 10),
(35, 1),
(35, 18),
(55, 3),
(59, 11),
(62, 17),
(83, 14),
(85, 15),
(86, 16);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_product_attribute_shop`
--

DROP TABLE IF EXISTS `fcs_product_attribute_shop`;
CREATE TABLE `fcs_product_attribute_shop` (
  `id_product_attribute` int(10) UNSIGNED NOT NULL,
  `id_shop` int(10) UNSIGNED NOT NULL,
  `wholesale_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `ecotax` decimal(17,6) NOT NULL DEFAULT '0.000000',
  `weight` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `unit_price_impact` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `default_on` tinyint(1) UNSIGNED DEFAULT NULL,
  `minimal_quantity` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `available_date` date NOT NULL DEFAULT '0000-00-00',
  `id_product` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_product_attribute_shop`
--

INSERT INTO `fcs_product_attribute_shop` (`id_product_attribute`, `id_shop`, `wholesale_price`, `price`, `ecotax`, `weight`, `unit_price_impact`, `default_on`, `minimal_quantity`, `available_date`, `id_product`) VALUES
(1, 1, '0.000000', '1.863636', '0.000000', '0.000000', '0.000000', 1, 1, '0000-00-00', 339),
(2, 1, '0.000000', '5.454545', '0.000000', '0.000000', '0.000000', 0, 1, '0000-00-00', 339),
(3, 1, '0.000000', '10.909091', '0.000000', '0.000000', '0.000000', 0, 1, '0000-00-00', 339),
(9, 1, '0.000000', '0.272727', '0.000000', '0.000000', '0.000000', 1, 1, '0000-00-00', 60),
(10, 1, '0.000000', '0.545455', '0.000000', '0.000000', '0.000000', 0, 1, '0000-00-00', 60),
(11, 1, '0.000000', '1.090909', '0.000000', '0.000000', '0.000000', 0, 1, '0000-00-00', 60),
(14, 1, '0.000000', '3.181819', '0.000000', '0.000000', '0.000000', 1, 1, '0000-00-00', 102),
(15, 1, '0.000000', '5.454545', '0.000000', '0.000000', '0.000000', 0, 1, '0000-00-00', 102),
(16, 1, '0.000000', '6.363636', '0.000000', '0.000000', '0.000000', 0, 1, '0000-00-00', 102),
(17, 1, '0.000000', '0.010000', '0.000000', '0.000000', '0.000000', 1, 1, '0000-00-00', 347),
(18, 1, '0.000000', '0.000000', '0.000000', '0.000000', '0.000000', 1, 1, '0000-00-00', 361);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_product_lang`
--

DROP TABLE IF EXISTS `fcs_product_lang`;
CREATE TABLE `fcs_product_lang` (
  `id_product` int(10) UNSIGNED NOT NULL,
  `id_shop` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `id_lang` int(10) UNSIGNED NOT NULL,
  `description` text,
  `description_short` text,
  `link_rewrite` varchar(128) NOT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_title` varchar(128) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `available_now` varchar(255) DEFAULT NULL,
  `available_later` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_product_lang`
--

INSERT INTO `fcs_product_lang` (`id_product`, `id_shop`, `id_lang`, `description`, `description_short`, `link_rewrite`, `meta_description`, `meta_keywords`, `meta_title`, `name`, `available_now`, `available_later`) VALUES
(0, 1, 0, '', '', 'test-artikel', NULL, NULL, NULL, 'Test-Artikel', NULL, NULL),
(47, 1, 1, 'Lange <strong>Beschreibung</strong>', '200ml<br />\nnoch ein text', 'joghurt', '', '', '', 'Joghurt', '', ''),
(49, 1, 1, '', '<p>250g</p>', 'topfen', '', '', '', 'Topfen', '', ''),
(60, 1, 1, '', '1 Liter', 'milch', '', '', '', 'Milch', '', ''),
(102, 1, 1, '', '<p>2 Paar</p>', 'frankfurter', '', '', '', 'Frankfurter', '', ''),
(103, 1, 1, '', '2 Paar', 'bratwrstel', '', '', '', 'Bratwürstel', '', ''),
(161, 1, 1, '', 'St&uuml;ck, wei&szlig; oder lila (nur au&szlig;en und schmeckt genauso wie der wei&szlig;e)', 'kohlrabi', '', '', '', 'Kohlrabi', '', ''),
(163, 1, 1, '', '0,25kg', 'mangold', '', '', '', 'Mangold', '', ''),
(173, 1, 1, '', '<p>1kg</p>', 'zwiebel', '', '', '', 'Zwiebel', '', ''),
(225, 1, 1, '', 'Salattomate, rot und rund<br />\n500 g', 'tomaten', '', '', '', 'Tomaten', '', ''),
(279, 1, 1, '', '<p>&nbsp;pro St&uuml;ck</p>', 'romanesco', '', '', '', 'Romanesco', '', ''),
(338, 1, 1, 'lange beschreibung', '', 'streichwurst', '', '', '', 'Streichwurst', '', ''),
(339, 1, 1, '', '', 'kartoffel', '', '', '', 'Kartoffel', '', ''),
(340, 1, 1, '', '', 'beuschl', '', '', '', 'Beuschl', '', ''),
(343, 1, 1, '', '', 'rote-rben', '', '', '', 'Rote Rüben', '', ''),
(344, 1, 1, '', '', 'knoblauch', '', '', '', 'Knoblauch', '', ''),
(346, 1, 1, '', '', 'artischocke', '', '', '', 'Artischocke', '', ''),
(347, 1, 1, '', '', 'essigwurst', '', '', '', 'Essigwurst', '', ''),
(350, 1, 1, '', '', 'neuer-artikel-von-demo-milch-hersteller', '', '', '', 'Neuer Artikel von Demo Milch-Hersteller', '', ''),
(352, 1, 1, '', '', 'vogerlsalat', '', '', '', 'Vogerlsalat', '', ''),
(353, 1, 1, '', '', 'neuer-artikel-von-demo-gemse-hersteller', '', '', '', 'Neuer Artikel von Demo Gemüse-Hersteller', '', ''),
(354, 1, 1, '', '', 'schnitzel', '', '', '', 'Schnitzel', '', ''),
(355, 1, 1, '', '', 'neuer-artikel-von-demo-gemse-hersteller', '', '', '', 'Neuer Artikel von Demo Gemüse-Hersteller', '', ''),
(356, 1, 1, '', '', 'neuer-artikel-von-demo-fleisch-hersteller', '', '', '', 'Neuer Artikel von Demo Fleisch-Hersteller', '', ''),
(357, 1, 1, '', '', 'neuer-artikel-von-demo-gemse-hersteller', '', '', '', 'Neuer Artikel von Demo Gemüse-Hersteller', '', ''),
(358, 1, 1, '', '', 'neuer-artikel-von-demo-gemse-hersteller', '', '', '', 'Neuer Artikel von Demo Gemüse-Hersteller', '', ''),
(359, 1, 1, '', '', 'neuer-artikel-von-demo-fleisch-hersteller', '', '', '', 'Neuer Artikel von Demo Fleisch-Hersteller', '', ''),
(360, 1, 1, '', '', 'neuer-artikel-von-demo-fleisch-hersteller', '', '', '', 'Neuer Artikel von Demo Fleisch-Hersteller', '', ''),
(361, 1, 1, '<p>Supermenschen brauch Superwürstl</p>', '<p>Unsere Superwurst für Supermenschen</p>', 'superwuerstl', '', '', '', 'SuperWürstl', '', ''),
(362, 1, 1, '', '', 'neuer-artikel-von-demo-fleisch-hersteller', '', '', '', 'Neuer Artikel von Demo Fleisch-Hersteller', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_product_shop`
--

DROP TABLE IF EXISTS `fcs_product_shop`;
CREATE TABLE `fcs_product_shop` (
  `id_product` int(10) UNSIGNED NOT NULL,
  `id_shop` int(10) UNSIGNED NOT NULL,
  `id_category_default` int(10) UNSIGNED DEFAULT NULL,
  `id_tax_rules_group` int(11) UNSIGNED NOT NULL,
  `on_sale` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `online_only` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `ecotax` decimal(17,6) NOT NULL DEFAULT '0.000000',
  `minimal_quantity` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `wholesale_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `unity` varchar(255) DEFAULT NULL,
  `unit_price_ratio` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `additional_shipping_cost` decimal(20,2) NOT NULL DEFAULT '0.00',
  `customizable` tinyint(2) NOT NULL DEFAULT '0',
  `uploadable_files` tinyint(4) NOT NULL DEFAULT '0',
  `text_fields` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `redirect_type` enum('','404','301','302') NOT NULL DEFAULT '',
  `id_product_redirected` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `available_for_order` tinyint(1) NOT NULL DEFAULT '1',
  `available_date` date NOT NULL DEFAULT '0000-00-00',
  `condition` enum('new','used','refurbished') NOT NULL DEFAULT 'new',
  `show_price` tinyint(1) NOT NULL DEFAULT '1',
  `indexed` tinyint(1) NOT NULL DEFAULT '0',
  `visibility` enum('both','catalog','search','none') NOT NULL DEFAULT 'both',
  `cache_default_attribute` int(10) UNSIGNED DEFAULT NULL,
  `advanced_stock_management` tinyint(1) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  `pack_stock_type` int(11) UNSIGNED NOT NULL DEFAULT '3'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_product_shop`
--

INSERT INTO `fcs_product_shop` (`id_product`, `id_shop`, `id_category_default`, `id_tax_rules_group`, `on_sale`, `online_only`, `ecotax`, `minimal_quantity`, `price`, `wholesale_price`, `unity`, `unit_price_ratio`, `additional_shipping_cost`, `customizable`, `uploadable_files`, `text_fields`, `active`, `redirect_type`, `id_product_redirected`, `available_for_order`, `available_date`, `condition`, `show_price`, `indexed`, `visibility`, `cache_default_attribute`, `advanced_stock_management`, `date_add`, `date_upd`, `pack_stock_type`) VALUES
(47, 1, 17, 3, 0, 0, '0.000000', 1, '1.727273', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, '2014-06-11 21:20:24', '2015-02-26 14:49:19', 3),
(49, 1, 17, 4, 0, 0, '0.000000', 1, '1.090909', '0.000000', '', '0.000000', '0.00', 1, 1, 1, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, '2014-06-11 21:20:24', '2015-02-26 14:46:32', 3),
(60, 1, 17, 3, 0, 0, '0.000000', 1, '0.909091', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 9, 0, '2014-06-11 21:20:24', '2014-12-14 19:47:33', 3),
(102, 1, 20, 2, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-04-27 21:13:37', '2014-09-19 14:32:51', 3),
(103, 1, 16, 2, 0, 0, '0.000000', 1, '3.181819', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-05-05 08:28:49', '2014-08-16 14:05:58', 3),
(161, 1, 20, 2, 0, 0, '0.000000', 1, '1.363636', '0.000000', 'Stück', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, '2014-07-12 20:41:43', '2014-12-14 19:35:39', 3),
(163, 1, 20, 4, 0, 0, '0.000000', 1, '1.363637', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2014-07-12 20:41:43', '0000-00-00 00:00:00', 3),
(173, 1, 20, 4, 0, 0, '0.000000', 1, '1.545454', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, '2014-07-12 20:41:43', '2014-12-14 19:36:10', 3),
(225, 1, 13, 4, 0, 0, '0.000000', 1, '1.545454', '0.000000', '500 g', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2014-07-19 21:05:13', '2014-08-03 21:28:39', 3),
(279, 1, 13, 4, 0, 0, '0.000000', 1, '2.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, '2014-09-16 21:42:14', '2014-12-14 19:35:50', 3),
(338, 1, 2, 5, 0, 0, '0.000000', 1, '4.464286', '0.000000', 'ca. 300g', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, '2014-11-10 20:02:35', '2014-12-14 19:35:58', 3),
(339, 1, 15, 4, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 1, 0, '2015-09-07 12:05:38', '2015-02-26 13:54:07', 3),
(340, 1, 20, 4, 0, 0, '0.000000', 1, '4.545455', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-05-05 08:28:45', '2015-06-23 14:52:53', 3),
(341, 1, 20, 4, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2015-06-23 15:58:11', '2015-06-23 15:58:11', 3),
(342, 1, 20, 4, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2015-06-23 21:55:52', '2015-06-23 21:55:52', 3),
(343, 1, 20, 0, 0, 0, '0.000000', 1, '1.900000', '0.000000', '1 kg', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2015-07-06 09:46:19', '2015-07-06 09:46:19', 3),
(344, 1, 20, 4, 0, 0, '0.000000', 1, '0.636364', '0.000000', '100 g', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2015-10-05 17:22:40', '2015-07-06 10:24:44', 3),
(346, 1, 20, 2, 0, 0, '0.000000', 1, '1.652893', '0.000000', 'Stück', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2015-08-19 09:35:46', '2015-08-19 09:35:46', 3),
(347, 1, 20, 4, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 17, 0, '2015-08-24 20:36:58', '2015-08-24 20:36:58', 3),
(348, 1, 20, 4, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2015-09-05 14:54:39', '2015-09-05 14:54:39', 3),
(350, 1, 20, 4, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2015-09-07 12:11:00', '2015-09-07 12:11:00', 3),
(352, 1, 20, 4, 0, 0, '0.000000', 1, '2.000000', '0.000000', '100 g', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2015-10-05 17:21:00', '2015-10-05 17:21:00', 3),
(353, 1, 20, 4, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2015-10-12 21:48:14', '2015-10-12 21:48:14', 3),
(354, 1, 20, 2, 0, 0, '0.000000', 1, '9.090909', '0.000000', 'Stück', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-05-14 13:31:36', '2015-11-24 22:37:49', 3),
(355, 1, 20, 4, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-02-10 08:44:47', '2016-02-10 08:44:47', 3),
(356, 1, 20, 2, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-03-23 12:10:17', '2016-03-23 12:10:17', 3),
(357, 1, 20, 4, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-03-23 21:50:37', '2016-03-23 21:50:37', 3),
(358, 1, 20, 4, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-03-23 21:51:04', '2016-03-23 21:51:04', 3),
(359, 1, 20, 2, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-03-24 17:53:06', '2016-03-24 17:53:06', 3),
(360, 1, 20, 2, 0, 0, '0.000000', 1, '2.727273', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-04-08 10:35:56', '2016-04-08 10:35:56', 3),
(361, 1, 20, 2, 0, 0, '0.000000', 1, '9.090909', '2.000000', '2kg', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-05-03 17:08:33', '2016-05-03 17:25:29', 3),
(362, 1, 20, 2, 0, 0, '0.000000', 1, '0.000000', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 0, '404', 0, 1, '0000-00-00', 'new', 1, 0, 'both', 0, 0, '2016-05-25 13:15:31', '2016-05-25 13:15:31', 3);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_smart_blog_post`
--

DROP TABLE IF EXISTS `fcs_smart_blog_post`;
CREATE TABLE `fcs_smart_blog_post` (
  `id_smart_blog_post` int(11) NOT NULL,
  `id_author` int(11) DEFAULT NULL,
  `id_customer` int(11) UNSIGNED NOT NULL,
  `id_manufacturer` int(11) UNSIGNED NOT NULL,
  `is_private` int(11) UNSIGNED NOT NULL,
  `id_category` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  `available` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `viewed` int(11) DEFAULT NULL,
  `is_featured` int(11) DEFAULT NULL,
  `comment_status` int(11) DEFAULT NULL,
  `post_type` varchar(45) DEFAULT NULL,
  `image` varchar(245) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_smart_blog_post`
--

INSERT INTO `fcs_smart_blog_post` (`id_smart_blog_post`, `id_author`, `id_customer`, `id_manufacturer`, `is_private`, `id_category`, `position`, `active`, `available`, `created`, `modified`, `viewed`, `is_featured`, `comment_status`, `post_type`, `image`) VALUES
(2, 2, 0, 0, 0, 1, 0, 1, 1, '2014-12-18 10:37:26', '2015-03-16 12:41:46', 30, 1, 1, '0', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_smart_blog_post_lang`
--

DROP TABLE IF EXISTS `fcs_smart_blog_post_lang`;
CREATE TABLE `fcs_smart_blog_post_lang` (
  `id_smart_blog_post` int(11) NOT NULL,
  `id_lang` varchar(45) NOT NULL DEFAULT '',
  `meta_title` varchar(150) DEFAULT NULL,
  `meta_keyword` varchar(200) DEFAULT NULL,
  `meta_description` varchar(450) DEFAULT NULL,
  `short_description` varchar(450) DEFAULT NULL,
  `content` text,
  `link_rewrite` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_smart_blog_post_lang`
--

INSERT INTO `fcs_smart_blog_post_lang` (`id_smart_blog_post`, `id_lang`, `meta_title`, `meta_keyword`, `meta_description`, `short_description`, `content`, `link_rewrite`) VALUES
(2, '1', 'Demo Blog Artikel', '', '', 'Lorem ipsum dolor sit amet, consetetur sadipscing', '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>', 'Demo-Blog-Artikel');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_smart_blog_post_shop`
--

DROP TABLE IF EXISTS `fcs_smart_blog_post_shop`;
CREATE TABLE `fcs_smart_blog_post_shop` (
  `id_smart_blog_post_shop` int(11) NOT NULL,
  `id_smart_blog_post` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_smart_blog_post_shop`
--

INSERT INTO `fcs_smart_blog_post_shop` (`id_smart_blog_post_shop`, `id_smart_blog_post`, `id_shop`) VALUES
(26, 2, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_stock_available`
--

DROP TABLE IF EXISTS `fcs_stock_available`;
CREATE TABLE `fcs_stock_available` (
  `id_stock_available` int(11) UNSIGNED NOT NULL,
  `id_product` int(11) UNSIGNED NOT NULL,
  `id_product_attribute` int(11) UNSIGNED NOT NULL,
  `id_shop` int(11) UNSIGNED NOT NULL,
  `id_shop_group` int(11) UNSIGNED NOT NULL,
  `quantity` int(10) NOT NULL DEFAULT '0',
  `depends_on_stock` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `out_of_stock` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_stock_available`
--

INSERT INTO `fcs_stock_available` (`id_stock_available`, `id_product`, `id_product_attribute`, `id_shop`, `id_shop_group`, `quantity`, `depends_on_stock`, `out_of_stock`) VALUES
(54, 1, 0, 1, 0, 0, 0, 0),
(55, 2, 0, 1, 0, 0, 0, 0),
(56, 3, 0, 1, 0, 0, 0, 0),
(57, 4, 0, 1, 0, 0, 0, 0),
(58, 5, 0, 1, 0, 0, 0, 0),
(59, 6, 0, 1, 0, 0, 0, 0),
(60, 7, 0, 1, 0, 0, 0, 0),
(101, 9, 0, 1, 0, 0, 0, 0),
(102, 15, 0, 1, 0, 0, 0, 0),
(103, 16, 0, 1, 0, 0, 0, 0),
(104, 31, 0, 1, 0, 0, 0, 0),
(105, 32, 0, 1, 0, 0, 0, 0),
(106, 33, 0, 1, 0, 0, 0, 0),
(119, 47, 0, 1, 0, 100, 0, 2),
(121, 49, 0, 1, 0, 910, 0, 2),
(132, 60, 0, 1, 0, 2012, 0, 2),
(195, 102, 0, 1, 0, 2996, 0, 2),
(196, 103, 0, 1, 0, 990, 0, 2),
(252, 83, 0, 1, 0, 0, 0, 0),
(316, 161, 0, 1, 0, 996, 0, 2),
(318, 163, 0, 1, 0, 988, 0, 2),
(328, 173, 0, 1, 0, 931, 0, 2),
(393, 225, 0, 1, 0, 10, 0, 2),
(436, 238, 0, 1, 0, 0, 0, 0),
(466, 279, 0, 1, 0, 916, 0, 2),
(572, 338, 0, 1, 0, 15, 0, 2),
(615, 40, 0, 1, 0, 0, 0, 0),
(616, 43, 0, 1, 0, 0, 0, 0),
(617, 56, 0, 1, 0, 0, 0, 0),
(618, 57, 0, 1, 0, 0, 0, 0),
(619, 58, 0, 1, 0, 0, 0, 0),
(620, 59, 0, 1, 0, 0, 0, 0),
(621, 64, 0, 1, 0, 0, 0, 0),
(622, 65, 0, 1, 0, 0, 0, 0),
(623, 66, 0, 1, 0, 0, 0, 0),
(624, 67, 0, 1, 0, 0, 0, 0),
(625, 68, 0, 1, 0, 0, 0, 0),
(626, 75, 0, 1, 0, 0, 0, 0),
(627, 76, 0, 1, 0, 0, 0, 0),
(628, 81, 0, 1, 0, 0, 0, 0),
(629, 82, 0, 1, 0, 0, 0, 0),
(630, 84, 0, 1, 0, 0, 0, 0),
(631, 85, 0, 1, 0, 0, 0, 0),
(632, 86, 0, 1, 0, 0, 0, 0),
(633, 87, 0, 1, 0, 0, 0, 0),
(634, 88, 0, 1, 0, 0, 0, 0),
(635, 89, 0, 1, 0, 0, 0, 0),
(636, 112, 0, 1, 0, 0, 0, 0),
(637, 114, 0, 1, 0, 0, 0, 0),
(638, 115, 0, 1, 0, 0, 0, 0),
(639, 128, 0, 1, 0, 0, 0, 0),
(640, 140, 0, 1, 0, 0, 0, 0),
(641, 141, 0, 1, 0, 0, 0, 0),
(642, 142, 0, 1, 0, 0, 0, 0),
(643, 143, 0, 1, 0, 0, 0, 0),
(644, 144, 0, 1, 0, 0, 0, 0),
(645, 145, 0, 1, 0, 0, 0, 0),
(646, 146, 0, 1, 0, 0, 0, 0),
(647, 150, 0, 1, 0, 0, 0, 0),
(648, 154, 0, 1, 0, 0, 0, 0),
(649, 178, 0, 1, 0, 0, 0, 0),
(650, 218, 0, 1, 0, 0, 0, 0),
(651, 219, 0, 1, 0, 0, 0, 0),
(652, 220, 0, 1, 0, 0, 0, 0),
(653, 229, 0, 1, 0, 0, 0, 0),
(654, 230, 0, 1, 0, 0, 0, 0),
(655, 281, 0, 1, 0, 0, 0, 0),
(656, 297, 0, 1, 0, 0, 0, 0),
(657, 299, 0, 1, 0, 0, 0, 0),
(658, 300, 0, 1, 0, 0, 0, 0),
(659, 301, 0, 1, 0, 0, 0, 0),
(660, 302, 0, 1, 0, 0, 0, 0),
(661, 303, 0, 1, 0, 0, 0, 0),
(662, 304, 0, 1, 0, 0, 0, 0),
(663, 305, 0, 1, 0, 0, 0, 0),
(664, 306, 0, 1, 0, 0, 0, 0),
(665, 314, 0, 1, 0, 0, 0, 0),
(666, 315, 0, 1, 0, 0, 0, 0),
(667, 316, 0, 1, 0, 0, 0, 0),
(668, 317, 0, 1, 0, 0, 0, 0),
(669, 358, 0, 1, 0, 999, 0, 2),
(670, 359, 0, 1, 0, 999, 0, 2),
(671, 360, 0, 1, 0, 999, 0, 2),
(672, 361, 0, 1, 0, 999, 0, 2),
(673, 362, 0, 1, 0, 999, 0, 2),
(674, 339, 0, 1, 0, 2959, 0, 2),
(675, 339, 1, 1, 0, 964, 0, 2),
(676, 339, 2, 1, 0, 995, 0, 2),
(677, 339, 3, 1, 0, 1000, 0, 2),
(678, 340, 0, 1, 0, 991, 0, 2),
(679, 343, 0, 1, 0, 15, 0, 2),
(680, 344, 0, 1, 0, 78, 0, 2),
(682, 0, 0, 0, 0, 20, 0, 0),
(686, 346, 0, 1, 0, 96, 0, 2),
(687, 347, 0, 1, 0, 998, 0, 2),
(691, 350, 0, 1, 0, 999, 0, 2),
(692, 60, 9, 1, 0, 996, 0, 0),
(693, 60, 10, 1, 0, 17, 0, 0),
(694, 60, 11, 1, 0, 999, 0, 0),
(696, 352, 0, 1, 0, 999, 0, 2),
(697, 353, 0, 1, 0, 999, 0, 2),
(700, 354, 0, 1, 0, 999, 0, 2),
(701, 102, 14, 1, 0, 999, 0, 0),
(702, 102, 15, 1, 0, 998, 0, 0),
(703, 102, 16, 1, 0, 999, 0, 0),
(704, 355, 0, 1, 0, 999, 0, 2),
(705, 347, 17, 1, 0, 998, 0, 0),
(706, 356, 0, 1, 0, 999, 0, 2),
(707, 357, 0, 1, 0, 999, 0, 2),
(708, 361, 18, 1, 0, 999, 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_tax`
--

DROP TABLE IF EXISTS `fcs_tax`;
CREATE TABLE `fcs_tax` (
  `id_tax` int(10) UNSIGNED NOT NULL,
  `rate` decimal(10,3) NOT NULL,
  `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `fcs_tax`
--

INSERT INTO `fcs_tax` (`id_tax`, `rate`, `active`, `deleted`) VALUES
(1, '20.000', 1, 0),
(2, '10.000', 1, 0),
(3, '13.000', 1, 0);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `fcs_address`
--
ALTER TABLE `fcs_address`
  ADD PRIMARY KEY (`id_address`),
  ADD KEY `address_customer` (`id_customer`),
  ADD KEY `id_country` (`id_country`),
  ADD KEY `id_state` (`id_state`),
  ADD KEY `id_manufacturer` (`id_manufacturer`),
  ADD KEY `id_supplier` (`id_supplier`),
  ADD KEY `id_warehouse` (`id_warehouse`);

--
-- Indizes für die Tabelle `fcs_attribute`
--
ALTER TABLE `fcs_attribute`
  ADD PRIMARY KEY (`id_attribute`),
  ADD KEY `attribute_group` (`id_attribute_group`),
  ADD KEY `position` (`position`);

--
-- Indizes für die Tabelle `fcs_attribute_lang`
--
ALTER TABLE `fcs_attribute_lang`
  ADD PRIMARY KEY (`id_attribute`,`id_lang`),
  ADD KEY `id_lang` (`id_lang`,`name`);

--
-- Indizes für die Tabelle `fcs_cake_action_logs`
--
ALTER TABLE `fcs_cake_action_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `fcs_cake_carts`
--
ALTER TABLE `fcs_cake_carts`
  ADD PRIMARY KEY (`id_cart`);

--
-- Indizes für die Tabelle `fcs_cake_cart_products`
--
ALTER TABLE `fcs_cake_cart_products`
  ADD PRIMARY KEY (`id_cart_product`);

--
-- Indizes für die Tabelle `fcs_cake_deposits`
--
ALTER TABLE `fcs_cake_deposits`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `fcs_cake_invoices`
--
ALTER TABLE `fcs_cake_invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `fcs_cake_payments`
--
ALTER TABLE `fcs_cake_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `fcs_category`
--
ALTER TABLE `fcs_category`
  ADD PRIMARY KEY (`id_category`),
  ADD KEY `category_parent` (`id_parent`),
  ADD KEY `nleftrightactive` (`nleft`,`nright`,`active`),
  ADD KEY `level_depth` (`level_depth`),
  ADD KEY `nright` (`nright`),
  ADD KEY `activenleft` (`active`,`nleft`),
  ADD KEY `activenright` (`active`,`nright`);

--
-- Indizes für die Tabelle `fcs_category_lang`
--
ALTER TABLE `fcs_category_lang`
  ADD PRIMARY KEY (`id_category`,`id_shop`,`id_lang`),
  ADD KEY `category_name` (`name`),
  ADD KEY `id_lang` (`id_lang`),
  ADD KEY `id_shop` (`id_shop`);

--
-- Indizes für die Tabelle `fcs_category_product`
--
ALTER TABLE `fcs_category_product`
  ADD PRIMARY KEY (`id_category`,`id_product`),
  ADD KEY `id_product` (`id_product`);

--
-- Indizes für die Tabelle `fcs_cms`
--
ALTER TABLE `fcs_cms`
  ADD PRIMARY KEY (`id_cms`);

--
-- Indizes für die Tabelle `fcs_cms_lang`
--
ALTER TABLE `fcs_cms_lang`
  ADD PRIMARY KEY (`id_cms`,`id_shop`,`id_lang`);

--
-- Indizes für die Tabelle `fcs_configuration`
--
ALTER TABLE `fcs_configuration`
  ADD PRIMARY KEY (`id_configuration`),
  ADD KEY `name` (`name`),
  ADD KEY `id_shop` (`id_shop`),
  ADD KEY `id_shop_group` (`id_shop_group`);

--
-- Indizes für die Tabelle `fcs_customer`
--
ALTER TABLE `fcs_customer`
  ADD PRIMARY KEY (`id_customer`),
  ADD KEY `customer_email` (`email`),
  ADD KEY `customer_login` (`email`,`passwd`),
  ADD KEY `id_customer_passwd` (`id_customer`,`passwd`),
  ADD KEY `id_gender` (`id_gender`),
  ADD KEY `id_shop_group` (`id_shop_group`),
  ADD KEY `id_shop` (`id_shop`,`date_add`);

--
-- Indizes für die Tabelle `fcs_homeslider`
--
ALTER TABLE `fcs_homeslider`
  ADD PRIMARY KEY (`id_homeslider_slides`,`id_shop`);

--
-- Indizes für die Tabelle `fcs_homeslider_slides`
--
ALTER TABLE `fcs_homeslider_slides`
  ADD PRIMARY KEY (`id_homeslider_slides`);

--
-- Indizes für die Tabelle `fcs_homeslider_slides_lang`
--
ALTER TABLE `fcs_homeslider_slides_lang`
  ADD PRIMARY KEY (`id_homeslider_slides`,`id_lang`);

--
-- Indizes für die Tabelle `fcs_image`
--
ALTER TABLE `fcs_image`
  ADD PRIMARY KEY (`id_image`),
  ADD UNIQUE KEY `idx_product_image` (`id_image`,`id_product`,`cover`),
  ADD KEY `image_product` (`id_product`);

--
-- Indizes für die Tabelle `fcs_image_lang`
--
ALTER TABLE `fcs_image_lang`
  ADD PRIMARY KEY (`id_image`,`id_lang`),
  ADD KEY `id_image` (`id_image`);

--
-- Indizes für die Tabelle `fcs_image_shop`
--
ALTER TABLE `fcs_image_shop`
  ADD PRIMARY KEY (`id_image`,`id_shop`),
  ADD KEY `id_shop` (`id_shop`),
  ADD KEY `cover` (`cover`);

--
-- Indizes für die Tabelle `fcs_manufacturer`
--
ALTER TABLE `fcs_manufacturer`
  ADD PRIMARY KEY (`id_manufacturer`);

--
-- Indizes für die Tabelle `fcs_manufacturer_lang`
--
ALTER TABLE `fcs_manufacturer_lang`
  ADD PRIMARY KEY (`id_manufacturer`,`id_lang`);

--
-- Indizes für die Tabelle `fcs_orders`
--
ALTER TABLE `fcs_orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_customer` (`id_customer`),
  ADD KEY `id_cart` (`id_cart`),
  ADD KEY `invoice_number` (`invoice_number`),
  ADD KEY `id_carrier` (`id_carrier`),
  ADD KEY `id_lang` (`id_lang`),
  ADD KEY `id_currency` (`id_currency`),
  ADD KEY `id_address_delivery` (`id_address_delivery`),
  ADD KEY `id_address_invoice` (`id_address_invoice`),
  ADD KEY `id_shop_group` (`id_shop_group`),
  ADD KEY `id_shop` (`id_shop`),
  ADD KEY `date_add` (`date_add`),
  ADD KEY `current_state` (`current_state`),
  ADD KEY `reference` (`reference`);

--
-- Indizes für die Tabelle `fcs_order_detail`
--
ALTER TABLE `fcs_order_detail`
  ADD PRIMARY KEY (`id_order_detail`),
  ADD KEY `order_detail_order` (`id_order`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_attribute_id` (`product_attribute_id`),
  ADD KEY `id_order_id_order_detail` (`id_order`,`id_order_detail`),
  ADD KEY `id_tax_rules_group` (`id_tax_rules_group`);

--
-- Indizes für die Tabelle `fcs_order_detail_tax`
--
ALTER TABLE `fcs_order_detail_tax`
  ADD KEY `id_tax` (`id_tax`),
  ADD KEY `id_order_detail` (`id_order_detail`);

--
-- Indizes für die Tabelle `fcs_product`
--
ALTER TABLE `fcs_product`
  ADD PRIMARY KEY (`id_product`),
  ADD KEY `product_supplier` (`id_supplier`),
  ADD KEY `id_category_default` (`id_category_default`),
  ADD KEY `indexed` (`indexed`),
  ADD KEY `date_add` (`date_add`),
  ADD KEY `product_manufacturer` (`id_manufacturer`,`id_product`);

--
-- Indizes für die Tabelle `fcs_product_attribute`
--
ALTER TABLE `fcs_product_attribute`
  ADD PRIMARY KEY (`id_product_attribute`),
  ADD KEY `product_attribute_product` (`id_product`),
  ADD KEY `reference` (`reference`),
  ADD KEY `supplier_reference` (`supplier_reference`),
  ADD KEY `id_product_id_product_attribute` (`id_product_attribute`,`id_product`);

--
-- Indizes für die Tabelle `fcs_product_attribute_combination`
--
ALTER TABLE `fcs_product_attribute_combination`
  ADD PRIMARY KEY (`id_attribute`,`id_product_attribute`),
  ADD KEY `id_product_attribute` (`id_product_attribute`);

--
-- Indizes für die Tabelle `fcs_product_attribute_shop`
--
ALTER TABLE `fcs_product_attribute_shop`
  ADD PRIMARY KEY (`id_product_attribute`,`id_shop`),
  ADD KEY `id_shop` (`id_shop`);

--
-- Indizes für die Tabelle `fcs_product_lang`
--
ALTER TABLE `fcs_product_lang`
  ADD PRIMARY KEY (`id_product`,`id_shop`,`id_lang`),
  ADD KEY `id_lang` (`id_lang`),
  ADD KEY `name` (`name`),
  ADD KEY `id_shop` (`id_shop`);

--
-- Indizes für die Tabelle `fcs_product_shop`
--
ALTER TABLE `fcs_product_shop`
  ADD PRIMARY KEY (`id_product`,`id_shop`),
  ADD KEY `id_category_default` (`id_category_default`),
  ADD KEY `date_add` (`date_add`,`active`,`visibility`),
  ADD KEY `indexed` (`indexed`,`active`,`id_product`),
  ADD KEY `id_shop` (`id_shop`),
  ADD KEY `active` (`active`),
  ADD KEY `visibility` (`visibility`);

--
-- Indizes für die Tabelle `fcs_smart_blog_post`
--
ALTER TABLE `fcs_smart_blog_post`
  ADD PRIMARY KEY (`id_smart_blog_post`);

--
-- Indizes für die Tabelle `fcs_smart_blog_post_lang`
--
ALTER TABLE `fcs_smart_blog_post_lang`
  ADD PRIMARY KEY (`id_smart_blog_post`,`id_lang`);

--
-- Indizes für die Tabelle `fcs_smart_blog_post_shop`
--
ALTER TABLE `fcs_smart_blog_post_shop`
  ADD PRIMARY KEY (`id_smart_blog_post_shop`,`id_smart_blog_post`,`id_shop`);

--
-- Indizes für die Tabelle `fcs_stock_available`
--
ALTER TABLE `fcs_stock_available`
  ADD PRIMARY KEY (`id_stock_available`),
  ADD UNIQUE KEY `product_sqlstock` (`id_product`,`id_product_attribute`,`id_shop`,`id_shop_group`),
  ADD KEY `id_shop` (`id_shop`),
  ADD KEY `id_shop_group` (`id_shop_group`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_product_attribute` (`id_product_attribute`);

--
-- Indizes für die Tabelle `fcs_tax`
--
ALTER TABLE `fcs_tax`
  ADD PRIMARY KEY (`id_tax`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `fcs_address`
--
ALTER TABLE `fcs_address`
  MODIFY `id_address` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;
--
-- AUTO_INCREMENT für Tabelle `fcs_attribute`
--
ALTER TABLE `fcs_attribute`
  MODIFY `id_attribute` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;
--
-- AUTO_INCREMENT für Tabelle `fcs_cake_action_logs`
--
ALTER TABLE `fcs_cake_action_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;
--
-- AUTO_INCREMENT für Tabelle `fcs_cake_carts`
--
ALTER TABLE `fcs_cake_carts`
  MODIFY `id_cart` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT für Tabelle `fcs_cake_cart_products`
--
ALTER TABLE `fcs_cake_cart_products`
  MODIFY `id_cart_product` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT für Tabelle `fcs_cake_deposits`
--
ALTER TABLE `fcs_cake_deposits`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT für Tabelle `fcs_cake_invoices`
--
ALTER TABLE `fcs_cake_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_cake_payments`
--
ALTER TABLE `fcs_cake_payments`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_category`
--
ALTER TABLE `fcs_category`
  MODIFY `id_category` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT für Tabelle `fcs_cms`
--
ALTER TABLE `fcs_cms`
  MODIFY `id_cms` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT für Tabelle `fcs_configuration`
--
ALTER TABLE `fcs_configuration`
  MODIFY `id_configuration` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=554;
--
-- AUTO_INCREMENT für Tabelle `fcs_customer`
--
ALTER TABLE `fcs_customer`
  MODIFY `id_customer` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;
--
-- AUTO_INCREMENT für Tabelle `fcs_homeslider`
--
ALTER TABLE `fcs_homeslider`
  MODIFY `id_homeslider_slides` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT für Tabelle `fcs_homeslider_slides`
--
ALTER TABLE `fcs_homeslider_slides`
  MODIFY `id_homeslider_slides` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT für Tabelle `fcs_image`
--
ALTER TABLE `fcs_image`
  MODIFY `id_image` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;
--
-- AUTO_INCREMENT für Tabelle `fcs_manufacturer`
--
ALTER TABLE `fcs_manufacturer`
  MODIFY `id_manufacturer` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT für Tabelle `fcs_orders`
--
ALTER TABLE `fcs_orders`
  MODIFY `id_order` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `fcs_order_detail`
--
ALTER TABLE `fcs_order_detail`
  MODIFY `id_order_detail` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT für Tabelle `fcs_product`
--
ALTER TABLE `fcs_product`
  MODIFY `id_product` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=363;
--
-- AUTO_INCREMENT für Tabelle `fcs_product_attribute`
--
ALTER TABLE `fcs_product_attribute`
  MODIFY `id_product_attribute` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT für Tabelle `fcs_smart_blog_post`
--
ALTER TABLE `fcs_smart_blog_post`
  MODIFY `id_smart_blog_post` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `fcs_smart_blog_post_shop`
--
ALTER TABLE `fcs_smart_blog_post_shop`
  MODIFY `id_smart_blog_post_shop` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT für Tabelle `fcs_stock_available`
--
ALTER TABLE `fcs_stock_available`
  MODIFY `id_stock_available` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=709;
--
-- AUTO_INCREMENT für Tabelle `fcs_tax`
--
ALTER TABLE `fcs_tax`
  MODIFY `id_tax` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;