<?php
use Migrations\AbstractMigration;

class ChangeRegistrationInfoTextConfiguration extends AbstractMigration
{
    public function change()
    {
        $sql = "UPDATE fcs_configuration SET
                    name = 'FCS_REGISTRATION_INFO_TEXT',
                    type = 'textarea_big'
                    WHERE name = 'FCS_AUTHENTICATION_INFO_TEXT';";
        $this->execute($sql);
    }
}
