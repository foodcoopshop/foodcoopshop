<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class CheckCreditBalanceLimit extends AbstractMigration
{
    public function change()
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Ab welchem Guthaben-Stand soll die Erinnerungsmail versendet werden?';
                break;
            case 'pl_PL':
            case 'en_US':
                $text = 'Height of credit saldo when the reminder email is sent.';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_CHECK_CREDIT_BALANCE_LIMIT', '".$text."', '0', 'number', '1450', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);
    }

}
