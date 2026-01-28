<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\BaseMigration;

class AddConfigurationTextForHome extends BaseMigration
{
    public function change(): void
    {
        $this->execute("
            INSERT INTO fcs_configuration (name, active, text, value, type, position, locale)
            VALUES (
                'FCS_HOME_TEXT', 
                1,
                'Text that is shown on the home page.<br /><div class=\"small\">Optional.</div>', 
                '', 
                'textarea_big', 
                1290, 
                '" . I18n::getLocale() . "'
            );
        ");
    }
}