<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class InitTestDataSeed extends AbstractSeed
{
    public function run(): void
    {
        $query = "
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Truncate tables before insertion
TRUNCATE TABLE `fcs_category`;
TRUNCATE TABLE `fcs_category_product`;
TRUNCATE TABLE `fcs_cronjob_logs`;
TRUNCATE TABLE `fcs_cronjobs`;
TRUNCATE TABLE `fcs_customer`;
TRUNCATE TABLE `fcs_deposits`;
TRUNCATE TABLE `fcs_feedbacks`;
TRUNCATE TABLE `fcs_images`;
TRUNCATE TABLE `fcs_invoice_taxes`;
TRUNCATE TABLE `fcs_invoices`;
TRUNCATE TABLE `fcs_manufacturer`;
TRUNCATE TABLE `fcs_order_detail`;
TRUNCATE TABLE `fcs_order_detail_feedbacks`;
TRUNCATE TABLE `fcs_order_detail_purchase_prices`;
TRUNCATE TABLE `fcs_order_detail_units`;
TRUNCATE TABLE `fcs_pages`;
TRUNCATE TABLE `fcs_payments`;
TRUNCATE TABLE `fcs_pickup_days`;
TRUNCATE TABLE `fcs_product`;
TRUNCATE TABLE `fcs_product_attribute`;
TRUNCATE TABLE `fcs_product_attribute_combination`;
TRUNCATE TABLE `fcs_purchase_prices`;
TRUNCATE TABLE `fcs_sliders`;
TRUNCATE TABLE `fcs_stock_available`;
TRUNCATE TABLE `fcs_storage_locations`;
TRUNCATE TABLE `fcs_sync_domains`;
TRUNCATE TABLE `fcs_sync_products`;
TRUNCATE TABLE `fcs_units`;
TRUNCATE TABLE `phinxlog`;
TRUNCATE TABLE `queue_phinxlog`;
TRUNCATE TABLE `queue_processes`;
TRUNCATE TABLE `queued_jobs`;

/*!40000 ALTER TABLE `fcs_category` DISABLE KEYS */;
INSERT INTO `fcs_category` VALUES
(16,0,'Fleischprodukte','',11,12,1,'2014-05-14 21:40:51','2014-05-14 21:48:48'),
(20,0,'Alle Produkte','',3,4,1,'2014-05-14 21:53:52','2014-05-17 13:14:22');
/*!40000 ALTER TABLE `fcs_category` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_category_product` DISABLE KEYS */;
INSERT INTO `fcs_category_product` VALUES
(20,60),
(16,102),
(20,102),
(16,103),
(20,103),
(20,163),
(20,339),
(16,340),
(20,340),
(20,344),
(20,346),
(16,347),
(20,347),
(16,348),
(20,348),
(20,349),
(20,350),
(20,351),
(20,352);
/*!40000 ALTER TABLE `fcs_category_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cronjob_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_cronjob_logs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_cronjobs` DISABLE KEYS */;
INSERT INTO `fcs_cronjobs` VALUES
(1,'TestCronjob','day',NULL,NULL,'22:30:00',1),
(2,'TestCronjob','week',NULL,'Monday','09:00:00',1),
(3,'TestCronjob','month',11,NULL,'07:30:00',1);
/*!40000 ALTER TABLE `fcs_cronjobs` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_customer` DISABLE KEYS */;
INSERT INTO `fcs_customer` VALUES
(87,3,0,'Demo','Mitglied','fcs-demo-mitglied@mailinator.com','$2y$10\$uu/znwy2GwCx0NlLOIqaquY862AdcV6BgTGtNEUdKj4o1US.idVlm',NULL,NULL,1,'2018-08-03',NULL,1,'2014-12-02 12:19:31','2015-12-06 23:37:44',0,0,'SP',1,1,1,1,0),
(88,4,0,'Demo','Admin','fcs-demo-admin@mailinator.com','$2y$10\\\$uu/znwy2GwCx0NlLOIqaquY862AdcV6BgTGtNEUdKj4o1US.idVlm',NULL,NULL,1,'2018-08-03',NULL,1,'2014-12-02 12:28:43','2016-09-29 16:25:09',0,0,'SP',1,1,1,1,0),
(89,4,0,'Demo','Gemüse-Hersteller','fcs-demo-gemuese-hersteller@mailinator.com','$2y$10\$uu/znwy2GwCx0NlLOIqaquY862AdcV6BgTGtNEUdKj4o1US.idVlm',NULL,NULL,0,'2018-08-03',NULL,1,'2014-12-02 12:37:26','2015-03-11 18:12:10',0,0,'SP',1,1,1,1,0),
(90,4,0,'Demo','Milch-Hersteller','fcs-demo-milch-hersteller@mailinator.com','$2y$10\$uu/znwy2GwCx0NlLOIqaquY862AdcV6BgTGtNEUdKj4o1US.idVlm',NULL,NULL,0,'2018-08-03',NULL,1,'2014-12-02 12:37:49','2015-03-11 18:11:54',0,0,'SP',1,1,1,1,0),
(91,4,0,'Demo','Fleisch-Hersteller','fcs-demo-fleisch-hersteller@mailinator.com','$2y$10\$uu/znwy2GwCx0NlLOIqaquY862AdcV6BgTGtNEUdKj4o1US.idVlm',NULL,NULL,0,'2018-08-03',NULL,1,'2014-12-02 12:38:12','2015-03-11 18:11:47',0,0,'SP',1,1,1,1,0),
(92,5,0,'Demo','Superadmin','fcs-demo-superadmin@mailinator.com','$2y$10\$uu/znwy2GwCx0NlLOIqaquY862AdcV6BgTGtNEUdKj4o1US.idVlm',NULL,NULL,1,'2018-08-03',NULL,1,'2016-09-29 16:26:12','2016-09-29 16:26:12',0,0,'SP',1,1,1,1,0),
(93,2,0,'Demo','SB-Kunde','fcs-demo-sb-kunde@mailinator.com','$2y$10\$uu/znwy2GwCx0NlLOIqaquY862AdcV6BgTGtNEUdKj4o1US.idVlm',NULL,NULL,0,'2018-08-03',NULL,0,'2016-09-29 16:26:12','2016-09-29 16:26:12',0,0,'SP',1,1,1,1,0);
/*!40000 ALTER TABLE `fcs_customer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_deposits` DISABLE KEYS */;
INSERT INTO `fcs_deposits` VALUES
(1,346,0,0.5),
(2,0,9,0.5),
(3,0,10,0.5);
/*!40000 ALTER TABLE `fcs_deposits` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_feedbacks` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_feedbacks` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_images` DISABLE KEYS */;
INSERT INTO `fcs_images` VALUES
(154,60),
(156,340),
(157,338);
/*!40000 ALTER TABLE `fcs_images` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_invoice_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_invoice_taxes` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_invoices` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_manufacturer` DISABLE KEYS */;
INSERT INTO `fcs_manufacturer` VALUES
(4,'Demo Fleisch-Hersteller','<p>tests</p>\r\n','','2014-05-14 13:23:02','2015-05-15 13:31:41',1,0,'','','','','','','','','','',NULL,0,1,1,2,NULL,'testfcs1@mailinator.com,testfcs2@mailinator.com',NULL,NULL,NULL,1,NULL,0,0,0,'',1,0,0),
(5,'Demo Gemüse-Hersteller','<p>Gem&uuml;se-Hersteller Beschreibung&nbsp;lang</p>','<div class=\"entry-content\">\r\n<p>Gem&uuml;se-Hersteller Beschreibung kurz</p>\r\n</div>','2014-05-14 13:36:44','2016-09-27 09:34:51',1,0,'','','','','','','','','','',88,0,1,1,1,NULL,'',NULL,NULL,NULL,NULL,'1',1,1,1,'',1,0,0),
(15,'Demo Milch-Hersteller','<p>Ja, ich bin der Milchhersteller!</p>','','2014-06-04 21:45:12','2016-03-07 09:02:25',1,0,'','','','','','','','','','',NULL,0,1,1,4,NULL,'test@test.at',NULL,NULL,NULL,NULL,NULL,0,0,0,'',1,1,0),
(16,'Hersteller ohne Customer-Eintrag','','','2014-06-04 21:45:12','2016-03-07 09:02:25',1,0,'','','','','','','','','','',NULL,10,1,1,1,NULL,'',NULL,NULL,NULL,NULL,NULL,0,0,0,'',1,0,0);
/*!40000 ALTER TABLE `fcs_manufacturer` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail` DISABLE KEYS */;
INSERT INTO `fcs_order_detail` VALUES
(1,346,0,'Artischocke : Stück',1,1.820000,1.650000,0.170000,0.170000,10.000,0.50,92,NULL,1,3,'2018-02-02','SP','2018-02-01 09:17:14','2021-05-04 11:10:14'),
(2,340,0,'Beuschl',1,4.540000,4.540000,0.000000,0.000000,0.000,0.00,92,NULL,2,3,'2018-02-02','SP','2018-02-01 09:17:14','2021-05-04 11:10:14'),
(3,60,10,'Milch : 0,5l',1,0.620000,0.550000,0.070000,0.070000,13.000,0.50,92,NULL,3,3,'2018-02-02','SP','2018-02-01 09:17:14','2021-05-04 11:10:14');
/*!40000 ALTER TABLE `fcs_order_detail` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail_feedbacks` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail_feedbacks` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail_purchase_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail_purchase_prices` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_order_detail_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_order_detail_units` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_pages` DISABLE KEYS */;
INSERT INTO `fcs_pages` VALUES
(3,'Page','',1,'header',1,'',88,0,'2016-08-29 13:36:43','2016-08-29 13:36:43',0,0,0,0);
/*!40000 ALTER TABLE `fcs_pages` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_payments` DISABLE KEYS */;
INSERT INTO `fcs_payments` VALUES
(1,92,0,'product',100.00,'','2018-07-03 20:00:20','2018-07-03 20:00:20',NULL,NULL,0,1,0,'',0,92),
(2,87,0,'product',100000.00,'','2020-12-09 20:00:20','2020-12-09 20:00:20',NULL,NULL,0,1,0,'',0,87);
/*!40000 ALTER TABLE `fcs_payments` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_pickup_days` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcs_pickup_days` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product` DISABLE KEYS */;
INSERT INTO `fcs_product` VALUES
(60,15,3,1,0.909091,'Milch','','1 Liter','',0,0,1,'week',1,NULL,NULL,3,NULL,'2014-06-11 21:20:24','2014-12-14 19:47:33'),
(102,4,2,1,0.000000,'Frankfurter','','<p>2 Paar</p>','',0,0,1,'week',1,NULL,NULL,3,NULL,'2016-04-27 21:13:37','2014-09-19 14:32:51'),
(103,4,2,1,3.181819,'Bratwürstel','','2 Paar','',0,0,1,'week',1,NULL,NULL,3,NULL,'2016-05-05 08:28:49','2014-08-16 14:05:58'),
(163,5,0,1,1.363637,'Mangold','','0,25kg','',0,0,1,'week',1,NULL,NULL,3,NULL,'2014-07-12 20:41:43','2017-07-26 13:24:10'),
(339,5,0,1,0.000000,'Kartoffel','','','',0,0,1,'week',1,NULL,NULL,3,NULL,'2015-09-07 12:05:38','2015-02-26 13:54:07'),
(340,4,0,1,4.545455,'Beuschl','','','',0,0,1,'week',1,NULL,NULL,3,NULL,'2016-05-05 08:28:45','2015-06-23 14:52:53'),
(344,5,0,1,0.636364,'Knoblauch','','','100 g',0,0,1,'week',1,NULL,NULL,3,NULL,'2015-10-05 17:22:40','2015-07-06 10:24:44'),
(346,5,2,1,1.652893,'Artischocke','','','Stück',0,0,1,'week',1,NULL,NULL,3,NULL,'2015-08-19 09:35:46','2015-08-19 09:35:45'),
(347,4,2,1,0.000000,'Forelle','','','Stück',0,0,1,'week',1,NULL,NULL,3,NULL,'2018-05-17 16:13:39','2018-05-17 16:15:21'),
(348,4,2,1,0.000000,'Rindfleisch','','','',0,0,1,'week',1,NULL,NULL,3,NULL,'2018-05-17 16:15:33','2018-05-17 16:16:38'),
(349,5,2,1,4.545455,'Lagerprodukt','','','',0,1,1,'week',1,NULL,NULL,3,NULL,'2018-08-16 12:15:48','2018-08-16 12:16:51'),
(350,5,2,1,0.000000,'Lagerprodukt mit Varianten','','','',0,1,1,'week',1,NULL,NULL,3,NULL,'2018-08-16 12:19:06','2018-08-16 12:19:23'),
(351,5,1,1,0.000000,'Lagerprodukt 2','','','',0,1,1,'week',1,NULL,NULL,3,NULL,'2019-06-05 15:09:53','2019-06-05 15:10:08'),
(352,5,1,1,1.200000,'Lagerprodukt mit Gewichtsbarcode','','','',0,1,1,'week',1,NULL,NULL,3,NULL,'2019-06-05 15:09:53','2019-06-05 15:10:08');
/*!40000 ALTER TABLE `fcs_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute` DISABLE KEYS */;
INSERT INTO `fcs_product_attribute` VALUES
(10,60,0.545455,0),
(11,348,0.000000,1),
(12,348,0.000000,0),
(13,350,1.818182,1),
(14,350,3.636364,0),
(15,350,0.000000,0);
/*!40000 ALTER TABLE `fcs_product_attribute` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_product_attribute_combination` DISABLE KEYS */;
INSERT INTO `fcs_product_attribute_combination` VALUES
(33,10),
(36,11),
(35,12),
(36,13),
(35,14),
(36,15);
/*!40000 ALTER TABLE `fcs_product_attribute_combination` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_purchase_prices` DISABLE KEYS */;
INSERT INTO `fcs_purchase_prices` VALUES
(1,346,0,1,1.200000),
(2,0,13,0,1.400000),
(3,347,0,3,NULL),
(4,348,0,3,NULL),
(5,60,0,2,NULL),
(6,0,10,0,0.250000),
(7,163,0,0,1.072727);
/*!40000 ALTER TABLE `fcs_purchase_prices` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_sliders` DISABLE KEYS */;
INSERT INTO `fcs_sliders` VALUES
(6,'demo-slider.jpg',NULL,0,0,1);
/*!40000 ALTER TABLE `fcs_sliders` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_stock_available` DISABLE KEYS */;
INSERT INTO `fcs_stock_available` VALUES
(132,60,0,1015,0,NULL,1,NULL),
(195,102,0,2996,0,NULL,1,NULL),
(196,103,0,990,0,NULL,1,NULL),
(318,163,0,988,0,NULL,1,NULL),
(674,339,0,2959,0,NULL,1,NULL),
(678,340,0,990,0,NULL,1,NULL),
(680,344,0,78,0,NULL,0,NULL),
(686,346,0,97,0,NULL,0,NULL),
(692,60,9,996,0,NULL,1,NULL),
(693,60,10,19,0,NULL,0,NULL),
(704,347,0,999,0,NULL,1,NULL),
(705,348,0,1998,0,NULL,1,NULL),
(706,348,11,999,0,NULL,1,NULL),
(707,348,12,999,0,NULL,1,NULL),
(708,349,0,5,-5,0,0,NULL),
(709,350,0,1004,0,NULL,1,NULL),
(710,350,13,5,-5,0,0,NULL),
(711,350,14,999,0,NULL,1,NULL),
(712,350,15,999,0,NULL,1,NULL),
(713,351,0,999,0,NULL,1,NULL),
(714,352,0,999,0,NULL,1,NULL);
/*!40000 ALTER TABLE `fcs_stock_available` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_storage_locations` DISABLE KEYS */;
INSERT INTO `fcs_storage_locations` VALUES
(1,'Keine Kühlung',10),
(2,'Kühlschrank',20),
(3,'Tiefkühler',30);
/*!40000 ALTER TABLE `fcs_storage_locations` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_sync_domains` DISABLE KEYS */;
INSERT INTO `fcs_sync_domains` VALUES
(1,'{{serverName}}',1);
/*!40000 ALTER TABLE `fcs_sync_domains` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_sync_products` DISABLE KEYS */;
INSERT INTO `fcs_sync_products` VALUES
(1,1,346,346,0,0),
(2,1,350,350,0,0),
(3,1,350,350,14,14),
(4,1,350,350,13,13);
/*!40000 ALTER TABLE `fcs_sync_products` ENABLE KEYS */;

/*!40000 ALTER TABLE `fcs_units` DISABLE KEYS */;
INSERT INTO `fcs_units` VALUES
(1,347,0,1.50,0.98,'g',100,1,350.000),
(2,0,11,20.00,NULL,'kg',1,1,0.500),
(3,0,12,20.00,14.00,'g',500,1,300.000),
(4,349,0,0.00,NULL,'kg',1,0,0.000),
(5,0,13,0.00,NULL,'kg',1,0,0.000),
(6,0,14,0.00,NULL,'kg',1,0,0.000),
(7,0,15,10.00,6.00,'kg',1,1,0.500),
(8,351,0,15.00,NULL,'kg',1,1,1.000),
(9,352,0,12.00,NULL,'kg',1,1,1.000);
/*!40000 ALTER TABLE `fcs_units` ENABLE KEYS */;

/*!40000 ALTER TABLE `queue_processes` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue_processes` ENABLE KEYS */;

/*!40000 ALTER TABLE `queued_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `queued_jobs` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

";
        $this->execute($query);
    }
}
