<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\PdfWriter\Traits;

use Cake\Datasource\FactoryLocator;

trait MemberCardDataTrait
{

    public function getMemberCardCustomerData($customerIds)
    {

        if (empty($customerIds)) {
            throw new \Exception('no customer id passed');
        }

        $customerTable = FactoryLocator::get('Table')->get('Customers');
        $customerTable->dropManufacturersInNextFind();
        $customers = $customerTable->find('all',
            fields: [
                'system_bar_code' => $customerTable->getBarcodeFieldString(),
            ],
            conditions: [
                'Customers.id_customer IN' => $customerIds,
            ],
            order: $customerTable->getCustomerOrderClause('ASC'),
            contain: [
                'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
            ]
        );
        $customers = $customerTable->addCustomersNameForOrderSelect($customers);
        $customers->select($customerTable);
        $customers->select($customerTable->AddressCustomers);
        return $customers;

    }

}