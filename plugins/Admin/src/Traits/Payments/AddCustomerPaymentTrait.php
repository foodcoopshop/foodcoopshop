<?php
declare(strict_types=1);

namespace Admin\Traits\Payments;

use App\Services\SanitizeService;
use Cake\Http\Response;
use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use App\Exception\DepositThresholdExceededException;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait AddCustomerPaymentTrait
{

    public function addCustomerPayment(int $customerId): ?Response
    {
        $type = $this->getRequest()->getData('type');
        
        if (!in_array($type, Payment::ALLOWED_CUSTOMER_TYPES)) {
            throw new \Exception('payment type not valid: ' . $type);
        }


        $amount = $this->getRequest()->getData('amount');
        $amount = Configure::read('app.numberHelper')->parseFloatRespectingLocale($amount);

        if ($amount > Payment::MAX_AMOUNTS_CUSTOMER[$type]) {
            $this->request = $this->request->withParam('_ext', 'json');
            $this->set([
                'status' => 0,
                'msg' => 'payment amount too high: ' . $amount,
                'amount' => $amount,
                'confirmSubmit' => 1,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'amount', 'confirmSubmit']);
            return null;
        }

        $customersTable = $this->getTableLocator()->get('Customers');
        $customer = $customersTable->find('all', conditions: [
            $customersTable->aliasField('id_customer') => $customerId,
        ])->first();
        if (empty($customer)) {
            throw new \Exception('customer not found: ' . $customerId);
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));

        $paymentsTable = $this->getTableLocator()->get('Payments');
        try {
            $newEntity = $paymentsTable->newEntity(
                [
                    'id_customer' => $customerId,
                    'amount' => $amount,
                    'date_add' => DateTime::now(),
                    'type' => $type,
                    'status' => APP_ON,
                    'date_changed' => DateTime::now(),
                    'created_by' => $this->identity->getId(),
                ],
                ['validate' => 'add']
            );
            if ($newEntity->hasErrors()) {
                throw new \Exception($paymentsTable->getAllValidationErrors($newEntity)[0]);
            }
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $message = Configure::read('app.htmlHelper')->getPaymentText($type);
        if ($type == Payment::TYPE_DEPOSIT) {
            $message .= ' ' . __d('admin', 'for') . ' ' . $customer->name;
        }

        // payments paybacks and product can also be placed for other customers
        if (in_array($type, [Payment::TYPE_PRODUCT, Payment::TYPE_PAYBACK])) {
            if ($this->identity->isSuperadmin() && $this->identity->getId() != $customerId) {
                $message .= ' ' . __d('admin', 'for') . ' ' . $customer->name;
            }
            // security check
            if (!$this->identity->isSuperadmin() && $this->identity->getId() != $customerId) {
                throw new \Exception('user without superadmin privileges tried to insert payment for another user: ' . $customerId);
            }
        }

        if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $type == Payment::TYPE_PRODUCT && $this->identity->isSuperadmin()) {
            $newEntity->approval = APP_ON;
        }

        $newPayment = $paymentsTable->save($newEntity);
        $message .= ' ' . __d('admin', 'was_added_successfully_{0}:_{1}', [
            '',
            '<b>' . Configure::read('app.numberHelper')->formatAsCurrency($amount).'</b>',
        ]);

        $actionLogType = 'payment_' . $type . '_added';
        if ($type == Payment::TYPE_DEPOSIT) {
            $actionLogType = 'payment_deposit_customer_added';
        }
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $newPayment->id, 'payments', $message);
        
        if ($type == Payment::TYPE_DEPOSIT) {
            $message .= '. ';
            $message .= __d('admin', 'The_amount_was_added_to_the_credit_system_of_{0}_and_can_be_deleted_there.', ['<b>'.$customer->name.'</b>']);
        }

        $this->Flash->success($message);
        
        $this->request = $this->request->withParam('_ext', 'json');
        $this->set([
            'status' => 1,
            'msg' => 'ok',
            'amount' => $amount,
            'paymentId' => $newPayment->id,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'amount', 'paymentId']);
        return null;

    }

}