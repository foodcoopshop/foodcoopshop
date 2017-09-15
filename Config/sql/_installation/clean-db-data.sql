
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
TRUNCATE TABLE `fcs_address`;
TRUNCATE TABLE `fcs_attribute`;
TRUNCATE TABLE `fcs_attribute_lang`;
TRUNCATE TABLE `fcs_cake_action_logs`;
TRUNCATE TABLE `fcs_cake_cart_products`;
TRUNCATE TABLE `fcs_cake_carts`;
TRUNCATE TABLE `fcs_cake_deposits`;
TRUNCATE TABLE `fcs_cake_invoices`;
TRUNCATE TABLE `fcs_cake_payments`;
TRUNCATE TABLE `fcs_category`;
TRUNCATE TABLE `fcs_category_lang`;
TRUNCATE TABLE `fcs_category_product`;
TRUNCATE TABLE `fcs_cms`;
TRUNCATE TABLE `fcs_cms_lang`;
TRUNCATE TABLE `fcs_configuration`;
TRUNCATE TABLE `fcs_customer`;
TRUNCATE TABLE `fcs_email_logs`;
TRUNCATE TABLE `fcs_homeslider_slides`;
TRUNCATE TABLE `fcs_homeslider_slides_lang`;
TRUNCATE TABLE `fcs_image`;
TRUNCATE TABLE `fcs_image_lang`;
TRUNCATE TABLE `fcs_image_shop`;
TRUNCATE TABLE `fcs_manufacturer`;
TRUNCATE TABLE `fcs_manufacturer_lang`;
TRUNCATE TABLE `fcs_order_detail`;
TRUNCATE TABLE `fcs_order_detail_tax`;
TRUNCATE TABLE `fcs_orders`;
TRUNCATE TABLE `fcs_product`;
TRUNCATE TABLE `fcs_product_attribute`;
TRUNCATE TABLE `fcs_product_attribute_combination`;
TRUNCATE TABLE `fcs_product_attribute_shop`;
TRUNCATE TABLE `fcs_product_lang`;
TRUNCATE TABLE `fcs_product_shop`;
TRUNCATE TABLE `fcs_smart_blog_post`;
TRUNCATE TABLE `fcs_smart_blog_post_lang`;
TRUNCATE TABLE `fcs_smart_blog_post_shop`;
TRUNCATE TABLE `fcs_stock_available`;
TRUNCATE TABLE `fcs_tax`;

/*!40000 ALTER TABLE `fcs_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_address` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_attribute` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_attribute` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_attribute_lang` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_attribute_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cake_action_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cake_action_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cake_cart_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cake_cart_products` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cake_carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cake_carts` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cake_deposits` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cake_deposits` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cake_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cake_invoices` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cake_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cake_payments` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category` DISABLE KEYS */;
INSERT INTO `fcs_category` VALUES
(1,0,1,0,1,30,1,'2016-10-19 21:05:00','2016-10-19 21:05:00',0,0),
(2,1,1,1,2,29,1,'2016-10-19 21:05:00','2016-10-19 21:05:00',0,1),
(20,2,1,2,3,4,1,'2016-10-19 21:05:00','2016-10-19 21:05:00',1,0);
/*!40000 ALTER TABLE `fcs_category` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category_lang` DISABLE KEYS */;
INSERT INTO `fcs_category_lang` VALUES
(1,1,1,'Root','','root','','',''),
(2,1,1,'Produkte','','home','','',''),
(20,1,1,'Alle Produkte','','alle-produkte','','','');
/*!40000 ALTER TABLE `fcs_category_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_category_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cms` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cms` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cms_lang` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cms_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_configuration` DISABLE KEYS */;
INSERT INTO `fcs_configuration` VALUES
(11,NULL,NULL,1,'FCS_PRODUCT_AVAILABILITY_LOW','Geringe Verfügbarkeit<br /><div class=\"small\">Ab welcher verfügbaren Produkt-Anzahl soll beim Bestellen der Hinweis \"(x verfügbar\") angezeigt werden?</div>','10','number',60,'2017-07-26 13:24:47','2014-06-01 01:40:34'),
(31,NULL,NULL,1,'FCS_DAYS_SHOW_PRODUCT_AS_NEW','Wie viele Tage sollen Produkte \"als neu markiert\" bleiben?','7','number',70,'2017-07-26 13:24:47','2014-05-14 21:15:45'),
(164,NULL,NULL,1,'FCS_CUSTOMER_GROUP','Welcher Gruppe sollen neu registrierte Mitglieder zugewiesen werden?','3','dropdown',40,'2017-07-26 13:24:47','2017-07-26 13:24:47'),
(456,NULL,NULL,1,'FCS_FOOTER_CMS_TEXT','Zusätzlicher Text für den Footer',NULL,'textarea_big',80,'2014-06-11 17:50:55','2016-07-01 21:47:47'),
(508,NULL,NULL,1,'FCS_FACEBOOK_URL','Facebook-Url für die Einbindung im Footer','https://www.facebook.com/FoodCoopShop/','text',90,'2015-07-08 13:23:54','2015-07-08 13:23:54'),
(538,NULL,NULL,1,'FCS_REGISTRATION_EMAIL_TEXT','Zusätzlicher Text, der in der Bestätigungsmail nach einer Registrierung versendet wird. <br /> <a href=\"/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT\" target=\"_blank\"><img src=\"/js/vendor/famfamfam-silk/dist/png/information.png?1483041252\" alt=\"\"> E-Mail-Vorschau anzeigen</a>','','textarea_big',170,'2016-06-26 00:00:00','2016-06-26 00:00:00'),
(543,NULL,NULL,1,'FCS_RIGHT_INFO_BOX_HTML','Inhalt der Box in der rechten Spalte unterhalb des Warenkorbes. <br /><div class=\"small\">Um eine Zeile grün zu hinterlegen (Überschrift) bitte als \"Überschrift 3\" formatieren.<br />Die Variable {ABHOLTAG} zeigt automatisch das richtige Abholdatum an.</div>','<h3>Abholzeiten</h3>\r\n\r\n<p>Wenn du deine Produkte jetzt bestellst, kannst du sie am <strong>{ABHOLTAG}</strong>&nbsp;zwischen 17 und 19 Uhr abholen.</p>\r\n\r\n<p>Du kannst jede Woche bis sp&auml;testens Dienstag Mitternacht bestellen und sie am darauffolgenden Freitag abholen.</p>\r\n','textarea_big',150,'2017-07-26 13:24:47','2017-07-26 13:24:47'),
(544,NULL,NULL,1,'FCS_CART_ENABLED','Ist die Bestell-Funktion aktiviert?<br /><div class=\"small\">Falls die Foodcoop mal Urlaub macht, kann das Bestellen hier deaktiviert werden.</div>','1','boolean',10,'2017-07-26 13:24:47','2017-07-26 13:24:47'),
(545,NULL,NULL,1,'FCS_ACCOUNTING_EMAIL','E-Mail-Adresse des Finanzverantwortlichen<br /><div class=\"small\">Wer bekommt die Benachrichtigung über den erfolgten Rechnungsversand?</div>','','text',110,'2017-07-26 13:24:47','2017-07-26 13:24:47'),
(546,NULL,NULL,1,'FCS_AUTHENTICATION_INFO_TEXT','Info-Text beim Registrierungsformular<br /><div class=\"small\">Beim Registrierungsformlar wird unterhalb der E-Mail-Adresse dieser Text angezeigt.</div>','Um bei uns zu bestellen musst du Vereinsmitglied sein.','textarea',160,'2017-07-26 13:24:47','2017-07-26 13:24:47'),
(547,NULL,NULL,1,'FCS_SHOW_PRODUCTS_FOR_GUESTS','Produkte für nicht eingeloggte Mitglieder sichtbar?','0','boolean',20,'2017-07-26 13:24:47','2017-07-26 13:24:47'),
(548,NULL,NULL,1,'FCS_DEFAULT_NEW_MEMBER_ACTIVE','Neue Mitglieder automatisch aktivieren?','0','boolean',50,'2017-07-26 13:24:47','2017-07-26 13:24:47'),
(549,NULL,NULL,1,'FCS_MINIMAL_CREDIT_BALANCE','Höhe des Bestell-Limits, ab dem den Mitgliedern kein Bestellen mehr möglich ist.<br /><div class=\"small\">Z.B.: \"100\" für 100 € im Minus. 0 bedeutet \"kein Bestell-Limit\".</div>','50','number',125,'2017-07-26 13:24:47','2017-07-26 13:24:47'),
(550,NULL,NULL,1,'FCS_BANK_ACCOUNT_DATA','Bankverbindung für die Guthaben-Einzahlungen\".','Guthaben-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',130,'2017-07-26 13:24:47','2017-07-26 13:24:47'),
(551,NULL,NULL,1,'FCS_MEMBER_FEE_BANK_ACCOUNT_DATA','Bankverbindung für die Mitgliedsbeitrags-Einzahlungen\".','MB-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',140,'2017-07-26 13:24:47','2017-07-26 13:24:47'),
(552,NULL,NULL,1,'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS','Zusätzliche Liefer-Informationen für die Hersteller<br /><div class=\"small\">wird in den Bestell-Listen nach dem Lieferdatum angezeigt.</div>',', 15:00 bis 17:00 Uhr','text',120,'2017-07-26 13:24:47','2017-07-26 13:24:47'),
(553,NULL,NULL,1,'FCS_ORDER_CONFIRMATION_MAIL_BCC','E-Mail-Adresse, an die die Bestell-Bestätigungen als BCC geschickt werden.<br /><div class=\"small\">Kann leer gelassen werden.</div>','','text',300,'2016-10-06 00:00:00','2016-10-06 00:00:00'),
(554,NULL,NULL,1,'FCS_SHOW_FOODCOOPSHOP_BACKLINK','Auf Homepage Link auf www.foodcoopshop.com anzeigen?<br /><div class=\"small\">Der Link wird im Footer angezeigt.</div>','1','boolean',180,'2016-11-27 00:00:00','2016-11-27 00:00:00'),
(555,NULL,NULL,1,'FCS_PAYMENT_PRODUCT_MAXIMUM','Maximalbetrag für jede Guthaben-Aufladung in Euro','500','number',127,'2016-11-28 00:00:00','2016-11-28 00:00:00'),
(556,NULL,NULL,1,'FCS_APP_NAME','Name der Foodcoop','','text',5,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(557,NULL,NULL,1,'FCS_APP_ADDRESS','Adresse der Foodcoop<br /><div class=\"small\">Wird im Footer von Homepage und E-Mails, Datenschutzerklärung, Nutzungsbedingungen usw. verwendet.</div>','','textarea',6,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(558,NULL,NULL,1,'FCS_APP_EMAIL','E-Mail-Adresse der Foodcoop<br /><div class=\"small\"></div>','','text',7,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(559,NULL,NULL,1,'FCS_PLATFORM_OWNER','Betreiber der Plattform<br /><div class=\"small\">Für Datenschutzerklärung und Nutzungsbedingungen, bitte auch Adresse angeben. Kann leer gelassen werden, wenn die Foodcoop selbst die Plattform betreibt.</div>','','textarea',8,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(560,NULL,NULL,1,'FCS_SHOP_ORDER_DEFAULT_STATE','Bestellstatus für Sofort-Bestellungen','1','dropdown',75,'2017-01-12 00:00:00','2017-01-12 00:00:00'),
(561,NULL,NULL,1,'FCS_DB_VERSION','Version der Datenbank-Struktur','12','readonly',10,'2017-03-13 00:00:00','2017-07-26 00:00:00'),
(562,NULL,NULL,0,'FCS_DB_UPDATE','Version des letzten versuchten Datenbank-Updates','12','readonly',20,'2017-03-13 00:00:00','2017-07-26 00:00:00'),
(563,NULL,NULL,1,'FCS_EMAIL_LOG_ENABLED','Sollen alle ausgehenden E-Mails in der Datenbank gespeichert werden?<br /><div class=\"small\">Für Debugging gedacht.</div>','0','readonly',30,'2017-07-05 00:00:00','2017-07-05 00:00:00'),
(564,NULL,NULL,1,'FCS_ORDER_COMMENT_ENABLED','Kommentarfeld bei Bestell-Abschluss anzeigen?<br /><div class=\"small\">Wird im Admin-Bereich unter \"Bestellungen\" angezeigt.</div>','0','boolean',13,'2017-07-09 00:00:00','2017-07-09 00:00:00'),
(565,NULL,NULL,1,'FCS_USE_VARIABLE_MEMBER_FEE','Variablen Mitgliedsbeitrag verwenden?<br /><div class=\"small\">Den variablen Mitgliedsbeitrag bei den Hersteller-Rechnungen abziehen? Die Produkt-Preise müssen entsprechend höher eingegeben werden.</div>','0','readonly',40,'2017-08-02 00:00:00','2017-08-02 00:00:00'),
(566,NULL,NULL,1,'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE','Standardwert für variablen Mitgliedsbeitrag<br /><div class=\"small\">Der Prozentsatz kann in den Hersteller-Einstellungen auch individuell angepasst werden.</div>','0','readonly',50,'2017-08-02 00:00:00','2017-08-02 00:00:00');
/*!40000 ALTER TABLE `fcs_configuration` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_customer` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_customer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_email_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_email_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_homeslider_slides` DISABLE KEYS */;
INSERT INTO `fcs_homeslider_slides` VALUES
(6,0,1);
/*!40000 ALTER TABLE `fcs_homeslider_slides` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_homeslider_slides_lang` DISABLE KEYS */;
INSERT INTO `fcs_homeslider_slides_lang` VALUES
(6,1,'','','','','demo-slider.jpg');
/*!40000 ALTER TABLE `fcs_homeslider_slides_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_image` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_image` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_image_lang` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_image_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_image_shop` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_image_shop` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_manufacturer` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_manufacturer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_manufacturer_lang` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_manufacturer_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail_tax` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail_tax` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_orders` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_product_attribute` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute_combination` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_product_attribute_combination` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute_shop` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_product_attribute_shop` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_lang` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_product_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_shop` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_product_shop` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_smart_blog_post` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_smart_blog_post` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_smart_blog_post_lang` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_smart_blog_post_lang` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_smart_blog_post_shop` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_smart_blog_post_shop` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_stock_available` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_stock_available` ENABLE KEYS */;

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

