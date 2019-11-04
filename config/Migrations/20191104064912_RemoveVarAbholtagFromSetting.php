<?php
use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class RemoveVarAbholtagFromSetting extends AbstractMigration
{
    public function change()
    {

        switch(I18n::getLocale()) {
            case 'de_DE':
                $replaceText = '<br />Die Variable {ABHOLTAG} zeigt automatisch das richtige Abholdatum an.';
                $newText = '';
                break;
            case 'pl_PL':
            case 'en_US':
                $replaceText = '\r\n\r\n<p>The pickup day can be seen in the product description, you can pick the product up on <strong>{DELIVERY_DAY}</strong>&nbsp;between 5 and 7 pm.</p>\r\n\r\n';
                $newText = '<p>The pickup day can be seen in the product description.</p>';
                break;
        }
        
        $sql = "UPDATE fcs_configuration SET
                    text = REPLACE(text, '".$replaceText."', '".$newText."')
                    WHERE name = 'FCS_RIGHT_INFO_BOX_HTML';";
        $this->execute($sql);
        
    }
}
