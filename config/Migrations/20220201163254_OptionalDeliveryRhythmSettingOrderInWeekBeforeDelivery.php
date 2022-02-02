<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class OptionalDeliveryRhythmSettingOrderInWeekBeforeDelivery extends AbstractMigration
{
    public function change()
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Bestellungen beim ein- und zweiwöchigen Lieferhythmus sind nur in der Woche vor der Lieferung möglich.';
                break;
            default:
                $text = 'Ordering products with delivery rhythm one or two weeks is only possible in the week before delivery.';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', '".$text."', '0', 'boolean', '3210', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);
    }
}
