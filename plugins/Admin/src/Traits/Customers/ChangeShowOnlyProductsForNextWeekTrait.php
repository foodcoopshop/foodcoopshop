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

trait ChangeShowOnlyProductsForNextWeekTrait
{

    public function changeShowOnlyProductsForNextWeek(): void
    {

        $customersTable = $this->getTableLocator()->get('Customers');
        $customer = $customersTable->find('all',
        conditions: [
            'Customers.id_customer' => $this->identity->getId(),
        ],
        contain: [
            'AddressCustomers'
        ])->first();

        $customer->show_only_products_for_next_week = !$customer->show_only_products_for_next_week;
        $customersTable->save($customer);

        $this->renewAuthSession();

        $this->redirect($this->referer());
    }

}