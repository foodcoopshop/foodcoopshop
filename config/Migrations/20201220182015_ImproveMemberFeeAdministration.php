<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class ImproveMemberFeeAdministration extends AbstractMigration
{
    public function change()
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Welche Produkte werden als Mitgliedsbeitrag verwendet?<div class="small">Die ausgewählten Produkte sind Datengrundlage der Spalte Mitgliedsbeitrag in der Mitgliederverwaltung und werden nicht in der Umsatzstatistik angezeigt.</div>';
                break;
            case 'pl_PL':
            case 'en_US':
                $text = 'Which products are used as member fee product?<div class="small">The selected products are the basis for the column Member Fee in the members adminstration and are not shown in the turnover statistics.</div>';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_MEMBER_FEE_PRODUCTS', '".$text."', '', 'multiple_dropdown', '3300', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);
    }
}
