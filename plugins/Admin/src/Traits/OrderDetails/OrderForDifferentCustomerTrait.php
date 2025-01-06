<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;

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

trait OrderForDifferentCustomerTrait 
{

    protected function initOrderForDifferentCustomer($customerId): void
    {

        if (! $customerId) {
            throw new RecordNotFoundException('customerId not passed');
        }

        $customersTable = $this->getTableLocator()->get('Customers');
        $orderCustomer = $customersTable->find('all',
            conditions: [
                'Customers.id_customer' => $customerId
            ],
            contain: [
                'AddressCustomers'
            ]
        )->first();

        if (! empty($orderCustomer)) {
            $this->getRequest()->getSession()->write('OrderIdentity', $orderCustomer);
        } else {
            $this->Flash->error(__d('admin', 'No_member_found_with_id_{0}.', [$customerId]));
        }
    }

    public function initInstantOrder($customerId): void
    {
        $this->initOrderForDifferentCustomer($customerId);
        $this->redirect('/');
    }

    public function initSelfServiceOrder($customerId): void
    {
        $this->initOrderForDifferentCustomer($customerId);
        $this->redirect(Configure::read('app.slugHelper')->getSelfService());
    }

    public function iframeInstantOrder(): void
    {
        $this->set('title_for_layout', __d('admin', 'Instant_order'));
    }

    public function iframeSelfServiceOrder(): void
    {
        $this->set('title_for_layout', __d('admin', 'Self_service_order'));
    }

}
