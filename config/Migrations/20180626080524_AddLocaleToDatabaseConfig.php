<?php
use Migrations\AbstractMigration;

class AddLocaleToDatabaseConfig extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_DEFAULT_LOCALE', 'Sprache', 'de_DE', 'readonly', '55', 'de_DE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
        ");
    }
}
