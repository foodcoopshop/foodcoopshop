
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
TRUNCATE TABLE `fcs_blog_posts`;
TRUNCATE TABLE `fcs_cart_products`;
TRUNCATE TABLE `fcs_carts`;
TRUNCATE TABLE `fcs_category`;
TRUNCATE TABLE `fcs_category_product`;
TRUNCATE TABLE `fcs_configuration`;
TRUNCATE TABLE `fcs_customer`;
TRUNCATE TABLE `fcs_deposits`;
TRUNCATE TABLE `fcs_email_logs`;
TRUNCATE TABLE `fcs_images`;
TRUNCATE TABLE `fcs_invoices`;
TRUNCATE TABLE `fcs_manufacturer`;
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
TRUNCATE TABLE `fcs_stock_available`;
TRUNCATE TABLE `fcs_sync_domains`;
TRUNCATE TABLE `fcs_sync_products`;
TRUNCATE TABLE `fcs_tax`;
TRUNCATE TABLE `fcs_timebased_currency_order_detail`;
TRUNCATE TABLE `fcs_timebased_currency_orders`;
TRUNCATE TABLE `fcs_timebased_currency_payments`;

/*!40000 ALTER TABLE `fcs_action_logs` DISABLE KEYS */;
INSERT INTO `fcs_action_logs` VALUES
(1,'customer_order_finished',92,2,'orders','Demo Superadmin hat eine neue Bestellung getätigt (18,42&nbsp;€).','2018-04-03 17:13:30');
/*!40000 ALTER TABLE `fcs_action_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_address` DISABLE KEYS */;
INSERT INTO `fcs_address` VALUES
(153,87,0,'Mitglied','Demo','Demostrasse 4','','4644','Scharnstein','','','0664/000000000','fcs-demo-mitglied@mailinator.com','2014-12-02 12:19:31','2014-12-02 12:19:31'),
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

/*!40000 ALTER TABLE `fcs_blog_posts` DISABLE KEYS */;
INSERT INTO `fcs_blog_posts` VALUES
(2,'Demo Blog Artikel','Lorem ipsum dolor sit amet, consetetur sadipscing','<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>',88,0,0,1,'2014-12-18 10:37:26','2015-03-16 12:41:46',1);
/*!40000 ALTER TABLE `fcs_blog_posts` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cart_products` DISABLE KEYS */;
INSERT INTO `fcs_cart_products` VALUES
(1,1,346,0,1,'2018-03-01 10:17:14','2018-03-01 10:17:14'),
(2,1,340,0,1,'2018-03-01 10:17:14','2018-03-01 10:17:14'),
(3,2,346,0,2,'2018-04-03 17:13:29','2018-04-03 17:13:29'),
(4,2,60,10,3,'2018-04-03 17:13:29','2018-04-03 17:13:29'),
(5,2,344,0,1,'2018-04-03 17:13:29','2018-04-03 17:13:29'),
(6,2,103,0,5,'2018-04-03 17:13:30','2018-04-03 17:13:30');
/*!40000 ALTER TABLE `fcs_cart_products` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_carts` DISABLE KEYS */;
INSERT INTO `fcs_carts` VALUES
(1,92,0,'2018-03-01 10:17:14','2018-03-01 10:17:14'),
(2,92,0,'2018-04-03 17:13:29','2018-04-03 17:13:30'),
(3,92,1,'2018-04-03 17:13:33','2018-04-03 17:13:33');
/*!40000 ALTER TABLE `fcs_carts` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category` DISABLE KEYS */;
INSERT INTO `fcs_category` VALUES
(13,0,'Obst und Gemüse','',2,5,6,1,'2014-05-14 12:23:25','2014-05-14 12:23:25'),
(14,0,'Sonstiges','',2,7,8,0,'2014-05-14 12:23:39','2014-12-02 12:52:03'),
(15,0,'Getreideprodukte und Hülsenfrüchte','',2,9,10,1,'2014-05-14 21:38:45','2015-02-26 13:56:19'),
(16,0,'Fleischprodukte','',2,11,12,1,'2014-05-14 21:40:51','2014-05-14 21:48:48'),
(17,0,'Milchprodukte','',2,13,14,1,'2014-05-14 21:43:00','2014-05-14 21:51:12'),
(18,0,'Getränke','',2,15,16,0,'2014-05-14 21:48:15','2014-12-02 12:52:03'),
(19,0,'Öle','',2,17,18,0,'2014-05-14 21:52:41','2014-12-02 12:52:03'),
(20,0,'Alle Produkte','',2,3,4,1,'2014-05-14 21:53:52','2014-05-17 13:14:22'),
(21,0,'Brot und Gebäck','',2,19,20,0,'2014-05-14 21:54:38','2014-12-02 12:52:04'),
(22,0,'Gewürze und Saaten','',2,21,22,0,'2014-05-14 21:56:28','2014-12-02 12:52:04'),
(23,0,'Honigprodukte','',2,23,24,0,'2014-06-29 13:16:24','2014-12-02 12:52:04'),
(24,0,'Eier','',2,25,26,0,'2014-07-11 23:10:22','2014-12-02 12:52:04'),
(25,0,'Schnäpse','',2,27,28,1,'2014-09-11 18:22:25','2015-08-08 10:34:22'),
(26,0,'Hygieneartikel','',2,29,30,0,'2014-11-08 12:05:53','2014-12-02 12:52:05');
/*!40000 ALTER TABLE `fcs_category` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category_product` DISABLE KEYS */;
INSERT INTO `fcs_category_product` VALUES
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
(553,1,'FCS_BACKUP_EMAIL_ADDRESS_BCC','E-Mail-Adresse, an die sämtliche vom System generierten E-Mails als BCC verschickt werden (Backup).<br /><div class=\"small\">Kann leer gelassen werden.</div>','','text',190,'2016-10-06 00:00:00','2016-10-06 00:00:00'),
(554,1,'FCS_SHOW_FOODCOOPSHOP_BACKLINK','Auf Homepage Link auf www.foodcoopshop.com anzeigen?<br /><div class=\"small\">Der Link wird im Footer angezeigt.</div>','1','boolean',180,'2016-11-27 00:00:00','2016-11-27 00:00:00'),
(555,1,'FCS_PAYMENT_PRODUCT_MAXIMUM','Maximalbetrag für jede Guthaben-Aufladung in Euro','500','number',127,'2016-11-28 00:00:00','2016-11-28 00:00:00'),
(556,1,'FCS_APP_NAME','Name der Foodcoop','FoodCoop Test','text',5,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(557,1,'FCS_APP_ADDRESS','Adresse der Foodcoop<br /><div class=\"small\">Wird im Footer von Homepage und E-Mails, Datenschutzerklärung, Nutzungsbedingungen usw. verwendet.</div>','Demostra&szlig;e 4,<br />\r\nA-4564 Demostadt','textarea',6,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(558,1,'FCS_APP_EMAIL','E-Mail-Adresse der Foodcoop<br /><div class=\"small\"></div>','demo-foodcoop@maillinator.com','text',7,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(559,1,'FCS_PLATFORM_OWNER','Betreiber der Plattform<br /><div class=\"small\">Für Datenschutzerklärung und Nutzungsbedingungen, bitte auch Adresse angeben. Kann leer gelassen werden, wenn die Foodcoop selbst die Plattform betreibt.</div>','','textarea',8,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(560,1,'FCS_SHOP_ORDER_DEFAULT_STATE','Bestellstatus für Sofort-Bestellungen','1','dropdown',75,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(563,1,'FCS_EMAIL_LOG_ENABLED','Sollen alle ausgehenden E-Mails in der Datenbank gespeichert werden?<br /><div class=\"small\">Für Debugging gedacht.</div>','1','readonly',30,'2017-07-05 00:00:00','2017-07-05 00:00:00'),
(564,1,'FCS_ORDER_COMMENT_ENABLED','Kommentarfeld bei Bestell-Abschluss anzeigen?<br /><div class=\"small\">Wird im Admin-Bereich unter \"Bestellungen\" angezeigt.</div>','1','boolean',13,'2017-07-09 00:00:00','2017-07-09 00:00:00'),
(565,1,'FCS_USE_VARIABLE_MEMBER_FEE','Variablen Mitgliedsbeitrag verwenden?<br /><div class=\"small\">Den variablen Mitgliedsbeitrag bei den Hersteller-Rechnungen abziehen? Die Produkt-Preise müssen entsprechend höher eingegeben werden.</div>','0','readonly',40,'2017-08-02 00:00:00','2017-08-02 00:00:00'),
(566,1,'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE','Standardwert für variablen Mitgliedsbeitrag<br /><div class=\"small\">Der Prozentsatz kann in den Hersteller-Einstellungen auch individuell angepasst werden.</div>','0','readonly',50,'2017-08-02 00:00:00','2017-08-02 00:00:00'),
(567,1,'FCS_NETWORK_PLUGIN_ENABLED','Netzwerk-Modul aktiviert?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/netzwerk-modul\" target=\"_blank\">Infos zum Netzwerk-Modul</a></div>','0','readonly',50,'2017-09-14 00:00:00','2017-09-14 00:00:00'),
(568,1,'FCS_TIMEBASED_CURRENCY_ENABLED','Zeitwährungs-Modul aktiv?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/zeitwaehrungs-modul\" target=\"_blank\">Infos zum Zeitwährungs-Modul</a></div>','1','boolean',200,'2018-03-16 15:23:31','2018-03-16 15:23:31'),
(569,1,'FCS_TIMEBASED_CURRENCY_NAME','Zeitwährung: Name<br /><div class=\"small\">max. 10 Zeichen</div>','Stunden','text',210,'2018-03-16 15:23:31','2018-03-16 15:23:31'),
(570,1,'FCS_TIMEBASED_CURRENCY_SHORTCODE','Zeitwährung: Abkürzung<br /><div class=\"small\">max. 3 Zeichen</div>','h','text',220,'2018-03-16 15:23:31','2018-03-16 15:23:31'),
(571,1,'FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE','Zeitwährung: Umrechnungskurs<br /><div class=\"small\">in €, 2 Kommastellen</div>','10,50','number',230,'2018-03-16 15:23:31','2018-03-16 15:23:31'),
(572,1,'FCS_TIMEBASED_CURRENCY_MAX_OVERDRAFT_CUSTOMER','Zeitwährung: Überziehungsrahmen für Mitglieder<br /><div class=\"small\">Wie viele Stunden kann ein Mitglied maximal ins Minus gehen?</div>','0','number',240,'2018-03-16 15:23:31','2018-03-16 15:23:31'),
(573,1,'FCS_TIMEBASED_CURRENCY_MAX_OVERDRAFT_MANUFACTURER','Zeitwährung: Überziehungsrahmen für Hersteller<br /><div class=\"small\">Wie viele Stunden kann ein Hersteller maximal ins Minus gehen?</div>','0','number',250,'2018-03-16 15:23:31','2018-03-16 15:23:31');
/*!40000 ALTER TABLE `fcs_configuration` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_customer` DISABLE KEYS */;
INSERT INTO `fcs_customer` VALUES
(87,3,NULL,'Demo','Mitglied','fcs-demo-mitglied@mailinator.com','bcee5b8b98c17603db37bfbd131a5074',NULL,1,'1000-01-01',1,'2014-12-02 12:19:31','2015-12-06 23:37:44',0),
(88,4,NULL,'Demo','Admin','fcs-demo-admin@mailinator.com','bcee5b8b98c17603db37bfbd131a5074',NULL,1,'1000-01-01',1,'2014-12-02 12:28:43','2016-09-29 16:25:09',0),
(89,4,NULL,'Demo','Gemüse-Hersteller','fcs-demo-gemuese-hersteller@mailinator.com','bcee5b8b98c17603db37bfbd131a5074',NULL,0,'1000-01-01',1,'2014-12-02 12:37:26','2015-03-11 18:12:10',0),
(90,4,NULL,'Demo','Milch-Hersteller','fcs-demo-milch-hersteller@mailinator.com','bcee5b8b98c17603db37bfbd131a5074',NULL,0,'1000-01-01',1,'2014-12-02 12:37:49','2015-03-11 18:11:54',0),
(91,4,NULL,'Demo','Fleisch-Hersteller','fcs-demo-fleisch-hersteller@mailinator.com','bcee5b8b98c17603db37bfbd131a5074',NULL,0,'1000-01-01',1,'2014-12-02 12:38:12','2015-03-11 18:11:47',0),
(92,5,NULL,'Demo','Superadmin','fcs-demo-superadmin@mailinator.com','bcee5b8b98c17603db37bfbd131a5074',NULL,1,'1000-01-01',1,'2016-09-29 16:26:12','2016-09-29 16:26:12',1);
/*!40000 ALTER TABLE `fcs_customer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_deposits` DISABLE KEYS */;
INSERT INTO `fcs_deposits` VALUES
(1,346,0,0.5),
(2,0,9,0.5),
(3,0,10,0.5),
(4,0,11,0.5);
/*!40000 ALTER TABLE `fcs_deposits` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_email_logs` DISABLE KEYS */;
INSERT INTO `fcs_email_logs` VALUES
(1,'{\"no-reply@foodcoopshop.com\":\"FoodCoopShop No-Reply\"}','{\"fcs-demo-superadmin@mailinator.com\":\"fcs-demo-superadmin@mailinator.com\"}','[]','[]','Bestellbestätigung','{\"Date\":\"Tue, 03 Apr 2018 17:13:32 +0200\",\"Message-ID\":\"<f4ac52b469f746329db4f44419c77fc9@www.foodcoopshop.test>\",\"MIME-Version\":\"1.0\",\"Content-Type\":\"multipart\\/mixed; boundary=\\\"db2d6b9081ba5a8c649dce962cb65c53\\\"\",\"Content-Transfer-Encoding\":\"8bit\"}','--db2d6b9081ba5a8c649dce962cb65c53\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\r\n\r\n    <head>\r\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\r\n        <meta name=\"viewport\" content=\"initial-scale=1.0\">\r\n        <meta name=\"format-detection\" content=\"telephone=no\">\r\n        <title>FoodCoop Test</title>\r\n    </head>\r\n    \r\n    <table width=\"742\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\" style=\"color:#000;font-family:Arial;\">\r\n        <tbody>\r\n            <tr>\r\n                <td align=\"center\" valign=\"middle\" style=\"padding-bottom: 20px;\">\r\n                    <a href=\"http://www.foodcoopshop.test\">\r\n                        <img src=\"http://www.foodcoopshop.test/files/images/logo.jpg\" width=\"150\" />\r\n                    </a>\r\n                </td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\"border-bottom: 1px solid #d6d4d4;\"></td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\"padding-bottom: 20px;\"></td>\r\n            </tr>\r\n            <tr>\r\n                <td><table width=\"742\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\"  style=\"color:#000;border-collapse:collapse;font-size:14px;font-family:Arial;line-height:19px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td style=\"font-weight:bold;font-size:18px;padding-bottom:20px;\">\r\n                Hallo Demo Superadmin,\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <td style=\"padding-bottom:20px;\">\r\n                vielen Dank für deine Bestellung Nr. 2 vom 03.04.2018 15:13:30.\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n\r\n<table width=\"742\" cellpadding=\"6\" border=\"0\" cellspacing=\"0\"  style=\"color:#000;border-collapse:collapse;font-size:14px;font-family:Arial;line-height:19px;\">\r\n    \r\n<tbody>\r\n    \r\n        <tr>\r\n            <td align=\"center\" style=\"padding: 10px;font-weight:bold;border:1px solid #d6d4d4;background-color:#fbfbfb;\">Anzahl</td><td align=\"center\" style=\"padding: 10px;font-weight:bold;border:1px solid #d6d4d4;background-color:#fbfbfb;\">Produkte</td><td align=\"center\" style=\"padding: 10px;font-weight:bold;border:1px solid #d6d4d4;background-color:#fbfbfb;\">Hersteller</td><td align=\"center\" style=\"padding: 10px;font-weight:bold;border:1px solid #d6d4d4;background-color:#fbfbfb;\">Preis</td><td align=\"center\" style=\"padding: 10px;font-weight:bold;border:1px solid #d6d4d4;background-color:#fbfbfb;\">Pfand</td><td align=\"center\" style=\"padding: 10px;font-weight:bold;border:1px solid #d6d4d4;background-color:#fbfbfb;\">Stunden</td>        </tr>\r\n        \r\n                                \r\n            <tr>\r\n                                <td valign=\"middle\" align=\"center\" style=\"border:1px solid #d6d4d4;font-weight:bold;\">\r\n                    2x\r\n                </td>\r\n                <td valign=\"middle\" style=\"border:1px solid #d6d4d4;\">\r\n                    Artischocke : Stück                </td>\r\n                <td valign=\"middle\" style=\"border:1px solid #d6d4d4;\">\r\n                    Demo Gemüse-Hersteller                </td>\r\n                <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                    2,70&nbsp;€                </td>\r\n                \r\n                <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                    1,00&nbsp;€                </td>\r\n                \r\n                                    <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                        00:05:36                    </td>\r\n                                \r\n            </tr>           \r\n            \r\n                                \r\n            <tr>\r\n                                <td valign=\"middle\" align=\"center\" style=\"border:1px solid #d6d4d4;font-weight:bold;\">\r\n                    5x\r\n                </td>\r\n                <td valign=\"middle\" style=\"border:1px solid #d6d4d4;\">\r\n                    Bratwürstel                </td>\r\n                <td valign=\"middle\" style=\"border:1px solid #d6d4d4;\">\r\n                    Demo Fleisch-Hersteller                </td>\r\n                <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                    15,24&nbsp;€                </td>\r\n                \r\n                <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                                    </td>\r\n                \r\n                                    <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                        00:13:25                    </td>\r\n                                \r\n            </tr>           \r\n            \r\n                                \r\n            <tr>\r\n                                <td valign=\"middle\" align=\"center\" style=\"border:1px solid #d6d4d4;\">\r\n                    1x\r\n                </td>\r\n                <td valign=\"middle\" style=\"border:1px solid #d6d4d4;\">\r\n                    Knoblauch : 100 g                </td>\r\n                <td valign=\"middle\" style=\"border:1px solid #d6d4d4;\">\r\n                    Demo Gemüse-Hersteller                </td>\r\n                <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                    0,48&nbsp;€                </td>\r\n                \r\n                <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                                    </td>\r\n                \r\n                                    <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                        00:00:59                    </td>\r\n                                \r\n            </tr>           \r\n            \r\n                                \r\n            <tr>\r\n                                <td valign=\"middle\" align=\"center\" style=\"border:1px solid #d6d4d4;font-weight:bold;\">\r\n                    3x\r\n                </td>\r\n                <td valign=\"middle\" style=\"border:1px solid #d6d4d4;\">\r\n                    Milch : 0,5l                </td>\r\n                <td valign=\"middle\" style=\"border:1px solid #d6d4d4;\">\r\n                    Demo Milch-Hersteller                </td>\r\n                <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                    1,86&nbsp;€                </td>\r\n                \r\n                <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                    1,50&nbsp;€                </td>\r\n                \r\n                                    <td valign=\"middle\" align=\"right\" style=\"border:1px solid #d6d4d4;\">\r\n                                            </td>\r\n                                \r\n            </tr>           \r\n            \r\n             \r\n         <tr>\r\n            <td style=\"border:1px solid #d6d4d4;\" colspan=\"3\"></td>\r\n            <td align=\"right\" style=\"font-weight:bold;border:1px solid #d6d4d4;\">18,42&nbsp;€</td>\r\n\r\n            <td align=\"right\" style=\"font-weight:bold;border:1px solid #d6d4d4;\">\r\n                2,50&nbsp;€            </td>\r\n            \r\n                            <td align=\"right\" style=\"font-weight:bold;border:1px solid #d6d4d4;\">\r\n                    00:20:00                </td>\r\n                        \r\n        </tr>\r\n        \r\n        <tr>\r\n            <td style=\"background-color:#fbfbfb;border:1px solid #d6d4d4;\" colspan=\"2\"></td>\r\n            <td align=\"right\" style=\"font-size:18px;font-weight:bold;background-color:#fbfbfb;border:1px solid #d6d4d4;\">Gesamt</td>\r\n            <td align=\"center\" style=\"font-size:18px;font-weight:bold;background-color:#fbfbfb;border:1px solid #d6d4d4;\" colspan=\"2\">\r\n                20,92&nbsp;€            </td>\r\n                            <td style=\"background-color:#fbfbfb;border:1px solid #d6d4d4;\"></td>\r\n                    </tr>\r\n        \r\n    </tbody>\r\n</table>\r\n\r\n<table width=\"742\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\"  style=\"color:#000;border-collapse:collapse;font-size:14px;font-family:Arial;line-height:19px;\">\r\n    <tbody>\r\n    \r\n        <tr><td style=\"padding-top:20px;\">\r\n            Enthaltene Umsatzsteuer: 2,15&nbsp;€        </td></tr>\r\n        \r\n        <tr><td>\r\n            Der Gesamtbetrag wurde von deinem Guthaben abgezogen.        </td></tr>\r\n        \r\n        \r\n        <tr><td><p>\r\n            Bitte hole deine Produkte am <b>Freitag, 6. April 2018</b> bei uns (Demostra&szlig;e 4,, \r\nA-4564 Demostadt) ab.\r\n        </p></td></tr>\r\n        \r\n        <tr><td style=\"font-size:12px;\">\r\n            Eine detaillierte Auflistung deiner Bestellung findest du in der angehängten Bestellübersicht (PDF). Die Informationen zum Rücktrittsrecht sind gesetzlich vorgeschrieben, das Rücktrittsrecht für verderbliche Waren ist allerdings ausgeschlossen.\r\n        </td></tr>\r\n        \r\n    </tbody>\r\n</table>\r\n</td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\"padding-top:20px;font-size:12px;\">\r\n                    Diese E-Mail wurde automatisch erstellt.\r\n                        <br /><br />\r\n                    --<br />\r\n                    Demostra&szlig;e 4,<br />\r\nA-4564 Demostadt<br /><a href=\"mailto:demo-foodcoop@maillinator.com\">demo-foodcoop@maillinator.com</a><br /><a href=\"http://www.foodcoopshop.test\">www.foodcoopshop.test</a>                                            <br /><br />Eingeloggt:\r\n                            Demo Superadmin                                    </td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n    \r\n</html>\r\n\r\n--db2d6b9081ba5a8c649dce962cb65c53\r\nContent-Disposition: attachment; filename=\"Informationen-ueber-Ruecktrittsrecht-und-Ruecktrittsformular.pdf\"\r\nContent-Type: application/pdf\r\nContent-Transfer-Encoding: base64\r\n\r\nJVBERi0xLjcKJeLjz9MKNiAwIG9iago8PCAvVHlwZSAvUGFnZSAvUGFyZW50IDEgMCBSIC9MYXN0\r\nTW9kaWZpZWQgKEQ6MjAxODA0MDMxNzEzMzErMDInMDAnKSAvUmVzb3VyY2VzIDIgMCBSIC9NZWRp\r\nYUJveCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQ3JvcEJveCBb\r\nMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQmxlZWRCb3ggWzAuMDAw\r\nMDAwIDAuMDAwMDAwIDU5NS4yNzYwMDAgODQxLjg5MDAwMF0gL1RyaW1Cb3ggWzAuMDAwMDAwIDAu\r\nMDAwMDAwIDU5NS4yNzYwMDAgODQxLjg5MDAwMF0gL0FydEJveCBbMC4wMDAwMDAgMC4wMDAwMDAg\r\nNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQ29udGVudHMgNyAwIFIgL1JvdGF0ZSAwIC9Hcm91cCA8\r\nPCAvVHlwZSAvR3JvdXAgL1MgL1RyYW5zcGFyZW5jeSAvQ1MgL0RldmljZVJHQiA+PiAvUFogMSA+\r\nPgplbmRvYmoKNyAwIG9iago8PC9GaWx0ZXIgL0ZsYXRlRGVjb2RlIC9MZW5ndGggMjIyMz4+IHN0\r\ncmVhbQp4nO1bTXPbNhC9+1fsMZlRWJLil9pL7SZO0zSdNnabmTY9QCJEMSZBBwCtVn82F/+GnnTo\r\nLgBKshQ5tuskcmPPKKYpQhAe9r19CyC+F6c+/sAUfPgBX2/gjz/xV46vp/gq9g6O4avDAALf880P\r\nHI/hyfGef+WG4cWGb7e9sehg8+Ll067Rxrf4SG1u9OWu1+gtBFHgpf0wzPoImA9p34sHQZr0oR95\r\nfhCnUQZpnHiDeJDEMYxq+OqZD48b+GVXxn3jILhxUN3OI9g9dimXY5li74B3o6zvZf144CeQDmIv\r\nTXGKBnCcwx8PDpsm/65pTuGYK/3wTzj+4co02NZX4iWh7Qu7DbGv0Ld9PeZ1o7Rk7zhEvVvpKxl4\r\n/cz2lfpmXF1f+4+iOInAdsny2xmaH3vZwHYXZ3Zoge0ux34ejRHLEWL5bc3KqioF0430Rk296HsZ\r\nd2l/4GVRiDyAei/GOQnTZO1+tXe098s1YvH6vAghSG6h0dbP2vrNL+vFjOntZc/IArZO0YrE+JEX\r\n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\r\nAWGUxFwKZW5kc3RyZWFtCmVuZG9iago4IDAgb2JqCjw8IC9UeXBlIC9QYWdlIC9QYXJlbnQgMSAw\r\nIFIgL0xhc3RNb2RpZmllZCAoRDoyMDE4MDQwMzE3MTMzMSswMicwMCcpIC9SZXNvdXJjZXMgMiAw\r\nIFIgL01lZGlhQm94IFswLjAwMDAwMCAwLjAwMDAwMCA1OTUuMjc2MDAwIDg0MS44OTAwMDBdIC9D\r\ncm9wQm94IFswLjAwMDAwMCAwLjAwMDAwMCA1OTUuMjc2MDAwIDg0MS44OTAwMDBdIC9CbGVlZEJv\r\neCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvVHJpbUJveCBbMC4w\r\nMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQXJ0Qm94IFswLjAwMDAwMCAw\r\nLjAwMDAwMCA1OTUuMjc2MDAwIDg0MS44OTAwMDBdIC9Db250ZW50cyA5IDAgUiAvUm90YXRlIDAg\r\nL0dyb3VwIDw8IC9UeXBlIC9Hcm91cCAvUyAvVHJhbnNwYXJlbmN5IC9DUyAvRGV2aWNlUkdCID4+\r\nIC9QWiAxID4+CmVuZG9iago5IDAgb2JqCjw8L0ZpbHRlciAvRmxhdGVEZWNvZGUgL0xlbmd0aCA4\r\nNzE+PiBzdHJlYW0KeJzVV01T2zAQvedX7LGdSYUtW/64FUppy6lAWmbKcHBi2VaxZZBkMsOf7aU/\r\nI4eurCQEaFKgdCb1TBx7tdLTPr1dyR5hsYcXTMGDQ/x9h7Nz/MvBI15//ebh+AN6lIO9Eewc+OAv\r\n7aMC3o8G3voh4W5Herfj1bqGjVNZdHowi3/U51mTe1qnK/BDn8QBpUmAhHkQB4SlfhwFEITE81kc\r\nJhCziKQsjRiDSQM7nzzYb+FoW+J+tgieLaqXcUF4hFS3sUwRHdAaJgFJApZ6EcQpI3GMS5TCKIez\r\nVwdtm79r20sYcW1en8Po8NFpsA4rIhF1WAhLEYt6DmufN602KvvBIRy+CFaUkiBxWLHXx7XA2n0T\r\nsigEB5nlLxOax0iSOjiWuNB8B5cjzpsCuZwgl2+bTNS1kJlpFZm0zRL7VndxkJIkpJgH0AwYrgmN\r\no3v2enAyONo03efWuKeZ/2YCV5t8VAlrmV6pFDgfSj0axI7oE8GhmWnNJRQzBVxIfOJmmokSH065\r\nMtdc1Z02kHMFp5lCq+wUZF1x0TYNl0OYcikhF1xbh5UO6DIf7wY7fFazopPlYu2wOm0nQVEak3iF\r\nIBv2HteTKisKLisuzBDeW3J6k8HoOpnDQScvjGilnnKh+SpXYlIZkK1BlvKe0y9NmckSGmFAVMjO\r\n9jOSMCeZxDGCqzmbXNx0xayyEQptyEoMf9oKNrX+V5kURfNMmlf+j9kY2bD5ZBMAlxiFkmda29SA\r\nfYGCMTWKw2ASoN/0pyUvd0oReFddoQuF7TDmpZA2bXRb10O8Q7UcupPa5dT2qyZ0eRTONxAkhDfc\r\nFhoMZY/jtlViXYCbrKptEbE8YNGHXWm4qPvXsdDWw1q/Ydpd2hQbLiwLMq5b2Tvvdno2RmrxRW8/\r\nN4HLqAU3SwEojtVCQyWktnWjxpurrBq+YmVFzjRGbdDZNlvexlwhNygKNVZZb3uoNdHY3iVacLib\r\nrtl+fih12pmfBj5wnTWma4qsX2A1j6jX0HWrSiSo6nX1IHQujb7syXpKjXrscfUea4NHHiqf9L1z\r\nO9mQEuanWI3vHnFuzfaE8zffYX9aFhqSxI9CNt8ZP8miVU1mtz2YoQ7hGLcFo4QxTscvckTEc2HK\r\nPMroffQTlD0H2hcAtoRaQ+LGc9/mRf0FTtKXTwplbmRzdHJlYW0KZW5kb2JqCjEwIDAgb2JqCjw8\r\nIC9UeXBlIC9QYWdlIC9QYXJlbnQgMSAwIFIgL0xhc3RNb2RpZmllZCAoRDoyMDE4MDQwMzE3MTMz\r\nMSswMicwMCcpIC9SZXNvdXJjZXMgMiAwIFIgL01lZGlhQm94IFswLjAwMDAwMCAwLjAwMDAwMCA1\r\nOTUuMjc2MDAwIDg0MS44OTAwMDBdIC9Dcm9wQm94IFswLjAwMDAwMCAwLjAwMDAwMCA1OTUuMjc2\r\nMDAwIDg0MS44OTAwMDBdIC9CbGVlZEJveCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4\r\nNDEuODkwMDAwXSAvVHJpbUJveCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkw\r\nMDAwXSAvQXJ0Qm94IFswLjAwMDAwMCAwLjAwMDAwMCA1OTUuMjc2MDAwIDg0MS44OTAwMDBdIC9D\r\nb250ZW50cyAxMSAwIFIgL1JvdGF0ZSAwIC9Hcm91cCA8PCAvVHlwZSAvR3JvdXAgL1MgL1RyYW5z\r\ncGFyZW5jeSAvQ1MgL0RldmljZVJHQiA+PiAvUFogMSA+PgplbmRvYmoKMTEgMCBvYmoKPDwvRmls\r\ndGVyIC9GbGF0ZURlY29kZSAvTGVuZ3RoIDEwNDM+PiBzdHJlYW0KeJztWdty2zYQfddX7KPTsSCQ\r\nAAjST03q2Gk66SSR0jzEmQ5FghJbErRBsprJz/ZH/JDlRZYvla0L7cgdcYYSBWK12HOwFwCUCEnx\r\nghlQeIv3X/DlK36FeJ/iPem9GsHgxAKLElpfMIrg9ahHVxa0bwpeLHtxpeDuw8fTudCdUTySzEaD\r\nW0/oAixuEcls22UIGAXJiPAs6TBgnFBLSO6CFA7xhOcIAUEKg18pHGfwYVfs3ngSbDypuumC6lGl\r\nWdgyQ+2ArdxlxGXCow5ITxApkSIPRiF8OTjJsvCXLDuHkcqLF19h9HZlN1imyyGO3ehCtTbqsmmj\r\n61ilWV4Y/18F/LATXY5HmNvokrS2a67rZZ8Lh0Oj0g+7MY0K4nqNOuE2plmNuhD19CPEMkAsf079\r\nOEli7ReZIUGWXulezDvJPOJyG/0A0p5ATmzp3GpPesPehzXm4vp+YYPldCC09L+Wjvw+LbVNF/f1\r\nMRNYStG1EEM5sWzXZk7D0MfL4O/CxEWRR5lJy8Q3c1ow8GyE4SNRs1pAo//BwVqR5yk4cBxJBGdU\r\ntk75WWkNw1hBqDT8oQzGggnM4lAZU0bYNMuSROlDCH3sF11WP+ruY6QNhWKVqxxOWvrAL3ModQi5\r\n0mHbEV9/Kw0yTa6R+8xYtDd05R2jntnErtKAWMR+OFXpZa76b5TJC4XsduCCG6D8o5GxPCJvIVNl\r\nxTzHtLjytN1R26i8wTp3OIdhMPWNRsZj/czNEx6/Qd3r/jvM9EcQBXm/rgAmKi0xSPWnVzO8rgXu\r\nlgLPLyztHhlY8bkuLjRYQ8abWJk0Lhb55OxAn72AOJgOZrGBs4Of8FeVKP7JNKSxGZQ6b1v98UTl\r\nwTTJ0Af1tdR0OVamFvnNLyN8MBBlyaRJN599g5+1/AAz0yqR7MfChRUrq+Di7dw1YxPrSakntww7\r\njhU6a6LivKhe1hbup213PDCJPDCkouHhz0e89rR1Rxsub5j0uGgXfK9UHeAL8NPGRY6AMlKtOdxD\r\nsMSRxfbgdwc+QutW4Nvz2DX1kwJj1QL8Pdqdoc1dG9EWwmqrnN/9tFqx5YMqT2BqHBu/DLDCOTvI\r\nK+Tryn5Ynivjh+kaJd6eiAeJwAqHVUS0WxgvNRYpJo6Ke9g4hL0rdMgA95ABS7jtBsYnXWBl/wAJ\r\nGJJ0aWCsYnhXbVzESVVHVRXke/8ci9S1yqkNoHw6oR0lzeYNadbWFdbelTpjhbmUOB7lsj14OPaL\r\nMj2CrXH//wAkRA3QPNY0S9NP+ltZGBVF1eIshxyfcUWrniDN9lY8clrrNHQxWG4TYXmOK24egCya\r\nq/OPbU5pH8Ibo4RrOVzIZecDcP+e5VYnSbgY9wS1saC9NYyhigsFrN6kEFeqlqC5xfHQd0oB3a0K\r\nZW5kc3RyZWFtCmVuZG9iagoxMiAwIG9iago8PCAvVHlwZSAvUGFnZSAvUGFyZW50IDEgMCBSIC9M\r\nYXN0TW9kaWZpZWQgKEQ6MjAxODA0MDMxNzEzMzErMDInMDAnKSAvUmVzb3VyY2VzIDIgMCBSIC9N\r\nZWRpYUJveCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQ3JvcEJv\r\neCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQmxlZWRCb3ggWzAu\r\nMDAwMDAwIDAuMDAwMDAwIDU5NS4yNzYwMDAgODQxLjg5MDAwMF0gL1RyaW1Cb3ggWzAuMDAwMDAw\r\nIDAuMDAwMDAwIDU5NS4yNzYwMDAgODQxLjg5MDAwMF0gL0FydEJveCBbMC4wMDAwMDAgMC4wMDAw\r\nMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQ29udGVudHMgMTMgMCBSIC9Sb3RhdGUgMCAvR3Jv\r\ndXAgPDwgL1R5cGUgL0dyb3VwIC9TIC9UcmFuc3BhcmVuY3kgL0NTIC9EZXZpY2VSR0IgPj4gL1Ba\r\nIDEgPj4KZW5kb2JqCjEzIDAgb2JqCjw8L0ZpbHRlciAvRmxhdGVEZWNvZGUgL0xlbmd0aCAxMDM4\r\nPj4gc3RyZWFtCnic7VnZcts2FH3XV9xHp2NDIBaC9FOTOk6aTjpNpDQPcaZDiaCElosNktVMfrY/\r\n4odeLrK8VLYW2pE74gwlCsTVBc65GwBKpKJ4wQwovMP7T/jyFb9CvN/gPem9GkL/1AGHElpfMIzg\r\n9bBHVxZkNwUvlr24UnD34eObudCdUTySzEaDW0/oAhzhEMUZ8zgCRkFxIn1HuRy4INSRSnigpEt8\r\n6btSwjiB/s8UTjL4sCvz3tgINjaqbrqgelRpF3OZoXbAVuFx4nHpUxeUL4lSSJEPwxC+HJxmWfhT\r\nlp3DUOfFi68wfLeyGyzT5RKXNbpQLUNdjDa6TnSS5YUN/tEgDjvR5fqEe40uRet5zXW9PBLSFdCo\r\nDMJupkYl8fxGnfSaqTmNuhD1HEWI5Rix/DEJTBybNCgyS8ZZcqV7YXeK+8QTDP0Akp5ETphyb7XH\r\nvUHvwxq2uL5fMHDcDoSW/tfSkd+npZ7TxX197ASWUnQtxFBBHOYx7jYMfbwc/1VYUxR5lNmkjAM7\r\npwUDz0YYPhI1qwU0+h8crBV5noID11VECk5V65SfdZrCwGgIdQq/a4uxYAIzE2prywibZlkc6/QQ\r\nwgD7RZfVj7r7CGlDIaNzncNpSx8EZQ5lGkKu07DtiK+/lRaZJtfIfWYssg1deceo54ywKg3IReyH\r\n01ibfDw9eqttXmiktwMf3ADm7w2N4xN1C5oqLeY55sWV7XZH50bVDdqFKwQMxtPApsi4SZ/59KQv\r\nblD3+ug9pvpjiMb5UVMCtBY+vbLwuhi4Wws8v7i0e2Rgyed5uNLgDRlvjbaJKRYJ5ewgPXsBZjzt\r\nz4yFs4Mf8FeVKf7OUkiM7Zdp3rYGo4lG2uIMfTC9lpsuR9rWIr8EZYQPFqIsnjT55nNg8bOW72Nq\r\nWiWSfV+4sGTlFVyitV07siadlOnk1sROjEZnrQy5qF7WM9ybbXc8cIU8cKSi4eGPR7z2tHVHG65v\r\nuPKFbFd8r3Qd4AsIksZFjoFyUi06vENw5LHD9+B3Bz5C61Xgs3nsmgZxgbFqAf4e7c7QFh5DtKV0\r\n2irn1yCplmx5v8oTmBpHNijHWOGcHeQV8nVpPyjPtQ3CZI0Sb0/Eg0RghcMrIto9jJcpFinWRMU9\r\nbBzC3hU6ZED4yIAjvXYH41NaYGX/AAkYktLSwkgbeF/tXJi4qqOqCvK34ByL1LXKqQ2gfDqhHSWN\r\niYY0Z+sKa+9KnbHCPUpcnwrVnjycBEWZHMPWuP9/AJKyBmgea5ql6af0W1lYHUXV4iyHHJ9xRauf\r\nIM32VjxzWus4dDFYwYh0fNeTN09AFs3VAcg2x7QP4Y1RwnNcIdWyAwJ4YNNyq7MkXI37kjKsaG+N\r\nY6BNoUHUuxTyStUSOLc4IPoX/hTdQwplbmRzdHJlYW0KZW5kb2JqCjE0IDAgb2JqCjw8IC9UeXBl\r\nIC9QYWdlIC9QYXJlbnQgMSAwIFIgL0xhc3RNb2RpZmllZCAoRDoyMDE4MDQwMzE3MTMzMSswMicw\r\nMCcpIC9SZXNvdXJjZXMgMiAwIFIgL01lZGlhQm94IFswLjAwMDAwMCAwLjAwMDAwMCA1OTUuMjc2\r\nMDAwIDg0MS44OTAwMDBdIC9Dcm9wQm94IFswLjAwMDAwMCAwLjAwMDAwMCA1OTUuMjc2MDAwIDg0\r\nMS44OTAwMDBdIC9CbGVlZEJveCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkw\r\nMDAwXSAvVHJpbUJveCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAv\r\nQXJ0Qm94IFswLjAwMDAwMCAwLjAwMDAwMCA1OTUuMjc2MDAwIDg0MS44OTAwMDBdIC9Db250ZW50\r\ncyAxNSAwIFIgL1JvdGF0ZSAwIC9Hcm91cCA8PCAvVHlwZSAvR3JvdXAgL1MgL1RyYW5zcGFyZW5j\r\neSAvQ1MgL0RldmljZVJHQiA+PiAvQW5ub3RzIFsgNSAwIFIgXSAvUFogMSA+PgplbmRvYmoKMTUg\r\nMCBvYmoKPDwvRmlsdGVyIC9GbGF0ZURlY29kZSAvTGVuZ3RoIDEwOTQ+PiBzdHJlYW0KeJztWVtz\r\nmzgUfvevOI/pTiwLJHHJ07Z10253upPW7vah6exgEDa7XBIB69n+2f6RPPQIcMilTnwhqbNjZkiw\r\nkDic7zs3cSgRNsUD5kDhLZ5/w+cv+C/A8zWe096LMQyODTAoodUB4xBejXt05YXm9YXny25cCrh9\r\n8eH1YtGtt3igNRu93HqLzsHgBrGZaToMAaNgMyJcw7YYME6oIWzugC0s4grXEgL8BAa/URhm8H5X\r\n9N7YCDY2qm6moHgUqVpd5igdcJQ7jDhMuNQC2xXEtpEiF8YBfD44zrLgZZadwVjmxbMvMH67shss\r\nk2URy6xloVgTZZm0ljWUSZYXyvsmgR92IstyCXNqWTat9FrIet7nwuJQi/SCblSjgjhuLU44tWpG\r\nLS5AOf0QsfQRy18TL4rjKPWKTBE/Sy5lt3ZnM5c43EQ/gKQnkBPTtm6Mx71R7/0atri+X5hgWB0s\r\nWvqspW9+l5RKp/O75qgpLKXoSoihnBimYzKrZujDhf9PoaKiyMNMJWXsqQUtGHg2wvCBqFktoNEf\r\ncLBW5HkMDizLJoIzajdO+UmmKYwiCYFM4U+pMBZMYR4FUqkyxKF5FscyPYTAw3nhhf5RTZ8gbbgo\r\nkrnM4bihD7wyhzINIJdp0EzE219LhUyTK+Q+MRbNDV15x6hnJjF1GhBt7Id3UezP+m+kyguJ5Hbg\r\ngRuA/LOBMVxi3wBGJ8U8x6y4stXuqG7UvkY6tziHkT/zVIqMR+kTV0+4/Bp1r/rvMNEfQejn/aoA\r\nSCr7nl3ad1UI3K4Dnl5M2j0qsNxzHNxlsJqKN5FUSVS0yeT0ID19BpE/G8wjBacHv+AvnSX+zVJI\r\nIjUo07wZ9SZTmfuzOEMPTK/kpYuJVNWS370yxAsFYRZP61zzyVP4t1o/wLS0Shz7uXBhuco0XLyx\r\nXDVRUTot0+kNxYaRRFeNZZQX+mal4d5su+OB2cgDQypqHv56wGNPW3e04d6G2S4XzW7vhawCfAFe\r\nUrvIEVBG9IbDOQRDHBlsD3534CO0jgbfXMSumRcXGKta8Pdod4Y2d0xEWwijqXH+8BK9XcsHOk9g\r\napwor/Sxwjk9yDXyVVk/Ks+k8oJkjQJvT8S9RGCFwzQRzfeL5ykWKSoKizvYOIS9K3TIAHeRAUM4\r\nzdeLj2mBlf09JGBISksFExnhbrfATU+s6yhdQZ54Z1ikrlVObQDl4y3aUdJMXpNmbF1h7V2pM1aY\r\nQ4nlUm43XYehV5TJEWyN+/8HICEqgBaxpt6afky/loWSYag3ZznkeI07WvkIabZtZ64MU/tA9kNl\r\nTd2C0gOG/kZUPVJrepLNpZIBTP6D8cuT4TEG0Pl8Tgr/LAhJpqZtwNymG9JbsX+2Vmu3BZ+bRBiu\r\n5Yjr3Zx2WDdztmk532c/GPUcw+LCXtbsgDs/wG7VFRMOcQU1sT6/8RYjGRUSRPXNRVyKWgLmFuR+\r\nB8lWCvoKZW5kc3RyZWFtCmVuZG9iagoxIDAgb2JqCjw8IC9UeXBlIC9QYWdlcyAvS2lkcyBbIDYg\r\nMCBSIDggMCBSIDEwIDAgUiAxMiAwIFIgMTQgMCBSIF0gL0NvdW50IDUgPj4KZW5kb2JqCjMgMCBv\r\nYmoKPDwvVHlwZSAvRm9udCAvU3VidHlwZSAvVHlwZTEgL0Jhc2VGb250IC9IZWx2ZXRpY2EgL05h\r\nbWUgL0YxIC9FbmNvZGluZyAvV2luQW5zaUVuY29kaW5nID4+CmVuZG9iago0IDAgb2JqCjw8L1R5\r\ncGUgL0ZvbnQgL1N1YnR5cGUgL1R5cGUxIC9CYXNlRm9udCAvSGVsdmV0aWNhLUJvbGQgL05hbWUg\r\nL0YyIC9FbmNvZGluZyAvV2luQW5zaUVuY29kaW5nID4+CmVuZG9iagoxNiAwIG9iago8PC9UeXBl\r\nIC9YT2JqZWN0IC9TdWJ0eXBlIC9JbWFnZSAvV2lkdGggMjYwIC9IZWlnaHQgMTM1IC9Db2xvclNw\r\nYWNlIC9EZXZpY2VSR0IgL0JpdHNQZXJDb21wb25lbnQgOCAvRmlsdGVyIC9EQ1REZWNvZGUgL0xl\r\nbmd0aCAxMzgzMCA+PiBzdHJlYW0K/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAMCAgMCAgMDAwME\r\nAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBD\r\nAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQU\r\nFBQUFBQUFBT/wgARCACHAQQDAREAAhEBAxEB/8QAHAABAAICAwEAAAAAAAAAAAAAAAYHBAgBAgUD\r\n/8QAGwEBAAIDAQEAAAAAAAAAAAAAAAQFAgMGAQf/2gAMAwEAAhADEAAAAdovMKWgcn4muIAAAAAA\r\nABle7LDlXn19yAAAA5Mv3dLd9lUUHmKFruMAAAHL3l71YgAAC/rLtrZm9J5WEbNy25HuwAAdfPPJ\r\nwjeRrjSXdOp2Dy9KQOSAAF1z+ul2609DLdmZbdVKf5vy9xPNIAA2StO+sOVd6y1PAbFWnc4/mvwd\r\nUOcSbeMaa/5eYyzfZQiPUV1Fo7amdLnZ7anhc3SkDkgAJnvt9l7bvs3LaOvnkHj1Mg2TdbKvgorp\r\nrAANkrTvrElXeuVXw2w9p29I13J+djovyy7Khq7jeni+bHsaGruMy8ttszOlzs9tTwubpSByQA5e\r\n7g3f1H1dkgAceec++1LB5ugq7iQANkrTvrFl3lAVnF3/AGfaUZW8h29yuaf1FIV/Jcvbsn9ZTsLl\r\nsPHVbMzpc7PbU8Lm6UgckAPYzmbY3P0nNz2cvRwHnL3xdcXU2m+aYmOoAbJWnfWLLvKig8zbs7pq\r\nrhc7DtFXsBZ9pTEDlfh5hdth1lEVvHSPdPnG+3zs9tTwubpSByQFny+jtGX0PvbJvs7ZIAA6eeap\r\n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\r\nkU8ToP6ru59H1+CF4fCf7Dtz63u8NHwvPpmEKEnH3ZYKtYtKQ8jnS76c4fdDA9gDquTAAnzLOa4v\r\n0NrwENCICq11+EJrMU0Vee5H7yYhRU6MTgDbpHDO4s9y/wDzxOg/qu7nGlyMCXYo3j/IwVah6hHe\r\nPDglDSdwSgxn9y3c5JsB+TPre7w0fC8+5iNApkrouleqZPzCtDLyq7RrYF5wW6ElEUZ6A/rB5JMd\r\nnUemjekqbBOhJvBMeiT+/wAITEfXCujjWktgfxldENHa5S7V7uBMYaMA8jsYy0EgT7hrEN4LqVl6\r\nKu84FGHc0UVhrebIDprSLUDFL648OERJEFyhEZ2xQFBBE0oWXcz63u8NAWxBscA7IF9L2/B8Z0RM\r\nWHgpDTUWP7T8KqAMUAivQkHKL2/EsrLpTL/PATIitF9kAC8vsz4dnwD/AFgQFQKI8iYtZ14rsX8C\r\nH+Ftttttttttttttttts/ZgAROEcBDUCIeyr+38VOnTp06dOnTLjNEsenDhLFLnG08Rrg9Ox4f/Z\r\nCmVuZHN0cmVhbQplbmRvYmoKMiAwIG9iago8PCAvUHJvY1NldCBbL1BERiAvVGV4dCAvSW1hZ2VC\r\nIC9JbWFnZUMgL0ltYWdlSV0gL0ZvbnQgPDwgL0YxIDMgMCBSIC9GMiA0IDAgUiA+PiAvWE9iamVj\r\ndCA8PCAvSTAgMTYgMCBSID4+ID4+CmVuZG9iago1IDAgb2JqCjw8L1R5cGUgL0Fubm90IC9TdWJ0\r\neXBlIC9MaW5rIC9SZWN0IFsyLjgzNTAwMCAxLjAwMDAwMCAxOS4wMDUwMDAgMi4xNTYwMDBdIC9Q\r\nIDE0IDAgUiAvTk0gKDAwMDUtMDAwMCkgL00gKEQ6MjAxODA0MDMxNzEzMzErMDInMDAnKSAvRiA0\r\nIC9Cb3JkZXIgWzAgMCAwXSAvQSA8PC9TIC9VUkkgL1VSSSAoaHR0cDovL3d3dy50Y3BkZi5vcmcp\r\nPj4gL0ggL0k+PgplbmRvYmoKMTcgMCBvYmoKPDwgL1RpdGxlICj+/wBJAG4AZgBvAHIAbQBhAHQA\r\naQBvAG4AZQBuACAA/ABiAGUAcgAgAFIA/ABjAGsAdAByAGkAdAB0AHMAcgBlAGMAaAB0ACAAdQBu\r\nAGQAIABSAPwAYwBrAHQAcgBpAHQAdABzAGYAbwByAG0AdQBsAGEAcikgL0F1dGhvciAo/v8ARgBv\r\nAG8AZABDAG8AbwBwACAAVABlAHMAdCkgL0NyZWF0b3IgKP7/AEYAbwBvAGQAQwBvAG8AcAAgAFQA\r\nZQBzAHQpIC9Qcm9kdWNlciAo/v8AVABDAFAARABGACAANgAuADIALgAxADcAIABcKABoAHQAdABw\r\nADoALwAvAHcAdwB3AC4AdABjAHAAZABmAC4AbwByAGcAXCkpIC9DcmVhdGlvbkRhdGUgKEQ6MjAx\r\nODA0MDMxNzEzMzErMDInMDAnKSAvTW9kRGF0ZSAoRDoyMDE4MDQwMzE3MTMzMSswMicwMCcpIC9U\r\ncmFwcGVkIC9GYWxzZSA+PgplbmRvYmoKMTggMCBvYmoKPDwgL1R5cGUgL01ldGFkYXRhIC9TdWJ0\r\neXBlIC9YTUwgL0xlbmd0aCA0MzEwID4+IHN0cmVhbQo8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9\r\nIlc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/Pgo8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5z\r\nOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA0LjIuMS1jMDQzIDUyLjM3MjcyOCwgMjAw\r\nOS8wMS8xOC0xNTowODowNCI+Cgk8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3Jn\r\nLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgoJCTxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0\r\nPSIiIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyI+CgkJCTxkYzpm\r\nb3JtYXQ+YXBwbGljYXRpb24vcGRmPC9kYzpmb3JtYXQ+CgkJCTxkYzp0aXRsZT4KCQkJCTxyZGY6\r\nQWx0PgoJCQkJCTxyZGY6bGkgeG1sOmxhbmc9IngtZGVmYXVsdCI+SW5mb3JtYXRpb25lbiDDvGJl\r\nciBSw7xja3RyaXR0c3JlY2h0IHVuZCBSw7xja3RyaXR0c2Zvcm11bGFyPC9yZGY6bGk+CgkJCQk8\r\nL3JkZjpBbHQ+CgkJCTwvZGM6dGl0bGU+CgkJCTxkYzpjcmVhdG9yPgoJCQkJPHJkZjpTZXE+CgkJ\r\nCQkJPHJkZjpsaT5Gb29kQ29vcCBUZXN0PC9yZGY6bGk+CgkJCQk8L3JkZjpTZXE+CgkJCTwvZGM6\r\nY3JlYXRvcj4KCQkJPGRjOmRlc2NyaXB0aW9uPgoJCQkJPHJkZjpBbHQ+CgkJCQkJPHJkZjpsaSB4\r\nbWw6bGFuZz0ieC1kZWZhdWx0Ij48L3JkZjpsaT4KCQkJCTwvcmRmOkFsdD4KCQkJPC9kYzpkZXNj\r\ncmlwdGlvbj4KCQkJPGRjOnN1YmplY3Q+CgkJCQk8cmRmOkJhZz4KCQkJCQk8cmRmOmxpPjwvcmRm\r\nOmxpPgoJCQkJPC9yZGY6QmFnPgoJCQk8L2RjOnN1YmplY3Q+CgkJPC9yZGY6RGVzY3JpcHRpb24+\r\nCgkJPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRv\r\nYmUuY29tL3hhcC8xLjAvIj4KCQkJPHhtcDpDcmVhdGVEYXRlPjIwMTgtMDQtMDNUMTc6MTM6MzEr\r\nMDI6MDA8L3htcDpDcmVhdGVEYXRlPgoJCQk8eG1wOkNyZWF0b3JUb29sPkZvb2RDb29wIFRlc3Q8\r\nL3htcDpDcmVhdG9yVG9vbD4KCQkJPHhtcDpNb2RpZnlEYXRlPjIwMTgtMDQtMDNUMTc6MTM6MzEr\r\nMDI6MDA8L3htcDpNb2RpZnlEYXRlPgoJCQk8eG1wOk1ldGFkYXRhRGF0ZT4yMDE4LTA0LTAzVDE3\r\nOjEzOjMxKzAyOjAwPC94bXA6TWV0YWRhdGFEYXRlPgoJCTwvcmRmOkRlc2NyaXB0aW9uPgoJCTxy\r\nZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnBkZj0iaHR0cDovL25zLmFkb2JlLmNv\r\nbS9wZGYvMS4zLyI+CgkJCTxwZGY6S2V5d29yZHM+PC9wZGY6S2V5d29yZHM+CgkJCTxwZGY6UHJv\r\nZHVjZXI+VENQREYgNi4yLjE3IChodHRwOi8vd3d3LnRjcGRmLm9yZyk8L3BkZjpQcm9kdWNlcj4K\r\nCQk8L3JkZjpEZXNjcmlwdGlvbj4KCQk8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxu\r\nczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyI+CgkJCTx4bXBNTTpEb2N1\r\nbWVudElEPnV1aWQ6ZmM1NmM1ZDctODIzNS0wODk2LTQ2YTgtMDk4MjQyNmE0MTdhPC94bXBNTTpE\r\nb2N1bWVudElEPgoJCQk8eG1wTU06SW5zdGFuY2VJRD51dWlkOmZjNTZjNWQ3LTgyMzUtMDg5Ni00\r\nNmE4LTA5ODI0MjZhNDE3YTwveG1wTU06SW5zdGFuY2VJRD4KCQk8L3JkZjpEZXNjcmlwdGlvbj4K\r\nCQk8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczpwZGZhRXh0ZW5zaW9uPSJodHRw\r\nOi8vd3d3LmFpaW0ub3JnL3BkZmEvbnMvZXh0ZW5zaW9uLyIgeG1sbnM6cGRmYVNjaGVtYT0iaHR0\r\ncDovL3d3dy5haWltLm9yZy9wZGZhL25zL3NjaGVtYSMiIHhtbG5zOnBkZmFQcm9wZXJ0eT0iaHR0\r\ncDovL3d3dy5haWltLm9yZy9wZGZhL25zL3Byb3BlcnR5IyI+CgkJCTxwZGZhRXh0ZW5zaW9uOnNj\r\naGVtYXM+CgkJCQk8cmRmOkJhZz4KCQkJCQk8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNl\r\nIj4KCQkJCQkJPHBkZmFTY2hlbWE6bmFtZXNwYWNlVVJJPmh0dHA6Ly9ucy5hZG9iZS5jb20vcGRm\r\nLzEuMy88L3BkZmFTY2hlbWE6bmFtZXNwYWNlVVJJPgoJCQkJCQk8cGRmYVNjaGVtYTpwcmVmaXg+\r\ncGRmPC9wZGZhU2NoZW1hOnByZWZpeD4KCQkJCQkJPHBkZmFTY2hlbWE6c2NoZW1hPkFkb2JlIFBE\r\nRiBTY2hlbWE8L3BkZmFTY2hlbWE6c2NoZW1hPgoJCQkJCTwvcmRmOmxpPgoJCQkJCTxyZGY6bGkg\r\ncmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgoJCQkJCQk8cGRmYVNjaGVtYTpuYW1lc3BhY2VVUkk+\r\naHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLzwvcGRmYVNjaGVtYTpuYW1lc3BhY2VVUkk+\r\nCgkJCQkJCTxwZGZhU2NoZW1hOnByZWZpeD54bXBNTTwvcGRmYVNjaGVtYTpwcmVmaXg+CgkJCQkJ\r\nCTxwZGZhU2NoZW1hOnNjaGVtYT5YTVAgTWVkaWEgTWFuYWdlbWVudCBTY2hlbWE8L3BkZmFTY2hl\r\nbWE6c2NoZW1hPgoJCQkJCQk8cGRmYVNjaGVtYTpwcm9wZXJ0eT4KCQkJCQkJCTxyZGY6U2VxPgoJ\r\nCQkJCQkJCTxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgoJCQkJCQkJCQk8cGRmYVBy\r\nb3BlcnR5OmNhdGVnb3J5PmludGVybmFsPC9wZGZhUHJvcGVydHk6Y2F0ZWdvcnk+CgkJCQkJCQkJ\r\nCTxwZGZhUHJvcGVydHk6ZGVzY3JpcHRpb24+VVVJRCBiYXNlZCBpZGVudGlmaWVyIGZvciBzcGVj\r\naWZpYyBpbmNhcm5hdGlvbiBvZiBhIGRvY3VtZW50PC9wZGZhUHJvcGVydHk6ZGVzY3JpcHRpb24+\r\nCgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6bmFtZT5JbnN0YW5jZUlEPC9wZGZhUHJvcGVydHk6bmFt\r\nZT4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTp2YWx1ZVR5cGU+VVJJPC9wZGZhUHJvcGVydHk6dmFs\r\ndWVUeXBlPgoJCQkJCQkJCTwvcmRmOmxpPgoJCQkJCQkJPC9yZGY6U2VxPgoJCQkJCQk8L3BkZmFT\r\nY2hlbWE6cHJvcGVydHk+CgkJCQkJPC9yZGY6bGk+CgkJCQkJPHJkZjpsaSByZGY6cGFyc2VUeXBl\r\nPSJSZXNvdXJjZSI+CgkJCQkJCTxwZGZhU2NoZW1hOm5hbWVzcGFjZVVSST5odHRwOi8vd3d3LmFp\r\naW0ub3JnL3BkZmEvbnMvaWQvPC9wZGZhU2NoZW1hOm5hbWVzcGFjZVVSST4KCQkJCQkJPHBkZmFT\r\nY2hlbWE6cHJlZml4PnBkZmFpZDwvcGRmYVNjaGVtYTpwcmVmaXg+CgkJCQkJCTxwZGZhU2NoZW1h\r\nOnNjaGVtYT5QREYvQSBJRCBTY2hlbWE8L3BkZmFTY2hlbWE6c2NoZW1hPgoJCQkJCQk8cGRmYVNj\r\naGVtYTpwcm9wZXJ0eT4KCQkJCQkJCTxyZGY6U2VxPgoJCQkJCQkJCTxyZGY6bGkgcmRmOnBhcnNl\r\nVHlwZT0iUmVzb3VyY2UiPgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5OmNhdGVnb3J5PmludGVybmFs\r\nPC9wZGZhUHJvcGVydHk6Y2F0ZWdvcnk+CgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6ZGVzY3JpcHRp\r\nb24+UGFydCBvZiBQREYvQSBzdGFuZGFyZDwvcGRmYVByb3BlcnR5OmRlc2NyaXB0aW9uPgoJCQkJ\r\nCQkJCQk8cGRmYVByb3BlcnR5Om5hbWU+cGFydDwvcGRmYVByb3BlcnR5Om5hbWU+CgkJCQkJCQkJ\r\nCTxwZGZhUHJvcGVydHk6dmFsdWVUeXBlPkludGVnZXI8L3BkZmFQcm9wZXJ0eTp2YWx1ZVR5cGU+\r\nCgkJCQkJCQkJPC9yZGY6bGk+CgkJCQkJCQkJPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJj\r\nZSI+CgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6Y2F0ZWdvcnk+aW50ZXJuYWw8L3BkZmFQcm9wZXJ0\r\neTpjYXRlZ29yeT4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTpkZXNjcmlwdGlvbj5BbWVuZG1lbnQg\r\nb2YgUERGL0Egc3RhbmRhcmQ8L3BkZmFQcm9wZXJ0eTpkZXNjcmlwdGlvbj4KCQkJCQkJCQkJPHBk\r\nZmFQcm9wZXJ0eTpuYW1lPmFtZDwvcGRmYVByb3BlcnR5Om5hbWU+CgkJCQkJCQkJCTxwZGZhUHJv\r\ncGVydHk6dmFsdWVUeXBlPlRleHQ8L3BkZmFQcm9wZXJ0eTp2YWx1ZVR5cGU+CgkJCQkJCQkJPC9y\r\nZGY6bGk+CgkJCQkJCQkJPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CgkJCQkJCQkJ\r\nCTxwZGZhUHJvcGVydHk6Y2F0ZWdvcnk+aW50ZXJuYWw8L3BkZmFQcm9wZXJ0eTpjYXRlZ29yeT4K\r\nCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTpkZXNjcmlwdGlvbj5Db25mb3JtYW5jZSBsZXZlbCBvZiBQ\r\nREYvQSBzdGFuZGFyZDwvcGRmYVByb3BlcnR5OmRlc2NyaXB0aW9uPgoJCQkJCQkJCQk8cGRmYVBy\r\nb3BlcnR5Om5hbWU+Y29uZm9ybWFuY2U8L3BkZmFQcm9wZXJ0eTpuYW1lPgoJCQkJCQkJCQk8cGRm\r\nYVByb3BlcnR5OnZhbHVlVHlwZT5UZXh0PC9wZGZhUHJvcGVydHk6dmFsdWVUeXBlPgoJCQkJCQkJ\r\nCTwvcmRmOmxpPgoJCQkJCQkJPC9yZGY6U2VxPgoJCQkJCQk8L3BkZmFTY2hlbWE6cHJvcGVydHk+\r\nCgkJCQkJPC9yZGY6bGk+CgkJCQk8L3JkZjpCYWc+CgkJCTwvcGRmYUV4dGVuc2lvbjpzY2hlbWFz\r\nPgoJCTwvcmRmOkRlc2NyaXB0aW9uPgoJPC9yZGY6UkRGPgo8L3g6eG1wbWV0YT4KPD94cGFja2V0\r\nIGVuZD0idyI/PgplbmRzdHJlYW0KZW5kb2JqCjE5IDAgb2JqCjw8IC9UeXBlIC9DYXRhbG9nIC9W\r\nZXJzaW9uIC8xLjcgL1BhZ2VzIDEgMCBSIC9OYW1lcyA8PCA+PiAvVmlld2VyUHJlZmVyZW5jZXMg\r\nPDwgL0RpcmVjdGlvbiAvTDJSID4+IC9QYWdlTGF5b3V0IC9TaW5nbGVQYWdlIC9QYWdlTW9kZSAv\r\nVXNlTm9uZSAvT3BlbkFjdGlvbiBbNiAwIFIgL0ZpdEggbnVsbF0gL01ldGFkYXRhIDE4IDAgUiA+\r\nPgplbmRvYmoKeHJlZgowIDIwCjAwMDAwMDAwMDAgNjU1MzUgZiAKMDAwMDAwODkxNSAwMDAwMCBu\r\nIAowMDAwMDIzMjE3IDAwMDAwIG4gCjAwMDAwMDkwMDEgMDAwMDAgbiAKMDAwMDAwOTEwNyAwMDAw\r\nMCBuIAowMDAwMDIzMzQyIDAwMDAwIG4gCjAwMDAwMDAwMTUgMDAwMDAgbiAKMDAwMDAwMDQ2NSAw\r\nMDAwMCBuIAowMDAwMDAyNzU5IDAwMDAwIG4gCjAwMDAwMDMyMDkgMDAwMDAgbiAKMDAwMDAwNDE1\r\nMCAwMDAwMCBuIAowMDAwMDA0NjAyIDAwMDAwIG4gCjAwMDAwMDU3MTcgMDAwMDAgbiAKMDAwMDAw\r\nNjE2OSAwMDAwMCBuIAowMDAwMDA3Mjc5IDAwMDAwIG4gCjAwMDAwMDc3NDkgMDAwMDAgbiAKMDAw\r\nMDAwOTIxOCAwMDAwMCBuIAowMDAwMDIzNTU5IDAwMDAwIG4gCjAwMDAwMjM5NjQgMDAwMDAgbiAK\r\nMDAwMDAyODM1NyAwMDAwMCBuIAp0cmFpbGVyCjw8IC9TaXplIDIwIC9Sb290IDE5IDAgUiAvSW5m\r\nbyAxNyAwIFIgL0lEIFsgPGZjNTZjNWQ3ODIzNTA4OTY0NmE4MDk4MjQyNmE0MTdhPiA8ZmM1NmM1\r\nZDc4MjM1MDg5NjQ2YTgwOTgyNDI2YTQxN2E+IF0gPj4Kc3RhcnR4cmVmCjI4NTY2CiUlRU9GCg==\r\n\r\n\r\n--db2d6b9081ba5a8c649dce962cb65c53\r\nContent-Disposition: attachment; filename=\"Bestelluebersicht.pdf\"\r\nContent-Type: application/pdf\r\nContent-Transfer-Encoding: base64\r\n\r\nJVBERi0xLjcKJeLjz9MKNiAwIG9iago8PCAvVHlwZSAvUGFnZSAvUGFyZW50IDEgMCBSIC9MYXN0\r\nTW9kaWZpZWQgKEQ6MjAxODA0MDMxNzEzMzErMDInMDAnKSAvUmVzb3VyY2VzIDIgMCBSIC9NZWRp\r\nYUJveCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQ3JvcEJveCBb\r\nMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQmxlZWRCb3ggWzAuMDAw\r\nMDAwIDAuMDAwMDAwIDU5NS4yNzYwMDAgODQxLjg5MDAwMF0gL1RyaW1Cb3ggWzAuMDAwMDAwIDAu\r\nMDAwMDAwIDU5NS4yNzYwMDAgODQxLjg5MDAwMF0gL0FydEJveCBbMC4wMDAwMDAgMC4wMDAwMDAg\r\nNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQ29udGVudHMgNyAwIFIgL1JvdGF0ZSAwIC9Hcm91cCA8\r\nPCAvVHlwZSAvR3JvdXAgL1MgL1RyYW5zcGFyZW5jeSAvQ1MgL0RldmljZVJHQiA+PiAvUFogMSA+\r\nPgplbmRvYmoKNyAwIG9iago8PC9GaWx0ZXIgL0ZsYXRlRGVjb2RlIC9MZW5ndGggMTU3Nj4+IHN0\r\ncmVhbQp4nO1cS4/bNhC++1fMsQVshqT4EPfUpEm2TVEg6W5PQQ6KzbXdteysLCdFTv2nvfRn5FBa\r\n2rXesvWwLG/WgGFB9HA433wzQ4mUMOISmw98AQxvzPcveP/B/EzM99J8p4MX1/DsNQGCEQ4+cH0D\r\nr64H+GBBmhS8K2rYKcge/HH5IJQZxZFkag2umtAdEEaQtCi1LQMYBmkhrogUFjCOLM4si4DkAimu\r\nBOcwduHZrxheruBdX+yuTYLapGrnL0a9UelFtnwx2sGcZbaFbIsrLEAqjqQ0LlJwPYH3P7xerSY/\r\nr1af4Fqv/R8/wPWbg8OgSJdAgoa6jFpqdFEc6nqp3dXa95x/NbBhK7qEQpYd6pI4sOtB1/MR44JB\r\nqNKZtGMa5shWoTpuh6aRUN3E6BndGCzHBsufXGe+WMyXjr/y0Hjl7nRHvJOWQjajJg7AHXDjEypF\r\n6vxicDV4V4GLKdKRaPQ0JvhwdpJD/VgMwE48e+BNm3Y+iDIBtyUSkhMlA3jv/zMSFPHtEQVPwxU0\r\nVDg4LhqWwDtzjP8CcxioXQcjwhBT28/Wmhtoil41fX1HL8aFyBiL8GOhV01f79HDkTmchObwbaXd\r\nWXNfo0hL6FXS13f04pHUBXrV9PUdPZVnDJX4WOhV09d39FjHgfuY4jaehYTVbdbbr6/v6MWzUBfo\r\nVdPXd/RUnjHdZL0D9PUdPdZx4D6muI1nIclDa0RHWW+/vr6jF89CXaBXTV/f0VN5xnST9Q7Qd3r0\r\nMLKxtJmVc5DMep0E7nnFbTl2RwHDhdxbUAuDBuM4p8VNnI86WvQev3gWj91s66Rq7NfXd/TiWbwL\r\n9Krp6zt6Ks+YbqrGAfr6jl7+XfIuqkYnzCtvDpY1aJ0Vy6xQYV+FayvlXoPp4K7sP94UiheRokVQ\r\nbCOsBLVEuIb0Qq99vVhsllP4vFoGS1dwtfmkPWfizpcPi0iHLZP2zlihrISxU+3/58+nPjguYNPE\r\nELGHQPgFsWKWFi6olgFQe8lsX7d7Uc92+eCMSpoKhU7rQS4RwxaWsdVcuNTut7Ue/aK9gLvaa+68\r\nGiifGhnGkEohs13nXq81sGpx2z/bLJrwOjPGwtV45nhL4/Gqaal/5hGVcN2r0e/OfHEBN+P1KFjT\r\nn2p3ow3DZzuGB6v72cX9vQC0DU+TXHYvZB9bpqtR3GfMTE9ls6vGI8/RV5PLpeOsz3IRXa/aSFpq\r\ne70abo5ZfnVmiwRzjzHyJ6/keEWRIq+89VaTza3/5JZTuMUSJX7R8/WTV06SwnBhDnt74ywnJ/JK\r\nV+WzSKacE8drrT9Vy7ekfMmhDjb7eqwfTYO7ATGmmA5hRCRGgtDEX8buvg7exfsgAslwy2e8hyKH\r\n9ysoo5gUAtmc7GKS/h0LyPLNq01jslUu1YvCeraVj7ONaUXKKc89f74ez1bjWw0XcOV/G9+2kDbL\r\nzWjfRYnw4zZS2eCpFH6mB2GnezgLT8dnKun4G5rr2H/aicETOpgaKatZdj1f98anPCn3kiE+qXuP\r\nUZDb7rG1HCONHGmWYzoo8cciYcRBzkIO8nsOtlTiu04vj6vEp5zy23L1ceFsxjNT4ImRmJ57gd8+\r\n/tNwfn2+FSBe4FOOxkNmf78F/qnWtVfr9rWeB6f6Y0X+jaKW4oXIvGTWBJqDk2XGpJSkQsEU5xDJ\r\nHqdZRsN7iyxMs9aQJNPsKVPTsUhFMaIs47oWSaXy+n/kpIpfvaVIlbl66xWpvs8qGJ90mgu+yvFQ\r\na3tPkuwjmRuHBT0nZankKJhHHSTbt1CJIoXaSHK+i5RLvXZcP7uTqBI5z3NmPFIckWzuOz4LjV5R\r\nmHP3sFAhgQ8V7RkJ45OAFAtZehJwBiysk12bbCDKCh30JEeNPP7U2lZr77bH9m5TIMd4+56fXSZ4\r\nOdcQbALR8DnYBjjTS1jPxzOYL28Xm/X8s4Y/3bXjfzVtG+2hCnsCn3xR7gsmlfEFody+36C59GfO\r\nwtfLJOQXgIeUVZhe10Z+cOCbuCq9JC4aLNve+TQX4zz5Xqjo9Pa1UE02i+4DnDJkE8G4TDyU8O2j\r\nIb5hvA/l274bjUFRpIJJSHoQV3ruazPT2D4WsXs8oAjLBu/M+h8Qih/RCmVuZHN0cmVhbQplbmRv\r\nYmoKOCAwIG9iago8PCAvVHlwZSAvUGFnZSAvUGFyZW50IDEgMCBSIC9MYXN0TW9kaWZpZWQgKEQ6\r\nMjAxODA0MDMxNzEzMzErMDInMDAnKSAvUmVzb3VyY2VzIDIgMCBSIC9NZWRpYUJveCBbMC4wMDAw\r\nMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQ3JvcEJveCBbMC4wMDAwMDAgMC4w\r\nMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQmxlZWRCb3ggWzAuMDAwMDAwIDAuMDAwMDAw\r\nIDU5NS4yNzYwMDAgODQxLjg5MDAwMF0gL1RyaW1Cb3ggWzAuMDAwMDAwIDAuMDAwMDAwIDU5NS4y\r\nNzYwMDAgODQxLjg5MDAwMF0gL0FydEJveCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4\r\nNDEuODkwMDAwXSAvQ29udGVudHMgOSAwIFIgL1JvdGF0ZSAwIC9Hcm91cCA8PCAvVHlwZSAvR3Jv\r\ndXAgL1MgL1RyYW5zcGFyZW5jeSAvQ1MgL0RldmljZVJHQiA+PiAvUFogMSA+PgplbmRvYmoKOSAw\r\nIG9iago8PC9GaWx0ZXIgL0ZsYXRlRGVjb2RlIC9MZW5ndGggMTQyND4+IHN0cmVhbQp4nO1cSW/b\r\nRhS+61e8YwtYk9mH41PremkDFIhr9RTkwEq0pIaSYop2ipz6T3vpz8ihI1ILKS4SV1GpBQgmSM37\r\n5n1vmeUNjZFQ2HzgM2B4a75/wvsP5s/IfO/Md9y7GsCbWwIEIxx8YPAIN4MeProhjTd8ynqwBUhe\r\n/Ha3aZToRUNtSnWuWKMnIJwgxSi1mCEMg2JIaKIkAy4QE5wxAkpIpIWWQsBwBm9+wXC9gPuu6F3a\r\nCUo7VT0/MfAG0tvp8tmgg7nLLYYsJjSWoLRAShkTaRiM4P13t4vF6KfF4hMMnKX//QcYvD06DLKw\r\nJJI0xDKw1GBRHGJdO7PF0vfsfxzgF7VgSY2YFWIpHOi1wfqxz4XkEELao3pUwwJZOoQTVqgaCeFG\r\nBqf/aLgcGi5/mNlT153ObX/hoeFitsXe+Z1iGlmcmjiAWU8Ym1Al9+67vYfefQFf3HM6sus9jTTc\r\n3B2luH4kBmDbPHnhjasK7+0ygbAUkkoQrQJ617/pC6P36oqA58ADVATsNcsGk3irjiChOgL0VkCf\r\ncMT16rPS5hGqslcMr+vsRXxhpwwjoin2iuF1nj28U0eyjTo8os16jCI1sVcIr+vsRSOpDfaK4XWd\r\nPZ2mDFW4KfaK4XWdPd5y4H5LcRvNQkqE2siWst5hvK6zF81CbbBXDK/r7Ok0ZdrJekfgnZ49jCys\r\nLM5SLuJZr5XAPa+4zeeuETJmkLoYcw0bXOCUJ7PY/Z0gt/P8RbN4ZNnZyqhxGK/r7EWzeBvsFcPr\r\nOns6TZl2Ro0j8LrOXvp+URujRiuel/842OCjZfbuk40yZWXuMuZbDca9p7zfeGPI3k7dlQOwhbCW\r\nlMlwN/XKWfqO6z7Px/CymAebuPDw/Mnx7NFsOt9spx5XMOicslKzmLJjx//Xn459sGeAzSOOiHUB\r\nRFwSFtE0s7SQR0DpzeNDYg+ynhS5MUYhpMxGp7WgUIhjhlWkrgG3rjNdDif9nx0vcF7Hq269EjSf\r\nmhrOkd6jZlXyWS4d4MUCt3u6MRozOzfKwsNwYntzY/Gieal76hEdM91N/1d76l7C43DZD8tbaw+f\r\nbD08KHQl61wHCaibnirJbN3IarpNW71Yp8yEpLzpVeWep+CV9OXcfpb3crlbsFpIMb1asIZ14vkX\r\ne+LGPLeJnr9aJcUqmmRZ5Z23GD1/9F/NcgqzMJljFzMEvFrlJCkMZ+awd4/2fHQiq7Q1fGa1yfeJ\r\n5p6Wn6qla5JfcyjDzSGJ5aOp99QjRhUjEPpEYSQJjf1kODsk4D4qg0ikwtNPUQlZBu9WUO5iUkpk\r\nCbKNSfFXJCDzz3FVjclafalcFJbTLb+fdUwr9oxy5dn+56/B6qWGdJnf/fpNEws7SyKtq4UdR4Li\r\nfQlnYeHoDGXPxERcUA5/1xN8J7QwFYhbCesctG+bmjYxKNUtsblUeC4+1R0t0ieQNcULYWnZrAo1\r\nR2fLhEp7LQViyQlSvWuOFvKs4OGaQ2Tl2VPmpqa8inIkLWvfdvV5FWVp8o/xqvSWRyTQNLltWub/\r\nOZZE524MlzZdsa3tuMf0qc5xtn3Je96mTBLLTH91FTIaymKRRRrHSAmxTWJ3ztKe+ck6XSHnPM/5\r\nZV8LRGTCos17ocGVSdzjvNBM9c/VCaND6Z4XJofSM3DDMum1Sn0u2eiok5IlEvm397Rzx0A6V/sW\r\nhK7e7NyG5PXUgaDW4cBLUO2eOHNYTocTmM4/us/L6YsDv8+Wtv/FPHt2PFSg9P1qi3xbcE2MLQgV\r\n6zeUb+b+xHZ9Zx6n/BLIBccFViClme8d+e51oX8LsOssp2h1ftoS8TeBd7dXLwJXORNxiHCzyLGI\r\n5ELFDt99/cM4vvF4Hw4cb6rUCU2RDqYD+714cKa+AzQ4/7c9B5dFZoXXpP8D3xhaQgplbmRzdHJl\r\nYW0KZW5kb2JqCjEwIDAgb2JqCjw8IC9UeXBlIC9QYWdlIC9QYXJlbnQgMSAwIFIgL0xhc3RNb2Rp\r\nZmllZCAoRDoyMDE4MDQwMzE3MTMzMSswMicwMCcpIC9SZXNvdXJjZXMgMiAwIFIgL01lZGlhQm94\r\nIFswLjAwMDAwMCAwLjAwMDAwMCA1OTUuMjc2MDAwIDg0MS44OTAwMDBdIC9Dcm9wQm94IFswLjAw\r\nMDAwMCAwLjAwMDAwMCA1OTUuMjc2MDAwIDg0MS44OTAwMDBdIC9CbGVlZEJveCBbMC4wMDAwMDAg\r\nMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvVHJpbUJveCBbMC4wMDAwMDAgMC4wMDAw\r\nMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQXJ0Qm94IFswLjAwMDAwMCAwLjAwMDAwMCA1OTUu\r\nMjc2MDAwIDg0MS44OTAwMDBdIC9Db250ZW50cyAxMSAwIFIgL1JvdGF0ZSAwIC9Hcm91cCA8PCAv\r\nVHlwZSAvR3JvdXAgL1MgL1RyYW5zcGFyZW5jeSAvQ1MgL0RldmljZVJHQiA+PiAvQW5ub3RzIFsg\r\nNSAwIFIgXSAvUFogMSA+PgplbmRvYmoKMTEgMCBvYmoKPDwvRmlsdGVyIC9GbGF0ZURlY29kZSAv\r\nTGVuZ3RoIDE1MDA+PiBzdHJlYW0KeJztXMty2zYU3esr7jKZsRC8QXjVJk7cZiYzSa2ukiwYiZbU\r\niJJN0XGaVf+0m35GF4VIWyTFh8SnaNea0YhD8t6De+4DIAAKI6Gw+cAtYHhrvn/Ax8/mZ2K+5+Y7\r\nHbwcwYs3BAhGOPjA6BJejwb4YEGaFLzOu7AFSB/8dn4vlGpFSzKVGldO6BoIJ0gxSi1mCMOgGBKa\r\nKMmAC8QEZ4yAEhJpoaUQMHbhxa8YzlbwoS92Vw6CykHVzC0G3kB6kS23Bh3MWW4xZDGhsQSlBVLK\r\nuEjDaAIfn71ZrSavVqsrGDlr//lnGL09OA3ysCSSNMQysNRgURxinTnuau179t8O8JNGsKRGzAqx\r\nFA7susf6eciF5BBC2pNmTMMCWTqEE1ZoGgnhJgZneGm4HBsuf3Lt+WIxX9r+ykPjlbvFjuJOMY0s\r\nTk0egDsQxidUyZ3zi8HF4EOJWNwJOhK1nsYE789OMkI/lgOwFU8feNO6ygdRJRCWQlIJolVA7909\r\nQ2Hs3hwR8By4gJqAg3bZYBJvzREkNEeA3ioYEo643nw21lxCXfbK4fWdvVgsRMYwItpirxxe79nD\r\nkTmS3ZvDY9bc9VGkIfZK4fWdvXgmdcFeOby+s6ezjKEKt8VeOby+s8c7TtzHlLfxKqREaI3sqOrt\r\nx+s7e/Eq1AV75fD6zp7OMqabqncA3vHZw8jCyuIs4yBZ9TpJ3IeVt8XctUKGC5kPYwvDBhc444qb\r\nOB8pWvSev3gVjz12dtJr7MfrO3vxKt4Fe+Xw+s6ezjKmm17jALy+s5c9X9RFr9FJ5BVfDib4aJW5\r\n+7RQrq7cWcZir8F0cF10jzeF/OnUaDkAWwhrSZkMZ1NfOmvfWSxullP4tloGk7hwcXPlePbEnS/v\r\np1MPWzDonbFSs4SxU8f/x59PfbBdwOYSR8Q6ASJOCYtZmru0UERA5cnjfWr3sp5Wee+MUki5Qsf1\r\noFCIY4ZVbF0D3s0X49nwF8cLQtfx6vuuAsnHJoZzpHeI2Sz4rNcO8HJp2z/bGE04nRtj4WI8s72l\r\n8XjZqtQ/84hOuO718J09X5zC5Xg9DBa33CC+Z9v4Dha50mtce81vmpw6hexOyGpbpqtW3JXLlKai\r\noVXtlmfgVYzkwnZWj3EZPaxaSDG9eVgN14iXP+zZIhG5bbT8ySsZXtEkzyvvvdXk5qv/5JZjuIXJ\r\nAr848/WTV45SwnBuDXt/aS8nR/JKV91nnkxxTLR3tfpALduS4vWGKtzs01g9mwbXA2JMMQphSBRG\r\nktDELWN3n4IPcR1EIhXufIpryHN4v5IyykkpkSXINifZ91hCFu/hqpuTjcZStSysZltxO5sYVuw4\r\nJXg+h1PAJ6KJIV9x+5v3TSLvLNMHSCtxS9m8s5BOa3gQLo4PUXZ8TE4sCX81k3xHdDA1UqxeWX24\r\n7o2PdVLuFfiY7m2jJ25aY3v1/6EkUn+syB41N9ULUFW+SpSpIUMqswCyjdopPxoFg7pDJHtWfuK9\r\ni+Dho5bI6V32stlisLUVVhQjylOuay6qTGhk6H/kQRXv01JBtdOn9Sqo/p/9YLwIKlI+Hyotde4U\r\nX5WZhzmak7JUCRTU7YNk+5YqUaZwjJQQ20w5d9a266eXVUsF58N8IBhqgUi69rUfhQZX5tbcPVGo\r\nkcSHivYsCOODgJ0oZCcsOQh4AFFYpbrWWU9NCx20q7VCHX98V3u3Zad3OxUEoZu3cLcZeTZ3IFib\r\ncuBbsDth5ixhPR/PYL78urhZz7858Lu7tv0f5tqN46ESWxWefFHsC66J8QWh4u5t8tdLf2YvfGeZ\r\npHwz60pJiWFuZeajv2E4mKdIIcu0lm5encdBmlIaHIQLb6tbx3Mm8OVPGL16f/YGPj27vb1F/vhq\r\ncolW3vTT8+1r3zXe4h4c+N5/qb+kiMjnFG327lsi+RZ6dHrzEnqdPTn7AohyZBHJhUps/Pz3i0lk\r\nk8E+FG6tq9UETZEOxja7bbhw5r5jYmGz83S7AzOPyhqu/Q+SjN2ICmVuZHN0cmVhbQplbmRvYmoK\r\nMSAwIG9iago8PCAvVHlwZSAvUGFnZXMgL0tpZHMgWyA2IDAgUiA4IDAgUiAxMCAwIFIgXSAvQ291\r\nbnQgMyA+PgplbmRvYmoKMyAwIG9iago8PC9UeXBlIC9Gb250IC9TdWJ0eXBlIC9UeXBlMSAvQmFz\r\nZUZvbnQgL0hlbHZldGljYSAvTmFtZSAvRjEgL0VuY29kaW5nIC9XaW5BbnNpRW5jb2RpbmcgPj4K\r\nZW5kb2JqCjQgMCBvYmoKPDwvVHlwZSAvRm9udCAvU3VidHlwZSAvVHlwZTEgL0Jhc2VGb250IC9I\r\nZWx2ZXRpY2EtQm9sZCAvTmFtZSAvRjIgL0VuY29kaW5nIC9XaW5BbnNpRW5jb2RpbmcgPj4KZW5k\r\nb2JqCjEyIDAgb2JqCjw8L1R5cGUgL1hPYmplY3QgL1N1YnR5cGUgL0ltYWdlIC9XaWR0aCAyNjAg\r\nL0hlaWdodCAxMzUgL0NvbG9yU3BhY2UgL0RldmljZVJHQiAvQml0c1BlckNvbXBvbmVudCA4IC9G\r\naWx0ZXIgL0RDVERlY29kZSAvTGVuZ3RoIDEzODMwID4+IHN0cmVhbQr/2P/gABBKRklGAAEBAQBI\r\nAEgAAP/bAEMAAwICAwICAwMDAwQDAwQFCAUFBAQFCgcHBggMCgwMCwoLCw0OEhANDhEOCwsQFhAR\r\nExQVFRUMDxcYFhQYEhQVFP/bAEMBAwQEBQQFCQUFCRQNCw0UFBQUFBQUFBQUFBQUFBQUFBQUFBQU\r\nFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFP/CABEIAIcBBAMBEQACEQEDEQH/xAAcAAEAAgID\r\nAQAAAAAAAAAAAAAABgcECAECBQP/xAAbAQEAAgMBAQAAAAAAAAAAAAAABAUCAwYBB//aAAwDAQAC\r\nEAMQAAAB2i8wpaByfia4gAAAAAAAGV7ssOVefX3IAAADky/d0t32VRQeYoWu4wAAAcveXvViAAAL\r\n+su2tmb0nlYRs3Lbke7AAB1888nCN5GuNJd06nYPL0pA5IAAXXP66XbrT0Mt2Zlt1Up/m/L3E80g\r\nADZK076w5V3rLU8BsVadzj+a/B1Q5xJt4xpr/l5jLN9lCI9RXUWjtqZ0udntqeFzdKQOSAAme+32\r\nXtu+zcto6+eQePUyDZN1sq+CiumsAA2StO+sSVd65VfDbD2nb0jXcn52Oi/LLsqGruN6eL5sexoa\r\nu4zLy22zM6XOz21PC5ulIHJADl7uDd/UfV2SABx55z77UsHm6CruJAA2StO+sWXeUBWcXf8AZ9pR\r\nlbyHb3K5p/UUhX8ly9uyf1lOwuWw8dVszOlzs9tTwubpSByQA9jOZtjc/Sc3PZy9HAecvfF1xdTa\r\nb5piY6gBslad9Ysu8qKDzNuzumquFzsO0VewFn2lMQOV+HmF22HWURW8dI90+cb7fOz21PC5ulIH\r\nJAWfL6O0ZfQ+9sm+ztkgADp55qnTfOIvqrQBslad9OpNvXcWjnsm583DRGdVfZMu+hceq+PmM4kW\r\n9fRqT39k3u9zs9tTwubpSByQFvTepvax7Dv76AAB8/Ma3iUeutZwnDwDZK0770M90AjUs7kXEEjV\r\n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\r\n6D+q7ucaXIwJdijeP8jBVqHqEd48OCUNJ3BKDGf3LdzkmwH5M+t7vDR8Lz7mI0CmSui6V6pk/MK0\r\nMvKrtGtgXnBboSURRnoD+sHkkx2dR6aN6SpsE6Em8Ex6JP7/AAhMR9cK6ONaS2B/GV0Q0drlLtXu\r\n4ExhowDyOxjLQSBPuGsQ3gupWXoq7zgUYdzRRWGt5sgOmtItQMUvrjw4REkQXKERnbFAUEETShZd\r\nzPre7w0BbEGxwDsgX0vb8HxnRExYeCkNNRY/tPwqoAxQCK9CQcovb8SysulMv88BMiK0X2QALy+z\r\nPh2fAP8AWBAVAojyJi1nXiuxfwIf4W2222222222222222z9mABE4RwENQIh7Kv7fxU6dOnTp06d\r\nMuM0Sx6cOEsUucbTxGuD07Hh/9kKZW5kc3RyZWFtCmVuZG9iagoyIDAgb2JqCjw8IC9Qcm9jU2V0\r\nIFsvUERGIC9UZXh0IC9JbWFnZUIgL0ltYWdlQyAvSW1hZ2VJXSAvRm9udCA8PCAvRjEgMyAwIFIg\r\nL0YyIDQgMCBSID4+IC9YT2JqZWN0IDw8IC9JMCAxMiAwIFIgPj4gPj4KZW5kb2JqCjUgMCBvYmoK\r\nPDwvVHlwZSAvQW5ub3QgL1N1YnR5cGUgL0xpbmsgL1JlY3QgWzIuODM1MDAwIDEuMDAwMDAwIDE5\r\nLjAwNTAwMCAyLjE1NjAwMF0gL1AgMTAgMCBSIC9OTSAoMDAwMy0wMDAwKSAvTSAoRDoyMDE4MDQw\r\nMzE3MTMzMSswMicwMCcpIC9GIDQgL0JvcmRlciBbMCAwIDBdIC9BIDw8L1MgL1VSSSAvVVJJICho\r\ndHRwOi8vd3d3LnRjcGRmLm9yZyk+PiAvSCAvST4+CmVuZG9iagoxMyAwIG9iago8PCAvQXV0aG9y\r\nICj+/wBGAG8AbwBkAEMAbwBvAHAAIABUAGUAcwB0KSAvQ3JlYXRvciAo/v8ARgBvAG8AZABDAG8A\r\nbwBwACAAVABlAHMAdCkgL1Byb2R1Y2VyICj+/wBUAEMAUABEAEYAIAA2AC4AMgAuADEANwAgAFwo\r\nAGgAdAB0AHAAOgAvAC8AdwB3AHcALgB0AGMAcABkAGYALgBvAHIAZwBcKSkgL0NyZWF0aW9uRGF0\r\nZSAoRDoyMDE4MDQwMzE3MTMzMSswMicwMCcpIC9Nb2REYXRlIChEOjIwMTgwNDAzMTcxMzMxKzAy\r\nJzAwJykgL1RyYXBwZWQgL0ZhbHNlID4+CmVuZG9iagoxNCAwIG9iago8PCAvVHlwZSAvTWV0YWRh\r\ndGEgL1N1YnR5cGUgL1hNTCAvTGVuZ3RoIDQyNTAgPj4gc3RyZWFtCjw/eHBhY2tldCBiZWdpbj0i\r\n77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0i\r\nYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDQuMi4xLWMwNDMgNTIuMzcy\r\nNzI4LCAyMDA5LzAxLzE4LTE1OjA4OjA0Ij4KCTxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3\r\ndy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+CgkJPHJkZjpEZXNjcmlwdGlvbiBy\r\nZGY6YWJvdXQ9IiIgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIj4K\r\nCQkJPGRjOmZvcm1hdD5hcHBsaWNhdGlvbi9wZGY8L2RjOmZvcm1hdD4KCQkJPGRjOnRpdGxlPgoJ\r\nCQkJPHJkZjpBbHQ+CgkJCQkJPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ij48L3JkZjpsaT4K\r\nCQkJCTwvcmRmOkFsdD4KCQkJPC9kYzp0aXRsZT4KCQkJPGRjOmNyZWF0b3I+CgkJCQk8cmRmOlNl\r\ncT4KCQkJCQk8cmRmOmxpPkZvb2RDb29wIFRlc3Q8L3JkZjpsaT4KCQkJCTwvcmRmOlNlcT4KCQkJ\r\nPC9kYzpjcmVhdG9yPgoJCQk8ZGM6ZGVzY3JpcHRpb24+CgkJCQk8cmRmOkFsdD4KCQkJCQk8cmRm\r\nOmxpIHhtbDpsYW5nPSJ4LWRlZmF1bHQiPjwvcmRmOmxpPgoJCQkJPC9yZGY6QWx0PgoJCQk8L2Rj\r\nOmRlc2NyaXB0aW9uPgoJCQk8ZGM6c3ViamVjdD4KCQkJCTxyZGY6QmFnPgoJCQkJCTxyZGY6bGk+\r\nPC9yZGY6bGk+CgkJCQk8L3JkZjpCYWc+CgkJCTwvZGM6c3ViamVjdD4KCQk8L3JkZjpEZXNjcmlw\r\ndGlvbj4KCQk8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9u\r\ncy5hZG9iZS5jb20veGFwLzEuMC8iPgoJCQk8eG1wOkNyZWF0ZURhdGU+MjAxOC0wNC0wM1QxNzox\r\nMzozMSswMjowMDwveG1wOkNyZWF0ZURhdGU+CgkJCTx4bXA6Q3JlYXRvclRvb2w+Rm9vZENvb3Ag\r\nVGVzdDwveG1wOkNyZWF0b3JUb29sPgoJCQk8eG1wOk1vZGlmeURhdGU+MjAxOC0wNC0wM1QxNzox\r\nMzozMSswMjowMDwveG1wOk1vZGlmeURhdGU+CgkJCTx4bXA6TWV0YWRhdGFEYXRlPjIwMTgtMDQt\r\nMDNUMTc6MTM6MzErMDI6MDA8L3htcDpNZXRhZGF0YURhdGU+CgkJPC9yZGY6RGVzY3JpcHRpb24+\r\nCgkJPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6cGRmPSJodHRwOi8vbnMuYWRv\r\nYmUuY29tL3BkZi8xLjMvIj4KCQkJPHBkZjpLZXl3b3Jkcz48L3BkZjpLZXl3b3Jkcz4KCQkJPHBk\r\nZjpQcm9kdWNlcj5UQ1BERiA2LjIuMTcgKGh0dHA6Ly93d3cudGNwZGYub3JnKTwvcGRmOlByb2R1\r\nY2VyPgoJCTwvcmRmOkRlc2NyaXB0aW9uPgoJCTxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIi\r\nIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIj4KCQkJPHhtcE1N\r\nOkRvY3VtZW50SUQ+dXVpZDoxNDA1NWEwOC1kNjkyLTIwYTAtOTYwYS1jNjYzMjM4NGRhYWI8L3ht\r\ncE1NOkRvY3VtZW50SUQ+CgkJCTx4bXBNTTpJbnN0YW5jZUlEPnV1aWQ6MTQwNTVhMDgtZDY5Mi0y\r\nMGEwLTk2MGEtYzY2MzIzODRkYWFiPC94bXBNTTpJbnN0YW5jZUlEPgoJCTwvcmRmOkRlc2NyaXB0\r\naW9uPgoJCTxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnBkZmFFeHRlbnNpb249\r\nImh0dHA6Ly93d3cuYWlpbS5vcmcvcGRmYS9ucy9leHRlbnNpb24vIiB4bWxuczpwZGZhU2NoZW1h\r\nPSJodHRwOi8vd3d3LmFpaW0ub3JnL3BkZmEvbnMvc2NoZW1hIyIgeG1sbnM6cGRmYVByb3BlcnR5\r\nPSJodHRwOi8vd3d3LmFpaW0ub3JnL3BkZmEvbnMvcHJvcGVydHkjIj4KCQkJPHBkZmFFeHRlbnNp\r\nb246c2NoZW1hcz4KCQkJCTxyZGY6QmFnPgoJCQkJCTxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVz\r\nb3VyY2UiPgoJCQkJCQk8cGRmYVNjaGVtYTpuYW1lc3BhY2VVUkk+aHR0cDovL25zLmFkb2JlLmNv\r\nbS9wZGYvMS4zLzwvcGRmYVNjaGVtYTpuYW1lc3BhY2VVUkk+CgkJCQkJCTxwZGZhU2NoZW1hOnBy\r\nZWZpeD5wZGY8L3BkZmFTY2hlbWE6cHJlZml4PgoJCQkJCQk8cGRmYVNjaGVtYTpzY2hlbWE+QWRv\r\nYmUgUERGIFNjaGVtYTwvcGRmYVNjaGVtYTpzY2hlbWE+CgkJCQkJPC9yZGY6bGk+CgkJCQkJPHJk\r\nZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CgkJCQkJCTxwZGZhU2NoZW1hOm5hbWVzcGFj\r\nZVVSST5odHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vPC9wZGZhU2NoZW1hOm5hbWVzcGFj\r\nZVVSST4KCQkJCQkJPHBkZmFTY2hlbWE6cHJlZml4PnhtcE1NPC9wZGZhU2NoZW1hOnByZWZpeD4K\r\nCQkJCQkJPHBkZmFTY2hlbWE6c2NoZW1hPlhNUCBNZWRpYSBNYW5hZ2VtZW50IFNjaGVtYTwvcGRm\r\nYVNjaGVtYTpzY2hlbWE+CgkJCQkJCTxwZGZhU2NoZW1hOnByb3BlcnR5PgoJCQkJCQkJPHJkZjpT\r\nZXE+CgkJCQkJCQkJPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CgkJCQkJCQkJCTxw\r\nZGZhUHJvcGVydHk6Y2F0ZWdvcnk+aW50ZXJuYWw8L3BkZmFQcm9wZXJ0eTpjYXRlZ29yeT4KCQkJ\r\nCQkJCQkJPHBkZmFQcm9wZXJ0eTpkZXNjcmlwdGlvbj5VVUlEIGJhc2VkIGlkZW50aWZpZXIgZm9y\r\nIHNwZWNpZmljIGluY2FybmF0aW9uIG9mIGEgZG9jdW1lbnQ8L3BkZmFQcm9wZXJ0eTpkZXNjcmlw\r\ndGlvbj4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTpuYW1lPkluc3RhbmNlSUQ8L3BkZmFQcm9wZXJ0\r\neTpuYW1lPgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5OnZhbHVlVHlwZT5VUkk8L3BkZmFQcm9wZXJ0\r\neTp2YWx1ZVR5cGU+CgkJCQkJCQkJPC9yZGY6bGk+CgkJCQkJCQk8L3JkZjpTZXE+CgkJCQkJCTwv\r\ncGRmYVNjaGVtYTpwcm9wZXJ0eT4KCQkJCQk8L3JkZjpsaT4KCQkJCQk8cmRmOmxpIHJkZjpwYXJz\r\nZVR5cGU9IlJlc291cmNlIj4KCQkJCQkJPHBkZmFTY2hlbWE6bmFtZXNwYWNlVVJJPmh0dHA6Ly93\r\nd3cuYWlpbS5vcmcvcGRmYS9ucy9pZC88L3BkZmFTY2hlbWE6bmFtZXNwYWNlVVJJPgoJCQkJCQk8\r\ncGRmYVNjaGVtYTpwcmVmaXg+cGRmYWlkPC9wZGZhU2NoZW1hOnByZWZpeD4KCQkJCQkJPHBkZmFT\r\nY2hlbWE6c2NoZW1hPlBERi9BIElEIFNjaGVtYTwvcGRmYVNjaGVtYTpzY2hlbWE+CgkJCQkJCTxw\r\nZGZhU2NoZW1hOnByb3BlcnR5PgoJCQkJCQkJPHJkZjpTZXE+CgkJCQkJCQkJPHJkZjpsaSByZGY6\r\ncGFyc2VUeXBlPSJSZXNvdXJjZSI+CgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6Y2F0ZWdvcnk+aW50\r\nZXJuYWw8L3BkZmFQcm9wZXJ0eTpjYXRlZ29yeT4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTpkZXNj\r\ncmlwdGlvbj5QYXJ0IG9mIFBERi9BIHN0YW5kYXJkPC9wZGZhUHJvcGVydHk6ZGVzY3JpcHRpb24+\r\nCgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6bmFtZT5wYXJ0PC9wZGZhUHJvcGVydHk6bmFtZT4KCQkJ\r\nCQkJCQkJPHBkZmFQcm9wZXJ0eTp2YWx1ZVR5cGU+SW50ZWdlcjwvcGRmYVByb3BlcnR5OnZhbHVl\r\nVHlwZT4KCQkJCQkJCQk8L3JkZjpsaT4KCQkJCQkJCQk8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJl\r\nc291cmNlIj4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTpjYXRlZ29yeT5pbnRlcm5hbDwvcGRmYVBy\r\nb3BlcnR5OmNhdGVnb3J5PgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5OmRlc2NyaXB0aW9uPkFtZW5k\r\nbWVudCBvZiBQREYvQSBzdGFuZGFyZDwvcGRmYVByb3BlcnR5OmRlc2NyaXB0aW9uPgoJCQkJCQkJ\r\nCQk8cGRmYVByb3BlcnR5Om5hbWU+YW1kPC9wZGZhUHJvcGVydHk6bmFtZT4KCQkJCQkJCQkJPHBk\r\nZmFQcm9wZXJ0eTp2YWx1ZVR5cGU+VGV4dDwvcGRmYVByb3BlcnR5OnZhbHVlVHlwZT4KCQkJCQkJ\r\nCQk8L3JkZjpsaT4KCQkJCQkJCQk8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KCQkJ\r\nCQkJCQkJPHBkZmFQcm9wZXJ0eTpjYXRlZ29yeT5pbnRlcm5hbDwvcGRmYVByb3BlcnR5OmNhdGVn\r\nb3J5PgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5OmRlc2NyaXB0aW9uPkNvbmZvcm1hbmNlIGxldmVs\r\nIG9mIFBERi9BIHN0YW5kYXJkPC9wZGZhUHJvcGVydHk6ZGVzY3JpcHRpb24+CgkJCQkJCQkJCTxw\r\nZGZhUHJvcGVydHk6bmFtZT5jb25mb3JtYW5jZTwvcGRmYVByb3BlcnR5Om5hbWU+CgkJCQkJCQkJ\r\nCTxwZGZhUHJvcGVydHk6dmFsdWVUeXBlPlRleHQ8L3BkZmFQcm9wZXJ0eTp2YWx1ZVR5cGU+CgkJ\r\nCQkJCQkJPC9yZGY6bGk+CgkJCQkJCQk8L3JkZjpTZXE+CgkJCQkJCTwvcGRmYVNjaGVtYTpwcm9w\r\nZXJ0eT4KCQkJCQk8L3JkZjpsaT4KCQkJCTwvcmRmOkJhZz4KCQkJPC9wZGZhRXh0ZW5zaW9uOnNj\r\naGVtYXM+CgkJPC9yZGY6RGVzY3JpcHRpb24+Cgk8L3JkZjpSREY+CjwveDp4bXBtZXRhPgo8P3hw\r\nYWNrZXQgZW5kPSJ3Ij8+CmVuZHN0cmVhbQplbmRvYmoKMTUgMCBvYmoKPDwgL1R5cGUgL0NhdGFs\r\nb2cgL1ZlcnNpb24gLzEuNyAvUGFnZXMgMSAwIFIgL05hbWVzIDw8ID4+IC9WaWV3ZXJQcmVmZXJl\r\nbmNlcyA8PCAvRGlyZWN0aW9uIC9MMlIgPj4gL1BhZ2VMYXlvdXQgL1NpbmdsZVBhZ2UgL1BhZ2VN\r\nb2RlIC9Vc2VOb25lIC9PcGVuQWN0aW9uIFs2IDAgUiAvRml0SCBudWxsXSAvTWV0YWRhdGEgMTQg\r\nMCBSID4+CmVuZG9iagp4cmVmCjAgMTYKMDAwMDAwMDAwMCA2NTUzNSBmIAowMDAwMDA2MDk5IDAw\r\nMDAwIG4gCjAwMDAwMjAzODcgMDAwMDAgbiAKMDAwMDAwNjE3MSAwMDAwMCBuIAowMDAwMDA2Mjc3\r\nIDAwMDAwIG4gCjAwMDAwMjA1MTIgMDAwMDAgbiAKMDAwMDAwMDAxNSAwMDAwMCBuIAowMDAwMDAw\r\nNDY1IDAwMDAwIG4gCjAwMDAwMDIxMTIgMDAwMDAgbiAKMDAwMDAwMjU2MiAwMDAwMCBuIAowMDAw\r\nMDA0MDU3IDAwMDAwIG4gCjAwMDAwMDQ1MjcgMDAwMDAgbiAKMDAwMDAwNjM4OCAwMDAwMCBuIAow\r\nMDAwMDIwNzI5IDAwMDAwIG4gCjAwMDAwMjEwMDggMDAwMDAgbiAKMDAwMDAyNTM0MSAwMDAwMCBu\r\nIAp0cmFpbGVyCjw8IC9TaXplIDE2IC9Sb290IDE1IDAgUiAvSW5mbyAxMyAwIFIgL0lEIFsgPDE0\r\nMDU1YTA4ZDY5MjIwYTA5NjBhYzY2MzIzODRkYWFiPiA8MTQwNTVhMDhkNjkyMjBhMDk2MGFjNjYz\r\nMjM4NGRhYWI+IF0gPj4Kc3RhcnR4cmVmCjI1NTUwCiUlRU9GCg==\r\n\r\n\r\n--db2d6b9081ba5a8c649dce962cb65c53\r\nContent-Disposition: attachment; filename=\"Allgemeine-Geschaeftsbedingungen.pdf\"\r\nContent-Type: application/pdf\r\nContent-Transfer-Encoding: base64\r\n\r\nJVBERi0xLjcKJeLjz9MKNyAwIG9iago8PCAvVHlwZSAvUGFnZSAvUGFyZW50IDEgMCBSIC9MYXN0\r\nTW9kaWZpZWQgKEQ6MjAxODA0MDMxNzEzMzErMDInMDAnKSAvUmVzb3VyY2VzIDIgMCBSIC9NZWRp\r\nYUJveCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQ3JvcEJveCBb\r\nMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQmxlZWRCb3ggWzAuMDAw\r\nMDAwIDAuMDAwMDAwIDU5NS4yNzYwMDAgODQxLjg5MDAwMF0gL1RyaW1Cb3ggWzAuMDAwMDAwIDAu\r\nMDAwMDAwIDU5NS4yNzYwMDAgODQxLjg5MDAwMF0gL0FydEJveCBbMC4wMDAwMDAgMC4wMDAwMDAg\r\nNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQ29udGVudHMgOCAwIFIgL1JvdGF0ZSAwIC9Hcm91cCA8\r\nPCAvVHlwZSAvR3JvdXAgL1MgL1RyYW5zcGFyZW5jeSAvQ1MgL0RldmljZVJHQiA+PiAvQW5ub3Rz\r\nIFsgNSAwIFIgXSAvUFogMSA+PgplbmRvYmoKOCAwIG9iago8PC9GaWx0ZXIgL0ZsYXRlRGVjb2Rl\r\nIC9MZW5ndGggMjMxNz4+IHN0cmVhbQp4nO1bTXPbOBK9+1d05ZRUyTS/Sc1p7fXHZiZbm0lcyWE8\r\nB1CERI5I0AFBa0p/Nhf/hj35sN0AKMmy7LETZ61M7CrHNkUKwkO/168biOtEiYtfMAMXfsbvP+C3\r\n3/FHjt8n+D3ZOTiFvWMPPNdx9RecjuHodMe994P+9Qc/3fbCYoCbv7w76R+68Sm+0TNf9OEe9tAn\r\n8ELPSQLfTwMEzIUkcKKhl8QBBKHjelESppBEsTOMhnEUwaiGvdcuHDbw67bM+4uD4IuD6nFuweFx\r\nSLmcywxHB7wapoGTBtHQjSEZRk6S4BIN4TSH314eN03+z6Y5h1Peqle/w+nP96bBbWPFTuybsXBY\r\nH8fyXTPWIa+bVkn2mUM4eJSx4qETpGasxNXz6sfa3w2jOAQzJMsfZ2pu5KRDM1yUmql5Zrgcx9kd\r\nI5YjxPIfNSurqhRMNdIZNfVi7GXcJcHQSUMfeQD1ToRr4ifx2vVq5/3Orw+IxYfzwgcvfoSHbn2v\r\n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\r\na2w6fGYzidy+QBR0PdmfDrG9+3//d0I9RPIMg5udxdUN23ueo3/Qf/FY8jn0ncgbxml0/VT38jId\r\n6n74Iv3VwfQVgx86qReHvUO956HnrzoUH6XOMNIdpbXB31NZCp7mvr8Y6hYM7zzp/pdBeguk/wPb\r\nqacnCmVuZHN0cmVhbQplbmRvYmoKOSAwIG9iago8PCAvVHlwZSAvUGFnZSAvUGFyZW50IDEgMCBS\r\nIC9MYXN0TW9kaWZpZWQgKEQ6MjAxODA0MDMxNzEzMzErMDInMDAnKSAvUmVzb3VyY2VzIDIgMCBS\r\nIC9NZWRpYUJveCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQ3Jv\r\ncEJveCBbMC4wMDAwMDAgMC4wMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQmxlZWRCb3gg\r\nWzAuMDAwMDAwIDAuMDAwMDAwIDU5NS4yNzYwMDAgODQxLjg5MDAwMF0gL1RyaW1Cb3ggWzAuMDAw\r\nMDAwIDAuMDAwMDAwIDU5NS4yNzYwMDAgODQxLjg5MDAwMF0gL0FydEJveCBbMC4wMDAwMDAgMC4w\r\nMDAwMDAgNTk1LjI3NjAwMCA4NDEuODkwMDAwXSAvQ29udGVudHMgMTAgMCBSIC9Sb3RhdGUgMCAv\r\nR3JvdXAgPDwgL1R5cGUgL0dyb3VwIC9TIC9UcmFuc3BhcmVuY3kgL0NTIC9EZXZpY2VSR0IgPj4g\r\nL0Fubm90cyBbIDYgMCBSIF0gL1BaIDEgPj4KZW5kb2JqCjEwIDAgb2JqCjw8L0ZpbHRlciAvRmxh\r\ndGVEZWNvZGUgL0xlbmd0aCAxMDUxPj4gc3RyZWFtCnic5ZjNcts2EMfveoo9JjMxTIIEP25RYtm1\r\n26SOpbaHJAeKBCk0JCgDZDTN8/U9cvEz9ORDFoRkRY4lS7Y7cRuNZGtALBb44b8LrBzCQgdfMAMH\r\nTvDzJ7x9j/8ycIjTvW74cnaEPYreixHsH7rgXrWPchiMes76IWHVkK4anq97sHEqC6NvZvEv2dxp\r\ncrsZnYPruyT0KI08BOZA6BEWu2HggecTx2WhH0HIAhKzOGAM0gr2jx04qOHNY1n3nUVwZ1E9TBd0\r\njy7Vci0z9A7Y6kceiTwWOwGEMSNhiFsUwyiDt08O6zp7WddTGHHdPH0Po5Otw2Cdr4AE1PpCtxR9\r\nUcf6OuBVrRuVfObgP3sQX0FMvMj6Cp1uXQtf/T2fBT5Yl0n2MEtzGIli645FdmmudZehn70cWabI\r\n8nmViLIUMmlqRdK6uvK91F3oxSTyKcYBVD2Ge0LD4Fp72Rv23mya7l1z3O4qvdXT+aY+qoC1SL9K\r\nCTgfSh3qhZbosFFcNGMuSl60soCsVekEuJAcWjlNVMOF0OmEwzCdlCKdNNhJ64aXJQchoS/1VLVo\r\n8akFyScVl2SxDbcmmu1Syn+CahAxSzWah6DgMNh7hfpMMsW15pBxDT9xZckp4KoQ4wY0EoWk1fi4\r\nguNqavq21fYIOxoUj4Kdk/W3RmvH+r5k8QhzXUq9eSINSKfEVusxZlJRVahHLr8CtlYgm5D8n6To\r\n2QD3nQUwl8BA5ZdlaUK3Vg3klwoSE8C/cKGbDqAVoeAadfg7V3h6FIDPUJcKhk0iM2O3qmH9Y0a6\r\nYyPddxd4KYFDBHrGMTliZjTpVBQf8O/NVAtRNqZdm3zKP5ucCv8gUoWG6aTLtdoORsCkkb6ccZl1\r\nqRkf/PZ6bxutf1dELKYkNIioRfRz0uaqo/Os0xOSmKHwjBplrfDI6FZ2fIozaGVmu/wK754Mjt49\r\nhdeK4IDePnWcqOs3aFU9veg4SThNVJngCI2em+rHTyeax6dn6ZwlDa7qY12BGxI4aaWAbq2XY+SQ\r\nJRq1ksNHKx4jlu4kbssMmyYXZSOFOV0S+ak1MjFvKx7kd4ZjHu8h7VplEmkjTbG8fD5eQEFo5TMH\r\nhLFScBMtNa70x7xeMH8eUf4i6XgE/sa9VjZ51yZmBqq4QBl0DV3OUdA/egFjnl2qHNvwhiYmClv/\r\nEOqDTiqTomx+TydK5E2OsbgL3W2Lt2solxX8rtWbQXIjKGqqLtPgYmR1XzpKp/WMK57B+C8YvTw9\r\nOMSQmM1mpEmnWU5qhfFwVTFsLAA2r6e3Zcm4068ZS/i488yN8X65WsAsm039cp9fWW7THvVJ5AY+\r\nm9cL/bIseNXVB0cmKi/yBm9imZDFykXsXvUfFn0xcyij150PzakKFLOlBHrlag3De+zpF5zYZxIK\r\nZW5kc3RyZWFtCmVuZG9iagoxIDAgb2JqCjw8IC9UeXBlIC9QYWdlcyAvS2lkcyBbIDcgMCBSIDkg\r\nMCBSIF0gL0NvdW50IDIgPj4KZW5kb2JqCjMgMCBvYmoKPDwvVHlwZSAvRm9udCAvU3VidHlwZSAv\r\nVHlwZTEgL0Jhc2VGb250IC9IZWx2ZXRpY2EgL05hbWUgL0YxIC9FbmNvZGluZyAvV2luQW5zaUVu\r\nY29kaW5nID4+CmVuZG9iago0IDAgb2JqCjw8L1R5cGUgL0ZvbnQgL1N1YnR5cGUgL1R5cGUxIC9C\r\nYXNlRm9udCAvSGVsdmV0aWNhLUJvbGQgL05hbWUgL0YyIC9FbmNvZGluZyAvV2luQW5zaUVuY29k\r\naW5nID4+CmVuZG9iagoxMSAwIG9iago8PC9UeXBlIC9YT2JqZWN0IC9TdWJ0eXBlIC9JbWFnZSAv\r\nV2lkdGggMjYwIC9IZWlnaHQgMTM1IC9Db2xvclNwYWNlIC9EZXZpY2VSR0IgL0JpdHNQZXJDb21w\r\nb25lbnQgOCAvRmlsdGVyIC9EQ1REZWNvZGUgL0xlbmd0aCAxMzgzMCA+PiBzdHJlYW0K/9j/4AAQ\r\nSkZJRgABAQEASABIAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQ\r\nDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQU\r\nFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wgARCACHAQQDAREAAhEBAxEB\r\n/8QAHAABAAICAwEAAAAAAAAAAAAAAAYHBAgBAgUD/8QAGwEBAAIDAQEAAAAAAAAAAAAAAAQFAgMG\r\nAQf/2gAMAwEAAhADEAAAAdovMKWgcn4muIAAAAAAABle7LDlXn19yAAAA5Mv3dLd9lUUHmKFruMA\r\nAAHL3l71YgAAC/rLtrZm9J5WEbNy25HuwAAdfPPJwjeRrjSXdOp2Dy9KQOSAAF1z+ul2609DLdmZ\r\nbdVKf5vy9xPNIAA2StO+sOVd6y1PAbFWnc4/mvwdUOcSbeMaa/5eYyzfZQiPUV1Fo7amdLnZ7anh\r\nc3SkDkgAJnvt9l7bvs3LaOvnkHj1Mg2TdbKvgorprAANkrTvrElXeuVXw2w9p29I13J+djovyy7K\r\nhq7jeni+bHsaGruMy8ttszOlzs9tTwubpSByQA5e7g3f1H1dkgAceec++1LB5ugq7iQANkrTvrFl\r\n3lAVnF3/AGfaUZW8h29yuaf1FIV/Jcvbsn9ZTsLlsPHVbMzpc7PbU8Lm6UgckAPYzmbY3P0nNz2c\r\n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\r\nnxrfA+AXQTdI/RAjHFkyRHtyIhqWwdh2j5q1bJs0CMbHlVmEXHDV6hQtAk5GhWYmpFwYvjZ6Nvdj\r\nt+Kl91teZf8AB9iAGE7LPs4W4W+EndeY3QSwQRxw64EIKweKaco1pyjWnKNaco1pyjWnKNaco1py\r\njWnKNaco1pyjWnKNaco1pyjWnKNaco1pyjWnSOFJx56lZ2N/XY39djf12N/XY39djf12N/XY39dj\r\nf12J7jQ0K2F41//EADoRAAAFAgEIBwcEAwEBAAAAAAABAgMEBRESExUgITE0UXEQFDNBQlKhIiUw\r\nMjVTgbHB0fBDYeGRYv/aAAgBAwEBPwFa0tpNSj1B+uHezKf/AEZ7k8CGe5PAhnuTwIZ7k8CGe5PA\r\nhnuTwIZ7k8CGe5PAhnuTwIZ7k8CGe5PAhnuTwIZ7k8CGe5PAhnuTwIZ7k8CGe5PAg3VpjqsCEkZg\r\ns4mWvCPePFPqPePFPqPePFPqPePFPqPePFPqPePFPqPePFPqPePFPqPePFPqPeH/AM+oZlma8i8n\r\nCr0PorjppbS2Xf8ACsZ/BojJJaN3vPodkNMdoqwQtLiSUnZpmdtYalMvnZtVxUEYmDV3p1kG1Y0E\r\nriK74NNijtutIWozI+8N0uO2vFa/92hynsOmRqLYVrBUdJIwtlY+4OJwLNJ9wS0tSTWRai06Pupd\r\nCnWXp2N35AxkzbLJfKH5TUYrunYIqsVZ2xdD85iOeFxWsM1CO+rAhWvoXUYp3RjFD7VfITd2c5CP\r\n2KOQrvg0mKc483lNgbI0pIj0FU1lbuUUX9/2F3bbPAVzE6M6w5iWVr6VH3Uuhpps6lkzLVcwlKUF\r\nhSWoVrW82Qq0dpjJ5MrXv+wb+Qg6hLtUwL2f8DrSGakhLZWK5B3s1CkxmpGPKlcUPtV8hN3ZzkI/\r\nYo5Cu+DSYJOSSSC1adXdQlGC11fppUfdS6Gvqv5P9Ois9u2K5/i/P7Bv5CB/Vv7wEr6ojmQd7NXI\r\nULxih9qvkJu7OchH7FHIV3waMVvG6m+wINRp9vTksZdpTd7XDrZtLNB92jR91LobhPpn5cy9nX0V\r\nWG9INK2e4OxqhMNJOls5BJYSIhMhykyusMFcMRJj0pL8grWCixJMhHhVCMo0t6r9+oUuE9FcUbpC\r\nbuznIR+xRyFd8GhDpXWGsorV/AzawTSmU7DDTJN6tumYqDxPPnZNraNH3Ug44lpBrXsIZ7jcD/v5\r\n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\r\n2zaNSirakpOuPDjqWIJs4BK1o+3XJvQvKpAht6WnOfW93ho+F583SHLqHyQFbntlV+4TjoUd3Qvl\r\nfwBchNYBA9IVcClyGYgaqLEOkQ174DdxoEIaact0kU8ToP6ru59H1+CF4fCf7Dtz63u8NHwvPpmE\r\nKEnH3ZYKtYtKQ8jnS76c4fdDA9gDquTAAnzLOa4v0NrwENCICq11+EJrMU0Vee5H7yYhRU6MTgDb\r\npHDO4s9y/wDzxOg/qu7nGlyMCXYo3j/IwVah6hHePDglDSdwSgxn9y3c5JsB+TPre7w0fC8+5iNA\r\npkrouleqZPzCtDLyq7RrYF5wW6ElEUZ6A/rB5JMdnUemjekqbBOhJvBMeiT+/wAITEfXCujjWktg\r\nfxldENHa5S7V7uBMYaMA8jsYy0EgT7hrEN4LqVl6Ku84FGHc0UVhrebIDprSLUDFL648OERJEFyh\r\nEZ2xQFBBE0oWXcz63u8NAWxBscA7IF9L2/B8Z0RMWHgpDTUWP7T8KqAMUAivQkHKL2/EsrLpTL/P\r\nATIitF9kAC8vsz4dnwD/AFgQFQKI8iYtZ14rsX8CH+Ftttttttttttttttts/ZgAROEcBDUCIeyr\r\n+38VOnTp06dOnTLjNEsenDhLFLnG08Rrg9Ox4f/ZCmVuZHN0cmVhbQplbmRvYmoKMiAwIG9iago8\r\nPCAvUHJvY1NldCBbL1BERiAvVGV4dCAvSW1hZ2VCIC9JbWFnZUMgL0ltYWdlSV0gL0ZvbnQgPDwg\r\nL0YxIDMgMCBSIC9GMiA0IDAgUiA+PiAvWE9iamVjdCA8PCAvSTAgMTEgMCBSID4+ID4+CmVuZG9i\r\nago1IDAgb2JqCjw8L1R5cGUgL0Fubm90IC9TdWJ0eXBlIC9MaW5rIC9SZWN0IFsyODEuOTA1NzQ4\r\nIDExMy40NzAyNjAgMjk4LjU3NTc0OCAxMjUuMDMwMjYwXSAvUCA3IDAgUiAvTk0gKDAwMDEtMDAw\r\nMCkgL00gKEQ6MjAxODA0MDMxNzEzMzErMDInMDAnKSAvRiA0IC9Cb3JkZXIgWzAgMCAwXSAvQSA8\r\nPC9TIC9VUkkgL1VSSSAoaHR0cDovL3d3dy5mb29kY29vcHNob3AudGVzdC9JbmZvcm1hdGlvbmVu\r\nLXVlYmVyLVJ1ZWNrdHJpdHRzcmVjaHQucGRmKT4+IC9IIC9JPj4KZW5kb2JqCjYgMCBvYmoKPDwv\r\nVHlwZSAvQW5ub3QgL1N1YnR5cGUgL0xpbmsgL1JlY3QgWzIuODM1MDAwIDEuMDAwMDAwIDE5LjAw\r\nNTAwMCAyLjE1NjAwMF0gL1AgOSAwIFIgL05NICgwMDAyLTAwMDApIC9NIChEOjIwMTgwNDAzMTcx\r\nMzMxKzAyJzAwJykgL0YgNCAvQm9yZGVyIFswIDAgMF0gL0EgPDwvUyAvVVJJIC9VUkkgKGh0dHA6\r\nLy93d3cudGNwZGYub3JnKT4+IC9IIC9JPj4KZW5kb2JqCjEyIDAgb2JqCjw8IC9UaXRsZSAo/v8A\r\nQQBsAGwAZwBlAG0AZQBpAG4AZQAgAEcAZQBzAGMAaADkAGYAdABzAGIAZQBkAGkAbgBnAHUAbgBn\r\nAGUAbikgL0F1dGhvciAo/v8ARgBvAG8AZABDAG8AbwBwACAAVABlAHMAdCkgL0NyZWF0b3IgKP7/\r\nAEYAbwBvAGQAQwBvAG8AcAAgAFQAZQBzAHQpIC9Qcm9kdWNlciAo/v8AVABDAFAARABGACAANgAu\r\nADIALgAxADcAIABcKABoAHQAdABwADoALwAvAHcAdwB3AC4AdABjAHAAZABmAC4AbwByAGcAXCkp\r\nIC9DcmVhdGlvbkRhdGUgKEQ6MjAxODA0MDMxNzEzMzErMDInMDAnKSAvTW9kRGF0ZSAoRDoyMDE4\r\nMDQwMzE3MTMzMSswMicwMCcpIC9UcmFwcGVkIC9GYWxzZSA+PgplbmRvYmoKMTMgMCBvYmoKPDwg\r\nL1R5cGUgL01ldGFkYXRhIC9TdWJ0eXBlIC9YTUwgL0xlbmd0aCA0MjgyID4+IHN0cmVhbQo8P3hw\r\nYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/Pgo8eDp4bXBt\r\nZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA0LjIu\r\nMS1jMDQzIDUyLjM3MjcyOCwgMjAwOS8wMS8xOC0xNTowODowNCI+Cgk8cmRmOlJERiB4bWxuczpy\r\nZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgoJCTxyZGY6\r\nRGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxl\r\nbWVudHMvMS4xLyI+CgkJCTxkYzpmb3JtYXQ+YXBwbGljYXRpb24vcGRmPC9kYzpmb3JtYXQ+CgkJ\r\nCTxkYzp0aXRsZT4KCQkJCTxyZGY6QWx0PgoJCQkJCTxyZGY6bGkgeG1sOmxhbmc9IngtZGVmYXVs\r\ndCI+QWxsZ2VtZWluZSBHZXNjaMOkZnRzYmVkaW5ndW5nZW48L3JkZjpsaT4KCQkJCTwvcmRmOkFs\r\ndD4KCQkJPC9kYzp0aXRsZT4KCQkJPGRjOmNyZWF0b3I+CgkJCQk8cmRmOlNlcT4KCQkJCQk8cmRm\r\nOmxpPkZvb2RDb29wIFRlc3Q8L3JkZjpsaT4KCQkJCTwvcmRmOlNlcT4KCQkJPC9kYzpjcmVhdG9y\r\nPgoJCQk8ZGM6ZGVzY3JpcHRpb24+CgkJCQk8cmRmOkFsdD4KCQkJCQk8cmRmOmxpIHhtbDpsYW5n\r\nPSJ4LWRlZmF1bHQiPjwvcmRmOmxpPgoJCQkJPC9yZGY6QWx0PgoJCQk8L2RjOmRlc2NyaXB0aW9u\r\nPgoJCQk8ZGM6c3ViamVjdD4KCQkJCTxyZGY6QmFnPgoJCQkJCTxyZGY6bGk+PC9yZGY6bGk+CgkJ\r\nCQk8L3JkZjpCYWc+CgkJCTwvZGM6c3ViamVjdD4KCQk8L3JkZjpEZXNjcmlwdGlvbj4KCQk8cmRm\r\nOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20v\r\neGFwLzEuMC8iPgoJCQk8eG1wOkNyZWF0ZURhdGU+MjAxOC0wNC0wM1QxNzoxMzozMSswMjowMDwv\r\neG1wOkNyZWF0ZURhdGU+CgkJCTx4bXA6Q3JlYXRvclRvb2w+Rm9vZENvb3AgVGVzdDwveG1wOkNy\r\nZWF0b3JUb29sPgoJCQk8eG1wOk1vZGlmeURhdGU+MjAxOC0wNC0wM1QxNzoxMzozMSswMjowMDwv\r\neG1wOk1vZGlmeURhdGU+CgkJCTx4bXA6TWV0YWRhdGFEYXRlPjIwMTgtMDQtMDNUMTc6MTM6MzEr\r\nMDI6MDA8L3htcDpNZXRhZGF0YURhdGU+CgkJPC9yZGY6RGVzY3JpcHRpb24+CgkJPHJkZjpEZXNj\r\ncmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6cGRmPSJodHRwOi8vbnMuYWRvYmUuY29tL3BkZi8x\r\nLjMvIj4KCQkJPHBkZjpLZXl3b3Jkcz48L3BkZjpLZXl3b3Jkcz4KCQkJPHBkZjpQcm9kdWNlcj5U\r\nQ1BERiA2LjIuMTcgKGh0dHA6Ly93d3cudGNwZGYub3JnKTwvcGRmOlByb2R1Y2VyPgoJCTwvcmRm\r\nOkRlc2NyaXB0aW9uPgoJCTxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcE1N\r\nPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIj4KCQkJPHhtcE1NOkRvY3VtZW50SUQ+\r\ndXVpZDo0NTMwMjFkNC01YzJkLTg5MWUtNGViMi0wMzc2YTg5MGE0NDQ8L3htcE1NOkRvY3VtZW50\r\nSUQ+CgkJCTx4bXBNTTpJbnN0YW5jZUlEPnV1aWQ6NDUzMDIxZDQtNWMyZC04OTFlLTRlYjItMDM3\r\nNmE4OTBhNDQ0PC94bXBNTTpJbnN0YW5jZUlEPgoJCTwvcmRmOkRlc2NyaXB0aW9uPgoJCTxyZGY6\r\nRGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnBkZmFFeHRlbnNpb249Imh0dHA6Ly93d3cu\r\nYWlpbS5vcmcvcGRmYS9ucy9leHRlbnNpb24vIiB4bWxuczpwZGZhU2NoZW1hPSJodHRwOi8vd3d3\r\nLmFpaW0ub3JnL3BkZmEvbnMvc2NoZW1hIyIgeG1sbnM6cGRmYVByb3BlcnR5PSJodHRwOi8vd3d3\r\nLmFpaW0ub3JnL3BkZmEvbnMvcHJvcGVydHkjIj4KCQkJPHBkZmFFeHRlbnNpb246c2NoZW1hcz4K\r\nCQkJCTxyZGY6QmFnPgoJCQkJCTxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgoJCQkJ\r\nCQk8cGRmYVNjaGVtYTpuYW1lc3BhY2VVUkk+aHR0cDovL25zLmFkb2JlLmNvbS9wZGYvMS4zLzwv\r\ncGRmYVNjaGVtYTpuYW1lc3BhY2VVUkk+CgkJCQkJCTxwZGZhU2NoZW1hOnByZWZpeD5wZGY8L3Bk\r\nZmFTY2hlbWE6cHJlZml4PgoJCQkJCQk8cGRmYVNjaGVtYTpzY2hlbWE+QWRvYmUgUERGIFNjaGVt\r\nYTwvcGRmYVNjaGVtYTpzY2hlbWE+CgkJCQkJPC9yZGY6bGk+CgkJCQkJPHJkZjpsaSByZGY6cGFy\r\nc2VUeXBlPSJSZXNvdXJjZSI+CgkJCQkJCTxwZGZhU2NoZW1hOm5hbWVzcGFjZVVSST5odHRwOi8v\r\nbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vPC9wZGZhU2NoZW1hOm5hbWVzcGFjZVVSST4KCQkJCQkJ\r\nPHBkZmFTY2hlbWE6cHJlZml4PnhtcE1NPC9wZGZhU2NoZW1hOnByZWZpeD4KCQkJCQkJPHBkZmFT\r\nY2hlbWE6c2NoZW1hPlhNUCBNZWRpYSBNYW5hZ2VtZW50IFNjaGVtYTwvcGRmYVNjaGVtYTpzY2hl\r\nbWE+CgkJCQkJCTxwZGZhU2NoZW1hOnByb3BlcnR5PgoJCQkJCQkJPHJkZjpTZXE+CgkJCQkJCQkJ\r\nPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6\r\nY2F0ZWdvcnk+aW50ZXJuYWw8L3BkZmFQcm9wZXJ0eTpjYXRlZ29yeT4KCQkJCQkJCQkJPHBkZmFQ\r\ncm9wZXJ0eTpkZXNjcmlwdGlvbj5VVUlEIGJhc2VkIGlkZW50aWZpZXIgZm9yIHNwZWNpZmljIGlu\r\nY2FybmF0aW9uIG9mIGEgZG9jdW1lbnQ8L3BkZmFQcm9wZXJ0eTpkZXNjcmlwdGlvbj4KCQkJCQkJ\r\nCQkJPHBkZmFQcm9wZXJ0eTpuYW1lPkluc3RhbmNlSUQ8L3BkZmFQcm9wZXJ0eTpuYW1lPgoJCQkJ\r\nCQkJCQk8cGRmYVByb3BlcnR5OnZhbHVlVHlwZT5VUkk8L3BkZmFQcm9wZXJ0eTp2YWx1ZVR5cGU+\r\nCgkJCQkJCQkJPC9yZGY6bGk+CgkJCQkJCQk8L3JkZjpTZXE+CgkJCQkJCTwvcGRmYVNjaGVtYTpw\r\ncm9wZXJ0eT4KCQkJCQk8L3JkZjpsaT4KCQkJCQk8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291\r\ncmNlIj4KCQkJCQkJPHBkZmFTY2hlbWE6bmFtZXNwYWNlVVJJPmh0dHA6Ly93d3cuYWlpbS5vcmcv\r\ncGRmYS9ucy9pZC88L3BkZmFTY2hlbWE6bmFtZXNwYWNlVVJJPgoJCQkJCQk8cGRmYVNjaGVtYTpw\r\ncmVmaXg+cGRmYWlkPC9wZGZhU2NoZW1hOnByZWZpeD4KCQkJCQkJPHBkZmFTY2hlbWE6c2NoZW1h\r\nPlBERi9BIElEIFNjaGVtYTwvcGRmYVNjaGVtYTpzY2hlbWE+CgkJCQkJCTxwZGZhU2NoZW1hOnBy\r\nb3BlcnR5PgoJCQkJCQkJPHJkZjpTZXE+CgkJCQkJCQkJPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJS\r\nZXNvdXJjZSI+CgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6Y2F0ZWdvcnk+aW50ZXJuYWw8L3BkZmFQ\r\ncm9wZXJ0eTpjYXRlZ29yeT4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTpkZXNjcmlwdGlvbj5QYXJ0\r\nIG9mIFBERi9BIHN0YW5kYXJkPC9wZGZhUHJvcGVydHk6ZGVzY3JpcHRpb24+CgkJCQkJCQkJCTxw\r\nZGZhUHJvcGVydHk6bmFtZT5wYXJ0PC9wZGZhUHJvcGVydHk6bmFtZT4KCQkJCQkJCQkJPHBkZmFQ\r\ncm9wZXJ0eTp2YWx1ZVR5cGU+SW50ZWdlcjwvcGRmYVByb3BlcnR5OnZhbHVlVHlwZT4KCQkJCQkJ\r\nCQk8L3JkZjpsaT4KCQkJCQkJCQk8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KCQkJ\r\nCQkJCQkJPHBkZmFQcm9wZXJ0eTpjYXRlZ29yeT5pbnRlcm5hbDwvcGRmYVByb3BlcnR5OmNhdGVn\r\nb3J5PgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5OmRlc2NyaXB0aW9uPkFtZW5kbWVudCBvZiBQREYv\r\nQSBzdGFuZGFyZDwvcGRmYVByb3BlcnR5OmRlc2NyaXB0aW9uPgoJCQkJCQkJCQk8cGRmYVByb3Bl\r\ncnR5Om5hbWU+YW1kPC9wZGZhUHJvcGVydHk6bmFtZT4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTp2\r\nYWx1ZVR5cGU+VGV4dDwvcGRmYVByb3BlcnR5OnZhbHVlVHlwZT4KCQkJCQkJCQk8L3JkZjpsaT4K\r\nCQkJCQkJCQk8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KCQkJCQkJCQkJPHBkZmFQ\r\ncm9wZXJ0eTpjYXRlZ29yeT5pbnRlcm5hbDwvcGRmYVByb3BlcnR5OmNhdGVnb3J5PgoJCQkJCQkJ\r\nCQk8cGRmYVByb3BlcnR5OmRlc2NyaXB0aW9uPkNvbmZvcm1hbmNlIGxldmVsIG9mIFBERi9BIHN0\r\nYW5kYXJkPC9wZGZhUHJvcGVydHk6ZGVzY3JpcHRpb24+CgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6\r\nbmFtZT5jb25mb3JtYW5jZTwvcGRmYVByb3BlcnR5Om5hbWU+CgkJCQkJCQkJCTxwZGZhUHJvcGVy\r\ndHk6dmFsdWVUeXBlPlRleHQ8L3BkZmFQcm9wZXJ0eTp2YWx1ZVR5cGU+CgkJCQkJCQkJPC9yZGY6\r\nbGk+CgkJCQkJCQk8L3JkZjpTZXE+CgkJCQkJCTwvcGRmYVNjaGVtYTpwcm9wZXJ0eT4KCQkJCQk8\r\nL3JkZjpsaT4KCQkJCTwvcmRmOkJhZz4KCQkJPC9wZGZhRXh0ZW5zaW9uOnNjaGVtYXM+CgkJPC9y\r\nZGY6RGVzY3JpcHRpb24+Cgk8L3JkZjpSREY+CjwveDp4bXBtZXRhPgo8P3hwYWNrZXQgZW5kPSJ3\r\nIj8+CmVuZHN0cmVhbQplbmRvYmoKMTQgMCBvYmoKPDwgL1R5cGUgL0NhdGFsb2cgL1ZlcnNpb24g\r\nLzEuNyAvUGFnZXMgMSAwIFIgL05hbWVzIDw8ID4+IC9WaWV3ZXJQcmVmZXJlbmNlcyA8PCAvRGly\r\nZWN0aW9uIC9MMlIgPj4gL1BhZ2VMYXlvdXQgL1NpbmdsZVBhZ2UgL1BhZ2VNb2RlIC9Vc2VOb25l\r\nIC9PcGVuQWN0aW9uIFs3IDAgUiAvRml0SCBudWxsXSAvTWV0YWRhdGEgMTMgMCBSID4+CmVuZG9i\r\nagp4cmVmCjAgMTUKMDAwMDAwMDAwMCA2NTUzNSBmIAowMDAwMDA0NDYzIDAwMDAwIG4gCjAwMDAw\r\nMTg3NDQgMDAwMDAgbiAKMDAwMDAwNDUyOCAwMDAwMCBuIAowMDAwMDA0NjM0IDAwMDAwIG4gCjAw\r\nMDAwMTg4NjkgMDAwMDAgbiAKMDAwMDAxOTE0MSAwMDAwMCBuIAowMDAwMDAwMDE1IDAwMDAwIG4g\r\nCjAwMDAwMDA0ODMgMDAwMDAgbiAKMDAwMDAwMjg3MSAwMDAwMCBuIAowMDAwMDAzMzQwIDAwMDAw\r\nIG4gCjAwMDAwMDQ3NDUgMDAwMDAgbiAKMDAwMDAxOTM1NyAwMDAwMCBuIAowMDAwMDE5NzEwIDAw\r\nMDAwIG4gCjAwMDAwMjQwNzUgMDAwMDAgbiAKdHJhaWxlcgo8PCAvU2l6ZSAxNSAvUm9vdCAxNCAw\r\nIFIgL0luZm8gMTIgMCBSIC9JRCBbIDw0NTMwMjFkNDVjMmQ4OTFlNGViMjAzNzZhODkwYTQ0ND4g\r\nPDQ1MzAyMWQ0NWMyZDg5MWU0ZWIyMDM3NmE4OTBhNDQ0PiBdID4+CnN0YXJ0eHJlZgoyNDI4NAol\r\nJUVPRgo=\r\n\r\n\r\n\r\n--db2d6b9081ba5a8c649dce962cb65c53--\r\n');
/*!40000 ALTER TABLE `fcs_email_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_images` DISABLE KEYS */;
INSERT INTO `fcs_images` VALUES
(154,60),
(156,340),
(157,338);
/*!40000 ALTER TABLE `fcs_images` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_invoices` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_manufacturer` DISABLE KEYS */;
INSERT INTO `fcs_manufacturer` VALUES
(4,'Demo Fleisch-Hersteller','<p>tests</p>\r\n','','2014-05-14 13:23:02','2015-05-15 13:31:41',1,NULL,NULL,0,'','','','','','','','','','',NULL,0,1,1,2,'testfcs1@mailinator.com;testfcs2@mailinator.com',0,NULL,NULL,NULL,NULL,NULL,1,15,100),
(5,'Demo Gemüse-Hersteller','<p>Gem&uuml;se-Hersteller Beschreibung&nbsp;lang</p>','<div class=\"entry-content\">\r\n<p>Gem&uuml;se-Hersteller Beschreibung kurz</p>\r\n</div>','2014-05-14 13:36:44','2016-09-27 09:34:51',1,NULL,NULL,0,'','','','','','','','','','',NULL,10,1,1,1,'',0,NULL,NULL,NULL,NULL,'1',1,30,100),
(15,'Demo Milch-Hersteller','<p>Ja, ich bin der Milchhersteller!</p>','','2014-06-04 21:45:12','2016-03-07 09:02:25',1,NULL,NULL,0,'','','','','','','','','','',NULL,0,1,1,4,'test@test.at',0,NULL,NULL,NULL,NULL,NULL,0,30,100),
(16,'Hersteller ohne Customer-Eintrag','','','2014-06-04 21:45:12','2016-03-07 09:02:25',1,NULL,NULL,0,'','','','','','','','','','',NULL,10,1,1,1,'',0,NULL,NULL,NULL,NULL,NULL,0,30,100);
/*!40000 ALTER TABLE `fcs_manufacturer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail` DISABLE KEYS */;
INSERT INTO `fcs_order_detail` VALUES
(1,1,346,0,'Artischocke : Stück',1,1.652893,1.820000,1.652893,2,0.50),
(2,1,340,0,'Beuschl',1,4.545455,4.545455,4.545455,0,0.00),
(3,2,346,0,'Artischocke : Stück',2,2.455786,2.700000,2.455786,2,1.00),
(4,2,103,0,'Bratwürstel',5,13.859095,15.240000,13.859095,2,0.00),
(5,2,344,0,'Knoblauch : 100 g',1,0.476364,0.476364,0.476364,0,0.00),
(6,2,60,10,'Milch : 0,5l',3,1.636365,1.860000,1.636365,3,1.50);
/*!40000 ALTER TABLE `fcs_order_detail` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail_tax` DISABLE KEYS */;
INSERT INTO `fcs_order_detail_tax` VALUES
(1,0.170000,0.170000),
(2,0.000000,0.000000),
(3,0.120000,0.240000),
(4,0.280000,1.400000),
(5,0.000000,0.000000),
(6,0.070000,0.210000);
/*!40000 ALTER TABLE `fcs_order_detail_tax` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_orders` DISABLE KEYS */;
INSERT INTO `fcs_orders` VALUES
(1,92,1,3,6.365455,6.365455,6.198348,'2018-02-01 09:17:14','2018-02-01 09:17:14',0.50,1,1,''),
(2,92,2,3,18.416364,18.416364,16.791245,'2018-04-03 15:13:30','2018-04-03 15:13:30',2.50,1,1,NULL);
/*!40000 ALTER TABLE `fcs_orders` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_pages` DISABLE KEYS */;
INSERT INTO `fcs_pages` VALUES
(3,'Statuten','',1,'header',1,'',88,0,'2016-08-29 13:36:43','2016-08-29 13:36:43',0,NULL,0,0),
(4,'Über uns','',3,'header',1,'',88,0,'2016-08-29 13:36:43','2016-08-29 13:36:43',0,NULL,0,0),
(8,'Links','<h4><strong>Links</strong></h4>\r\n<ul>\r\n<li><a href=\"http://www.foodcoopshop.com\" target=\"_blank\">foodcoopshop.com</a>&nbsp;- Die Software f&uuml;r eure Foodcoop</li>\r\n<li><a href=\"http://www.fairteiler-scharnstein.at\" target=\"_blank\">Fairteiler Scharnstein</a></li>\r\n</ul>',2,'header',1,'',88,0,'2016-08-29 13:36:43','2016-08-29 13:36:43',0,NULL,0,0),
(10,'Mitmachen','',4,'header',1,'',88,0,'2016-08-29 13:36:43','2016-08-29 13:36:43',0,NULL,0,0),
(11,'Newsletter','',5,'header',-1,'',88,0,'2016-09-12 14:59:53','2016-08-29 13:36:43',0,NULL,0,0);
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
(47,'Lange <strong>Beschreibung</strong>','200ml<br />\nnoch ein text','','Joghurt',0),
(49,'','<p>250g</p>','','Topfen',0),
(60,'','1 Liter','','Milch',0),
(102,'','<p>2 Paar</p>','','Frankfurter',0),
(103,'','2 Paar','','Bratwürstel',0),
(161,'','St&uuml;ck, wei&szlig; oder lila (nur au&szlig;en und schmeckt genauso wie der wei&szlig;e)','Stück','Kohlrabi',0),
(163,'','0,25kg','','Mangold',0),
(173,'','<p>1kg</p>','','Zwiebel',0),
(225,'','Salattomate, rot und rund<br />\n500 g','500 g','Tomaten',0),
(279,'','<p>&nbsp;pro St&uuml;ck</p>','','Romanesco',0),
(338,'lange beschreibung','','ca. 300g','Streichwurst',0),
(339,'','','','Kartoffel',0),
(340,'','','','Beuschl',0),
(343,'','','1 kg','Rote Rüben',0),
(344,'','','100 g','Knoblauch',0),
(346,'','','Stück','Artischocke',0),
(347,'','','','Essigwurst',0),
(350,'','','','Neuer Artikel von Demo Milch-Hersteller',0),
(352,'','','100 g','Vogerlsalat',0),
(353,'','','','Neuer Artikel von Demo Gemüse-Hersteller',0),
(354,'','','Stück','Schnitzel',0),
(355,'','','','Neuer Artikel von Demo Gemüse-Hersteller',0),
(356,'','','','Neuer Artikel von Demo Fleisch-Hersteller',0),
(357,'','','','Neuer Artikel von Demo Gemüse-Hersteller',0),
(358,'','','','Neuer Artikel von Demo Gemüse-Hersteller',0),
(359,'','','','Neuer Artikel von Demo Fleisch-Hersteller',0),
(360,'','','','Neuer Artikel von Demo Fleisch-Hersteller',0),
(361,'<p>Supermenschen brauch Superwürstl</p>','<p>Unsere Superwurst für Supermenschen</p>','2kg','SuperWürstl',0),
(362,'','','','Neuer Artikel von Demo Fleisch-Hersteller',0);
/*!40000 ALTER TABLE `fcs_product_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_shop` DISABLE KEYS */;
INSERT INTO `fcs_product_shop` VALUES
(47,17,1.727273,'2014-06-11 21:20:24','2015-02-26 14:49:19'),
(49,17,1.090909,'2014-06-11 21:20:24','2015-02-26 14:46:32'),
(60,17,0.909091,'2014-06-11 21:20:24','2014-12-14 19:47:33'),
(102,20,0.000000,'2016-04-27 21:13:37','2014-09-19 14:32:51'),
(103,16,3.181819,'2016-05-05 08:28:49','2014-08-16 14:05:58'),
(161,20,1.363636,'2014-07-12 20:41:43','2014-12-14 19:35:39'),
(163,20,1.363637,'2014-07-12 20:41:43','2017-07-26 13:24:10'),
(173,20,1.545454,'2014-07-12 20:41:43','2014-12-14 19:36:10'),
(225,13,1.545454,'2014-07-19 21:05:13','2014-08-03 21:28:39'),
(279,13,2.000000,'2014-09-16 21:42:14','2014-12-14 19:35:50'),
(338,2,4.464286,'2014-11-10 20:02:35','2014-12-14 19:35:58'),
(339,15,0.000000,'2015-09-07 12:05:38','2015-02-26 13:54:07'),
(340,20,4.545455,'2016-05-05 08:28:45','2015-06-23 14:52:53'),
(341,20,0.000000,'2015-06-23 15:58:11','2015-06-23 15:58:11'),
(342,20,0.000000,'2015-06-23 21:55:52','2015-06-23 21:55:52'),
(343,20,1.900000,'2015-07-06 09:46:19','2015-07-06 09:46:19'),
(344,20,0.636364,'2015-10-05 17:22:40','2015-07-06 10:24:44'),
(346,20,1.652893,'2015-08-19 09:35:46','2015-08-19 09:35:46'),
(347,20,0.000000,'2015-08-24 20:36:58','2015-08-24 20:36:58'),
(348,20,0.000000,'2015-09-05 14:54:39','2015-09-05 14:54:39'),
(350,20,0.000000,'2015-09-07 12:11:00','2015-09-07 12:11:00'),
(352,20,2.000000,'2015-10-05 17:21:00','2015-10-05 17:21:00'),
(353,20,0.000000,'2015-10-12 21:48:14','2015-10-12 21:48:14'),
(354,20,9.090909,'2016-05-14 13:31:36','2015-11-24 22:37:49'),
(355,20,0.000000,'2016-02-10 08:44:47','2016-02-10 08:44:47'),
(356,20,0.000000,'2016-03-23 12:10:17','2016-03-23 12:10:17'),
(357,20,0.000000,'2016-03-23 21:50:37','2016-03-23 21:50:37'),
(358,20,0.000000,'2016-03-23 21:51:04','2016-03-23 21:51:04'),
(359,20,0.000000,'2016-03-24 17:53:06','2016-03-24 17:53:06'),
(360,20,2.727273,'2016-04-08 10:35:56','2016-04-08 10:35:56'),
(361,20,9.090909,'2016-05-03 17:08:33','2016-05-03 17:25:29'),
(362,20,0.000000,'2016-05-25 13:15:31','2016-05-25 13:15:31');
/*!40000 ALTER TABLE `fcs_product_shop` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_sliders` DISABLE KEYS */;
INSERT INTO `fcs_sliders` VALUES
(6,'2be64c60e6126c9085fd9d9717532a14e5a5bb4e_slide4.png',0,1);
/*!40000 ALTER TABLE `fcs_sliders` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_stock_available` DISABLE KEYS */;
INSERT INTO `fcs_stock_available` VALUES
(54,1,0,0),
(55,2,0,0),
(56,3,0,0),
(57,4,0,0),
(58,5,0,0),
(59,6,0,0),
(60,7,0,0),
(101,9,0,0),
(102,15,0,0),
(103,16,0,0),
(104,31,0,0),
(105,32,0,0),
(106,33,0,0),
(119,47,0,100),
(121,49,0,910),
(132,60,0,2012),
(195,102,0,2996),
(196,103,0,985),
(252,83,0,0),
(316,161,0,996),
(318,163,0,988),
(328,173,0,931),
(393,225,0,10),
(436,238,0,0),
(466,279,0,916),
(572,338,0,15),
(615,40,0,0),
(616,43,0,0),
(617,56,0,0),
(618,57,0,0),
(619,58,0,0),
(620,59,0,0),
(621,64,0,0),
(622,65,0,0),
(623,66,0,0),
(624,67,0,0),
(625,68,0,0),
(626,75,0,0),
(627,76,0,0),
(628,81,0,0),
(629,82,0,0),
(630,84,0,0),
(631,85,0,0),
(632,86,0,0),
(633,87,0,0),
(634,88,0,0),
(635,89,0,0),
(636,112,0,0),
(637,114,0,0),
(638,115,0,0),
(639,128,0,0),
(640,140,0,0),
(641,141,0,0),
(642,142,0,0),
(643,143,0,0),
(644,144,0,0),
(645,145,0,0),
(646,146,0,0),
(647,150,0,0),
(648,154,0,0),
(649,178,0,0),
(650,218,0,0),
(651,219,0,0),
(652,220,0,0),
(653,229,0,0),
(654,230,0,0),
(655,281,0,0),
(656,297,0,0),
(657,299,0,0),
(658,300,0,0),
(659,301,0,0),
(660,302,0,0),
(661,303,0,0),
(662,304,0,0),
(663,305,0,0),
(664,306,0,0),
(665,314,0,0),
(666,315,0,0),
(667,316,0,0),
(668,317,0,0),
(669,358,0,999),
(670,359,0,999),
(671,360,0,999),
(672,361,0,999),
(673,362,0,999),
(674,339,0,2959),
(675,339,1,964),
(676,339,2,995),
(677,339,3,1000),
(678,340,0,990),
(679,343,0,15),
(680,344,0,77),
(682,0,0,20),
(686,346,0,95),
(687,347,0,998),
(691,350,0,999),
(692,60,9,996),
(693,60,10,17),
(694,60,11,999),
(696,352,0,999),
(697,353,0,999),
(700,354,0,999),
(701,102,14,999),
(702,102,15,998),
(703,102,16,999),
(704,355,0,999),
(705,347,17,998),
(706,356,0,999),
(707,357,0,999),
(708,361,18,999);
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

/*!40000 ALTER TABLE `fcs_timebased_currency_order_detail` DISABLE KEYS */;
INSERT INTO `fcs_timebased_currency_order_detail` VALUES
(3,0.850000,0.940000,336,30,10.50),
(4,2.050000,2.260000,805,15,10.50),
(5,0.160000,0.160000,59,30,10.50);
/*!40000 ALTER TABLE `fcs_timebased_currency_order_detail` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_timebased_currency_orders` DISABLE KEYS */;
INSERT INTO `fcs_timebased_currency_orders` VALUES
(2,3.590000,3.940000,1200);
/*!40000 ALTER TABLE `fcs_timebased_currency_orders` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_timebased_currency_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_timebased_currency_payments` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

