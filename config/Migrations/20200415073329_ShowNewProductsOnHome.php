<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class ShowNewProductsOnHome extends AbstractMigration
{
    public function change()
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Neue Produkte auch auf der Startseite anzeigen?';
                break;
            case 'pl_PL':
            case 'en_US':
                $text = 'Show new products on home?';
                break;
        }
        
        $sql = "INSERT INTO `fcs_configuration` (
                  `id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`
               )
               VALUES (
                    NULL, '1', 'FCS_SHOW_NEW_PRODUCTS_ON_HOME', '".$text."', '1', 'boolean', '22', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);
    }
}
