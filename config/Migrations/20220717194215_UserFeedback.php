<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class UserFeedback extends AbstractMigration
{
    public function change()
    {

        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Mitglieder- und Hersteller-Feedback aktiv?<br /><div class="small">Ermöglicht das Erfassen und Anzeigen von Feedback. <a href="https://foodcoopshop.github.io/de/user-feedback.html" target="_blank">Mehr Infos</a></div>';
                break;
            default:
                $text = 'Member and manufacturer feedback enabled?<br /><div class="small">Members and manufacturers can write feedback.</div>';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_USER_FEEDBACK_ENABLED', '".$text."', '0', 'boolean', '3500', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $sql = "CREATE TABLE fcs_feedbacks ( `id` INT NOT NULL AUTO_INCREMENT , `customer_id` INT NULL DEFAULT NULL , `text` TEXT NULL, `approved` DATETIME NOT NULL DEFAULT '1970-01-01' , `modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `privacy_type` TINYINT NULL DEFAULT '0' , PRIMARY KEY (`id`)) ENGINE = InnoDB;";
        $this->execute($sql);

    }
}
