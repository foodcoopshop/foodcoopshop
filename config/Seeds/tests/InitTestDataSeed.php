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
TRUNCATE TABLE `fcs_sync_domains`;
TRUNCATE TABLE `fcs_sync_products`;
TRUNCATE TABLE `fcs_units`;
TRUNCATE TABLE `phinxlog`;
TRUNCATE TABLE `queue_phinxlog`;
TRUNCATE TABLE `queue_processes`;
TRUNCATE TABLE `queued_jobs`;

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
