<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class HideHomeConfigurations extends BaseMigration
{
    public function up(): void
    {
        $this->execute("UPDATE fcs_configuration SET type = 'hidden' WHERE name = 'FCS_HOME_TEXT';");
        $this->execute("UPDATE fcs_configuration SET type = 'hidden' WHERE name = 'FCS_FOODCOOPS_MAP_ENABLED';");
    }
}
