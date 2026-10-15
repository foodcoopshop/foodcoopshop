<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddConfigurationHomeMetadata extends BaseMigration
{
    public function change(): void
    {
        $this->execute("INSERT INTO fcs_configuration (name, active, value, type, position) VALUES ('FCS_HOME_META_TITLE',1,'','hidden',1291);");
        $this->execute("INSERT INTO fcs_configuration (name, active, value, type, position) VALUES ('FCS_HOME_META_DESCRIPTION',1,'','hidden',1292);");
    }
}