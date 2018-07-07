<?php
use Migrations\AbstractMigration;

class CurrencySymbolAsConfiguration extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_CURRENCY_SYMBOL', 'Währungssymbol', '€', 'readonly', '52', 'de_DE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
        ");
    }
}
