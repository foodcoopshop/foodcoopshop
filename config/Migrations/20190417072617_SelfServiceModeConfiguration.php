<?php
use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class SelfServiceModeConfiguration extends AbstractMigration
{
    public function change()
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 'Selbstbedienungs-Modus für Lagerprodukte verwenden?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/selbstbedienungs-modus\" target=\"_blank\">Zur Online-Doku</a></div>', '0', 'readonly', '57', 'de_DE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
            case 'en_US':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 'Use self-service mode for stock products?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/en/self-service-mode\" target=\"_blank\">Online documentation</a></div>', '0', 'readonly', '57', 'en_US', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
        }
        $this->execute($sql);
    }
}
