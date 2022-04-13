<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class Newsletter extends AbstractMigration
{
    public function change()
    {

        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Newsletter-Funktion aktiv?<br /><div class="small">Mitglieder können sich bei der Registrierung für den Newsletter anmelden. <a href="https://foodcoopshop.github.io/de/mitglieder.html#newsletter-funktion" target="_blank">Mehr Infos</a></div>';
                break;
            default:
                $text = 'Newsletter enabled?<br /><div class="small">Shows newsletter checkbox on registration.</div>';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_NEWSLETTER_ENABLED', '".$text."', '0', 'boolean', '3400', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $this->execute("ALTER TABLE `fcs_customer` ADD `newsletter_enabled` TINYINT UNSIGNED NULL DEFAULT '0' AFTER `credit_upload_reminder_enabled`;");
    }
}
