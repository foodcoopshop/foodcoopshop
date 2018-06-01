<?php
use Migrations\AbstractMigration;

class LocaleConfig extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_LOCALE', 'Sprache<br /><div class=\"small\">In welcher Sprache möchtest du die Software verwenden?</div>', 'de_DE', 'dropdown', '195', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
        ");
    }
}
