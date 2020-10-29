<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class AddRetailMode extends AbstractMigration
{
    public function change()
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Einzelhandels-Modus aktiviert?<br /><div class="small"><a href="https://foodcoopshop.github.io/de/einzelhandel" target="_blank">Infos zur Verwendung im Einzelhandel</a></div>';
                break;
            default:
                $text = 'Retail mode activated?.';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_RETAIL_MODE_ENABLED', '".$text."', '0', 'readonly', '58', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);
    }
}
