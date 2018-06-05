<?php
use Migrations\AbstractMigration;

class LocaleConfig extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_LOCALE', 'Sprache<br /><div class=\"small\">In welcher Sprache möchtest du die Software verwenden?</div>', 'de_DE', 'dropdown', '195', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

            CREATE TABLE i18n (
                id int NOT NULL auto_increment,
                locale varchar(6) NOT NULL,
                model varchar(255) NOT NULL,
                foreign_key int(10) NOT NULL,
                field varchar(255) NOT NULL,
                content text,
                PRIMARY KEY (id),
                UNIQUE INDEX I18N_LOCALE_FIELD(locale, model, foreign_key, field),
                INDEX I18N_FIELD(model, foreign_key, field)
            );

        ");
    }
}
