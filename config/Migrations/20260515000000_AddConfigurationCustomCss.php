<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddConfigurationCustomCss extends BaseMigration
{
    public function change(): void
    {
        $this->execute("INSERT INTO fcs_configuration (name, active, value, type, position) VALUES ('FCS_CUSTOM_CSS',1,'','textarea_css',3610);");
    }
}