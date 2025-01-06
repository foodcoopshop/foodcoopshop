<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use App\Model\Entity\Payment;
use Cake\Http\Response;

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

trait DeleteTrait
{

    public function delete(int $customerId): ?Response
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $isOwnProfile = $this->identity->getId() == $customerId;

        if (!$this->identity->isSuperadmin()) {
            throw new ForbiddenException('deleting user ' . $customerId . 'denied');
        }

        $customersTable = $this->getTableLocator()->get('Customers');
        $paymentsTable = $this->getTableLocator()->get('Payments');

        try {

            $customer = $customersTable->find('all',
            conditions: [
                'Customers.id_customer' => $customerId
            ],
            contain: [
                'Manufacturers',
                'ActiveOrderDetails'
            ])->first();

            if (empty($customer)) {
                throw new RecordNotFoundException('customer ' . $customerId . ' not found');
            }

            $errors = [];

            if (Configure::read('app.applyOrdersNotYetBilledCheckOnDeletingCustomers')) {
                $openOrderDetails = count($customer->active_order_details);
                if ($openOrderDetails > 0) {
                    $errors[] = __d('admin', 'Amount_of_orders_where_the_invoice_has_not_been_sent_yet_to_the_manufacturer:'). ' '. $openOrderDetails . '.';
                }
            }

            if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
                $creditBalance = $customersTable->getCreditBalance($customerId);
                if ($creditBalance != 0) {
                    $errors[] = __d('admin', 'The_credit_is') . ' ' . Configure::read('app.numberHelper')->formatAsCurrency($creditBalance) . '. ' . __d('admin', 'It_needs_to_be_zero.');
                }
            }

            if (Configure::read('app.applyPaymentsOkCheckOnDeletingCustomers')) {
                $notApprovedPaymentsCount = $paymentsTable->find('all', conditions: [
                    'id_customer' => $customerId,
                    'approval < ' => APP_ON,
                    'status' => APP_ON,
                    'type' => Payment::TYPE_PRODUCT,
                    'DATE_FORMAT(date_add, \'%Y\') >= DATE_FORMAT(NOW(), \'%Y\') - 2' // check only last full 2 years (eg. payment of 02.02.2018 is checked on 12.11.2020)
                ])->count();
                if ($notApprovedPaymentsCount > 0) {
                    $errors[] = __d('admin', 'Amount_of_not_approved_payments_within_the_last_2_years:'). ' '. $notApprovedPaymentsCount . '.';
                }
            }

            if (!empty($customer->manufacturers)) {
                $manufacturerNames = [];
                foreach($customer->manufacturers as $manufacturer) {
                    $manufacturerNames[] = $manufacturer->name;
                }
                $errors[] = __d('admin', 'The_member_is_still_associated_to_the_following_manufacturers:') . ' ' . join(', ', $manufacturerNames);
            }

            if (!empty($errors)) {
                $errorString = '<ul><li>' . join('</li><li>', $errors) . '</li></ul>';
                throw new \Exception($errorString);
            }
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $customersTable->deleteAll(['id_customer' => $customerId]);
        $addressCustomersTable = $this->getTableLocator()->get('AddressCustomers');
        $feedbacksTable = $this->getTableLocator()->get('Feedbacks');
        $addressCustomersTable->deleteAll(['id_customer' => $customerId]);
        $feedbacksTable->deleteAll(['customer_id' => $customerId]);

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->removeCustomerNameFromAllActionLogs($customer->firstname . ' ' . $customer->lastname);
        $actionLogsTable->removeCustomerNameFromAllActionLogs($customer->lastname . ' ' . $customer->firstname);
        $actionLogsTable->removeCustomerEmailFromAllActionLogs($customer->email);

        $this->deleteUploadedImage($customerId, Configure::read('app.htmlHelper')->getCustomerThumbsPath());

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        if ($isOwnProfile) {
            $message = __d('admin', 'Your_account_has_been_deleted_successfully.');
            $redirectUrl = Configure::read('app.slugHelper')->getHome();
        } else {
            $message = __d('admin', '{0}_has_deleted_an_account.', [$this->identity->name]);
            $redirectUrl = $this->getRequest()->getData('referer');
        }
        $actionLogsTable->customSave('customer_deleted', $this->identity->getId(), $customer->id_customer, 'customers', $message);
        $this->Flash->success($message);

        if ($isOwnProfile) {
            $this->identity->logout();
        }

        $this->set([
            'status' => 1,
            'msg' => 'ok',
            'redirectUrl' => $redirectUrl
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'redirectUrl']);
        return null;
    }

}