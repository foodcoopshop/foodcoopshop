
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

-- Truncate tables before insertion
TRUNCATE TABLE `fcs_action_logs`;
TRUNCATE TABLE `fcs_address`;
TRUNCATE TABLE `fcs_attribute`;
TRUNCATE TABLE `fcs_blog_posts`;
TRUNCATE TABLE `fcs_cart_product_units`;
TRUNCATE TABLE `fcs_cart_products`;
TRUNCATE TABLE `fcs_carts`;
TRUNCATE TABLE `fcs_category`;
TRUNCATE TABLE `fcs_category_product`;
TRUNCATE TABLE `fcs_configuration`;
TRUNCATE TABLE `fcs_cronjob_logs`;
TRUNCATE TABLE `fcs_cronjobs`;
TRUNCATE TABLE `fcs_customer`;
TRUNCATE TABLE `fcs_deposits`;
TRUNCATE TABLE `fcs_images`;
TRUNCATE TABLE `fcs_invoice_taxes`;
TRUNCATE TABLE `fcs_invoices`;
TRUNCATE TABLE `fcs_manufacturer`;
TRUNCATE TABLE `fcs_order_detail`;
TRUNCATE TABLE `fcs_order_detail_feedbacks`;
TRUNCATE TABLE `fcs_order_detail_tax`;
TRUNCATE TABLE `fcs_order_detail_units`;
TRUNCATE TABLE `fcs_pages`;
TRUNCATE TABLE `fcs_payments`;
TRUNCATE TABLE `fcs_pickup_days`;
TRUNCATE TABLE `fcs_product`;
TRUNCATE TABLE `fcs_product_attribute`;
TRUNCATE TABLE `fcs_product_attribute_combination`;
TRUNCATE TABLE `fcs_sliders`;
TRUNCATE TABLE `fcs_stock_available`;
TRUNCATE TABLE `fcs_sync_domains`;
TRUNCATE TABLE `fcs_sync_products`;
TRUNCATE TABLE `fcs_tax`;
TRUNCATE TABLE `fcs_timebased_currency_order_detail`;
TRUNCATE TABLE `fcs_timebased_currency_payments`;
TRUNCATE TABLE `fcs_units`;
TRUNCATE TABLE `phinxlog`;
TRUNCATE TABLE `queue_phinxlog`;
TRUNCATE TABLE `queue_processes`;
TRUNCATE TABLE `queued_jobs`;
TRUNCATE TABLE `queued_tasks`;

/*!40000 ALTER TABLE `fcs_action_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_action_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_address` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_attribute` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_attribute` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_blog_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_blog_posts` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cart_product_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cart_product_units` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cart_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cart_products` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_carts` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category` DISABLE KEYS */;
INSERT INTO `fcs_category` VALUES
(20,2,'Alle Produkte','',3,4,1,'2016-10-19 21:05:00','2016-10-19 21:05:00');
/*!40000 ALTER TABLE `fcs_category` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_category_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_configuration` DISABLE KEYS */;
INSERT INTO `fcs_configuration` VALUES
(11,1,'FCS_PRODUCT_AVAILABILITY_LOW','Geringe Verfügbarkeit<br /><div class=\"small\">Ab welcher verfügbaren Produkt-Anzahl soll beim Bestellen der Hinweis \"(x verfügbar\") angezeigt werden?</div>','10','number',600,'de_DE','2017-07-26 13:24:47','2014-06-01 01:40:34'),
(31,1,'FCS_DAYS_SHOW_PRODUCT_AS_NEW','Wie viele Tage sollen Produkte \"als neu markiert\" bleiben?','7','number',700,'de_DE','2017-07-26 13:24:47','2014-05-14 21:15:45'),
(164,1,'FCS_CUSTOMER_GROUP','Welcher Gruppe sollen neu registrierte Mitglieder zugewiesen werden?','3','dropdown',400,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(456,1,'FCS_FOOTER_CMS_TEXT','Zusätzlicher Text für den Footer',NULL,'textarea_big',800,'de_DE','2014-06-11 17:50:55','2016-07-01 21:47:47'),
(508,1,'FCS_FACEBOOK_URL','Facebook-Url für die Einbindung im Footer','https://www.facebook.com/FoodCoopShop/','text',900,'de_DE','2015-07-08 13:23:54','2015-07-08 13:23:54'),
(538,1,'FCS_REGISTRATION_EMAIL_TEXT','Zusätzlicher Text, der in der Bestätigungsmail nach einer Registrierung versendet wird. <br /> <a href=\"/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT\" target=\"_blank\"><i class=\"fas fa-info-circle\"></i> E-Mail-Vorschau anzeigen</a>','','textarea_big',1700,'de_DE','2016-06-26 00:00:00','2016-06-26 00:00:00'),
(543,1,'FCS_RIGHT_INFO_BOX_HTML','Inhalt der Box in der rechten Spalte unterhalb des Warenkorbes. <br /><div class=\"small\">Um eine Zeile grün zu hinterlegen (Überschrift) bitte als \"Überschrift 3\" formatieren.</div>','<h3>Abholzeiten</h3>\r\n\r\n<p>Du kannst jede Woche bis sp&auml;testens Dienstag Mitternacht bestellen und die Produkte am Freitag abholen.</p>\r\n','textarea_big',1500,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(544,1,'FCS_NO_DELIVERY_DAYS_GLOBAL','Lieferpause für alle Hersteller?<br /><div class=\"small\">Hier können lieferfreie Tage (z.B. Feiertage) für die gesamte Foodcoop festgelegt werden.</div>','','multiple_dropdown',100,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(545,1,'FCS_ACCOUNTING_EMAIL','E-Mail-Adresse des Finanzverantwortlichen<br /><div class=\"small\">Wer bekommt die Benachrichtigung über den erfolgten Rechnungsversand?</div>','','text',1100,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(546,1,'FCS_REGISTRATION_INFO_TEXT','Info-Text beim Registrierungsformular<br /><div class=\"small\">Beim Registrierungsformlar wird unterhalb der E-Mail-Adresse dieser Text angezeigt.</div>','Um bei uns zu bestellen musst du Vereinsmitglied sein.','textarea_big',1600,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(547,1,'FCS_SHOW_PRODUCTS_FOR_GUESTS','Produkte für nicht eingeloggte Mitglieder sichtbar?','0','boolean',200,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(548,1,'FCS_DEFAULT_NEW_MEMBER_ACTIVE','Neue Mitglieder automatisch aktivieren?','0','boolean',500,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(549,1,'FCS_MINIMAL_CREDIT_BALANCE','Bis zu welchem Guthaben-Betrag sollen Bestellungen möglich sein?','0','number',1250,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(550,1,'FCS_BANK_ACCOUNT_DATA','Bankverbindung für die Guthaben-Einzahlungen\".','Guthaben-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',1300,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(551,1,'FCS_MEMBER_FEE_BANK_ACCOUNT_DATA','Bankverbindung für die Mitgliedsbeitrags-Einzahlungen\".','MB-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',1400,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(552,1,'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS','Zusätzliche Liefer-Informationen für die Hersteller<br /><div class=\"small\">wird in den Bestell-Listen nach dem Lieferdatum angezeigt.</div>',', 15:00 bis 17:00 Uhr','text',1200,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(553,1,'FCS_BACKUP_EMAIL_ADDRESS_BCC','E-Mail-Adresse, an die sämtliche vom System generierten E-Mails als BCC verschickt werden (Backup).<br /><div class=\"small\">Kann leer gelassen werden.</div>','','text',1900,'de_DE','2016-10-06 00:00:00','2016-10-06 00:00:00'),
(554,1,'FCS_SHOW_FOODCOOPSHOP_BACKLINK','Auf Homepage Link auf www.foodcoopshop.com anzeigen?<br /><div class=\"small\">Der Link wird im Footer angezeigt.</div>','1','boolean',1800,'de_DE','2016-11-27 00:00:00','2016-11-27 00:00:00'),
(556,1,'FCS_APP_NAME','Name der Foodcoop','','text',50,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(557,1,'FCS_APP_ADDRESS','Adresse der Foodcoop<br /><div class=\"small\">Wird im Footer von Homepage und E-Mails, Datenschutzerklärung, Nutzungsbedingungen usw. verwendet.</div>','','textarea',60,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(558,1,'FCS_APP_EMAIL','E-Mail-Adresse der Foodcoop<br /><div class=\"small\"></div>','','text',70,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(559,1,'FCS_PLATFORM_OWNER','Betreiber der Plattform<br /><div class=\"small\">Für Datenschutzerklärung und Nutzungsbedingungen, bitte auch Adresse angeben. Kann leer gelassen werden, wenn die Foodcoop selbst die Plattform betreibt.</div>','','textarea',90,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(564,1,'FCS_ORDER_COMMENT_ENABLED','Kommentarfeld bei Bestell-Abschluss anzeigen?<br /><div class=\"small\">Wird im Admin-Bereich unter \"Bestellungen\" angezeigt.</div>','1','boolean',130,'de_DE','2017-07-09 00:00:00','2017-07-09 00:00:00'),
(565,1,'FCS_USE_VARIABLE_MEMBER_FEE','Variablen Mitgliedsbeitrag verwenden?<br /><div class=\"small\">Den variablen Mitgliedsbeitrag bei den Hersteller-Rechnungen abziehen? Die Produkt-Preise müssen entsprechend höher eingegeben werden.</div>','0','readonly',400,'de_DE','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(566,1,'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE','Standardwert für variablen Mitgliedsbeitrag<br /><div class=\"small\">Der Prozentsatz kann in den Hersteller-Einstellungen auch individuell angepasst werden.</div>','0','readonly',500,'de_DE','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(567,1,'FCS_NETWORK_PLUGIN_ENABLED','Netzwerk-Modul aktiviert?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/netzwerk-modul\" target=\"_blank\">Infos zum Netzwerk-Modul</a></div>','0','readonly',500,'de_DE','2017-09-14 00:00:00','2017-09-14 00:00:00'),
(568,1,'FCS_TIMEBASED_CURRENCY_ENABLED','Stundenabrechnungs-Modul aktiv?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/stundenabrechnungs-modul\" target=\"_blank\">Infos zum Stundenabrechnung-Modul</a></div>','0','boolean',2000,'de_DE','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(569,1,'FCS_TIMEBASED_CURRENCY_NAME','Stundenabrechnung: Name der Einheit<br /><div class=\"small\">max. 10 Zeichen</div>','Stunden','text',2100,'de_DE','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(570,1,'FCS_TIMEBASED_CURRENCY_SHORTCODE','Stundenabrechnung: Abkürzung<br /><div class=\"small\">max. 3 Zeichen</div>','h','text',2200,'de_DE','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(571,1,'FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE','Stundenabrechnung: Umrechnungskurs<br /><div class=\"small\">in €, 2 Kommastellen</div>','10,00','number',2300,'de_DE','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(572,1,'FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_CUSTOMER','Stundenabrechnung: Überziehungsrahmen für Mitglieder<br /><div class=\"small\">Wie viele Stunden kann ein Mitglied maximal ins Minus gehen?</div>','10','number',2400,'de_DE','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(573,1,'FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_MANUFACTURER','Stundenabrechnung: Überziehungsrahmen für Hersteller<br /><div class=\"small\">Wie viele Stunden kann ein Hersteller maximal ins Plus gehen?</div>','100','number',2500,'de_DE','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(574,1,'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS','Produktpreis für nicht eingeloggte Mitglieder anzeigen?','0','boolean',210,'de_DE','2018-05-28 18:05:54','2018-05-28 18:05:54'),
(575,1,'FCS_CURRENCY_SYMBOL','Währungssymbol','€','readonly',520,'de_DE','2018-06-13 19:53:14','2018-06-13 19:53:14'),
(576,1,'FCS_DEFAULT_LOCALE','Sprache','de_DE','readonly',550,'de_DE','2018-06-26 10:18:55','2018-06-26 10:18:55'),
(577,1,'FCS_FOODCOOPS_MAP_ENABLED','Auf Home Karte mit anderen Foodcoops anzeigen?','1','boolean',1280,'de_DE','2019-02-11 22:25:36','2019-02-11 22:25:36'),
(578,1,'FCS_WEEKLY_PICKUP_DAY','Wöchentlicher Abholtag','5','readonly',600,'de_DE','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(579,1,'FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA','Bestelllisten-Versand: x Tage vor dem Abholtag','2','readonly',650,'de_DE','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(580,1,'FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM','Sollen Lagerprodukte mit der wöchentlichen Bestellung bestellt werden können?','1','boolean',750,'de_DE','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(581,1,'FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS','In der Sofort-Bestellung ausschließlich Lagerprodukte anzeigen?','0','boolean',760,'de_DE','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(582,1,'FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES','Lagerprodukte in Rechnungen miteinbeziehen?','1','readonly',600,'de_DE','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(583,1,'FCS_REGISTRATION_NOTIFICATION_EMAILS','Wer soll bei neuen Registrierungen informiert werden?<br /><div class=\"small\">Mehrere E-Mail-Adressen mit , (ohne Leerzeichen) trennen.</div>','','text',550,'de_DE','2019-03-05 20:01:59','2019-03-05 20:01:59'),
(584,1,'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED','Selbstbedienungs-Modus für Lagerprodukte aktiv?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/selbstbedienungs-modus\" target=\"_blank\">Zur Online-Doku</a></div>','0','boolean',3000,'de_DE','2019-04-17 20:01:59','2019-04-17 20:01:59'),
(585,1,'FCS_APP_ADDITIONAL_DATA','Zusätzliche Infos zur Foodcoop<br /><div class=\"small\">Z.B. ZVR-Zahl</div>','','textarea',80,'de_DE','2019-08-03 20:07:04','2019-08-03 20:07:04'),
(586,1,'FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED','Selbstbedienungs-Modus im Test-Modus ausführen?<br /><div class=\"small\">Keine Verlinkung im Haupt-Menü und bei Lagerprodukten.</div>','0','boolean',3100,'de_DE','2019-12-09 13:46:27','2019-12-09 13:46:27'),
(587,1,'FCS_CASHLESS_PAYMENT_ADD_TYPE','Art der Eintragung der Guthaben-Aufladungen<br /><div class=\"small\">Wie gelangen die Guthaben-Aufladungen vom Bankkonto in den FoodCoopShop?</div>','manual','dropdown',1450,'de_DE','2020-02-11 10:12:57','2020-02-11 10:12:57'),
(588,1,'FCS_SHOW_NEW_PRODUCTS_ON_HOME','Neue Produkte auch auf der Startseite anzeigen?','1','boolean',220,'de_DE','2020-04-15 09:41:54','2020-04-15 09:41:54'),
(589,1,'FCS_FEEDBACK_TO_PRODUCTS_ENABLED','Feedback-Funktion für Produkte aktiviert?<br /><div class=\"small\">Mitglieder können Feedback zu bestellten Produkte verfassen.</div>','1','boolean',3200,'de_DE','2020-06-19 09:02:46','2020-06-19 09:02:46'),
(590,1,'FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY','Mitglied kann Abholtag beim Bestellen selbst auswählen.','0','readonly',590,'de_DE','2020-07-06 10:34:35','2020-07-06 10:34:35'),
(591,1,'FCS_SEND_INVOICES_TO_CUSTOMERS','Einzelhandels-Modus aktiviert?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/einzelhandel\" target=\"_blank\">Infos zur Verwendung im Einzelhandel</a></div>','0','readonly',580,'de_DE','2020-10-29 10:06:34','2020-10-29 10:06:34'),
(592,1,'FCS_DEPOSIT_TAX_RATE','Umsatzsteuersatz für Pfand','20,00','readonly',581,'de_DE','2020-11-03 15:23:55','2020-11-03 15:23:55'),
(593,1,'FCS_INVOICE_HEADER_TEXT','Header-Text für Rechnungen an Mitglieder','','readonly',582,'de_DE','2020-11-03 15:23:55','2020-11-03 15:23:55'),
(594,1,'FCS_MEMBER_FEE_PRODUCTS','Welche Produkte werden als Mitgliedsbeitrag verwendet?<div class="small">Die ausgewählten Produkte sind Datengrundlage der Spalte Mitgliedsbeitrag in der Mitgliederverwaltung und werden nicht in der Umsatzstatistik angezeigt.</div>','','multiple_dropdown',3300,'de_DE','2020-12-20 19:26:10','2020-12-20 19:26:10');
/*!40000 ALTER TABLE `fcs_configuration` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cronjob_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cronjob_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cronjobs` DISABLE KEYS */;
INSERT INTO `fcs_cronjobs` VALUES
(1,'BackupDatabase','day',NULL,NULL,'04:00:00',1),
(2,'CheckCreditBalance','week',NULL,'Friday','22:30:00',1),
(3,'EmailOrderReminder','week',NULL,'Monday','18:00:00',1),
(4,'PickupReminder','week',NULL,'Monday','09:00:00',1),
(5,'SendInvoicesToManufacturers','month',11,NULL,'10:30:00',1),
(6,'SendOrderLists','day',NULL,NULL,'04:30:00',1),
(7,'SendInvoicesToCustomers','week',NULL,'Saturday','10:00:00',0);
/*!40000 ALTER TABLE `fcs_cronjobs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_customer` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_customer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_deposits` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_deposits` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_images` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_invoice_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_invoice_taxes` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_invoices` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_manufacturer` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_manufacturer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail_feedbacks` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail_feedbacks` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail_tax` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail_tax` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail_units` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_pages` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_pages` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_payments` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_pickup_days` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_pickup_days` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_product_attribute` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute_combination` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_product_attribute_combination` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_sliders` DISABLE KEYS */;
INSERT INTO `fcs_sliders` VALUES
(6,'demo-slider.jpg',NULL,0,0,1);
/*!40000 ALTER TABLE `fcs_sliders` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_stock_available` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_stock_available` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_sync_domains` DISABLE KEYS */;
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

/*!40000 ALTER TABLE `fcs_timebased_currency_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_timebased_currency_payments` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_units` ENABLE KEYS */;

/*!40000 ALTER TABLE `phinxlog` DISABLE KEYS */;
INSERT INTO `phinxlog` VALUES
(20200404145856,'RemoveV2Migrations','2020-04-04 15:01:04','2020-04-04 15:01:04',0),
(20200415073329,'ShowNewProductsOnHome','2020-04-15 07:41:54','2020-04-15 07:41:54',0),
(20200501192722,'EnableCashlessPaymentAddTypeConfiguration','2020-05-01 19:30:09','2020-05-01 19:30:09',0),
(20200618063024,'AddProductFeedback','2020-06-19 07:02:46','2020-06-19 07:02:46',0),
(20200703072605,'CustomerCanSelectPickupDay','2020-07-06 08:34:35','2020-07-06 08:34:35',0),
(20200831142250,'RemoveEmailLogTable','2020-08-31 15:10:21','2020-08-31 15:10:21',0),
(20200910091755,'AddMemberSettingUseCameraForMobileBarcodeScanning','2020-09-10 09:20:50','2020-09-10 09:20:51',0),
(20200925073919,'GermanIbanFix','2020-09-25 08:12:37','2020-09-25 08:12:37',0),
(20201017182431,'AdaptMinimalCreditBalance','2020-10-17 18:37:54','2020-10-17 18:37:54',0),
(20201029084931,'AddRetailMode','2020-10-29 09:06:34','2020-10-29 09:06:34',0),
(20201118084516,'AddRetailMode2','2020-11-18 08:47:32','2020-11-18 08:47:32',0),
(20201213120713,'AddRetailMode3','2020-12-13 12:13:56','2020-12-13 12:13:56',0),
(20201217101514,'SliderWithLink','2020-12-17 10:26:31','2020-12-17 10:26:31',0),
(20201220182015,'ImproveMemberFeeAdministration','2020-12-20 18:26:10','2020-12-20 18:26:10',0);
/*!40000 ALTER TABLE `phinxlog` ENABLE KEYS */;

/*!40000 ALTER TABLE `queue_phinxlog` DISABLE KEYS */;
INSERT INTO `queue_phinxlog` VALUES
(20150425180802,'Init','2020-09-17 07:23:19','2020-09-17 07:23:19',0),
(20150511062806,'Fixmissing','2020-09-17 07:23:19','2020-09-17 07:23:19',0),
(20150911132343,'ImprovementsForMysql','2020-09-17 07:23:19','2020-09-17 07:23:19',0),
(20161319000000,'IncreaseDataSize','2020-09-17 07:23:19','2020-09-17 07:23:19',0),
(20161319000001,'Priority','2020-09-17 07:23:19','2020-09-17 07:23:19',0),
(20161319000002,'Rename','2020-09-17 07:23:19','2020-09-17 07:23:19',0),
(20161319000003,'Processes','2020-09-17 07:23:19','2020-09-17 07:23:19',0),
(20171013131845,'AlterQueuedJobs','2020-09-17 07:23:19','2020-09-17 07:23:19',0),
(20171013133145,'Utf8mb4Fix','2020-09-17 07:23:19','2020-09-17 07:23:19',0),
(20171019083500,'ColumnLength','2020-09-17 07:23:19','2020-09-17 07:23:19',0),
(20171019083501,'MigrationQueueNull','2020-09-17 07:23:19','2020-09-17 07:23:19',0),
(20171019083502,'MigrationQueueStatus','2020-09-17 07:23:19','2020-09-17 07:23:20',0),
(20171019083503,'MigrationQueueProcesses','2020-09-17 07:23:20','2020-09-17 07:23:20',0),
(20171019083505,'MigrationQueueProcessesIndex','2020-09-17 07:23:20','2020-09-17 07:23:20',0),
(20171019083506,'MigrationQueueProcessesKey','2020-09-17 07:23:20','2020-09-17 07:23:20',0);
/*!40000 ALTER TABLE `queue_phinxlog` ENABLE KEYS */;

/*!40000 ALTER TABLE `queue_processes` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue_processes` ENABLE KEYS */;

/*!40000 ALTER TABLE `queued_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `queued_jobs` ENABLE KEYS */;

/*!40000 ALTER TABLE `queued_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `queued_tasks` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

