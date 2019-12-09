<?php
use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class AddSelfServiceDbConfigTest extends AbstractMigration
{
    public function change()
    {
        // 1) adapt existing config
        $this->execute("
            UPDATE fcs_configuration SET
            type = 'boolean',
            position = 300
            WHERE name = 'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED';
        ");
        
        switch(I18n::getLocale()) {
            case 'de_DE':
                $sql = "UPDATE fcs_configuration SET
                    text = REPLACE(text, 'verwenden', 'aktiv')
                    WHERE name = 'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED';";
                $this->execute($sql);
                break;
        }
        
        // 2) add new config FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Selbstbedienungs-Modus im Test-Modus ausführen?<br /><div class="small">Keine Verlinkung im Haupt-Menü und bei Lagerprodukten.</div>';
                break;
            case 'pl_PL':
            case 'en_US':
                $text = 'Run self-service mode in test mode?<br /><div class="small">Does not add links to main menu and to stock products.</div>';
                break;
        }
        
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED', '".$text."', '0', 'boolean', '310', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);
        
        
    }
}
