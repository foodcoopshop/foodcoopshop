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

use Cake\ORM\TableRegistry;

trait MemberCardDataTrait
{

    public function getMemberCardCustomerData($customerIds)
    {

        if (empty($customerIds)) {
            throw new \Exception('no customer id passed');
        }

        $customersTable = TableRegistry::getTableLocator()->get('Customers');
        $addressCustomersTable = TableRegistry::getTableLocator()->get('AddressCustomers');

        $customersTable->dropManufacturersInNextFind();
        $customers = $customersTable->find('all',
            fields: [
                'system_bar_code' => $customersTable->getBarcodeFieldString(),
            ],
            conditions: [
                $customersTable->aliasField('id_customer IN') => $customerIds,
            ],
            order: $customersTable->getCustomerOrderClause('ASC'),
            contain: [
                'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
            ]
        );
        $customers = $customersTable->addCustomersNameForOrderSelect($customers);
        $customers->select($customersTable);
        $customers->select($addressCustomersTable);
        return $customers;

    }

}