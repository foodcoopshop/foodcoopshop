<?php
use Migrations\AbstractMigration;

class ShowProductPriceForGuests extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            UPDATE `fcs_configuration` SET `text` = 'Produkte für nicht eingeloggte Mitglieder sichtbar?' WHERE `fcs_configuration`.`name` = 'FCS_SHOW_PRODUCTS_FOR_GUESTS';
            INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS', 'Produktpreis für nicht eingeloggte Mitglieder anzeigen?', '0', 'boolean', '21', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
        ");
    }
}
