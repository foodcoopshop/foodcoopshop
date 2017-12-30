
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Truncate tables before insertion
TRUNCATE TABLE `fcs_action_logs`;
TRUNCATE TABLE `fcs_address`;
TRUNCATE TABLE `fcs_attribute`;
TRUNCATE TABLE `fcs_cart_products`;
TRUNCATE TABLE `fcs_carts`;
TRUNCATE TABLE `fcs_category`;
TRUNCATE TABLE `fcs_category_product`;
TRUNCATE TABLE `fcs_configuration`;
TRUNCATE TABLE `fcs_customer`;
TRUNCATE TABLE `fcs_deposits`;
TRUNCATE TABLE `fcs_email_logs`;
TRUNCATE TABLE `fcs_image`;
TRUNCATE TABLE `fcs_image_lang`;
TRUNCATE TABLE `fcs_image_shop`;
TRUNCATE TABLE `fcs_invoices`;
TRUNCATE TABLE `fcs_manufacturer`;
TRUNCATE TABLE `fcs_manufacturer_lang`;
TRUNCATE TABLE `fcs_order_detail`;
TRUNCATE TABLE `fcs_order_detail_tax`;
TRUNCATE TABLE `fcs_orders`;
TRUNCATE TABLE `fcs_pages`;
TRUNCATE TABLE `fcs_payments`;
TRUNCATE TABLE `fcs_product`;
TRUNCATE TABLE `fcs_product_attribute`;
TRUNCATE TABLE `fcs_product_attribute_combination`;
TRUNCATE TABLE `fcs_product_attribute_shop`;
TRUNCATE TABLE `fcs_product_lang`;
TRUNCATE TABLE `fcs_product_shop`;
TRUNCATE TABLE `fcs_sliders`;
TRUNCATE TABLE `fcs_smart_blog_post`;
TRUNCATE TABLE `fcs_smart_blog_post_lang`;
TRUNCATE TABLE `fcs_smart_blog_post_shop`;
TRUNCATE TABLE `fcs_stock_available`;
TRUNCATE TABLE `fcs_sync_domains`;
TRUNCATE TABLE `fcs_sync_products`;
TRUNCATE TABLE `fcs_tax`;

/*!40000 ALTER TABLE `fcs_action_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_action_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_address` DISABLE KEYS */;
INSERT INTO `fcs_address` VALUES
(153,87,0,'Mitglied','Demo','Demostrasse 4','','4644','Scharnstein','','','0664/000000000','foodcoopshop-demo-mitglied@mailinator.com','2014-12-02 12:19:31','2014-12-02 12:19:31'),
(154,88,0,'Admin','Demo','Demostrasse 4','','4644','Scharnstein','test','','0600/000000','fcs-demo-admin@mailinator.com','2014-12-02 12:28:44','2014-12-02 12:28:44'),
(173,0,4,'Fleisch-Hersteller','Demo','Demostrasse 4','','4644','Scharnstein','','','','fcs-demo-fleisch-hersteller@mailinator.com','2014-05-27 22:20:18','2015-04-07 16:18:28'),
(177,0,15,'Milch-Hersteller','Demo','Demostrasse 4','','4644','Scharnstein','','','','fcs-demo-milch-hersteller@mailinator.com','2014-06-04 21:46:38','2015-10-16 10:06:52'),
(180,0,5,'Gemüse-Hersteller','Demo','Demostrasse 4','','4644','Scharnstein','','','','fcs-demo-gemuese-hersteller@mailinator.com','2014-05-14 21:20:05','2015-12-30 00:54:35'),
(181,0,16,'Hersteller ohne Customer-Eintrag','Demo','Demostrasse 4','','4644','Scharnstein','','','','fcs-hersteller-ohne-customer-eintrag@mailinator.com','2014-05-14 21:20:05','2015-12-30 00:54:35'),
(182,92,0,'Superadmin','Demo','Demostrasse 4','','4644','Demostadt',NULL,'','0600/000000','fcs-demo-superadmin@mailinator.com','2017-07-26 13:19:19','2017-07-26 13:19:19');
/*!40000 ALTER TABLE `fcs_address` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_attribute` DISABLE KEYS */;
INSERT INTO `fcs_attribute` VALUES
(27,'3 kg',1,NULL,NULL),
(28,'5 kg',1,NULL,NULL),
(29,'1 L',1,NULL,NULL),
(30,'5 L',1,NULL,NULL),
(31,'0,10l',1,NULL,NULL),
(32,'0,25l',1,NULL,NULL),
(33,'0,5l',1,NULL,NULL),
(34,'0,75l',1,NULL,NULL),
(35,'1 kg',1,NULL,NULL),
(36,'0,5 kg',1,NULL,NULL),
(38,'Beutel 15g',1,NULL,NULL),
(39,'Beutel 30g',1,NULL,NULL),
(42,'Beutel 45g',1,NULL,NULL),
(43,'Beutel 50g',1,NULL,NULL),
(44,'Beutel 100g',1,NULL,NULL),
(45,'Beutel 250g',1,NULL,NULL),
(46,'Beutel 25g',1,NULL,NULL),
(47,'Beutel 500g',1,NULL,NULL),
(48,'Beutel 1kg',1,NULL,NULL),
(49,'Beutel 5kg',1,NULL,NULL),
(50,'Glas 15g',1,NULL,NULL),
(51,'Glas 25g',1,NULL,NULL),
(52,'Glas 30g',1,NULL,NULL),
(53,'Glas 50g',1,NULL,NULL),
(54,'Glas 100g',1,NULL,NULL),
(55,'10 kg',1,NULL,NULL),
(56,'25 kg',1,NULL,NULL),
(57,'Glas 45g',1,NULL,NULL),
(58,'Beutel 40g',1,NULL,NULL),
(59,'1,0l',1,NULL,NULL),
(60,'S (klein)',1,NULL,NULL),
(61,'S (mittel)',1,NULL,NULL),
(62,'S (groß)',1,NULL,NULL),
(63,'M',1,NULL,NULL),
(64,'L',1,NULL,NULL),
(65,'XL',1,NULL,NULL),
(66,'klein',1,NULL,NULL),
(67,'mittel',1,NULL,NULL),
(68,'groß',1,NULL,NULL),
(69,'1L Flasche',1,NULL,NULL),
(70,'2L Flasche ',1,NULL,NULL),
(71,'5L Bag-in-Box',1,NULL,NULL),
(72,'6x1L Flaschen in Kiste',1,NULL,NULL),
(73,'200 g ',1,NULL,NULL),
(74,'100 g',1,NULL,NULL),
(75,'0,25kg',1,NULL,NULL),
(76,'0,5kg',1,NULL,NULL),
(77,'1,5 kg',1,NULL,NULL),
(78,'0,75 kg',1,NULL,NULL),
(79,'3L Bag-in-Box',1,NULL,NULL),
(80,'25 min',1,NULL,NULL),
(81,'50 min',1,NULL,NULL),
(82,'1 Stück',1,NULL,NULL),
(83,'2 Stück',1,NULL,NULL),
(84,'3 Stück',1,NULL,NULL),
(85,'4 Stück',1,NULL,NULL),
(86,'5 Stück',1,NULL,NULL);
/*!40000 ALTER TABLE `fcs_attribute` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cart_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cart_products` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_carts` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category` DISABLE KEYS */;
INSERT INTO `fcs_category` VALUES
(1,0,'Root','',0,1,32,1,'2014-05-05 21:05:09','2014-05-05 21:05:09'),
(2,1,'Produkte','',1,2,31,1,'2014-05-05 21:05:09','2014-05-05 21:05:09'),
(13,2,'Obst und Gemüse','',2,5,6,1,'2014-05-14 12:23:25','2014-05-14 12:23:25'),
(14,2,'Sonstiges','',2,7,8,0,'2014-05-14 12:23:39','2014-12-02 12:52:03'),
(15,2,'Getreideprodukte und Hülsenfrüchte','',2,9,10,1,'2014-05-14 21:38:45','2015-02-26 13:56:19'),
(16,2,'Fleischprodukte','',2,11,12,1,'2014-05-14 21:40:51','2014-05-14 21:48:48'),
(17,2,'Milchprodukte','',2,13,14,1,'2014-05-14 21:43:00','2014-05-14 21:51:12'),
(18,2,'Getränke','',2,15,16,0,'2014-05-14 21:48:15','2014-12-02 12:52:03'),
(19,2,'Öle','',2,17,18,0,'2014-05-14 21:52:41','2014-12-02 12:52:03'),
(20,2,'Alle Produkte','',2,3,4,1,'2014-05-14 21:53:52','2014-05-17 13:14:22'),
(21,2,'Brot und Gebäck','',2,19,20,0,'2014-05-14 21:54:38','2014-12-02 12:52:04'),
(22,2,'Gewürze und Saaten','',2,21,22,0,'2014-05-14 21:56:28','2014-12-02 12:52:04'),
(23,2,'Honigprodukte','',2,23,24,0,'2014-06-29 13:16:24','2014-12-02 12:52:04'),
(24,2,'Eier','',2,25,26,0,'2014-07-11 23:10:22','2014-12-02 12:52:04'),
(25,2,'Schnäpse','',2,27,28,1,'2014-09-11 18:22:25','2015-08-08 10:34:22'),
(26,2,'Hygieneartikel','',2,29,30,0,'2014-11-08 12:05:53','2014-12-02 12:52:05');
/*!40000 ALTER TABLE `fcs_category` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category_product` DISABLE KEYS */;
INSERT INTO `fcs_category_product` VALUES
(2,75),
(2,76),
(2,77),
(2,78),
(2,234),
(2,241),
(2,242),
(2,248),
(2,249),
(2,253),
(2,256),
(2,259),
(2,260),
(2,261),
(2,262),
(2,263),
(2,264),
(2,265),
(2,266),
(2,267),
(2,268),
(2,269),
(2,270),
(2,273),
(2,275),
(2,277),
(2,278),
(2,279),
(2,280),
(2,282),
(2,283),
(2,284),
(2,285),
(2,286),
(2,307),
(2,308),
(2,309),
(2,310),
(2,311),
(2,312),
(2,313),
(2,318),
(2,319),
(2,331),
(2,332),
(2,333),
(2,334),
(2,335),
(2,336),
(2,337),
(2,338),
(2,339),
(2,341),
(2,342),
(2,348),
(13,109),
(13,110),
(13,144),
(13,153),
(13,155),
(13,158),
(13,160),
(13,161),
(13,162),
(13,163),
(13,166),
(13,167),
(13,173),
(13,174),
(13,224),
(13,225),
(13,228),
(13,233),
(13,234),
(13,250),
(13,253),
(13,256),
(13,273),
(13,279),
(13,280),
(13,290),
(13,291),
(13,293),
(13,296),
(13,297),
(13,298),
(13,299),
(13,318),
(13,319),
(13,343),
(13,344),
(13,346),
(14,189),
(14,190),
(14,191),
(14,192),
(14,193),
(14,261),
(14,262),
(14,263),
(14,264),
(14,265),
(14,266),
(14,267),
(14,268),
(14,285),
(14,286),
(14,307),
(14,308),
(14,309),
(14,310),
(14,311),
(14,312),
(15,113),
(15,116),
(15,117),
(15,118),
(15,148),
(15,239),
(15,241),
(15,242),
(15,248),
(15,249),
(15,339),
(16,36),
(16,80),
(16,91),
(16,93),
(16,94),
(16,95),
(16,98),
(16,102),
(16,103),
(16,106),
(16,336),
(16,337),
(16,338),
(16,340),
(16,347),
(16,348),
(16,354),
(16,361),
(17,47),
(17,49),
(17,51),
(17,52),
(17,58),
(17,59),
(17,60),
(17,257),
(18,185),
(18,186),
(18,187),
(18,188),
(18,218),
(18,219),
(18,220),
(18,259),
(18,260),
(18,300),
(18,313),
(19,150),
(20,1),
(20,2),
(20,3),
(20,4),
(20,5),
(20,6),
(20,7),
(20,8),
(20,36),
(20,47),
(20,49),
(20,51),
(20,52),
(20,58),
(20,59),
(20,60),
(20,74),
(20,75),
(20,76),
(20,77),
(20,78),
(20,80),
(20,81),
(20,82),
(20,84),
(20,86),
(20,87),
(20,88),
(20,91),
(20,93),
(20,94),
(20,95),
(20,98),
(20,102),
(20,103),
(20,106),
(20,109),
(20,110),
(20,113),
(20,116),
(20,117),
(20,118),
(20,128),
(20,130),
(20,132),
(20,136),
(20,137),
(20,139),
(20,143),
(20,144),
(20,148),
(20,150),
(20,153),
(20,155),
(20,158),
(20,160),
(20,161),
(20,162),
(20,163),
(20,166),
(20,167),
(20,173),
(20,174),
(20,185),
(20,186),
(20,187),
(20,188),
(20,189),
(20,190),
(20,191),
(20,192),
(20,193),
(20,218),
(20,219),
(20,220),
(20,224),
(20,225),
(20,228),
(20,233),
(20,234),
(20,239),
(20,241),
(20,242),
(20,248),
(20,249),
(20,250),
(20,253),
(20,256),
(20,257),
(20,259),
(20,260),
(20,261),
(20,262),
(20,263),
(20,264),
(20,265),
(20,266),
(20,267),
(20,268),
(20,269),
(20,270),
(20,273),
(20,275),
(20,277),
(20,278),
(20,279),
(20,280),
(20,282),
(20,283),
(20,284),
(20,285),
(20,286),
(20,290),
(20,291),
(20,293),
(20,296),
(20,297),
(20,298),
(20,299),
(20,307),
(20,308),
(20,309),
(20,310),
(20,311),
(20,312),
(20,313),
(20,318),
(20,319),
(20,331),
(20,332),
(20,333),
(20,334),
(20,336),
(20,337),
(20,338),
(20,339),
(20,340),
(20,341),
(20,342),
(20,343),
(20,344),
(20,345),
(20,346),
(20,347),
(20,348),
(20,350),
(20,352),
(20,353),
(20,354),
(20,355),
(20,356),
(20,357),
(20,358),
(20,359),
(20,360),
(20,361),
(20,362),
(21,130),
(21,132),
(21,136),
(21,137),
(21,139),
(21,269),
(21,270),
(21,341),
(21,342),
(22,74),
(22,81),
(22,82),
(22,84),
(22,86),
(22,87),
(22,88),
(22,128),
(23,75),
(23,76),
(23,77),
(23,78),
(23,312),
(24,143),
(25,275),
(25,277),
(25,278),
(26,282),
(26,283),
(26,284),
(26,331),
(26,332),
(26,333),
(26,334);
/*!40000 ALTER TABLE `fcs_category_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_configuration` DISABLE KEYS */;
INSERT INTO `fcs_configuration` VALUES
(11,1,'FCS_PRODUCT_AVAILABILITY_LOW','Geringe Verfügbarkeit<br /><div class=\"small\">Ab welcher verfügbaren Produkt-Anzahl soll beim Bestellen der Hinweis \"(x verfügbar\") angezeigt werden?</div>','10','number',60,'2017-07-26 13:19:19','2014-06-01 01:40:34'),
(31,1,'FCS_DAYS_SHOW_PRODUCT_AS_NEW','Wie viele Tage sollen Produkte \"als neu markiert\" bleiben?','7','number',70,'2017-07-26 13:19:19','2014-05-14 21:15:45'),
(164,1,'FCS_CUSTOMER_GROUP','Welcher Gruppe sollen neu registrierte Mitglieder zugewiesen werden?','3','dropdown',40,'2017-07-26 13:19:19','2017-07-26 13:19:19'),
(456,1,'FCS_FOOTER_CMS_TEXT','Zusätzlicher Text für den Footer',NULL,'textarea_big',80,'2014-06-11 17:50:55','2016-07-01 21:47:47'),
(508,1,'FCS_FACEBOOK_URL','Facebook-Url für die Einbindung im Footer','https://www.facebook.com/FoodCoopShop/','text',90,'2015-07-08 13:23:54','2015-07-08 13:23:54'),
(538,1,'FCS_REGISTRATION_EMAIL_TEXT','Zusätzlicher Text, der in der Bestätigungsmail nach einer Registrierung versendet wird. <br /> <a href=\"/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT\" target=\"_blank\"><img src=\"/js/vendor/famfamfam-silk/dist/png/information.png?1483041252\" alt=\"\"> E-Mail-Vorschau anzeigen</a>','','textarea_big',170,'2016-06-26 00:00:00','2016-06-26 00:00:00'),
(543,1,'FCS_RIGHT_INFO_BOX_HTML','Inhalt der Box in der rechten Spalte unterhalb des Warenkorbes. <br /><div class=\"small\">Um eine Zeile grün zu hinterlegen (Überschrift) bitte als \"Überschrift 3\" formatieren.<br />Die Variable {ABHOLTAG} zeigt automatisch das richtige Abholdatum an.</div>','<h3>Abholzeiten</h3>\r\n\r\n<p>Wenn du deine Produkte jetzt bestellst, kannst du sie am <strong>{ABHOLTAG}</strong>&nbsp;zwischen 17 und 19 Uhr abholen.</p>\r\n\r\n<p>Du kannst jede Woche bis sp&auml;testens Dienstag Mitternacht bestellen und sie am darauffolgenden Freitag abholen.</p>\r\n','textarea_big',150,'2017-07-26 13:19:19','2017-07-26 13:19:19'),
(544,1,'FCS_CART_ENABLED','Ist die Bestell-Funktion aktiviert?<br /><div class=\"small\">Falls die Foodcoop mal Urlaub macht, kann das Bestellen hier deaktiviert werden.</div>','1','boolean',10,'2017-07-26 13:19:19','2017-07-26 13:19:19'),
(545,1,'FCS_ACCOUNTING_EMAIL','E-Mail-Adresse des Finanzverantwortlichen<br /><div class=\"small\">Wer bekommt die Benachrichtigung über den erfolgten Rechnungsversand?</div>','','text',110,'2017-07-26 13:19:19','2017-07-26 13:19:19'),
(546,1,'FCS_AUTHENTICATION_INFO_TEXT','Info-Text beim Registrierungsformular<br /><div class=\"small\">Beim Registrierungsformlar wird unterhalb der E-Mail-Adresse dieser Text angezeigt.</div>','Um bei uns zu bestellen musst du Vereinsmitglied sein.','textarea',160,'2017-07-26 13:19:19','2017-07-26 13:19:19'),
(547,1,'FCS_SHOW_PRODUCTS_FOR_GUESTS','Produkte für nicht eingeloggte Mitglieder sichtbar?<br /><div class=\"small\">Die Preise werden nicht angezeigt.</div>','0','boolean',20,'2017-07-26 13:19:19','2017-07-26 13:19:19'),
(548,1,'FCS_DEFAULT_NEW_MEMBER_ACTIVE','Neue Mitglieder automatisch aktivieren?','0','boolean',50,'2017-07-26 13:19:19','2017-07-26 13:19:19'),
(549,1,'FCS_MINIMAL_CREDIT_BALANCE','Höhe des Bestell-Limits, ab dem den Mitgliedern kein Bestellen mehr möglich ist.<br /><div class=\"small\">Z.B.: \"100\" für 100 € im Minus. 0 bedeutet \"kein Bestell-Limit\".</div>','100','number',125,'2017-07-26 13:19:19','2017-07-26 13:19:19'),
(550,1,'FCS_BANK_ACCOUNT_DATA','Bankverbindung für die Guthaben-Einzahlungen\".','Guthaben-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',130,'2017-07-26 13:19:19','2017-07-26 13:19:19'),
(551,1,'FCS_MEMBER_FEE_BANK_ACCOUNT_DATA','Bankverbindung für die Mitgliedsbeitrags-Einzahlungen\".','MB-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',140,'2017-07-26 13:19:19','2017-07-26 13:19:19'),
(552,1,'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS','Zusätzliche Liefer-Informationen für die Hersteller<br /><div class=\"small\">wird in den Bestell-Listen nach dem Lieferdatum angezeigt.</div>',', 15:00 bis 17:00 Uhr','text',120,'2017-07-26 13:19:19','2017-07-26 13:19:19'),
(553,1,'FCS_BACKUP_EMAIL_ADDRESS_BCC','E-Mail-Adresse, an die sämtliche vom System generierten E-Mails als BCC verschickt werden (Backup).<br /><div class=\"small\">Kann leer gelassen werden.</div>','','text',300,'2016-10-06 00:00:00','2016-10-06 00:00:00'),
(554,1,'FCS_SHOW_FOODCOOPSHOP_BACKLINK','Auf Homepage Link auf www.foodcoopshop.com anzeigen?<br /><div class=\"small\">Der Link wird im Footer angezeigt.</div>','1','boolean',180,'2016-11-27 00:00:00','2016-11-27 00:00:00'),
(555,1,'FCS_PAYMENT_PRODUCT_MAXIMUM','Maximalbetrag für jede Guthaben-Aufladung in Euro','500','number',127,'2016-11-28 00:00:00','2016-11-28 00:00:00'),
(556,1,'FCS_APP_NAME','Name der Foodcoop','FoodCoop Test','text',5,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(557,1,'FCS_APP_ADDRESS','Adresse der Foodcoop<br /><div class=\"small\">Wird im Footer von Homepage und E-Mails, Datenschutzerklärung, Nutzungsbedingungen usw. verwendet.</div>','Demostra&szlig;e 4,<br />\r\nA-4564 Demostadt','textarea',6,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(558,1,'FCS_APP_EMAIL','E-Mail-Adresse der Foodcoop<br /><div class=\"small\"></div>','demo-foodcoop@maillinator.com','text',7,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(559,1,'FCS_PLATFORM_OWNER','Betreiber der Plattform<br /><div class=\"small\">Für Datenschutzerklärung und Nutzungsbedingungen, bitte auch Adresse angeben. Kann leer gelassen werden, wenn die Foodcoop selbst die Plattform betreibt.</div>','','textarea',8,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(560,1,'FCS_SHOP_ORDER_DEFAULT_STATE','Bestellstatus für Sofort-Bestellungen','1','dropdown',75,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(561,1,'FCS_DB_VERSION','Version der Datenbank-Struktur','18','readonly',10,'2017-03-13 00:00:00','2017-07-26 00:00:00'),
(562,0,'FCS_DB_UPDATE','Version des letzten versuchten Datenbank-Updates','18','readonly',20,'2017-03-13 00:00:00','2017-07-26 00:00:00'),
(563,1,'FCS_EMAIL_LOG_ENABLED','Sollen alle ausgehenden E-Mails in der Datenbank gespeichert werden?<br /><div class=\"small\">Für Debugging gedacht.</div>','1','readonly',30,'2017-07-05 00:00:00','2017-07-05 00:00:00'),
(564,1,'FCS_ORDER_COMMENT_ENABLED','Kommentarfeld bei Bestell-Abschluss anzeigen?<br /><div class=\"small\">Wird im Admin-Bereich unter \"Bestellungen\" angezeigt.</div>','1','boolean',13,'2017-07-09 00:00:00','2017-07-09 00:00:00'),
(565,1,'FCS_USE_VARIABLE_MEMBER_FEE','Variablen Mitgliedsbeitrag verwenden?<br /><div class=\"small\">Den variablen Mitgliedsbeitrag bei den Hersteller-Rechnungen abziehen? Die Produkt-Preise müssen entsprechend höher eingegeben werden.</div>','0','readonly',40,'2017-08-02 00:00:00','2017-08-02 00:00:00'),
(566,1,'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE','Standardwert für variablen Mitgliedsbeitrag<br /><div class=\"small\">Der Prozentsatz kann in den Hersteller-Einstellungen auch individuell angepasst werden.</div>','0','readonly',50,'2017-08-02 00:00:00','2017-08-02 00:00:00'),
(567,1,'FCS_NETWORK_PLUGIN_ENABLED','Netzwerk-Modul aktiviert?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/netzwerk-modul\" target=\"_blank\">Infos zum Netzwerk-Modul</a></div>','0','readonly',50,'2017-09-14 00:00:00','2017-09-14 00:00:00');
/*!40000 ALTER TABLE `fcs_configuration` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_customer` DISABLE KEYS */;
INSERT INTO `fcs_customer` VALUES
(87,3,NULL,'Demo','Mitglied','fcs-demo-mitglied@mailinator.com','',NULL,1,'1000-01-01',1,'2014-12-02 12:19:31','2015-12-06 23:37:44'),
(88,4,NULL,'Demo','Admin','fcs-demo-admin@mailinator.com','',NULL,1,'1000-01-01',1,'2014-12-02 12:28:43','2016-09-29 16:25:09'),
(89,4,NULL,'Demo','Gemüse-Hersteller','fcs-demo-gemuese-hersteller@mailinator.com','',NULL,0,'1000-01-01',1,'2014-12-02 12:37:26','2015-03-11 18:12:10'),
(90,4,NULL,'Demo','Milch-Hersteller','fcs-demo-milch-hersteller@mailinator.com','',NULL,0,'1000-01-01',1,'2014-12-02 12:37:49','2015-03-11 18:11:54'),
(91,4,NULL,'Demo','Fleisch-Hersteller','fcs-demo-fleisch-hersteller@mailinator.com','',NULL,0,'1000-01-01',1,'2014-12-02 12:38:12','2015-03-11 18:11:47'),
(92,5,NULL,'Demo','Superadmin','fcs-demo-superadmin@mailinator.com','',NULL,0,'1000-01-01',1,'2016-09-29 16:26:12','2016-09-29 16:26:12');
/*!40000 ALTER TABLE `fcs_customer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_deposits` DISABLE KEYS */;
INSERT INTO `fcs_deposits` VALUES
(1,346,0,0.5),
(2,0,9,0.5),
(3,0,10,0.5),
(4,0,11,0.5);
/*!40000 ALTER TABLE `fcs_deposits` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_email_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_email_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_image` DISABLE KEYS */;
INSERT INTO `fcs_image` VALUES
(154,60,2,1),
(156,340,1,NULL),
(157,338,1,NULL);
/*!40000 ALTER TABLE `fcs_image` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_image_lang` DISABLE KEYS */;
INSERT INTO `fcs_image_lang` VALUES
(154,1,'Milch'),
(156,1,'Beuschl'),
(157,1,'Streichwurst');
/*!40000 ALTER TABLE `fcs_image_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_image_shop` DISABLE KEYS */;
INSERT INTO `fcs_image_shop` VALUES
(154,1,1,60),
(156,1,1,340),
(157,1,1,338);
/*!40000 ALTER TABLE `fcs_image_shop` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_invoices` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_manufacturer` DISABLE KEYS */;
INSERT INTO `fcs_manufacturer` VALUES
(4,'Demo Fleisch-Hersteller','2014-05-14 13:23:02','2015-05-15 13:31:41',1,NULL,NULL,0,'','','','','','','','','','',NULL,0,1,1,2,'testfcs1@mailinator.com;testfcs2@mailinator.com',0,NULL,NULL,NULL,NULL,NULL),
(5,'Demo Gemüse-Hersteller','2014-05-14 13:36:44','2016-09-27 09:34:51',1,NULL,NULL,0,'','','','','','','','','','',NULL,10,1,1,1,'',0,NULL,NULL,NULL,NULL,'1'),
(15,'Demo Milch-Hersteller','2014-06-04 21:45:12','2016-03-07 09:02:25',1,NULL,NULL,0,'','','','','','','','','','',NULL,0,1,1,4,'test@test.at',0,NULL,NULL,NULL,NULL,NULL),
(16,'Hersteller ohne Customer-Eintrag','2014-06-04 21:45:12','2016-03-07 09:02:25',1,NULL,NULL,0,'','','','','','','','','','',NULL,10,1,1,1,'',0,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `fcs_manufacturer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_manufacturer_lang` DISABLE KEYS */;
INSERT INTO `fcs_manufacturer_lang` VALUES
(4,1,'<p>tests</p>\r\n','','','',''),
(5,1,'<p>Gem&uuml;se-Hersteller Beschreibung&nbsp;lang</p>','<div class=\"entry-content\">\r\n<p>Gem&uuml;se-Hersteller Beschreibung kurz</p>\r\n</div>','','',''),
(15,1,'<p>Ja, ich bin der Milchhersteller!</p>','','','','');
/*!40000 ALTER TABLE `fcs_manufacturer_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail_tax` DISABLE KEYS */;
INSERT INTO `fcs_order_detail_tax` VALUES
(1,2,0.170000,0.340000),
(2,3,0.070000,0.210000);
/*!40000 ALTER TABLE `fcs_order_detail_tax` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_orders` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_pages` DISABLE KEYS */;
INSERT INTO `fcs_pages` VALUES
(3,'Statuten','',1,'header',1,'',0,0,'2016-08-29 13:36:43','2016-08-29 13:36:43',0,NULL,0,0),
(4,'Über uns','',3,'header',1,'',0,0,'2016-08-29 13:36:43','2016-08-29 13:36:43',0,NULL,0,0),
(8,'Links','<h4><strong>Links</strong></h4>\r\n<ul>\r\n<li><a href=\"https://www.foodcoopshop.com\" target=\"_blank\">foodcoopshop.com</a>&nbsp;- Die Software f&uuml;r eure Foodcoop</li>\r\n<li><a href=\"http://www.fairteiler-scharnstein.at\" target=\"_blank\">Fairteiler Scharnstein</a></li>\r\n</ul>',2,'header',1,'',0,0,'2016-08-29 13:36:43','2016-08-29 13:36:43',0,NULL,0,0),
(9,'Impressum','<h4>Impressum</h4>\r\n<p>Content</p>',0,'header',1,'',0,0,'2016-08-29 13:36:43','2016-08-29 13:36:43',0,NULL,0,0),
(10,'Mitmachen','',4,'header',1,'',0,0,'2016-08-29 13:36:43','2016-08-29 13:36:43',0,NULL,0,0),
(11,'Newsletter','',5,'header',-1,'',3,0,'2016-09-12 14:59:53','2016-08-29 13:36:43',0,NULL,0,0);
/*!40000 ALTER TABLE `fcs_pages` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_payments` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product` DISABLE KEYS */;
INSERT INTO `fcs_product` VALUES
(47,15,17,3,0,1.727273,1,'2014-06-11 21:20:24','2015-02-26 14:49:19'),
(49,15,17,0,0,1.090909,1,'2014-06-11 21:20:24','2015-02-26 14:46:32'),
(60,15,17,3,0,0.909091,1,'2014-06-11 21:20:24','2014-12-14 19:47:33'),
(102,4,20,2,0,3.181818,1,'2016-04-27 21:13:37','2014-09-19 14:32:51'),
(103,4,16,2,0,3.181818,1,'2016-05-05 08:28:49','2014-08-16 14:05:58'),
(161,5,20,2,0,0.909091,1,'2014-07-12 20:41:43','2014-12-14 19:35:39'),
(163,5,20,0,0,1.363637,1,'2014-07-12 20:41:43','2017-07-26 13:24:10'),
(173,5,20,0,0,1.545454,1,'2014-07-12 20:41:43','2014-12-14 19:36:10'),
(225,5,13,0,0,1.545454,1,'2014-07-19 21:05:13','2014-08-03 21:28:39'),
(279,5,13,0,0,2.000000,1,'2014-09-16 21:42:14','2014-12-14 19:35:50'),
(338,4,2,0,0,4.545455,1,'2014-11-10 20:02:35','2014-12-14 19:35:58'),
(339,5,15,0,0,0.000000,1,'2015-09-07 12:05:38','2015-02-26 13:54:07'),
(340,4,20,0,0,0.000000,1,'2016-05-05 08:28:45','2015-06-23 14:52:53'),
(343,5,20,0,0,0.000000,1,'2015-07-06 09:46:19','2015-07-06 09:46:19'),
(344,5,20,0,0,0.000000,1,'2015-10-05 17:22:40','2015-07-06 10:24:44'),
(346,5,20,2,0,0.000000,1,'2015-08-19 09:35:45','2015-08-19 09:35:45'),
(347,4,20,0,0,0.000000,1,'2015-08-24 20:36:58','2015-08-24 20:36:58'),
(350,15,20,0,0,0.000000,0,'2015-09-07 12:11:00','2015-09-07 12:11:00'),
(352,5,20,0,0,0.000000,1,'2015-10-05 17:21:00','2015-10-05 17:21:00'),
(353,5,20,0,0,0.000000,0,'2015-10-12 21:48:14','2015-10-12 21:48:14'),
(354,4,20,2,0,0.000000,1,'2016-05-14 13:31:36','2015-11-24 22:37:48'),
(355,5,20,0,0,0.000000,0,'2016-02-10 08:44:47','2016-02-10 08:44:47'),
(356,4,20,2,0,0.000000,0,'2016-03-23 12:10:17','2016-03-23 12:10:17'),
(357,5,20,0,0,0.000000,0,'2016-03-23 21:50:37','2016-03-23 21:50:37'),
(358,5,20,0,0,0.000000,0,'2016-03-23 21:51:04','2016-03-23 21:51:04'),
(359,4,20,2,0,0.000000,0,'2016-03-24 17:53:06','2016-03-24 17:53:06'),
(360,4,20,2,0,0.000000,0,'2016-04-08 10:35:56','2016-04-08 10:35:56'),
(361,4,20,2,0,9.090909,1,'2016-05-03 17:08:33','2016-05-03 17:25:29'),
(362,4,20,2,0,0.000000,0,'2016-05-25 13:15:31','2016-05-25 13:15:31');
/*!40000 ALTER TABLE `fcs_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute` DISABLE KEYS */;
INSERT INTO `fcs_product_attribute` VALUES
(1,339,1.363636,1000,1),
(2,339,5.454545,1000,0),
(3,339,10.909091,1000,0),
(9,60,0.000000,0,1),
(10,60,0.000000,0,0),
(11,60,0.000000,0,0),
(14,102,0.000000,0,1),
(15,102,0.000000,0,0),
(16,102,0.000000,0,0),
(17,347,0.000000,0,1),
(18,361,0.000000,0,1);
/*!40000 ALTER TABLE `fcs_product_attribute` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute_combination` DISABLE KEYS */;
INSERT INTO `fcs_product_attribute_combination` VALUES
(28,2),
(32,9),
(33,10),
(35,1),
(35,18),
(55,3),
(59,11),
(62,17),
(83,14),
(85,15),
(86,16);
/*!40000 ALTER TABLE `fcs_product_attribute_combination` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute_shop` DISABLE KEYS */;
INSERT INTO `fcs_product_attribute_shop` VALUES
(1,1,1.863636,1,339),
(2,1,5.454545,0,339),
(3,1,10.909091,0,339),
(9,1,0.272727,1,60),
(10,1,0.545455,0,60),
(11,1,1.090909,0,60),
(14,1,3.181819,1,102),
(15,1,5.454545,0,102),
(16,1,6.363636,0,102),
(17,1,0.010000,1,347),
(18,1,0.000000,1,361);
/*!40000 ALTER TABLE `fcs_product_attribute_shop` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_lang` DISABLE KEYS */;
INSERT INTO `fcs_product_lang` VALUES
(0,1,0,'','',NULL,'Test-Artikel'),
(47,1,1,'Lange <strong>Beschreibung</strong>','200ml<br />\nnoch ein text','','Joghurt'),
(49,1,1,'','<p>250g</p>','','Topfen'),
(60,1,1,'','1 Liter','','Milch'),
(102,1,1,'','<p>2 Paar</p>','','Frankfurter'),
(103,1,1,'','2 Paar','','Bratwürstel'),
(161,1,1,'','St&uuml;ck, wei&szlig; oder lila (nur au&szlig;en und schmeckt genauso wie der wei&szlig;e)','Stück','Kohlrabi'),
(163,1,1,'','0,25kg','','Mangold'),
(173,1,1,'','<p>1kg</p>','','Zwiebel'),
(225,1,1,'','Salattomate, rot und rund<br />\n500 g','500 g','Tomaten'),
(279,1,1,'','<p>&nbsp;pro St&uuml;ck</p>','','Romanesco'),
(338,1,1,'lange beschreibung','','ca. 300g','Streichwurst'),
(339,1,1,'','','','Kartoffel'),
(340,1,1,'','','','Beuschl'),
(343,1,1,'','','1 kg','Rote Rüben'),
(344,1,1,'','','100 g','Knoblauch'),
(346,1,1,'','','Stück','Artischocke'),
(347,1,1,'','','','Essigwurst'),
(350,1,1,'','','','Neuer Artikel von Demo Milch-Hersteller'),
(352,1,1,'','','100 g','Vogerlsalat'),
(353,1,1,'','','','Neuer Artikel von Demo Gemüse-Hersteller'),
(354,1,1,'','','Stück','Schnitzel'),
(355,1,1,'','','','Neuer Artikel von Demo Gemüse-Hersteller'),
(356,1,1,'','','','Neuer Artikel von Demo Fleisch-Hersteller'),
(357,1,1,'','','','Neuer Artikel von Demo Gemüse-Hersteller'),
(358,1,1,'','','','Neuer Artikel von Demo Gemüse-Hersteller'),
(359,1,1,'','','','Neuer Artikel von Demo Fleisch-Hersteller'),
(360,1,1,'','','','Neuer Artikel von Demo Fleisch-Hersteller'),
(361,1,1,'<p>Supermenschen brauch Superwürstl</p>','<p>Unsere Superwurst für Supermenschen</p>','2kg','SuperWürstl'),
(362,1,1,'','','','Neuer Artikel von Demo Fleisch-Hersteller');
/*!40000 ALTER TABLE `fcs_product_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_shop` DISABLE KEYS */;
INSERT INTO `fcs_product_shop` VALUES
(47,1,17,1.727273,'2014-06-11 21:20:24','2015-02-26 14:49:19'),
(49,1,17,1.090909,'2014-06-11 21:20:24','2015-02-26 14:46:32'),
(60,1,17,0.909091,'2014-06-11 21:20:24','2014-12-14 19:47:33'),
(102,1,20,0.000000,'2016-04-27 21:13:37','2014-09-19 14:32:51'),
(103,1,16,3.181819,'2016-05-05 08:28:49','2014-08-16 14:05:58'),
(161,1,20,1.363636,'2014-07-12 20:41:43','2014-12-14 19:35:39'),
(163,1,20,1.363637,'2014-07-12 20:41:43','2017-07-26 13:24:10'),
(173,1,20,1.545454,'2014-07-12 20:41:43','2014-12-14 19:36:10'),
(225,1,13,1.545454,'2014-07-19 21:05:13','2014-08-03 21:28:39'),
(279,1,13,2.000000,'2014-09-16 21:42:14','2014-12-14 19:35:50'),
(338,1,2,4.464286,'2014-11-10 20:02:35','2014-12-14 19:35:58'),
(339,1,15,0.000000,'2015-09-07 12:05:38','2015-02-26 13:54:07'),
(340,1,20,4.545455,'2016-05-05 08:28:45','2015-06-23 14:52:53'),
(341,1,20,0.000000,'2015-06-23 15:58:11','2015-06-23 15:58:11'),
(342,1,20,0.000000,'2015-06-23 21:55:52','2015-06-23 21:55:52'),
(343,1,20,1.900000,'2015-07-06 09:46:19','2015-07-06 09:46:19'),
(344,1,20,0.636364,'2015-10-05 17:22:40','2015-07-06 10:24:44'),
(346,1,20,1.652893,'2015-08-19 09:35:46','2015-08-19 09:35:46'),
(347,1,20,0.000000,'2015-08-24 20:36:58','2015-08-24 20:36:58'),
(348,1,20,0.000000,'2015-09-05 14:54:39','2015-09-05 14:54:39'),
(350,1,20,0.000000,'2015-09-07 12:11:00','2015-09-07 12:11:00'),
(352,1,20,2.000000,'2015-10-05 17:21:00','2015-10-05 17:21:00'),
(353,1,20,0.000000,'2015-10-12 21:48:14','2015-10-12 21:48:14'),
(354,1,20,9.090909,'2016-05-14 13:31:36','2015-11-24 22:37:49'),
(355,1,20,0.000000,'2016-02-10 08:44:47','2016-02-10 08:44:47'),
(356,1,20,0.000000,'2016-03-23 12:10:17','2016-03-23 12:10:17'),
(357,1,20,0.000000,'2016-03-23 21:50:37','2016-03-23 21:50:37'),
(358,1,20,0.000000,'2016-03-23 21:51:04','2016-03-23 21:51:04'),
(359,1,20,0.000000,'2016-03-24 17:53:06','2016-03-24 17:53:06'),
(360,1,20,2.727273,'2016-04-08 10:35:56','2016-04-08 10:35:56'),
(361,1,20,9.090909,'2016-05-03 17:08:33','2016-05-03 17:25:29'),
(362,1,20,0.000000,'2016-05-25 13:15:31','2016-05-25 13:15:31');
/*!40000 ALTER TABLE `fcs_product_shop` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_sliders` DISABLE KEYS */;
INSERT INTO `fcs_sliders` VALUES
(6,'2be64c60e6126c9085fd9d9717532a14e5a5bb4e_slide4.png',0,1);
/*!40000 ALTER TABLE `fcs_sliders` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_smart_blog_post` DISABLE KEYS */;
INSERT INTO `fcs_smart_blog_post` VALUES
(2,2,0,0,0,1,0,1,1,'2014-12-18 10:37:26','2015-03-16 12:41:46',30,1,1,'0','');
/*!40000 ALTER TABLE `fcs_smart_blog_post` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_smart_blog_post_lang` DISABLE KEYS */;
INSERT INTO `fcs_smart_blog_post_lang` VALUES
(2,'1','Demo Blog Artikel','','','Lorem ipsum dolor sit amet, consetetur sadipscing','<p>Lorem ipsum dolor sit amet</p>','Demo-Blog-Artikel');
/*!40000 ALTER TABLE `fcs_smart_blog_post_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_smart_blog_post_shop` DISABLE KEYS */;
INSERT INTO `fcs_smart_blog_post_shop` VALUES
(26,2,1);
/*!40000 ALTER TABLE `fcs_smart_blog_post_shop` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_stock_available` DISABLE KEYS */;
INSERT INTO `fcs_stock_available` VALUES
(54,1,0,1,0),
(55,2,0,1,0),
(56,3,0,1,0),
(57,4,0,1,0),
(58,5,0,1,0),
(59,6,0,1,0),
(60,7,0,1,0),
(101,9,0,1,0),
(102,15,0,1,0),
(103,16,0,1,0),
(104,31,0,1,0),
(105,32,0,1,0),
(106,33,0,1,0),
(119,47,0,1,100),
(121,49,0,1,910),
(132,60,0,1,2979),
(195,102,0,1,2996),
(196,103,0,1,990),
(252,83,0,1,0),
(316,161,0,1,996),
(318,163,0,1,988),
(328,173,0,1,931),
(393,225,0,1,10),
(436,238,0,1,0),
(466,279,0,1,916),
(572,338,0,1,15),
(615,40,0,1,0),
(616,43,0,1,0),
(617,56,0,1,0),
(618,57,0,1,0),
(619,58,0,1,0),
(620,59,0,1,0),
(621,64,0,1,0),
(622,65,0,1,0),
(623,66,0,1,0),
(624,67,0,1,0),
(625,68,0,1,0),
(626,75,0,1,0),
(627,76,0,1,0),
(628,81,0,1,0),
(629,82,0,1,0),
(630,84,0,1,0),
(631,85,0,1,0),
(632,86,0,1,0),
(633,87,0,1,0),
(634,88,0,1,0),
(635,89,0,1,0),
(636,112,0,1,0),
(637,114,0,1,0),
(638,115,0,1,0),
(639,128,0,1,0),
(640,140,0,1,0),
(641,141,0,1,0),
(642,142,0,1,0),
(643,143,0,1,0),
(644,144,0,1,0),
(645,145,0,1,0),
(646,146,0,1,0),
(647,150,0,1,0),
(648,154,0,1,0),
(649,178,0,1,0),
(650,218,0,1,0),
(651,219,0,1,0),
(652,220,0,1,0),
(653,229,0,1,0),
(654,230,0,1,0),
(655,281,0,1,0),
(656,297,0,1,0),
(657,299,0,1,0),
(658,300,0,1,0),
(659,301,0,1,0),
(660,302,0,1,0),
(661,303,0,1,0),
(662,304,0,1,0),
(663,305,0,1,0),
(664,306,0,1,0),
(665,314,0,1,0),
(666,315,0,1,0),
(667,316,0,1,0),
(668,317,0,1,0),
(669,358,0,1,999),
(670,359,0,1,999),
(671,360,0,1,999),
(672,361,0,1,999),
(673,362,0,1,999),
(674,339,0,1,2959),
(675,339,1,1,964),
(676,339,2,1,995),
(677,339,3,1,1000),
(678,340,0,1,991),
(679,343,0,1,15),
(680,344,0,1,78),
(682,0,0,0,20),
(686,346,0,1,98),
(687,347,0,1,998),
(691,350,0,1,999),
(692,60,9,1,996),
(693,60,10,1,20),
(694,60,11,1,999),
(696,352,0,1,999),
(697,353,0,1,999),
(700,354,0,1,999),
(701,102,14,1,999),
(702,102,15,1,998),
(703,102,16,1,999),
(704,355,0,1,999),
(705,347,17,1,998),
(706,356,0,1,999),
(707,357,0,1,999),
(708,361,18,1,999);
/*!40000 ALTER TABLE `fcs_stock_available` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_sync_domains` DISABLE KEYS */;
INSERT INTO `fcs_sync_domains` VALUES
(1,'http://www.foodcoopshop.test',1);
/*!40000 ALTER TABLE `fcs_sync_domains` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_sync_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_sync_products` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_tax` DISABLE KEYS */;
INSERT INTO `fcs_tax` VALUES
(1,20.000,1,0),
(2,10.000,1,0),
(3,13.000,1,0);
/*!40000 ALTER TABLE `fcs_tax` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

