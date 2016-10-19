-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 19. Okt 2016 um 16:26
-- Server-Version: 10.1.13-MariaDB
-- PHP-Version: 7.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Datenbank: `foodcoopshop_clean`
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
(508, NULL, NULL, 1, 'FCS_FACEBOOK_URL', 'Facebook-Url für die Einbindung im Footer', 'https://www.facebook.com/FoodCoopShop-1600216136944038/', 'text', 90, '2015-07-08 13:23:54', '2015-07-08 13:23:54'),
(538, NULL, NULL, 1, 'FCS_REGISTRATION_EMAIL_TEXT', 'Zusätzlicher Text, der in der Bestätigungsmail nach einer Registrierung versendet wird.', '', 'textarea', 170, '2016-06-26 00:00:00', '2016-06-26 00:00:00'),
(543, NULL, NULL, 1, 'FCS_RIGHT_INFO_BOX_HTML', 'Inhalt der Box in der rechten Spalte unterhalb des Warenkorbes. <br /><div class="small">Um eine Zeile grün zu hinterlegen (Überschrift) bitte als "Überschrift 3" formatieren.<br />Die Variable {ABHOLTAG} zeigt automatisch das richtige Abholdatum an.</div>', '<h3>Abholzeiten</h3>\r\n\r\n<p>Wenn du deine Produkte jetzt bestellst, kannst du sie am <strong>{ABHOLTAG}</strong>&nbsp;zwischen 17 und 19 Uhr abholen.</p>\r\n\r\n<p>Du kannst jede Woche bis sp&auml;testens Dienstag Mitternacht bestellen und sie am darauffolgenden Freitag abholen.</p>\r\n', 'textarea', 150, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(544, NULL, NULL, 1, 'FCS_CART_ENABLED', 'Ist die Bestell-Funktion aktiviert?<br /><div class="small">Falls die Foodcoop mal Urlaub macht, kann das Bestellen hier deaktiviert werden.</div>', '1', 'boolean', 10, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(545, NULL, NULL, 1, 'FCS_ACCOUNTING_EMAIL', 'E-Mail-Adresse des Finanzverantwortlichen<br /><div class="small">Wer bekommt die Benachrichtigung über den erfolgten Rechnungsversand?</div>', '', 'text', 110, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(546, NULL, NULL, 1, 'FCS_AUTHENTICATION_INFO_TEXT', 'Info-Text beim Registrierungsformular<br /><div class="small">Beim Registrierungsformlar wird unterhalb der E-Mail-Adresse dieser Text angezeigt.</div>', 'Um bei uns zu bestellen musst du Vereinsmitglied sein.', 'textarea', 160, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(547, NULL, NULL, 1, 'FCS_SHOW_PRODUCTS_FOR_GUESTS', 'Produkte nur für Mitglieder sichtbar?', '1', 'boolean', 20, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(548, NULL, NULL, 1, 'FCS_DEFAULT_NEW_MEMBER_ACTIVE', 'Neue Mitglieder automatisch aktivieren?', '0', 'boolean', 50, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
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
(6, 1, '', '', '', '#', 'demo-slider.jpg');

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
  `bank_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `total_deposit` decimal(10,2) NOT NULL
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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fcs_product_attribute_combination`
--

DROP TABLE IF EXISTS `fcs_product_attribute_combination`;
CREATE TABLE `fcs_product_attribute_combination` (
  `id_attribute` int(10) UNSIGNED NOT NULL,
  `id_product_attribute` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  MODIFY `id_address` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_attribute`
--
ALTER TABLE `fcs_attribute`
  MODIFY `id_attribute` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
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
  MODIFY `id_cart_product` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_cake_deposits`
--
ALTER TABLE `fcs_cake_deposits`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
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
  MODIFY `id_category` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_cms`
--
ALTER TABLE `fcs_cms`
  MODIFY `id_cms` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_configuration`
--
ALTER TABLE `fcs_configuration`
  MODIFY `id_configuration` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=554;
--
-- AUTO_INCREMENT für Tabelle `fcs_customer`
--
ALTER TABLE `fcs_customer`
  MODIFY `id_customer` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_homeslider_slides`
--
ALTER TABLE `fcs_homeslider_slides`
  MODIFY `id_homeslider_slides` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT für Tabelle `fcs_image`
--
ALTER TABLE `fcs_image`
  MODIFY `id_image` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_manufacturer`
--
ALTER TABLE `fcs_manufacturer`
  MODIFY `id_manufacturer` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_orders`
--
ALTER TABLE `fcs_orders`
  MODIFY `id_order` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_order_detail`
--
ALTER TABLE `fcs_order_detail`
  MODIFY `id_order_detail` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_product`
--
ALTER TABLE `fcs_product`
  MODIFY `id_product` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_product_attribute`
--
ALTER TABLE `fcs_product_attribute`
  MODIFY `id_product_attribute` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_smart_blog_post`
--
ALTER TABLE `fcs_smart_blog_post`
  MODIFY `id_smart_blog_post` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_smart_blog_post_shop`
--
ALTER TABLE `fcs_smart_blog_post_shop`
  MODIFY `id_smart_blog_post_shop` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT für Tabelle `fcs_stock_available`
--
ALTER TABLE `fcs_stock_available`
  MODIFY `id_stock_available` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `fcs_tax`
--
ALTER TABLE `fcs_tax`
  MODIFY `id_tax` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;