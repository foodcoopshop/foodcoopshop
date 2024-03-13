<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Cake\Datasource\FactoryLocator;

class ManufacturerLoginFix extends AbstractMigration
{
    public function change(): void
    {

        $manufacturersTable = FactoryLocator::get('Table')->get('Manufacturers');
        $customersTable = FactoryLocator::get('Table')->get('Customers');

        $manufacturers = $manufacturersTable->find('all',
        contain: [
            'AddressManufacturers',
        ]);

        $customerUpdateCount = 0;
        foreach($manufacturers as $manufacturer) {
            $customerRecord = $manufacturersTable->getCustomerRecord($manufacturer->address_manufacturer->email);
            if (!empty($customerRecord)) {
                if ($customerRecord->active != $manufacturer->active) {
                    $customerRecord->active = $manufacturer->active;
                    $customersTable->save($customerRecord);
                    $customerUpdateCount++;
                    echo 'Updated customer status for ' . $manufacturer->id_manufacturer . ' ' . $manufacturer->name . ' to ' . ($manufacturer->active == 0 ? 'offline' : 'online') . PHP_EOL;
                }
            }
        }
        echo 'Updated ' . $customerUpdateCount . ' customers.' . PHP_EOL;
    }

}
