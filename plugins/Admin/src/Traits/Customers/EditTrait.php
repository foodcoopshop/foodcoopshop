<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use App\Services\SanitizeService;

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

trait EditTrait {

    public function profile()
    {
        $this->set('title_for_layout', __d('admin', 'Edit_my_profile'));
        $this->_processForm($this->identity->getId());
        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit($customerId)
    {
        if ($customerId === null) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_profile'));
        $this->_processForm($customerId);
        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    private function _processForm($customerId)
    {

        $isOwnProfile = $this->identity->getId() == $customerId;
        $this->set('isOwnProfile', $isOwnProfile);

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all',
        conditions: [
            'Customers.id_customer' => $customerId
        ],
        contain: [
            'AddressCustomers'
        ])->first();

        if (empty($customer)) {
            throw new NotFoundException('customer not found');
        }

        $this->setFormReferer();

        if (empty($this->getRequest()->getData())) {
            $this->set('customer', $customer);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $this->setRequest($this->getRequest()->withData('Customers.email', $this->getRequest()->getData('Customers.address_customer.email')));
        $this->setRequest($this->getRequest()->withData('Customers.address_customer.firstname', $this->getRequest()->getData('Customers.firstname')));
        $this->setRequest($this->getRequest()->withData('Customers.address_customer.lastname', $this->getRequest()->getData('Customers.lastname')));

        $this->setRequest($this->getRequest()->withoutData('Customers.active'));
        $this->setRequest($this->getRequest()->withoutData('Customers.id_default_group'));

        $customer = $this->Customer->patchEntity(
            $customer,
            $this->getRequest()->getData(),
            [
                'validate' => 'edit',
                'associated' => [
                    'AddressCustomers'
                ]
            ]
            );

        if ($customer->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('customer', $customer);
            $this->render('edit');
        } else {
            $this->Customer->save(
                $customer,
                [
                    'associated' => [
                        'AddressCustomers'
                    ]
                ]
                );

            if (!empty($this->getRequest()->getData('Customers.tmp_image'))) {
                $this->saveUploadedImage($customer->id_customer, $this->getRequest()->getData('Customers.tmp_image'), Configure::read('app.htmlHelper')->getCustomerThumbsPath(), Configure::read('app.customerImageSizes'));
            }

            if (!empty($this->getRequest()->getData('Customers.delete_image'))) {
                $this->deleteUploadedImage($customer->id_customer, Configure::read('app.htmlHelper')->getCustomerThumbsPath());
            }

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            if ($isOwnProfile) {
                $message = __d('admin', 'Your_profile_was_changed.');
            } else {
                $message = __d('admin', 'The_profile_of_{0}_was_changed.', ['<b>' . $customer->name . '</b>']);
            }
            $this->ActionLog->customSave('customer_profile_changed', $this->identity->getId(), $customer->id_customer, 'customers', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $customer->id_customer);

            if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getCustomerProfile()) {
                $this->renewAuthSession();
            }

            $this->redirect($this->getPreparedReferer());
        }

        $this->set('customer', $customer);
    }

}