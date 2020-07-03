<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class AddDeliveryRhythmDaily extends AbstractMigration
{
    public function change()
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Haupt-Lieferrhythmus<br /><div class="small">Wöchentlich oder täglich.</div>';
                break;
            case 'pl_PL':
            case 'en_US':
                $text = 'Main delivery rhythm<br /><div class="small">Weekly or daily.</div>';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_MAIN_DELIVERY_RHYTHM', '".$text."', 'weekly', 'readonly', '58', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);
    }
}