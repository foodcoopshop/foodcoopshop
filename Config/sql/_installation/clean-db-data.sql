-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 20. Jun 2017 um 15:15
-- Server-Version: 10.1.13-MariaDB
-- PHP-Version: 7.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank: `foodcoopshop_clean`
--

--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_address`
--

TRUNCATE TABLE `fcs_address`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_attribute`
--

TRUNCATE TABLE `fcs_attribute`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_attribute_lang`
--

TRUNCATE TABLE `fcs_attribute_lang`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_cake_action_logs`
--

TRUNCATE TABLE `fcs_cake_action_logs`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_cake_carts`
--

TRUNCATE TABLE `fcs_cake_carts`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_cake_cart_products`
--

TRUNCATE TABLE `fcs_cake_cart_products`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_cake_deposits`
--

TRUNCATE TABLE `fcs_cake_deposits`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_cake_invoices`
--

TRUNCATE TABLE `fcs_cake_invoices`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_cake_payments`
--

TRUNCATE TABLE `fcs_cake_payments`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_category`
--

TRUNCATE TABLE `fcs_category`;
--
-- Daten für Tabelle `fcs_category`
--

INSERT INTO `fcs_category` (`id_category`, `id_parent`, `id_shop_default`, `level_depth`, `nleft`, `nright`, `active`, `date_add`, `date_upd`, `position`, `is_root_category`) VALUES
(1, 0, 1, 0, 1, 30, 1, '2016-10-19 21:05:00', '2016-10-19 21:05:00', 0, 0),
(2, 1, 1, 1, 2, 29, 1, '2016-10-19 21:05:00', '2016-10-19 21:05:00', 0, 1),
(20, 2, 1, 2, 3, 4, 1, '2016-10-19 21:05:00', '2016-10-19 21:05:00', 1, 0);

--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_category_lang`
--

TRUNCATE TABLE `fcs_category_lang`;
--
-- Daten für Tabelle `fcs_category_lang`
--

INSERT INTO `fcs_category_lang` (`id_category`, `id_shop`, `id_lang`, `name`, `description`, `link_rewrite`, `meta_title`, `meta_keywords`, `meta_description`) VALUES
(1, 1, 1, 'Root', '', 'root', '', '', ''),
(2, 1, 1, 'Produkte', '', 'home', '', '', ''),
(20, 1, 1, 'Alle Produkte', '', 'alle-produkte', '', '', '');

--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_category_product`
--

TRUNCATE TABLE `fcs_category_product`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_cms`
--

TRUNCATE TABLE `fcs_cms`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_cms_lang`
--

TRUNCATE TABLE `fcs_cms_lang`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_configuration`
--

TRUNCATE TABLE `fcs_configuration`;
--
-- Daten für Tabelle `fcs_configuration`
--

INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES
(11, NULL, NULL, 1, 'FCS_PRODUCT_AVAILABILITY_LOW', 'Geringe Verfügbarkeit<br /><div class=\"small\">Ab welcher verfügbaren Produkt-Anzahl soll beim Bestellen der Hinweis \"(x verfügbar\") angezeigt werden?</div>', '10', 'number', 60, '0000-00-00 00:00:00', '2014-06-01 01:40:34'),
(31, NULL, NULL, 1, 'FCS_DAYS_SHOW_PRODUCT_AS_NEW', 'Wie viele Tage sollen Produkte \"als neu markiert\" bleiben?', '7', 'number', 70, '0000-00-00 00:00:00', '2014-05-14 21:15:45'),
(164, NULL, NULL, 1, 'FCS_CUSTOMER_GROUP', 'Welcher Gruppe sollen neu registrierte Mitglieder zugewiesen werden?', '3', 'dropdown', 40, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(456, NULL, NULL, 1, 'FCS_FOOTER_CMS_TEXT', 'Zusätzlicher Text für den Footer', NULL, 'textarea', 80, '2014-06-11 17:50:55', '2016-07-01 21:47:47'),
(508, NULL, NULL, 1, 'FCS_FACEBOOK_URL', 'Facebook-Url für die Einbindung im Footer', 'https://www.facebook.com/FoodCoopShop/', 'text', 90, '2015-07-08 13:23:54', '2015-07-08 13:23:54'),
(538, NULL, NULL, 1, 'FCS_REGISTRATION_EMAIL_TEXT', 'Zusätzlicher Text, der in der Bestätigungsmail nach einer Registrierung versendet wird. <br /> <a href=\"/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT\" target=\"_blank\"><img src=\"/js/vendor/famfamfam-silk/dist/png/information.png?1483041252\" alt=\"\"> E-Mail-Vorschau anzeigen</a>', '', 'textarea', 170, '2016-06-26 00:00:00', '2016-06-26 00:00:00'),
(543, NULL, NULL, 1, 'FCS_RIGHT_INFO_BOX_HTML', 'Inhalt der Box in der rechten Spalte unterhalb des Warenkorbes. <br /><div class=\"small\">Um eine Zeile grün zu hinterlegen (Überschrift) bitte als \"Überschrift 3\" formatieren.<br />Die Variable {ABHOLTAG} zeigt automatisch das richtige Abholdatum an.</div>', '<h3>Abholzeiten</h3>\r\n\r\n<p>Wenn du deine Produkte jetzt bestellst, kannst du sie am <strong>{ABHOLTAG}</strong>&nbsp;zwischen 17 und 19 Uhr abholen.</p>\r\n\r\n<p>Du kannst jede Woche bis sp&auml;testens Dienstag Mitternacht bestellen und sie am darauffolgenden Freitag abholen.</p>\r\n', 'textarea', 150, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(544, NULL, NULL, 1, 'FCS_CART_ENABLED', 'Ist die Bestell-Funktion aktiviert?<br /><div class=\"small\">Falls die Foodcoop mal Urlaub macht, kann das Bestellen hier deaktiviert werden.</div>', '1', 'boolean', 10, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(545, NULL, NULL, 1, 'FCS_ACCOUNTING_EMAIL', 'E-Mail-Adresse des Finanzverantwortlichen<br /><div class=\"small\">Wer bekommt die Benachrichtigung über den erfolgten Rechnungsversand?</div>', '', 'text', 110, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(546, NULL, NULL, 1, 'FCS_AUTHENTICATION_INFO_TEXT', 'Info-Text beim Registrierungsformular<br /><div class=\"small\">Beim Registrierungsformlar wird unterhalb der E-Mail-Adresse dieser Text angezeigt.</div>', 'Um bei uns zu bestellen musst du Vereinsmitglied sein.', 'textarea', 160, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(547, NULL, NULL, 1, 'FCS_SHOW_PRODUCTS_FOR_GUESTS', 'Produkte für nicht eingeloggte Mitglieder sichtbar?', '0', 'boolean', 20, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(548, NULL, NULL, 1, 'FCS_DEFAULT_NEW_MEMBER_ACTIVE', 'Neue Mitglieder automatisch aktivieren?', '0', 'boolean', 50, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(549, NULL, NULL, 1, 'FCS_MINIMAL_CREDIT_BALANCE', 'Höhe des Bestell-Limits, ab dem den Mitgliedern kein Bestellen mehr möglich ist.<br /><div class=\"small\">Z.B.: \"100\" für 100 € im Minus. 0 bedeutet \"kein Bestell-Limit\".</div>', '50', 'number', 125, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(550, NULL, NULL, 1, 'FCS_BANK_ACCOUNT_DATA', 'Bankverbindung für die Guthaben-Einzahlungen\".', 'Guthaben-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878', 'text', 130, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(551, NULL, NULL, 1, 'FCS_MEMBER_FEE_BANK_ACCOUNT_DATA', 'Bankverbindung für die Mitgliedsbeitrags-Einzahlungen\".', 'MB-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878', 'text', 140, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(552, NULL, NULL, 1, 'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS', 'Zusätzliche Liefer-Informationen für die Hersteller<br /><div class=\"small\">wird in den Bestell-Listen nach dem Lieferdatum angezeigt.</div>', ', 15:00 bis 17:00 Uhr', 'text', 120, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(553, NULL, NULL, 1, 'FCS_ORDER_CONFIRMATION_MAIL_BCC', 'E-Mail-Adresse, an die die Bestell-Bestätigungen als BCC geschickt werden.<br /><div class=\"small\">Kann leer gelassen werden.</div>', '', 'text', 300, '2016-10-06 00:00:00', '2016-10-06 00:00:00'),
(554, NULL, NULL, 1, 'FCS_SHOW_FOODCOOPSHOP_BACKLINK', 'Link auf www.foodcoopshop.com anzeigen?<br /><div class=\"small\">Der Link wird im Footer und in den generierten PDFs (Bestelllisten, Rechnungen) angezeigt.</div>', '1', 'boolean', 180, '2016-11-27 00:00:00', '2016-11-27 00:00:00'),
(555, NULL, NULL, 1, 'FCS_PAYMENT_PRODUCT_MAXIMUM', 'Maximalbetrag für jede Guthaben-Aufladung in Euro', '500', 'number', 127, '2016-11-28 00:00:00', '2016-11-28 00:00:00'),
(556, NULL, NULL, 1, 'FCS_APP_NAME', 'Name der Foodcoop', '', 'text', 5, '2017-01-12 00:00:00', '2017-01-12 00:00:00'),
(557, NULL, NULL, 1, 'FCS_APP_ADDRESS', 'Adresse der Foodcoop<br /><div class=\"small\">Wird im Footer von Homepage und E-Mails, Datenschutzerklärung, Nutzungsbedingungen usw. verwendet.</div>', '', 'textarea', 6, '2017-01-12 00:00:00', '2017-01-12 00:00:00'),
(558, NULL, NULL, 1, 'FCS_APP_EMAIL', 'E-Mail-Adresse der Foodcoop<br /><div class=\"small\"></div>', '', 'text', 7, '2017-01-12 00:00:00', '2017-01-12 00:00:00'),
(559, NULL, NULL, 1, 'FCS_PLATFORM_OWNER', 'Betreiber der Plattform<br /><div class=\"small\">Für Datenschutzerklärung und Nutzungsbedingungen, bitte auch Adresse angeben. Kann leer gelassen werden, wenn die Foodcoop selbst die Plattform betreibt.</div>', '', 'textarea', 8, '2017-01-12 00:00:00', '2017-01-12 00:00:00'),
(560, NULL, NULL, 1, 'FCS_SHOP_ORDER_DEFAULT_STATE', 'Bestellstatus für Sofort-Bestellungen', '1', 'dropdown', 75, '2017-01-12 00:00:00', '2017-01-12 00:00:00'),
(561, NULL, NULL, 1, 'FCS_DB_VERSION', 'Version der Datenbank-Struktur', '3', 'readonly', 0, '2017-03-13 00:00:00', '2017-03-13 00:00:00'),
(562, NULL, NULL, 0, 'FCS_DB_UPDATE', 'Version des letzten versuchten Datenbank-Updates', '3', 'readonly', 0, '2017-03-13 00:00:00', '2017-03-13 00:00:00');

--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_customer`
--

TRUNCATE TABLE `fcs_customer`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_homeslider_slides`
--

TRUNCATE TABLE `fcs_homeslider_slides`;
--
-- Daten für Tabelle `fcs_homeslider_slides`
--

INSERT INTO `fcs_homeslider_slides` (`id_homeslider_slides`, `position`, `active`) VALUES
(6, 0, 1);

--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_homeslider_slides_lang`
--

TRUNCATE TABLE `fcs_homeslider_slides_lang`;
--
-- Daten für Tabelle `fcs_homeslider_slides_lang`
--

INSERT INTO `fcs_homeslider_slides_lang` (`id_homeslider_slides`, `id_lang`, `title`, `description`, `legend`, `url`, `image`) VALUES
(6, 1, '', '', '', '', 'demo-slider.jpg');

--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_image`
--

TRUNCATE TABLE `fcs_image`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_image_lang`
--

TRUNCATE TABLE `fcs_image_lang`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_image_shop`
--

TRUNCATE TABLE `fcs_image_shop`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_manufacturer`
--

TRUNCATE TABLE `fcs_manufacturer`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_manufacturer_lang`
--

TRUNCATE TABLE `fcs_manufacturer_lang`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_orders`
--

TRUNCATE TABLE `fcs_orders`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_order_detail`
--

TRUNCATE TABLE `fcs_order_detail`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_order_detail_tax`
--

TRUNCATE TABLE `fcs_order_detail_tax`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_product`
--

TRUNCATE TABLE `fcs_product`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_product_attribute`
--

TRUNCATE TABLE `fcs_product_attribute`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_product_attribute_combination`
--

TRUNCATE TABLE `fcs_product_attribute_combination`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_product_attribute_shop`
--

TRUNCATE TABLE `fcs_product_attribute_shop`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_product_lang`
--

TRUNCATE TABLE `fcs_product_lang`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_product_shop`
--

TRUNCATE TABLE `fcs_product_shop`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_smart_blog_post`
--

TRUNCATE TABLE `fcs_smart_blog_post`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_smart_blog_post_lang`
--

TRUNCATE TABLE `fcs_smart_blog_post_lang`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_smart_blog_post_shop`
--

TRUNCATE TABLE `fcs_smart_blog_post_shop`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_stock_available`
--

TRUNCATE TABLE `fcs_stock_available`;
--
-- TRUNCATE Tabelle vor dem Einfügen `fcs_tax`
--

TRUNCATE TABLE `fcs_tax`;
--
-- Daten für Tabelle `fcs_tax`
--

INSERT INTO `fcs_tax` (`id_tax`, `rate`, `active`, `deleted`) VALUES
(1, '20.000', 1, 0),
(2, '10.000', 1, 0),
(3, '13.000', 1, 0);
COMMIT;
