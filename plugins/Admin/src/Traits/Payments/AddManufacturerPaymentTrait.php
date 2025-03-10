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

trait AddManufacturerPaymentTrait
{

    public function addManufacturerPayment(int $manufacturerId): ?Response
    {
        $type = $this->getRequest()->getData('type');
        if (!in_array($type, Payment::ALLOWED_MANUFACTURER_TYPES)) {
            throw new \Exception('payment type not valid: ' . $type);
        }

        $text = strip_tags(html_entity_decode($this->getRequest()->getData('text')));
        if (empty($text)) {
            throw new \Exception('payment text not valid: ' . $text);
        }

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $manufacturer = $manufacturersTable->find('all', conditions: [
            $manufacturersTable->aliasField('id_manufacturer') => $manufacturerId,
        ])->first();

        if (empty($manufacturer)) {
            throw new \Exception('manufacturer not found: ' . $manufacturerId);
        }

        $applyAmountTresholdCheck = (bool) $this->getRequest()->getData('applyAmountTresholdCheck');
        $amount = $this->getRequest()->getData('amount');
        $amount = Configure::read('app.numberHelper')->parseFloatRespectingLocale($amount);
    
        $maxAmount = Payment::MAX_AMOUNTS_MANUFACTURER[$text];
        if ($applyAmountTresholdCheck && $amount > $maxAmount) {
            $this->request = $this->request->withParam('_ext', 'json');
            $msg = __d('admin', 'The maximum amount of {0} was exceeded.', [
                Configure::read('app.numberHelper')->formatAsCurrency($maxAmount),
            ]);
            $msg .= ' ' . __d('admin', 'Press the submit button again to add the payment of {0} anyway.', [
                Configure::read('app.numberHelper')->formatAsCurrency($amount),
            ]);
            $this->set([
                'status' => 0,
                'msg' => $msg,
                'amount' => $amount,
                'confirmSubmit' => 1,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'amount', 'confirmSubmit']);
            return null;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));

        $paymentsTable = $this->getTableLocator()->get('Payments');

        $dateAdd = $this->getRequest()->getData('dateAdd');
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

        try {
            $newEntity = $paymentsTable->newEntity(
                [
                    'id_manufacturer' => $manufacturerId,
                    'amount' => $amount,
                    'type' => $type,
                    'status' => APP_ON,
                    'date_changed' => DateTime::now(),
                    'created_by' => $this->identity->getId(),
                    'date_add' => $dateAddForEntity,
                    'text' => $text,
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
            $message = __d('admin', 'Deposit_take_back') . ' ('.Configure::read('app.htmlHelper')->getManufacturerDepositPaymentText($text).')';
            $message .= ' ' . __d('admin', 'for') . ' ' . $manufacturer->name;
        }

        $newPayment = $paymentsTable->save($newEntity);
        $paymentPastDateMessage = '';
        if ($type == Payment::TYPE_DEPOSIT && $paymentPastDate) {
            $paymentPastDateMessage = ' ' . __d('admin', 'for_the') . ' <b>' . $dateAddForEntity->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . '</b>';
        }
        $message .= ' ' . __d('admin', 'was_added_successfully_{0}:_{1}', [
            $paymentPastDateMessage,
            '<b>' . Configure::read('app.numberHelper')->formatAsCurrency($amount).'</b>',
        ]);

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogType = 'payment_' . $type . '_added';
        if ($type == Payment::TYPE_DEPOSIT) {
            $actionLogType = 'payment_deposit_manufacturer_added';
        }

        $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $newPayment->id, 'payments', $message);

        if ($type == Payment::TYPE_DEPOSIT) {
            $message .= '. ';
            $message .= __d('admin', 'The_amount_was_added_to_the_deposit_account_of_{0}_and_can_be_deleted_there.', ['<b>'.$manufacturer->name.'</b>']);
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