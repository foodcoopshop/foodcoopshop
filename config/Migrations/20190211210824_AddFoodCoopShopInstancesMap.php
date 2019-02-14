<?php
use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class AddFoodCoopShopInstancesMap extends AbstractMigration
{
    public function change()
    {
        
        switch(I18n::getLocale()) {
            case 'de_DE':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_FOODCOOPS_MAP_ENABLED', 'Auf Home Karte mit anderen Foodcoops anzeigen?', '1', 'boolean', '128', 'de_DE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
            case 'en_US':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_FOODCOOPS_MAP_ENABLED', 'Show map with other foodcoops on home?',         '1', 'boolean', '128', 'en_US', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
        }
        
        $this->execute($sql);
    }
}
