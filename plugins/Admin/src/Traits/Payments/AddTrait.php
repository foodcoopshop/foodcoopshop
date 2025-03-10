<?php
declare(strict_types=1);

namespace Admin\Traits\Payments;

use App\Services\SanitizeService;
use Cake\Http\Response;
use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\I18n\Date;

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

trait AddTrait
{

    public function add(): ?Response
    {
        $this->request = $this->request->withParam('_ext', 'json');
        $type = $this->getRequest()->getData('type');
        if (!is_null($type)) {
            $type = trim($type);
        }

        if (!in_array($type, [
            Payment::TYPE_PRODUCT,
            Payment::TYPE_PAYBACK,
            Payment::TYPE_DEPOSIT,
        ])) {
            throw new \Exception('payment type not correct: ' . $type);
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));

        $amount = $this->getRequest()->getData('amount');
        $amount = Configure::read('app.numberHelper')->parseFloatRespectingLocale($amount);

        $dateAdd = $this->getRequest()->getData('dateAdd');

        $paymentsTable = $this->getTableLocator()->get('Payments');
        try {
            $entity = $paymentsTable->newEntity(
                [
                    'amount' => $amount,
                    'date_add' => $dateAdd,
                ],
                ['validate' => 'add']
            );
            if ($entity->hasErrors()) {
                throw new \Exception($paymentsTable->getAllValidationErrors($entity)[0]);
            }
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $text = '';
        if (!empty($this->getRequest()->getData('text'))) {
            $text = strip_tags(html_entity_decode($this->getRequest()->getData('text')));
        }

        $message = Configure::read('app.htmlHelper')->getPaymentText($type);
        if (in_array($type, [Payment::TYPE_PRODUCT, Payment::TYPE_PAYBACK])) {
            $customerId = (int) $this->getRequest()->getData('customerId');
        }

        $actionLogType = $type;

        if ($type == 'deposit') {
            // payments to deposits can be added to customers or manufacturers
            $customerId = (int) $this->getRequest()->getData('customerId');
            if ($customerId > 0) {
                $userType = 'customer';
                $customersTable = $this->getTableLocator()->get('Customers');
                $customer = $customersTable->find('all', conditions: [
                    $customersTable->aliasField('id_customer') => $customerId,
                ])->first();
                if (empty($customer)) {
                    throw new \Exception('customer id not correct: ' . $customerId);
                }
                $message .= ' ' . __d('admin', 'for') . ' ' . $customer->name;
            }

            $manufacturerId = (int) $this->getRequest()->getData('manufacturerId');

            if ($manufacturerId > 0) {
                $userType = 'manufacturer';
                $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
                $manufacturer = $manufacturersTable->find('all', conditions: [
                    $manufacturersTable->aliasField('id_manufacturer') => $manufacturerId,
                ])->first();

                if (empty($manufacturer)) {
                    throw new \Exception('manufacturer id not correct: ' . $manufacturerId);
                }

                $message = __d('admin', 'Deposit_take_back') . ' ('.Configure::read('app.htmlHelper')->getManufacturerDepositPaymentText($text).')';
                $message .= ' ' . __d('admin', 'for') . ' ' . $manufacturer->name;
            }

            if ($type == 'deposit') {
                if (!isset($userType)) {
                    throw new \Exception('no userType set - payment cannot be saved');
                }
                $actionLogType .= '_'.$userType;
            }
        }

        // payments paybacks and product can also be placed for other users
        if (in_array($type, [Payment::TYPE_PRODUCT, Payment::TYPE_PAYBACK]) && isset($customerId)) {
            $customersTable = $this->getTableLocator()->get('Customers');
            $customer = $customersTable->find('all', conditions: [
                $customersTable->aliasField('id_customer') => $customerId,
            ])->first();
            if ($this->identity->isSuperadmin() && $this->identity->getId() != $customerId) {
                $message .= ' ' . __d('admin', 'for') . ' ' . $customer->name;
            }
            // security check
            if (!$this->identity->isSuperadmin() && $this->identity->getId() != $customerId) {
                throw new \Exception('user without superadmin privileges tried to insert payment for another user: ' . $customerId);
            }
            if (empty($customer)) {
                throw new \Exception('customer id not correct: ' . $customerId);
            }
        }

        $dateAddForEntity = DateTime::now();
        $paymentPastDate = false;
        if ($dateAdd > 0) {
            $dateAddForEntity = Date::createFromFormat(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), Configure::read('app.timeHelper')->formatToDbFormatDate($dateAdd));
            $paymentPastDate = true;
        }
        if ($dateAddForEntity->isToday()) {
            $paymentPastDate = false;
            $dateAddForEntity = DateTime::now(); // always save time for today, even if it's explicitely passed
        }

        // add entry in table payments
        $paymentsTable = $this->getTableLocator()->get('Payments');
        $entity = $paymentsTable->newEntity(
            [
                'status' => APP_ON,
                'type' => $type,
                'id_customer' => $customerId ?? 0,
                'id_manufacturer' => $manufacturerId ?? 0,
                'date_add' => $dateAddForEntity,
                'date_changed' => DateTime::now(),
                'amount' => $amount,
                'text' => $text,
                'created_by' => $this->identity->getId(),
            ]
        );

        if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $type == 'product' && $this->identity->isSuperadmin()) {
            $entity->approval = APP_ON;
        }

        $newPayment = $paymentsTable->save($entity);
        $paymentPastDateMessage = '';
        if ($type == 'deposit' && $paymentPastDate) {
            $paymentPastDateMessage = ' ' . __d('admin', 'for_the') . ' <b>' . $dateAddForEntity->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . '</b>';
        }
        $message .= ' ' . __d('admin', 'was_added_successfully_{0}:_{1}', [
            $paymentPastDateMessage,
            '<b>' . Configure::read('app.numberHelper')->formatAsCurrency($amount).'</b>',
        ]);

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('payment_' . $actionLogType . '_added', $this->identity->getId(), $newPayment->id, 'payments', $message);

        if (in_array($actionLogType, ['deposit_customer', 'deposit_manufacturer']) && isset($customer) && isset($manufacturer)) {
            $message .= '. ';
            $message .= match($actionLogType) {
                'deposit_customer' => __d('admin', 'The_amount_was_added_to_the_credit_system_of_{0}_and_can_be_deleted_there.', ['<b>'.$customer->name.'</b>']),
                'deposit_manufacturer' => __d('admin', 'The_amount_was_added_to_the_deposit_account_of_{0}_and_can_be_deleted_there.', ['<b>'.$manufacturer->name.'</b>']),
                default => '',
            };
        }

        $this->Flash->success($message);

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