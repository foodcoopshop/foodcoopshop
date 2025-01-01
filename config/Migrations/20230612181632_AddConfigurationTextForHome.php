<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class AddConfigurationTextForHome extends AbstractMigration
{
    public function change(): void
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Text, der auf der Startseite angezeigt wird.<br /><div class="small">Optional.</div>';
                break;
            default:
                $text = 'Text that is shown on the home page.<br /><div class="small">Optional.</div>';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_HOME_TEXT', '".$text."', '', 'textarea_big', '1290', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);
    }
}