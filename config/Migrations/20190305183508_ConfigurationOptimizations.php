<?php
use Cake\Core\Configure;
use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class ConfigurationOptimizations extends AbstractMigration
{
    public function change()
    {
        $this->execute("DELETE FROM fcs_configuration WHERE name = 'FCS_PAYMENT_PRODUCT_MAXIMUM';");
        $value = '';
        if (Configure::check('app.registrationNotificationEmails')) {
            $value = join(',', Configure::read('app.registrationNotificationEmails'));
        }
        switch(I18n::getLocale()) {
            case 'de_DE':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_REGISTRATION_NOTIFICATION_EMAILS', 'Wer soll bei neuen Registrierungen informiert werden?<br /><div class=\"small\">Mehrere E-Mail-Adressen mit , (ohne Leerzeichen) trennen.</div>', '".$value."', 'text', '55', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
            case 'en_US':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_REGISTRATION_NOTIFICATION_EMAILS', 'Who should be notified on new registrations?<br /><div class=\"small\">Please separate multiple e-mail addresses with , (no space).</div>', '".$value."', 'text', '55', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
        }
        $this->execute($sql);
    }
}
