<?php
use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class IndividualSendOrderListDay extends AbstractMigration
{
    public function change()
    {
        
        $this->execute("
            ALTER TABLE `fcs_product`
                ADD `delivery_rhythm_send_order_list_weekday` INT(10) UNSIGNED NULL AFTER `delivery_rhythm_order_possible_until`,
                ADD `delivery_rhythm_send_order_list_day` DATE NULL AFTER `delivery_rhythm_send_order_list_weekday`;
        ");
        
        $weeklyPickupDay = Configure::read('app.sendOrderListsWeekday') + Configure::read('app.deliveryDayDelta');
        if ($weeklyPickupDay > 6) {
            $weeklyPickupDay -= 7;
        }
        switch(I18n::getLocale()) {
            case 'de_DE':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_WEEKLY_PICKUP_DAY', 'Wöchentlicher Abholtag', " . $weeklyPickupDay . ", 'readonly', '60', 'de_DE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                $sql .= "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 'Bestelllisten-Versand: x Tage vor dem Abholtag', " . Configure::read('app.deliveryDayDelta') . ", 'readonly', '65', 'de_DE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
            case 'en_US':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_WEEKLY_PICKUP_DAY', 'Weekly pickup day', " . $weeklyPickupDay . ", 'readonly', '60', 'en_US', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                $sql .= "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 'Sending of order lists: x days before pickup day', " . Configure::read('app.deliveryDayDelta') . ", 'readonly', '65', 'en_US', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
        }
        $this->execute($sql);
        
        TableRegistry::getTableLocator()->get('Configurations')->loadConfigurations();
        $this->execute("
            UPDATE fcs_product
                SET delivery_rhythm_send_order_list_weekday = " . Configure::read('app.timeHelper')->getSendOrderListsWeekday() . "
        ");
        $this->execute("
            UPDATE `fcs_cronjobs` SET `time_interval` = 'day', `weekday` = NULL WHERE `fcs_cronjobs`.`name` = 'SendOrderLists';
        ");
        
    }
}
