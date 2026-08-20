<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait GetCustomersForDropdownTrait
{

    public function getCustomersForDropdown(bool $includeManufacturers, bool $includeOfflineCustomers = true): void
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $conditions = [];
        if ($this->identity->isCustomer()) {
            $conditions = ['Customers.id_customer' => $this->identity->getId()];
        }

        if ($this->identity->isSuperadmin()) {
            $includeOfflineCustomers = true;
        }

        $customerTable = $this->getTableLocator()->get('Customers');
        $customers = $customerTable->getForDropdown($includeManufacturers, $includeOfflineCustomers, $conditions);
        $customersForDropdown = [];
        foreach ($customers as $key => $ps) {
            $options = [];
            foreach ($ps as $pId => $p) {
                $options[] = [
                    'value' => (string) $pId,
                    'text' => $p,
                ];
            }
            $customersForDropdown[] = [
                'label' => $key,
                'options' => $options,
            ];
        }

        array_unshift($customersForDropdown, [
            'value' => '',
            'text' => __('all_members'),
        ]);

        $this->set([
            'status' => 1,
            'dropdownData' => $customersForDropdown,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'dropdownData']);
    }

}