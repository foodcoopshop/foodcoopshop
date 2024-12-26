<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

use Cake\Core\Configure;

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

trait EditGroupTrait
{

    public function editGroup()
    {
        $customerId = (int) $this->getRequest()->getData('customerId');
        $groupId = (int) $this->getRequest()->getData('groupId');

        $this->request = $this->request->withParam('_ext', 'json');

        if (! in_array($groupId, array_keys(Configure::read('app.htmlHelper')->getAuthDependentGroups($this->identity->getGroupId())))) {
            $message = 'user group not allowed: ' . $groupId;
            $this->log($message);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $customersTable = $this->getTableLocator()->get('Customers');
        $oldCustomer = $customersTable->find('all', conditions: [
            'Customers.id_customer' => $customerId
        ])->first();

        // eg. member is not allowed to change groupId of admin, not even to set a groupid he would be allowed to (member)
        if ($this->identity->getGroupId() < $oldCustomer->id_default_group) {
            $message = 'logged user has lower groupId than the user he wants to edit: customerId: ' . $oldCustomer->id_customer . ', groupId: ' . $oldCustomer->id_default_group;
            $this->log($message);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $customersTable->save(
            $customersTable->patchEntity(
                $oldCustomer,
                [
                    'id_default_group' => $groupId
                ]
                )
            );

        $messageString = __d('admin', 'The_group_of_the_member_{0}_was_changed_to_{1}.', [
            '<b>' . $oldCustomer->name . '</b>',
            '<b>' . Configure::read('app.htmlHelper')->getGroupName($groupId) . '</b>'
        ]);
        $this->Flash->success($messageString);
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('customer_group_changed', $this->identity->getId(), $customerId, 'customers', $messageString);

        $this->set([
            'status' => 1,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status']);
    }

}