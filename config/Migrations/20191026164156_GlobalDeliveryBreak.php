<?php
use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class GlobalDeliveryBreak extends AbstractMigration
{
    public function change()
    {
        
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Lieferpause für alle Hersteller?<br /><div class="small">Hier können lieferfreie Tage (z.B. Feiertage) für die gesamte Foodcoop festgelegt werden. Eine Info-Box wird automatisch zwei Wochen vor Beginn der Lieferpause angezeigt.</div>';
                break;
            case 'pl_PL':
            case 'en_US':
                $text = 'Delivery break for all manufacturers?<br /><div class="small">Here you can define delivery-free days for the whole food-coop. An info box is shown automatically two weeks before the delivery break.</div>';
                break;
        }
        
        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');
        $configuration = $this->Configuration->find('all', [
            'conditions' => [
                'Configurations.name' => 'FCS_CART_ENABLED'
            ]
        ])->first();
        
        $value = '';
        if ($configuration->value == '0') {
            $value = Configure::read('app.timeHelper')->getNextDeliveryDay(time());
        }
        
        $sql = "UPDATE fcs_configuration SET
                    name = 'FCS_NO_DELIVERY_DAYS_GLOBAL',
                    text = '".$text."',
                    type = 'multiple_dropdown',
                    value = '".$value."'
                    WHERE name = 'FCS_CART_ENABLED';";
        $this->execute($sql);
        
    }
}
