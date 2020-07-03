<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class CustomerCanSelectPickupDay extends AbstractMigration
{
    public function change()
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Mitglied kann Abholtag beim Bestellen selbst auswählen.';
                break;
            case 'pl_PL':
            case 'en_US':
                $text = 'Pickup day can be selected by member on order confirmation.';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY', '".$text."', '0', 'readonly', '59', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);
    }
}
