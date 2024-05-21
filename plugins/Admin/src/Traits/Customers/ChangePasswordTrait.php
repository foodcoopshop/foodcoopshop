<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

use Authentication\PasswordHasher\DefaultPasswordHasher;

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

trait ChangePasswordTrait
{

    public function changePassword()
    {
        $this->set('title_for_layout', __d('admin', 'Change_password'));

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', conditions: [
            'Customers.id_customer' => $this->identity->getId()
        ])->first();

        if (empty($this->getRequest()->getData())) {
            $this->set('customer', $customer);
            return;
        }

        $customer = $this->Customer->patchEntity(
            $customer,
            $this->getRequest()->getData(),
            [
                'validate' => 'changePassword'
            ]
        );

        if ($customer->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('customer', $customer);
        } else {
            $ph = new DefaultPasswordHasher();
            $this->Customer->save(
                $this->Customer->patchEntity(
                    $customer,
                    [
                        'passwd' => $ph->hash($this->getRequest()->getData('Customers.passwd_1'))
                    ]
                    )
                );

            if ($this->identity->isManufacturer()) {
                $message = __d('admin', 'The_manufacturer_{0}_has_changed_his_password.', ['<b>' . $this->identity->getManufacturerName() . '</b>']);
                $actionLogType = 'manufacturer_password_changed';
                $actionLogId = $this->identity->getManufacturerId();
                $actionLogModel = 'manufacturers';
            } else {
                $message = __d('admin', '{0}_has_changed_the_password.', ['<b>' . $this->identity->name . '</b>']);
                $actionLogType = 'customer_password_changed';
                $actionLogId = $this->identity->getId();
                $actionLogModel = 'customers';
            }

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave($actionLogType, $this->identity->getId(), $actionLogId, $actionLogModel, $message);
            $this->Flash->success(__d('admin', 'Your_new_password_has_been_saved_successfully.'));
            $this->redirect($this->referer());
        }

        $this->set('customer', $customer);
    }

}