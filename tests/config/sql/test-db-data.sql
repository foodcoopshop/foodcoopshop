
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
TRUNCATE TABLE `fcs_order_detail_units`;
TRUNCATE TABLE `fcs_orders`;
TRUNCATE TABLE `fcs_pages`;
TRUNCATE TABLE `fcs_payments`;
TRUNCATE TABLE `fcs_product`;
TRUNCATE TABLE `fcs_product_attribute`;
TRUNCATE TABLE `fcs_product_attribute_combination`;
TRUNCATE TABLE `fcs_sliders`;
TRUNCATE TABLE `fcs_stock_available`;
TRUNCATE TABLE `fcs_sync_domains`;
TRUNCATE TABLE `fcs_sync_products`;
TRUNCATE TABLE `fcs_tax`;
TRUNCATE TABLE `fcs_timebased_currency_order_detail`;
TRUNCATE TABLE `fcs_timebased_currency_orders`;
TRUNCATE TABLE `fcs_timebased_currency_payments`;
TRUNCATE TABLE `fcs_units`;

/*!40000 ALTER TABLE `fcs_action_logs` DISABLE KEYS */;
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
(33,'0,5l',0,1,NULL,NULL),
(35,'1 kg',1,1,NULL,NULL),
(36,'0,5 kg',1,1,NULL,NULL);
/*!40000 ALTER TABLE `fcs_attribute` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_blog_posts` DISABLE KEYS */;
INSERT INTO `fcs_blog_posts` VALUES
(2,'Demo Blog Artikel','Lorem ipsum dolor sit amet, consetetur sadipscing','<p>Lorem ipsum dolor sit amet.</p>',88,0,0,1,'2014-12-18 10:37:26','2015-03-16 12:41:46',1);
/*!40000 ALTER TABLE `fcs_blog_posts` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cart_products` DISABLE KEYS */;
INSERT INTO `fcs_cart_products` VALUES
(1,1,346,0,1,'2018-03-01 10:17:14','2018-03-01 10:17:14'),
(2,1,340,0,1,'2018-03-01 10:17:14','2018-03-01 10:17:14');
/*!40000 ALTER TABLE `fcs_cart_products` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_carts` DISABLE KEYS */;
INSERT INTO `fcs_carts` VALUES
(1,92,0,'2018-03-01 10:17:14','2018-03-01 10:17:14');
/*!40000 ALTER TABLE `fcs_carts` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category` DISABLE KEYS */;
INSERT INTO `fcs_category` VALUES
(16,0,'Fleischprodukte','',11,12,1,'2014-05-14 21:40:51','2014-05-14 21:48:48'),
(20,0,'Alle Produkte','',3,4,1,'2014-05-14 21:53:52','2014-05-17 13:14:22');
/*!40000 ALTER TABLE `fcs_category` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category_product` DISABLE KEYS */;
INSERT INTO `fcs_category_product` VALUES
(16,339),
(20,60),
(20,102),
(20,103),
(20,163),
(20,339),
(20,340),
(20,344),
(20,346),
(20,347),
(20,348);
/*!40000 ALTER TABLE `fcs_category_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_configuration` DISABLE KEYS */;
INSERT INTO `fcs_configuration` VALUES
(11,1,'FCS_PRODUCT_AVAILABILITY_LOW','Geringe Verfügbarkeit<br /><div class=\"small\">Ab welcher verfügbaren Produkt-Anzahl soll beim Bestellen der Hinweis \"(x verfügbar\") angezeigt werden?</div>','10','number',60,'de_DE','2017-07-26 13:19:19','2014-06-01 01:40:34'),
(31,1,'FCS_DAYS_SHOW_PRODUCT_AS_NEW','Wie viele Tage sollen Produkte \"als neu markiert\" bleiben?','7','number',70,'de_DE','2017-07-26 13:19:19','2014-05-14 21:15:45'),
(164,1,'FCS_CUSTOMER_GROUP','Welcher Gruppe sollen neu registrierte Mitglieder zugewiesen werden?','3','dropdown',40,'de_DE','2017-07-26 13:19:19','2017-07-26 13:19:19'),
(456,1,'FCS_FOOTER_CMS_TEXT','Zusätzlicher Text für den Footer',NULL,'textarea_big',80,'de_DE','2014-06-11 17:50:55','2016-07-01 21:47:47'),
(508,1,'FCS_FACEBOOK_URL','Facebook-Url für die Einbindung im Footer','https://www.facebook.com/FoodCoopShop/','text',90,'de_DE','2015-07-08 13:23:54','2015-07-08 13:23:54'),
(538,1,'FCS_REGISTRATION_EMAIL_TEXT','Zusätzlicher Text, der in der Bestätigungsmail nach einer Registrierung versendet wird. <br /> <a href=\"/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT\" target=\"_blank\"><img src=\"/js/vendor/famfamfam-silk/dist/png/information.png?1483041252\" alt=\"\"> E-Mail-Vorschau anzeigen</a>','','textarea_big',170,'de_DE','2016-06-26 00:00:00','2016-06-26 00:00:00'),
(543,1,'FCS_RIGHT_INFO_BOX_HTML','Inhalt der Box in der rechten Spalte unterhalb des Warenkorbes. <br /><div class=\"small\">Um eine Zeile grün zu hinterlegen (Überschrift) bitte als \"Überschrift 3\" formatieren.<br />Die Variable {ABHOLTAG} zeigt automatisch das richtige Abholdatum an.</div>','<h3>Abholzeiten</h3>\r\n\r\n<p>Wenn du deine Produkte jetzt bestellst, kannst du sie am <strong>{ABHOLTAG}</strong>&nbsp;zwischen 17 und 19 Uhr abholen.</p>\r\n\r\n<p>Du kannst jede Woche bis sp&auml;testens Dienstag Mitternacht bestellen und sie am darauffolgenden Freitag abholen.</p>\r\n','textarea_big',150,'de_DE','2017-07-26 13:19:19','2017-07-26 13:19:19'),
(544,1,'FCS_CART_ENABLED','Ist die Bestell-Funktion aktiviert?<br /><div class=\"small\">Falls die Foodcoop mal Urlaub macht, kann das Bestellen hier deaktiviert werden.</div>','1','boolean',10,'de_DE','2017-07-26 13:19:19','2017-07-26 13:19:19'),
(545,1,'FCS_ACCOUNTING_EMAIL','E-Mail-Adresse des Finanzverantwortlichen<br /><div class=\"small\">Wer bekommt die Benachrichtigung über den erfolgten Rechnungsversand?</div>','','text',110,'de_DE','2017-07-26 13:19:19','2017-07-26 13:19:19'),
(546,1,'FCS_AUTHENTICATION_INFO_TEXT','Info-Text beim Registrierungsformular<br /><div class=\"small\">Beim Registrierungsformlar wird unterhalb der E-Mail-Adresse dieser Text angezeigt.</div>','Um bei uns zu bestellen musst du Vereinsmitglied sein.','textarea',160,'de_DE','2017-07-26 13:19:19','2017-07-26 13:19:19'),
(547,1,'FCS_SHOW_PRODUCTS_FOR_GUESTS','Produkte für nicht eingeloggte Mitglieder sichtbar?','0','boolean',20,'de_DE','2017-07-26 13:19:19','2017-07-26 13:19:19'),
(548,1,'FCS_DEFAULT_NEW_MEMBER_ACTIVE','Neue Mitglieder automatisch aktivieren?','0','boolean',50,'de_DE','2017-07-26 13:19:19','2017-07-26 13:19:19'),
(549,1,'FCS_MINIMAL_CREDIT_BALANCE','Höhe des Bestell-Limits, ab dem den Mitgliedern kein Bestellen mehr möglich ist.<br /><div class=\"small\">Z.B.: \"100\" für 100 € im Minus. 0 bedeutet \"kein Bestell-Limit\".</div>','100','number',125,'de_DE','2017-07-26 13:19:19','2017-07-26 13:19:19'),
(550,1,'FCS_BANK_ACCOUNT_DATA','Bankverbindung für die Guthaben-Einzahlungen\".','Guthaben-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',130,'de_DE','2017-07-26 13:19:19','2017-07-26 13:19:19'),
(551,1,'FCS_MEMBER_FEE_BANK_ACCOUNT_DATA','Bankverbindung für die Mitgliedsbeitrags-Einzahlungen\".','MB-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',140,'de_DE','2017-07-26 13:19:19','2017-07-26 13:19:19'),
(552,1,'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS','Zusätzliche Liefer-Informationen für die Hersteller<br /><div class=\"small\">wird in den Bestell-Listen nach dem Lieferdatum angezeigt.</div>',', 15:00 bis 17:00 Uhr','text',120,'de_DE','2017-07-26 13:19:19','2017-07-26 13:19:19'),
(553,1,'FCS_BACKUP_EMAIL_ADDRESS_BCC','E-Mail-Adresse, an die sämtliche vom System generierten E-Mails als BCC verschickt werden (Backup).<br /><div class=\"small\">Kann leer gelassen werden.</div>','','text',190,'de_DE','2016-10-06 00:00:00','2016-10-06 00:00:00'),
(554,1,'FCS_SHOW_FOODCOOPSHOP_BACKLINK','Auf Homepage Link auf www.foodcoopshop.com anzeigen?<br /><div class=\"small\">Der Link wird im Footer angezeigt.</div>','1','boolean',180,'de_DE','2016-11-27 00:00:00','2016-11-27 00:00:00'),
(555,1,'FCS_PAYMENT_PRODUCT_MAXIMUM','Maximalbetrag für jede Guthaben-Aufladung in Euro','500','number',127,'de_DE','2016-11-28 00:00:00','2016-11-28 00:00:00'),
(556,1,'FCS_APP_NAME','Name der Foodcoop','FoodCoop Test','text',5,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(557,1,'FCS_APP_ADDRESS','Adresse der Foodcoop<br /><div class=\"small\">Wird im Footer von Homepage und E-Mails, Datenschutzerklärung, Nutzungsbedingungen usw. verwendet.</div>','Demostra&szlig;e 4,<br />\r\nA-4564 Demostadt','textarea',6,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(558,1,'FCS_APP_EMAIL','E-Mail-Adresse der Foodcoop<br /><div class=\"small\"></div>','demo-foodcoop@maillinator.com','text',7,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(559,1,'FCS_PLATFORM_OWNER','Betreiber der Plattform<br /><div class=\"small\">Für Datenschutzerklärung und Nutzungsbedingungen, bitte auch Adresse angeben. Kann leer gelassen werden, wenn die Foodcoop selbst die Plattform betreibt.</div>','','textarea',8,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(560,1,'FCS_INSTANT_ORDER_DEFAULT_STATE','Bestellstatus für Sofort-Bestellungen','1','dropdown',75,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(563,1,'FCS_EMAIL_LOG_ENABLED','Sollen alle ausgehenden E-Mails in der Datenbank gespeichert werden?<br /><div class=\"small\">Für Debugging gedacht.</div>','1','readonly',30,'de_DE','2017-07-05 00:00:00','2017-07-05 00:00:00'),
(564,1,'FCS_ORDER_COMMENT_ENABLED','Kommentarfeld bei Bestell-Abschluss anzeigen?<br /><div class=\"small\">Wird im Admin-Bereich unter \"Bestellungen\" angezeigt.</div>','1','boolean',13,'de_DE','2017-07-09 00:00:00','2017-07-09 00:00:00'),
(565,1,'FCS_USE_VARIABLE_MEMBER_FEE','Variablen Mitgliedsbeitrag verwenden?<br /><div class=\"small\">Den variablen Mitgliedsbeitrag bei den Hersteller-Rechnungen abziehen? Die Produkt-Preise müssen entsprechend höher eingegeben werden.</div>','0','readonly',40,'de_DE','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(566,1,'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE','Standardwert für variablen Mitgliedsbeitrag<br /><div class=\"small\">Der Prozentsatz kann in den Hersteller-Einstellungen auch individuell angepasst werden.</div>','0','readonly',50,'de_DE','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(567,1,'FCS_NETWORK_PLUGIN_ENABLED','Netzwerk-Modul aktiviert?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/netzwerk-modul\" target=\"_blank\">Infos zum Netzwerk-Modul</a></div>','1','readonly',50,'de_DE','2017-09-14 00:00:00','2017-09-14 00:00:00'),
(568,1,'FCS_TIMEBASED_CURRENCY_ENABLED','Stundenabrechnungs-Modul aktiv?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/stundenabrechnungs-modul\" target=\"_blank\">Infos zum Stundenabrechnungs-Modul</a></div>','0','boolean',200,'de_DE','2018-03-16 15:23:31','2018-03-16 15:23:31'),
(569,1,'FCS_TIMEBASED_CURRENCY_NAME','Stundenabrechnung: Name der Einheit<br /><div class=\"small\">max. 10 Zeichen</div>','Stunden','text',210,'de_DE','2018-03-16 15:23:31','2018-03-16 15:23:31'),
(570,1,'FCS_TIMEBASED_CURRENCY_SHORTCODE','Stundenabrechnung: Abkürzung<br /><div class=\"small\">max. 3 Zeichen</div>','h','text',220,'de_DE','2018-03-16 15:23:31','2018-03-16 15:23:31'),
(571,1,'FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE','Stundenabrechnung: Umrechnungskurs<br /><div class=\"small\">in €, 2 Kommastellen</div>','10,00','number',230,'de_DE','2018-03-16 15:23:31','2018-03-16 15:23:31'),
(572,1,'FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_CUSTOMER','Stundenabrechnung: Überziehungsrahmen für Mitglieder<br /><div class=\"small\">Wie viele Stunden kann ein Mitglied maximal ins Minus gehen?</div>','0','number',240,'de_DE','2018-03-16 15:23:31','2018-03-16 15:23:31'),
(573,1,'FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_MANUFACTURER','Stundenabrechnung: Überziehungsrahmen für Hersteller<br /><div class=\"small\">Wie viele Stunden kann ein Hersteller maximal ins Plus gehen?</div>','0','number',250,'de_DE','2018-03-16 15:23:31','2018-03-16 15:23:31'),
(574,1,'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS','Produktpreis für nicht eingeloggte Mitglieder anzeigen?','0','boolean',21,'de_DE','2018-05-28 18:05:57','2018-05-28 18:05:57'),
(575,1,'FCS_CURRENCY_SYMBOL','Währungssymbol','€','readonly',52,'de_DE','2018-06-13 19:53:14','2018-06-13 19:53:14'),
(576,1,'FCS_DEFAULT_LOCALE','Sprache','de_DE','readonly',55,'de_DE','2018-06-26 10:18:55','2018-06-26 10:18:55');
/*!40000 ALTER TABLE `fcs_configuration` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_customer` DISABLE KEYS */;
INSERT INTO `fcs_customer` VALUES
(87,3,'Demo','Mitglied','fcs-demo-mitglied@mailinator.com','',NULL,1,'1000-01-01',1,'2014-12-02 12:19:31','2015-12-06 23:37:44',0),
(88,4,'Demo','Admin','fcs-demo-admin@mailinator.com','',NULL,1,'1000-01-01',1,'2014-12-02 12:28:43','2016-09-29 16:25:09',0),
(89,4,'Demo','Gemüse-Hersteller','fcs-demo-gemuese-hersteller@mailinator.com','',NULL,0,'1000-01-01',1,'2014-12-02 12:37:26','2015-03-11 18:12:10',0),
(90,4,'Demo','Milch-Hersteller','fcs-demo-milch-hersteller@mailinator.com','',NULL,0,'1000-01-01',1,'2014-12-02 12:37:49','2015-03-11 18:11:54',0),
(91,4,'Demo','Fleisch-Hersteller','fcs-demo-fleisch-hersteller@mailinator.com','',NULL,0,'1000-01-01',1,'2014-12-02 12:38:12','2015-03-11 18:11:47',0),
(92,5,'Demo','Superadmin','fcs-demo-superadmin@mailinator.com','',NULL,1,'1000-01-01',1,'2016-09-29 16:26:12','2016-09-29 16:26:12',0);
/*!40000 ALTER TABLE `fcs_customer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_deposits` DISABLE KEYS */;
INSERT INTO `fcs_deposits` VALUES
(1,346,0,0.5),
(2,0,9,0.5),
(3,0,10,0.5);
/*!40000 ALTER TABLE `fcs_deposits` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_email_logs` DISABLE KEYS */;
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
(4,'Demo Fleisch-Hersteller','<p>tests</p>\r\n','','2014-05-14 13:23:02','2015-05-15 13:31:41',1,NULL,NULL,0,'','','','','','','','','','',NULL,0,1,1,2,'testfcs1@mailinator.com;testfcs2@mailinator.com',0,NULL,NULL,NULL,NULL,NULL,0,30,100),
(5,'Demo Gemüse-Hersteller','<p>Gem&uuml;se-Hersteller Beschreibung&nbsp;lang</p>','<div class=\"entry-content\">\r\n<p>Gem&uuml;se-Hersteller Beschreibung kurz</p>\r\n</div>','2014-05-14 13:36:44','2016-09-27 09:34:51',1,NULL,NULL,0,'','','','','','','','','','',NULL,10,1,1,1,'',0,NULL,NULL,NULL,NULL,'1',0,30,100),
(15,'Demo Milch-Hersteller','<p>Ja, ich bin der Milchhersteller!</p>','','2014-06-04 21:45:12','2016-03-07 09:02:25',1,NULL,NULL,0,'','','','','','','','','','',NULL,0,1,1,4,'test@test.at',0,NULL,NULL,NULL,NULL,NULL,0,30,100),
(16,'Hersteller ohne Customer-Eintrag','','','2014-06-04 21:45:12','2016-03-07 09:02:25',1,NULL,NULL,0,'','','','','','','','','','',NULL,10,1,1,1,'',0,NULL,NULL,NULL,NULL,NULL,0,30,100);
/*!40000 ALTER TABLE `fcs_manufacturer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail` DISABLE KEYS */;
INSERT INTO `fcs_order_detail` VALUES
(1,1,346,0,'Artischocke : Stück',1,1.820000,1.652893,2,0.50),
(2,1,340,0,'Beuschl',1,4.545455,4.545455,0,0.00);
/*!40000 ALTER TABLE `fcs_order_detail` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail_tax` DISABLE KEYS */;
INSERT INTO `fcs_order_detail_tax` VALUES
(1,0.170000,0.170000),
(2,0.000000,0.000000);
/*!40000 ALTER TABLE `fcs_order_detail_tax` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail_units` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_orders` DISABLE KEYS */;
INSERT INTO `fcs_orders` VALUES
(1,92,1,3,6.365455,6.365455,6.198348,'2018-02-01 09:17:14','2018-02-01 09:17:14',0.50,1,1,'');
/*!40000 ALTER TABLE `fcs_orders` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_pages` DISABLE KEYS */;
INSERT INTO `fcs_pages` VALUES
(3,'Page','',1,'header',1,'',88,0,'2016-08-29 13:36:43','2016-08-29 13:36:43',0,NULL,0,0);
/*!40000 ALTER TABLE `fcs_pages` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_payments` DISABLE KEYS */;
INSERT INTO `fcs_payments` VALUES
(1,92,0,'product',100.00,'','2018-07-03 20:00:20','2018-07-03 20:00:20',1,0,'',0,92);
/*!40000 ALTER TABLE `fcs_payments` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product` DISABLE KEYS */;
INSERT INTO `fcs_product` VALUES
(60,15,3,0,0.909091,'Milch','','1 Liter','',0,1,'2014-06-11 21:20:24','2014-12-14 19:47:33'),
(102,4,2,0,0.000000,'Frankfurter','','<p>2 Paar</p>','',0,1,'2016-04-27 21:13:37','2014-09-19 14:32:51'),
(103,4,2,0,3.181819,'Bratwürstel','','2 Paar','',0,1,'2016-05-05 08:28:49','2014-08-16 14:05:58'),
(163,5,0,0,1.363637,'Mangold','','0,25kg','',0,1,'2014-07-12 20:41:43','2017-07-26 13:24:10'),
(339,5,0,0,0.000000,'Kartoffel','','','',0,1,'2015-09-07 12:05:38','2015-02-26 13:54:07'),
(340,4,0,0,4.545455,'Beuschl','','','',0,1,'2016-05-05 08:28:45','2015-06-23 14:52:53'),
(344,5,0,0,0.636364,'Knoblauch','','','100 g',0,1,'2015-10-05 17:22:40','2015-07-06 10:24:44'),
(346,5,2,0,1.652893,'Artischocke','','','Stück',0,1,'2015-08-19 09:35:46','2015-08-19 09:35:45'),
(347,4,2,0,0.000000,'Forelle','','','Stück',0,1,'2018-05-17 16:13:39','2018-05-17 16:15:21'),
(348,4,2,0,0.000000,'Rindfleisch','','','',0,1,'2018-05-17 16:15:33','2018-05-17 16:16:38');
/*!40000 ALTER TABLE `fcs_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute` DISABLE KEYS */;
INSERT INTO `fcs_product_attribute` VALUES
(10,60,0.545455,0,0),
(11,348,0.000000,0,1),
(12,348,0.000000,0,0);
/*!40000 ALTER TABLE `fcs_product_attribute` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute_combination` DISABLE KEYS */;
INSERT INTO `fcs_product_attribute_combination` VALUES
(33,10),
(35,12),
(36,11);
/*!40000 ALTER TABLE `fcs_product_attribute_combination` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_sliders` DISABLE KEYS */;
INSERT INTO `fcs_sliders` VALUES
(6,'2be64c60e6126c9085fd9d9717532a14e5a5bb4e_slide4.png',0,1);
/*!40000 ALTER TABLE `fcs_sliders` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_stock_available` DISABLE KEYS */;
INSERT INTO `fcs_stock_available` VALUES
(132,60,0,2979),
(195,102,0,2996),
(196,103,0,990),
(318,163,0,988),
(674,339,0,2959),
(678,340,0,990),
(680,344,0,78),
(686,346,0,97),
(692,60,9,996),
(693,60,10,20),
(704,347,0,999),
(705,348,0,1998),
(706,348,11,999),
(707,348,12,999);
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
/*!40000 ALTER TABLE `fcs_timebased_currency_order_detail` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_timebased_currency_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_timebased_currency_orders` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_timebased_currency_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_timebased_currency_payments` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_units` DISABLE KEYS */;
INSERT INTO `fcs_units` VALUES
(1,347,0,1.50,'g',100,1,350.000),
(2,0,11,20.00,'kg',1,1,0.500),
(3,0,12,20.00,'kg',1,1,1.000);
/*!40000 ALTER TABLE `fcs_units` ENABLE KEYS */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

