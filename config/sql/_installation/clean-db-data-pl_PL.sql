
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
(20,2,'Wszystkie produkty','',3,4,1,'2016-10-19 21:05:00','2016-10-19 21:05:00');
/*!40000 ALTER TABLE `fcs_category` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_category_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_configuration` DISABLE KEYS */;
INSERT INTO `fcs_configuration` VALUES
(11,1,'FCS_PRODUCT_AVAILABILITY_LOW','Niska dostępność <br /> <div class = \"small\"> Od jakiej ilości powinien być widoczny tekst informacyjny \"(x dostępne\")? </div>','10','number',600,'pl_PL','2017-07-26 13:24:47','2014-06-01 01:40:34'),
(31,1,'FCS_DAYS_SHOW_PRODUCT_AS_NEW','Ile dni produkty powinny być oznaczone jako \"nowe\"?','7','number',700,'pl_PL','2017-07-26 13:24:47','2014-05-14 21:15:45'),
(164,1,'FCS_CUSTOMER_GROUP','Do której grupy użytkowników mają zostać przypisani nowi członkowie?','3','dropdown',400,'pl_PL','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(456,1,'FCS_FOOTER_CMS_TEXT','Dodatkowy tekst dla stopki',NULL,'textarea_big',800,'pl_PL','2014-06-11 17:50:55','2016-07-01 21:47:47'),
(508,1,'FCS_FACEBOOK_URL','Adres URL Facebooka do osadzenia w stopce','https://www.facebook.com/FoodCoopShop/','text',900,'pl_PL','2015-07-08 13:23:54','2015-07-08 13:23:54'),
(538,1,'FCS_REGISTRATION_EMAIL_TEXT','Dodatkowy tekst wysyłany w e-mailu rejestracyjnym po udanej rejestracji. <br /> <a href=\"/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT\" target=\"_blank\"> <i class = \"fas fa-info-circle\"> </i> podgląd wiadomości e-mail </a>','','textarea_big',1700,'pl_PL','2016-06-26 00:00:00','2016-06-26 00:00:00'),
(543,1,'FCS_RIGHT_INFO_BOX_HTML','Treść pola w prawej kolumnie poniżej koszyka. <br /> <div class = \"small\"> Aby tło w wierszu było zielone, należy sformatować jako \"Nagłówek 3\". </div>','<h3>Odbiory</h3>\r\n\r\n<p>Dzień odbioru jest widoczny w opisie produktu, możesz odebrać produkty w <strong>{DELIVERY_DAY}</strong>&nbsp;pomiędzy 19, a 21.</p>\r\n\r\n<p>Zam&oacute;wienia składamy co tydzień, maksymalnie do środy (zależy od produktu).</p>','textarea_big',1500,'pl_PL','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(544,1,'FCS_NO_DELIVERY_DAYS_GLOBAL','Delivery break for all manufacturers?<br /><div class=\"small\">Here you can define delivery-free days for the whole food-coop.</div>','','multiple_dropdown',100,'pl_PL','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(545,1,'FCS_ACCOUNTING_EMAIL','Adres e-mail osoby odpowiedzialnej za finanse <br /> <div class = \"small\"> Kto otrzymuje powiadomienie o wysłaniu faktur? </div>','','text',1100,'pl_PL','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(546,1,'FCS_REGISTRATION_INFO_TEXT','Tekst informacyjny w formularzu rejestracyjnym <br /> <div class = \"small\"> Ten tekst informacyjny jest wyświetlany w formularzu rejestracyjnym poniżej adresu e-mail. </div>','Musisz być członkiem jeśli chcesz złożyć zamówienie.','textarea_big',1600,'pl_PL','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(547,1,'FCS_SHOW_PRODUCTS_FOR_GUESTS','Produkty widoczne dla gości?','0','boolean',200,'pl_PL','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(548,1,'FCS_DEFAULT_NEW_MEMBER_ACTIVE','Automatycznie aktywuj nowych członków?','0','boolean',500,'pl_PL','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(549,1,'FCS_MINIMAL_CREDIT_BALANCE','Up to which credit amount orders should be possible?','0','number',1250,'pl_PL','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(550,1,'FCS_BANK_ACCOUNT_DATA','Konto bankowe do przelewów kredytowych.','Przykład konta bankowego do przedpłat / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',1300,'pl_PL','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(551,1,'FCS_MEMBER_FEE_BANK_ACCOUNT_DATA','Konto bankowe do przesyłania opłat członkowskich.','Przykład konta bankowego do opłat członkowskich / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',1400,'pl_PL','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(552,1,'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS','Dodatkowe szczegóły dostawy dla producentów <br /> <div class = \"small\"> zostaną wyświetlone na listach zamówień po dacie dostawy. </div>',', 15:00 - 19:00','text',1200,'pl_PL','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(553,1,'FCS_BACKUP_EMAIL_ADDRESS_BCC','Adres e-mail, na który wysyłane są wszystkie automatycznie wygenerowane wiadomości e-mail jako UDW (kopia zapasowa). <br /> <div class = \"small\"> Można pozostawić puste. </div>','','text',1900,'pl_PL','2016-10-06 00:00:00','2016-10-06 00:00:00'),
(554,1,'FCS_SHOW_FOODCOOPSHOP_BACKLINK','Pokaż link do www.foodcoopshop.com? <br /> <div class = \"small\"> Link jest wyświetlany w stopce. </div>','1','boolean',1800,'pl_PL','2016-11-27 00:00:00','2016-11-27 00:00:00'),
(556,1,'FCS_APP_NAME','Nazwa kooperatywy','','text',50,'pl_PL','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(557,1,'FCS_APP_ADDRESS','Adres kooperatywy <br /> <div class = \"small\"> Używany w stopce strony głównej, mailach, polityce prywatności i warunkach użytkowania.</div>','','textarea',60,'pl_PL','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(558,1,'FCS_APP_EMAIL','Adres e-mail kooperatywy <br /> <div class = \"small\"> </div>','','text',70,'pl_PL','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(559,1,'FCS_PLATFORM_OWNER','Operator platformy <br /> <div class = \"small\"> Aby zapoznać się z polityką prywatności i warunkami użytkowania, dodaj również adres. Można pozostawić puste, jeśli sama kooperatywa jest operatorem. </div>','','textarea',90,'pl_PL','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(564,1,'FCS_ORDER_COMMENT_ENABLED','Pokaż pole komentarza podczas składania zamówienia? <br /> <div class = \"small\"> Widoczne w obszarze administracyjnym w obszarze \"Zamówienia\". </div>','1','boolean',130,'pl_PL','2017-07-09 00:00:00','2017-07-09 00:00:00'),
(565,1,'FCS_USE_VARIABLE_MEMBER_FEE','Użyj zmiennej opłaty członkowskiej? <br /> <div class = \"small\"> Zmniejsz zmienną opłatę członkowską na fakturach producenta? Tym samym, ceny muszą być zwiększone. </div>','0','readonly',400,'pl_PL','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(566,1,'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE','Wartość domyślna dla zmiennej opłaty członkowskiej <br /> <div class = \"small\"> Procent można zmienić w ustawieniach producenta. </div>','0','readonly',500,'pl_PL','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(567,1,'FCS_NETWORK_PLUGIN_ENABLED','Aktywowany moduł sieciowy? <br /> <div class = \"small\"> <a href=\"https://foodcoopshop.github.io/en/network-module\" target=\"_blank\"> Informacje o module sieciowym </ a> </div>','0','readonly',500,'pl_PL','2017-09-14 00:00:00','2017-09-14 00:00:00'),
(568,1,'FCS_TIMEBASED_CURRENCY_ENABLED','Aktywny moduł płatności czasem? <br /> <div class = \"small\"> <a href = \"https://foodcoopshop.github.io/en/paying-with-time-module\" target = \"_ blank\" > Informacje o module płatności czasem </a> </div>','0','boolean',2000,'pl_PL','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(569,1,'FCS_TIMEBASED_CURRENCY_NAME','Płatność czasem: nazwa jednostki <br /> <div class = \"small\"> max. 10 znaków </div>','godz.','text',2100,'pl_PL','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(570,1,'FCS_TIMEBASED_CURRENCY_SHORTCODE','Płatność czasem: skrót <br /> <div class = \"small\"> max. 3 znaki </div>','h','text',2200,'pl_PL','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(571,1,'FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE','Płatność czasem: Kurs wymiany <br /> <div class = \"small\"> w zł, 2 miejsca dziesiętne </div>','10.00','number',2300,'pl_PL','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(572,1,'FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_CUSTOMER','Płatność czasem: Ramka przekroczenia limitu dla członków <br /> <div class = \"small\"> Ile maksymalnie ujemnych godzin mogą mieć członkowie? </div>','10','number',2400,'pl_PL','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(573,1,'FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_MANUFACTURER','Płatność czasem: Ramka przekroczenia limitu dla producentów <br /> <div class = \"small\"> Ile maksymalnie dodatnich godzin mogą mieć producenci? </div>','100','number',2500,'pl_PL','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(574,1,'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS','Ceny produktów widoczne dla gości?','0','boolean',210,'pl_PL','2018-05-28 18:05:54','2018-05-28 18:05:54'),
(575,1,'FCS_CURRENCY_SYMBOL','Symbol waluty','zł','readonly',520,'pl_PL','2018-06-13 19:53:14','2018-06-13 19:53:14'),
(576,1,'FCS_DEFAULT_LOCALE','Język','pl_PL','readonly',550,'pl_PL','2018-06-26 10:18:55','2018-06-26 10:18:55'),
(577,1,'FCS_FOODCOOPS_MAP_ENABLED','Pokaż mapę z innymi kooperatywami na stronie głównej?','1','boolean',1280,'pl_PL','2019-02-11 22:25:36','2019-02-11 22:25:36'),
(578,1,'FCS_WEEKLY_PICKUP_DAY','Dzień odbioru','5','readonly',600,'pl_PL','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(579,1,'FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA','Wysyłanie list zamówień: x dni przed dniem odbioru','2','readonly',650,'pl_PL','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(580,1,'FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM','Pozwolić na cotygodniowe zamówienia na produkty magazynowe?','1','boolean',750,'pl_PL','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(581,1,'FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS','Pokazywać tylko produkty magazynowane przy zamówieniach błyskawicznych?','0','boolean',760,'pl_PL','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(582,1,'FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES','Zawrzyj w fakturach produkty magazynowane?','1','readonly',600,'pl_PL','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(583,1,'FCS_REGISTRATION_NOTIFICATION_EMAILS','Kto powinien być powiadamiany o nowych rejestracjach? <br /> <div class = \"small\"> Oddziel wiele adresów e-mail przecinkami (bez spacji). </div>','','text',550,'pl_PL','2019-03-05 20:01:59','2019-03-05 20:01:59'),
(584,1,'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED','Użyć trybu samoobsługowego dla produktów magazynowych?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/pl/self-service-mode\" target=\"_blank\">Dokumentacja</a></div>','0','boolean',3000,'pl_PL','2019-04-17 20:01:59','2019-04-17 20:01:59'),
(585,1,'FCS_APP_ADDITIONAL_DATA','Additional food-coop infos','','textarea',80,'pl_PL','2019-08-03 20:07:12','2019-08-03 20:07:12'),
(586,1,'FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED','Run self-service mode in test mode?<br /><div class=\"small\">Does not add links to main menu and to stock products.</div>','0','boolean',3100,'pl_PL','2019-12-09 13:46:37','2019-12-09 13:46:37'),
(587,1,'FCS_CASHLESS_PAYMENT_ADD_TYPE','Type of adding the payments<br /><div class=\"small\">How do the payment addings get into FoodCoopShop?</div>','manual','dropdown',1450,'pl_PL','2020-02-11 10:13:06','2020-02-11 10:13:06'),
(588,1,'FCS_SHOW_NEW_PRODUCTS_ON_HOME','Show new products on home?','1','boolean',220,'pl_PL','2020-04-15 09:42:02','2020-04-15 09:42:02'),
(589,1,'FCS_FEEDBACK_TO_PRODUCTS_ENABLED','Are members allowed to write feedback to products?','1','boolean',3200,'pl_PL','2020-06-19 09:02:55','2020-06-19 09:02:55'),
(590,1,'FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY','Pickup day can be selected by member on order confirmation.','0','readonly',590,'pl_PL','2020-07-06 10:34:44','2020-07-06 10:34:44'),
(591,1,'FCS_SEND_INVOICES_TO_CUSTOMERS','Retail mode activated?.','0','readonly',580,'pl_PL','2020-10-29 10:06:45','2020-10-29 10:06:45'),
(592,1,'FCS_DEPOSIT_TAX_RATE','VAT for deposit','20.00','readonly',581,'pl_PL','2020-11-03 15:24:06','2020-11-03 15:24:06'),
(593,1,'FCS_INVOICE_HEADER_TEXT','Header text for invoices to members','','readonly',582,'pl_PL','2020-11-03 15:24:01','2020-11-03 15:24:01'),
(594,1,'FCS_MEMBER_FEE_PRODUCTS','Which products are used as member fee product?<div class="small">The selected products are the basis for the column Member Fee in the members adminstration and are not shown in the turnover statistics.</div>','','multiple_dropdown',3300,'pl_PL','2020-12-20 19:26:21','2020-12-20 19:26:21');
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
(1,5.000,1,0),
(2,8.000,1,0),
(3,23.000,1,0);
/*!40000 ALTER TABLE `fcs_tax` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_timebased_currency_order_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_timebased_currency_order_detail` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_timebased_currency_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_timebased_currency_payments` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_units` ENABLE KEYS */;

/*!40000 ALTER TABLE `phinxlog` DISABLE KEYS */;
INSERT INTO `phinxlog` VALUES
(20200404145856,'RemoveV2Migrations','2020-04-04 15:01:12','2020-04-04 15:01:12',0),
(20200415073329,'ShowNewProductsOnHome','2020-04-15 07:42:02','2020-04-15 07:42:02',0),
(20200501192722,'EnableCashlessPaymentAddTypeConfiguration','2020-05-01 19:30:17','2020-05-01 19:30:17',0),
(20200618063024,'AddProductFeedback','2020-06-19 07:02:54','2020-06-19 07:02:55',0),
(20200703072605,'CustomerCanSelectPickupDay','2020-07-06 08:34:44','2020-07-06 08:34:44',0),
(20200831142250,'RemoveEmailLogTable','2020-08-31 15:10:29','2020-08-31 15:10:29',0),
(20200910091755,'AddMemberSettingUseCameraForMobileBarcodeScanning','2020-09-10 09:21:00','2020-09-10 09:21:00',0),
(20200925073919,'GermanIbanFix','2020-09-25 08:12:47','2020-09-25 08:12:47',0),
(20201017182431,'AdaptMinimalCreditBalance','2020-10-17 18:38:05','2020-10-17 18:38:05',0),
(20201029084931,'AddRetailMode','2020-10-29 09:06:45','2020-10-29 09:06:45',0),
(20201118084516,'AddRetailMode2','2020-11-18 08:47:43','2020-11-18 08:47:43',0),
(20201213120713,'AddRetailMode3','2020-12-13 12:14:06','2020-12-13 12:14:06',0),
(20201217101514,'SliderWithLink','2020-12-17 10:26:42','2020-12-17 10:26:42',0),
(20201220182015,'ImproveMemberFeeAdministration','2020-12-20 18:26:21','2020-12-20 18:26:21',0);
/*!40000 ALTER TABLE `phinxlog` ENABLE KEYS */;

/*!40000 ALTER TABLE `queue_phinxlog` DISABLE KEYS */;
INSERT INTO `queue_phinxlog` VALUES
(20150425180802,'Init','2020-09-17 07:23:31','2020-09-17 07:23:31',0),
(20150511062806,'Fixmissing','2020-09-17 07:23:31','2020-09-17 07:23:31',0),
(20150911132343,'ImprovementsForMysql','2020-09-17 07:23:31','2020-09-17 07:23:31',0),
(20161319000000,'IncreaseDataSize','2020-09-17 07:23:31','2020-09-17 07:23:31',0),
(20161319000001,'Priority','2020-09-17 07:23:31','2020-09-17 07:23:31',0),
(20161319000002,'Rename','2020-09-17 07:23:31','2020-09-17 07:23:31',0),
(20161319000003,'Processes','2020-09-17 07:23:31','2020-09-17 07:23:31',0),
(20171013131845,'AlterQueuedJobs','2020-09-17 07:23:31','2020-09-17 07:23:31',0),
(20171013133145,'Utf8mb4Fix','2020-09-17 07:23:31','2020-09-17 07:23:31',0),
(20171019083500,'ColumnLength','2020-09-17 07:23:31','2020-09-17 07:23:32',0),
(20171019083501,'MigrationQueueNull','2020-09-17 07:23:32','2020-09-17 07:23:32',0),
(20171019083502,'MigrationQueueStatus','2020-09-17 07:23:32','2020-09-17 07:23:32',0),
(20171019083503,'MigrationQueueProcesses','2020-09-17 07:23:32','2020-09-17 07:23:32',0),
(20171019083505,'MigrationQueueProcessesIndex','2020-09-17 07:23:32','2020-09-17 07:23:32',0),
(20171019083506,'MigrationQueueProcessesKey','2020-09-17 07:23:32','2020-09-17 07:23:32',0);
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

