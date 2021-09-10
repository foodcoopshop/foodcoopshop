<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class Instagram extends AbstractMigration
{
    public function change()
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Instagram-Url für die Einbindung im Footer';
                break;
            default:
                $text = 'Instagram url for embedding in footer';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_INSTAGRAM_URL', '".$text."', '', 'text', '920', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $sql = "UPDATE `fcs_configuration` SET position = 900 WHERE name = 'FCS_APP_EMAIL';";
        $this->execute($sql);
        $sql = "UPDATE `fcs_configuration` SET position = 910 WHERE name = 'FCS_FACEBOOK_URL';";
        $this->execute($sql);
        $sql = "UPDATE `fcs_configuration` SET position = 920 WHERE name = 'FCS_FOOTER_CMS_TEXT';";
        $this->execute($sql);
        $sql = "UPDATE `fcs_configuration` SET position = 930 WHERE name = 'FCS_SHOW_FOODCOOPSHOP_BACKLINK';";
        $this->execute($sql);
    }
}
