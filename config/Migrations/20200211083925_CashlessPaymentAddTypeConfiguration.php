<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class CashlessPaymentAddTypeConfiguration extends AbstractMigration
{
    public function change()
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Art der Eintragung der Guthaben-Aufladungen<br /><div class="small">Wie gelangen die Guthaben-Aufladungen vom Bankkonto in den FoodCoopShop?</div>';
                break;
            case 'pl_PL':
            case 'en_US':
                $text = 'Type of adding the payments<br /><div class="small">How do the payment addings get into FoodCoopShop?</div>';
                break;
        }
        
        $sql = "INSERT INTO `fcs_configuration` (
                  `id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`
               )
               VALUES (
                    NULL, '1', 'FCS_CASHLESS_PAYMENT_ADD_TYPE', '".$text."', 'manual', 'dropdown', '145', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);
        
        $this->execute("ALTER TABLE `fcs_payments` CHANGE `approval_comment` `approval_comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
        $this->execute("ALTER TABLE `fcs_timebased_currency_payments` CHANGE `approval_comment` `approval_comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
        
    }
}
