<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

use Cake\Core\Configure;
use App\Mailer\AppMailer;
use Cake\Datasource\Exception\RecordNotFoundException;

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

trait ChangeStatusTrait
{

    public function changeStatus($customerId, $status, $sendEmail)
    {
        if (! in_array($status, [
            APP_OFF,
            APP_ON
        ])) {
            throw new RecordNotFoundException('status needs to be 0 or 1');
        }

        $customersTable = $this->getTableLocator()->get('Customers');
        $customer = $customersTable->find('all',
        conditions: [
            'Customers.id_customer' => $customerId
        ],
        contain: [
            'AddressCustomers'
        ])->first();

        $customer->active = $status;
        $customersTable->save($customer);

        $message = __d('admin', 'The_member_{0}_has_been_deactivated_succesfully.', ['<b>' . $customer->name . '</b>']);
        $actionLogType = 'customer_set_inactive';
        if ($status) {
            $message = __d('admin', 'The_member_{0}_has_been_activated_succesfully.', ['<b>' . $customer->name . '</b>']);
            $actionLogType = 'customer_set_active';
        }

        if ($sendEmail) {
            $newPassword = $customersTable->setNewPassword($customer->id_customer);

            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('customer_activated');
            $email->setTo($customer->email)
            ->setSubject(__d('admin', 'Your_account_was_activated'))
            ->setViewVars([
                'identity' => $this->identity,
                'data' => $customer,
                'newsletterCustomer' => $customer,
                'newPassword' => $newPassword,
            ]);

            if (Configure::read('app.termsOfUseEnabled')) {
                $email->addAttachments([__d('admin', 'Filename_Terms-of-use').'.pdf' => ['data' => $this->generateTermsOfUsePdf(), 'mimetype' => 'application/pdf']]);
            }
            $email->addToQueue();

            $message = __d('admin', 'The_member_{0}_has_been_activated_succesfully_and_the_member_was_notified_by_email.', ['<b>' . $customer->name . '</b>']);
        }

        $this->Flash->success($message);

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $customerId, 'customer', $message);

        $this->redirect($this->referer());
    }

}