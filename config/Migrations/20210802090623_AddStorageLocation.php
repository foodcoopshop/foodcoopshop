<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class AddStorageLocation extends AbstractMigration
{
    public function change()
    {

        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Lagerort für Produkte erfassen und in Bestelllisten anzeigen?<div class="small">Lagerorte: Keine Kühlung / Kühlschrank / Tiefkühler. Es erscheint ein zusätzlicher Button neben "Bestellungen - Bestellungen als PDF generieren"</div>';
                $storageLocationA = 'Keine Kühlung';
                $storageLocationB = 'Kühlschrank';
                $storageLocationC = 'Tiefkühler';
                break;
            default:
                $text = 'Save storage location for products?<div class="small">New button next to "Orders - show order as pdf"</div>';
                $storageLocationA = 'No cooling';
                $storageLocationB = 'Refrigerator';
                $storageLocationC = 'Freezer';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS', '".$text."', '0', 'boolean', '3210', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $this->execute("
            CREATE TABLE `fcs_storage_locations` (
            `id` int(11) NOT NULL,
            `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `rank` tinyint(4) UNSIGNED NOT NULL DEFAULT '0'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            INSERT INTO `fcs_storage_locations` (`id`, `name`, `rank`) VALUES
                (1, '".$storageLocationA."', 10),
                (2, '".$storageLocationB."', 20),
                (3, '".$storageLocationC."', 30);
            ALTER TABLE `fcs_storage_locations`
            ADD PRIMARY KEY (`id`);
            ALTER TABLE `fcs_storage_locations`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
            COMMIT;
        ");

        $this->execute("ALTER TABLE `fcs_product` ADD `id_storage_location` TINYINT(4) UNSIGNED NOT NULL DEFAULT '1' AFTER `id_tax`;");

    }
}
