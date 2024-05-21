<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class InitDataSeed extends AbstractSeed
{
    public function run(): void
    {
        $query = "
            INSERT INTO `fcs_cronjobs` VALUES
            (1,'BackupDatabase','day',NULL,NULL,'04:00:00',1),
            (2,'CheckCreditBalance','week',NULL,'Friday','22:50:00',1),
            (3,'EmailOrderReminder','week',NULL,'Monday','18:10:00',1),
            (4,'PickupReminder','week',NULL,'Monday','09:10:00',1),
            (5,'SendInvoicesToManufacturers','month',5,NULL,'10:30:00',1),
            (6,'SendOrderLists','day',NULL,NULL,'04:20:00',1),
            (7,'SendInvoicesToCustomers','week',NULL,'Saturday','10:00:00',0),
            (8,'SendDeliveryNotes','month',1,NULL,'18:00:00',0);
        ";
        $this->execute($query);

        $query = "INSERT INTO `fcs_sliders` VALUES (1,'demo-slider.jpg',NULL,0,0,1);";
        $this->execute($query);

    }
}
