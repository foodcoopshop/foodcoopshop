<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class UseExistingBarcode extends AbstractMigration
{
    public function change()
    {
        $sql = "DROP TABLE IF EXISTS `fcs_barcodes`;
                CREATE TABLE `fcs_barcodes` (
                  `id` int(10) UNSIGNED NOT NULL,
                  `product_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                  `product_attribute_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                  `barcode` VARCHAR(13) NULL DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ALTER TABLE `fcs_barcodes`
              ADD PRIMARY KEY (`id`),
              ADD KEY `product_id` (`product_id`,`product_attribute_id`);
            ALTER TABLE `fcs_barcodes`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
            COMMIT;";
        $this->execute($sql);
    }
}
