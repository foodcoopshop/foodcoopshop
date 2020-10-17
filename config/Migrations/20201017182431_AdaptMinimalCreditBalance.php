<?php
declare(strict_types=1);

use Cake\Datasource\FactoryLocator;
use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class AdaptMinimalCreditBalance extends AbstractMigration
{
    public function change()
    {

        switch(I18n::getLocale()) {
            case 'de_DE':
                $newText = 'Bis zu welchem Guthaben-Betrag sollen Bestellungen möglich sein?';
                break;
            case 'pl_PL':
            case 'en_US':
                $newText = 'Up to which credit amount orders should be possible?';
                break;
        }
        $this->Configuration = FactoryLocator::get('Table')->get('Configurations');
        $oldValue = $this->Configuration->find('all', [
            'conditions' => [
                'Configurations.name' => 'FCS_MINIMAL_CREDIT_BALANCE'
            ]
        ])->first();

        $newValue = $oldValue->value * -1;
        if($oldValue->value == 1) {
            $newValue = 0;
        }
        if($oldValue->value == 0) {
            $newValue = -500;
        }

        $sql = "UPDATE fcs_configuration SET
                text = '".$newText."',
                value = ".$newValue."
                WHERE name = 'FCS_MINIMAL_CREDIT_BALANCE'";
        $this->execute($sql);

    }
}
