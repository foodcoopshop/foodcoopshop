<?php
use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class AdditionalFieldForPrivacyPolicy extends AbstractMigration
{
    public function change()
    {
        
        $this->execute("UPDATE fcs_configuration SET position = 9 WHERE name = 'FCS_PLATFORM_OWNER';");
        switch(I18n::getLocale()) {
            case 'de_DE':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_APP_ADDITIONAL_DATA', 'Zusätzliche Infos zur Foodcoop<br /><div class=\"small\">Z.B. ZVR-Zahl</div>', '', 'textarea', '8', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
            case 'pl_PL':
            case 'en_US':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_APP_ADDITIONAL_DATA', 'Additional food-coop infos', '', 'textarea', '8', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
        }
        $this->execute($sql);
        
    }
}
