
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
TRUNCATE TABLE `fcs_cronjob_logs`;
TRUNCATE TABLE `fcs_cronjobs`;
TRUNCATE TABLE `fcs_customer`;
TRUNCATE TABLE `fcs_deposits`;
TRUNCATE TABLE `fcs_email_logs`;
TRUNCATE TABLE `fcs_images`;
TRUNCATE TABLE `fcs_invoices`;
TRUNCATE TABLE `fcs_manufacturer`;
TRUNCATE TABLE `fcs_order_detail`;
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

/*!40000 ALTER TABLE `fcs_action_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_action_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_address` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_attribute` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_attribute` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_blog_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_blog_posts` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cart_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cart_products` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_carts` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category` DISABLE KEYS */;
INSERT INTO `fcs_category` VALUES
(20,2,'All products','',3,4,1,'2016-10-19 21:05:00','2016-10-19 21:05:00');
/*!40000 ALTER TABLE `fcs_category` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_category_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_configuration` DISABLE KEYS */;
INSERT INTO `fcs_configuration` VALUES
(11,1,'FCS_PRODUCT_AVAILABILITY_LOW','Low availability<br /><div class=\"small\">From which amount on there should be an information text visible \"(x available\")?</div>','10','number',60,'en_US','2017-07-26 13:24:47','2014-06-01 01:40:34'),
(31,1,'FCS_DAYS_SHOW_PRODUCT_AS_NEW','How many days products should be \"marked as new\"?','7','number',70,'en_US','2017-07-26 13:24:47','2014-05-14 21:15:45'),
(164,1,'FCS_CUSTOMER_GROUP','To which user group new members should be assigned to?','3','dropdown',40,'en_US','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(456,1,'FCS_FOOTER_CMS_TEXT','Additional text for footer',NULL,'textarea_big',80,'en_US','2014-06-11 17:50:55','2016-07-01 21:47:47'),
(508,1,'FCS_FACEBOOK_URL','Facebook url for embedding in footer','https://www.facebook.com/FoodCoopShop/','text',90,'en_US','2015-07-08 13:23:54','2015-07-08 13:23:54'),
(538,1,'FCS_REGISTRATION_EMAIL_TEXT','Additional text that is sent in the registration e-mail after a successful registration. <br /> <a href=\"/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT\" target=\"_blank\"><i class=\"fas fa-info-circle\"></i> E-mail preview</a>','','textarea_big',170,'en_US','2016-06-26 00:00:00','2016-06-26 00:00:00'),
(543,1,'FCS_RIGHT_INFO_BOX_HTML','Content of the box in the right column below the shopping cart. <br /><div class=\"small\">To make the background of a row green, please format as \"Heading 3\".</div>','<h3>Delivery time</h3>\r\n\r\n<p>The pickup day can be seen in the product description, you can pick the product up on <strong>{DELIVERY_DAY}</strong>&nbsp;between 5 and 7 pm.</p>\r\n\r\n<p>You can order every week until Tuesday midnight and pick them up the following Friday.</p>\r\n','textarea_big',150,'en_US','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(544,1,'FCS_CART_ENABLED','Is the shopping cart activated?<br /><div class=\"small\">If the food-coop is on holiday, please deactivate the order function here.</div>','1','boolean',10,'en_US','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(545,1,'FCS_ACCOUNTING_EMAIL','E-mail address for the financial manager<br /><div class=\"small\">Who receives the notification that invoices have been sent?</div>','','text',110,'en_US','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(546,1,'FCS_AUTHENTICATION_INFO_TEXT','Info text in registration form<br /><div class=\"small\">This info text is shown in the registration form below the e-mail address.</div>','You need to be a member if you want to order here.','textarea',160,'en_US','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(547,1,'FCS_SHOW_PRODUCTS_FOR_GUESTS','Products visible for guests?','0','boolean',20,'en_US','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(548,1,'FCS_DEFAULT_NEW_MEMBER_ACTIVE','Automatically activate new members?','0','boolean',50,'en_US','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(549,1,'FCS_MINIMAL_CREDIT_BALANCE','Credit limit too low? When is ordering deactivated?<br /><div class=\"small\">E.g.: \"100\" for 100 € minus. 0 means \"no order limit\".</div>','50','number',125,'en_US','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(550,1,'FCS_BANK_ACCOUNT_DATA','Bank account for credit uploads.','Credit account Example Bank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',130,'en_US','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(551,1,'FCS_MEMBER_FEE_BANK_ACCOUNT_DATA','Bank account for member fee uploads.','Member fee account Example Bank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',140,'en_US','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(552,1,'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS','Additional deliverey details for manufacturers<br /><div class=\"small\">will be shown in the order lists after the delivery date.</div>',', 3pm to 5pm','text',120,'en_US','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(553,1,'FCS_BACKUP_EMAIL_ADDRESS_BCC','E-mail adress to which all automatically generated e-mail are sent to as BCC (Backup).<br /><div class=\"small\">Can be left empty.</div>','','text',190,'en_US','2016-10-06 00:00:00','2016-10-06 00:00:00'),
(554,1,'FCS_SHOW_FOODCOOPSHOP_BACKLINK','Show link to www.foodcoopshop.com?<br /><div class=\"small\">The link is shown in the footer.</div>','1','boolean',180,'en_US','2016-11-27 00:00:00','2016-11-27 00:00:00'),
(556,1,'FCS_APP_NAME','Name of the food-coop','','text',5,'en_US','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(557,1,'FCS_APP_ADDRESS','Adress of the food-coop<br /><div class=\"small\">Used in footer of homepage and e-mails, privacy policy and terms of use.</div>','','textarea',6,'en_US','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(558,1,'FCS_APP_EMAIL','E-mail adress of the food-coop<br /><div class=\"small\"></div>','','text',7,'en_US','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(559,1,'FCS_PLATFORM_OWNER','Operator of the platform<br /><div class=\"small\">For privacy policy and terms of use, please also add adrress. Can be left empty if the food-coop itself is operator.</div>','','textarea',8,'en_US','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(563,1,'FCS_EMAIL_LOG_ENABLED','Should all sent e-mails be stored in the database?<br /><div class=\"small\">For debugging and unit tests.</div>','0','readonly',30,'en_US','2017-07-05 00:00:00','2017-07-05 00:00:00'),
(564,1,'FCS_ORDER_COMMENT_ENABLED','Show comment field when placing an order?<br /><div class=\"small\">Shown in admin area under \"Orders\".</div>','0','boolean',13,'en_US','2017-07-09 00:00:00','2017-07-09 00:00:00'),
(565,1,'FCS_USE_VARIABLE_MEMBER_FEE','Use variable member fee?<br /><div class=\"small\">Reduce the variable member fee in the manufacturer\'s invoices? Therefore the prices need to be increased.</div>','0','readonly',40,'en_US','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(566,1,'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE','Default value for variable member fee<br /><div class=\"small\">The percentage can be changed in the manufacturer\'s settings.</div>','0','readonly',50,'en_US','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(567,1,'FCS_NETWORK_PLUGIN_ENABLED','Network module activated?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/en/network-module\" target=\"_blank\">Infos to the network module</a></div>','0','readonly',50,'en_US','2017-09-14 00:00:00','2017-09-14 00:00:00'),
(568,1,'FCS_TIMEBASED_CURRENCY_ENABLED','Paying-with-time module aktivated?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/en/paying-with-time-module\" target=\"_blank\">Infos th the paying-with-time module</a></div>','0','boolean',200,'en_US','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(569,1,'FCS_TIMEBASED_CURRENCY_NAME','Paying-with-time: Unit name<br /><div class=\"small\">max. 10 characters</div>','hours','text',210,'en_US','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(570,1,'FCS_TIMEBASED_CURRENCY_SHORTCODE','Paying-with-time: Abbreviation<br /><div class=\"small\">max. 3 characters</div>','h','text',220,'en_US','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(571,1,'FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE','Paying-with-time: Exchange rate<br /><div class=\"small\">in €, 2 decimals</div>','10.00','number',230,'en_US','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(572,1,'FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_CUSTOMER','Paying-with-time: Overdraft frame for members<br /><div class=\"small\">How many negative hours are allowed maximal for members?</div>','10','number',240,'en_US','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(573,1,'FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_MANUFACTURER','Paying-with-time: Overdraft frame for manufacturers<br /><div class=\"small\">How many positive hours are allowed maximal for manufacturers?</div>','100','number',250,'en_US','2018-03-16 15:23:34','2018-03-16 15:23:34'),
(574,1,'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS','Shop product price for guests??','0','boolean',21,'en_US','2018-05-28 18:05:54','2018-05-28 18:05:54'),
(575,1,'FCS_CURRENCY_SYMBOL','Currency symbol','$','readonly',52,'en_US','2018-06-13 19:53:14','2018-06-13 19:53:14'),
(576,1,'FCS_DEFAULT_LOCALE','Language','en_US','readonly',55,'en_US','2018-06-26 10:18:55','2018-06-26 10:18:55'),
(577,1,'FCS_FOODCOOPS_MAP_ENABLED','Show map with other foodcoops on home?','1','boolean',128,'en_US','2019-02-11 22:25:36','2019-02-11 22:25:36'),
(578,1,'FCS_WEEKLY_PICKUP_DAY','Weekly pickup day','5','readonly',60,'en_US','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(579,1,'FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA','Sending of order lists: x days before pickup day','2','readonly',65,'en_US','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(580,1,'FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM','Allow weekly orders for stock products?','1','boolean',75,'en_US','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(581,1,'FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS','Only show stock products in instant orders?','0','boolean',76,'en_US','2019-02-18 12:38:00','2019-02-18 12:38:00');
(582,1,'FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES','Include stock products in invoices?','1','readonly',60,'en_US','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(583,1,'FCS_REGISTRATION_NOTIFICATION_EMAILS','Who should be notified on new registrations?<br /><div class=\"small\">Please separate multiple e-mail addresses with , (no space).</div>','','text',55,'en_US','2019-03-05 20:01:59','2019-03-05 20:01:59');
/*!40000 ALTER TABLE `fcs_configuration` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cronjob_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cronjob_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cronjobs` DISABLE KEYS */;
INSERT INTO `fcs_cronjobs` VALUES
(1,'BackupDatabase','day',NULL,NULL,'04:00:00',1),
(2,'CheckCreditBalance','week',NULL,'Friday','22:30:00',1),
(3,'EmailOrderReminder','week',NULL,'Monday','18:00:00',1),
(4,'PickupReminder','week',NULL,'Monday','09:00:00',1),
(5,'SendInvoices','month',11,NULL,'07:30:00',1),
(6,'SendOrderLists','week',NULL,'Wednesday','04:30:00',1);
/*!40000 ALTER TABLE `fcs_cronjobs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_customer` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_customer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_deposits` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_deposits` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_email_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_email_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_images` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_invoices` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_manufacturer` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_manufacturer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail` ENABLE KEYS */;

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
(6,'demo-slider.jpg',0,1);
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
(20180213193116,'InitPhinxlog','2018-08-01 07:27:56','2018-08-01 07:27:56',0),
(20180213193117,'Migration018','2018-02-13 16:49:03','2018-02-13 16:49:04',0),
(20180213193123,'Migration019','2018-02-13 16:49:04','2018-02-13 16:49:04',0),
(20180213193133,'Migration020','2018-02-13 16:49:04','2018-02-13 16:49:04',0),
(20180217191214,'CakeUpdate','2018-03-05 08:31:25','2018-03-05 08:32:13',0),
(20180313083036,'TimebasedCurrency','2018-04-16 05:26:12','2018-04-16 05:26:20',0),
(20180430130912,'PricePerUnit','2018-05-18 07:00:58','2018-05-18 07:01:06',0),
(20180528152246,'ShowProductPriceForGuests','2018-06-01 08:02:58','2018-06-01 08:02:58',0),
(20180601101119,'LocaleConfig','2018-06-11 06:13:02','2018-06-11 06:13:03',0),
(20180604063719,'PricePerUnitFix','2018-06-04 10:56:06','2018-06-04 10:56:07',0),
(20180613080329,'CategoriesLevelDepthFix','2018-06-13 06:20:52','2018-06-13 06:20:53',0),
(20180613121712,'TreeLeftRightFix','2018-06-13 10:29:04','2018-06-13 10:29:06',0),
(20180613174031,'CurrencySymbolAsConfiguration','2018-06-25 06:55:19','2018-06-25 06:55:19',0),
(20180626080524,'AddLocaleToDatabaseConfig','2018-06-27 06:00:47','2018-06-27 06:00:47',0),
(20180702075300,'RenameShopOrderToInstantOrder','2018-07-02 07:23:18','2018-07-02 07:23:18',0),
(20180717100910,'ProductTablesOptimization','2018-08-01 07:28:57','2018-08-01 07:28:57',0),
(20180720130810,'RemoveOrdersTable','2018-08-01 07:28:57','2018-08-01 07:28:57',0),
(20180727070325,'CorrectBicLength','2018-08-01 07:28:57','2018-08-01 07:28:57',0),
(20180814121543,'ImprovedStockManagement','2018-08-14 14:57:53','2018-08-14 14:57:53',0),
(20180827074035,'AdditionalOrderPeriods','2018-08-27 08:28:29','2018-08-27 08:28:29',0),
(20181001120127,'UpdatePasswordHashingMethod','2018-08-27 08:28:29','2018-08-27 08:28:29',0),
(20181015080309,'ImproveNewPasswordRequest','2018-08-27 08:28:29','2018-08-27 08:28:29',0),
(20181018125456,'Cronjobs','2018-08-27 08:28:29','2018-08-27 08:28:29',0),
(20181027192224,'BootstrapUpdate','2018-10-27 08:28:29','2018-10-27 08:28:29',0),
(20181029212405,'CorrectNetPrice','2018-10-29 08:28:29','2018-10-29 08:28:29',0),
(20181226215706,'NoDeliveryDaysDefaultNull','2018-12-26 08:28:29','2018-12-26 08:28:29',0),
(20190114095502,'Fontawesome5','2019-01-14 00:00:00','2019-01-14 00:00:00',0),
(20190211210824,'AddFoodCoopShopInstancesMap','2019-02-11 21:25:36','2019-02-11 21:25:36',0),
(20190218101915,'IndividualSendOrderListDay','2019-02-18 11:38:00','2019-02-18 11:38:00',0),
(20190219104144,'StockProductOrderManagement','2019-02-19 21:25:36','2019-02-11 21:25:36',0),
(20190305183508,'ConfigurationOptimizations','2019-03-05 19:01:59','2019-03-05 19:01:59',0);
/*!40000 ALTER TABLE `phinxlog` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

