<?php
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class AdditionalOrderPeriods extends AbstractMigration
{
    public function change()
    {
        
        $this->execute("
            ALTER TABLE `fcs_product` ADD `delivery_rhythm_type` VARCHAR(10) NOT NULL DEFAULT 'week' AFTER `active`,
                ADD `delivery_rhythm_count` TINYINT(10) NOT NULL DEFAULT '1' AFTER `delivery_rhythm_type`,
                ADD `delivery_rhythm_first_delivery_day` DATE NULL AFTER `delivery_rhythm_count`
                ADD `delivery_rhythm_order_possible_until` DATE NULL AFTER `delivery_rhythm_first_delivery_day`;
            ALTER TABLE `fcs_manufacturer` ADD `no_delivery_days` TEXT NOT NULL AFTER `send_product_sold_out_limit_reached_for_contact_person`;
        ");
        
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturers = $this->Manufacturer->find('all');
        foreach($manufacturers as $manufacturer) {
            $noDeliveryDays = [];
            if (!empty($manufacturer->holiday_from)) {
                if (empty($manufacturer->holiday_to)) {
                    $manufacturer->holiday_to = FrozenDate::create(date('Y'), 12, 26);
                }
                $manufacturer->holiday_from = FrozenDate::create(date('Y-m-d'));
                $period = new \DatePeriod(
                    new \DateTime($manufacturer->holiday_from->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'))),
                    new \DateInterval('P1D'),
                    new \DateTime($manufacturer->holiday_to->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')))
                );
                foreach ($period as $key => $value) {
                    $dayBetween = $value->format('Y-m-d');
                    $noDeliveryDays[] = Configure::read('app.timeHelper')->getDbFormattedPickupDayByDbFormattedDate($dayBetween);
                }
                $noDeliveryDays = array_unique($noDeliveryDays);
            }
            if (!empty($noDeliveryDays)) {
                $this->Manufacturer->save(
                    $this->Manufacturer->patchEntity($manufacturer, ['no_delivery_days' => join(',', $noDeliveryDays)])
                    );
            }
        }
        
        $this->execute("
            ALTER TABLE `fcs_manufacturer` DROP `holiday_from`, DROP `holiday_to`;
        ");
    
    }
}
