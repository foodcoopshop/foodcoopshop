<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use Cake\Routing\Router;

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

trait RenewAuthSessionTrait 
{

    protected function renewAuthSession()
    {
        $customerTable = $this->getTableLocator()->get('Customers');
        $customer = $customerTable->find('all',
            conditions: [
                'Customers.id_customer' => $this->identity->getId()
            ],
            contain: [
                'AddressCustomers',
            ]
        )->first();
        if (!empty($customer)) {
            $this->Authentication->setIdentity($customer);
            Router::setRequest($this->getRequest());
        }
    }

}

